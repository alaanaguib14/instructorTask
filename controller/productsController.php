<?php
include_once "../config/connection.php";

// add product
function addProduct($data, $userRole){
    global $connect;

    header("Content-Type: application/json");
    $pName = trim(mysqli_real_escape_string($connect, $data['name']));
    $pPrice = (float) trim($data['price']);
    $pDesc = isset($data['description']) ? trim(mysqli_real_escape_string($connect, $data['description'])) : '';
    $cat_id = (int) trim($data['category_id']);
    $pImage = isset($data['image']) ? trim(mysqli_real_escape_string($connect, $data['image'])) : null;

    // validate missing fields
    if (empty($pName) || empty($pPrice) || empty($pDesc) || empty($cat_id)) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Product name, price, description, and category ID are required"
        ]);
        return;
    }
    // validate price
    if (!is_numeric($pPrice) || $pPrice <= 0) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Invalid product price"
        ]);
        return;
    }

    // if the category is deleted or not found
    $catQuery = "SELECT * FROM `category` WHERE `cat_id` = '$cat_id' AND `is_deleted` = 0";
    $catResult = mysqli_query($connect,$catQuery);
    if (mysqli_num_rows($catResult) === 0) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Invalid category ID"
        ]);
        return;
    }
    //validate image type and size 
    if ($pImage) {
        $imageData = base64_decode($pImage, true);
        if ($imageData === false) {
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "message" => "Invalid image format"
            ]);
            return;
        }
        if (strlen($imageData) > 10 * 1024 * 1024) { // 10MB limit
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "message" => "Image size exceeds 10MB limit"
            ]);
            return;
        }
    }

    // store the image in uploads dir

    if ($pImage) {
        $imageData = base64_decode($pImage);
        $imageName = 'product-' . time() . '.png';
        $imagePath = '../uploads/' . $imageName;
        if (file_put_contents($imagePath, $imageData) === false) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Failed to save product image"
            ]);
            return;
        }
        $pImage = 'uploads/' . $imageName; 
    } else {
        $pImage = null;
    }

    // insert product
    $insertQuery = "INSERT INTO `products` (`product_name`,`product_desc`,`price`,`product_img`,`cat_id`)
                    VALUES ('$pName','$pDesc','$pPrice','$pImage','$cat_id')";
    $result = mysqli_query($connect,$insertQuery);
    if ($result) {
        http_response_code(201);
        echo json_encode([
            "success" => true,
            "message" => "Product added successfully",
            "data"=>[
                "product_name" => $pName,
                "product_desc" => $pDesc,
                "price" => $pPrice,
                "category_id" => $cat_id,
                "image" => $pImage
            ]
        ]);
    }else{
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Failed to add product, Try again"
        ]);
    }
}

