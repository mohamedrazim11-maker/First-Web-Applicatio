<?php
session_start();
include "../includes/db.php";

if ($_SESSION['role'] !== 'super_admin') {
    header("Location: dashboard.php");
    exit();
}

$success = "";
$error = "";

// Handle Multiple Image Upload
if (isset($_POST['upload_images'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
    $target_dir = "../images/";

    foreach ($_FILES['extra_images']['name'] as $key => $val) {
        $file_name = time() . "_" . basename($_FILES['extra_images']['name'][$key]);
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES['extra_images']['tmp_name'][$key], $target_file)) {
            mysqli_query($conn, "INSERT INTO product_images (product_id, image_path) VALUES ('$product_id', '$file_name')");
            $success = "Images uploaded successfully!";
        }
    }
}

// Handle Image Deletion
if (isset($_GET['delete_img'])) {
    $img_id = mysqli_real_escape_string($conn, $_GET['delete_img']);
    $res = mysqli_query($conn, "SELECT image_path FROM product_images WHERE id = '$img_id'");
    $img_data = mysqli_fetch_assoc($res);
    
    if ($img_data) {
        unlink("../images/" . $img_data['image_path']);
        mysqli_query($conn, "DELETE FROM product_images WHERE id = '$img_id'");
        $success = "Image removed.";
    }
}

$products = mysqli_query($conn, "SELECT id, name FROM products ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Product Photos</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #6366f1; --bg: #0f172a; --glass: rgba(255, 255, 255, 0.05); --border: rgba(255, 255, 255, 0.1); }
        body { background: var(--bg); color: white; font-family: 'Outfit', sans-serif; padding: 40px; }
        .card { background: var(--glass); padding: 30px; border-radius: 20px; border: 1px solid var(--border); max-width: 900px; margin: auto; }
        select, input { width: 100%; padding: 12px; margin-bottom: 15px; background: rgba(0,0,0,0.2); border: 1px solid var(--border); color: white; border-radius: 10px; box-sizing: border-box; }
        button { padding: 12px 25px; background: var(--primary); color: white; border: none; border-radius: 10px; cursor: pointer; font-weight: 600; }
        .gallery { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px; margin-top: 30px; }
        .img-card { position: relative; border-radius: 10px; overflow: hidden; border: 1px solid var(--border); }
        .img-card img { width: 100%; height: 120px; object-fit: cover; }
        .del-btn { position: absolute; top: 5px; right: 5px; background: #f87171; color: white; border: none; padding: 5px; cursor: pointer; border-radius: 5px; font-size: 10px; }
    </style>
</head>
<body>
    <div class="card">
        <a href="dashboard.php" style="color:#94a3b8; text-decoration:none;">← Back</a>
        <h2>Gallery Management</h2>
        
        <form method="post" enctype="multipart/form-data">
            <label>Select Product</label>
            <select name="product_id" required onchange="window.location.href='?pid='+this.value">
                <option value="">-- Choose Product --</option>
                <?php while($p = mysqli_fetch_assoc($products)): ?>
                    <option value="<?= $p['id'] ?>" <?= (isset($_GET['pid']) && $_GET['pid'] == $p['id']) ? 'selected' : '' ?>><?= $p['name'] ?></option>
                <?php endwhile; ?>
            </select>
            
            <label>Upload Extra Photos (Multiple)</label>
            <input type="file" name="extra_images[]" multiple required>
            <button name="upload_images" type="submit">Upload to Gallery</button>
        </form>

        <?php if(isset($_GET['pid'])): 
            $pid = mysqli_real_escape_string($conn, $_GET['pid']);
            $gallery = mysqli_query($conn, "SELECT * FROM product_images WHERE product_id = '$pid'");
        ?>
            <div class="gallery">
                <?php while($img = mysqli_fetch_assoc($gallery)): ?>
                    <div class="img-card">
                        <img src="../images/<?= $img['image_path'] ?>">
                        <a href="?delete_img=<?= $img['id'] ?>&pid=<?= $pid ?>" class="del-btn" onclick="return confirm('Remove this image?')">Delete</a>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>