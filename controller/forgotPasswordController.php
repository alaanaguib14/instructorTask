<?php
include_once '../config/connection.php';
include_once '../vendor/autoload.php';

function forgotPassword($body){
    global $connect;
    header("Content-Type: application/json; charset=UTF-8");

    $email = trim(mysqli_real_escape_string($connect, $body['email']));
    // Check if email is empty
    if (empty($body['email'])) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Email is required"]);
        return;
    }
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(["message" => "Invalid email format."]);
        exit();
    }
    // Check if email exists in the database
    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($connect, $query);
    if(mysqli_num_rows($result) === 0){
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "Email not found"
        ]);
    }
    else
    {
        $user = mysqli_fetch_assoc($result);
        // Generate token and hash it
        $token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", strtotime("+10 minutes")); 
        $hashedToken = password_hash($token, PASSWORD_DEFAULT);

        // Delete any existing tokens for the user
        $deleteQuery = "DELETE FROM password_resets WHERE `id` = {$user['id']}";
        mysqli_query($connect, $deleteQuery);

        // Store token in the database
        $insertQuery = "INSERT INTO password_resets (`id`, `reset_token`, `reset_expires`) VALUES ({$user['id']}, '$hashedToken', '$expires')";
        $result = mysqli_query($connect, $insertQuery);
        if($result){
            $resetLink = "http://localhost/itask/route/reset_password.php?token=$token";
 
            echo json_encode([
                "success" => true, 
                "message" => "Password reset email sent",
                "data" => [
                    "resetLink" => $resetLink
                ]
            ]);
        }else{
            http_response_code(500);
            echo json_encode([
                "success" => false, 
                "message" => "Failed to create password reset token"
            ]);
        }
    }
}

// reset password
function resetPassword($body,$token){
    global $connect;
    header("Content-Type: application/json;");

    $newPassword = trim($body['new_password']);
    $confirmPassword = trim($body['confirm_password']);

    // Validate token
    if (empty($token)) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Reset token is required"
        ]);
        return;
    }

    // Validate passwords
    if (empty($newPassword) || empty($confirmPassword)) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "New password and confirmation are required"
        ]);
        return;
    }
    // confirm password
    if ($newPassword !== $confirmPassword) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Passwords do not match"
        ]);
        return;
    }
    // check password length
    if (strlen($newPassword) < 6) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => 'Password must be at least 6 characters long'
        ]);
        return;
    }
    // check password complexity
    if (!preg_match('/[A-Z]/', $newPassword) || !preg_match('/[a-z]/', $newPassword) || !preg_match('/[0-9]/', $newPassword) || !preg_match('/[\W]/', $newPassword)) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Password must include at least one uppercase letter, one lowercase letter, one number, and one special character."
        ]);
        return;
    }
    // check token validity
    $tokenQuery = " SELECT `password_resets`.*, `users`.`email`
                    FROM `password_resets`
                    JOIN `users` ON `password_resets`.`id`=`users`.`id`
                    WHERE `password_resets`.`reset_expires` > NOW()";
    $result = mysqli_query($connect, $tokenQuery);
    if(mysqli_num_rows($result) === 0){
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Invalid or expired token"
        ]);
        return;
    }
    $row = mysqli_fetch_assoc($result);
    if(!password_verify($token, $row['reset_token'])){
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Invalid token"
        ]);
        return;
    }
    // update password
    $hashedPassword = password_hash($newPassword,PASSWORD_DEFAULT);
    $updatePassword = " UPDATE `users` 
                        SET `password` = '$hashedPassword'
                        WHERE `id` = {$row['id']}";
    $updateResult = mysqli_query($connect,$updatePassword);
    if ($updateResult) {
        mysqli_query($connect, "DELETE FROM password_resets WHERE id = {$row['id']}");
        http_response_code(200);
        echo json_encode([
            "success" => true,
            "message" => "Password reset successfully",
            "data" => [
                "user_id" => $row['id']
            ]
        ]);
    }else{
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Failed to reset password"
        ]);
    }
}
?>