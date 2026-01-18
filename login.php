<?php
session_start();
include "includes/db.php";

// FIX: If the user is already logged in, don't let them see the login page
if(isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $pass  = $_POST['password'];

    $res = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    $user = mysqli_fetch_assoc($res);

    // Verify hashed password from your database
    if ($user && password_verify($pass, $user['password'])) {
        $_SESSION['user'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        header("Location: index.php"); // The "Jump" to home
        exit();
    } else {
        $error = "Invalid Gmail or Password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Razim Tech</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            display: flex; justify-content: center; align-items: center; min-height: 100vh;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); margin: 0;
        }
        .auth-container {
            background: rgba(255, 255, 255, 0.95); padding: 40px; border-radius: 20px;
            width: 100%; max-width: 400px; text-align: center; box-shadow: 0 20px 25px rgba(0,0,0,0.3);
        }
        .logo-text { font-size: 2rem; font-weight: 800; color: #2563eb; margin-bottom: 10px; text-transform: uppercase; }
        .input-group { text-align: left; margin-bottom: 15px; }
        .input-group label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 0.85rem; color: #334155; }
        .input-group input { width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; outline: none; }
        .btn-auth { width: 100%; background: #2563eb; color: white; padding: 13px; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; margin-top: 10px; }
        .error-msg { color: #ef4444; background: #fee2e2; padding: 10px; border-radius: 6px; margin-bottom: 15px; font-size: 0.85rem; }
    </style>
</head>
<body>
<div class="auth-container">
    <div class="logo-text">Razim <span style="color:#0f172a">SHOP</span></div>
    <h2>Welcome Back</h2>
    <?php if(isset($error)) echo "<div class='error-msg'>$error</div>"; ?>
    <form method="POST">
        <div class="input-group">
            <label>Gmail Address</label>
            <input type="email" name="email" placeholder="example@gmail.com" required>
        </div>
        <div class="input-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="••••••••" required>
        </div>
        <button type="submit" name="login" class="btn-auth">Login to Shop</button>
    </form>
    <div style="margin-top:20px; font-size:0.9rem; color:#64748b;">
        Don't have an account? <a href="register.php" style="color:#2563eb; text-decoration:none; font-weight:700;">Register Now</a>
    </div>
</div>
</body>
</html>