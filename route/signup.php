<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

include_once "../controller/signupController.php";
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
$userData = json_decode(file_get_contents("php://input"), true);
createUser($userData);

?>