document.addEventListener('DOMContentLoaded', () => {
    // 1. Preloader Fix
    const loader = document.getElementById('preloader');
    if (loader) {
        setTimeout(() => {
            loader.style.opacity = '0';
            setTimeout(() => { loader.style.display = 'none'; }, 500);
        }, 2000); 
    }

    // 2. Fixed AJAX Add to Cart
    const cartForms = document.querySelectorAll('.cart-form');
    cartForms.forEach(form => {
        form.addEventListener('submit', async (e) => {
            // CRITICAL: This stops the page from refreshing
            e.preventDefault();

            const pid = form.querySelector('input[name="pid"]').value;
            const stockSpan = document.getElementById(`stock-${pid}`);
            const availableStock = parseInt(stockSpan.innerText);

            if (availableStock <= 0) {
                showToast("❌ This item is out of stock.");
                return;
            }

            let userQty = prompt(`Enter quantity (Available: ${availableStock}):`, "1");
            if (userQty === null) return;
            userQty = parseInt(userQty);

            if (isNaN(userQty) || userQty <= 0 || userQty > availableStock) {
                showToast("⚠️ Invalid quantity or not enough stock.");
                return;
            }

            const formData = new FormData();
            formData.append('pid', pid);
            formData.append('qty', userQty);
            formData.append('add', 'true');

            try {
                // Sending request to cart.php
                const response = await fetch('cart.php', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                
                const result = await response.text();
                
                // Your cart.php returns "success" on a valid AJAX post
                if (result.trim() === "success") {
                    // Update Cart Badge UI
                    const badge = document.getElementById('cart-count');
                    if (badge) {
                        let currentCount = parseInt(badge.innerText) || 0;
                        badge.innerText = currentCount + userQty;
                        badge.style.transform = "scale(1.4)";
                        setTimeout(() => { badge.style.transform = "scale(1)"; }, 300);
                    }
                    showToast(`✅ Added ${userQty} item(s) to cart`);
                } else if (result.trim() === "login_required") {
                    showToast("🔑 Please login first");
                    setTimeout(() => window.location.href = 'login.php', 1500);
                }
            } catch (err) {
                showToast("❌ Connection error");
            }
        });
    });
});

// Toast System
function showToast(message) {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        document.body.appendChild(container);
    }
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.innerText = message;
    container.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 500);
    }, 3000);
}

// Item Removal Logic (From previous step)
async function removeItem(cartId) {
    if (!confirm("Are you sure?")) return;
    const formData = new FormData();
    formData.append('remove_id', cartId);
    const response = await fetch('cart.php', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });
    const result = await response.text();
    if (result.trim() === "deleted") {
        document.getElementById(`row-${cartId}`)?.remove();
        updateGrandTotal();
    }
}

function updateGrandTotal() {
    let total = 0;
    document.querySelectorAll('.responsive-table tbody tr').forEach(row => {
        const val = parseInt(row.cells[3].innerText.replace(/[^0-9]/g, ''));
        if (!isNaN(val)) total += val;
    });
    const display = document.getElementById('grand-total');
    if (display) display.innerText = "Rs. " + total.toLocaleString();
}
document.addEventListener('DOMContentLoaded', () => {
    // Apply saved theme on load
    if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark-mode');
        document.getElementById('mode-btn').innerHTML = '<i class="fa-solid fa-sun"></i>';
    }
});

function toggleDarkMode() {
    const isDark = document.body.classList.toggle('dark-mode');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
    document.getElementById('mode-btn').innerHTML = isDark ? '<i class="fa-solid fa-sun"></i>' : '<i class="fa-solid fa-moon"></i>';
}

async function saveProfileData() {
    const phone = document.getElementById('u-phone').value;
    const address = document.getElementById('u-address').value;
    const formData = new FormData();
    formData.append('update_profile', 'true');
    formData.append('phone', phone);
    formData.append('address', address);

    const res = await fetch('update_profile.php', { method: 'POST', body: formData });
    const result = await res.text();
    if(result.trim() === 'success') showToast("✅ Profile updated in SQL");
}

async function uploadProfilePic() {
    const file = document.getElementById('p-upload').files[0];
    const formData = new FormData();
    formData.append('profile_pic', file);
    
    // This requires a PHP handler for the image move
    const res = await fetch('update_profile.php', { method: 'POST', body: formData });
    location.reload(); // Refresh to show new photo
}
function liveSearch() {
    // Get the search input value and convert to lowercase
    let filter = document.getElementById('product-search').value.toLowerCase();
    
    // Get all product cards
    let products = document.querySelectorAll('.product');

    products.forEach(product => {
        // Find the product name inside the card
        let name = product.querySelector('h3').innerText.toLowerCase();
        
        // If the name includes the searched letters, show it; otherwise, hide it
        if (name.includes(filter)) {
            product.style.display = ""; // Show
        } else {
            product.style.display = "none"; // Hide
        }
    });
}