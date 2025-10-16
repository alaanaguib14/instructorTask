<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
include_once '../config/connection.php';
include_once '../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();
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

    $refreshToken = trim(mysqli_real_escape_string($connect, $user['refresh_token']));
    
    $query = " SELECT * FROM `refresh_tokens`
               WHERE `token` = '$refreshToken'
               LIMIT 1";
    $result= mysqli_query($connect,$query);
    if(mysqli_num_rows($result)===0 || !$result){
        http_response_code(403);
        echo json_encode([
            "success"=> false,
            "message"=> "Invalid refresh token because no result found"
        ]);
        return;
    }

    $tokenData = mysqli_fetch_assoc($result);

    if (!$tokenData) {
        http_response_code(403);
        echo json_encode([
            "success" => false, 
            "message" => "Invalid refresh token because no token data"
        ]);
        return;
    }

    if (strtotime($tokenData['expires_at']) < time()) {
        // delete expired token from db
        mysqli_query($connect, "DELETE FROM refresh_tokens WHERE token='$refreshToken'");
        http_response_code(403);
        echo json_encode([
            "success" => false, 
            "message" => "Expired refresh token"
        ]);
        return;
    }

    try {
        $decoded = JWT::decode($refreshToken, new Key($_ENV['JWT_REFRESH_SECRET'],'HS512' ));

        $userId = isset($decoded->userId) ? $decoded->userId : (isset($decoded->id) ? $decoded->id : null);
        $userName = isset($decoded->name) ? $decoded->name : null;
        $userEmail = isset($decoded->email) ? $decoded->email : null;
        $userRole = isset($decoded->role) ? $decoded->role : null;

        if (!$userId) {
            http_response_code(403);
            echo json_encode([
                "success" => false, 
                "message" => "Invalid refresh token payload"
            ]);
            return;
        }
        
        $newAccessToken= JWT::encode([
            'createdAt' => time(),
            'expiresAt' => time() + 1800, // 30 minutes
            'userId' => $userId,
            'name' => $userName,
            'email' => $userEmail,
            'role' => $userRole
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
        return;
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