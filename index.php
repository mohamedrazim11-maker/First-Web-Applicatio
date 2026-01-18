<?php
session_start(); 
include "includes/db.php";

if(!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$uid = $_SESSION['user']; 

$u_query = mysqli_query($conn, "SELECT * FROM users WHERE id = '$uid'");
$u_data = mysqli_fetch_assoc($u_query);
$profile_pic = !empty($u_data['profile_pic']) ? "images/profiles/".$u_data['profile_pic'] : "images/default-avatar.png";

$user_name = $u_data['name'];
$user_email = $u_data['email'];
$phone = isset($u_data['phone']) ? $u_data['phone'] : '';
$address = isset($u_data['address']) ? $u_data['address'] : '';
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
        
        /* Product Description Overlay */
        .product-img-link { position: relative; cursor: pointer; display: block; overflow: hidden; border-radius: 8px; }
        .product-description-overlay {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(15, 23, 42, 0.9); color: white; padding: 15px;
            font-size: 0.85rem; display: flex; align-items: center; justify-content: center;
            text-align: center; opacity: 0; transition: opacity 0.3s ease; pointer-events: none;
        }
        .product-img-link:hover .product-description-overlay { opacity: 1; }

        /* Styled Add to Cart Button */
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
        <button onclick="toggleDarkMode()" id="mode-btn" class="nav-icon-btn"><i class="fa-solid fa-moon"></i></button>
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
                    <button onclick="document.getElementById('p-upload').click()" class="btn-sm">Change Photo</button>
                </div>
                <hr>
                <label>Contact Number</label>
                <input type="text" id="u-phone" value="<?php echo htmlspecialchars($phone); ?>">
                <label>Delivery Address</label>
                <textarea id="u-address" rows="2"><?php echo htmlspecialchars($address); ?></textarea>
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
            <input type="text" id="product-search" placeholder="Search tech..." onkeyup="liveSearch()">
        </div>
    </div>

    <div class="products" id="products-grid">
        <?php
        $q = mysqli_query($conn, "SELECT * FROM products");
        while($p = mysqli_fetch_assoc($q)) {
            $imgPath = (!empty($p['image'])) ? "images/".$p['image'] : "images/no-image.png";
            $stock = (int)$p['stock'];
            $description = htmlspecialchars($p['description'] ?? 'Premium quality hardware.');
        ?>
        <div class="product">
            <div class="product-img-link" onclick="openDetails('<?php echo addslashes($p['name']); ?>', '<?php echo $imgPath; ?>', '<?php echo number_format($p['price']); ?>', '<?php echo $stock; ?>')">
                <img src="<?php echo $imgPath; ?>" alt="Product">
                <div class="product-description-overlay"><?php echo $description; ?></div>
            </div>
            <div class="product-info">
                <h3><?php echo $p['name']; ?></h3>
                <p class="price">Rs. <?php echo number_format($p['price']); ?></p>
                <p class="stock">Available: <span id="stock-<?php echo $p['id']; ?>"><?php echo $stock; ?></span></p>
            </div>
            <form class="cart-form" method="POST" action="cart.php">
                <input type="hidden" name="pid" value="<?php echo $p['id']; ?>">
                <input type="hidden" name="add" value="1">
                <button type="submit" class="btn-add" <?php echo ($stock <= 0) ? 'disabled' : ''; ?>>
                    <i class="fa-solid fa-cart-plus"></i> Add to Cart
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
        <p id="m-stock"></p>
    </div>
</div>

<script>
    window.addEventListener('load', () => {
        setTimeout(() => {
            const loader = document.getElementById('preloader');
            loader.style.opacity = '0';
            setTimeout(() => { loader.style.display = 'none'; }, 500);
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