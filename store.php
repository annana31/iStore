<?php 
include "check_session.php";

// DB connection
$host = "localhost";
$dbname = "store_db";
$user = "root"; 
$pass = "";     

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_to_cart') {
    $product_name = $_POST['product_name'] ?? '';
    $product_price = $_POST['product_price'] ?? 0;
    $quantity = $_POST['quantity'] ?? 1;
    $username = $_SESSION['username'] ?? 'Guest';


    $stmt = $conn->prepare("INSERT INTO cart_items (username, product_name, product_price, quantity) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssdi", $username, $product_name, $product_price, $quantity);


    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $stmt->error]);
    }
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>iStore - Apple Products</title>
    <link rel="stylesheet" href="style.css" />
    <style>
        /* =========================
           CENTERED MONOCHROME NOTIFICATION
        ========================== */
        #notification-container {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 9999;
            pointer-events: none;
        }

        .notification {
            background-color: #111;
            color: #fff;
            padding: 14px 28px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.25);
            opacity: 0;
            transform: translateY(-20px);
            transition: opacity 0.4s ease, transform 0.4s ease;
            font-size: 14px;
            max-width: 280px;
            text-align: center;
            pointer-events: auto;
            user-select: none;
            margin: 0 auto;
        }

        .notification.show {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>
<body>

<header>
    <h1>iStore</h1>

    <div class="nav-right">
        <!-- CART -->
        <span class="cart" id="cart-icon">
            Cart <span class="count">0</span>
        </span>

        <!-- ACCOUNT DROPDOWN -->
        <div class="profile-dropdown" id="profile-dropdown">
            <span class="account-btn">Account ▾</span>
            <div class="dropdown-menu" id="dropdown-menu">
                <button id="logout-btn">Logout</button>
            </div>
        </div>
    </div>
</header>

<nav>
    <ul>
        <li class="active" data-category="all">Home</li>
        <li data-category="phones">Phones</li>
        <li data-category="ipads">iPads</li>
        <li data-category="macs">Macs</li>
        <li data-category="watches">Watches</li>
        <li data-category="accessories">Accessories</li>
    </ul>
    <input type="search" placeholder="Search Products" />
</nav>

<main>
    <img src="assets/banner.png" alt="Flash Sale Banner" class="banner" />

    <div class="products" id="product-list">
        <!-- PHONES -->
        <div class="product" data-category="phones">
            <img src="assets/iphone14pro.png" alt="iPhone 14 Pro" />
            <h3>iPhone 14 Pro</h3>
            <p>$999</p>
            <button>Add to Cart</button>
        </div>
        <div class="product" data-category="phones">
            <img src="assets/iphone14.png" alt="iPhone 14" />
            <h3>iPhone 14</h3>
            <p>$799</p>
            <button>Add to Cart</button>
        </div>
        <div class="product" data-category="phones">
            <img src="assets/iphonese.png" alt="iPhone SE" />
            <h3>iPhone SE</h3>
            <p>$429</p>
            <button>Add to Cart</button>
        </div>
        <div class="product" data-category="phones">
            <img src="assets/iphone17pro.png" alt="iPhone 17 Pro" />
            <h3>iPhone 17 Pro</h3>
            <p>$1199</p>
            <button>Add to Cart</button>
        </div>
        <div class="product" data-category="phones">
            <img src="assets/iphone16.png" alt="iPhone 16" />
            <h3>iPhone 16</h3>
            <p>$1090</p>
            <button>Add to Cart</button>
        </div>

        <!-- IPADS -->
        <div class="product" data-category="ipads">
            <img src="assets/ipadpro12.png" alt="iPad Pro 12.9" />
            <h3>iPad Pro</h3>
            <p>$1099</p>
            <button>Add to Cart</button>
        </div>
        <div class="product" data-category="ipads">
            <img src="assets/ipadair.png" alt="iPad Air" />
            <h3>iPad Air</h3>
            <p>$799</p>
            <button>Add to Cart</button>
        </div>
        <div class="product" data-category="ipads">
            <img src="assets/ipadairpurple.png" alt="iPad Mini" />
            <h3>iPad Mini</h3>
            <p>$549</p>
            <button>Add to Cart</button>
        </div>
        <div class="product" data-category="ipads">
            <img src="assets/ipadmini.png" alt="iPad Mini" />
            <h3>iPad Mini</h3>
            <p>$560</p>
            <button>Add to Cart</button>
        </div>
        <div class="product" data-category="ipads">
            <img src="assets/ipadpro11.png" alt="iPad" />
            <h3>iPad</h3>
            <p>$499</p>
            <button>Add to Cart</button>
        </div>

        <!-- MACS -->
        <div class="product" data-category="macs">
            <img src="assets/macbookair.png" alt="MacBook Air" />
            <h3>MacBook Air</h3>
            <p>$999</p>
            <button>Add to Cart</button>
        </div>
        <div class="product" data-category="macs">
            <img src="assets/macbookpro.png" alt="MacBook Pro" />
            <h3>MacBook Pro</h3>
            <p>$1010</p>
            <button>Add to Cart</button>
        </div>
        <div class="product" data-category="macs">
            <img src="assets/macbookpro14.png" alt="MacBook Pro 14" />
            <h3>MacBook Pro 14</h3>
            <p>$1999</p>
            <button>Add to Cart</button>
        </div>
        <div class="product" data-category="macs">
            <img src="assets/macbookairm4.png" alt="Macbook Air M4" />
            <h3>Macbook Air M4</h3>
            <p>$1050</p>
            <button>Add to Cart</button>
        </div>
        <div class="product" data-category="macs">
            <img src="assets/imac24.png" alt="iMac 24" />
            <h3>iMac 24</h3>
            <p>$1299</p>
            <button>Add to Cart</button>
        </div>

        <!-- WATCHES -->
        <div class="product" data-category="watches">
            <img src="assets/applewatch8.png" alt="Apple Watch Series 8" />
            <h3>Apple Watch Series 8</h3>
            <p>$399</p>
            <button>Add to Cart</button>
        </div>
        <div class="product" data-category="watches">
            <img src="assets/applewatchultra.png" alt="Apple Watch Ultra" />
            <h3>Apple Watch Ultra</h3>
            <p>$799</p>
            <button>Add to Cart</button>
        </div>
        <div class="product" data-category="watches">
            <img src="assets/applewatch10.png" alt="Apple Watch 10" />
            <h3>Apple Watch 10</h3>
            <p>$599</p>
            <button>Add to Cart</button>
        </div>
        <div class="product" data-category="watches">
            <img src="assets/applewatchair.png" alt="Apple Watch Air" />
            <h3>Apple Watch Air</h3>
            <p>$599</p>
            <button>Add to Cart</button>
        </div>
        <div class="product" data-category="watches">
            <img src="assets/applewatchh.png" alt="Apple Watch H" />
            <h3>Apple Watch H</h3>
            <p>$562</p>
            <button>Add to Cart</button>
        </div>

        <!-- ACCESSORIES -->
        <div class="product" data-category="accessories">
            <img src="assets/airpodspro.png" alt="AirPods Pro" />
            <h3>AirPods Pro</h3>
            <p>$249</p>
            <button>Add to Cart</button>
        </div>
        <div class="product" data-category="accessories">
            <img src="assets/magicmouse.png" alt="Magic Mouse" />
            <h3>Magic Mouse</h3>
            <p>$99</p>
            <button>Add to Cart</button>
        </div>
        <div class="product" data-category="accessories">
            <img src="assets/magickeyboard.png" alt="Magic Keyboard" />
            <h3>Magic Keyboard</h3>
            <p>$149</p>
            <button>Add to Cart</button>
        </div>
        <div class="product" data-category="accessories">
            <img src="assets/applepencil.png" alt="Apple Pencil 2nd Gen" />
            <h3>Apple Pencil 2nd Gen</h3>
            <p>$129</p>
            <button>Add to Cart</button>
        </div>
        <div class="product" data-category="accessories">
            <img src="assets/airpodsmax.png" alt="AirPods Max" />
            <h3>AirPods Max</h3>
            <p>$487</p>
            <button>Add to Cart</button>
        </div>
    </div>
</main>

<!-- Notification container -->
<div id="notification-container"></div>

<script src="script.js"></script>


</body>
</html>