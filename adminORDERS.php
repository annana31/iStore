<?php
session_start();

if (!isset($_SESSION["admin_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: adminLOGIN.html");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin - Orders</title>

<style>
/* ======================
   GLOBAL
====================== */
body {
    margin: 0;
    font-family: -apple-system, BlinkMacSystemFont, sans-serif;
    background: #f5f5f7;
}

a {
    text-decoration: none;
    color: inherit;
}

/* ======================
   NAVBAR
====================== */
.navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 40px;
    background: #111;
    color: #fff;
}

.navbar .nav-left {
    display: flex;
    gap: 25px;
}

.navbar .nav-left a {
    color: #fff;
    font-weight: 500;
    font-size: 15px;
    transition: 0.2s ease;
}

.navbar .nav-left a:hover {
    color: #8b8b8b;
}

/* ACCOUNT DROPDOWN */
.navbar .account {
    position: relative;
    cursor: pointer;
    font-weight: 500;
    font-size: 15px;
}

.navbar .account:hover {
    color: #8b8b8b;
}

.dropdown-menu {
    display: none;
    position: absolute;
    right: 0;
    top: 28px;
    background: #fff;
    color: #111;
    border-radius: 8px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    min-width: 120px;
    overflow: hidden;
    z-index: 100;
}

.dropdown-menu button {
    width: 100%;
    padding: 10px 16px;
    border: none;
    background: none;
    text-align: left;
    cursor: pointer;
    font-size: 14px;
    transition: 0.2s ease;
}

.dropdown-menu button:hover {
    background: #f0f0f0;
}

/* ======================
   CONTAINER
====================== */
.container {
    padding: 40px;
    max-width: 1400px;
    margin: auto;
}

/* ======================
   ORDERS SECTION (SCOPED)
====================== */
orders .orders-title {
    font-size: 22px;
    font-weight: 600;
    margin-bottom: 25px;
}

orders .orders-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 20px;
}

orders .order-card {
    background: #fff;
    padding: 20px;
    border-radius: 16px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.05);
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 15px;
    transition: 0.2s ease;
}

orders .order-card:hover {
    transform: translateY(-3px);
}

orders .order-image {
    width: 80px;
    height: 80px;
    border-radius: 12px;
    object-fit: contain;
    background: #f7f7f7;
    flex-shrink: 0;
}

orders .order-details {
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    gap: 5px;
}

orders .order-details .product-name {
    font-weight: 600;
    font-size: 16px;
    color: #111;
}

orders .order-details .quantity {
    font-size: 14px;
    color: #555;
}

orders .order-actions {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

orders .confirm-btn {
    background: #000000;
    color: white;
    border: none;
    border-radius: 8px;
    padding: 8px 14px;
    cursor: pointer;
    font-weight: 600;
    transition: 0.2s ease;
}

orders .confirm-btn:hover {
    background: #222121;
}

orders .remove-btn {
    background: #ccc;
    color: #111;
    border: none;
    border-radius: 8px;
    padding: 8px 14px;
    cursor: pointer;
    font-weight: 600;
    transition: 0.2s ease;
}

orders .remove-btn:hover {
    background: #b3b3b3;
}

/* ======================
   TOAST NOTIFICATIONS
====================== */
#toast {
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    background: #ccc;
    color: #111;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 500;
    opacity: 0;
    transition: opacity 0.3s ease, transform 0.3s ease;
    z-index: 999;
}

#toast.show {
    opacity: 1;
    transform: translateX(-50%) translateY(0);
}

/* ======================
   REMOVE MODAL
====================== */
#remove-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

#remove-modal .modal-content {
    background: #fff;
    padding: 30px 40px;
    border-radius: 12px;
    text-align: center;
    max-width: 400px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
}

#remove-modal .modal-content p {
    margin-bottom: 20px;
    font-size: 16px;
    color: #111;
}

#remove-modal .modal-content button {
    padding: 8px 16px;
    margin: 0 10px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    font-size: 14px;
    transition: 0.2s ease;
}

#remove-modal .modal-content .yes-btn {
    background: #000000;
    color: white;
}

#remove-modal .modal-content .yes-btn:hover {
    background: #363636;
}

#remove-modal .modal-content .cancel-btn {
    background: #ccc;
    color: #111;
}

#remove-modal .modal-content .cancel-btn:hover {
    background: #b3b3b3;
}

</style>
</head>
<body>

