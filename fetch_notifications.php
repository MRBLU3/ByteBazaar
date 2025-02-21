<?php
session_start();
include 'conn.php'; // Ensure this file connects to your database

// Check if the user is logged in
if (!isset($_SESSION['userSession'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

// Initialize response array
$response = [];

// Query to count new orders
$order_query = "SELECT COUNT(*) as count FROM orders WHERE status = 'pending'";
$order_result = mysqli_query($conn, $order_query);
$order_data = mysqli_fetch_assoc($order_result);
$response['new_orders'] = $order_data['count'];

// Query to count new messages
$message_query = "SELECT COUNT(*) as count FROM messages WHERE status = 'unread'";
$message_result = mysqli_query($conn, $message_query);
$message_data = mysqli_fetch_assoc($message_result);
$response['new_messages'] = $message_data['count'];

// Query to count new users
$user_query = "SELECT COUNT(*) as count FROM users WHERE status = 'new'";
$user_result = mysqli_query($conn, $user_query);
$user_data = mysqli_fetch_assoc($user_result);
$response['new_users'] = $user_data['count'];

// Query to count total notifications
$total_notifications = $response['new_orders'] + $response['new_messages'] + $response['new_users'];
$response['total_notifications'] = $total_notifications;

// Return the response as JSON
echo json_encode($response);
?>
