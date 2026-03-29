<?php
session_start();

if (!isset($_SESSION["admin_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: adminLOGIN.html");
    exit();
}

/* DATABASE CONNECTION */
$conn = new mysqli("localhost", "root", "", "store_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

/* TOGGLE AVAILABILITY */
if(isset($_POST['toggle_id'])){
    $id = $_POST['toggle_id'];

    $stmt = $conn->prepare("UPDATE admin_products 
                            SET is_available = NOT is_available 
                            WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

/* ADD PRODUCT */
if(isset($_POST['add_product'])){

    $name = $_POST['name'];
    $price = $_POST['price'];
    $category = $_POST['category'];

    $imagePath = "";

    if(!empty($_FILES['image']['name'])){
        $imageName = time() . "_" . $_FILES['image']['name'];
        $tmpName = $_FILES['image']['tmp_name'];

        $uploadDir = "assets/";
        $imagePath = $uploadDir . $imageName;

        move_uploaded_file($tmpName, $imagePath);

        $stmt = $conn->prepare("INSERT INTO admin_products (name, price, category, image) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sdss", $name, $price, $category, $imagePath);
        $stmt->execute();
    }
}

/* DELETE PRODUCT */
if(isset($_POST['delete_id'])){
    $id = $_POST['delete_id'];

    $stmt = $conn->prepare("DELETE FROM admin_products WHERE id=?");
    $stmt->bind_param("i",$id);
    $stmt->execute();
}

/* FETCH ALL PRODUCTS */
$products = [];
$result = $conn->query("SELECT * FROM admin_products ORDER BY id DESC"); // newest first
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()){
        $products[] = $row;
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin - Products</title>

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
   ADD PRODUCT CARD
====================== */
.add-product {
    background: #ffffff;
    padding: 35px;
    border-radius: 16px;
    box-shadow: 0 15px 40px rgba(0,0,0,0.06);
    margin-bottom: 50px;
}

.add-product h2 {
    margin-bottom: 25px;
    font-size: 22px;
    font-weight: 600;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 25px;
    margin-bottom: 25px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 8px;
    color: #333;
}

.form-group input,
.form-group select {
    padding: 12px;
    border-radius: 10px;
    border: 1px solid #ddd;
    font-size: 14px;
    background: #fafafa;
}

.form-group input:focus,
.form-group select:focus {
    border-color: #000;
    background: #fff;
    outline: none;
}

.add-btn {
    width: 100%;
    padding: 14px;
    border-radius: 10px;
    border: none;
    background: #111;
    color: #fff;
    font-weight: 600;
    cursor: pointer;
}

.add-btn:hover {
    background: #333;
}

/* ======================
   PRODUCTS SECTION
====================== */
.productss .products-title {
    font-size: 22px;
    margin-bottom: 20px;
    font-weight: 600;
}

.productss .products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
    gap: 25px;
}

.productss .product-card {
    background: #fff;
    padding: 18px;
    border-radius: 16px;
    text-align: center;
    box-shadow: 0 8px 25px rgba(0,0,0,0.05);
    transition: 0.2s ease;
}

.productss .product-card:hover {
    transform: translateY(-5px);
}

.productss .product-card img {
    width: 100%;
    height: 180px;
    object-fit: contain;
}

.productss .product-card h3 {
    margin: 15px 0 5px;
    font-size: 16px;
}

.productss .product-card p {
    margin: 0 0 12px;
    font-weight: 600;
    color: #555;
}

.productss .actions {
    display: flex;
    justify-content: center;
    gap: 10px; 
}

.productss .actions button {
    padding: 7px 12px;
    border-radius: 6px;
    border: none;
    font-size: 13px;
    cursor: pointer;
}

/* ======================
   EDIT MODAL
====================== */
.modal-overlay {
    position: fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background: rgba(0,0,0,0.45);
    display:none;
    justify-content:center;
    align-items:center;
    z-index: 500;
}

.modal {
    background:#fff;
    padding:25px 20px;
    border-radius:16px;
    width:100%;
    max-width:400px;
    text-align:center;
    box-shadow:0 15px 40px rgba(0,0,0,0.2);
}

.modal h3 {
    margin-bottom:20px;
    font-size:18px;
}

.modal .modal-form {
    display:flex;
    flex-direction:column;
    gap:12px;
}

.modal .modal-form input,
.modal .modal-form select {
    padding:10px;
    border-radius:8px;
    border:1px solid #ddd;
    font-size:14px;
}

.modal .modal-buttons {
    display:flex;
    justify-content:center;
    gap:15px;
    margin-top:15px;
}

.modal .modal-buttons button {
    padding:10px 20px;
    border-radius:8px;
    border:none;
    font-weight:600;
    cursor:pointer;
    transition:0.2s ease;
}

.modal .save-btn {
    background:#111;
    color:#fff;
}

.modal .save-btn:hover {
    background:#333;
}

.modal .cancel-btn {
    background:#ccc;
    color:#111;
}

.modal .cancel-btn:hover {
    background:#b3b3b3;
}

/* ======================
   NOTIFICATION
====================== */
#notification {
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    background: #ccc;
    color: #111;
    padding: 12px 20px;
    border-radius: 10px;
    font-weight: 500;
    display: none;
    z-index: 1000;
}

/* ======================
   UNAVAILABLE STYLE
====================== */
.productss .product-card.unavailable {
    opacity: 0.5;
}

.productss .product-card.unavailable h3,
.productss .product-card.unavailable p {
    text-decoration: line-through;
}

/* ======================
   ACTION BUTTONS MINIMALIST
====================== */
.productss .actions button {
    padding: 6px 5px;   /* smaller padding for a compact look */
    min-width: 50px;      /* uniform width */
    font-size: 13px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    text-align: center;
    transition: 0.2s ease;
}

/* Button colors */
.productss .edit-btn {
    background: #ccc;
    color: #111;
}

.productss .edit-btn:hover {
    opacity: 0.85;
    background: #a7a7a7;
}

.productss .delete-btn {
    background: #111;
    color: #fff;
}

.productss .delete-btn:hover {
    background:#202020;
    opacity: 0.85;
}

.productss .unavailable-btn {
    background: #ccc;
    color: #111;
}

.productss .unavailable-btn:hover {
    opacity: 0.85;
    background: #a7a7a7;

}

.products-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px; /* same spacing as before */
}

.products-header #adminSearch {
    padding: 8px 12px;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 14px;
    width: 200px; /* adjust as needed */
}
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

    <!-- ADD PRODUCT -->
    <div class="add-product">
        <h2>Add New Product</h2>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-grid">
                <div class="form-group">
                    <label>Product Name</label>
                    <input type="text" name="name" id="add-name" required>
                </div>
                <div class="form-group">
                    <label>Price (₱)</label>
                    <input type="number" name="price" id="add-price" required>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select name="category" id="add-category">
                        <option>Phones</option>
                        <option>iPads</option>
                        <option>Macs</option>
                        <option>Watches</option>
                        <option>Accessories</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Product Image</label>
                    <input type="file" name="image" id="add-image" accept="image/*">
                </div>
            </div>
            <button type="submit" name="add_product" class="add-btn">Add Product</button>
        </form>
    </div>

    <!-- PRODUCTS DISPLAY -->
    <div class="productss">
        <div class="products-header">
    <div class="products-title">All Products</div>
    <input type="text" id="adminSearch" placeholder="Search products...">
