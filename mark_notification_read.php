<?php
include "conn.php";
include "ck_session.php";

$userName = $_SESSION['userSession'];

if (!isSessionAction()) {
    header("location: admin.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['type'])) {
        $notificationType = $_POST['type'];

        if ($notificationType == 'orders') {
            $update_query = "UPDATE `orders` SET read_status = 1 WHERE payment_status = 'Pending'";
        } elseif ($notificationType == 'messages') {
            $update_query = "UPDATE `message` SET read_status = 1 WHERE read_status = 0";
        } elseif ($notificationType == 'users') {
            // Implement user notification marking logic if needed
        }

        if (isset($update_query)) {
            mysqli_query($conn, $update_query) or die('Failed to mark notifications as read');
        }

        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Notification type not set."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}
?>
