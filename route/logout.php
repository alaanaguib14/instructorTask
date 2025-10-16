<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include_once "../config/connection.php";
include_once "../middleware/jwtVerify.php";

$user = verifyJwt();
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $headers = getallheaders();
    $token = trim(str_replace("Bearer", "", $headers['Authorization']));

    $query = "DELETE FROM `refresh_tokens` WHERE `user_id` = {$user->userId}";
    $result = mysqli_query($connect, $query);

    if ($result) {
        http_response_code(200);
        echo json_encode([
            "success" => true,
            "message" => "Logged out successfully"
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Failed to logout"
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Method not allowed"
    ]);
}
?>