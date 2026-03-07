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
    $product_image = $_POST['product_image'] ?? null;



    $storage = $_POST['storage'] ?? null;
$color = $_POST['color'] ?? null;

$stmt = $conn->prepare("INSERT INTO cart_items (username, product_name, product_price, quantity, storage, color, product_image, status, added_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");
$stmt->bind_param("ssdisss", $username, $product_name, $product_price, $quantity, $storage, $color, $product_image);



    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $stmt->error]);
    }
    exit;
}

/* FETCH ALL PRODUCTS FOR STORE */
$store_products = [];
$result = $conn->query("SELECT * FROM admin_products ORDER BY id DESC"); // newest first
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()){
        $store_products[] = $row;
    }
}


// Map admin category to nav category
function normalizeCategory($cat) {
    $cat = strtolower(trim($cat)); // lowercase, trim spaces
    $map = [
        'iphone' => 'phones',
        'iphones' => 'phones',
        'phone' => 'phones',
        'ipad' => 'ipads',
        'macbook' => 'macs',
        'macs' => 'macs',
        'watch' => 'watches',
        'apple watch' => 'watches',
        'accessory' => 'accessories',
        'accessories' => 'accessories',
    ];
    return $map[$cat] ?? $cat; // fallback to original if not in map
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
            <p>₱ 30,999</p>
         <button type="button" class="view-item-btn">View Item</button>
        </div>
        <div class="product" data-category="phones">
            <img src="assets/iphone14.png" alt="iPhone 14" />
            <h3>iPhone 14</h3>
            <p>₱ 27,199</p>
            <button type="button" class="view-item-btn">View Item</button>
        </div>
        <div class="product" data-category="phones">
            <img src="assets/iphonese.png" alt="iPhone SE" />
            <h3>iPhone SE</h3>
            <p>₱ 14,829</p>
           <button type="button" class="view-item-btn">View Item</button>
        </div>
        <div class="product" data-category="phones">
            <img src="assets/iphone17pro.png" alt="iPhone 17 Pro" />
            <h3>iPhone 17 Pro</h3>
            <p>₱ 51,199</p>
            <button type="button" class="view-item-btn">View Item</button>
        </div>
        <div class="product" data-category="phones">
            <img src="assets/iphone16.png" alt="iPhone 16" />
            <h3>iPhone 16</h3>
            <p>₱ 43,090</p>
          <button type="button" class="view-item-btn">View Item</button>
        </div>

        <!-- IPADS -->
        <div class="product" data-category="ipads">
            <img src="assets/ipadpro12.png" alt="iPad Pro 12.9" />
            <h3>iPad Pro</h3>
            <p>₱ 30,099</p>
           <button type="button" class="view-item-btn">View Item</button>
        </div>
        <div class="product" data-category="ipads">
            <img src="assets/ipadair.png" alt="iPad Air" />
            <h3>iPad Air</h3>
            <p>₱ 27,999</p>
            <button type="button" class="view-item-btn">View Item</button>
        </div>
        <div class="product" data-category="ipads">
            <img src="assets/ipadairpurple.png" alt="iPad Mini" />
            <h3>iPad Mini</h3>
            <p>₱ 15,149</p>
           <button type="button" class="view-item-btn">View Item</button>
        </div>
        <div class="product" data-category="ipads">
            <img src="assets/ipadmini.png" alt="iPad Mini" />
            <h3>iPad Mini</h3>
            <p>₱ 15,260</p>
            <button type="button" class="view-item-btn">View Item</button>
        </div>
        <div class="product" data-category="ipads">
            <img src="assets/ipadpro11.png" alt="iPad" />
            <h3>iPad</h3>
            <p>₱ 10,499</p>
            <button type="button" class="view-item-btn">View Item</button>
        </div>

        <!-- MACS -->
        <div class="product" data-category="macs">
            <img src="assets/macbookair.png" alt="MacBook Air" />
            <h3>MacBook Air</h3>
            <p>₱ 29,999</p>
          <button type="button" class="view-item-btn">View Item</button>
        </div>
        <div class="product" data-category="macs">
            <img src="assets/macbookpro.png" alt="MacBook Pro" />
            <h3>MacBook Pro</h3>
            <p>₱ 21,010</p>
            <button type="button" class="view-item-btn">View Item</button>
        </div>
        <div class="product" data-category="macs">
            <img src="assets/macbookpro14.png" alt="MacBook Pro 14" />
            <h3>MacBook Pro 14</h3>
            <p>₱ 31,999</p>
           <button type="button" class="view-item-btn">View Item</button>
        </div>
        <div class="product" data-category="macs">
            <img src="assets/macbookairm4.png" alt="Macbook Air M4" />
            <h3>Macbook Air M4</h3>
            <p>₱ 33,050</p>
            <button type="button" class="view-item-btn">View Item</button>
        </div>
        <div class="product" data-category="macs">
            <img src="assets/imac24.png" alt="iMac 24" />
            <h3>iMac 24</h3>
            <p>₱ 27,299</p>
            <button type="button" class="view-item-btn">View Item</button>
        </div>

        <!-- WATCHES -->
        <div class="product" data-category="watches">
            <img src="assets/applewatch8.png" alt="Apple Watch Series 8" />
            <h3>Apple Watch Series 8</h3>
            <p>₱ 2,399</p>
            <button type="button" class="view-item-btn">View Item</button>
        </div>
        <div class="product" data-category="watches">
            <img src="assets/applewatchultra.png" alt="Apple Watch Ultra" />
            <h3>Apple Watch Ultra</h3>
            <p>₱ 3,799</p>
         <button type="button" class="view-item-btn">View Item</button>
        </div>
        <div class="product" data-category="watches">
            <img src="assets/applewatch10.png" alt="Apple Watch 10" />
            <h3>Apple Watch 10</h3>
            <p>₱ 3,599</p>
          <button type="button" class="view-item-btn">View Item</button>
        </div>
        <div class="product" data-category="watches">
            <img src="assets/applewatchair.png" alt="Apple Watch Air" />
            <h3>Apple Watch Air</h3>
            <p>₱ 4,599</p>
          <button type="button" class="view-item-btn">View Item</button>
        </div>
        <div class="product" data-category="watches">
            <img src="assets/applewatchh.png" alt="Apple Watch " />
            <h3>Apple Watch </h3>
            <p>₱ 3,562</p>
           <button type="button" class="view-item-btn">View Item</button>
        </div>

        <!-- ACCESSORIES -->
        <div class="product" data-category="accessories">
            <img src="assets/airpodspro.png" alt="AirPods Pro" />
            <h3>AirPods Pro</h3>
            <p>₱ 2,249</p>
          <button type="button" class="view-item-btn">View Item</button>
        </div>
        <div class="product" data-category="accessories">
            <img src="assets/magicmouse.png" alt="Magic Mouse" />
            <h3>Magic Mouse</h3>
            <p>₱ 999</p>
           <button type="button" class="view-item-btn">View Item</button>
        </div>
        <div class="product" data-category="accessories">
            <img src="assets/magickeyboard.png" alt="Magic Keyboard" />
            <h3>Magic Keyboard</h3>
            <p>₱ 849</p>
            <button type="button" class="view-item-btn">View Item</button>
        </div>
        <div class="product" data-category="accessories">
            <img src="assets/applepencil.png" alt="Apple Pencil 2nd Gen" />
            <h3>Apple Pencil 2nd Gen</h3>
            <p>₱ 1,129</p>
           <button type="button" class="view-item-btn">View Item</button>
        </div>
        <div class="product" data-category="accessories">
            <img src="assets/airpodsmax.png" alt="AirPods Max" />
            <h3>AirPods Max</h3>
            <p>₱ 1,487</p>
            <button type="button" class="view-item-btn">View Item</button>
        </div>

        <!-- ADMIN PRODUCTS -->
    <?php if(!empty($store_products)): ?>
    <?php foreach($store_products as $product): ?>
    <?php $category = normalizeCategory($product['category']); ?>
    <div class="product" data-category="<?= htmlspecialchars($category) ?>">
        <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" />
        <h3><?= htmlspecialchars($product['name']) ?></h3>
        <p>₱<?= number_format($product['price']) ?></p>
        <button type="button" class="view-item-btn"
            data-id="<?= $product['id'] ?>"
            data-name="<?= htmlspecialchars($product['name']) ?>"
            data-price="<?= $product['price'] ?>"
            data-image="<?= htmlspecialchars($product['image']) ?>"
            data-category="<?= htmlspecialchars($category) ?>"
        >
            View Item
        </button>
    </div>
<?php endforeach; ?>
<?php endif; ?>
    </div>
</div> <!-- end of #product-list -->

</main>

<script>
const navItems = document.querySelectorAll('nav ul li');

navItems.forEach(nav => {
    nav.addEventListener('click', () => {
        navItems.forEach(n => n.classList.remove('active'));
        nav.classList.add('active');

        const category = nav.dataset.category;
        const products = document.querySelectorAll('#product-list .product'); // re-select every time

        products.forEach(prod => {
            if (category === 'all' || prod.dataset.category === category) {
                prod.style.display = 'flex'; // flex to match layout
            } else {
                prod.style.display = 'none';
            }
        });
    });
});
</script>

<!-- Notification container -->
<div id="notification-container"></div>
<!-- ================= PREMIUM APPLE STYLE PANEL ================= -->

<div class="side-panel" id="sidePanel">
    <button class="close-btn" onclick="closePanel()">✕</button>

    <div class="product-image-container">
        <img id="panelImage" src="" alt="Product Image">
    </div>

    <h2 id="panelName">iPhone 14 Pro</h2>
    <div class="price" id="panelPrice">$999</div>

    <label id="quantityLabel">Quantity</label>
    <input type="number" value="1" min="1">

    <div id="storageContainer">
        <label for="storageSelect">Storage</label>
        <select id="storageSelect">
            <option value="128GB">128GB</option>
            <option value="256GB">256GB</option>
            <option value="512GB">512GB</option>
            <option value="1TB">1TB</option>
        </select>
    </div>

    <div id="colorContainer">
        <label for="colorSelect">Color</label>
        <select id="colorSelect">
            <!-- Will be filled dynamically -->
        </select>
    </div>

    <button class="add-to-cart">Add to Cart</button>
    
</div>

<script src="script.js"></script>



</body>
</html>