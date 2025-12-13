<?php
require '../config/db.php';

$email = isset($_POST['email']) ? trim($_POST['email']): '';
$password = isset($_POST['password']) ? $_POST['password']: '';

if(empty($email) || empty($password)){
    echo json_encode([
        'success' => false, 
        'message' => " All fields are required"
    ]);
    exit;
}

if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid email format'
    ]);
    exit;
} 


try{
    $stmt = $pdo->prepare("SELECT id,password_hash,username FROM users WHERE email = :email");
    $stmt -> execute(['email' => $email]);
    $user = $stmt -> fetch(PDO::FETCH_ASSOC);

    if(!$user){
        echo json_encode([
            'success' => false ,
             'message' => 'User not found'
            ]);
    }

    if(password_verify($password, $user['password_hash'])){
        echo json_encode([
            'succes' => true,
            'message' => 'Login succesfully',
            'user' => [
                'username' => $user['id'],
                'username' => $user['username']
            ]
        ]); 
    }
   
    if ($user['email'] != $email || $user['password_hash'] != $password){
        echo json_endcode([
            'success' 
        ])
    }

}catch(PDOException $e){
    echo json_encode(['success' => false , 'message' => 'Database Error'.$e->getMessage()]);
    exit;
}

?>