document.addEventListener('DOMContentLoaded', () => {
    // 1. Preloader Fix
    const loader = document.getElementById('preloader');
    if (loader) {
        setTimeout(() => {
            loader.style.opacity = '0';
            setTimeout(() => { loader.style.display = 'none'; }, 500);
        }, 2000); 
    }

    // 2. AJAX Add to Cart Logic
    const cartForms = document.querySelectorAll('.cart-form');
    cartForms.forEach(form => {
        form.addEventListener('submit', async (e) => {
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
                const response = await fetch('cart.php', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                
                const result = await response.text();
                
                if (result.trim() === "success") {
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

    // 3. Apply saved theme on load
    if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark-mode');
        const modeBtn = document.getElementById('mode-btn');
        if(modeBtn) modeBtn.innerHTML = '<i class="fa-solid fa-sun"></i>';
    }
});

/**
 * Product Details Modal Logic
 * Updated to handle description and images
 */
function openDetails(name, img, price, stock, desc) {
    const modal = document.getElementById('detailsModal');
    if (modal) {
        document.getElementById('m-name').innerText = name;
        document.getElementById('m-img').src = img;
        document.getElementById('m-price').innerText = "Rs. " + price;
        document.getElementById('m-stock').innerText = "In Stock: " + stock + " units";
        
        // Ensure the description field exists in your index.php modal
        const descElem = document.getElementById('m-desc');
        if (descElem) descElem.innerText = desc;
        
        modal.style.display = 'flex';
    }
}

function closeDetails() {
    const modal = document.getElementById('detailsModal');
    if (modal) modal.style.display = 'none';
}

// Close modal when clicking outside of it
window.onclick = function(event) {
    const modal = document.getElementById('detailsModal');
    if (event.target == modal) {
        closeDetails();
    }
}

/**
 * Live Search Logic
 */
function liveSearch() {
    let filter = document.getElementById('product-search').value.toLowerCase();
    let products = document.querySelectorAll('.product');

    products.forEach(product => {
        let name = product.querySelector('h3').innerText.toLowerCase();
        product.style.display = name.includes(filter) ? "" : "none";
    });
}

/**
 * Dark Mode Toggle
 */
function toggleDarkMode() {
    const isDark = document.body.classList.toggle('dark-mode');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
    const modeBtn = document.getElementById('mode-btn');
    if(modeBtn) modeBtn.innerHTML = isDark ? '<i class="fa-solid fa-sun"></i>' : '<i class="fa-solid fa-moon"></i>';
}

/**
 * Profile Management (Restricted for Guests via UI)
 */
async function saveProfileData() {
    const phone = document.getElementById('u-phone').value;
    const address = document.getElementById('u-address').value;
    const formData = new FormData();
    formData.append('update_profile', 'true');
    formData.append('phone', phone);
    formData.append('address', address);

    try {
        const res = await fetch('update_profile.php', { method: 'POST', body: formData });
        const result = await res.text();
        if(result.trim() === 'success') {
            showToast("✅ Profile updated successfully");
        } else {
            showToast("❌ Failed to update profile");
        }
    } catch (err) {
        showToast("❌ Error connecting to server");
    }
}

async function uploadProfilePic() {
    const fileInput = document.getElementById('p-upload');
    if (fileInput.files.length === 0) return;

    const formData = new FormData();
    formData.append('profile_pic', fileInput.files[0]);
    
    try {
        const res = await fetch('update_profile.php', { method: 'POST', body: formData });
        location.reload(); 
    } catch (err) {
        showToast("❌ Error uploading photo");
    }
}

/**
 * Toast Notification System
 */
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
}function showToast(msg) {
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.innerText = msg;
    document.body.appendChild(toast);
    setTimeout(() => { 
        toast.style.opacity = '0';
        setTimeout(() => {
            toast.remove(); 
            location.reload();
        }, 500);
    }, 2000);
}

function removeProfilePic() {
    if(confirm("Remove your profile picture?")) {
        let formData = new FormData();
        formData.append('remove_photo', '1');
        fetch('update_profile.php', { method: 'POST', body: formData })
        .then(res => res.text())
        .then(data => showToast(data));
    }
}

function uploadProfilePic() {
    let file = document.getElementById('p-upload').files[0];
    if(!file) return;
    let formData = new FormData();
    formData.append('profile_pic', file);
    fetch('update_profile.php', { method: 'POST', body: formData })
    .then(res => res.text())
    .then(data => showToast(data));
}

function saveProfileData() {
    let formData = new FormData();
    formData.append('update_profile', '1');
    formData.append('phone', document.getElementById('u-phone').value);
    formData.append('address', document.getElementById('u-address').value);
    fetch('update_profile.php', { method: 'POST', body: formData })
    .then(res => res.text())
    .then(data => showToast(data));
}
document.addEventListener('DOMContentLoaded', function() {
    const cartCount = document.getElementById('cart-count');

    // Add to Cart AJAX
    document.querySelectorAll('button[name="add"]').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const form = this.closest('form');
            const formData = new FormData(form);
            formData.append('add', '1');

            fetch('cart.php', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.text())
            .then(data => {
                if(data.trim() === 'success') {
                    // Update and Animate Counter
                    let currentNum = parseInt(cartCount.innerText) || 0;
                    cartCount.innerText = currentNum + 1;
                    cartCount.classList.add('pulse-anim');
                    setTimeout(() => cartCount.classList.remove('pulse-anim'), 500);

                    button.innerHTML = '<i class="fa-solid fa-check"></i> Added!';
                    setTimeout(() => {
                        button.innerHTML = '<i class="fa-solid fa-cart-plus"></i> Add to Cart';
                    }, 2000);
                }
            });
        });
    });
});

// Remove Specific Item
function removeItem(cid) {
    if(confirm('Remove this item?')) {
        const formData = new FormData();
        formData.append('remove_id', cid);

        fetch('cart.php', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.text())
        .then(data => {
            if(data.trim() === 'deleted') {
                location.reload(); 
            }
        });
    }
}

// Clear Entire Cart
function clearCart() {
    if(confirm('Are you sure you want to empty your cart?')) {
        const formData = new FormData();
        formData.append('clear_cart', '1');

        fetch('cart.php', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.text())
        .then(data => {
            if(data.trim() === 'cleared') {
                location.reload();
            }
        });
    }
}