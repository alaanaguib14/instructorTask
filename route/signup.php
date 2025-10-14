<?php
include_once "../controller/signupController.php";
$userData = json_decode(file_get_contents("php://input"), true);
createUser($userData);

?>