<?php
// Step 1: Include database connection
require "../config/db.php";

// Step 2: Get current user ID
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

if($user_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit;
}

// Step 3: Fetch posts
try {
    // SQL explanation:
    // - Select posts by the user OR by users they follow
    // - Join users table to get username
    // - Sort by newest first
    $stmt = $pdo->prepare("
        SELECT posts.id, posts.user_id, posts.content, posts.image, posts.created_at, users.username
        FROM posts
        INNER JOIN users ON posts.user_id = users.id
        WHERE posts.user_id = :user_id
        OR posts.user_id IN (
            SELECT following_id FROM followers WHERE follower_id = :user_id
        )
        ORDER BY posts.created_at DESC
    ");

    $stmt->execute(['user_id' => $user_id]);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'posts' => $posts]);

} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: '.$e->getMessage()]);
}
?>
