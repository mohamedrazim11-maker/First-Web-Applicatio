<?php
session_start(); 
include "includes/db.php";

// Check if entering as a guest via URL
$is_guest = !isset($_SESSION['user']) && isset($_GET['guest']);

// If not logged in AND not a guest, redirect to login
if(!isset($_SESSION['user']) && !$is_guest) {
    header("Location: login.php");
    exit();
}

// Get selected category from URL
$selected_category = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';

// Data fetching logic
if(isset($_SESSION['user'])) {
    $uid = $_SESSION['user']; 
    $u_query = mysqli_query($conn, "SELECT * FROM users WHERE id = '$uid'");
    $u_data = mysqli_fetch_assoc($u_query);
    $profile_pic = !empty($u_data['profile_pic']) ? "images/profiles/".$u_data['profile_pic'] : "images/default-avatar.png";
    $user_name = $u_data['name'];
    $user_email = $u_data['email'];
    $phone = isset($u_data['phone']) ? $u_data['phone'] : '';
    $address = isset($u_data['address']) ? $u_data['address'] : '';
} else {
    // Default values for Guest users
    $profile_pic = "images/default-avatar.png";
    $user_name = "Guest Visitor";
    $user_email = "Login to save profile";
    $phone = "N/A";
    $address = "N/A";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Premium Computer Shop | Razim Tech</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="js/main.js" defer></script>
    <style>
        #preloader {
            position: fixed; top: 0; left: 0; width: 100%; height: 100vh;
            background: #0f172a; display: flex; flex-direction: column;
            justify-content: center; align-items: center; z-index: 10000;
        }
        .loader-logo {
            color: #2563eb; font-size: 3rem; font-weight: 800;
            letter-spacing: 5px; margin-bottom: 20px; animation: pulse 1.5s infinite;
        }
        .spinner {
            width: 50px; height: 50px; border: 5px solid rgba(255,255,255,0.1);
            border-top: 5px solid #2563eb; border-radius: 50%; animation: spin 1s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        @keyframes pulse { 0%, 100% { transform: scale(1); opacity: 1; } 50% { transform: scale(0.95); opacity: 0.7; } }
        
        .product-img-link { position: relative; cursor: pointer; display: block; overflow: hidden; border-radius: 8px; }

        .btn-add {
            width: 100%; background: linear-gradient(135deg, var(--primary), #1d4ed8);
            color: white; border: none; padding: 12px; border-radius: 10px;
            font-weight: 600; cursor: pointer; display: flex; align-items: center;
            justify-content: center; gap: 8px; transition: all 0.3s ease; margin-top: 10px;
        }
        .btn-add:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(37, 99, 235, 0.3); }

        .modal { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); align-items: center; justify-content: center; }
        .modal-content { background: white; padding: 30px; border-radius: 20px; width: 90%; max-width: 500px; text-align: center; position: relative; }
        .close-modal { position: absolute; top: 15px; right: 20px; font-size: 25px; cursor: pointer; color: #64748b; }

        /* Category Styles */
        .category-nav { 
        display: flex; 
        gap: 12px; 
        overflow-x: auto; 
        padding: 15px 5px; 
        margin-bottom: 25px; 
        scrollbar-width: none; 
    }
    .category-nav::-webkit-scrollbar { display: none; }
    
    .cat-pill { 
        padding: 10px 22px; 
        background: rgba(255, 255, 255, 0.1); /* Brighter glass effect */
        border: 1px solid rgba(255, 255, 255, 0.2); 
        border-radius: 50px; 
        color: #6366f1; /* Pure white text */
        text-decoration: none; 
        white-space: nowrap; 
        transition: all 0.3s ease; 
        font-size: 14px;
        font-weight: 500;
        box-shadow: 0 4px 6px rgba(83, 8, 81, 0.1);
    }
    
    .cat-pill:hover { 
        background: rgba(255, 255, 255, 0.2); 
        transform: translateY(-2px);
    }

    .cat-pill.active { 
        background: #6366f1; /* Bright Indigo/Blue */
        border-color: #818cf8;
        color: white;
        box-shadow: 0 0 15px rgba(99, 102, 241, 0.4);
    }
    </style>
</head>
<body>

<div id="preloader">
    <div class="loader-logo">RAZIM TECH</div>
    <div class="spinner"></div>
</div>

<header>
    <div class="logo">
        <h1 class="logo-text">Razim <span style="color:var(--dark)">SHOP</span></h1>
    </div>
    <nav style="display: flex; align-items: center;">
        <a href="index.php"><i class="fa-solid fa-house"></i> Home</a>
        <?php if(!$is_guest): ?>
            <a href="cart.php" class="cart-link">
                <i class="fa-solid fa-cart-shopping"></i> Cart 
            <span id="cart-count">
    <?php 
    if(isset($_SESSION['user'])) {
        $uid = $_SESSION['user'];
        // We count the number of distinct rows to see how many items are actually there
        $count_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM cart WHERE user_id = '$uid'");
        $count_row = mysqli_fetch_assoc($count_res);
        echo ($count_row['total'] > 0) ? $count_row['total'] : 0;
    } else {
        echo 0;
    }
    ?>
</span>
            </a>
        <?php endif; ?>
        <button onclick="toggleDarkMode()" id="mode-btn" class="nav-icon-btn"><i class="fa-solid fa-moon"></i></button>
        <div class="settings-menu">
            <button class="settings-btn"><i class="fa-solid fa-gear"></i></button>
          <div class="settings-content">
    <div class="profile-header">
        <div class="profile-avatar-container">
            <img src="<?php echo $profile_pic; ?>" id="p-preview" alt="Profile">
            <?php if(!$is_guest): ?>
            <div class="avatar-overlay">
                <button onclick="document.getElementById('p-upload').click()" title="Upload Photo"><i class="fa-solid fa-camera"></i></button>
                <?php if(!empty($u_data['profile_pic'])): ?>
                    <button onclick="removeProfilePic()" class="btn-remove-photo" title="Remove Photo"><i class="fa-solid fa-trash"></i></button>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        <div class="user-info-display">
            <strong class="user-name-label"><?php echo htmlspecialchars($user_name); ?></strong>
            <span class="user-email-label"><?php echo htmlspecialchars($user_email); ?></span>
        </div>
        <?php if(!$is_guest): ?>
            <input type="file" id="p-upload" style="display:none" onchange="uploadProfilePic()">
        <?php endif; ?>
    </div>
    
    <?php if(!$is_guest): ?>
        <div class="profile-form-body">
            <div class="input-group">
                <label><i class="fa-solid fa-phone"></i> Contact Number</label>
                <input type="text" id="u-phone" value="<?php echo htmlspecialchars($phone); ?>" placeholder="Enter phone...">
            </div>
            <div class="input-group">
                <label><i class="fa-solid fa-location-dot"></i> Delivery Address</label>
                <textarea id="u-address" rows="2" placeholder="Enter address..."><?php echo htmlspecialchars($address); ?></textarea>
            </div>
            <button onclick="saveProfileData()" class="btn-save">Update Profile</button>
        </div>
        <hr>
        <a href="logout.php" class="logout-link"><i class="fa-solid fa-power-off"></i> Logout</a>
    <?php else: ?>
        <hr>
        <p class="guest-msg">Login to customize your profile</p>
        <a href="login.php" class="logout-link login-link"><i class="fa-solid fa-right-to-bracket"></i> Login Now</a>
    <?php endif; ?>
</div>
    </nav>
</header>

<div class="container">
    <div class="search-section" style="margin-bottom: 30px;">
        <div class="search-wrapper">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="text" id="product-search" placeholder="Search tech..." onkeyup="liveSearch()">
        </div>
    </div>

    <div class="category-nav">
        <a href="index.php<?php echo $is_guest ? '?guest=1' : ''; ?>" class="cat-pill <?php echo $selected_category == '' ? 'active' : ''; ?>">All Products</a>
        <?php
        $cat_query = mysqli_query($conn, "SELECT * FROM categories");
        while($cat = mysqli_fetch_assoc($cat_query)) {
            $active_class = ($selected_category == $cat['id']) ? 'active' : '';
            $guest_param = $is_guest ? '&guest=1' : '';
            echo "<a href='index.php?category={$cat['id']}{$guest_param}' class='cat-pill {$active_class}'>{$cat['category_name']}</a>";
        }
        ?>
    </div>

   <div class="products" id="products-grid">
        <?php
        $sql = "SELECT * FROM products" . ($selected_category != '' ? " WHERE category_id = '$selected_category'" : "");
        $q = mysqli_query($conn, $sql);
        while($p = mysqli_fetch_assoc($q)) {
            $imgPath = (!empty($p['image'])) ? "images/".$p['image'] : "images/no-image.png";
            $guest_url = $is_guest ? "&guest=1" : "";
        ?>
            <div class="product">
                <a href="view_product.php?id=<?php echo $p['id'] . $guest_url; ?>" class="product-img-link">
                    <img src="<?php echo $imgPath; ?>" alt="Product">
                </a>
                <div class="product-info">
                    <h3><?php echo $p['name']; ?></h3>
                    <p class="price">Rs. <?php echo number_format($p['price']); ?></p>
                </div>
                <?php if(!$is_guest): ?>
                    <form method="POST" action="cart.php">
                        <input type="hidden" name="pid" value="<?php echo $p['id']; ?>">
                        <button type="submit" name="add" class="btn-add"><i class="fa-solid fa-cart-plus"></i> Add to Cart</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php } ?>
    </div>
</div>

<script>
    window.addEventListener('load', () => {
        setTimeout(() => {
            const loader = document.getElementById('preloader');
            if(loader){ loader.style.opacity = '0'; setTimeout(() => { loader.style.display = 'none'; }, 500); }
        }, 1000); 
    });
</script>
</script>
<script>
    window.addEventListener('load', () => {
        setTimeout(() => {
            const loader = document.getElementById('preloader');
            if(loader){
                loader.style.opacity = '0';
                setTimeout(() => { loader.style.display = 'none'; }, 500);
            }
        }, 2000); 
    });
    function openDetails(name, img, price, stock) {
        document.getElementById('m-name').innerText = name;
        document.getElementById('m-img').src = img;
        document.getElementById('m-price').innerText = "Rs. " + price;
        document.getElementById('m-stock').innerText = "In Stock: " + stock + " units";
        document.getElementById('detailsModal').style.display = 'flex';
    }
    function closeDetails() { document.getElementById('detailsModal').style.display = 'none'; }
</script>
</body>
</html>