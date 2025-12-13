<?php
require "../config/db.php";

// Step 1: Get POST data
$follower_id = isset($_POST['follower_id']) ? (int)$_POST['follower_id'] : 0;
$following_id = isset($_POST['following_id']) ? (int)$_POST['following_id'] : 0;

// Step 2: Validate input
if($follower_id <= 0 || $following_id <= 0 || $follower_id === $following_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid user IDs']);
    exit;
}

try {
    // Step 3: Check if already following
    $stmt = $pdo->prepare("SELECT id FROM followers WHERE follower_id = :follower_id AND following_id = :following_id");
    $stmt->execute([
        'follower_id' => $follower_id,
        'following_id' => $following_id
    ]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if($existing) {
        // Step 4a: Unfollow
        $stmt = $pdo->prepare("DELETE FROM followers WHERE id = :id");
        $stmt->execute(['id' => $existing['id']]);
        echo json_encode(['success' => true, 'message' => 'Unfollowed successfully']);
    } else {
        // Step 4b: Follow
        $stmt = $pdo->prepare("INSERT INTO followers (follower_id, following_id) VALUES (:follower_id, :following_id)");
        $stmt->execute([
            'follower_id' => $follower_id,
            'following_id' => $following_id
        ]);
        echo json_encode(['success' => true, 'message' => 'Followed successfully']);
    }

} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: '.$e->getMessage()]);
}
?>
