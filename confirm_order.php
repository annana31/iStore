<?php
session_start();
include "config.php"; // your DB connection

if(!isset($_POST['id'])){
    exit('error');
}

$order_id = intval($_POST['id']);

// Update status to Confirmed
$stmt = $conn->prepare("UPDATE admin_orders SET status='Confirmed' WHERE id=?");
$stmt->bind_param("i", $order_id);

if($stmt->execute()){
    echo "success"; // AJAX will handle UI
}else{
    echo "error";
}
?>