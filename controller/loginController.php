<?php
include_once "../config/connection.php";
include_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

function login($user){
    global $connect;

    header("Content-Type: application/json");
    $email = trim(mysqli_real_escape_string($connect, $user['email']));
    $password = trim($user['password']);
    // validate missing fields
    if (empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Email and password are required"
        ]);
        return;
    }
    // user existence
    $userQuery = "SELECT * FROM `users` WHERE `email`='$email'";
    $result = mysqli_query($connect, $userQuery);
    if (mysqli_num_rows($result) === 0) {
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "message" => "Invalid email or password"
        ]);
        return;
    }
    // verify password 
    $userData = mysqli_fetch_assoc($result);
    if (!password_verify($password, $userData['password'])) {
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "message" => "Invalid email or password"
        ]);
        return;
    }
    // jwt generation
    $token = JWT::encode(
    [
        'createdAt' => time(),
        'expiresAt' => time() + 1800, // 30 minutes
        'userId' => $userData['id'],
        'name' => $userData['name'],
        'email' => $userData['email'],
        'role' => $userData['role']
    ], 
    $_ENV['JWT_SECRET'],
    'HS512');

    http_response_code(200);
    echo json_encode([
        "success" => true,
        "message" => "Login successful",
        "token" => $token,
        "data" => [
            "id" => $userData['id'],
            "name" => $userData['name'],
            "email" => $userData['email'],
            "role" => $userData['role']
        ]
    ]);
}
?>