document.addEventListener("DOMContentLoaded", () => {

    const products = document.querySelectorAll(".product");
    const searchInput = document.querySelector("input[type='search']");
    const cartCount = document.querySelector(".count");
    const cartIcon = document.querySelector(".cart");
    const profileDropdown = document.querySelector(".profile-dropdown");
    const dropdownMenu = document.querySelector(".dropdown-menu");
    const logoutBtn = document.getElementById("logout-btn");
    const navItems = document.querySelectorAll("nav ul li");

    const cartItemsContainer = document.getElementById("cart-items");

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
    // ADD TO CART (STORE PAGE)
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
                    cart.push({
                        name,
                        price,
                        image,
                        quantity: 1
                    });
                }

                localStorage.setItem("cart", JSON.stringify(cart));
                updateCartCount();
            });
        });
    }

    // =========================
    // CART PAGE RENDER
    // =========================
    if (cartItemsContainer) {
        renderCart();
    }

    function renderCart() {
        cartItemsContainer.innerHTML = "";

        let subtotal = 0;

        cart.forEach((item, index) => {
            subtotal += item.price * item.quantity;

            cartItemsContainer.innerHTML += `
                <div class="cart-item">
                    <img src="${item.image}">
                    <div class="item-details">
                        <h3>${item.name}</h3>
                        <div class="item-controls">
                            <select onchange="updateQuantity(${index}, this.value)">
                                ${[1,2,3,4,5].map(num =>
                                    `<option value="${num}" ${num == item.quantity ? "selected" : ""}>${num}</option>`
                                ).join("")}
                            </select>
                            <button class="remove-btn" onclick="removeItem(${index})">
                                Remove
                            </button>
                        </div>
                    </div>
                    <h3>$${(item.price * item.quantity).toFixed(2)}</h3>
                </div>
            `;
        });

        updateTotals(subtotal);
    }

    function updateTotals(subtotal) {
        const vat = subtotal * 0.05;
        const total = subtotal + vat;

        const subtotalEl = document.getElementById("subtotal");
        const vatEl = document.getElementById("vat");
        const finalTotalEl = document.getElementById("final-total");
        const heroTotalEl = document.getElementById("cart-total");

        if (subtotalEl) subtotalEl.textContent = "$" + subtotal.toFixed(2);
        if (vatEl) vatEl.textContent = "$" + vat.toFixed(2);
        if (finalTotalEl) finalTotalEl.textContent = total.toFixed(2);
        if (heroTotalEl) heroTotalEl.textContent = total.toFixed(2);
    }

    // Make functions global so HTML can access them
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
    // NAV
    // =========================
    navItems.forEach(item => {
        item.addEventListener("click", () => {
            navItems.forEach(i => i.classList.remove("active"));
            item.classList.add("active");
        });
    });

    // =========================
    // PROFILE DROPDOWN TOGGLE + LOGOUT
    // =========================
    if (profileDropdown && dropdownMenu) {
        profileDropdown.addEventListener("click", (e) => {
            e.stopPropagation();
            dropdownMenu.style.display = dropdownMenu.style.display === "block" ? "none" : "block";
        });
    }

    if (logoutBtn) {
        logoutBtn.addEventListener("click", () => {
            window.location.href = "index.html";
        });
    }

    // Close dropdown if click outside
    document.addEventListener("click", () => {
        if (dropdownMenu) {
            dropdownMenu.style.display = "none";
        }
    });

});