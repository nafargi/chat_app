<?php
require "../config/db.php";

// Step 1: Get parameters
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

if($user_id <= 0){
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit;
}

try {
    // Step 2: Fetch notifications (unread first, newest first)
    $stmt = $pdo->prepare("
        SELECT n.id, n.type, n.from_user_id, u.username AS from_username,
               n.post_id, n.is_read, n.created_at
        FROM notifications n
        INNER JOIN users u ON n.from_user_id = u.id
        WHERE n.user_id = :user_id
        ORDER BY n.is_read ASC, n.created_at DESC
        LIMIT 20
    ");
    $stmt->execute(['user_id' => $user_id]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'notifications' => $notifications]);

} catch(PDOException $e){
    echo json_encode(['success' => false, 'message' => 'Database error: '.$e->getMessage()]);
}
?>
