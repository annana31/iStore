<?php
session_start();
include "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = $_POST["username"];
    $password = $_POST["password"];

    if(empty($username) || empty($password)){
        echo "Please fill all fields.";
        exit;
    }

    $stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {

        $stmt->bind_result($hashedPassword);
        $stmt->fetch();

        if (password_verify($password, $hashedPassword)) {

            $_SESSION["username"] = $username;

            echo "success";

        } else {

            echo "Invalid password.";

        }

    } else {

        echo "Username not found.";

    }

}
?>
