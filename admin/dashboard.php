<?php
session_start();
include "../includes/db.php";

if (!isset($_SESSION['role'])) {
    header("Location: admin_login.php");
    exit();
}

$admin_name = $_SESSION['admin'];
$role = $_SESSION['role'];

// Financial Logic for Super Admin
$total_revenue = 0;
$total_profit = 0;

if ($role === 'super_admin') {
    $query = "SELECT 
        SUM(p.price * o.quantity) as revenue, 
        SUM((p.price - p.cost_price) * o.quantity) as profit 
        FROM orders o 
        JOIN products p ON o.product_id = p.id";
    
    $fin_query = mysqli_query($conn, $query);
    
    if($fin_query && mysqli_num_rows($fin_query) > 0) {
        $fin_data = mysqli_fetch_assoc($fin_query);
        $total_revenue = $fin_data['revenue'] ?? 0;
        $total_profit = $fin_data['profit'] ?? 0;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SOLO Admin | Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        :root { --primary: #6366f1; --accent: #a855f7; --bg: #0f172a; --glass: rgba(255, 255, 255, 0.05); --border: rgba(255, 255, 255, 0.1); }
        body { background: var(--bg); color: white; font-family: 'Outfit', sans-serif; margin: 0; display: flex; min-height: 100vh; }
        .sidebar { width: 280px; background: rgba(0, 0, 0, 0.2); border-right: 1px solid var(--border); padding: 40px 20px; display: flex; flex-direction: column; }
        .nav-link { padding: 15px 20px; color: #94a3b8; text-decoration: none; border-radius: 12px; margin-bottom: 10px; display: block; transition: 0.3s; }
        .nav-link:hover, .nav-link.active { background: var(--glass); color: white; }
        .nav-link.active { background: linear-gradient(45deg, var(--primary), var(--accent)); }
        .main-content { flex: 1; padding: 60px; }
        .grid-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 25px; }
        .stat-card { background: var(--glass); border: 1px solid var(--border); padding: 30px; border-radius: 24px; transition: 0.3s; }
        .stat-card:hover { border-color: var(--primary); transform: translateY(-5px); }
        .value { font-size: 32px; font-weight: 600; display: block; margin: 10px 0; }
        .role-badge { font-size: 12px; padding: 4px 12px; border-radius: 20px; background: rgba(168, 85, 247, 0.1); color: var(--accent); border: 1px solid var(--accent); }
    </style>
</head>
<body>
<div class="sidebar">
    <div style="text-align: center; margin-bottom: 50px;">
        <h2 style="background: linear-gradient(45deg, #6366f1, #a855f7); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">SOLO</h2>
    </div>
    <nav style="flex: 1;">
        <a href="dashboard.php" class="nav-link active">Console Overview</a>
        <a href="view_orders.php" class="nav-link">Order Records</a>
        <?php if($role === 'super_admin'): ?>
            <a href="manage_categories.php" class="nav-link">Product Categories</a> <a href="add_product.php" class="nav-link">Add Products</a>
            <a href="manage_suppliers.php" class="nav-link">Suppliers & Debts</a>
            <a href="manage_admins.php" class="nav-link">Personnel Mgmt</a>
        <?php endif; ?>
    </nav>
    <a href="logout.php" style="color:#f87171; text-decoration:none; text-align:center; padding:12px; border:1px solid rgba(248,113,113,0.2); border-radius:10px; font-weight: 600;">Terminate Session</a>
</div>

<div class="main-content">
    <div class="animate__animated animate__fadeIn">
        <span style="color: var(--primary);">System Overview</span>
        <h1 style="margin: 10px 0;">Welcome, <?= $admin_name ?></h1>
        <span class="role-badge"><?= strtoupper($role) ?> ACCESS LEVEL</span>
    </div>

    <div class="grid-container" style="margin-top: 40px;">
        <div class="stat-card animate__animated animate__fadeInUp">
            <h3 style="color: #94a3b8; font-size: 14px; text-transform: uppercase;">Total Sales Revenue</h3>
            <span class="value">$<?= number_format($total_revenue, 2) ?></span>
        </div>
        
        <?php if($role === 'super_admin'): ?>
        <div class="stat-card animate__animated animate__fadeInUp" style="animation-delay: 0.1s; border-left: 4px solid #10b981;">
            <h3 style="color: #10b981; font-size: 14px; text-transform: uppercase;">Estimated Net Profit</h3>
            <span class="value" style="color: #10b981;">$<?= number_format($total_profit, 2) ?></span>
            <small style="color: #64748b;">(Selling Price - Cost Price)</small>
        </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>