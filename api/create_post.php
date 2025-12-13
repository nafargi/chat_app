<?php
// Step 1: Include database connection
require "../config/db.php";

// Step 2: Get POST data
$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
$content = isset($_POST['content']) ? trim($_POST['content']) : '';
$imagePath = null; // default if no image

// Step 3: Validate input
if($user_id <= 0 || empty($content)) {
    echo json_encode(['success' => false, 'message' => 'User ID and content are required']);
    exit;
}
if(strlen($content) > 1000) {
    echo json_encode(['success' => false, 'message' => 'Content too long']);
    exit;
}

// Step 4: Handle image upload
if(isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = "../uploads/";
    if(!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true); // create folder if not exists
    }

    $fileTmpPath = $_FILES['image']['tmp_name'];
    $fileName = time() . "_" . basename($_FILES['image']['name']);
    $destPath = $uploadDir . $fileName;

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    if(!in_array($fileExt, $allowedExtensions)) {
        echo json_encode(['success' => false, 'message' => 'Invalid image type']);
        exit;
    }

    if(move_uploaded_file($fileTmpPath, $destPath)) {
        $imagePath = 'uploads/' . $fileName; // save relative path in DB
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to upload image']);
        exit;
    }
}

// Step 5: Insert into database
try {
    $stmt = $pdo->prepare("INSERT INTO posts (user_id, content, image, created_at) VALUES (:user_id, :content, :image, NOW())");
    $stmt->execute([
        'user_id' => $user_id,
        'content' => $content,
        'image' => $imagePath
    ]);

    echo json_encode(['success' => true, 'message' => 'Post created successfully']);

} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: '.$e->getMessage()]);
}
?>
