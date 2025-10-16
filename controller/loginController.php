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
    $accessToken = JWT::encode(
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

    $refreshToken = JWT::encode(
        [
            'userId' => $userData['id'],
            'expiresAt' => time() + (14*24*60*60) 
        ],
        $_ENV['JWT_REFRESH_SECRET'],
        'HS512'
    );
    $expiresAt = date("Y-m-d H:i:s", strtotime("+14 days"));
    $insertRefreshToken = " INSERT INTO `refresh_tokens` (user_id,token,expires_at)
                            VALUES ('{$userData['id']}','$$refreshToken','$expiresAt')";
    mysqli_query($connect,$insertRefreshToken);
    http_response_code(200);
    echo json_encode([
        "success" => true,
        "message" => "Login successful",
        "data" => [
            "token" => $accessToken,
            "refresh_token" => $refreshToken,
            "id" => $userData['id'],
            "name" => $userData['name'],
            "email" => $userData['email'],
            "role" => $userData['role']
        ]
    ]);
}
?>