<!-- NAVBAR -->
<div class="navbar">
    <div class="nav-left">
        <a href="#">Orders</a>
        <a href="#">Products</a>
        <a href="#">Users</a>
    </div>
    <div class="account" id="account-btn">Account
        <div class="dropdown-menu" id="account-dropdown">
            <button id="logout-btn">Logout</button>
        </div>
    </div>
</div>

<div class="container">

    <!-- ORDERS SECTION -->
    <orders>
        <div class="orders-title">Orders</div>
        <div class="orders-grid">

            <!-- EXAMPLE ORDER CARD -->
            <div class="order-card">
                <img class="order-image" src="assets/iphone14pro.png" alt="iPhone 14 Pro">
                <div class="order-details">
                    <div class="product-name">iPhone 14 Pro</div>
                    <div class="quantity">Quantity: 2</div>
                </div>
                <div class="order-actions">
                    <button class="confirm-btn">Confirm</button>
                    <button class="remove-btn">Remove</button>
                </div>
            </div>

            <div class="order-card">
                <img class="order-image" src="assets/macbookpro14.png" alt="MacBook Pro 14">
                <div class="order-details">
                    <div class="product-name">MacBook Pro 14</div>
                    <div class="quantity">Quantity: 1</div>
                </div>
                <div class="order-actions">
                    <button class="confirm-btn">Confirm</button>
                    <button class="remove-btn">Remove</button>
                </div>
            </div>

            <div class="order-card">
                <img class="order-image" src="assets/applewatch8.png" alt="Apple Watch Series 8">
                <div class="order-details">
                    <div class="product-name">Apple Watch Series 8</div>
                    <div class="quantity">Quantity: 3</div>
                </div>
                <div class="order-actions">
                    <button class="confirm-btn">Confirm</button>
                    <button class="remove-btn">Remove</button>
                </div>
            </div>

        </div>
    </orders>

</div>

<!-- Confirm Toast -->
<div id="toast"></div>

<!-- Remove Modal -->
<div id="remove-modal">
    <div class="modal-content">
        <p>Are you sure you want to remove this order?</p>
        <button class="yes-btn">Yes</button>
        <button class="cancel-btn">Cancel</button>
    </div>
</div>

<script>
// Account dropdown
const accountBtn = document.getElementById('account-btn');
const accountDropdown = document.getElementById('account-dropdown');
const logoutBtn = document.getElementById('logout-btn');

accountBtn.addEventListener('click', () => {
    accountDropdown.style.display = accountDropdown.style.display === 'block' ? 'none' : 'block';
});

document.addEventListener('click', (e) => {
    if(!accountBtn.contains(e.target)) accountDropdown.style.display = 'none';
});

// Navbar navigation
document.querySelector('.nav-left a:nth-child(1)').addEventListener('click', () => { window.location.href = 'adminORDERS.html'; });
document.querySelector('.nav-left a:nth-child(2)').addEventListener('click', () => { window.location.href = 'adminPRODUCTS.html'; });
document.querySelector('.nav-left a:nth-child(3)').addEventListener('click', () => { window.location.href = 'adminUSERS.html'; });

// Logout
logoutBtn.addEventListener('click', () => { window.location.href = 'admin_logout.php'; });

// Toast
const toast = document.getElementById('toast');
function showToast(message) {
    toast.textContent = message;
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 2000);
}

// Remove modal
const removeModal = document.getElementById('remove-modal');
let currentOrderToRemove = null;

document.querySelectorAll('.confirm-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const orderCard = btn.closest('.order-card');
        orderCard.remove(); // Remove the order
        showToast('Order Confirmed');
    });
});

document.querySelectorAll('.remove-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        currentOrderToRemove = btn.closest('.order-card');
        removeModal.style.display = 'flex';
    });
});

// Modal buttons
removeModal.querySelector('.yes-btn').addEventListener('click', () => {
    if(currentOrderToRemove) currentOrderToRemove.remove();
    removeModal.style.display = 'none';
    showToast('Order Removed');
    currentOrderToRemove = null;
});

removeModal.querySelector('.cancel-btn').addEventListener('click', () => {
    removeModal.style.display = 'none';
    currentOrderToRemove = null;
});

// Close modal if clicking outside content
removeModal.addEventListener('click', (e) => {
    if(e.target === removeModal){
        removeModal.style.display = 'none';
        currentOrderToRemove = null;
    }
});
</script>

</body>
</html>