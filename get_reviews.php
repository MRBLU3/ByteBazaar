<?php
include "conn.php";

if (isset($_GET['product_id'])) {
    $product_id = $_GET['product_id'];

    // Fetch reviews for the product
    $reviews_query = mysqli_query($conn, "SELECT * FROM reviews WHERE product_id = '$product_id' ORDER BY created_at DESC");
    $reviews = [];

    while ($review = mysqli_fetch_assoc($reviews_query)) {
        $reviews[] = $review;
    }

    // Return reviews as JSON
    echo json_encode($reviews);
}
?>
