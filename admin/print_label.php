<?php
session_start();
include "../includes/db.php";

if (!isset($_SESSION['role'])) exit("Unauthorized");

$id = mysqli_real_escape_string($conn, $_GET['id']);
$res = mysqli_query($conn, "SELECT * FROM products WHERE id = '$id'");
$product = mysqli_fetch_assoc($res);

if (!$product) exit("Product not found");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Print Labels - <?= $product['name'] ?></title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 20px; background: #f4f4f9; }
        
        /* Layout for the labels */
        .label-container { display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; }
        
        .label-card {
            width: 250px;
            height: 120px;
            background: white;
            border: 1px solid #000;
            padding: 10px;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .shop-name { font-size: 10px; font-weight: bold; color: #666; text-transform: uppercase; margin-bottom: 5px; }
        .product-name { font-size: 16px; font-weight: bold; margin: 5px 0; }
        .price { font-size: 22px; font-weight: 900; color: #000; }

        /* Hide interface during actual printing */
        @media print {
            .no-print { display: none; }
            body { background: white; padding: 0; }
            .label-card { page-break-inside: avoid; border: 1px solid #ccc; }
        }
    </style>
</head>
<body>

<div class="no-print" style="text-align: center; margin-bottom: 30px;">
    <h2>Label Printing System</h2>
    <p>Set the number of labels you need for <strong><?= $product['name'] ?></strong></p>
    <button onclick="setQuantity()" style="padding: 10px 20px; cursor: pointer; background: #1e40af; color: white; border: none; border-radius: 5px;">
        Set Quantity & Print
    </button>
</div>

<div class="label-container" id="printArea">
    </div>

<script>
function setQuantity() {
    let amount = prompt("How many labels do you want to print?", "1");
    
    if (amount != null && amount > 0) {
        let container = document.getElementById('printArea');
        container.innerHTML = ''; // Clear existing
        
        for (let i = 0; i < amount; i++) {
            container.innerHTML += `
                <div class="label-card">
                    <div class="shop-name">SOLO OFFICIAL STORE</div>
                    <div class="product-name">${'<?= strtoupper($product['name']) ?>'}</div>
                    <div class="price">$${'<?= number_format($product['price'], 2) ?>'}</div>
                </div>
            `;
        }
        
        // Small delay to ensure HTML is rendered before print dialog
        setTimeout(() => { window.print(); }, 500);
    }
}

// Automatically trigger the prompt on page load
window.onload = setQuantity;
</script>

</body>
</html>