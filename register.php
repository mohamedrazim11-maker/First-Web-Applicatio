<?php
include "includes/db.php";

if (isset($_POST['register'])) {
    $name  = mysqli_real_escape_string($conn, trim($_POST['name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $pass_raw = $_POST['password'];
    $role  = 'user'; 

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !strpos($email, '@gmail.com')) {
        $error = "Please use a valid @gmail.com address.";
    } else if (strlen($pass_raw) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        $pass_hashed = password_hash($pass_raw, PASSWORD_DEFAULT);
        $check = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
        if (mysqli_num_rows($check) > 0) {
            $error = "This Gmail is already registered.";
        } else {
            $sql = "INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$pass_hashed', '$role')";
            if(mysqli_query($conn, $sql)) {
                header("Location: login.php?msg=success");
                exit;
            } else { $error = "Registration failed."; }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Razim Tech</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        .auth-container h2 { color: #0f172a; margin-bottom: 5px; }
        .auth-container p { color: #64748b; margin-bottom: 25px; font-size: 0.9rem; }
        .input-group { text-align: left; margin-bottom: 15px; }
        .input-group label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 0.85rem; color: #334155; }
        .input-group input {
            width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; outline: none; transition: 0.3s;
        }
        .input-group input:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }
        .btn-auth {
            width: 100%; background: #2563eb; color: white; padding: 13px; border: none;
            border-radius: 8px; font-weight: 700; cursor: pointer; transition: 0.3s; margin-top: 10px;
        }
        .btn-auth:hover { background: #1d4ed8; transform: translateY(-2px); }
        .error-msg { color: #ef4444; background: #fee2e2; padding: 10px; border-radius: 6px; margin-bottom: 15px; font-size: 0.85rem; }
        .switch-link { margin-top: 20px; font-size: 0.9rem; color: #64748b; }
        .switch-link a { color: #2563eb; font-weight: 700; text-decoration: none; }
    </style>
</head>
<body>
<div class="auth-container">
    <div class="logo-text">Razim <span style="color:#0f172a">SHOP</span></div>
    <h2>Create Account</h2>
    <p>Join our premium tech community today.</p>
    <?php if(isset($error)) echo "<div class='error-msg'>$error</div>"; ?>
    <form method="POST">
        <div class="input-group">
            <label>Full Name</label>
            <input type="text" name="name" placeholder="John Doe" required>
        </div>
        <div class="input-group">
            <label>Gmail Address</label>
            <input type="email" name="email" placeholder="example@gmail.com" required>
        </div>
        <div class="input-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="••••••••" required>
        </div>
        <button type="submit" name="register" class="btn-auth">Register Now</button>
    </form>
    <div class="switch-link">
        Already have an account? <a href="login.php">Login Here</a>
    </div>
</div>
</body>
</html>