</div>
        <div class="products-grid" id="products-grid">

<?php
foreach($products as $row){
?>
<div class="product-card <?php echo !$row['is_available'] ? 'unavailable' : ''; ?>" 
     data-category="<?php echo htmlspecialchars($row['category']); ?>">

    <img src="<?php echo htmlspecialchars($row['image']); ?>" alt="Product Image">
    <h3><?php echo $row['name']; ?></h3>
    <p>₱<?php echo number_format($row['price']); ?></p>

    <div class="actions">

        <!-- DELETE BUTTON (FORM FIXED) -->
        <button type="button" class="delete-btn" data-id="<?php echo $row['id']; ?>">Delete</button>

        <!-- EDIT BUTTON -->
        <button type="button" class="edit-btn" data-id="<?php echo $row['id']; ?>">Edit</button>

        <!-- TOGGLE AVAILABILITY -->
        <form method="POST" style="display:inline;">
            <input type="hidden" name="toggle_id" value="<?php echo $row['id']; ?>">
            <button type="submit" class="unavailable-btn">
                <?php echo $row['is_available'] ? 'Unavailable' : 'Available'; ?>
            </button>
        </form>

    </div>
</div>
<?php } ?>

        </div>
    </div>

</div>

<!-- EDIT MODAL -->
<div class="modal-overlay" id="edit-modal-overlay">
    <div class="modal">
        <h3>Edit Product</h3>
        <form class="modal-form" id="edit-form">
            <input type="text" id="edit-name" placeholder="Product Name" required>
            <select id="edit-category" required>
                <option>Phones</option>
                <option>iPads</option>
                <option>Macs</option>
                <option>Watches</option>
                <option>Accessories</option>
            </select>
            <input type="hidden" id="edit-id">
            <input type="text" id="edit-price" placeholder="Price (₱)" required>
            <input type="file" id="edit-image-file" accept="image/*">
            <div class="modal-buttons">
                <button type="submit" class="save-btn">Save</button>
                <button type="button" class="cancel-btn" id="edit-cancel">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- DELETE MODAL -->
<div class="modal-overlay" id="delete-modal-overlay">
    <div class="modal">
        <h3>Are you sure you want to delete this product?</h3>
        <div class="modal-buttons">
            <button id="confirm-delete" class="save-btn">Yes</button>
            <button id="cancel-delete" class="cancel-btn">No</button>
        </div>
    </div>
</div>

<!-- NOTIFICATION -->
<div id="notification">Product action completed!</div>

