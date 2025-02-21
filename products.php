<?php
include "ck_session.php";
if (!isSessionAction()) {
    header("location: index.php");
    exit;
}

include 'conn.php';

if (isset($_POST['add_product'])) {
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $price = $_POST['price'];
    $stocks = $_POST['stocks']; // New stock input

    $image_name = $_FILES['image']['name'];
    $image_size = $_FILES['image']['size'];
    $image_tmp_name = $_FILES['image']['tmp_name'];
    $image_folder = 'uploads/' . $image_name;

    $select_product_name = mysqli_query($conn, "SELECT product_name FROM `products` WHERE product_name = '$product_name'") or die('Query failed');

    if (mysqli_num_rows($select_product_name) > 0) {
        $message[] = 'Product name already added';
    } else {
        $add_product_query = mysqli_query($conn, "INSERT INTO `products`(product_name, price, stocks, image, date_added) VALUES('$product_name', '$price', '$stocks', '$image_name', NOW())") or die('Query failed');

        if ($add_product_query) {
            if ($image_size > 2000000) {
                $message[] = 'Image size is too large';
            } else {
                move_uploaded_file($image_tmp_name, $image_folder);
                $message[] = 'Product added successfully!';
            }
        } else {
            $message[] = 'Product could not be added!';
        }
    }
}

if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];

    // Check if the image exists before trying to delete it
    $delete_image_query = mysqli_query($conn, "SELECT image FROM `products` WHERE id = '$delete_id'") or die('Query failed');
    if (mysqli_num_rows($delete_image_query) > 0) {
        $fetch_delete_image = mysqli_fetch_assoc($delete_image_query);
        $image_path = 'uploads/' . $fetch_delete_image['image'];

        // Check if the file exists before trying to delete it
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }

    // Delete related reviews first to avoid foreign key constraint error
    $delete_reviews_query = mysqli_query($conn, "DELETE FROM `reviews` WHERE product_id = '$delete_id'") or die('Query failed: Unable to delete related reviews.');

    // Delete the product
    $delete_product_query = mysqli_query($conn, "DELETE FROM `products` WHERE id = '$delete_id'") or die('Query failed: Unable to delete product.');

    header('Location: products.php');
    exit;
}

