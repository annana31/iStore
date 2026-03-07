document.addEventListener("DOMContentLoaded", () => {
  function getAllProducts() {
  return document.querySelectorAll(".product");
}
  const searchInput = document.querySelector("input[type='search']");
  const cartCount = document.querySelector(".count");
  const cartIcon = document.querySelector(".cart");
  const profileDropdown = document.querySelector(".profile-dropdown");
  const logoutBtn = document.getElementById("logout-btn");
  const navItems = document.querySelectorAll("nav ul li");
  const cartItemsContainer = document.getElementById("cart-items");
  const checkoutButtons = document.querySelectorAll(".checkout-btn");
  

// =========================
// PRICE FORMATTER
// =========================
function formatPrice(price){
  return "₱" + Number(price).toLocaleString("en-PH", {minimumFractionDigits:2});
}




// =========================
// UPDATE CART COUNT FROM DATABASE
// =========================
async function updateCartCount() {
  try {
    const res = await fetch('cart_fetch.php'); // fetch latest cart items from DB
    const data = await res.json();
    const totalItems = data.reduce((sum, item) => sum + Number(item.quantity), 0);
    if (cartCount) cartCount.textContent = totalItems;
  } catch (err) {
    console.error("Failed to fetch cart count:", err);
    if (cartCount) cartCount.textContent = 0;
  }
}

// Call this once on page load to initialize
updateCartCount();


// =========================
// SIDE PANEL FUNCTIONALITY
// =========================
const panel = document.getElementById("sidePanel");
const closeBtn = panel.querySelector(".close-btn");
const panelImage = document.getElementById("panelImage");
const panelName = document.getElementById("panelName");
const panelPrice = document.getElementById("panelPrice");
const quantityInput = panel.querySelector("input[type='number']");
const panelAddBtn = panel.querySelector(".add-to-cart");

const storageSelect = document.getElementById("storageSelect");
const colorSelect = document.getElementById("colorSelect");

const deviceColors = {
  phones: ["Midnight","Starlight","Product Red","Blue","Purple","Green","Silver","Gold","Deep Purple","Space Black","Sage","Ocean Blue"],
  ipads: ["Space Gray","Silver","Green","Sky Blue","Pink","Purple","Yellow"],
  macs: ["Silver","Space Gray","Midnight","Starlight","Blue","Pink","Purple"]
};


// =========================
// OPEN PANEL - DELEGATED
// =========================
document.body.addEventListener("click", e => {
  const button = e.target.closest(".view-item-btn");
  if (!button) return;

  e.preventDefault();
  e.stopPropagation();

  const product = button.closest(".product");
  if (!product) return;

  const category = product.dataset.category.toLowerCase();

  colorSelect.parentElement.style.display = "none";
  storageSelect.parentElement.style.display = "none";

  if (category === "phones" || category === "ipads" || category === "macs") {
    colorSelect.parentElement.style.display = "block";
    storageSelect.parentElement.style.display = "block";

    colorSelect.innerHTML = deviceColors[category].map(c => `<option>${c}</option>`).join("");
    storageSelect.innerHTML = `
      <option>128GB</option>
      <option>256GB</option>
      <option>512GB</option>
      <option>1TB</option>`;
    storageSelect.value = "128GB";
  } else if (category === "watches") {
    storageSelect.parentElement.style.display = "block";
    storageSelect.innerHTML = `
      <option>128GB</option>
      <option>256GB</option>`;
    storageSelect.value = "128GB";
  }

panelImage.src = product.querySelector("img")?.dataset.src || product.querySelector("img")?.src || "assets/default.png";

  panelName.innerText = product.querySelector("h3")?.innerText || "";
  panelPrice.innerText = product.querySelector("p")?.innerText || "";
  quantityInput.value = 1;

  panel.classList.add("active");
  document.body.classList.add("panel-open");
});


// =========================
// CLOSE PANEL
// =========================
if(closeBtn) closeBtn.addEventListener("click",closePanel);

function closePanel(){
  panel.classList.remove("active");
  document.body.classList.remove("panel-open");
}

document.addEventListener("click",e=>{
  if(panel.classList.contains("active") &&
    !panel.contains(e.target) &&
    !e.target.closest(".view-item-btn")){
    closePanel();
  }
});


// =========================
// ADD TO CART
// =========================


panelAddBtn.addEventListener("click", async e => {
  e.preventDefault();

  const name = panelName.innerText;
  const price = parseFloat(panelPrice.innerText.replace(/[₱,]/g,"")) || 0;
  const quantity = parseInt(quantityInput.value) || 1;
  const storage = storageSelect.parentElement.style.display !== "none" ? storageSelect.value : "";
  const color = colorSelect.parentElement.style.display !== "none" ? colorSelect.value : "";
  const image = panelImage.src.split("/").pop(); // get filename only

  try {
    const res = await fetch('store.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `action=add_to_cart&product_name=${encodeURIComponent(name)}&product_price=${price}&quantity=${quantity}&storage=${encodeURIComponent(storage)}&color=${encodeURIComponent(color)}&product_image=${encodeURIComponent(image)}`

    });

    const data = await res.json();

    if (data.status === 'success') {
      showNotification(`${name} added to cart!`);
      updateCartCount(); // refresh cart count
      closePanel();
    } else {
      alert("Error adding to cart: " + data.message);
    }
  } catch(err) {
    console.error(err);
    alert("Network error while adding to cart.");
  }



  async function updateCartCountFromDB() {
  try {
    const res = await fetch('cart_fetch.php');
    const data = await res.json();
    const totalItems = data.reduce((sum, item) => sum + Number(item.quantity), 0);
    if (cartCount) cartCount.textContent = totalItems;
  } catch (err) {
    console.error(err);
  }
}
});




// =========================
// CART PAGE RENDER
// =========================
if(cartItemsContainer) renderCart();

async function renderCart() {
    if (!cartItemsContainer) return;

    try {
        const res = await fetch('cart_fetch.php');
        const cartData = await res.json();

        cartItemsContainer.innerHTML = "";

        if (cartData.length === 0) {
            cartItemsContainer.innerHTML = `
            <tr>
                <td colspan="7" class="empty-cart">Your cart is empty</td>
            </tr>`;
            finalTotal.textContent = "₱0";
            cartCount.textContent = 0;
            return;
        }

        let subtotal = 0;

       cartData.forEach(item => {
    const imagePath = item.product_image ? `assets/${item.product_image}` : "assets/default.png";

    subtotal += Number(item.product_price) * Number(item.quantity);

    cartItemsContainer.innerHTML += `
    <tr>
        <td class="item-cell">
            <img src="${imagePath}" alt="${item.product_name}" style="width:60px;height:auto;">
            ${item.product_name}
        </td>
        <td>${item.storage || "-"}</td>
        <td>${item.color || "-"}</td>
        <td class="price-cell">₱${Number(item.product_price).toLocaleString()}</td>
        <td>${item.quantity}</td>
        <td class="total-cell">₱${(Number(item.product_price) * Number(item.quantity)).toLocaleString()}</td>
        <td>
            <button class="remove-btn" data-id="${item.id}">×</button>
        </td>
    </tr>`;
});



        finalTotal.textContent = "₱" + subtotal.toLocaleString();

        // Remove buttons
        document.querySelectorAll(".remove-btn").forEach(btn => {
            btn.addEventListener("click", async e => {
                const id = e.target.dataset.id;
                await fetch('cart_remove.php', {
                    method:'POST',
                    headers:{'Content-Type':'application/x-www-form-urlencoded'},
                    body:`id=${id}`
                });
                await renderCart(); // reload after removing
            });
        });

        // Update cart count in navbar
        const totalItems = cartData.reduce((sum, item) => sum + Number(item.quantity), 0);
        cartCount.textContent = totalItems;

    } catch (err) {
        console.error("Failed to fetch cart items:", err);
        cartItemsContainer.innerHTML = `
        <tr>
            <td colspan="7" class="empty-cart">Failed to load cart.</td>
        </tr>`;
    }
}


// =========================
// TOTALS
// =========================
function updateTotals(subtotal){

const subtotalEl=document.getElementById("subtotal");
const finalTotalEl=document.getElementById("final-total");
const heroTotalEl=document.getElementById("cart-total");

if(subtotalEl) subtotalEl.textContent = formatPrice(subtotal);
if(finalTotalEl) finalTotalEl.textContent = formatPrice(subtotal);
if(heroTotalEl) heroTotalEl.textContent = formatPrice(subtotal);

}


// =========================
// CART EVENTS
// =========================
function attachCartItemEventsDB() {
  document.querySelectorAll(".cart-item").forEach(itemEl => {
    const removeBtn = itemEl.querySelector(".remove-btn");
    const id = itemEl.dataset.id; // database id

    removeBtn.onclick = async () => {
      try {
        await fetch('cart_remove.php', {
          method: 'POST',
          headers: {'Content-Type': 'application/x-www-form-urlencoded'},
          body: `id=${id}`
        });
        renderCart(); // refresh cart display
        updateCartCount(); // refresh nav cart count
      } catch (err) {
        console.error("Failed to remove item:", err);
      }
    };
  });
}


// =========================
// CART ICON
// =========================
if(cartIcon){
cartIcon.addEventListener("click",()=>{
window.location.href="cart.html";
});
}


// =========================
// SEARCH
// =========================
if (searchInput) {
  searchInput.addEventListener("input", () => {
    const searchValue = searchInput.value.toLowerCase();

    getAllProducts().forEach(product => {
      const name = product.querySelector("h3").textContent.toLowerCase();
      product.style.display = name.includes(searchValue) ? "block" : "none";
    });
  });
}


// =========================
// CATEGORY FILTER (UPDATED)
// =========================
navItems.forEach(item=>{
  item.addEventListener("click",()=>{
    navItems.forEach(i=>i.classList.remove("active"));
    item.classList.add("active");

    const category = item.dataset.category;

    getAllProducts().forEach(product => {
  product.style.display =
    category === "all" || product.dataset.category === category
      ? "block"
      : "none";
});
  });
});


// =========================
// PROFILE
// =========================
if(profileDropdown){
profileDropdown.addEventListener("click",e=>{
e.stopPropagation();
profileDropdown.classList.toggle("active");
});

document.addEventListener("click",()=>{
profileDropdown.classList.remove("active");
});
}

if(logoutBtn){
logoutBtn.addEventListener("click",()=>{
localStorage.removeItem("cart");
window.location.href="index.html";
});
}


// =========================
// NOTIFICATION
// =========================
const notificationContainer=document.getElementById("notification-container");
let notifTimeout;

function showNotification(message){

let notif = notificationContainer.querySelector(".notification");

if(!notif){
notif=document.createElement("div");
notif.className="notification";
notificationContainer.appendChild(notif);
}

notif.textContent=message;
notif.classList.add("show");

if(notifTimeout) clearTimeout(notifTimeout);

notifTimeout=setTimeout(()=>{
notif.classList.remove("show");
},2500);

}

// =========================
// SHOW ALL PRODUCTS ON PAGE LOAD
// =========================
const allCategory = document.querySelector("nav ul li[data-category='all']");
if(allCategory) allCategory.click();

}); // end of DOMContentLoaded