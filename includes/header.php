<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Computer Shop</title>
    <link rel="stylesheet" href="css/layout.css">
</head>
<body>

<header class="main-header">
    <div class="logo">
        <a href="index.php">💻 Computer Shop</a>
    </div>

    <nav class="nav-links">
        <a href="index.php">Home</a>
        <a href="cart.php">Cart</a>

        <?php if (isset($_SESSION['user'])) { ?>
            <a href="logout.php">Logout</a>
        <?php } else { ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php } ?>
    </nav>
</header>

<main class="page-content">
