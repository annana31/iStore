<?php
include "check_session.php";

$host = "localhost";
$dbname = "store_db";
$user = "root";
$pass = "";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$username = $_SESSION['username'] ?? 'Guest';

$discountQuery = $conn->prepare("SELECT has_used_discount FROM users WHERE username = ?");
$discountQuery->bind_param("s", $username);
$discountQuery->execute();
$userData = $discountQuery->get_result()->fetch_assoc();
$hasDiscount = $userData['has_used_discount'] ?? 0;

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

    $price = $row['product_price'];

   
    if ($hasDiscount == 1) {
        $discountedPrice = round($price * 0.7, 2);
        $row['original_price'] = $price; 
        $price = $discountedPrice;
    }

    $cart_items[] = [
        'id' => $row['id'],
        'product_name' => $row['product_name'],
        'product_price' => $price,
        'quantity' => $row['quantity'],
        'storage' => $row['storage'],
        'color' => $row['color'],
        'product_image' => $row['product_image'] 
    ];
}


header('Content-Type: application/json');
echo json_encode($cart_items);


$stmt->close();
$conn->close();
$discountQuery->close();
?>