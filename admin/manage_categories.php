<?php
session_start();
include "../includes/db.php";

// Access Control: Only Super Admins can manage categories
if ($_SESSION['role'] !== 'super_admin') {
    header("Location: dashboard.php");
    exit();
}

// Logic to Add a Category
if (isset($_POST['add_cat'])) {
    $cat_name = mysqli_real_escape_string($conn, $_POST['category_name']);
    
    if (!empty($cat_name)) {
        $query = "INSERT INTO categories (category_name) VALUES ('$cat_name')";
        if (mysqli_query($conn, $query)) {
            $success = "Category '$cat_name' added successfully!";
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
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
    </style>
</head>
<body>
    <div class="card">
        <a href="dashboard.php" class="back-btn">← Back to Dashboard</a>
        <h2>Manage Product Categories</h2>
        
        <?php if(isset($success)) echo "<p style='color:#10b981'>$success</p>"; ?>
        <?php if(isset($error)) echo "<p style='color:#f87171'>$error</p>"; ?>

        <form method="post">
            <label>New Category Name</label>
            <input name="category_name" placeholder="e.g. Smartphones, Laptops, Accessories" required>
            <button name="add_cat" type="submit">Create Category</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Category Name</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($categories)): ?>
                <tr>
                    <td>#<?= $row['id'] ?></td>
                    <td><?= $row['category_name'] ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>