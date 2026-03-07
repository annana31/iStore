<?php
// cart_remove.php
header('Content-Type: application/json');
session_start();
include "config.php";

function respond($status, $message = '') {
    echo json_encode(['status' => $status, 'message' => $message]);
    exit;
}

// Database connection check
if (!$conn) {
    respond('error', 'Database connection failed');
}

// Check login
$username = $_SESSION['username'] ?? null;
if (!$username) respond('error', 'Not logged in');

// Check POST id
if (!isset($_POST['id'])) respond('error', 'No ID provided');

$id = intval($_POST['id']);
if ($id <= 0) respond('error', 'Invalid ID');

// Delete item
$stmt = $conn->prepare("DELETE FROM cart_items WHERE id=? AND username=?");
if (!$stmt) respond('error', 'Database prepare failed');

$stmt->bind_param("is", $id, $username);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        respond('success');
    } else {
        respond('error', 'Item not found in cart');
    }
} else {
    respond('error', 'Database execution failed');
}

$stmt->close();
$conn->close();