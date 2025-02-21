<?php
session_start();  // Start the session
include "conn.php";  // Include database connection

header('Content-Type: application/json');  // Set response header for JSON output

// Check if the user is logged in
if (isset($_SESSION['userSession'])) {
    $userName = $_SESSION['userSession'];  // Get username from session

    // Fetch user ID from the database based on the username
    $user_query = mysqli_query($conn, "SELECT username FROM users WHERE username = '$userName' LIMIT 1");
    if ($user_row = mysqli_fetch_assoc($user_query)) {
        $user_id = $user_row['username'];  // Get the user ID

        // Handle review submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);  // Sanitize product ID
            $review_text = mysqli_real_escape_string($conn, $_POST['review_text']);  // Sanitize review text
            $rating = intval($_POST['rating']);  // Ensure rating is an integer

            // Validate input
            if (!empty($product_id) && !empty($review_text) && $rating >= 1 && $rating <= 5) {
                // Insert review into the database
                $query = "INSERT INTO `reviews` (product_id, username, review_text, rating) 
                          VALUES ('$product_id', '$user_id', '$review_text', '$rating')";
                if (mysqli_query($conn, $query)) {
                    header("Location: order_status.php");
                    exit();
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to submit review.']);
                    exit();
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Invalid input data.']);
                exit();
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
            exit();
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'User not found.']);
        exit();
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'You need to be logged in to submit a review.']);
    exit();
}
?>
