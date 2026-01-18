<?php
session_start(); // FIXED: Must be the very first line
include "includes/db.php";

// Redirect if not logged in
if(!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$uid = $_SESSION['user']; // Now session is active, this will work

// Fetch user data for settings panel
$u_query = mysqli_query($conn, "SELECT * FROM users WHERE id = '$uid'");
$u_data = mysqli_fetch_assoc($u_query);
$profile_pic = !empty($u_data['profile_pic']) ? "images/profiles/".$u_data['profile_pic'] : "images/default-avatar.png";

// Get Name and Email
$user_name = $u_data['name'];
$user_email = $u_data['email'];

// Pre-define variables to prevent warnings if data is missing
$phone = isset($u_data['phone']) ? $u_data['phone'] : '';
$address = isset($u_data['address']) ? $u_data['address'] : '';
$profile_pic = !empty($u_data['profile_pic']) ? "images/profiles/".$u_data['profile_pic'] : "images/default-avatar.png";
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
        /* Perfectly Centered Preloader */
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
        
        /* Product Details Modal Styles */
        .modal { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); align-items: center; justify-content: center; }
        .modal-content { background: white; padding: 30px; border-radius: 20px; width: 90%; max-width: 500px; text-align: center; position: relative; }
        .close-modal { position: absolute; top: 15px; right: 20px; font-size: 25px; cursor: pointer; color: #64748b; }
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
        <a href="cart.php" class="cart-link">
            <i class="fa-solid fa-cart-shopping"></i> Cart 
            <span id="cart-count">0</span>
        </a>
        
        <button onclick="toggleDarkMode()" id="mode-btn" class="nav-icon-btn">
            <i class="fa-solid fa-moon"></i>
        </button>

        <div class="settings-menu">
            <button class="settings-btn"><i class="fa-solid fa-gear"></i></button>
            <div class="settings-content">
    <div class="profile-header">
        <img src="<?php echo $profile_pic; ?>" id="p-preview" alt="Profile" style="width:60px; height:60px; border-radius:50%; object-fit:cover;">
        
        <div class="user-info-display" style="margin: 10px 0; border-bottom: 1px solid #eee; padding-bottom: 10px;">
            <strong style="display:block; font-size: 1rem; color: var(--primary);"><?php echo htmlspecialchars($user_name); ?></strong>
            <span style="font-size: 0.8rem; color: #64748b;"><?php echo htmlspecialchars($user_email); ?></span>
        </div>

        <input type="file" id="p-upload" style="display:none" onchange="uploadProfilePic()">
        <button onclick="document.getElementById('p-upload').click()" class="btn-sm" style="font-size: 11px; cursor: pointer;">Change Photo</button>
    </div>
    
    <hr>
    
    <label style="font-size: 0.75rem; color: #64748b;">Contact Number</label>
    <input type="text" id="u-phone" placeholder="Phone" value="<?php echo htmlspecialchars($u_data['phone'] ?? ''); ?>">
    
    <label style="font-size: 0.75rem; color: #64748b;">Delivery Address</label>
    <textarea id="u-address" placeholder="Address" rows="2"><?php echo htmlspecialchars($u_data['address'] ?? ''); ?></textarea>
    
    <button onclick="saveProfileData()" class="btn-save">Save Profile</button>
    <hr>
    <a href="logout.php" class="logout-link"><i class="fa-solid fa-power-off"></i> Logout</a>
</div>
        </div>
    </nav>
    
</header>

<div class="container">
    <div class="search-section" style="margin-bottom: 30px;">
        <div class="search-wrapper">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="text" id="product-search" placeholder="Search for hardware, laptops, or accessories..." onkeyup="liveSearch()">
        </div>
    </div>

    <div class="products" id="products-grid">
        <?php
        $q = mysqli_query($conn, "SELECT * FROM products");
        while($p = mysqli_fetch_assoc($q)) {
            $imgPath = (!empty($p['image'])) ? "images/".$p['image'] : "images/no-image.png";
            $stock = (int)$p['stock'];
        ?>
        <div class="product">
            <div class="product-img-link" onclick="openDetails('<?php echo addslashes($p['name']); ?>', '<?php echo $imgPath; ?>', '<?php echo number_format($p['price']); ?>', '<?php echo $stock; ?>')">
                <img src="<?php echo $imgPath; ?>" alt="Product">
            </div>
            <div class="product-info">
                <h3><?php echo $p['name']; ?></h3>
                <p class="price">Rs. <?php echo number_format($p['price']); ?></p>
                <p class="stock">Available: <span id="stock-<?php echo $p['id']; ?>"><?php echo $stock; ?></span></p>
            </div>
            <form class="cart-form" method="POST">
                <input type="hidden" name="pid" value="<?php echo $p['id']; ?>">
                <button type="submit" class="btn-add" <?php echo ($stock <= 0) ? 'disabled' : ''; ?>>
                    <i class="fa-solid fa-plus"></i> Add to Cart
                </button>
            </form>
        </div>
        <?php } ?>
    </div>
</div>
 

<div id="detailsModal" class="modal">
    <div class="modal-content">
        <span class="close-modal" onclick="closeDetails()">&times;</span>
        <img id="m-img" src="" style="max-height: 200px; margin-bottom: 15px;">
        <h2 id="m-name"></h2>
        <h3 id="m-price" style="color:var(--primary);"></h3>
        <p id="m-stock" style="font-weight: bold; margin-top: 10px;"></p>
        <p style="color:#64748b; margin-top: 10px;">High-performance tech guaranteed by Razim Tech.</p>
    </div>
</div>

<script>
    // 2-Second Animation Logic
    window.addEventListener('load', () => {
        setTimeout(() => {
            const loader = document.getElementById('preloader');
            loader.style.opacity = '0';
            loader.style.transition = 'opacity 0.5s ease';
            setTimeout(() => { loader.style.display = 'none'; }, 500);
        }, 2000); 
    });

    // Modal Logic
    function openDetails(name, img, price, stock) {
        document.getElementById('m-name').innerText = name;
        document.getElementById('m-img').src = img;
        document.getElementById('m-price').innerText = "Rs. " + price;
        document.getElementById('m-stock').innerText = "In Stock: " + stock + " units";
        document.getElementById('detailsModal').style.display = 'flex';
    }
    function closeDetails() {
        document.getElementById('detailsModal').style.display = 'none';
    }
</script>

</body>
</html>