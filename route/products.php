<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PATCH, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
include_once "../controller/productsController.php";
include_once "../middleware/auth.php";
include_once "../middleware/jwtVerify.php";

$user = verifyJwt();
$method = $_SERVER['REQUEST_METHOD'];

if ($method =='GET')
{
    // pagination, filtering, id, and searching parameters 
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 6;
    $id = null;
    $param = [];
    if (isset($_GET['cat_id'])) {
        $param['cat_id'] = (int) $_GET['cat_id'];
    }
    if (isset($_GET['search'])) {
        $param['search'] = trim($_GET['search']);
    }
    if (isset($_GET['order_by'])) {
        $param['order_by'] = $_GET['order_by'];
    }
    if (isset($_GET['id'])) {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : null;
    }
    getProducts($page, $limit, $param, $user->role, $id);
}
elseif($method == 'POST')
{
    authorize($user,['admin','editor']);
    $data = json_decode(file_get_contents("php://input"), true);
    addProduct($data,$user);
}
elseif ($method == 'PATCH')
{
    authorize($user,['admin','editor']);
    $data = json_decode(file_get_contents("php://input"), true);
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Product ID is required"
        ]);
        exit;
    }
    $id = (int)$_GET['id'];
    updateProduct($data, $id, $user);
}
elseif ($method == 'DELETE')
{
    authorize($user,'admin');
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Product ID is required"
        ]);
        exit;
    }
    $id = (int)$_GET['id'];
    deleteProduct($id, $user);
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