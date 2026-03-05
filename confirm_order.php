<?php
include 'config.php'; // your DB connection

if(isset($_POST['id'])){
    $id = intval($_POST['id']); // sanitize input

    $stmt = $conn->prepare("UPDATE cart_items SET status = 'Confirmed' WHERE id = ?");
    $stmt->bind_param("i", $id);

    if($stmt->execute()){
        echo 'success';
    } else {
        echo 'error';
    }
}
?>