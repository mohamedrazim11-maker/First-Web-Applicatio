<?php
session_start();
include "includes/db.php";

if(!isset($_SESSION['user'])) {
    if(!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) { echo "login_required"; exit; }
    header("Location: login.php");
    exit;
}

$uid = $_SESSION['user'];

// Fetch user data for pre-filling checkout form
$u_query = mysqli_query($conn, "SELECT * FROM users WHERE id = '$uid'");
$u_data = mysqli_fetch_assoc($u_query);

// Handle Add to Cart
if(isset($_POST['add']) && isset($_POST['pid'])) {
    $pid = intval($_POST['pid']);
    $qty = isset($_POST['qty']) ? intval($_POST['qty']) : 1;
    mysqli_query($conn, "INSERT INTO cart (user_id, product_id, quantity) 
                         VALUES ($uid, $pid, $qty) 
                         ON DUPLICATE KEY UPDATE quantity = quantity + $qty");
    if(!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) { echo "success"; exit; }
}

// Handle Remove Item
if(isset($_POST['remove_id'])) {
    $rid = intval($_POST['remove_id']);
    // Deleting based on the 'id' column in the cart table
    mysqli_query($conn, "DELETE FROM cart WHERE id=$rid AND user_id=$uid");
    if(!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) { echo "deleted"; exit; }
}

// Handle Clear Cart
if(isset($_POST['clear_cart'])) {
    mysqli_query($conn, "DELETE FROM cart WHERE user_id=$uid");
    if(!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) { echo "cleared"; exit; }
}

// Cart Count for Header
$count_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM cart WHERE user_id = '$uid'");
$count_row = mysqli_fetch_assoc($count_res);
$cart_count = $count_row['total'] ?? 0;

$q = mysqli_query($conn, "SELECT cart.id as cid, products.name, products.price, cart.quantity 
    FROM cart JOIN products ON cart.product_id = products.id WHERE user_id=$uid");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Your Cart | Razim Tech</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="js/main.js" defer></script>
    <style>
        .container { max-width: 1000px; margin: 40px auto; padding: 20px; }
        .responsive-table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .responsive-table th, .responsive-table td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        .responsive-table th { background: #f8fafc; color: #64748b; font-weight: 600; }
        .btn-remove { background: #fee2e2; color: #ef4444; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer; transition: 0.3s; }
        .btn-remove:hover { background: #ef4444; color: white; }
        .checkout-section { margin-top: 30px; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .grand-total { font-size: 1.5rem; font-weight: 700; color: #1e293b; text-align: right; margin-bottom: 20px; }
        .checkout-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .method-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 10px; }
        .method-card { border: 2px solid #f1f5f9; padding: 15px; border-radius: 10px; text-align: center; cursor: pointer; transition: 0.3s; }
        .method-card input { display: none; }
        .method-card:has(input:checked) { border-color: #2563eb; background: #eff6ff; color: #2563eb; }
        .btn-checkout { background: #2563eb; color: white; padding: 15px 30px; border-radius: 10px; font-weight: 600; font-size: 1rem; transition: 0.3s; }
        .btn-checkout:hover { background: #1d4ed8; transform: translateY(-2px); }
        #cart-count { background: #ef4444; color: white; font-size: 0.75rem; padding: 2px 6px; border-radius: 50%; position: absolute; top: -5px; right: -10px; }
        .cart-link { position: relative; }
    </style>
</head>
<body>

<header>
    <h1>Razim Tech</h1>
    <nav style="display: flex; gap: 20px; align-items: center;">
        <a href="index.php" style="text-decoration: none; color: var(--dark); font-weight: 600;">Shop</a>
        <a href="cart.php" class="cart-link" style="text-decoration: none; color: #2563eb; font-weight: 600;">
            <i class="fa-solid fa-cart-shopping"></i> Cart
            <span id="cart-count"><?php echo $cart_count; ?></span>
        </a>
    </nav>
</header>

<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2><i class="fa-solid fa-cart-shopping"></i> Your Shopping Cart</h2>
        <button onclick="clearCart()" style="background: #ef4444; color: white; border: none; padding: 8px 15px; border-radius: 8px; cursor: pointer; font-weight: 600;">
            <i class="fa-solid fa-trash-can"></i> Clear Cart
        </button>
    </div>

    <table class="responsive-table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Total</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $gt = 0; 
            if(mysqli_num_rows($q) > 0) {
                while($c = mysqli_fetch_assoc($q)) { 
                    $t = $c['price'] * $c['quantity']; 
                    $gt += $t; 
            ?>
            <tr id="row-<?php echo $c['cid']; ?>">
                <td><?php echo htmlspecialchars($c['name']); ?></td>
                <td>Rs. <?php echo number_format($c['price']); ?></td>
                <td><?php echo $c['quantity']; ?></td>
                <td>Rs. <?php echo number_format($t); ?></td>
                <td>
                    <button class="btn-remove" onclick="removeItem(<?php echo $c['cid']; ?>)">
                        <i class="fa-solid fa-trash"></i> Remove
                    </button>
                </td>
            </tr>
            <?php 
                } 
            } else {
                echo "<tr><td colspan='5' style='text-align:center; padding: 40px; color: #64748b;'>Your cart is empty.</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <?php if($gt > 0): ?>
    <div class="checkout-section">
        <form action="place_order.php" method="POST">
            <div class="grand-total">Grand Total: Rs. <?php echo number_format($gt); ?></div>
            
            <div style="background: #f8fafc; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
                <h4>Shipping Details</h4>
                <div class="checkout-grid">
                    <div>
                        <label style="font-size: 0.8rem; font-weight: bold; color: #475569;">Full Name</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($u_data['name'] ?? ''); ?>" required style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius: 8px; margin-top: 5px;">
                    </div>
                    <div>
                        <label style="font-size: 0.8rem; font-weight: bold; color: #475569;">Contact Number</label>
                        <input type="text" name="phone" value="<?php echo htmlspecialchars($u_data['phone'] ?? ''); ?>" required style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius: 8px; margin-top: 5px;">
                    </div>
                </div>
                <div style="margin-top: 15px;">
                    <label style="font-size: 0.8rem; font-weight: bold; color: #475569;">Delivery Address</label>
                    <textarea name="address" required style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius: 8px; margin-top: 5px; height: 80px;"><?php echo htmlspecialchars($u_data['address'] ?? ''); ?></textarea>
                </div>

                <h4 style="margin-top: 25px;">Payment Method</h4>
                <div class="method-grid">
                    <label class="method-card">
                        <input type="radio" name="method" value="card" checked>
                        <i class="fa-solid fa-credit-card"></i><br>Card
                    </label>
                    <label class="method-card">
                        <input type="radio" name="method" value="paypal">
                        <i class="fa-brands fa-paypal"></i><br>PayPal
                    </label>
                    <label class="method-card">
                        <input type="radio" name="method" value="bank">
                        <i class="fa-solid fa-building-columns"></i><br>Bank
                    </label>
                </div>
            </div>
            <button type="submit" class="btn-checkout" style="width: 100%; border: none; cursor: pointer;">Place Order Now</button>
        </form>
    </div>
    <?php endif; ?>
</div>

</body>
</html>