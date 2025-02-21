<?php
include 'conn.php';

if (isset($_POST['cart_id']) && isset($_POST['cart_quantity'])) {
    $cart_id = $_POST['cart_id'];
    $cart_quantity = $_POST['cart_quantity'];
    mysqli_query($conn, "UPDATE cart SET quantity = '$cart_quantity' WHERE id = '$cart_id'") or die('query failed');
    echo 'success';
}
?>
