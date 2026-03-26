<?php
session_start();
include "config.php";

$username = $_SESSION['username'] ?? null;
if (!$username) { 
    exit(json_encode(['status'=>'error','message'=>'Not logged in'])); 
}

$user_res = $conn->query("SELECT has_used_discount FROM users WHERE username='$username'");
if(!$user_res || $user_res->num_rows == 0){
    exit(json_encode(['status'=>'error','message'=>'User not found']));
}

$user_data = $user_res->fetch_assoc();
$has_discount = $user_data['has_used_discount'] ?? 1;


$cart_res = $conn->query("SELECT * FROM cart_items WHERE username='$username'");
if($cart_res->num_rows == 0){
    exit(json_encode(['status'=>'error','message'=>'Cart is empty']));
}

while($item = $cart_res->fetch_assoc()){
    $stmt = $conn->prepare("
        INSERT INTO admin_orders 
        (username, total, status, product_name, quantity, storage, color) 
        VALUES (?, ?, 'Pending', ?, ?, ?, ?)
    ");
    if(!$stmt){
        error_log("Prepare failed: ".$conn->error);
        exit(json_encode(['status'=>'error','message'=>'Server error']));
    }

    $price_per_item = $item['product_price'];
    if($has_discount == 1){
        $price_per_item = round($price_per_item * 0.7, 2);
    }
    $total_item = $price_per_item * $item['quantity'];

    $stmt->bind_param("sdsiss", 
        $username,
        $total_item,
        $item['product_name'],
        $item['quantity'],
        $item['storage'],
        $item['color']
    );

    if(!$stmt->execute()){
        error_log("Execute failed: ".$stmt->error);
        exit(json_encode(['status'=>'error','message'=>'Server error']));
    }
}

$del_res = $conn->query("DELETE FROM cart_items WHERE username='$username'");
if(!$del_res){
    error_log("Cart delete failed: ".$conn->error);
    exit(json_encode(['status'=>'error','message'=>'Server error']));
}

if($has_discount == 1){
    $upd_res = $conn->query("UPDATE users SET has_used_discount = 0, is_new_user = 0 WHERE username='$username'");
    if(!$upd_res){
        error_log("Failed to update discount status: ".$conn->error);
    }
}

echo json_encode(['status'=>'success']);
?>