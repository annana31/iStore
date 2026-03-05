<?php
session_start();
if (!isset($_SESSION["admin_id"]) || $_SESSION["role"] !== "admin") {
    exit("unauthorized");
}

if(isset($_POST['id'])) {
    $order_id = intval($_POST['id']);
    $conn = new mysqli("localhost", "root", "", "store_db");
    if ($conn->connect_error) { exit("db_error"); }

    $stmt = $conn->prepare("DELETE FROM cart_items WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();

    echo $success ? "success" : "failed";
} else {
    echo "no_id";
}
?>