<?php
require "../config/db.php";

// Step 1: Get user_id
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
if($user_id <= 0){
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit;
}

try {
    // Step 2: Fetch user info
    $stmt = $pdo->prepare("SELECT id, username, created_at FROM users WHERE id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$user){
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }

    // Step 3: Fetch followers & following counts
    $stmt = $pdo->prepare("
        SELECT 
            (SELECT COUNT(*) FROM followers WHERE following_id = :user_id) AS followers,
            (SELECT COUNT(*) FROM followers WHERE follower_id = :user_id) AS following
    ");
    $stmt->execute(['user_id' => $user_id]);
    $counts = $stmt->fetch(PDO::FETCH_ASSOC);

    // Step 4: Fetch user posts
    $stmt = $pdo->prepare("
        SELECT id, content, image, created_at 
        FROM posts 
        WHERE user_id = :user_id 
        ORDER BY created_at DESC
    ");
    $stmt->execute(['user_id' => $user_id]);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Step 5: Return JSON
    echo json_encode([
        'success' => true,
        'user' => $user,
        'followers' => (int)$counts['followers'],
        'following' => (int)$counts['following'],
        'posts' => $posts
    ]);

} catch(PDOException $e){
    echo json_encode(['success' => false, 'message' => 'Database error: '.$e->getMessage()]);
}
?>

