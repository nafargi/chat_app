<?php
require "../config/db.php";

// Step 1: Get parameters
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

// Step 2: Validate input
if($user_id <= 0 || $limit <= 0 || $offset < 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

try {
    // Step 3: Fetch paginated posts
    $stmt = $pdo->prepare("
        SELECT posts.id, posts.user_id, posts.content, posts.image, posts.created_at, users.username
        FROM posts
        INNER JOIN users ON posts.user_id = users.id
        WHERE posts.user_id = :user_id
        OR posts.user_id IN (
            SELECT following_id FROM followers WHERE follower_id = :user_id
        )
        ORDER BY posts.created_at DESC
        LIMIT :limit OFFSET :offset
    ");

    // Bind values
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'posts' => $posts,
        'next_offset' => $offset + count($posts)
    ]);

} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: '.$e->getMessage()]);
}
?>