// get products
function getProducts($page = 1, $limit = 6, $param,$userRole,$id = null){
    global $connect;

    header("Content-Type: application/json");
    $products = [];
    $parameters = "";
    // param can be category_id or search term or order by or null
    if ($param) {
        // parameters for SQL query
        if(isset($param['cat_id'])){
            $cat_id = (int) $param['cat_id'];
            $parameters = " AND `cat_id` = '$cat_id' ";
        }
        if(isset($param['search'])){
            $search = trim(mysqli_real_escape_string($connect, $param['search']));
            $parameters .= " AND (`product_name` LIKE '%$search%' OR `product_desc` LIKE '%$search%') ";
        }
        if (isset($param['order_by'])) {
            $order_by = $param['order_by'];
            if ($order_by === 'price_asc') {
                $parameters .= " ORDER BY `price` ASC ";
            } elseif ($order_by === 'price_desc') {
                $parameters .= " ORDER BY `price` DESC ";
            } else {
                $parameters .= " ORDER BY `product_id` DESC "; // default order
            }
        }
    }

    // pagination 
    $offset = ($page - 1) * $limit;
    $countQuery = "SELECT COUNT(*) as total FROM `products` WHERE `is_deleted` = 0 $parameters";
    $countResult = mysqli_query($connect, $countQuery);
    $totalProducts = mysqli_fetch_assoc($countResult)['total'];
    $totalPages = ceil($totalProducts / $limit);

    //if the user requests a page number greater than total pages
    if ($page > $totalPages && $totalProducts > 0) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Page number exceeds total pages",
            "data" => [
                "totalPages" => $totalPages,
                "totalProducts" => $totalProducts
            ]
        ]);
        return;
    }

    if ($id !== null && is_int($id)) {

        $productQuery = "SELECT `products`.*,`category`.`cat_title`
                        FROM `products`
                        LEFT JOIN `category` ON `products`.`cat_id` = `category`.`cat_id`
                        WHERE `products`.`is_deleted` = 0 
                        AND `category`.`is_deleted` = 0 
                        AND `products`.`product_id` = '$id'";
        $productResult = mysqli_query($connect,$productQuery);
        //product is not found
        if (mysqli_num_rows($productResult) === 0) {
            http_response_code(404);
            echo json_encode([
                "success" => false,
                "message" => "Product not found"
            ]);
            return;
        }
        if ($productResult) {
            foreach ($productResult as $key) {
                $products[] = $key;
            }
            http_response_code(200);
            echo json_encode([
                "success" => true,
                "message" => "Product fetched successfully",
                "data" => [
                    "product" => $products
                ]
            ]);
            return;
        } else {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Failed to fetch product, Try again later"
            ]);
            return;
        }
    }elseif($param) {
        $productQuery = "SELECT `products`.*,`category`.`cat_title`
                        FROM `products`
                        LEFT JOIN `category` ON `products`.`cat_id` = `category`.`cat_id`
                        WHERE `products`.`is_deleted` = 0 
                        AND `category`.`is_deleted` = 0 $parameters
                        LIMIT $limit OFFSET $offset";
    }else{
        $productQuery = "SELECT `products`.*,`category`.`cat_title`
                        FROM `products`
                        LEFT JOIN `category` ON `products`.`cat_id` = `category`.`cat_id`
                        WHERE `products`.`is_deleted` = 0 
                        AND `category`.`is_deleted` = 0 
                        ORDER BY `products`.`product_id` DESC
                        LIMIT $limit OFFSET $offset";
    }
    $productResult = mysqli_query($connect,$productQuery);
    if ($productResult) {
        foreach ($productResult as $key) {
            $products[] = $key;
        }
        http_response_code(200);
        echo json_encode([
            "success" => true,
            "message" => "Products",
            "data" => [
                "page" => $page,
                "limit" => $limit,
                "totalPages" => $totalPages,
                "totalProducts" => $totalProducts,
                "products" => $products
            ]
        ]);
    }else{
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Failed to fetch products, Try again later"
        ]);        
    }
}

