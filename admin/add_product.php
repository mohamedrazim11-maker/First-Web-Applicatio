<?php
session_start();
include "../includes/db.php";
if ($_SESSION['role'] !== 'super_admin') header("Location: admin_login.php");

if (isset($_POST['add'])) {
    $name  = mysqli_real_escape_string($conn, $_POST['name']);
    $price = $_POST['price'];
    $cost  = $_POST['cost'];
    $stock = $_POST['stock'];
    $cat   = $_POST['category_id'];
    $sup   = $_POST['supplier_id'];
    $desc  = mysqli_real_escape_string($conn, $_POST['description']);

    $img = $_FILES['image']['name'];
    move_uploaded_file($_FILES['image']['tmp_name'], "../images/".$img);

    // Insert product and update supplier debit automatically
    $query = "INSERT INTO products(name, price, cost_price, stock, image, description, category_id, supplier_id) 
              VALUES('$name', '$price', '$cost', '$stock', '$img', '$desc', '$cat', '$sup')";
    
    if(mysqli_query($conn, $query)) {
        $total_cost = $cost * $stock;
        mysqli_query($conn, "UPDATE suppliers SET total_debit = total_debit + $total_cost WHERE id = $sup");
        $success = "Product added and Supplier debit updated!";
    }
}

$cats = mysqli_query($conn, "SELECT * FROM categories");
$sups = mysqli_query($conn, "SELECT * FROM suppliers");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Nexus Admin | Add Product</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #6366f1; --bg: #0f172a; --glass: rgba(255, 255, 255, 0.05); }
        body { background: var(--bg); color: white; font-family: 'Outfit', sans-serif; padding: 40px; }
        .glass-card { background: var(--glass); padding: 30px; border-radius: 20px; border: 1px solid rgba(255,255,255,0.1); max-width: 600px; margin: auto; }
        input, select, textarea { width: 100%; padding: 12px; margin-bottom: 15px; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1); color: white; border-radius: 10px; box-sizing: border-box; }
        button { width: 100%; padding: 15px; background: var(--primary); color: white; border: none; border-radius: 10px; cursor: pointer; font-weight: 600; }
    </style>
</head>
<body>
    <div class="glass-card">
        <h2>Add Inventory Item</h2>
        <?php if(isset($success)) echo "<p style='color:#10b981'>$success</p>"; ?>
        <form method="post" enctype="multipart/form-data">
            <input name="name" placeholder="Product Name" required>
            <div style="display:flex; gap:10px;">
                <input name="price" placeholder="Selling Price ($)" required>
                <input name="cost" placeholder="Supplier Cost ($)" required>
                <input name="stock" placeholder="Stock Qty" required>
            </div>
            <select name="category_id" required>
                <option value="">Select Category</option>
                <?php while($c = mysqli_fetch_assoc($cats)) echo "<option value='{$c['id']}'>{$c['category_name']}</option>"; ?>
            </select>
            <select name="supplier_id" required>
                <option value="">Select Supplier</option>
                <?php while($s = mysqli_fetch_assoc($sups)) echo "<option value='{$s['id']}'>{$s['supplier_name']}</option>"; ?>
            </select>
            <input type="file" name="image" required>
            <textarea name="description" placeholder="Product Description"></textarea>
            <button name="add">Register Product & Update Debit</button>
        </form>
    </div>
</body>
</html>