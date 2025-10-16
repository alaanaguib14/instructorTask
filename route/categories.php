<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PATCH, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
include_once "../middleware/auth.php";
include_once "../middleware/jwtVerify.php";
include_once "../controller/categoriesController.php";

$user = verifyJwt();
$method = $_SERVER['REQUEST_METHOD'];

if ($method =='GET')
{
    $id = (int)$_GET['id'];
    getCategories($user->role, $id);
}
elseif($method == 'POST')
{
    authorize($user,['admin','editor']);
    $data = json_decode(file_get_contents("php://input"), true);
    addCategory($data,$user);
}
elseif ($method == 'PATCH')
{
    authorize($user,['admin','editor']);
    $data = json_decode(file_get_contents("php://input"), true);
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Category ID is required"
        ]);
        exit;
    }
    $id = (int)$_GET['id'];
    updateCategory($data, $id, $user);
}
elseif ($method == 'DELETE')
{
    authorize($user,'admin');
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Category ID is required"
        ]);
        exit;
    }
    $id = (int)$_GET['id'];
    deleteCategory($id, $user);
}
elseif($method == 'PATCH' && isset($_GET['action']) && $_GET['action'] === 'restore')
{
    authorize($user,'admin');
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Category ID is required"
        ]);
        exit;
    }
    $id = (int)$_GET['id'];
    restoreCategory($id, $user);
}
else
{
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Method not allowed"
    ]);
    exit;
}
?>