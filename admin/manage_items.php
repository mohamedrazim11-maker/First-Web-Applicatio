<?php
session_start();
include "../includes/db.php";

if (!isset($_SESSION['role'])) {
    header("Location: admin_login.php");
    exit();
}

// Handle Manual Stock Update
if (isset($_POST['update_stock'])) {
    $id = mysqli_real_escape_string($conn, $_POST['product_id']);
    $new_stock = mysqli_real_escape_string($conn, $_POST['stock']);
    mysqli_query($conn, "UPDATE products SET stock = '$new_stock' WHERE id = '$id'");
}

// Handle Product Deletion
if (isset($_GET['delete'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete']);
    mysqli_query($conn, "DELETE FROM products WHERE id = '$id'");
    header("Location: manage_items.php");
}

// Search Logic
$search = "";
$query = "SELECT * FROM products ORDER BY id DESC";

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    // Search by Name or ID (Item Code)
    $query = "SELECT * FROM products WHERE name LIKE '%$search%' OR id LIKE '%$search%'";
}

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Items | SOLO Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #6366f1; --accent: #a855f7; --bg: #0f172a; --glass: rgba(255, 255, 255, 0.05); --border: rgba(255, 255, 255, 0.1); }
        body { background: var(--bg); color: white; font-family: 'Outfit', sans-serif; margin: 0; display: flex; min-height: 100vh; }
        .sidebar { width: 280px; background: rgba(0, 0, 0, 0.2); border-right: 1px solid var(--border); padding: 40px 20px; }
        .main-content { flex: 1; padding: 60px; }
        .nav-link { padding: 15px 20px; color: #94a3b8; text-decoration: none; border-radius: 12px; margin-bottom: 10px; display: block; }
        .nav-link.active { background: linear-gradient(45deg, var(--primary), var(--accent)); color: white; }
        
        .search-bar { background: var(--glass); border: 1px solid var(--border); padding: 15px; border-radius: 12px; width: 100%; color: white; margin-bottom: 30px; box-sizing: border-box; }
        
        table { width: 100%; border-collapse: collapse; background: var(--glass); border-radius: 15px; overflow: hidden; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid var(--border); }
        th { background: rgba(255,255,255,0.02); color: #94a3b8; font-weight: 400; }
        
        .btn { padding: 8px 15px; border-radius: 8px; text-decoration: none; font-size: 13px; cursor: pointer; border: none; transition: 0.3s; display: inline-block; }
        .btn-print { background: var(--primary); color: white; }
        .btn-delete { background: #ef4444; color: white; }
        .btn-update { background: #10b981; color: white; }
        
        input[type="number"] { background: transparent; border: 1px solid var(--border); color: white; padding: 5px; border-radius: 5px; width: 60px; }
        .cost-text { color: #f43f5e; font-weight: 600; } /* Subtle red for cost */
    </style>
</head>
<body>

<div class="sidebar">
    <div style="text-align: center; margin-bottom: 50px;">
        <h2 style="background: linear-gradient(45deg, #6366f1, #a855f7); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">SOLO</h2>
    </div>
    <nav>
        <a href="dashboard.php" class="nav-link">Console Overview</a>
        <a href="manage_items.php" class="nav-link active">Manage Items</a>
        <a href="view_orders.php" class="nav-link">Order Records</a>
    </nav>
</div>

<div class="main-content">
    <h1>Item Inventory</h1>
    
    <form method="GET" action="">
        <input type="text" name="search" class="search-bar" placeholder="Search by Product Name or Item Code..." value="<?= htmlspecialchars($search) ?>">
    </form>

    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Product Name</th>
                <th>Cost</th>
                <th>Selling Price</th>
                <th>Stock Amount</th>
                <th>Update</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td>#<?= $row['id'] ?></td>
                <td><?= $row['name'] ?></td>
                <td class="cost-text">$<?= number_format($row['cost_price'], 2) ?></td>
                <td>$<?= number_format($row['price'], 2) ?></td>
                <form method="POST">
                    <td>
                        <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                        <input type="number" name="stock" value="<?= $row['stock'] ?>">
                    </td>
                    <td>
                        <button type="submit" name="update_stock" class="btn btn-update">Save</button>
                    </td>
                </form>
                <td>
                    <a href="print_label.php?id=<?= $row['id'] ?>" class="btn btn-print">Print Label</a>
                    <a href="manage_items.php?delete=<?= $row['id'] ?>" class="btn btn-delete" onclick="return confirm('Permanently remove this item?')">Remove</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>