<?php
session_start();
include "../includes/db.php";

// Access Control: Only Super Admins can manage categories
if ($_SESSION['role'] !== 'super_admin') {
    header("Location: dashboard.php");
    exit();
}

// Logic to Remove a Category with Product Check
if (isset($_GET['delete'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete']);
    
    // Check if any products are linked to this category
    $check_products = mysqli_query($conn, "SELECT id FROM products WHERE category_id = '$id' LIMIT 1");
    
    if (mysqli_num_rows($check_products) > 0) {
        $error = "Cannot delete: There are still products assigned to this category. Reassign or delete those products first.";
    } else {
        $delete_query = "DELETE FROM categories WHERE id = '$id'";
        if (mysqli_query($conn, $delete_query)) {
            $success = "Category removed successfully.";
        } else {
            $error = "Error removing category: " . mysqli_error($conn);
        }
    }
}

// Logic to Add a Category with Duplicate Prevention
if (isset($_POST['add_cat'])) {
    $cat_name = mysqli_real_escape_string($conn, $_POST['category_name']);
    $item_code = strtoupper(mysqli_real_escape_string($conn, $_POST['item_code'])); // Capture 3-letter code
    
    if (!empty($cat_name) && strlen($item_code) === 3) {
        
        // CHECK FOR DUPLICATES (Name or Code)
        $check_dupe = mysqli_query($conn, "SELECT id FROM categories WHERE category_name = '$cat_name' OR category_code = '$item_code'");
        
        if (mysqli_num_rows($check_dupe) > 0) {
            $error = "Error: A category with this name or code already exists.";
        } else {
            $query = "INSERT INTO categories (category_name, category_code) VALUES ('$cat_name', '$item_code')";
            if (mysqli_query($conn, $query)) {
                $success = "Category '$cat_name' with code '$item_code' added successfully!";
            } else {
                $error = "Error: " . mysqli_error($conn);
            }
        }
    } else {
        $error = "Category name is required and Item Code must be exactly 3 letters.";
    }
}

// Fetch existing categories
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SOLO Admin | Manage Categories</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #6366f1; --bg: #0f172a; --glass: rgba(255, 255, 255, 0.05); --border: rgba(255, 255, 255, 0.1); }
        body { background: var(--bg); color: white; font-family: 'Outfit', sans-serif; padding: 40px; }
        .card { background: var(--glass); padding: 30px; border-radius: 20px; border: 1px solid var(--border); max-width: 800px; margin: auto; }
        input { width: 100%; padding: 12px; margin-bottom: 15px; background: rgba(0,0,0,0.2); border: 1px solid var(--border); color: white; border-radius: 10px; box-sizing: border-box; }
        button { padding: 12px 25px; background: var(--primary); color: white; border: none; border-radius: 10px; cursor: pointer; font-weight: 600; }
        table { width: 100%; border-collapse: collapse; margin-top: 30px; }
        th, td { padding: 15px; border-bottom: 1px solid var(--border); text-align: left; }
        th { color: #94a3b8; font-size: 14px; text-transform: uppercase; }
        .back-btn { display: inline-block; margin-bottom: 20px; color: #94a3b8; text-decoration: none; font-size: 14px; }
        .btn-delete { color: #f87171; text-decoration: none; font-size: 13px; font-weight: 600; padding: 5px 10px; border: 1px solid rgba(248, 113, 113, 0.2); border-radius: 5px; }
        .btn-delete:hover { background: rgba(248, 113, 113, 0.1); }
        .alert { padding: 12px; border-radius: 10px; margin-bottom: 20px; font-size: 14px; }
        .alert-success { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2); }
        .alert-error { background: rgba(248, 113, 113, 0.1); color: #f87171; border: 1px solid rgba(248, 113, 113, 0.2); }
    </style>
</head>
<body>
    <div class="card">
        <a href="dashboard.php" class="back-btn">← Back to Dashboard</a>
        <h2>Manage Product Categories</h2>
        
        <?php if(isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>

        <form method="post">
            <label>New Category Name</label>
            <input name="category_name" placeholder="e.g. Smartphones, Laptops, Accessories" required>
            <label>3-Letter Item Code Prefix</label>
            <input name="item_code" placeholder="e.g. SMP, LAP, ACC" maxlength="3" required>
            <button name="add_cat" type="submit">Create Category</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Category Name</th>
                    <th>Item Code Prefix</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($categories)): ?>
                <tr>
                    <td>#<?= $row['id'] ?></td>
                    <td><?= $row['category_name'] ?></td>
                    <td style="font-weight:bold; color:var(--primary)"><?= $row['category_code'] ?></td>
                    <td>
                        <a href="manage_categories.php?delete=<?= $row['id'] ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this category?')">Remove</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>