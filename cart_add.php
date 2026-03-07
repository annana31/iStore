<?php
session_start();
include "config.php";

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

$username = $_SESSION['username'];

// Retrieve POST data safely
$product_name  = $_POST['product_name'] ?? '';
$product_price = $_POST['product_price'] ?? 0;
$quantity      = $_POST['quantity'] ?? 1;
$storage       = $_POST['storage'] ?? '';
$color         = $_POST['color'] ?? '';
$product_image = $_POST['product_image'] ?? null;

// Optional: Validate required fields
if (!$product_name || !$product_price || !$quantity) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required product info']);
    exit;
}

// Prepare SQL statement
$sql = "INSERT INTO cart_items 
        (username, product_name, product_price, quantity, storage, color, product_image, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "ssissss",
    $username,
    $product_name,
    $product_price,
    $quantity,
    $storage,
    $color,
    $product_image
);

// Execute and return JSON response
if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => $stmt->error]);
}

$stmt->close();
$conn->close();
?>