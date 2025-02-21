<?php
include "conn.php";

if (isset($_GET['product_id'])) {
    $product_id = $_GET['product_id'];

    // Fetch product details
    $product_query = mysqli_query($conn, "SELECT * FROM `products` WHERE id = '$product_id'");
    $product = mysqli_fetch_assoc($product_query);

    // Check if description is NULL and set a default value if so
    $description = isset($product['description']) && !is_null($product['description']) ? $product['description'] : 'No description available';

    // Fetch reviews for the product
    $reviews_query = mysqli_query($conn, "SELECT * FROM `reviews` WHERE product_id = '$product_id'");
    $reviews = [];
    while ($review = mysqli_fetch_assoc($reviews_query)) {
        $reviews[] = $review;
    }

    // Return product details and reviews as JSON
    echo json_encode([
        'product_name' => $product['product_name'],
        'price' => $product['price'],
        'description' => $description,
        'image' => $product['image'],
        'reviews' => $reviews
    ]);
}
?>