<script>
// ======================
// NOTIFICATION FUNCTION
// ======================
const notification = document.getElementById('notification');
function showNotification(message){
    notification.innerText = message;
    notification.style.display = 'block';
    setTimeout(()=>{notification.style.display='none';},2000);
}

// ======================
// EDIT & DELETE LOGIC
// ======================
let productToEdit = null, productToDelete = null;
let productToDeleteId = null;
const editModalOverlay = document.getElementById('edit-modal-overlay');
const editForm = document.getElementById('edit-form');
const editName = document.getElementById('edit-name');
const editCategory = document.getElementById('edit-category');
const editPrice = document.getElementById('edit-price');
const editImageFile = document.getElementById('edit-image-file');
const editId = document.getElementById('edit-id');

const deleteModalOverlay = document.getElementById('delete-modal-overlay');
const confirmDeleteBtn = document.getElementById('confirm-delete');
const cancelDeleteBtn = document.getElementById('cancel-delete');

// CANCEL EDIT
document.getElementById('edit-cancel').addEventListener('click', () => {
    editModalOverlay.style.display = 'none';
    productToEdit = null;
});

function bindEditDelete(card){
    const editBtn = card.querySelector('.edit-btn');
    const deleteBtn = card.querySelector('.delete-btn');
    

    // EDIT BUTTON
    editBtn.addEventListener('click', () => {
        productToEdit = card;

        editName.value = card.querySelector('h3').innerText;
        editCategory.value = card.getAttribute('data-category');

        const rawPrice = card.querySelector('p').innerText
            .replace('₱','')
            .replace(/,/g,'');

        editPrice.value = rawPrice;
        editId.value = editBtn.getAttribute('data-id');

        editImageFile.value = '';
        editModalOverlay.style.display = 'flex';
    });

    // DELETE BUTTON
    deleteBtn.addEventListener('click', () => {
        productToDelete = card;
        productToDeleteId = deleteBtn.getAttribute('data-id');
        deleteModalOverlay.style.display = 'flex';
    });
}



// DELETE MODAL
confirmDeleteBtn.addEventListener('click', () => {
    if(productToDelete){

        const formData = new FormData();
        formData.append('delete_id', productToDeleteId);

        fetch('', {
            method: 'POST',
            body: formData
        }).then(() => {

            productToDelete.remove();
            productToDelete = null;
            deleteModalOverlay.style.display = 'none';
            showNotification('Product Deleted Successfully!');
        });
    }
});
cancelDeleteBtn.addEventListener('click', () => {
    deleteModalOverlay.style.display = 'none';
    productToDelete = null;
});
deleteModalOverlay.addEventListener('click', e => {
    if(e.target === deleteModalOverlay) deleteModalOverlay.style.display = 'none';
});
editModalOverlay.addEventListener('click', e => {
    if(e.target === editModalOverlay) editModalOverlay.style.display = 'none';
});

// SAVE EDIT (send to updateProduct.php)
editForm.addEventListener('submit', e => {
    e.preventDefault();
    if(!productToEdit) return;

    const formData = new FormData();
    formData.append('edit_id', editId.value);
    formData.append('name', editName.value);
    formData.append('category', editCategory.value);
    formData.append('price', editPrice.value.replace(/[^0-9.]/g, ''));
    if(editImageFile.files[0]) formData.append('image', editImageFile.files[0]);

    fetch('updateProduct.php', { method: 'POST', body: formData })
    .then(res => res.text())
    .then(data => {
        productToEdit.querySelector('h3').innerText = editName.value;
        productToEdit.setAttribute('data-category', editCategory.value);
        const priceNumber = parseFloat(editPrice.value.replace(/[^0-9.]/g,''));
        productToEdit.querySelector('p').innerText = `₱${priceNumber.toLocaleString()}`;
        if(editImageFile.files && editImageFile.files[0]){
            const reader = new FileReader();
            reader.onload = ev => productToEdit.querySelector('img').src = ev.target.result;
            reader.readAsDataURL(editImageFile.files[0]);
        }
        editModalOverlay.style.display='none';
        productToEdit = null;
        showNotification('Product Updated Successfully!');
    });
});

// ACCOUNT DROPDOWN
const accountBtn = document.getElementById('account-btn');
const accountDropdown = document.getElementById('account-dropdown');
accountBtn.addEventListener('click', () => accountDropdown.style.display = accountDropdown.style.display === 'block' ? 'none' : 'block');

// LOGOUT
document.getElementById('logout-btn').addEventListener('click', () => {
    window.location.href = "adminLOGOUT.php";
});

// INIT
document.querySelectorAll('.product-card').forEach(bindEditDelete);

// ======================
// ADMIN PRODUCTS SEARCH
// ======================
const searchInput = document.getElementById('adminSearch');
const productCards = document.querySelectorAll('.productss .product-card');

searchInput.addEventListener('input', () => {
    const query = searchInput.value.toLowerCase();
    productCards.forEach(card => {
        const name = card.querySelector('h3').innerText.toLowerCase();
        const category = card.getAttribute('data-category').toLowerCase();
        if(name.includes(query) || category.includes(query)) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
});

</script>

</body>
</html>