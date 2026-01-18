<?php
session_start();
include "../includes/db.php";
if ($_SESSION['role'] !== 'super_admin') header("Location: dashboard.php");

// Handle Deletion
if (isset($_GET['delete'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete']);
    mysqli_query($conn, "DELETE FROM admins WHERE id='$id' AND role='sub_admin'");
    header("Location: manage_admins.php?msg=removed");
}

// Handle Creation
if (isset($_POST['create'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $user = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $pass = md5($_POST['password']);
    
    $q = "INSERT INTO admins (name, email, username, password, role) VALUES ('$name', '$email', '$user', '$pass', 'sub_admin')";
    if(mysqli_query($conn, $q)) { $success = "Sub-Admin created!"; }
}

$admins = mysqli_query($conn, "SELECT * FROM admins WHERE role='sub_admin' ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Nexus Admin | Personnel</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        :root { --primary: #6366f1; --accent: #a855f7; --bg: #0f172a; --glass: rgba(255, 255, 255, 0.05); }
        body { background: var(--bg); color: white; font-family: 'Outfit', sans-serif; margin: 0; padding: 40px; }
        
        .container { display: flex; gap: 40px; max-width: 1200px; margin: 0 auto; }
        
        /* Form Card */
        .card { 
            background: var(--glass); border: 1px solid rgba(255,255,255,0.1); 
            padding: 30px; border-radius: 24px; width: 350px; height: fit-content;
        }

        /* Table Section */
        .list-section { flex: 1; }
        .nexus-table { 
            width: 100%; border-collapse: collapse; background: var(--glass); 
            border-radius: 20px; overflow: hidden; border: 1px solid rgba(255,255,255,0.1);
        }
        .nexus-table th { background: rgba(99, 102, 241, 0.1); padding: 15px; text-align: left; color: var(--primary); font-size: 12px; }
        .nexus-table td { padding: 15px; border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 14px; }
        
        input { 
            width: 100%; padding: 12px; margin-bottom: 15px; background: rgba(255,255,255,0.05); 
            border: 1px solid rgba(255,255,255,0.1); color: white; border-radius: 10px; outline: none;
        }
        
        .btn-create { width: 100%; padding: 12px; background: linear-gradient(45deg, var(--primary), var(--accent)); border: none; color: white; border-radius: 10px; font-weight: 600; cursor: pointer; }
        .btn-delete { color: #f87171; text-decoration: none; font-size: 12px; border: 1px solid rgba(248, 113, 113, 0.3); padding: 5px 10px; border-radius: 6px; transition: 0.3s; }
        .btn-delete:hover { background: #f87171; color: white; }
    </style>
</head>
<body>

<h1 class="animate__animated animate__fadeInDown">Personnel Management</h1>
<a href="dashboard.php" style="color: #64748b; text-decoration: none; font-size: 14px;">← Back to Console</a>

<div class="container" style="margin-top: 30px;">
    <div class="card animate__animated animate__fadeInLeft">
        <h3>Add Sub-Admin</h3>
        <?php if(isset($success)) echo "<p style='color:#10b981'>$success</p>"; ?>
        <form method="post">
            <input name="name" placeholder="Full Name" required>
            <input name="email" type="email" placeholder="Email Address" required>
            <input name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button name="create" class="btn-create">Grant Access</button>
        </form>
    </div>

    <div class="list-section animate__animated animate__fadeInRight">
        <table class="nexus-table">
            <thead>
                <tr>
                    <th>NAME</th>
                    <th>USERNAME</th>
                    <th>LAST LOGIN</th>
                    <th>ACTION</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($admins)) { ?>
                <tr>
                    <td><?= $row['name'] ?></td>
                    <td style="color: #94a3b8;"><?= $row['username'] ?></td>
                    <td style="color: var(--accent);">
                        <?= $row['last_login'] ? date('M d, H:i', strtotime($row['last_login'])) : 'Never' ?>
                    </td>
                    <td>
                        <a href="?delete=<?= $row['id'] ?>" class="btn-delete" onclick="return confirm('Revoke access for this admin?')">Revoke</a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>