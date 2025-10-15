<?php
// authorization middleware
include_once __DIR__ . '/../vendor/autoload.php';

function authorize($decoded, $requiredRole){
    header('Content-Type: application/json');

    // check user role
    if ($decoded->role !== $requiredRole) {
        http_response_code(403);
        echo json_encode([
            "success" => false,
            "message" => "access denied."
        ]);
        exit;
    }
    // user is authorized
    return true;
}

?>