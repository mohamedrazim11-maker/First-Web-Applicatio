<?php
session_start();
include "includes/db.php";

$is_guest = !isset($_SESSION['user']) && isset($_GET['guest']);
$pid = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : header("Location: index.php");

// Fetch product details
$res = mysqli_query($conn, "SELECT * FROM products WHERE id = '$pid'");
$product = mysqli_fetch_assoc($res);

// Fetch extra gallery images
$gallery = mysqli_query($conn, "SELECT image_path FROM product_images WHERE product_id = '$pid'");
$all_images = [];
if(!empty($product['image'])) $all_images[] = "images/".$product['image'];
while($img = mysqli_fetch_assoc($gallery)) {
    $all_images[] = "images/".$img['image_path'];
}

// Cart Count logic
$cart_count = 0;
if(isset($_SESSION['user'])) {
    $uid = $_SESSION['user'];
    $count_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM cart WHERE user_id = '$uid'");
    $count_row = mysqli_fetch_assoc($count_res);
    $cart_count = $count_row['total'] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title><?php echo $product['name']; ?> | Razim Tech</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="js/main.js" defer></script>
    <style>
        :root { --primary: #6366f1; --bg: #0f172a; --glass: rgba(255, 255, 255, 0.05); }
        body { background: var(--bg); color: white; margin: 0; padding-bottom: 50px; }
        
        header { 
            display: flex; justify-content: space-between; align-items: center; 
            padding: 15px 10%; background: rgba(15, 23, 42, 0.9); 
            backdrop-filter: blur(10px); position: sticky; top: 0; z-index: 1000; 
            border-bottom: 1px solid var(--glass); 
        }

        .view-container { max-width: 800px; margin: auto; padding: 20px; }
        .gallery-wrapper { position: relative; width: 100%; overflow: hidden; border-radius: 20px; background: #000; height: 400px; }
        .gallery-track { display: flex; transition: transform 0.4s ease-out; height: 100%; }
        .gallery-slide { min-width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; }
        .gallery-slide img { max-width: 100%; max-height: 100%; object-fit: contain; }
        .nav-btn { position: absolute; top: 50%; transform: translateY(-50%); background: rgba(0,0,0,0.5); color: white; border: none; padding: 15px; cursor: pointer; border-radius: 50%; z-index: 10; }
        .prev { left: 10px; } .next { right: 10px; }
        .dots { display: flex; justify-content: center; gap: 8px; margin-top: 15px; }
        .dot { width: 10px; height: 10px; border-radius: 50%; background: var(--glass); cursor: pointer; }
        .dot.active { background: var(--primary); }
        .details-content { margin-top: 30px; background: var(--glass); padding: 25px; border-radius: 20px; }
        .price-tag { font-size: 24px; color: var(--primary); font-weight: 700; margin: 10px 0; }
        .description { line-height: 1.6; color: #94a3b8; margin-top: 20px; }
        .back-link { color: #94a3b8; text-decoration: none; display: flex; align-items: center; gap: 8px; font-weight: 500; }
        .cart-link { position: relative; color: white; text-decoration: none; font-size: 1.2rem; }
    </style>
</head>
<body>

<header>
    <a href="index.php<?php echo $is_guest ? '?guest=1' : ''; ?>" class="back-link">
        <i class="fa-solid fa-arrow-left"></i> Back to Shop
    </a>
    <?php if(!$is_guest): ?>
    <a href="cart.php" class="cart-link">
        <i class="fa-solid fa-cart-shopping"></i>
        <span id="cart-count"><?php echo $cart_count; ?></span>
    </a>
    <?php endif; ?>
</header>

<div class="view-container">
    <div class="gallery-wrapper" id="gallery">
        <div class="gallery-track" id="track">
            <?php foreach($all_images as $img): ?>
                <div class="gallery-slide"><img src="<?php echo $img; ?>"></div>
            <?php endforeach; ?>
        </div>
        <?php if(count($all_images) > 1): ?>
            <button class="nav-btn prev" onclick="moveSlide(-1)"><i class="fa-solid fa-chevron-left"></i></button>
            <button class="nav-btn next" onclick="moveSlide(1)"><i class="fa-solid fa-chevron-right"></i></button>
        <?php endif; ?>
    </div>
    
    <div class="dots">
        <?php for($i=0; $i<count($all_images); $i++): ?>
            <div class="dot <?php echo $i==0?'active':''; ?>" onclick="goToSlide(<?php echo $i; ?>)"></div>
        <?php endfor; ?>
    </div>

    <div class="details-content">
        <h1><?php echo $product['name']; ?></h1>
        <div class="price-tag">Rs. <?php echo number_format($product['price']); ?></div>
        <p>Stock Status: <span style="color:#10b981"><?php echo $product['stock']; ?> units available</span></p>
        
        <div class="description">
            <h3>Product Description</h3>
            <p><?php echo nl2br(htmlspecialchars($product['description'] ?? 'No description available for this product.')); ?></p>
        </div>

        <?php if(!$is_guest): ?>
            <form method="POST" action="cart.php" style="margin-top:30px;">
                <input type="hidden" name="pid" value="<?php echo $pid; ?>">
                <button type="submit" name="add" class="btn-add" style="width:200px">
                    <i class="fa-solid fa-cart-plus"></i> Add to Cart
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
    let currentIndex = 0;
    const track = document.getElementById('track');
    const slides = document.querySelectorAll('.gallery-slide');
    const dots = document.querySelectorAll('.dot');
    function updateGallery() {
        track.style.transform = `translateX(-${currentIndex * 100}%)`;
        dots.forEach((dot, i) => dot.classList.toggle('active', i === currentIndex));
    }
    function moveSlide(dir) {
        currentIndex = (currentIndex + dir + slides.length) % slides.length;
        updateGallery();
    }
    function goToSlide(index) {
        currentIndex = index;
        updateGallery();
    }
</script>
</body>
</html>