<?php
session_start();
include "config.php";

$username = $_SESSION['username'];

$stmt = $conn->prepare("UPDATE users SET has_used_discount = 1, is_new_user = 0 WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();

echo json_encode(["status" => "success"]);