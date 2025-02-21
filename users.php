<?php

include "ck_session.php";
if(!isSessionAction()){
    header("location: index.php");
}


?>
<?php 


require_once('conn.php');
$query = "select * from users";
$result = mysqli_query($conn,$query);


// require_once 'conn.php';
// require_once 'functions.php';

// $result = display_data();

if(isset($_GET['delete'])){
    $delete_id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM `users` WHERE id = '$delete_id'") or die('query failed');
    header('location: users.php');
 }
 

 
// Count new orders
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="css/users.css">
    <link rel="icon" href="images/code.png">
    <title>Users</title>
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
            <div class="row-mt-5">
        <div class="col">
        <div class="card-mt-5">
 <div class="card-body1">
    <?php
      if(isset($_POST['submit'])){
         $name = $_POST['search'];
         $select_users = mysqli_query($conn, "SELECT * FROM `users` WHERE name LIKE '%{$name}%'") or die('query failed');
         if(mysqli_num_rows($select_users) > 0){
         while($fetch_users = mysqli_fetch_assoc($select_users)){
   ?>
        <table class="table-border1" style="width: 100%">
        <tr>
            <td> -Name- </td>
            <td> -Email- </td>
            <td> -Number- </td>
            <td> -Age- </td>
            <td> -Gender- </td>
            <td> -Username- </td>
            <td> -Password- </td>
            <td> -Usertype- </td>   
            <td></td>
        </tr>
        <tr>
                <td><?php echo $fetch_users['name']; ?></td>
                  <td><?php echo $fetch_users['email']; ?></td>
                  <td><?php echo $fetch_users['number']; ?></td>
                  <td><?php echo $fetch_users['age']; ?></td>
                  <td><?php echo $fetch_users['gender']; ?></td>
                  <td><?php echo $fetch_users['username']; ?></td>
                  <td><?php echo $fetch_users['password']; ?></td>
                  <td><?php echo $fetch_users['usertype']; ?></td>
                  <td><a href="users.php?delete=<?php echo $row['id']; ?>" onclick="return confirm('delete this message?');" class="delete-btn">Delete</a></td>  
                </tr>
        </table>
<?php
            }
         }else{
            echo '<center><p class="empty">no result found!</p>';
         }
      }else{

      }
   ?>
   </div>


        <div class="card-header">
            <center><h2 class="display-6 text-center">User</h2>
        </div>
        <div class="card-body">
        <table class="table-border" style="width: 100%">
        <tr>
            <td> -Name- </td>
            <td> -Email- </td>
            <td> -Number- </td>
            <td> -Age- </td>
            <td> -Gender- </td>
            <td> -Username- </td>
            <td> -Password- </td>
            <td> -Usertype- </td>   
            <td></td>
        </tr>
        <tr>
                <?php 
                  while($row = mysqli_fetch_assoc($result))
                  {
                ?>
                  <td><?php echo $row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name']; ?></td>
                  <td><?php echo $row['email']; ?></td>
                  <td><?php echo $row['number']; ?></td>
                  <td><?php echo $row['birth_date']; ?></td>
                  <td><?php echo $row['gender']; ?></td>
                  <td><?php echo $row['username']; ?></td>
                  <td><?php echo $row['password']; ?></td>
                  <td><?php echo $row['role']; ?></td>
                  <td> <a href="javascript:void(0)" class="delete-btn" onclick="openModal(<?php echo $row['id']; ?>)">Delete</a></td>  
                </tr>
                <?php    
                  }
                
                ?>
                
        </table>
        </div>

        <div id="deleteModal" class="modal" style="display: none;">
    <div class="modal-content">
        <h3>Confirm Deletion</h3>
        <p>Are you sure you want to delete this user?</p>
        <form id="deleteForm" method="get" action="users.php">
            <input type="hidden" name="delete" id="deleteProductId">
            <button type="submit" class="btn confirm-btn">Yes, Delete</button>
            <button type="button" class="btn cancel-btn" onclick="closeModal()">Cancel</button>
        </form>
    </div>
</div>


        </div>
        </div>
        </div>

       


        </div>
    </div>
  
        <script src="https://cdn.jsdelivr.net/npm/chart.js@3.5.1/dist/chart.min.js"></script>
        <script src="js/Employees.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js@3.5.1/dist/chart.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>
        <script>
    // Open the modal and set the product ID
    function openModal(productId) {
        document.getElementById('deleteProductId').value = productId;
        document.getElementById('deleteModal').style.display = 'flex';
    }

    // Close the modal
    function closeModal() {
        document.getElementById('deleteModal').style.display = 'none';
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