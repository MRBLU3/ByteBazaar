<?php
include "conn.php";

// Get the product ID from the GET request
$product_id = $_GET['product_id'];

// Assuming the actual column name is 'user' or something else; replace it here
$fetch_reviews_query = mysqli_query($conn, "SELECT name, review_text, rating FROM reviews WHERE product_id = '$product_id'") or die('Query failed.');

// Create a table to display the reviews
if (mysqli_num_rows($fetch_reviews_query) > 0) {
    echo '<table>';
    echo '<tr><th>User</th><th>Review</th><th>Rating</th></tr>';
    while ($row = mysqli_fetch_assoc($fetch_reviews_query)) {
        echo '<tr>';
        echo '<td>' . $row['name'] . '</td>'; // Adjust the column name if needed
        echo '<td>' . $row['review_text'] . '</td>';
        echo '<td>' . $row['rating'] . '/5</td>';
        echo '</tr>';
    }
    echo '</table>';
} else {
    echo 'No reviews available.';
}
?>
