<?php
session_start();

// Database connection
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "store_db";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("connection_error");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {

        $user = $result->fetch_assoc();

        // ✅ VERIFY HASHED PASSWORD
        if (password_verify($password, $user["password"])) {

            // ✅ CHECK IF ADMIN
            if ($user["role"] === "admin") {

                $_SESSION["admin_id"] = $user["id"];
                $_SESSION["admin_username"] = $user["username"];
                $_SESSION["role"] = $user["role"];

                echo "success";

            } else {
                echo "not_admin";
            }

        } else {
            echo "invalid";
        }

    } else {
        echo "invalid";
    }

    $stmt->close();
    $conn->close();
}
?>
