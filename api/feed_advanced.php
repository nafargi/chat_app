<?php
require "../config/db.php";

$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

if($user_id <= 0 || $limit <= 0 || $offset < 0){
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

try {
    // Step 1: Fetch posts from self + followed users
    $stmt = $pdo->prepare("
        SELECT p.id, p.user_id, p.content, p.image, p.created_at, u.username,
            (SELECT COUNT(*) FROM likes WHERE post_id = p.id) AS likes_count,
            (SELECT COUNT(*) FROM comments WHERE post_id = p.id) AS comments_count,
            (CASE WHEN f.follower_id IS NOT NULL THEN 1 ELSE 0 END) AS is_following
        FROM posts p
        INNER JOIN users u ON p.user_id = u.id
        LEFT JOIN followers f ON f.follower_id = :user_id AND f.following_id = p.user_id
        ORDER BY (likes_count*2 + comments_count*3 + is_following*5 - TIMESTAMPDIFF(HOUR, p.created_at, NOW())*0.5) DESC
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'posts' => $posts, 'next_offset' => $offset + count($posts)]);

} catch(PDOException $e){
    echo json_encode(['success' => false, 'message' => 'Database error: '.$e->getMessage()]);
}
?>
