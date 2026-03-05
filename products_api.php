<?php
session_start();

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "store_db";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("error");
}

if (!isset($_SESSION["admin_id"])) {
    die("unauthorized");
}

$action = $_POST["action"] ?? '';


// ================== FETCH PRODUCTS ==================
if ($action === "fetch") {
    $result = $conn->query("SELECT * FROM products ORDER BY id DESC");
    $products = [];

    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }

    echo json_encode($products);
    exit();
}


// ================== ADD PRODUCT ==================
if ($action === "add") {

    $name = $_POST["name"];
    $price = $_POST["price"];
    $category = $_POST["category"];

    $imageName = "default.png";

    if (isset($_FILES["image"]) && $_FILES["image"]["error"] === 0) {
        $imageName = time() . "_" . $_FILES["image"]["name"];
        move_uploaded_file($_FILES["image"]["tmp_name"], "uploads/" . $imageName);
    }

    $stmt = $conn->prepare("INSERT INTO products (name, price, category, image) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sdss", $name, $price, $category, $imageName);
    $stmt->execute();

    echo "success";
    exit();
}


// ================== DELETE ==================
if ($action === "delete") {

    $id = $_POST["id"];

    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    echo "success";
    exit();
}


// ================== UPDATE ==================
if ($action === "update") {

    $id = $_POST["id"];
    $name = $_POST["name"];
    $price = $_POST["price"];
    $category = $_POST["category"];

    $stmt = $conn->prepare("UPDATE products SET name=?, price=?, category=? WHERE id=?");
    $stmt->bind_param("sdsi", $name, $price, $category, $id);
    $stmt->execute();

    echo "success";
    exit();
}

$conn->close();
?>
