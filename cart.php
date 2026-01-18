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

if(isset($_POST['add']) && isset($_POST['pid'])) {
    $pid = intval($_POST['pid']);
    $qty = isset($_POST['qty']) ? intval($_POST['qty']) : 1;
    mysqli_query($conn, "INSERT INTO cart (user_id, product_id, quantity) 
                         VALUES ($uid, $pid, $qty) 
                         ON DUPLICATE KEY UPDATE quantity = quantity + $qty");
    if(!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) { echo "success"; exit; }
}

if(isset($_POST['remove_id'])) {
    $rid = intval($_POST['remove_id']);
    mysqli_query($conn, "DELETE FROM cart WHERE id=$rid AND user_id=$uid");
    if(!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) { echo "deleted"; exit; }
}

$q = mysqli_query($conn, "SELECT cart.id as cid, products.name, products.price, cart.quantity 
    FROM cart JOIN products ON cart.product_id = products.id WHERE user_id=$uid");
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="js/main.js" defer></script>
</head>
<body>

<div class="container">
    <h2><i class="fa-solid fa-cart-shopping"></i> Your Shopping Cart</h2>
    <table class="responsive-table">
        <thead>
            <tr><th>Product</th><th>Price</th><th>Qty</th><th>Total</th><th>Action</th></tr>
        </thead>
        <tbody>
            <?php $gt=0; while($c=mysqli_fetch_assoc($q)){ 
                $t=$c['price']*$c['quantity']; $gt+=$t; ?>
            <tr id="row-<?php echo $c['cid']; ?>">
                <td><?php echo $c['name']; ?></td>
                <td>Rs. <?php echo number_format($c['price']); ?></td>
                <td><?php echo $c['quantity']; ?></td>
                <td>Rs. <?php echo number_format($t); ?></td>
                <td>
                    <button class="btn-remove" onclick="removeItem(<?php echo $c['cid']; ?>)">
                        <i class="fa-solid fa-trash"></i> Remove
                    </button>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>

    <div class="cart-summary">
        <h3>Grand Total: <span id="grand-total">Rs. <?php echo number_format($gt); ?></span></h3>
        
        <form action="checkout.php" method="GET" style="margin-top: 20px;">
            <div class="payment-methods" style="text-align: left; padding: 20px; background: #f8fafc; border-radius: 12px;">
                <h4><i class="fa-solid fa-truck-fast"></i> Delivery Details</h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px;">
                    <div>
                        <label style="font-size: 0.8rem; font-weight: bold;">Full Name</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($u_data['name'] ?? ''); ?>" required style="width:100%; padding:8px; border:1px solid #ddd; border-radius: 5px;">
                    </div>
                    <div>
                        <label style="font-size: 0.8rem; font-weight: bold;">Email</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($u_data['email'] ?? ''); ?>" required style="width:100%; padding:8px; border:1px solid #ddd; border-radius: 5px;">
                    </div>
                    <div>
                        <label style="font-size: 0.8rem; font-weight: bold;">Phone</label>
                        <input type="text" name="phone" value="<?php echo htmlspecialchars($u_data['phone'] ?? ''); ?>" required style="width:100%; padding:8px; border:1px solid #ddd; border-radius: 5px;">
                    </div>
                    <div>
                        <label style="font-size: 0.8rem; font-weight: bold;">Address</label>
                        <input type="text" name="address" value="<?php echo htmlspecialchars($u_data['address'] ?? ''); ?>" required style="width:100%; padding:8px; border:1px solid #ddd; border-radius: 5px;">
                    </div>
                </div>

                <h4 style="margin-top: 25px;">Select Payment Method</h4>
                <div class="method-grid">
                    <label class="method-card">
                        <input type="radio" name="method" value="card" checked>
                        <i class="fa-solid fa-credit-card"></i> Card
                    </label>
                    <label class="method-card">
                        <input type="radio" name="method" value="paypal">
                        <i class="fa-brands fa-paypal"></i> PayPal
                    </label>
                    <label class="method-card">
                        <input type="radio" name="method" value="bank">
                        <i class="fa-solid fa-building-columns"></i> Bank
                    </label>
                </div>
            </div>
            <button type="submit" class="btn-checkout" style="width: 100%; border: none; cursor: pointer;">Confirm & Place Order</button>
        </form>
    </div>
</div>

</body>
</html>