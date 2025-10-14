<?php
include_once "../controller/loginController.php";
$credentials = json_decode(file_get_contents("php://input"), true);
login($credentials);

?>