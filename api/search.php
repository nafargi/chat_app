<?php
require "../config/db.php";

// Step 1: Get parameters
$query = isset($_GET['query']) ? trim($_GET['query']) : '';
$type = isset($_GET['type']) ? trim($_GET['type']) : 'user';

if(empty($query)){
    echo json_encode(['success' => false, 'message' => 'Query cannot be empty']);
    exit;
}

// Step 2: Prepare search
try {
    if($type === 'user'){
        // Search users by username
        $stmt = $pdo->prepare("SELECT id, username, created_at FROM users WHERE username LIKE :query LIMIT 20");
        $stmt->execute(['query' => "%$query%"]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else if($type === 'post'){
        // Search posts by content
        $stmt = $pdo->prepare("
            SELECT posts.id, posts.user_id, posts.content, posts.image, posts.created_at, users.username
            FROM posts
            INNER JOIN users ON posts.user_id = users.id
            WHERE posts.content LIKE :query
            ORDER BY posts.created_at DESC
            LIMIT 20
        ");
        $stmt->execute(['query' => "%$query%"]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid search type']);
        exit;
    }

    echo json_encode(['success' => true, 'results' => $results]);

} catch(PDOException $e){
    echo json_encode(['success' => false, 'message' => 'Database error: '.$e->getMessage()]);
}
?>
