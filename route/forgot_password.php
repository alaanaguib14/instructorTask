<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include_once '../controller/forgotPasswordController.php';
include_once "../middleware/jwtVerify.php";

$headers= getallheaders();

// Check if the user already has a valid token
if (isset($headers['Authorization'])) {
    $user = verifyJwt();
    if ($user) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "User is already logged in"
        ]);
        exit;
    }
}
$body = json_decode(file_get_contents("php://input"), true);
forgotPassword($body);

?>