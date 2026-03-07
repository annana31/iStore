<?php
include "check_session.php";

// Database connection
$host = "localhost";
$dbname = "store_db";
$user = "root";
$pass = "";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the username from session
$username = $_SESSION['username'] ?? 'Guest';

// Fetch cart items, include static or admin-added products
$sql = "
    SELECT 
        c.id, 
        c.product_name, 
        c.product_price, 
        c.quantity, 
        c.storage, 
        c.color, 
        c.product_image, 
        p.image AS admin_image
    FROM cart_items c
    LEFT JOIN admin_products p 
        ON TRIM(LOWER(c.product_name)) = TRIM(LOWER(p.name))
    WHERE c.username = ? AND c.status = 'Pending'
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();

$result = $stmt->get_result();

$cart_items = [];
while ($row = $result->fetch_assoc()) {
    $cart_items[] = [
        'id' => $row['id'],
        'product_name' => $row['product_name'],
        'product_price' => $row['product_price'],
        'quantity' => $row['quantity'],
        'storage' => $row['storage'],
        'color' => $row['color'],
        'uploaded_image' => $row['admin_image'],   // admin-added image
        'product_image' => $row['product_image']  // old static image
    ];
}

// Return JSON
header('Content-Type: application/json');
echo json_encode($cart_items);

// Close connections
$stmt->close();
$conn->close();
?>