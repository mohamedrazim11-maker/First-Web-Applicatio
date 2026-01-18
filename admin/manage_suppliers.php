<?php
session_start();
include "../includes/db.php";
if ($_SESSION['role'] !== 'super_admin') header("Location: dashboard.php");

if (isset($_POST['add_sup'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $addr = mysqli_real_escape_string($conn, $_POST['addr']);
    $c1 = mysqli_real_escape_string($conn, $_POST['c1']);
    $c2 = mysqli_real_escape_string($conn, $_POST['c2']);
    mysqli_query($conn, "INSERT INTO suppliers (supplier_name, address, contact_1, contact_2) VALUES ('$name', '$addr', '$c1', '$c2')");
}
$sups = mysqli_query($conn, "SELECT * FROM suppliers");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Suppliers & Debts</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { background: #0f172a; color: white; font-family: 'Outfit'; padding: 40px; }
        .card { background: rgba(255,255,255,0.05); padding: 25px; border-radius: 15px; border: 1px solid rgba(255,255,255,0.1); margin-bottom: 20px; }
        input, textarea { width: 100%; padding: 10px; margin: 5px 0; background: #000; color: #fff; border: 1px solid #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border-bottom: 1px solid #333; text-align: left; }
        .debit { color: #f87171; font-weight: bold; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Add Supplier</h2>
        <form method="post">
            <input name="name" placeholder="Supplier Name" required>
            <textarea name="addr" placeholder="Address"></textarea>
            <input name="c1" placeholder="Contact 1" required>
            <input name="c2" placeholder="Contact 2">
            <button type="submit" name="add_sup" style="padding: 10px 20px; background: #6366f1; color: white; border: none; cursor: pointer;">Save Supplier</button>
        </form>
    </div>

    <div class="card">
        <h2>Supplier Debit Records</h2>
        <table>
            <tr><th>Supplier</th><th>Contacts</th><th>Total Debit</th></tr>
            <?php while($row = mysqli_fetch_assoc($sups)): ?>
            <tr>
                <td><?= $row['supplier_name'] ?></td>
                <td><?= $row['contact_1'] ?> / <?= $row['contact_2'] ?></td>
                <td class="debit">$<?= number_format($row['total_debit'], 2) ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>