if (isset($_POST['update_product'])) {
    $update_p_id = $_POST['update_p_id'];
    $update_name = $_POST['update_name'];
    $update_price = $_POST['update_price'];
    $update_stocks = $_POST['update_stocks']; // Update stocks

    mysqli_query($conn, "UPDATE `products` SET product_name = '$update_name', price = '$update_price', stocks = '$update_stocks' WHERE id = '$update_p_id'") or die('Query failed');

    $update_image = $_FILES['update_image']['name'];
    $update_image_tmp_name = $_FILES['update_image']['tmp_name'];
    $update_image_size = $_FILES['update_image']['size'];
    $update_folder = 'uploads/' . $update_image;
    $update_old_image = $_POST['update_old_image'];

    if (!empty($update_image)) {
        if ($update_image_size > 2000000) {
            $message[] = 'Image size is too large';
        } else {
            mysqli_query($conn, "UPDATE `products` SET image = '$update_image' WHERE id = '$update_p_id'") or die('Query failed');
            move_uploaded_file($update_image_tmp_name, $update_folder);
            unlink('uploads/' . $update_old_image);
        }
    }

    header('Location: products.php');
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
    <title>Products</title>
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <link rel="stylesheet" href="css/product.css">
    <link rel="icon" href="images/code.png">
    
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

        <section class="add-products">

        <form action="" method="post" enctype="multipart/form-data">
    <h3>add product</h3>
    <input type="text" name="product_name" class="box" placeholder="Enter product name" required>
    <input type="number" min="0" name="price" class="box" placeholder="Enter product price" required>
    <input type="number" min="0" name="stocks" class="box" placeholder="Enter stock quantity" required>
    <input type="file" name="image" accept="image/jpg, image/jpeg, image/png" class="box" required>
    <input type="submit" value="add product" name="add_product" class="btn">
</form>


</section>

<!-- product CRUD section ends -->

<section class="products" style="padding-top: 0;">

   <div class="box-container">
   <?php
      if(isset($_POST['submit'])){
         $search_item = $_POST['search'];
         $select_products = mysqli_query($conn, "SELECT * FROM `products` WHERE name LIKE '%{$search_item}%'") or die('query failed');
         if(mysqli_num_rows($select_products) > 0){
         while($fetch_product = mysqli_fetch_assoc($select_products)){
   ?>
   <div class="box">
    <img src="uploads/<?php echo $fetch_product['image']; ?>" alt="">
    <div class="name"><?php echo $fetch_product['product_name']; ?></div>
    <div class="price">₱<?php echo $fetch_product['price']; ?></div>
    <div class="stocks">Stock:<?php echo $fetch_products['stocks'];?> </div>
    <a href="products.php?update=<?php echo $fetch_product['id']; ?>" class="option-btn">update</a>
    <a href="products.php?delete=<?php echo $fetch_product['id']; ?>" class="delete-btn" onclick="return confirm('delete this product?');">delete</a>
</div>

   <?php
            }
         }else{
            echo '<p class="empty">no result found!</p>';
         }
      }else{
      }
   ?>
   </div>
  

</section>

<!-- show products  -->

<section class="show-products">

<div class="box-container">

   <?php
      $select_products = mysqli_query($conn, "SELECT * FROM `products`") or die('query failed');
      if(mysqli_num_rows($select_products) > 0){
         while($fetch_products = mysqli_fetch_assoc($select_products)){
   ?>
   <div class="box">
      <img src="uploads/<?php echo $fetch_products['image']; ?>" alt="">
      <div class="name"><?php echo $fetch_products['product_name']; ?></div>
      <div class="price">₱<?php echo $fetch_products['price']; ?></div>
      <div class="stocks">Stock:<?php echo $fetch_products['stocks']; ?> </div> 
      <a href="products.php?update=<?php echo $fetch_products['id']; ?>" class="option-btn">update</a>
      <a href="javascript:void(0)" class="delete-btn" onclick="openModal(<?php echo $fetch_products['id']; ?>)">delete</a>
   </div>
   <?php
      }
   }else{
      echo '<center><h1 class="empty">no products added yet!</h1>';
   }

   ?>
</div>

</section>


<div id="deleteModal" class="modal" style="display: none;">
    <div class="modal-content">
        <h3>Confirm Deletion</h3>
        <p>Are you sure you want to delete this product?</p>
        <form id="deleteForm" method="get" action="products.php">
            <input type="hidden" name="delete" id="deleteProductId">
            <button type="submit" class="btn confirm-btn">Yes, Delete</button>
            <button type="button" class="btn cancel-btn" onclick="closeModal()">Cancel</button>
        </form>
    </div>
</div>

<!-- show product section ends -->

<!-- update products  -->
<section class="edit-product-form">

   <?php
      if(isset($_GET['update'])){
         $update_id = $_GET['update'];
         $update_query = mysqli_query($conn, "SELECT * FROM `products` WHERE id = '$update_id'") or die('query failed');
         if(mysqli_num_rows($update_query) > 0){
            while($fetch_update = mysqli_fetch_assoc($update_query)){
   ?>
   <form action="" method="post" enctype="multipart/form-data">
      <input type="hidden" name="update_p_id" value="<?php echo $fetch_update['id']; ?>">
      <input type="hidden" name="update_old_image" value="<?php echo $fetch_update['image']; ?>">
      <img src="uploads/<?php echo $fetch_update['image']; ?>" alt="">
      <span>Product Name:</span>
      <input type="text" name="update_name" value="<?php echo $fetch_update['product_name']; ?>" class="box" required placeholder="enter product name">
      <span>Product Price:</span>
      <input type="number" name="update_price" value="<?php echo $fetch_update['price']; ?>" min="0" class="box" required placeholder="enter product price">
      <span>Product Stock:</span>
      <input type="number" name="update_stocks" value="<?php echo $fetch_update['stocks']; ?>" min="0" class="box" required placeholder="enter product stock"> 
      <input type="file" class="box" name="update_image" accept="image/jpg, image/jpeg, image/png">
      <input type="submit" value="update" name="update_product" class="btn">
      <a href="products.php" class="delete-btn">Cancel</a>
   </form>
   <?php
            }
      }
      }else{
         echo '<script>document.querySelector(".edit-product-form").style.display = "none";</script>';
      }
   ?>

</section>

<!-- update products section ends -->

</div>
</div>

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
