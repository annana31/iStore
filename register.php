<?php
session_start();
include "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = $_POST["username"];
    $password = $_POST["password"];
    $confirmPassword = $_POST["confirmPassword"];

    if(empty($username) || empty($password) || empty($confirmPassword)){
        echo "Please fill all fields.";
        exit;
    }

    if($password !== $confirmPassword){
        echo "Passwords do not match.";
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $check = $conn->prepare("SELECT id FROM users WHERE username=?");
    $check->bind_param("s", $username);
    $check->execute();
    $check->store_result();

    if($check->num_rows > 0){
        echo "Username already exists.";
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO users (username,password) VALUES (?,?)");
    $stmt->bind_param("ss", $username, $hashedPassword);

    if($stmt->execute()){

        $_SESSION["username"] = $username;

        echo "success";

    }else{

        echo "Error registering user.";

    }

}
?>