// update product
function updateProduct($data,$id,$userRole){
    global $connect;
    header("Content-Type: application/json");

    // get the product info so that if the user doesn't provide all fields, we can use the existing ones
    $getQuery = "SELECT * FROM `products` WHERE `product_id` = '$id' AND `is_deleted` = 0";
    $getResult = mysqli_query($connect, $getQuery);
    $existingProduct = mysqli_fetch_assoc($getResult);
    if (!$existingProduct) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "Product not found"
        ]);
        return;
    }
    $pName = isset($data['name']) ? trim(mysqli_real_escape_string($connect, $data['name'])) : $existingProduct['product_name'];
    $pDesc= isset($data['description']) ? trim(mysqli_real_escape_string($connect, $data['description'])) : $existingProduct['product_desc'];
    $price = isset($data['price']) ? (float) $data['price'] : (float) $existingProduct['price'];
    $cat_id = isset($data['category_id']) ? (int) $data['category_id'] : (int) $existingProduct['cat_id'];
    $pImage = isset($data['image']) ? trim(mysqli_real_escape_string($connect, $data['image'])) : $existingProduct['product_img'];

    // validate price
    if (isset($data['price']) && (!is_numeric($price) || $price <= 0)) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Invalid product price"
        ]);
        return;
    }

    // if the category is deleted or not found
    if (isset($data['category_id'])) {  
        $catQuery = "SELECT * FROM `category` WHERE `cat_id` = '$cat_id' AND `is_deleted` = 0";
        $catResult = mysqli_query($connect,$catQuery);
        if (mysqli_num_rows($catResult) === 0) {
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "message" => "Invalid category ID"
            ]);
            return;
        }
    }
    // store the image in uploads dir
    if (isset($data['image']) && $pImage) {
        $imageData = base64_decode($pImage);
        $imageName = 'product_' . time() . '.png';
        $imagePath = __DIR__ . '/../uploads/' . $imageName;
        if (file_put_contents($imagePath, $imageData) === false) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Failed to save product image"
            ]);
            return;
        }
        $pImage = 'uploads/' . $imageName; // store relative path in DB
    } elseif (isset($data['image']) && !$pImage) {
        $pImage = null; // if image is explicitly set to null, remove it
    } else {
        $pImage = $existingProduct['product_img']; // keep existing image
    }
    $updateQuery = "UPDATE `products` 
                    SET `product_name` = '$pName',
                        `product_desc` = '$pDesc',
                           `price`     = '$price',
                           `cat_id`    = '$cat_id',
                        `product_img`  = '$pImage'
                    WHERE `product_id` = '$id' AND `is_deleted` = 0";
    $updateResult = mysqli_query($connect, $updateQuery);
    if ($updateResult) {
        http_response_code(200);
        echo json_encode([
            "success" => true,
            "message" => "product updated successfully",
            "data" => [
                "product_id" => $id,
                "product_name" => $pName,
                "product_desc" => $pDesc,
                "price" => $price,
                "category_id" => $cat_id,
                "image" => $pImage
            ]
        ]);
    }else{
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Failed to update product, Try again later"
        ]);
    }
}

// soft delete product 
function deleteProduct($id,$userRole){
    global $connect;
    header("Content-Type: application/json");

    // product exists
    $getQuery = "SELECT * FROM `products` WHERE `product_id` = '$id' AND `is_deleted` = 0";
    $getResult = mysqli_query($connect, $getQuery);
    if (mysqli_num_rows($getResult) === 0) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "Product not found"
        ]);
        return;
    }
    
    // soft delete
    $deleteQuery = "UPDATE `products` SET `is_deleted` = 1 WHERE `product_id` = '$id' AND `is_deleted` = 0";
    $deleteResult = mysqli_query($connect, $deleteQuery);
    if ($deleteResult) {
        http_response_code(200);
        echo json_encode([
            "success" => true,
            "message" => "Product soft deleted successfully",
            "data" => [
                "product_id" => $id
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Failed to delete product, Try again later"
        ]);
    }
}

// restore soft deleted product by action
function restoreProduct($id,$userRole){
    global $connect;
    header("Content-Type: application/json");

    // product exists and is deleted
    $getQuery = "SELECT * FROM `products` WHERE `product_id` = '$id' AND `is_deleted` = 1";
    $getResult = mysqli_query($connect, $getQuery);
    if (mysqli_num_rows($getResult) === 0) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "Product not found or not deleted"
        ]);
        return;
    }
    
    // restore
    $restoreQuery = "UPDATE `products` SET `is_deleted` = 0 WHERE `product_id` = '$id' AND `is_deleted` = 1";
    $restoreResult = mysqli_query($connect, $restoreQuery);
    if ($restoreResult) {
        http_response_code(200);
        echo json_encode([
            "success" => true,
            "message" => "Product restored successfully",
            "data" => [
                "product_id" => $id
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Failed to restore product, Try again later"
        ]);
    }
}
?>