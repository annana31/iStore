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
// LOAD & NORMALIZE CART
// =========================
let cart = JSON.parse(localStorage.getItem("cart")) || [];
cart = cart.map(item => ({
  name: item.name || item.product_name || "Unknown",
  price: Number(item.price) || parseFloat(item.product_price) || 0,
  image: item.image || item.product_image || "",
  quantity: item.quantity || 1,
  storage: item.storage || "",
  color: item.color || ""
}));
localStorage.setItem("cart", JSON.stringify(cart));


// =========================
// UPDATE CART COUNT
// =========================
function updateCartCount() {
  if (cartCount) {
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    cartCount.textContent = totalItems;
  }
}
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

  const category = product.dataset.category;

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

  panelImage.src = product.querySelector("img")?.src || "";
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
panelAddBtn.addEventListener("click",e=>{
  e.preventDefault();
  e.stopPropagation();

  const name = panelName.innerText;

  // 🔥 FIX PRICE PARSING
  const price = parseFloat(panelPrice.innerText.replace(/[₱,]/g,"")) || 0;

  const image = panelImage.src;
  const quantity = parseInt(quantityInput.value) || 1;

  const storage = storageSelect.parentElement.style.display!=="none"?storageSelect.value:"";
  const color = colorSelect.parentElement.style.display!=="none"?colorSelect.value:"";

  const existingItem = cart.find(item =>
    item.name===name &&
    item.storage===storage &&
    item.color===color
  );

  if(existingItem){
    existingItem.quantity+=quantity;
  }else{
    cart.push({name,price,image,quantity,storage,color});
  }

  localStorage.setItem("cart",JSON.stringify(cart));
  updateCartCount();
  showNotification(`${name} added to cart!`);
  closePanel();
});


// =========================
// CART PAGE RENDER
// =========================
if(cartItemsContainer) renderCart();

function renderCart(){

cartItemsContainer.innerHTML="";

if(cart.length===0){
cartItemsContainer.innerHTML="<p>Your cart is empty.</p>";
updateTotals(0);
return;
}

let subtotal=0;

cart.forEach((item,index)=>{

subtotal += item.price * item.quantity;

cartItemsContainer.innerHTML +=`
<div class="cart-item" data-index="${index}">
<img src="${item.image}">
<div class="item-details">
<h3>${item.name}</h3>
${item.storage?`<p>Storage: ${item.storage}</p>`:""}
${item.color?`<p>Color: ${item.color}</p>`:""}
<p>Price: ${formatPrice(item.price)}</p>
<p>Quantity: <input type="number" class="quantity-input" value="${item.quantity}" min="1" readonly></p>
<button class="remove-btn">Remove</button>
</div>
<h3 class="item-total">${formatPrice(item.price*item.quantity)}</h3>
</div>
`;
});

updateTotals(subtotal);
attachCartItemEvents();
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
function attachCartItemEvents(){

document.querySelectorAll(".cart-item").forEach(itemEl=>{

const index=parseInt(itemEl.dataset.index);
const removeBtn=itemEl.querySelector(".remove-btn");

removeBtn.onclick=()=>{
cart.splice(index,1);
localStorage.setItem("cart",JSON.stringify(cart));
renderCart();
updateCartCount();
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
window.location.href="login.html";
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