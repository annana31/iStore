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

    let cart = JSON.parse(localStorage.getItem("cart")) || [];

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
    // ADD TO CART
    // =========================
    if (products.length > 0) {
        products.forEach(product => {
            const button = product.querySelector("button");

            button.addEventListener("click", () => {
                const name = product.querySelector("h3").textContent;
                const priceText = product.querySelector("p").textContent;
                const price = parseFloat(priceText.replace("$", ""));
                const image = product.querySelector("img").src;

                const existingItem = cart.find(item => item.name === name);

                if (existingItem) {
                    existingItem.quantity += 1;
                } else {
                    cart.push({ name, price, image, quantity: 1 });
                }

                localStorage.setItem("cart", JSON.stringify(cart));
                updateCartCount();
            });
        });
    }

    // =========================
    // CART PAGE RENDER
    // =========================
    if (cartItemsContainer) renderCart();

    function renderCart() {
        cartItemsContainer.innerHTML = "";
        let subtotal = 0;

        cart.forEach((item, index) => {
            subtotal += item.price * item.quantity;

            cartItemsContainer.innerHTML += `
                <div class="cart-item">
                    <img src="${item.image}" alt="${item.name}">
                    <div class="item-details">
                        <h3>${item.name}</h3>
                        <div class="item-controls">
                            <select onchange="updateQuantity(${index}, this.value)">
                                ${[1,2,3,4,5].map(num =>
                                    `<option value="${num}" ${num == item.quantity ? "selected" : ""}>${num}</option>`
                                ).join("")}
                            </select>
                            <button class="remove-btn" onclick="removeItem(${index})">Remove</button>
                        </div>
                    </div>
                    <h3>$${(item.price * item.quantity).toFixed(2)}</h3>
                </div>
            `;
        });

        updateTotals(subtotal);
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

    // =========================
    // GLOBAL FUNCTIONS
    // =========================
    window.updateQuantity = function(index, quantity) {
        cart[index].quantity = parseInt(quantity);
        localStorage.setItem("cart", JSON.stringify(cart));
        renderCart();
        updateCartCount();
    };

    window.removeItem = function(index) {
        cart.splice(index, 1);
        localStorage.setItem("cart", JSON.stringify(cart));
        renderCart();
        updateCartCount();
    };

    // =========================
    // CART ICON → REDIRECT
    // =========================
    if (cartIcon) {
        cartIcon.addEventListener("click", () => {
            window.location.href = "cart.html";
        });
    }

    // =========================
    // SEARCH
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

    // =========================
    // NAV CATEGORY FILTER
    // =========================
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
    // PROFILE DROPDOWN + LOGOUT
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
            window.location.href = "index.html";
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
                    top: 0; left: 0; right: 0; bottom: 0;
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

});