<?php
session_start();
include "../includes/db.php";

if ($_SESSION['role'] !== 'super_admin') {
    header("Location: dashboard.php");
    exit();
}

$edit_mode = false;
$edit_id = "";
$edit_name = "";
$edit_code = "";

// Logic to Load Data for Editing
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $edit_id = mysqli_real_escape_string($conn, $_GET['edit']);
    $res = mysqli_query($conn, "SELECT * FROM categories WHERE id = '$edit_id'");
    $data = mysqli_fetch_assoc($res);
    $edit_name = $data['category_name'];
    $edit_code = $data['category_code'];
}

// Logic to Update Category
if (isset($_POST['update_cat'])) {
    $id = $_POST['cat_id'];
    $name = mysqli_real_escape_string($conn, $_POST['category_name']);
    $code = strtoupper(mysqli_real_escape_string($conn, $_POST['item_code']));

    $update_query = "UPDATE categories SET category_name='$name', category_code='$code' WHERE id='$id'";
    if (mysqli_query($conn, $update_query)) {
        header("Location: manage_categories.php?success=Updated");
        exit();
    } else {
        $error = "Update failed: " . mysqli_error($conn);
    }
}

// Logic to Remove a Category
if (isset($_GET['delete'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete']);
    $check_products = mysqli_query($conn, "SELECT id FROM products WHERE category_id = '$id' LIMIT 1");
    if (mysqli_num_rows($check_products) > 0) {
        $error = "Cannot delete: Products are still assigned to this category.";
    } else {
        mysqli_query($conn, "DELETE FROM categories WHERE id = '$id'");
        $success = "Category removed.";
    }
}

// Logic to Add a Category
if (isset($_POST['add_cat'])) {
    $cat_name = mysqli_real_escape_string($conn, $_POST['category_name']);
    $item_code = strtoupper(mysqli_real_escape_string($conn, $_POST['item_code']));
    
    $check_dupe = mysqli_query($conn, "SELECT id FROM categories WHERE category_name = '$cat_name' OR category_code = '$item_code'");
    if (mysqli_num_rows($check_dupe) > 0) {
        $error = "Duplicate name or code found.";
    } else {
        mysqli_query($conn, "INSERT INTO categories (category_name, category_code) VALUES ('$cat_name', '$item_code')");
        $success = "Category added.";
    }
}

$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Categories</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #6366f1; --bg: #0f172a; --glass: rgba(255, 255, 255, 0.05); --border: rgba(255, 255, 255, 0.1); }
        body { background: var(--bg); color: white; font-family: 'Outfit', sans-serif; padding: 40px; }
        .card { background: var(--glass); padding: 30px; border-radius: 20px; border: 1px solid var(--border); max-width: 800px; margin: auto; }
        input { width: 100%; padding: 12px; margin-bottom: 15px; background: rgba(0,0,0,0.2); border: 1px solid var(--border); color: white; border-radius: 10px; }
        button { padding: 12px 25px; background: var(--primary); color: white; border: none; border-radius: 10px; cursor: pointer; font-weight: 600; }
        .btn-edit { color: #fbbf24; text-decoration: none; margin-right: 10px; font-size: 13px; }
        .btn-delete { color: #f87171; text-decoration: none; font-size: 13px; }
        table { width: 100%; border-collapse: collapse; margin-top: 30px; }
        th, td { padding: 15px; border-bottom: 1px solid var(--border); text-align: left; }
        .alert { padding: 12px; border-radius: 10px; margin-bottom: 20px; }
        .alert-success { background: rgba(16, 185, 129, 0.1); color: #10b981; }
    </style>
</head>
<body>
    <div class="card">
        <a href="dashboard.php" style="color:#94a3b8; text-decoration:none;">← Back</a>
        <h2><?php echo $edit_mode ? "Edit Category" : "Manage Categories"; ?></h2>

        <?php if(isset($success) || isset($_GET['success'])): ?>
            <div class="alert alert-success">Operation Successful</div>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="cat_id" value="<?= $edit_id ?>">
            <label>Category Name</label>
            <input name="category_name" value="<?= $edit_name ?>" required>
            <label>3-Letter Code</label>
            <input name="item_code" value="<?= $edit_code ?>" maxlength="3" required>
            
            <?php if($edit_mode): ?>
                <button name="update_cat" type="submit">Update Category</button>
                <a href="manage_categories.php" style="color:white; margin-left:15px;">Cancel</a>
            <?php else: ?>
                <button name="add_cat" type="submit">Create Category</button>
            <?php endIF; ?>
        </form>

        <table>
            <thead>
                <tr>
                    <th>Category Name</th>
                    <th>Code</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($categories)): ?>
                <tr>
                    <td><?= $row['category_name'] ?></td>
                    <td style="color:var(--primary)"><?= $row['category_code'] ?></td>
                    <td>
                        <a href="manage_categories.php?edit=<?= $row['id'] ?>" class="btn-edit">Edit</a>
                        <a href="manage_categories.php?delete=<?= $row['id'] ?>" class="btn-delete" onclick="return confirm('Delete?')">Remove</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>