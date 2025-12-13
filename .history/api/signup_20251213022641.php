<?php
// Step 1: Include database connection
require "../config/db.php"; 
// Explanation:
// We need to talk to the database. 
// `db.php` already creates $pdo (the PDO connection object).
// If we didn’t include this, we couldn’t insert users into the database.

// Step 2: Get POST data safely
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
// Explanation:
// $_POST gets data sent from frontend via POST method.
// trim() removes extra spaces (prevents accidental empty fields).
// We use isset() to avoid "undefined index" errors if fields are missing.

// Step 3: Validate input
if(empty($username) || empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}
// Explanation:
// Input validation prevents empty fields from being saved in the database.
// Always stop execution (exit) if validation fails, to prevent invalid insert.

// Step 4: Validate email format
if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}
// Explanation:
// filter_var ensures email has proper format
// prevents garbage data or invalid emails

// Step 5: Check if username or email already exists
try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username OR email = :email");
    $stmt->execute(['username' => $username, 'email' => $email]);
    if($stmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'Username or email already exists']);
        exit;
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: '.$e->getMessage()]);
    exit;
}
// Explanation:
// Prepared statements prevent SQL injection (secure)
// We check for duplicates to enforce unique usernames/emails
// rowCount() tells us if any record exists
// try/catch handles database errors gracefully

// Step 6: Hash the password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
// Explanation:
// We NEVER store plain passwords
// password_hash uses a secure algorithm (bcrypt)
// PASSWORD_DEFAULT ensures future-proof hashing

// Step 7: Insert the new user into the database
try {
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, created_at) VALUES (:username, :email, :password, NOW())");
    $stmt->execute([
        'username' => $username,
        'email' => $email,
        'password' => $hashedPassword
    ]);
    echo json_encode(['success' => true, 'message' => 'User registered successfully']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: '.$e->getMessage()]);
}
// Explanation:
// Prepared statement securely inserts the user
// NOW() automatically sets created_at timestamp
// Returning JSON allows frontend (React) to handle response

?>
