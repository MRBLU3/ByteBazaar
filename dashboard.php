<?php
include 'conn.php';
include 'ck_session.php';

$userName = $_SESSION['userSession'];
// Ensure session exists
if (!isSessionAction()) {
    header("location: admin.php");
    exit();
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="css/dash.css">
    <link rel="icon" href="images/logo.png">
    <title>Admin panel</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.5.1/dist/chart.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
            <div class="cards">
                <!-- Existing card elements -->
                <a href="products.php">
                    <div class="card">
                        <div class="card-content">
                            <?php  
                            $select_products = mysqli_query($conn, "SELECT * FROM `products`") or die('query failed');
                            $count = mysqli_num_rows($select_products);
                            ?>
                            <div class="number"><?php echo $count; ?></div>
                            <div class="card-name">Products</div>
                        </div>
                        <div class="icon-box"><i class="fa fa-shopping-cart"></i></div>
                    </div>
                </a>
                <a href="order.php">
                    <div class="card">
                        <div class="card-content">
                            <?php  
                            $select_order = mysqli_query($conn, "SELECT * FROM `orders`") or die('query failed');
                            $count = mysqli_num_rows($select_order);
                            ?>
                            <div class="number"><?php echo $count; ?></div>
                            <div class="card-name">Orders</div>
                        </div>
                        <div class="icon-box"><i class="fa fa-shopping-cart"></i></div>
                    </div>
                </a>
                <div class="card">
                    <div class="card-content">
                        <?php
                        $total_completed = 0;
                        $select_completed = mysqli_query($conn, "SELECT total_price FROM `orders` WHERE payment_status = 'delivered'") or die('query failed');
                        if(mysqli_num_rows($select_completed) > 0){
                           while($fetch_completed = mysqli_fetch_assoc($select_completed)){
                              $total_price = $fetch_completed['total_price'];
                              $total_completed += $total_price;
                           }
                        }
                        ?>
                        <div class="number"><?php echo $total_completed; ?></div>
                        <div class="card-name">Earning</div>
                    </div>
                    <div class="icon-box"><i class='fa-solid fa-peso-sign'></i></div>
                </div>
                <a href="message.php">
                    <div class="card">
                        <div class="card-content">
                            <?php  
                            $select_message = mysqli_query($conn, "SELECT * FROM `message`") or die('query failed');
                            $count = mysqli_num_rows($select_message);
                            ?>
                            <div class="number"><?php echo $count; ?></div>
                            <div class="card-name">Message</div>
                        </div>
                        <div class="icon-box"><i class="fa-solid fa-message"></i></div>
                    </div>
                </a>
                <a href="users.php">
                    <div class="card">
                        <div class="card-content">
                            <?php  
                            $select_users = mysqli_query($conn, "SELECT * FROM `users`") or die('query failed');
                            $count = mysqli_num_rows($select_users);
                            ?>
                            <div class="number"><?php echo $count; ?></div>
                            <div class="card-name">Total Accounts</div>
                        </div>
                        <div class="icon-box"><i class='fa-solid fa-users'></i></div>
                    </div>
                </a>

                <a href="review.php">
        <div class="card">
            <div class="card-content">
                <?php  
                $select_reviews = mysqli_query($conn, "SELECT * FROM `reviews`") or die('query failed');
                $count = mysqli_num_rows($select_reviews);
                ?>
                <div class="number"><?php echo $count; ?></div>
                <div class="card-name">Reviews</div>
            </div>
            <div class="icon-box"><i class="fa fa-star"></i></div>
        </div>
    </a>
    <a href="order.php">
    <div class="card">
        <div class="card-content">
            <?php  
            // Modify the query to count only pending orders
            $select_order = mysqli_query($conn, "SELECT * FROM `orders` WHERE payment_status = 'pending'") or die('query failed');
            $count = mysqli_num_rows($select_order);
            ?>
            <div class="number"><?php echo $count; ?></div>
            <div class="card-name">Pending Orders</div>
        </div>
        <div class="icon-box"><i class="fa fa-shopping-cart"></i></div>
    </div>
</a>

<a href="order.php">
    <div class="card">
        <div class="card-content">
            <?php  
            // Modify the query to count only pending orders
            $select_order = mysqli_query($conn, "SELECT * FROM `orders` WHERE payment_status = 'Approved'") or die('query failed');
            $count = mysqli_num_rows($select_order);
            ?>
            <div class="number"><?php echo $count; ?></div>
            <div class="card-name">Approved Orders</div>
        </div>
        <div class="icon-box"><i class="fa fa-shopping-cart"></i></div>
    </div>
</a>

           
            </div>

            <!-- New Chart Section -->
            <center><canvas id="myChart" style="width: 300px; height: 400px;"></canvas></center>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.5.1/dist/chart.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>

    


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

<script>
let salesChart;

function fetchSalesData() {
    $.ajax({
        url: 'fetch_sales_data.php', // Ensure this path is correct
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            console.log('Sales Data:', data); // Log data for debugging
            updateSalesChart(data.labels, data.sales);
        },
        error: function(xhr, status, error) {
            console.error('Error fetching sales data:', error);
        }
    });
}

function updateSalesChart(labels, sales) {
    const ctx = document.getElementById("myChart").getContext("2d");

    // Destroy previous chart if it exists
    if (salesChart) {
        salesChart.destroy();
    }

    // Create a new chart
    salesChart = new Chart(ctx, {
        type: "bar",
        data: {
            labels: labels,
            datasets: [{
                label: 'Sales',
                backgroundColor: "#4e73df",
                data: sales
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: { display: false },
            title: {
                display: true,
                text: "Monthly Sales Overview"
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// Call the fetch function when the document is ready
$(document).ready(function() {
    fetchSalesData();
});

</script> 



</body>
</html>
