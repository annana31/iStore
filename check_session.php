<?php
session_start();

if (!isset($_SESSION['username'])) {
    // Redirect to login if not logged in
    header("Location: login.php");
    exit;
}

// $_SESSION['username'] is now available for store.php
?>
