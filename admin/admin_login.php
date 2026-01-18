<?php
session_start();
include "../includes/db.php";

if (isset($_POST['login'])) {
    // We escape the input to prevent SQL injection
    $user_input = mysqli_real_escape_string($conn, $_POST['user_input']); 
    $pass  = md5($_POST['password']);
    $role  = $_POST['role'];

    // Updated Query: Checks if user_input matches EITHER email OR username
    $q = mysqli_query($conn, "SELECT * FROM admins WHERE (email='$user_input' OR username='$user_input') AND password='$pass' AND role='$role'");

    if (mysqli_num_rows($q) == 1) {
        $data = mysqli_fetch_assoc($q);
        
        // Track Login Time
        $admin_id = $data['id'];
        mysqli_query($conn, "UPDATE admins SET last_login = NOW() WHERE id = $admin_id");

        $_SESSION['admin'] = $data['name'];
        $_SESSION['role']  = $data['role'];
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Access Denied: Invalid $role credentials";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SOLO ADMIN| Secure Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        :root { --primary: #6366f1; --accent: #a855f7; --glass: rgba(255, 255, 255, 0.05); --border: rgba(255, 255, 255, 0.1); }
        body, html { margin: 0; padding: 0; font-family: 'Outfit', sans-serif; height: 100vh; overflow: hidden; background: #0f172a; color: white; }
        .circle { position: absolute; border-radius: 50%; background: linear-gradient(45deg, var(--primary), var(--accent)); filter: blur(80px); opacity: 0.3; z-index: -1; animation: float 20s infinite alternate; }
        .c1 { width: 400px; height: 400px; top: -100px; left: -100px; }
        .c2 { width: 300px; height: 300px; bottom: -50px; right: -50px; animation-delay: -5s; }
        @keyframes float { from { transform: translate(0,0); } to { transform: translate(50px, 100px); } }
        .wrapper { display: flex; justify-content: center; align-items: center; height: 100vh; }
        .glass-card { background: var(--glass); backdrop-filter: blur(20px); border: 1px solid var(--border); border-radius: 24px; padding: 40px; width: 100%; max-width: 420px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); }
        .role-toggle { display: flex; background: rgba(0,0,0,0.2); padding: 5px; border-radius: 12px; margin-bottom: 30px; }
        .role-btn { flex: 1; padding: 10px; border: none; background: transparent; color: #94a3b8; cursor: pointer; border-radius: 10px; font-weight: 600; transition: 0.3s; }
        .role-btn.active { background: var(--primary); color: white; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3); }
        input { width: 100%; padding: 14px; margin-bottom: 20px; background: rgba(255,255,255,0.05); border: 1px solid var(--border); border-radius: 12px; color: white; box-sizing: border-box; outline: none; transition: 0.3s; font-family: inherit; }
        input:focus { border-color: var(--primary); background: rgba(255,255,255,0.1); }
        .login-btn { width: 100%; padding: 14px; background: linear-gradient(45deg, var(--primary), var(--accent)); border: none; border-radius: 12px; color: white; font-weight: 600; cursor: pointer; transition: 0.3s; }
        .login-btn:hover { transform: scale(1.02); filter: brightness(1.1); }
        .error-alert { color: #f87171; font-size: 14px; text-align: center; margin-bottom: 15px; }
    </style>
</head>
<body>
<div class="circle c1"></div><div class="circle c2"></div>
<div class="wrapper">
    <div class="glass-card animate__animated animate__fadeInUp">
        <h2 style="text-align: center; margin: 0;">Nexus Portal</h2>
        <p style="text-align: center; color: #94a3b8; font-size: 14px; margin-bottom: 30px;">Secure Administrative Access</p>
        
        <?php if(isset($error)): ?>
            <div class="error-alert animate__animated animate__shakeX"><?= $error ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="role-toggle">
                <button type="button" class="role-btn active" onclick="setRole('super_admin', this)">Main Admin</button>
                <button type="button" class="role-btn" onclick="setRole('sub_admin', this)">Sub Admin</button>
            </div>
            
            <input type="hidden" name="role" id="roleInput" value="super_admin">
            
            <input type="text" name="user_input" placeholder="Username or Email" required>
            <input type="password" name="password" placeholder="Password" required>
            
            <button name="login" class="login-btn">Authenticate</button>
        </form>
    </div>
</div>
<script>
    function setRole(role, btn) {
        document.getElementById('roleInput').value = role;
        document.querySelectorAll('.role-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        const root = document.documentElement;
        if(role === 'sub_admin') {
            root.style.setProperty('--primary', '#10b981');
            root.style.setProperty('--accent', '#3b82f6');
        } else {
            root.style.setProperty('--primary', '#6366f1');
            root.style.setProperty('--accent', '#a855f7');
        }
    }
</script>
</body>
</html>