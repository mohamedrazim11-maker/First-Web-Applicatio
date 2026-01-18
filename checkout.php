<?php
session_start();
include "includes/db.php";

if(!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$uid = $_SESSION['user'];
$payment_method = isset($_GET['method']) ? $_GET['method'] : 'not_specified';

$cart_q = mysqli_query($conn, 
    "SELECT cart.product_id, cart.quantity, products.price, products.stock 
     FROM cart 
     JOIN products ON cart.product_id = products.id 
     WHERE cart.user_id = $uid"
);

if(mysqli_num_rows($cart_q) > 0) {
    mysqli_begin_transaction($conn);
    try {
        while($item = mysqli_fetch_assoc($cart_q)) {
            $pid = $item['product_id'];
            $qty = $item['quantity'];
            
            // REDUCE STOCK: Decrement the stock column
            $update = mysqli_query($conn, "UPDATE products SET stock = stock - $qty WHERE id = $pid AND stock >= $qty");
            if(!$update) throw new Exception("Insufficient stock for product ID: $pid");
        }

        $total = 0; // Calculate total if needed for orders table
        $order_query = "INSERT INTO orders (user_id, payment_method, status) VALUES ($uid, '$payment_method', 'completed')";
        mysqli_query($conn, $order_query);

        mysqli_query($conn, "DELETE FROM cart WHERE user_id = $uid");
        mysqli_commit($conn);
        header("Location: index.php?order=success");
    } catch (Exception $e) {
        mysqli_rollback($conn);
        die("Error processing order: " . $e->getMessage());
    }
} else {
    header("Location: index.php");
}
?>