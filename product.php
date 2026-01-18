<?php
session_start();
include "includes/db.php";

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = intval($_GET['id']);
$q = mysqli_query($conn, "SELECT * FROM products WHERE id=$id");
$product = mysqli_fetch_assoc($q);

if (!$product) {
    echo "Product not found";
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= $product['name'] ?></title>
    <link rel="stylesheet" href="css/user.css">
    <script src="js/main.js" defer></script>
</head>
<body>

<header class="site-header">
    <h1>Computer Shop</h1>
    <nav>
        <a href="index.php">Home</a>
        <a href="cart.php">Cart</a>
        <?php if(isset($_SESSION['user'])) { ?>
            <a href="logout.php">Logout</a>
        <?php } else { ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php } ?>
    </nav>
</header>

<div class="product-details">
    <img src="images/<?= $product['image'] ?>">
    <div class="info">
        <h2><?= $product['name'] ?></h2>
        <p class="price">Rs. <?= $product['price'] ?></p>
        <p><?= $product['description'] ?></p>

        <form method="post" action="cart.php">
            <input type="hidden" name="pid" value="<?= $product['id'] ?>">
            <button name="add">Add to Cart</button>
        </form>
    </div>
</div>

</body>
</html>
