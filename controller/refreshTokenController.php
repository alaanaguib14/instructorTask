<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
include_once '../config/connection.php';
include_once '../vendor/autoload.php';

function refreshAccessToken($user){
    global $connect;

    header("Content-Type: application/json");
    if (empty($user['refresh_token'])) {
        http_response_code(400);
        echo json_encode([
            "success"=> false,
            "message"=> "Refresh token is required"
        ]);
        return;
    }

    $refreshToken = trim($user['refresh_token']);

    $query = " SELECT * FROM `refresh_tokens`
               WHERE `token` = '$refreshToken'";
    $result= mysqli_query($connect,$query);
    if(mysqli_num_rows($result)===0){
        http_response_code(403);
        echo json_encode([
            "success"=> false,
            "message"=> "Invalid refresh token"
        ]);
    }

    $tokenData = mysqli_fetch_assoc($result);
    if (strtotime($tokenData['expires_at']) < time()) {
        mysqli_query($connect, "DELETE FROM refresh_tokens WHERE token='$refreshToken'");
        http_response_code(403);
        echo json_encode(["success" => false, "message" => "Expired refresh token"]);
        return;
    }

    try {
        $decoded = JWT::decode($refreshToken, new Key($_ENV['JWT_REFRESH_SECRET'],'HS512' ));
        $newAccessToken= JWT::encode([
            'createdAt' => time(),
            'expiresAt' => time() + 1800, // 30 minutes
            'userId' => $decoded->userId,
            'name' => $decoded->name,
            'email' => $decoded->email,
            'role' => $decoded->role
        ],
        $_ENV['JWT_SECRET'],
        'HS512'
        );
        echo json_encode([
            "success"=> true,
            "message"=> "Access token refreshed",
            "data"=> [
                "token"=> $newAccessToken
            ]
        ]);
    } catch (\Throwable $th) {
        http_response_code(403);
        echo json_encode([
            "success"=> false,
            "message"=> "Invalid refresh token"
        ]);
        return;
    }

}
?>