document.addEventListener("DOMContentLoaded", () => {
  const products = document.querySelectorAll(".product");
  const searchInput = document.querySelector("input[type='search']");
  const cartCount = document.querySelector(".count");
  const cartIcon = document.querySelector(".cart");
  const profileDropdown = document.querySelector(".profile-dropdown");
  const logoutBtn = document.getElementById("logout-btn");
  const navItems = document.querySelectorAll("nav ul li");
  const cartItemsContainer = document.getElementById("cart-items");
  const checkoutButtons = document.querySelectorAll(".checkout-btn");

// =========================
// LOAD & NORMALIZE CART
// =========================
let cart = JSON.parse(localStorage.getItem("cart")) || [];
cart = cart.map(item => ({
  name: item.name || item.product_name || "Unknown",
  price: item.price || parseFloat(item.product_price) || 0,
  image: item.image || item.product_image || "",
  quantity: item.quantity || 1,
  storage: item.storage || "", // ✅ preserve storage
  color: item.color || ""      // ✅ preserve color
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

  // STORAGE & COLOR SELECTS
  const storageSelect = document.getElementById("storageSelect");
  const colorSelect = document.getElementById("colorSelect");

  const deviceColors = {
    phones: ["Midnight", "Starlight", "Product Red", "Blue", "Purple", "Green", "Silver", "Gold", "Deep Purple", "Space Black", "Sage", "Ocean Blue"],
    ipads: ["Space Gray", "Silver", "Green", "Sky Blue", "Pink", "Purple", "Yellow"],
    macs: ["Silver", "Space Gray", "Midnight", "Starlight", "Blue", "Pink", "Purple"]
  };

  // OPEN PANEL
  document.querySelectorAll(".view-item-btn").forEach(button => {
    button.addEventListener("click", e => {
      e.preventDefault();
      e.stopPropagation();
      const product = button.closest(".product");
      if (!product) return;

      const category = product.dataset.category;

      // Reset visibility
      colorSelect.parentElement.style.display = "none";
      storageSelect.parentElement.style.display = "none";

      // PHONES, IPADS, MACS → color + storage
      if (category === "phones" || category === "ipads" || category === "macs") {
        colorSelect.parentElement.style.display = "block";
        storageSelect.parentElement.style.display = "block";
        colorSelect.innerHTML = deviceColors[category].map(c => `<option>${c}</option>`).join("");
        storageSelect.innerHTML = `<option>128GB</option><option>256GB</option><option>512GB</option><option>1TB</option>`;
        storageSelect.value = "128GB";
      }

      // APPLE WATCH → only storage
      else if (category === "watches") {
        colorSelect.parentElement.style.display = "none";
        storageSelect.parentElement.style.display = "block";
        storageSelect.innerHTML = `<option>128GB</option><option>256GB</option>`;
        storageSelect.value = "128GB";
      }

      // ACCESSORIES → only quantity
      else if (category === "accessories") {
        colorSelect.parentElement.style.display = "none";
        storageSelect.parentElement.style.display = "none";
      }

      // Set panel product info
      panelImage.src = product.querySelector("img")?.src || "";
      panelName.innerText = product.querySelector("h3")?.innerText || "";
      panelPrice.innerText = product.querySelector("p")?.innerText || "";
      quantityInput.value = 1;

      panel.classList.add("active");
      document.body.classList.add("panel-open");
    });
  });

  // CLOSE PANEL
  if (closeBtn) closeBtn.addEventListener("click", closePanel);
  function closePanel() {
    panel.classList.remove("active");
    document.body.classList.remove("panel-open");
  }

  document.addEventListener("click", e => {
    if (panel.classList.contains("active") &&
        !panel.contains(e.target) &&
        !e.target.closest(".view-item-btn")) {
      closePanel();
    }
  });

  document.addEventListener("keydown", e => {
    if (e.key === "Escape" && panel.classList.contains("active")) {
      closePanel();
    }
  });

panelAddBtn.addEventListener("click", e => {
  e.preventDefault();
  e.stopPropagation();

  const name = panelName.innerText;
  const price = parseFloat(panelPrice.innerText.replace("$", "")) || 0;
  const image = panelImage.src;
  const quantity = parseInt(quantityInput.value) || 1;

  // If selects are hidden, default to empty string
  const storage = storageSelect.parentElement.style.display !== "none" ? storageSelect.value : "";
  const color = colorSelect.parentElement.style.display !== "none" ? colorSelect.value : "";

  // Check if this exact item exists in cart
  const existingItem = cart.find(item =>
    item.name === name &&
    item.storage === storage &&
    item.color === color
  );

  if (existingItem) {
    existingItem.quantity += quantity;
  } else {
    cart.push({
      name,
      price,
      image,
      quantity,
      storage,  // always store
      color     // always store
    });
  }

  localStorage.setItem("cart", JSON.stringify(cart)); // ✅ persistent
  updateCartCount();
  showNotification(`${name} added to cart!`);
  closePanel();
});

  // =========================
  // CART PAGE RENDER
  // =========================
  if (cartItemsContainer) renderCart();

function renderCart() {
  cartItemsContainer.innerHTML = "";
  if (cart.length === 0) {
    cartItemsContainer.innerHTML = "<p>Your cart is empty.</p>";
    updateTotals(0);
    return;
  }

  let subtotal = 0;
  cart.forEach((item, index) => {
    subtotal += item.price * item.quantity;

    cartItemsContainer.innerHTML += `
      <div class="cart-item" data-index="${index}">
        <img src="${item.image}" alt="${item.name}">
        <div class="item-details">
          <h3>${item.name}</h3>
          ${item.storage ? `<p>Storage: ${item.storage}</p>` : ''}
          ${item.color ? `<p>Color: ${item.color}</p>` : ''}
          <p>Price: $${item.price.toFixed(2)}</p>
          <p>Quantity: <input type="number" class="quantity-input" value="${item.quantity}" min="1" readonly></p>
          <button class="remove-btn">Remove</button>
        </div>
        <h3 class="item-total">$${(item.price * item.quantity).toFixed(2)}</h3>
      </div>
    `;
  });

    updateTotals(subtotal);
    attachCartItemEvents();
  }

  function updateTotals(subtotal) {
    const total = subtotal;
    const subtotalEl = document.getElementById("subtotal");
    const finalTotalEl = document.getElementById("final-total");
    const heroTotalEl = document.getElementById("cart-total");

    if (subtotalEl) subtotalEl.textContent = "$" + subtotal.toFixed(2);
    if (finalTotalEl) finalTotalEl.textContent = total.toFixed(2);
    if (heroTotalEl) heroTotalEl.textContent = total.toFixed(2);
  }

  function attachCartItemEvents() {
    document.querySelectorAll(".cart-item").forEach(itemEl => {
      const index = parseInt(itemEl.dataset.index);
      const decreaseBtn = itemEl.querySelector(".decrease-btn");
      const increaseBtn = itemEl.querySelector(".increase-btn");
      const quantityInput = itemEl.querySelector(".quantity-input");
      const removeBtn = itemEl.querySelector(".remove-btn");

      decreaseBtn.onclick = () => {
        if (cart[index].quantity > 1) cart[index].quantity -= 1;
        quantityInput.value = cart[index].quantity;
        updateCartItemTotal(index, itemEl);
      };

      increaseBtn.onclick = () => {
        cart[index].quantity += 1;
        quantityInput.value = cart[index].quantity;
        updateCartItemTotal(index, itemEl);
      };

      quantityInput.onchange = () => {
        let val = parseInt(quantityInput.value);
        if (isNaN(val) || val < 1) val = 1;
        cart[index].quantity = val;
        quantityInput.value = val;
        updateCartItemTotal(index, itemEl);
      };

      removeBtn.onclick = () => {
        cart.splice(index, 1);
        localStorage.setItem("cart", JSON.stringify(cart));
        renderCart();
        updateCartCount();
      };
    });

    localStorage.setItem("cart", JSON.stringify(cart));
    updateCartCount();
  }

  function updateCartItemTotal(index, itemEl) {
    const totalEl = itemEl.querySelector(".item-total");
    totalEl.textContent = "$" + (cart[index].price * cart[index].quantity).toFixed(2);
    updateTotals(cart.reduce((sum, item) => sum + item.price * item.quantity, 0));
    localStorage.setItem("cart", JSON.stringify(cart));
    updateCartCount();
  }

  if (cartIcon) {
    cartIcon.addEventListener("click", () => {
      window.location.href = "cart.html";
    });
  }

  // =========================
  // SEARCH & CATEGORY FILTER
  // =========================
  if (searchInput) {
    searchInput.addEventListener("input", () => {
      const searchValue = searchInput.value.toLowerCase();
      products.forEach(product => {
        const name = product.querySelector("h3").textContent.toLowerCase();
        product.style.display = name.includes(searchValue) ? "block" : "none";
      });
    });
  }

  navItems.forEach(item => {
    item.addEventListener("click", () => {
      navItems.forEach(i => i.classList.remove("active"));
      item.classList.add("active");
      const category = item.dataset.category;
      products.forEach(product => {
        product.style.display = category === "all" || product.dataset.category === category ? "block" : "none";
      });
    });
  });

  // =========================
  // PROFILE DROPDOWN & LOGOUT
  // =========================
  if (profileDropdown) {
    profileDropdown.addEventListener("click", e => {
      e.stopPropagation();
      profileDropdown.classList.toggle("active");
    });
    document.addEventListener("click", () => {
      profileDropdown.classList.remove("active");
    });
  }

  if (logoutBtn) {
    logoutBtn.addEventListener("click", () => {
      localStorage.removeItem("cart");
      window.location.href = "login.html";
    });
  }

  // =========================
  // CHECKOUT MODAL
  // =========================
  checkoutButtons.forEach(btn => {
    btn.addEventListener("click", () => {
      if (cart.length === 0) {
        alert("Your cart is empty!");
        return;
      }

      let modal = document.getElementById("order-modal");
      if (!modal) {
        modal = document.createElement("div");
        modal.id = "order-modal";
        modal.style.cssText = `
          position: fixed;
          top: 0;
          left: 0;
          right: 0;
          bottom: 0;
          display: flex;
          align-items: center;
          justify-content: center;
          background: rgba(0,0,0,0.5);
          z-index: 9999;
        `;
        modal.innerHTML = `
          <div style="background:#fff; padding:30px; border-radius:12px; text-align:center; max-width:300px;">
            <h2>✅ Order Placed!</h2>
            <p>Thank you for your purchase.</p>
            <button id="order-ok-btn" style="margin-top:20px; padding:10px 20px; border:none; border-radius:6px; background:#111; color:#fff; cursor:pointer;">OK</button>
          </div>
        `;
        document.body.appendChild(modal);
      }

      modal.style.display = "flex";
      const okBtn = document.getElementById("order-ok-btn");
      okBtn.onclick = () => {
        modal.style.display = "none";
        cart = [];
        localStorage.setItem("cart", JSON.stringify(cart));
        renderCart();
        updateCartCount();
        updateTotals(0);
      };
    });
  });

  // =========================
  // NOTIFICATIONS
  // =========================
  const notificationContainer = document.getElementById("notification-container");
  let notifTimeout;

  function showNotification(message) {
    let notif = notificationContainer.querySelector(".notification");
    if (!notif) {
      notif = document.createElement("div");
      notif.className = "notification";
      notificationContainer.appendChild(notif);
    }
    notif.textContent = message;
    notif.classList.add("show");

    if (notifTimeout) clearTimeout(notifTimeout);
    notifTimeout = setTimeout(() => {
      notif.classList.remove("show");
    }, 2500);
  }
});