<?php
// jwt verification middleware
include_once __DIR__ . '/../vendor/autoload.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

function verifyJwt(){

    header('Content-Type: application/json');

    $headers=getallheaders();

    // extract the token
    $token = trim(str_replace("Bearer", "", $headers['Authorization']));

    // no token provided
    if (!$token) {
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "message" => "no token provided"
        ]);
        exit;
    }
    // verify token
    try {
        $decoded = JWT::decode($token, new Key($_ENV['JWT_SECRET'], 'HS512'));
        // token expired
        if ($decoded->expiresAt < time()) {
            http_response_code(401);
            echo json_encode([
                "success" => false,
                "message" => "token expired"
            ]);
            exit;
        }
        // token is valid
        return $decoded;
    } catch (\Throwable $th) {
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "message" => "invalid token"
        ]);
        exit;
    }
}

?>