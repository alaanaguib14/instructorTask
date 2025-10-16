<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include_once '../controller/forgotPasswordController.php';
$body = json_decode(file_get_contents("php://input"), true);
forgotPassword($body);

?>