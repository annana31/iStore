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
<title>Admin - Users</title>

<style>
/* ======================
   GLOBAL
====================== */
body {
    margin: 0;
    font-family: -apple-system, BlinkMacSystemFont, sans-serif;
    background: #f5f5f7;
    line-height: 1.5;
    color: #111;
}

a { text-decoration: none; color: inherit; }

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

.navbar .nav-left a:hover { color: #8b8b8b; }

/* ACCOUNT DROPDOWN */
.navbar .account {
    position: relative;
    cursor: pointer;
    font-weight: 500;
    font-size: 15px;
}

.dropdown-menu {
    display: none;
    position: absolute;
    right: 0;
    top: 32px;
    background: #fff;
    color: #111;
    border-radius: 8px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    min-width: 140px;
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
}

.dropdown-menu button:hover { background: #f0f0f0; }

/* ======================
   CONTAINER
====================== */
.container {
    padding: 40px 5%;
    max-width: 1200px;
    margin: auto;
    box-sizing: border-box;
}

/* ======================
   USERS LIST (SCOPED)
====================== */
users .users-title {
    font-size: 24px;
    font-weight: 600;
    margin-bottom: 25px;
}

users table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 10px 25px rgba(0,0,0,0.05);
}

users thead tr {
    display: flex;
    justify-content: space-between;
    padding: 16px 20px;
    background: #f5f5f5;
    font-weight: 600;
    border-bottom: 1px solid #eee;
}

users tbody tr {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #eee;
    padding: 16px 20px;
}

users tbody tr:hover { background: #fafafa; }

users td:first-child { flex: 1; }       /* Username expands */
users td:last-child { flex: 0; }        /* Delete button stays right */

/* DELETE BUTTON */
users .delete-btn {
    background: #000000;
    color: white;
    border: none;
    border-radius: 8px;
    padding: 8px 14px;
    cursor: pointer;
    font-weight: 600;
    transition: 0.2s ease;
}

users .delete-btn:hover { background: #3f3e3e; }

/* ======================
   MODAL
====================== */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.45);
    display: none;
    justify-content: center;
    align-items: center;
    padding: 15px;
    z-index: 200;
}

.modal {
    background: #fff;
    padding: 30px 25px;
    border-radius: 16px;
    width: 100%;
    max-width: 400px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    text-align: center;
}

.modal h3 { margin-bottom: 25px; font-size: 18px; color: #111; }

.modal .buttons {
    display: flex;
    justify-content: center;
    gap: 15px;
    flex-wrap: wrap;
}

.modal .buttons button {
    padding: 10px 20px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    font-weight: 600;
    transition: 0.2s ease;
}

.modal .btn-yes { background: #000000; color: #fff; }
.modal .btn-yes:hover { background: #363535; }

.modal .btn-no { background: #ccc; color: #111; }
.modal .btn-no:hover { background: #b3b3b3; }

/* ======================
   TOAST NOTIFICATION
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
   RESPONSIVE
====================== */
@media (max-width: 768px) {
    .navbar { flex-direction: column; align-items: flex-start; }
    users thead tr, users tbody tr { flex-direction: column; align-items: flex-start; }
    users td:last-child { margin-top: 10px; align-self: flex-end; }
    .modal { max-width: 90%; padding: 25px 20px; }
    .modal .buttons button { width: 100%; max-width: 140px; }
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

    <!-- USERS SECTION -->
    <users>
        <div class="users-title">Registered Users</div>
        <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>john_doe</td>
                    <td><button class="delete-btn">Delete</button></td>
                </tr>
                <tr>
                    <td>jane_smith</td>
                    <td><button class="delete-btn">Delete</button></td>
                </tr>
                <tr>
                    <td>alice_wong</td>
                    <td><button class="delete-btn">Delete</button></td>
                </tr>
            </tbody>
        </table>
    </users>

</div>

<!-- CONFIRMATION MODAL -->
<div class="modal-overlay" id="modal-overlay">
    <div class="modal">
        <h3 id="modal-message">Are you sure you want to delete this user?</h3>
        <div class="buttons">
            <button class="btn-yes" id="modal-yes">Yes</button>
            <button class="btn-no" id="modal-no">No</button>
        </div>
    </div>
</div>

<!-- TOAST NOTIFICATION -->
<div id="toast"></div>

<script>
// ACCOUNT DROPDOWN
const accountBtn = document.getElementById('account-btn');
const accountDropdown = document.getElementById('account-dropdown');
const logoutBtn = document.getElementById('logout-btn');

accountBtn.addEventListener('click', () => {
    accountDropdown.style.display = accountDropdown.style.display === 'block' ? 'none' : 'block';
});

document.addEventListener('click', (event) => {
    if (!accountBtn.contains(event.target)) {
        accountDropdown.style.display = 'none';
    }
});

// NAVBAR LINKS
document.querySelector('.nav-left a:nth-child(1)').addEventListener('click', () => { window.location.href = 'adminORDERS.php'; });
document.querySelector('.nav-left a:nth-child(2)').addEventListener('click', () => { window.location.href = 'adminPRODUCTS.php'; });
document.querySelector('.nav-left a:nth-child(3)').addEventListener('click', () => { window.location.href = 'adminUSERS.php'; });

// LOGOUT
logoutBtn.addEventListener('click', () => { window.location.href = 'index.html'; });

// TOAST FUNCTION
const toast = document.getElementById('toast');
function showToast(message) {
    toast.textContent = message;
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 2000);
}

// MODAL DELETE CONFIRMATION
const modalOverlay = document.getElementById('modal-overlay');
const modalYes = document.getElementById('modal-yes');
const modalNo = document.getElementById('modal-no');
let rowToDelete = null;

document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        rowToDelete = btn.closest('tr');
        const username = rowToDelete.querySelector('td').innerText;
        document.getElementById('modal-message').innerText = `Are you sure you want to delete "${username}"?`;
        modalOverlay.style.display = 'flex';
    });
});

modalYes.addEventListener('click', () => {
    if (rowToDelete) {
        rowToDelete.remove();
        rowToDelete = null;
        showToast('Account Deleted');
    }
    modalOverlay.style.display = 'none';
});

modalNo.addEventListener('click', () => {
    modalOverlay.style.display = 'none';
    rowToDelete = null;
});

modalOverlay.addEventListener('click', (e) => {
    if (e.target === modalOverlay) {
        modalOverlay.style.display = 'none';
        rowToDelete = null;
    }
});
</script>

</body>
</html>