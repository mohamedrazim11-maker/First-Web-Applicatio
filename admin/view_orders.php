<?php
session_start();
include "../includes/db.php";
if (!isset($_SESSION['role'])) header("Location: admin_login.php");

$q = mysqli_query($conn, 
"SELECT orders.*, users.name as customer_name 
 FROM orders 
 JOIN users ON orders.user_id = users.id 
 ORDER BY orders.id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Nexus Admin | Orders</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        :root { --primary: #6366f1; --accent: #a855f7; --bg: #0f172a; --glass: rgba(255, 255, 255, 0.05); }
        body { background: var(--bg); color: white; font-family: 'Outfit', sans-serif; margin: 0; padding: 40px; min-height: 100vh; }
        
        .header-section { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
        .back-btn { color: #94a3b8; text-decoration: none; font-size: 14px; transition: 0.3s; display: inline-block; margin-bottom: 8px; }
        .back-btn:hover { color: var(--primary); }

        .table-container {
            background: var(--glass);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        }

        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { background: rgba(99, 102, 241, 0.1); padding: 22px; font-size: 12px; text-transform: uppercase; letter-spacing: 1.5px; color: var(--primary); border-bottom: 1px solid rgba(255,255,255,0.05); }
        td { padding: 20px; border-bottom: 1px solid rgba(255, 255, 255, 0.05); font-weight: 300; color: #cbd5e1; }
        tr { transition: 0.3s; opacity: 0; }
        tr:hover { background: rgba(255, 255, 255, 0.03); }

        .status-pill { padding: 5px 14px; border-radius: 20px; font-size: 11px; background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2); font-weight: 600; }
        .price-tag { color: var(--accent); font-weight: 600; }
    </style>
</head>
<body>

<div class="header-section animate__animated animate__fadeInDown">
    <div>
        <a href="dashboard.php" class="back-btn">← Back to Console</a>
        <h1 style="margin: 0; font-weight: 600; font-size: 2rem;">Customer Orders</h1>
    </div>
    <div style="text-align: right; background: var(--glass); padding: 10px 20px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1);">
        <span style="color: #94a3b8; font-size: 12px;">Active Session</span><br>
        <span style="color: var(--primary); font-weight: 600; letter-spacing: 1px;"><?= strtoupper($_SESSION['role']) ?></span>
    </div>
</div>

<div class="table-container animate__animated animate__fadeIn">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Customer</th>
                <th>Total Amount</th>
                <th>Order Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php while($o = mysqli_fetch_assoc($q)) { ?>
            <tr class="order-row">
                <td style="color: var(--primary);">#<?= $o['id'] ?></td>
                <td style="font-weight: 600; color: white;"><?= $o['customer_name'] ?></td>
                <td class="price-tag">$<?= number_format($o['total'], 2) ?></td>
                <td><?= date('M d, Y', strtotime($o['order_date'])) ?></td>
                <td><span class="status-pill">Completed</span></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const rows = document.querySelectorAll(".order-row");
        rows.forEach((row, index) => {
            setTimeout(() => {
                row.classList.add("animate__animated", "animate__fadeInUp");
                row.style.opacity = "1";
            }, index * 60);
        });
    });
</script>
</body>
</html>