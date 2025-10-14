<?php
include_once "../config/connection.php";

function createUser($user){
    global $connect;

    header("Content-Type: application/json");

    $name = trim(mysqli_real_escape_string($connect, $user['name']));
    $email = trim(mysqli_real_escape_string($connect, $user['email']));
    $password = trim($user['password']);
    $confirmPassword = trim($user['confirmPassword']);
    $role = isset($user['role']) ? $user['role'] : 'user';

    // validate missing fields
    if (empty($name)) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Name is required"
        ]);
        return;
    }
    if (empty($email)) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Email is required"
        ]);
        return;
    }
    if (empty($password)) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Password is required"
        ]);
        return;
    }
    // check email format 
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Invalid email format"
        ]);
        return;
    }
    // check email uniqueness
    $emailExists = "SELECT `id` FROM `users` WHERE `email`='$email'";
    $result = mysqli_query($connect,$emailExists);
    if (mysqli_num_rows($result) > 0) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Email already exists"
        ]);
        return;
    }
    // check password length
    if (strlen($password) < 6) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => 'Password must be at least 6 characters long'
        ]);
        return;
    }
    // check password complexity
    if (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password) || !preg_match('/[\W]/', $password)) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Password must include at least one uppercase letter, one lowercase letter, one number, and one special character."
        ]);
        return;
    }

    // confirm password
    if ($password !== $confirmPassword) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Passwords do not match"
        ]);
        return;
    }
    // hash password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    // insert user
    $insertUserQuery = "INSERT INTO `users` (name,email,password,role) VALUES ('$name','$email', '$hashedPassword', '$role')";
    $insertResult = mysqli_query($connect,$insertUserQuery);
    if ($insertResult) {
        http_response_code(201);
        echo json_encode([
            "success" => true,
            "message" => "User registered successfully",
            "data" => [
                "name" => $name,
                "email" => $email,
                "role" => $role
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Failed to register user, Try again later. " . mysqli_error($connect)
        ]);
    }


}
?>