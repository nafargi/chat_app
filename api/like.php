<?php
require "../config/db.php";

// Step 1: Get POST data
$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
$post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;

// Step 2: Validate input
if($user_id <= 0 || $post_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID or post ID']);
    exit;
}

try {
    // Step 3: Check if already liked
    $stmt = $pdo->prepare("SELECT id FROM likes WHERE user_id = :user_id AND post_id = :post_id");
    $stmt->execute(['user_id' => $user_id, 'post_id' => $post_id]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if($existing) {
        // Step 4a: Unlike
        $stmt = $pdo->prepare("DELETE FROM likes WHERE id = :id");
        $stmt->execute(['id' => $existing['id']]);
        echo json_encode(['success' => true, 'message' => 'Post unliked']);
    } else {
        // Step 4b: Like
        $stmt = $pdo->prepare("INSERT INTO likes (user_id, post_id) VALUES (:user_id, :post_id)");
        $stmt->execute(['user_id' => $user_id, 'post_id' => $post_id]);
        echo json_encode(['success' => true, 'message' => 'Post liked']);
    }

} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: '.$e->getMessage()]);
}
?>
