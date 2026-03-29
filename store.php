<?php 
include "check_session.php";

$host = "localhost";
$dbname = "store_db";
$user = "root"; 
$pass = "";     

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$username = $_SESSION['username'] ?? null;

if (!$username) {
    header("Location: login.php");
    exit;
}

$userQuery = $conn->prepare("SELECT is_new_user, has_used_discount FROM users WHERE username = ?");
$userQuery->bind_param("s", $username);
$userQuery->execute();
$userResult = $userQuery->get_result();
$userData = $userResult->fetch_assoc();

$isNewUser = $userData['is_new_user'] ?? 0;
$hasUsedDiscount = $userData['has_used_discount'] ?? 0;

$showPopup = ($isNewUser == 1 && $hasUsedDiscount == 0);

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

$store_products = [];
$result = $conn->query("SELECT * FROM admin_products ORDER BY id DESC"); // newest first
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()){
        $store_products[] = $row;
    }
}



function normalizeCategory($cat) {
    $cat = strtolower(trim($cat)); 
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
    return $map[$cat] ?? $cat; 
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
.popup {
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.5);
    display: flex; justify-content: center; align-items: center;
    z-index: 10000;
    backdrop-filter: blur(5px);
}

.popup-content {
    background: #fff;
    padding: 45px 35px;
    border-radius: 24px;
    width: 400px;
    max-width: 90%;
    text-align: center;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    box-shadow: 0 20px 50px rgba(0,0,0,0.3); /* popup shadow */
    display: flex; flex-direction: column; gap: 18px;
    overflow: hidden;
    position: relative;
}

.popup-content h2 {
    font-size: 36px; /* bigger */
    font-weight: 700;
    color: #111;
    margin: 0;
    text-align: center;
}

.popup-content p:first-of-type {
    font-size: 20px;
    color: #555;
    margin: 10px 0;
    text-align: center;
}

.popup-content p:nth-of-type(2) {
    font-size: 48px; /* make 30% OFF big */
    font-weight: 800;
    color: #e74c3c;
    margin: 10px 0;
    text-align: center;
}

.popup-content .popup-emoji {
    font-size: 32px;
    margin: 10px 0;
    text-align: center;
}
.popup-content button {
    margin-top: 20px;
    padding: 14px 30px;
    font-size: 16px;
    font-weight: 600;
    border: none;
    border-radius: 12px;
    background: #000;
    color: #fff;
    cursor: pointer;
    transition: background 0.3s ease, transform 0.2s ease;
}
.popup-content p.popup-message {
    font-size: 18px;
    color: #555;
    margin: 10px 0 0 0;
    text-align: center;
    font-weight: 500;
}
.popup-content button:hover {
    background: #333;
    transform: translateY(-2px);
}
#confettiContainerFull {
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    pointer-events: none; /* clicks pass through */
    overflow: hidden;
    z-index: 9999; /* below popup (popup is 10000) */
}

.confetti-full {
    position: absolute;
    width: 8px;
    height: 8px;
    opacity: 0.8;
    background-color: red; /* will be randomized in JS */
    top: -10px;
    transform: rotate(0deg);
    border-radius: 2px;
    animation-name: fall;
    animation-timing-function: linear;
}

@keyframes fall {
    0% {
        transform: translateY(0) rotate(0deg);
        opacity: 1;
    }
    100% {
        transform: translateY(110vh) rotate(360deg);
        opacity: 0;
    }
}
#discountPopup {
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 10000; 
    background: rgba(255, 255, 255, 0.05); 
    backdrop-filter: none; 
}
    </style>
</head>
<body>

<div id="confettiContainerFull"></div>
<?php if ($showPopup): ?>
<div id="discountPopup" class="popup">
    <div class="popup-content">
    <h2>Congratulations!</h2>
    <p>Welcome to iStore!</p>
    <p>30% OFF 🎁</p>
    <p class="popup-message">Enjoy your purchase and happy shopping!</p>
    <button onclick="claimDiscount()">Claim</button>
</div>
</div>
<?php endif; ?>
<header>
    <h1>iStore</h1>

    <div class="nav-right">
     
        <span class="cart" id="cart-icon">
            Cart <span class="count">0</span>
        </span>

        
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
</div> 

</main>

<script>
const navItems = document.querySelectorAll('nav ul li');

navItems.forEach(nav => {
    nav.addEventListener('click', () => {
        navItems.forEach(n => n.classList.remove('active'));
        nav.classList.add('active');

        const category = nav.dataset.category;
        const products = document.querySelectorAll('#product-list .product'); 

        products.forEach(prod => {
            if (category === 'all' || prod.dataset.category === category) {
                prod.style.display = 'flex'; 
            } else {
                prod.style.display = 'none';
            }
        });
    });
});
</script>


<div id="notification-container"></div>


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
          
        </select>
    </div>

    <button class="add-to-cart">Add to Cart</button>
    
</div>

<script src="script.js"></script>
<script>
function claimDiscount() {
    fetch("claim_discount.php")
    .then(res => res.json())
    .then(data => {
        if (data.status === "success") {
            location.reload();
        }
    });
}
</script>


<script>
function createFullScreenConfetti() {
    const container = document.getElementById('confettiContainerFull');
    const colors = ['#f1c40f','#e74c3c','#3498db','#2ecc71','#9b59b6','#ff9f1c'];

    for(let i=0; i<15; i++){
        const confetti = document.createElement('div');
        confetti.classList.add('confetti-full');
        confetti.style.backgroundColor = colors[Math.floor(Math.random()*colors.length)];
        confetti.style.left = Math.random() * 100 + '%';
        confetti.style.width = 6 + Math.random() * 10 + 'px';
        confetti.style.height = 6 + Math.random() * 10 + 'px';
        confetti.style.animationDuration = 3 + Math.random() * 3 + 's';
        confetti.style.animationDelay = Math.random() + 's';
        container.appendChild(confetti);

        setTimeout(() => container.removeChild(confetti), 6000);
    }
}


document.addEventListener("DOMContentLoaded", () => {
    const popup = document.getElementById('discountPopup');
    if(popup){
        const interval = setInterval(createFullScreenConfetti, 500);

        popup.querySelector('button').addEventListener('click', () => {
            clearInterval(interval);
        });
    }
});
</script>


</body>
</html>