<?php
require "../config/db.php";

// Step 1: Get POST data
$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
$post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
$comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

// Step 2: Validate input
if($user_id <= 0 || $post_id <= 0 || empty($comment)) {
    echo json_encode(['success' => false, 'message' => 'User ID, Post ID and comment are required']);
    exit;
}
if(strlen($comment) > 500) {
    echo json_encode(['success' => false, 'message' => 'Comment too long']);
    exit;
}

try {
    // Step 3: Insert comment
    $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, comment, created_at) VALUES (:post_id, :user_id, :comment, NOW())");
    $stmt->execute([
        'post_id' => $post_id,
        'user_id' => $user_id,
        'comment' => $comment
    ]);

    // Step 4: Return JSON response
    echo json_encode([
        'success' => true,
        'message' => 'Comment added successfully',
        'comment' => [
            'id' => $pdo->lastInsertId(),
            'post_id' => $post_id,
            'user_id' => $user_id,
            'comment' => $comment,
            'created_at' => date('Y-m-d H:i:s')
        ]
    ]);

} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: '.$e->getMessage()]);
}
?>
