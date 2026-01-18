<?php
session_start();
include "../includes/db.php";

// Access Control
if (!isset($_SESSION['role'])) {
    header("Location: admin_login.php");
    exit();
}

$admin_name = $_SESSION['admin'];
$role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SOLO Admin | Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        :root { 
            --primary: #6366f1; 
            --accent: #a855f7; 
            --bg: #0f172a; 
            --glass: rgba(255, 255, 255, 0.05); 
            --border: rgba(255, 255, 255, 0.1); 
        }
        
        body { 
            background: var(--bg); 
            color: white; 
            font-family: 'Outfit', sans-serif; 
            margin: 0; 
            display: flex; 
            min-height: 100vh; 
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            border-right: 1px solid var(--border);
            padding: 40px 20px;
            display: flex;
            flex-direction: column;
        }

        .logo-area {
            text-align: center;
            margin-bottom: 50px;
        }

        .logo-area h2 {
            letter-spacing: 2px;
            background: linear-gradient(45deg, var(--primary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 0;
        }

        .nav-link {
            padding: 15px 20px;
            color: #94a3b8;
            text-decoration: none;
            border-radius: 12px;
            margin-bottom: 10px;
            transition: 0.3s;
            display: flex;
            align-items: center;
        }

        .nav-link:hover {
            background: var(--glass);
            color: white;
        }

        .nav-link.active {
            background: linear-gradient(45deg, var(--primary), var(--accent));
            color: white;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
        }

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 60px;
        }

        .welcome-section {
            margin-bottom: 40px;
        }

        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }

        .stat-card {
            background: var(--glass);
            border: 1px solid var(--border);
            padding: 30px;
            border-radius: 24px;
            transition: 0.3s;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.08);
        }

        .stat-card h3 {
            margin: 0;
            color: #94a3b8;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .stat-card .value {
            font-size: 32px;
            font-weight: 600;
            margin: 15px 0;
            display: block;
        }

        .role-badge {
            font-size: 12px;
            padding: 4px 12px;
            border-radius: 20px;
            background: rgba(168, 85, 247, 0.2);
            color: var(--accent);
            border: 1px solid rgba(168, 85, 247, 0.3);
        }

        .logout-btn {
            margin-top: auto;
            color: #f87171;
            text-decoration: none;
            font-size: 14px;
            text-align: center;
            padding: 12px;
            border: 1px solid rgba(248, 113, 113, 0.2);
            border-radius: 10px;
            transition: 0.3s;
        }

        .logout-btn:hover {
            background: rgba(248, 113, 113, 0.1);
        }
    </style>
</head>
<body>

<div class="sidebar animate__animated animate__fadeInLeft">
    <div class="logo-area">
        <h2>SOLO</h2>
        <p style="font-size: 10px; color: #64748b;">SYSTEMS v2.0</p>
    </div>

    <nav style="flex: 1;">
        <a href="dashboard.php" class="nav-link active">Console Overview</a>
        <a href="view_orders.php" class="nav-link">Order Records</a>
        
        <?php if($role === 'super_admin'): ?>
        <a href="manage_admins.php" class="nav-link">Personnel Mgmt</a>
        <?php endif; ?>
    </nav>

    <a href="logout.php" class="logout-btn">Terminate Session</a>
</div>

<div class="main-content">
    <div class="welcome-section animate__animated animate__fadeInDown">
        <span style="color: var(--primary); font-weight: 600;">Welcome back,</span>
        <h1 style="margin: 5px 0 10px 0; font-size: 2.5rem;"><?= $admin_name ?></h1>
        <span class="role-badge"><?= strtoupper($role) ?> LEVEL ACCESS</span>
    </div>

    <div class="grid-container">
        <div class="stat-card animate__animated animate__fadeInUp" style="animation-delay: 0.1s;">
            <h3>Total Revenue</h3>
            <span class="value">$124,592.00</span>
            <div style="color: #10b981; font-size: 13px;">↑ 12% from last month</div>
        </div>

        <div class="stat-card animate__animated animate__fadeInUp" style="animation-delay: 0.2s;">
            <h3>Active Orders</h3>
            <span class="value">48</span>
            <div style="color: var(--primary); font-size: 13px;">8 pending verification</div>
        </div>

        <div class="stat-card animate__animated animate__fadeInUp" style="animation-delay: 0.3s;">
            <h3>System Status</h3>
            <span class="value" style="color: #10b981;">Operational</span>
            <div style="color: #64748b; font-size: 13px;">All nodes encrypted</div>
        </div>
    </div>
</div>

</body>
</html>