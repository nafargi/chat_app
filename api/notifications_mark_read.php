<?php
require "../config/db.php";

// Step 1: Get POST data
$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
$notification_id = isset($_POST['notification_id']) ? (int)$_POST['notification_id'] : 0;

if($user_id <= 0){
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit;
}

try {
    if($notification_id > 0){
        // Step 2a: Mark a single notification as read
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = :id AND user_id = :user_id");
        $stmt->execute(['id' => $notification_id, 'user_id' => $user_id]);
        $msg = 'Notification marked as read';
    } else {
        // Step 2b: Mark all notifications as read
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = :user_id AND is_read = 0");
        $stmt->execute(['user_id' => $user_id]);
        $msg = 'All notifications marked as read';
    }

    echo json_encode(['success' => true, 'message' => $msg]);

} catch(PDOException $e){
    echo json_encode(['success' => false, 'message' => 'Database error: '.$e->getMessage()]);
}
?>
