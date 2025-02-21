<?php

include "conn.php";

include "ck_session.php";

$userName = $_SESSION['userSession'];

if(!isSessionAction()){
    header("location: landing.php");
}
if(isset($_POST['update_order'])){

    $order_update_id = $_POST['order_id'];
    $update_payment = $_POST['update_payment'];
    $tracking_number = $_POST['tracking_number'];

    mysqli_query($conn, "UPDATE `orders` SET payment_status = '$update_payment', tracking_number = '$tracking_number' WHERE id = '$order_update_id'") or die('query failed');
    echo '<script>alert("payment status has been updated!")</script>';
    header('location:order.php');
 }
 
 if(isset($_GET['delete'])){
    $delete_id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM `orders` WHERE id = '$delete_id'") or die('query failed');
    header('location:order.php');
 }
 
$select_order = mysqli_query($conn, "SELECT COUNT(*) AS new_orders FROM `orders` WHERE payment_status = 'Pending'") or die('query failed');
$order_data = mysqli_fetch_assoc($select_order);
$new_orders = $order_data['new_orders'];

$select_message = mysqli_query($conn, "SELECT COUNT(*) AS new_messages FROM `message` WHERE read_status = 0") or die('query failed');
$message_data = mysqli_fetch_assoc($select_message);
$new_messages = $message_data['new_messages'];

$select_users = mysqli_query($conn, "SELECT COUNT(*) AS new_users FROM `users` WHERE created_at > NOW() - INTERVAL 1 DAY") or die('query failed');
$user_data = mysqli_fetch_assoc($select_users);
$new_users = $user_data['new_users'];

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="css/boostrap.min.css">
    <link rel="stylesheet" href="css/order.css">
    <link rel="icon" href="images/code.png">
    <title>Order</title>
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
        <section class="orders">

<h1 class="title">Placed Order</h1>

<div class="box-container">
   <?php
   $select_orders = mysqli_query($conn, "SELECT * FROM `orders`") or die('query failed');
   if(mysqli_num_rows($select_orders) > 0){
      while($fetch_orders = mysqli_fetch_assoc($select_orders)){
   ?>
   <div class="box">
      <p> user id : <span><?php echo $fetch_orders['userSession']; ?></span> </p>
      <p> placed on : <span><?php echo $fetch_orders['placed_on']; ?></span> </p>
      <p> name : <span><?php echo $fetch_orders['first_name']; ?> <?php echo $fetch_orders['middle_name']; ?> <?php echo $fetch_orders['last_name']; ?> <?php echo $fetch_orders['suffix']; ?></span> </p>
      <p> number : <span><?php echo $fetch_orders['number']; ?></span> </p>
      <p> email : <span><?php echo $fetch_orders['email']; ?></span> </p>
      <p> address : <span><?php echo $fetch_orders['address']; ?></span> </p>
      <p> total products : <span><?php echo $fetch_orders['product_name']; ?></span> </p>
      <p> total price : <span>₱<?php echo $fetch_orders['total_price']; ?>/-</span> </p>
      <p> payment method : <span><?php echo $fetch_orders['method']; ?></span> </p>
      <form action="" method="post">
      <input type="text" name="tracking_number" 
       placeholder="Enter tracking number" 
       value="<?php echo isset($fetch_orders['tracking_number']) ? $fetch_orders['tracking_number'] : ''; ?>" 
       required 
       class="tracking-input">

                <input type="hidden" name="order_id" value="<?php echo $fetch_orders['id']; ?>">

                <select name="update_payment" required>
    <option value="" selected disabled><?php echo ucfirst(trim($fetch_orders['payment_status'])); ?></option>
    <?php if (strtolower(trim($fetch_orders['payment_status'])) === 'pending'): ?>
        <option value="Approved">Approve</option>
    <?php elseif (strtolower(trim($fetch_orders['payment_status'])) === 'approved'): ?>
        <option value="To Ship">Mark as To Ship</option>
    <?php elseif (strtolower(trim($fetch_orders['payment_status'])) === 'to ship'): ?>
        <option value="Delivered">Mark as Delivered</option>
    <?php endif; ?>
</select>

                <input type="submit" value="update" name="update_order" class="option-btn">
                <a href="javascript:void(0)" class="delete-btn" onclick="openModal(<?php echo $fetch_orders['id']; ?>)">delete</a>
            </form>
   </div>
   <?php
      }
   }else{
      echo '<center><p class="empty">no orders placed yet!</p>';
   }
   ?>
</div>

</section>

<div id="deleteConfirmationModal" class="modal" style="display: none;">
    <div class="modal-content">
        <h2>Confirm Deletion</h2>
        <p>Are you sure you want to delete this order?</p>
        <form id="deleteForm" method="get" action="order.php">
            <input type="hidden" name="delete" id="deleteOrderId">
            <button type="submit" class="btn confirm-btn">Delete</button>
            <button type="button" class="btn cancel-btn" onclick="closeModal()">Cancel</button>
        </form>
    </div>
</div>

    </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.5.1/dist/chart.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>\
    <script>// Open the modal and set the order ID
function openModal(orderId) {
    document.getElementById('deleteOrderId').value = orderId;
    document.getElementById('deleteConfirmationModal').style.display = 'flex';
}

// Close the modal
function closeModal() {
    document.getElementById('deleteConfirmationModal').style.display = 'none';
}
</script>
    <script>
    // Function to toggle the notification dropdown
    function toggleNotificationDropdown() {
        var dropdown = document.getElementById("notificationDropdown");
        if (dropdown.style.display === "none" || dropdown.style.display === "") {
            dropdown.style.display = "block";
        } else {
            dropdown.style.display = "none";
        }
    }

    // Function to fetch notifications via AJAX
    function fetchNotifications() {
        $.ajax({
            url: 'fetch_notifications.php',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data.total_notifications > 0) {
                    $('#notificationDropdown').find('.badge').text(data.total_notifications);
                } else {
                    $('#notificationDropdown').find('.badge').text('');
                }

                $('#notificationDropdown ul').html(`
                    <li><a href="order.php" data-notification-type="orders"><i class="fa fa-shopping-cart"></i> New Orders: ${data.new_orders}</a></li>
                    <li><a href="message.php" data-notification-type="messages"><i class="fa fa-envelope"></i> New Messages: ${data.new_messages}</a></li>
                    <li><a href="users.php" data-notification-type="users"><i class="fa fa-user"></i> New Users: ${data.new_users}</a></li>
                `);
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error: ', status, error);
                console.error('Response:', xhr.responseText);
            }
        });
    }

    // Function to mark notifications as read when clicked
    $(document).on('click', '#notificationDropdown ul a', function(e) {
        e.preventDefault();

        var notificationType = $(this).data('notification-type');

        $.ajax({
            url: 'mark_notification_read.php',
            type: 'POST',
            data: { type: notificationType },
            success: function(response) {
                fetchNotifications();
                window.location.href = $(e.target).attr('href');
            }
        });
    });

    // Fetch notifications every 10 seconds
    setInterval(fetchNotifications, 10000);

    // Fetch notifications when the page loads
    $(document).ready(function() {
        fetchNotifications();
    });

   
</script>

</body>
</html>