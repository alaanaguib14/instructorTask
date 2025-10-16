<?php
include_once "../config/connection.php";

// add category
function addCategory($data,$userRole){
    global $connect;

    header("Content-Type: application/json");

    $catTitle = trim(mysqli_real_escape_string($connect,$data['title']));
    $catDesc  = trim(mysqli_real_escape_string($connect,$data['description']));

    // validate missing fields
    if(empty($catTitle) || empty($catDesc)){
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Category title, and description is required"
        ]);
        return;
    }

    // insert category
    $insertQuery = "INSERT INTO `category` (`cat_title`,`cat_description`) 
                    VALUES ('$catTitle','$catDesc')";
    $insertResult = mysqli_query($connect,$insertQuery);
    if ($insertResult) {
        http_response_code(201);
        echo json_encode([
           "success"=> true,
           "message"=> "category added successfully",
           "data"=> [
                "category_title" => $catTitle,
                "category_description" => $catDesc
           ]
        ]);
    }else{
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Failed to add category, Try again later"
        ]);
    }
}

// get categories
function getCategories($userRole, $id = null){
    global $connect;
    
    header("Content-Type: application/json");
    $categories = [];

    if ($id !== null && is_int($id)){
        $catQuery = "SELECT * FROM `category` WHERE `cat_id` = '$id' AND `is_deleted` = 0";
        $catResult = mysqli_query($connect,$catQuery);
        if ($catResult && mysqli_num_rows($catResult) > 0) {
            $category = mysqli_fetch_assoc($catResult);
            http_response_code(200);
            echo json_encode([
                "success" => true,
                "message" => "Category fetched successfully",
                "data" => $category
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                "success" => false,
                "message" => "Category not found"
            ]);
        }
    }else{
        $catQuery = "SELECT * FROM `category` WHERE `is_deleted` = 0";
        $catResult = mysqli_query($connect,$catQuery);
        if ($catResult) {
            foreach ($catResult as $key) {
                $categories[] = $key;
            }
            http_response_code(200);
            echo json_encode([
                "success" => true,
                "message" => "Categories fetched successfully",
                "data" => $categories
            ]);
        }else{
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Failed to fetch categories, Try again later"
            ]);
        }
    }
    
}

// update Category 
function updateCategory($data,$id,$userRole) {
    global $connect;
    header("Content-Type: application/json");

    // get the category info so that if the user doesn't provide all fields, we can use the existing ones
    $getCategories = "SELECT * FROM `category` WHERE `cat_id` = '$id' AND `is_deleted` = 0";
    $getResults = mysqli_query($connect,$getCategories);
    $existingCategory = mysqli_fetch_assoc($getResults);
    if (!$existingCategory) {
        http_response_code(404);
        echo json_encode([
            "success"=> false,
            "message"=>"Category not found"
        ]);
        return;
    }
    $catTitle = isset($data['title']) ? trim(mysqli_real_escape_string($connect,$data['title'])):$existingCategory['cat_title'];
    $catDesc = isset($data['description']) ? trim(mysqli_real_escape_string($connect,$data['description'])):$existingCategory['cat_description'];

    $updateQuery = "UPDATE `category`
                    SET `cat_title` = '$catTitle',
                        `cat_description`  = '$catDesc'
                    WHERE `cat_id` = '$id' AND `is_deleted` = 0";
    $updateResult = mysqli_query($connect,$updateQuery);
    if ($updateResult) {
        http_response_code(200);
        echo json_encode([
            "success" => true,
            "message" => "Category updated successfully",
            "data" => [
                "category_id" => $id,
                "category_title" => $catTitle,
                "category_description" => $catDesc
            ]
        ]) ;
    }else{
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Failed to update category, Try again later"
        ]);
    }
}

// soft delete category
function deleteCategory($id,$userRole){
    global $connect;
    header("Content-Type: application/json");

    // category exists?
    $getQuery = "SELECT * FROM `category` WHERE `cat_id` = '$id' AND `is_deleted` = 0";
    $getResult = mysqli_query($connect,$getQuery);
    if (mysqli_num_rows($getResult) === 0) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "Category not found"
        ]);
        return;
    }

    // soft delete
    $deleteQuery = "UPDATE `category` SET `is_deleted` = 1 WHERE `cat_id` = '$id'";
    $deleteResult = mysqli_query($connect,$deleteQuery);
    if ($deleteResult) {
        http_response_code(200);
        echo json_encode([
            "success" => true,
            "message" => "Category deleted successfully",
            "data" => [
                "category_id" => $id
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Failed to delete category, Try again later"
        ]);
    }
}
// restore deleted category
function restoreCategory($id,$userRole){
    global $connect;
    header("Content-Type: application/json");

    // category exists?
    $getQuery = "SELECT * FROM `category` WHERE `cat_id` = '$id' AND `is_deleted` = 1";
    $getResult = mysqli_query($connect,$getQuery);
    if (mysqli_num_rows($getResult) === 0) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "Category not found or not deleted"
        ]);
        return;
    }

    // restore
    $restoreQuery = "UPDATE `category` SET `is_deleted` = 0 WHERE `cat_id` = '$id'";
    $restoreResult = mysqli_query($connect,$restoreQuery);
    if ($restoreResult) {
        http_response_code(200);
        echo json_encode([
            "success" => true,
            "message" => "Category restored successfully",
            "data" => [
                "category_id" => $id
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Failed to restore category, Try again later"
        ]);
    }
}
?>