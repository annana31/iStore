document.addEventListener("DOMContentLoaded", () => {

    const products = document.querySelectorAll(".product");
    const searchInput = document.querySelector("input[type='search']");
    const cartCount = document.querySelector(".count");
    const cartIcon = document.querySelector(".cart");
    const profileIcon = document.querySelector(".profile");
    const navItems = document.querySelectorAll("nav ul li");

    let cart = [];

    // -----------------------
    // ADD TO CART
    // -----------------------
    products.forEach(product => {
        const button = product.querySelector("button");
        button.addEventListener("click", () => {
            const name = product.querySelector("h3").textContent;
            const price = product.querySelector("p").textContent;

            cart.push({ name, price });
            updateCartCount();
        });
    });

    function updateCartCount() {
        cartCount.textContent = cart.length;
    }

    // -----------------------
    // CART MODAL
    // -----------------------
    cartIcon.addEventListener("click", () => {
        if (cart.length === 0) {
            alert("Your cart is empty!");
            return;
        }

        let message = "Your Cart:\n\n";
        cart.forEach((item, index) => {
            message += `${index + 1}. ${item.name} - ${item.price}\n`;
        });

        message += "\nClick OK to clear cart.";

        if (confirm(message)) {
            cart = [];
            updateCartCount();
        }
    });

    // -----------------------
    // SEARCH FUNCTION
    // -----------------------
    searchInput.addEventListener("input", () => {
        const searchValue = searchInput.value.toLowerCase();

        products.forEach(product => {
            const name = product.querySelector("h3").textContent.toLowerCase();

            if (name.includes(searchValue)) {
                product.style.display = "block";
            } else {
                product.style.display = "none";
            }
        });
    });

    // -----------------------
    // NAVIGATION MENU
    // -----------------------
    navItems.forEach(item => {
        item.addEventListener("click", () => {
            navItems.forEach(i => i.classList.remove("active"));
            item.classList.add("active");

            alert(item.textContent + " section clicked!");
        });
    });

    // -----------------------
    // PROFILE ICON (LOGOUT)
    // -----------------------
    profileIcon.addEventListener("click", () => {
        if (confirm("Do you want to logout?")) {
            window.location.href = "index.html";
        }
    });

});