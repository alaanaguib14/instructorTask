<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include_once '../controller/refreshTokenController.php';

$data = json_decode(file_get_contents("php://input"), true);
refreshAccessToken($data);
?>