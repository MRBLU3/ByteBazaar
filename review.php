<?php
include "ck_session.php";
if(!isSessionAction()){
    header("location: index.php");
}

require_once('conn.php');

// Query to fetch reviews along with the product name
$query = "SELECT reviews.id AS review_id, reviews.rating, reviews.review_text, reviews.username, reviews.created_at, products.product_name, products.id AS product_id
          FROM reviews
          JOIN products ON reviews.product_id = products.id
          ORDER BY products.product_name, reviews.created_at DESC";

$result = mysqli_query($conn, $query);

// Fetch product IDs and group reviews by product
$reviews_by_product = [];
while ($row = mysqli_fetch_assoc($result)) {
    $reviews_by_product[$row['product_id']][] = $row;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_review_id'])) {
    $review_id = $_POST['delete_review_id'];
    $delete_query = "DELETE FROM reviews WHERE id = $review_id";
    if (mysqli_query($conn, $delete_query)) {
        header('location: review.php');
}
}

// Fetch notifications data
$select_order = mysqli_query($conn, "SELECT COUNT(*) AS new_orders FROM `orders` WHERE payment_status = 'Pending'") or die('query failed');
$order_data = mysqli_fetch_assoc($select_order);
$new_orders = $order_data['new_orders'];

// Count new messages
$select_message = mysqli_query($conn, "SELECT COUNT(*) AS new_messages FROM `message` WHERE read_status = 0") or die('query failed');
$message_data = mysqli_fetch_assoc($select_message);
$new_messages = $message_data['new_messages'];

// Count new accounts
$select_users = mysqli_query($conn, "SELECT COUNT(*) AS new_users FROM `users` WHERE created_at > NOW() - INTERVAL 1 DAY") or die('query failed');
$user_data = mysqli_fetch_assoc($select_users);
$new_users = $user_data['new_users'];

// Total notifications
$total_notifications = $new_orders + $new_messages + $new_users;

$notifications = [
    "new_orders" => $new_orders,
    "new_messages" => $new_messages,
    "new_users" => $new_users,
    "total_notifications" => $total_notifications
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous" />
    <link rel="stylesheet" href="css/reviews.css">
    <link rel="icon" href="images/code.png">
    <title>Product Reviews</title>
</head>

<body>
    <div class="container">
        <div class="topbar">
            <div class="logo"></div>
            <div class="notification-icon">
                <i class="fas fa-bell" onclick="toggleNotificationDropdown()"></i>
                <span class="badge"><?php echo $notifications['total_notifications']; ?></span>
                <div id="notificationDropdown" class="dropdown-content" style="display: none;">
                    <ul>
                        <li><a href="order.php" data-notification-type="orders"><i class="fa fa-shopping-cart"></i> New Orders: <?php echo $new_orders; ?></a></li>
                        <li><a href="message.php" data-notification-type="messages"><i class="fa fa-envelope"></i> New Messages: <?php echo $new_messages; ?></a></li>
                        <li><a href="users.php" data-notification-type="users"><i class="fa fa-user"></i> New Users: <?php echo $new_users; ?></a></li>
                    </ul>
                </div>
            </div>
            <div class="user">
                <img src="images/me.jpeg" alt="">
            </div>
        </div>

        <div class="sidebar">
        <ul>
                <li><a href="dashboard.php"><i class="fas fa-dashboard "></i><div>Dashboard</div></a></li>
                <li><a href="inventory.php"><i class="fa fa-list" aria-hidden="true"></i><div>Inventory</div></a></li>
                <li><a href="products.php"><i class="fa fa-shopping-cart"></i><div>Products</div></a></li>
                <li><a href="users.php"><i class="fas fa-users"></i><div>Users</div></a></li>
                <li><a href="order.php"><i class="fas fa-chart-bar"></i><div>Order</div></a></li>
                <li><a href="review.php"><i class="fas fa-chart-area"></i><div>Reviews</div></a></li>
                <li><a href="message.php"><i class="fa-solid fa-message"></i><div>Message</div></a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out"></i><div>Logout</div></a></li>
            </ul>
        </div>

        <div class="main">
            <div class="card-mt-5">
                <center><h1>Product Reviews</h1></center>

                <?php if (!empty($reviews_by_product)): ?>
                    <?php foreach ($reviews_by_product as $product_id => $reviews): ?>
                        <div class="review-container">
                            <h2><?php echo $reviews[0]['product_name']; ?></h2>
                            <table class="review-table">
                                <tr class="review-header">
                                    <th>Rating</th>
                                    <th>Review</th>
                                    <th>Username</th>
                                    <th>Reviewed On</th>
                                    <th>Action</th>
                                </tr>
                                <?php foreach ($reviews as $row): ?>
                                    <tr class="review-item">
                                        <td style="height: 109px;" class="review-stars">
                                            <?php 
                                                // Display stars based on the rating
                                                for ($i = 0; $i < $row['rating']; $i++) {
                                                    echo "★"; 
                                                }
                                                for ($i = $row['rating']; $i < 5; $i++) {
                                                    echo "☆"; 
                                                }
                                            ?>
                                        </td>
                                        <td class="review-text"><?php echo $row['review_text']; ?></td>
                                        <td class="reviewer"><?php echo $row['username'] ? $row['username'] : 'Anonymous'; ?></td>
                                        <td class="review-date"><?php echo date("F j, Y, g:i a", strtotime($row['created_at'])); ?></td>
                                        <td style="width: 100px; height: 100px;">
                                            <button type="button" class="delete-btn" onclick="openDeleteModal(<?php echo $row['review_id']; ?>)">Delete</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <center><p>No reviews available.</p></center>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="closeDeleteModal()">&times;</span>
            <h2>Are you sure you want to delete this review?</h2>
            <form method="POST" action="review.php">
                <input type="hidden" name="delete_review_id" id="deleteReviewId">
                <center><button type="submit" class="delete-confirm-btn">Yes, Delete</button>
                <button type="button" class="cancel-btn" onclick="closeDeleteModal()">Cancel</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.5.1/dist/chart.min.js"></script>
    <script>
    function toggleNotificationDropdown() {
        var dropdown = document.getElementById("notificationDropdown");
        dropdown.style.display = dropdown.style.display === "none" || dropdown.style.display === "" ? "" : "none";
    }

    function openDeleteModal(reviewId) {
        document.getElementById("deleteReviewId").value = reviewId;
        document.getElementById("deleteModal").style.display = "";
    }

    function closeDeleteModal() {
        document.getElementById("deleteModal").style.display = "none";
    }
    </script>
</body>
</html>
