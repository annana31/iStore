<?php
session_start();
if(!isset($_SESSION["admin_id"]) || $_SESSION["role"] !== "admin") exit('Unauthorized');

$conn = new mysqli("localhost","root","","store_db");
if($conn->connect_error) die("Connection failed: ".$conn->connect_error);

if(isset($_POST['edit_id'])){
    $id = $_POST['edit_id'];
    $name = $_POST['name'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $imagePath = "";

    if(isset($_FILES['image']) && $_FILES['image']['name'] != ''){
        $imageName = time() . "_" . $_FILES['image']['name'];
        $tmpName = $_FILES['image']['tmp_name'];
        $uploadDir = "assets/";
        $imagePath = $uploadDir . $imageName;
        move_uploaded_file($tmpName, $imagePath);
    }

    if($imagePath != ""){
        $stmt = $conn->prepare("UPDATE admin_products SET name=?, category=?, price=?, image=? WHERE id=?");
        $stmt->bind_param("ssdsi",$name,$category,$price,$imagePath,$id);
    } else {
        $stmt = $conn->prepare("UPDATE admin_products SET name=?, category=?, price=? WHERE id=?");
        $stmt->bind_param("ssdi",$name,$category,$price,$id);
    }

    $stmt->execute();
    echo "success";
}
?>