<?php
include "conn.php";
include "ck_session.php";

// Get the username from session
$userName = $_SESSION['userSession'];

// Check if session is valid
if (!isSessionAction()) {
    header("location: homepage.php");
    exit();
}

// Query for user orders
$order_query = mysqli_query($conn, "SELECT * FROM `orders` WHERE userSession = '$userName' ORDER BY placed_on DESC");


$order_count = 0; // Default value if no orders exist
$order_count_query = mysqli_query($conn, "SELECT COUNT(*) as total_orders FROM `orders` WHERE userSession = '$userName' AND payment_status != 'delivered'") or die('Query failed');
if ($order_count_row = mysqli_fetch_assoc($order_count_query)) {
    $order_count = $order_count_row['total_orders'];
} else {
    $order_count = 0; // Default value if no orders exist
}

// Count the items in the cart
$cart_count = 0;
$cart_count_query = mysqli_query($conn, "SELECT COUNT(*) as product_id FROM `cart` WHERE userSession = '$userName'") or die('query failed');
if ($cart_count_row = mysqli_fetch_assoc($cart_count_query)) {
    $cart_count = $cart_count_row['product_id'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ByteBazaar</title>

    <link rel="icon" href="images/logo.png">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous" />
    <link rel="stylesheet" href="css/order_status.css">
</head>
<body>

<!-- Header and navigation -->
<header>
    <a href="homepage.php" class="logo">ByteBazaar</a>
    <nav class="navbar">
        <ul class="nav-links">
            <input type="checkbox" id="checkbox_toggle" />
            <label for="checkbox_toggle" class="hamburger">&#9776;</label>
            <div class="menu"> 
                <li>
                    <form action="search.php" method="get" class="search-form">
                        <input type="text" name="query" placeholder="Search..." required>
                        <button type="submit" class="btn">Search</button>
                    </form>
                </li>
                <li>
                    <a href="cart.php">
                        <i class="fas fa-shopping-cart" aria-hidden="true"></i>
                        <?php if ($cart_count > 0): ?>
                            <span class="cart-count"><?php echo $cart_count; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li><a href="homepage.php">Home</a></li>
                <li>
                <a href="order_status.php">
    Order
    <?php if($order_count > 0): ?>
            <span class="cart-count"><?php echo $order_count; ?></span>
    <?php endif; ?>
</a>
</li>
                <li><a href="homepage.php">About</a></li>
                <li><a href="homepage.php#product">Product</a></li>
                <li><a href="homepage.php#contact">Contact</a></li>
                <li><a href="logoutuser.php">Logout</a></li>
            </div>
        </ul>
    </nav>
</header>

<!-- Order Status Section -->
<div class="heading">
    <h3>Your Order Status</h3>
</div>

<section class="order-status">
    <?php if (mysqli_num_rows($order_query) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Order Date</th>
                    <th>Shipping Provider</th>
                    <th>Tracking Number</th>
                    <th>Products</th>
                    <th>Total Price</th>
                    <th>Status</th>
                    <th>Review</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($order = mysqli_fetch_assoc($order_query)): ?>
                    <tr>
                        <td><?php echo $order['placed_on']; ?></td>
                        <td><?php echo $order['shipping']; ?></td>
                        <td><?php echo $order['tracking_number']; ?></td>
                        <td><?php echo $order['product_name']; ?></td>
                        <td>₱<?php echo $order['total_price']; ?></td>
                        <td><?php echo $order['payment_status']; ?></td>
                        <td>
                            <?php 
                            if ($order['payment_status'] === 'Delivered') {
                                $product_id = $order['product_id'];

                                // Check if a review already exists for this product
                                $review_query = mysqli_query($conn, "SELECT * FROM `reviews` WHERE product_id = '$product_id' AND username = '$userName'");
                                    if (mysqli_num_rows($review_query) > 0) {
                                        echo '<p class="success-message">Review Submitted</p>';
                                    } else {
                                        ?>
                                        <form action="submit_review.php" method="post" class="form1">
                                            <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                            <input type="hidden" name="user_id" value="<?php echo $userName; ?>">
                                            <textarea name="review_text" required placeholder="Write your review..."></textarea>
                                        <select name="rating" required>
                                            <option value="">Rate this product...</option>
                                            <option value="1">1 Star</option>
                                            <option value="2">2 Stars</option>
                                            <option value="3">3 Stars</option>
                                            <option value="4">4 Stars</option>
                                            <option value="5">5 Stars</option>
                                        </select>
                                        <input type="submit" value="Submit Review">
                                    </form>
                                    <?php
                                }
                            } else {
                                echo '<p>Order Pending</p>';
                            }
                            ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="empty">No orders found</p>
    <?php endif; ?>
</section>

</body>
</html>
