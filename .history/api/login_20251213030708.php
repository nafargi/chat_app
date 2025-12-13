<?php
require '../config/db.php';

$email = isset($_POST['email']) ? trim($_POST['email']): '';
$password = isset($_POST['password']) ? $_POST['password']: '';

if(empty($email) || empty($password)){
    echo json_encode(['success' => false, 'message' => " All fields are required"]);
    exit;
}

if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
} 


try{}catch(PDOException $){}

?>