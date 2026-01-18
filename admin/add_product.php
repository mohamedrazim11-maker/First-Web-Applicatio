<?php
session_start();
include "../includes/db.php";
if ($_SESSION['role'] !== 'super_admin') header("Location: admin_login.php");

// AJAX request handler for fetching next item code
if (isset($_GET['check_code']) && isset($_GET['cat_id'])) {
    $cat_id = (int)$_GET['cat_id'];
    $cat_res = mysqli_fetch_assoc(mysqli_query($conn, "SELECT category_code FROM categories WHERE id = $cat_id"));
    $prod_count = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM products WHERE category_id = $cat_id"));
    
    $next_number = $prod_count + 1;
    echo $next_number . $cat_res['category_code'];
    exit();
}

if (isset($_POST['add'])) {
    $name  = mysqli_real_escape_string($conn, $_POST['name']);
    $price = $_POST['price'];
    $cost  = $_POST['cost'];
    $stock = $_POST['stock'];
    $cat   = $_POST['category_id'];
    $sup   = $_POST['supplier_id'];
    $sku   = $_POST['generated_sku']; // The new generated item code
    $desc  = mysqli_real_escape_string($conn, $_POST['description']);

    $img = $_FILES['image']['name'];
    move_uploaded_file($_FILES['image']['tmp_name'], "../images/".$img);

    // Insert product with generated SKU/Item Code
    $query = "INSERT INTO products(name, price, cost_price, stock, image, description, category_id, supplier_id, sku) 
              VALUES('$name', '$price', '$cost', '$stock', '$img', '$desc', '$cat', '$sup', '$sku')";
    
    if(mysqli_query($conn, $query)) {
        $total_cost = (float)$cost * (int)$stock;
        mysqli_query($conn, "UPDATE suppliers SET total_debit = total_debit + $total_cost WHERE id = $sup");
        $success = "Product added with code $sku!";
    }
}

$cats = mysqli_query($conn, "SELECT * FROM categories");
$sups = mysqli_query($conn, "SELECT * FROM suppliers");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SOLO Admin | Add Product</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #6366f1; --bg: #0f172a; --glass: rgba(255, 255, 255, 0.05); }
        body { background: var(--bg); color: white; font-family: 'Outfit', sans-serif; padding: 40px; }
        .glass-card { background: var(--glass); padding: 30px; border-radius: 20px; border: 1px solid rgba(255,255,255,0.1); max-width: 600px; margin: auto; }
        input, select, textarea { width: 100%; padding: 12px; margin-bottom: 15px; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1); color: white; border-radius: 10px; box-sizing: border-box; }
        button { width: 100%; padding: 15px; background: var(--primary); color: white; border: none; border-radius: 10px; cursor: pointer; font-weight: 600; }
        .sku-preview { background: #1e293b; color: #10b981; padding: 10px; border-radius: 10px; margin-bottom: 15px; font-weight: bold; border-left: 4px solid #10b981; }
    </style>
</head>
<body>
   <div class="glass-card">
        <h2>Add Inventory Item</h2>
        <?php if(isset($success)) echo "<p style='color:#10b981'>$success</p>"; ?>
        
        <form method="post" enctype="multipart/form-data">
            <div id="sku_box" style="display:none;" class="sku-preview">
                Auto-Generated Item Code: <span id="sku_display"></span>
                <input type="hidden" name="generated_sku" id="sku_input">
            </div>

            <input name="name" placeholder="Product Name" required>
            <div style="display:flex; gap:10px;">
                <input name="price" placeholder="Selling Price ($)" required>
                <input name="cost" placeholder="Supplier Cost ($)" required>
                <input name="stock" placeholder="Stock Qty" required>
            </div>
            
            <select name="category_id" id="cat_select" required onchange="fetchItemCode(this.value)">
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

    <script>
    function fetchItemCode(catId) {
        if (!catId) {
            document.getElementById('sku_box').style.display = 'none';
            return;
        }
        fetch(`add_product.php?check_code=1&cat_id=${catId}`)
            .then(response => response.text())
            .then(code => {
                document.getElementById('sku_display').innerText = code;
                document.getElementById('sku_input').value = code;
                document.getElementById('sku_box').style.display = 'block';
            });
    }
    
    </script>
</body>
</html>