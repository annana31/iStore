<?php
session_start();

// Redirect if not admin
if (!isset($_SESSION["admin_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: adminLOGIN.html");
    exit();
}

$conn = new mysqli("localhost", "root", "", "store_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin - Orders</title>
<style>
/* ====================== GLOBAL ====================== */
body {
    margin: 0;
    font-family: -apple-system, BlinkMacSystemFont, sans-serif;
    background: #f5f5f7;
}
a { text-decoration: none; color: inherit; }

/* ====================== NAVBAR ====================== */
.navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 40px;
    background: #111;
    color: #fff;
}
.navbar .nav-left { display: flex; gap: 25px; }
.navbar .nav-left a { color: #fff; font-weight: 500; font-size: 15px; transition: 0.2s ease; }
.navbar .nav-left a:hover { color: #8b8b8b; }
.navbar .account { position: relative; cursor: pointer; font-weight: 500; font-size: 15px; }
.navbar .account:hover { color: #8b8b8b; }
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
.dropdown-menu button:hover { background: #f0f0f0; }

/* ====================== CONTAINER ====================== */
.container { padding: 40px; max-width: 1400px; margin: auto; }

/* ====================== ORDERS SECTION ====================== */
orders .orders-title { font-size: 22px; font-weight: 600; margin-bottom: 25px; }
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
orders .order-card:hover { transform: translateY(-3px); }
orders .order-details { flex-grow: 1; display: flex; flex-direction: column; gap: 5px; }
orders .order-details .product-name { font-weight: 600; font-size: 16px; color: #111; }
orders .order-details .quantity { font-size: 14px; color: #555; }
orders .order-details strong.status { font-weight: 600; }
orders .order-actions { display: flex; flex-direction: column; gap: 8px; }
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
orders .confirm-btn:hover { background: #222121; }
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
orders .remove-btn:hover { background: #b3b3b3; }

/* ====================== TOAST ====================== */
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
#toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }

/* ====================== REMOVE MODAL ====================== */
#remove-modal {
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
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
#remove-modal .modal-content p { margin-bottom: 20px; font-size: 16px; color: #111; }
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
#remove-modal .modal-content .yes-btn { background: #000; color: white; }
#remove-modal .modal-content .yes-btn:hover { background: #363636; }
#remove-modal .modal-content .cancel-btn { background: #ccc; color: #111; }
#remove-modal .modal-content .cancel-btn:hover { background: #b3b3b3; }
</style>
</head>
<body>

<!-- NAVBAR -->
<div class="navbar">
    <div class="nav-left">
        <a href="adminORDERS.php">Orders</a>
        <a href="adminPRODUCTS.php">Products</a>
        <a href="adminUSERS.php">Users</a>
    </div>
    <div class="account" id="account-btn">Account
        <div class="dropdown-menu" id="account-dropdown">
            <button id="logout-btn">Logout</button>
        </div>
    </div>
</div>

<div class="container">
    <orders>
        <div class="orders-title">Orders</div>
        <div class="orders-grid">
        <?php
        $sql = "SELECT * FROM cart_items ORDER BY added_at DESC";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
        ?>
        <div class="order-card" id="order-<?= $row['id']; ?>">
            <div class="order-details">
                <div class="product-name"><?= htmlspecialchars($row['product_name']); ?></div>
                <div class="quantity">Price: ₱<?= number_format($row['product_price'],2); ?></div>
                <div class="quantity">Quantity: <?= $row['quantity']; ?></div>
                <div class="quantity">User: <?= htmlspecialchars($row['username']); ?></div>
                <div class="quantity">Ordered at: <?= $row['added_at']; ?></div>
                <div class="quantity">
                    Status:
                    <strong class="status" style="color: <?= ($row['status']=='Confirmed')?'green':'orange'; ?>;">
                        <?= $row['status']; ?>
                    </strong>
                </div>
            </div>
            <div class="order-actions">
                <?php if($row['status']=='Pending'): ?>
                <button class="confirm-btn" data-id="<?= $row['id']; ?>">Confirm</button>
                <button class="remove-btn" data-id="<?= $row['id']; ?>">Remove</button>
                <?php else: ?>
                Confirmed
                <?php endif; ?>
            </div>
        </div>
        <?php
            }
        } else {
            echo "<p>No orders found.</p>";
        }
        ?>
        </div>
    </orders>
</div>

<!-- Toast -->
<div id="toast"></div>

<!-- Remove Modal -->
<div id="remove-modal">
    <div class="modal-content">
        <p>Are you sure you want to remove this order?</p>
        <button class="yes-btn">Yes</button>
        <button class="cancel-btn">Cancel</button>
    </div>
</div>

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// Account dropdown
const accountBtn = document.getElementById('account-btn');
const accountDropdown = document.getElementById('account-dropdown');
const logoutBtn = document.getElementById('logout-btn');
accountBtn.addEventListener('click', () => {
    accountDropdown.style.display = accountDropdown.style.display==='block'?'none':'block';
});
document.addEventListener('click', e => {
    if(!accountBtn.contains(e.target)) accountDropdown.style.display='none';
});
logoutBtn.addEventListener('click', () => { window.location.href='admin_logout.php'; });

// Toast
const toast = document.getElementById('toast');
function showToast(msg){ toast.textContent=msg; toast.classList.add('show'); setTimeout(()=>toast.classList.remove('show'),2000); }

// Confirm order via AJAX
$(document).ready(function(){
    $('.confirm-btn').click(function(){
        var orderId = $(this).data('id');
        var button = $(this);
        var statusEl = $('#order-'+orderId+' strong.status');
        $.ajax({
            url: 'confirm_order.php',
            method: 'POST',
            data: { id: orderId },
            success: function(resp){
                if(resp.trim()=='success'){
                    statusEl.text('Confirmed').css('color','green');
                    button.replaceWith('Confirmed');
                    showToast('Order Confirmed');
                } else { alert('Failed to confirm order.'); }
            },
            error:function(){ alert('Error connecting to server.'); }
        });
    });
});

// Remove order
$('.remove-btn').click(function(){
    currentOrderToRemove = $(this).closest('.order-card')[0];
    var orderId = $(this).data('id');
    $('#remove-modal').css('display','flex');

    // Yes button click
    $('#remove-modal .yes-btn').off('click').on('click', function(){
        $.ajax({
            url: 'remove_order.php',
            method: 'POST',
            data: { id: orderId },
            success: function(resp){
                if(resp.trim() == 'success'){
                    $(currentOrderToRemove).remove();
                    showToast('Order Removed');
                } else {
                    alert('Failed to remove order.');
                }
                $('#remove-modal').hide();
                currentOrderToRemove = null;
            },
            error: function(){
                alert('Error connecting to server.');
                $('#remove-modal').hide();
                currentOrderToRemove = null;
            }
        });
    });

    // Cancel button click
    $('#remove-modal .cancel-btn').off('click').on('click', function(){
        $('#remove-modal').hide();
        currentOrderToRemove = null;
    });
});


</script>
</body>
</html>