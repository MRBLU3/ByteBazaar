<?php

include "conn.php";
include "ck_session.php";

$userName = $_SESSION['userSession'];

if(!isSessionAction()){
    header("location: index.php");
    exit();
}
$firstName = $_SESSION['userSession'];
if (!isset($_SESSION['modal_displayed'])) {
    $_SESSION['modal_displayed'] = false;
}

$order_status_query = mysqli_query($conn, "SELECT payment_status FROM `orders` WHERE userSession = '$userName' LIMIT 1") or die('query failed');
$order_status_row = mysqli_fetch_assoc($order_status_query);

if ($order_status_row) {
    $order_approved = (strtolower($order_status_row['payment_status']) === 'approved');
    $order_delivered = (strtolower($order_status_row['payment_status']) === 'delivered');
} else {
    $order_approved = false;
    $order_delivered = false;
}
$show_notification = false;
if (($order_approved || $order_delivered) && !$_SESSION['modal_displayed']) {
    $show_notification = true;
    $_SESSION['modal_displayed'] = true; 
}

if (isset($_POST['add_to_cart'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $product_price = mysqli_real_escape_string($conn, $_POST['product_price']);
    $product_image = mysqli_real_escape_string($conn, $_POST['product_image']);
    $product_quantity = intval($_POST['product_quantity']); 
    $userSession = $_SESSION['userSession']; 

    $get_product_stock = mysqli_query($conn, "SELECT stocks FROM `products` WHERE id = '$product_id'") or die('Query failed.');
    $product_stock = mysqli_fetch_assoc($get_product_stock)['stocks'];

    if ($product_quantity > $product_stock) {
        echo "<script>alert('Requested quantity exceeds available stock.');</script>";
    } else {
        $check_cart_numbers = mysqli_query($conn, "SELECT * FROM `cart` WHERE product_id = '$product_id' AND userSession = '$userSession'") or die('Query failed.');
        if (mysqli_num_rows($check_cart_numbers) > 0) {

            $cart_row = mysqli_fetch_assoc($check_cart_numbers);
            $new_quantity = $cart_row['quantity'] + $product_quantity;
            if ($new_quantity > $product_stock) {
                echo "<script>alert('Adding this quantity will exceed available stock.');</script>";
            } else {
                $update_cart_query = "UPDATE `cart` SET quantity = '$new_quantity' WHERE product_id = '$product_id' AND userSession = '$userSession'";
                mysqli_query($conn, $update_cart_query) or die('Query failed.');
            }
        } else {
            $add_to_cart_query = "INSERT INTO `cart` (userSession, product_id, product_name, price, quantity, image) 
                                  VALUES ('$userSession', '$product_id', '$product_name', '$product_price', '$product_quantity', '$product_image')";
            mysqli_query($conn, $add_to_cart_query) or die('Query failed.');
        }
        $_SESSION['added_to_cart'] = true;
    }
}

$order_count = 0;
$userName = $_SESSION['userSession']; 
$order_count_query = mysqli_query($conn, "SELECT COUNT(*) as total_orders FROM `orders` WHERE userSession = '$userName' AND payment_status != 'delivered'") or die('Query failed');
if ($order_count_row = mysqli_fetch_assoc($order_count_query)) {
    $order_count = $order_count_row['total_orders'];
} else {
    $order_count = 0;
}

$cart_count = 0;
$cart_count_query = mysqli_query($conn, "SELECT COUNT(*) as total_items FROM `cart` WHERE userSession = '$userName'") or die('query failed');
if ($cart_count_row = mysqli_fetch_assoc($cart_count_query)) {
    $cart_count = $cart_count_row['total_items'];
}
?>
<?php
include "conn.php";
if(isset($_POST['send'])){

    $name = $_POST['name'];
    $email = $_POST['email'];
    $number = $_POST['number'];
    $msg = $_POST['message'];
    $select_message = mysqli_query($conn, "SELECT * FROM `message` WHERE name = '$name' AND email = '$email' AND number = '$number' AND message = '$msg'") or die('query failed');
    if(mysqli_num_rows($select_message) > 0){
    }else{
       mysqli_query($conn, "INSERT INTO `message` (name, email, number, message) VALUES('$name', '$email', '$number', '$msg')") or die('query failed');
    }
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="css/homepage.css">

</head>
<body>
<header>
    <a href="homepage.php#home" class="logo">ByteBazaar</a>
 
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
             <?php if($cart_count > 0): ?>
                 <span class="cart-count"><?php echo $cart_count; ?></span>
             <?php endif; ?>
               </a>
                </li>
                <li><a class="" href="#home">Home</a></li>
                <li>
                <a href="order_status.php">
    Order
    <?php if($order_count > 0): ?>
            <span class="cart-count"><?php echo $order_count; ?></span>
    <?php endif; ?>
    <?php if ($order_approved): ?>
        <span class="order-status"></span>
    <?php elseif ($order_delivered): ?>
        <span class="order-status"></span>
    <?php endif; ?>
</a>
</li>
                <li><a href="#about">About</a></li>
                <li><a href="#product">Product</a></li>
                <li><a href="#contact">Contact</a></li>
                <li><a href="logoutuser.php">Logout</a></li>
            </div>
        </ul>
    </nav>
</header>
<div id="orderNotification" class="notification" 
     style="display: <?php echo $show_notification ? 'block' : 'none'; ?>;">
    <?php if ($order_approved): ?>
        Your order has been approved!
    <?php elseif ($order_delivered): ?>
        Your order has been delivered!
    <?php endif; ?>
</div>

<div id="welcomeModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Welcome,  <?php echo $firstName; ?>!</h2>
    </div>
</div>
<section class="home" id="home">

    <div class="content">
        <h1>Best Quality Programming Books For You</h1>
        <p>Learn programming.</p>
        <a href="#product"><button class="btn">Order now</button></a>
    </div>

    <div class="image">
        <img src="images/prog.jpg" alt="">
    </div>

</section>

<section class="about" id="about">

    <div class="image">
        <img src="images/prog.png" alt="">
    </div>

    <div class="content">
        <h1 class="heading">About Us.</h1>
        <h3>ByteBazaar</h3>
        <p>is an online book store web base application that allows customers to buy books online.</p>
    </div>

</section>
<section class="products" id="product">
    <h1 class="title">Latest Products</h1>
    <div class="box-container">
        <?php  
            $select_products = mysqli_query($conn, "SELECT * FROM `products` LIMIT 6") or die('query failed');
            if(mysqli_num_rows($select_products) > 0){
                while($fetch_products = mysqli_fetch_assoc($select_products)){
                    $product_id = $fetch_products['id'];
                    $product_name = $fetch_products['product_name'];
                    $product_price = $fetch_products['price'];
                    $product_image = $fetch_products['image'];
                    $product_stocks = $fetch_products['stocks'];
        ?>
        <form action="" method="post">
            <div class="box" data-name="<?php echo $fetch_products['product_name']; ?>" data-price="<?php echo $fetch_products['price']; ?>" data-image="<?php echo $fetch_products['image']; ?>" data-id="<?php echo $fetch_products['id']; ?>">
                <img class="image" src="uploads/<?php echo $fetch_products['image']; ?>" alt="">
                <div class="name"><?php echo $fetch_products['product_name']; ?></div>
                <div class="name">Stocks: <?php echo $fetch_products['stocks']; ?></div>
                <?php if ($product_stocks == 0) { ?>
                    <span class="out-of-stock">Out of stock</span>
                <?php } ?>
                <div class="price">₱<?php echo $fetch_products['price']; ?></div>
                <input type="number" min="1" name="product_quantity" value="1" class="qty">
                <input type="hidden" name="product_id" value="<?php echo $fetch_products['id']; ?>">
                <input type="hidden" name="product_name" value="<?php echo $fetch_products['product_name']; ?>">
                <input type="hidden" name="product_price" value="<?php echo $fetch_products['price']; ?>">
                <input type="hidden" name="product_image" value="<?php echo $fetch_products['image']; ?>">

                <!-- Add to Cart Button will be disabled if stock is zero -->
                <input type="submit" class="btn" value="add to cart" name="add_to_cart" <?php echo ($product_stocks == 0) ? 'disabled' : ''; ?>>
                
                <!-- Displaying a message for out of stock -->

                <button type="button" class="btn view-details-btn" onclick="openProductModal(<?php echo $fetch_products['id']; ?>)">View Details</button>
            </div>
        </form>
        <?php
                }
            } else {
                echo '<p class="empty">no products added yet!</p>';
            }
        ?>
    </div>

<div class="load-more" style="margin-top: 2rem; text-align:center">
    <div id="productModal" class="modal">
        <div class="modal-content">
            <div class="modal-details">
                <img id="modalImage" src="" alt="Product Image">
                <h2 id="modalName"></h2>
                <p id="modalPrice"></p>
                <p id="modalDescription"></p>
            </div>
            <div class="modal-reviews">
                <h3>Reviews</h3>
                <table id="reviewsList">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Rating</th>
                            <th>Review</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody></tbody> 
                </table>
            </div>
        </div>
    </div>
</div>
</section>
<div id="successModal" class="modal" style="display:none;">
    <div class="modal-content">
        <p id="successMessage">Product successfully added to cart!</p>
    </div>
</div>

<section class="contact" id="contact">

<h1 class="heading">Contact</h1>

<h1 class="title">Contact us for latest updates</h1>

<form action="" method="post">

    <div class="inputBox">
        <input type="text" name="name" required placeholder="name">
    </div>
    <div class="inputBox">
        <input type="email" name="email" required placeholder="your email">
        <input type="number" name="number" required placeholder="number">
    </div>
    <textarea name="message" id="" cols="30" rows="10" placeholder="message"></textarea>

    <button type="submit" value="send message" name="send" class="btn" onclick="alert('Message was sent successfully');">Send Message</button>
</form>

</section>

<section class="footer">

<div class="box-container">

    <div class="box">
        <h3>Why choose us?</h3>
        <p>Choosing ByteBazaar can allow you to purchase the best selling books which are great if you're starting in programming.</p>
    </div>

    <div class="box">
        <h3>Quick Links</h3>
        <a href="#home">Home</a>
        <a href="#about">About</a>
        <a href="#product">Products</a>
        <a href="#contact">Contact</a>
    </div>

    <div class="box">
        <h3>contact us</h3>
        <a href="https://www.google.com/maps/place/8%C2%B032'17.6%22N+126%C2%B007'12.9%22E/@8.5382283,126.1196023,19z/data=!3m1!4b1!4m4!3m3!8m2!3d8.538227!4d126.120246?entry=ttu&g_ep=EgoyMDI0MDkzMC4wIKXMDSoASAFQAw%3D%3D"><p> <i class="fas fa-map-marker-alt"></i> Barobo, Surigao del Sur, Philippines </p></a>
        <p> <i class="fa fa-at"></i> ByteBazaar@gmail.com </p>
        <p> <i class="fas fa-phone"></i> +639383479055 </p>
    </div>

</div>

<h1 class="credit"> created by <a href="https://github.com/MRBLU3">MRBLU3</a> | all rights reserved. </h1>
</section>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
function openProductModal(productId) {
    const modal = document.getElementById("productModal");
    const closeButton = document.querySelector(".close");
    const reviewsList = document.getElementById("reviewsList");

    fetch(`get_product_details.php?product_id=${productId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById("modalImage").src = "uploads/" + data.image;
            document.getElementById("modalName").textContent = data.product_name;
            document.getElementById("modalPrice").textContent = "₱" + data.price;

            reviewsList.innerHTML = '';  

            let reviewsTable = `
                <table border="1" cellpadding="5" cellspacing="0">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Rating</th>
                            <th>Review</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            if (data.reviews && data.reviews.length > 0) {
                data.reviews.forEach(review => {
                    reviewsTable += `
                        <tr>
                            <td>${review.username}</td>
                            <td>${review.rating} Stars</td>
                            <td>${review.review_text}</td>
                            <td>${review.created_at}</td>
                        </tr>
                    `;
                });
            } else {
                reviewsTable += `
                    <tr>
                        <td colspan="4">No reviews yet.</td>
                    </tr>
                `;
            }
            reviewsTable += `
                    </tbody>
                </table>
            `;
            reviewsList.innerHTML = reviewsTable;
        })
        .catch(error => {
            console.error("Error fetching product details:", error);
        });
    modal.style.display = "block";
    closeButton.onclick = function() {
        modal.style.display = "none";
    };
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    };
}
</script>
<style>
#orderNotification {
    transition: opacity 0.3s ease;
    opacity: 1;
}

#orderNotification.hide {
    opacity: 0;
}
</style>
<script>
window.onload = function() {
    var notification = document.getElementById('orderNotification');
    if (notification.style.display === 'block') {
        setTimeout(function() {
            notification.style.display = 'none'; 
        }, 5000); 
    }
    notification.addEventListener('click', function() {
        notification.style.display = 'none';
    });
};
</script>
<script>
    var modal = document.getElementById("welcomeModal");
    var span = document.getElementsByClassName("close")[0];
    span.onclick = function() {
        modal.style.display = "none";
    }
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
    setTimeout(function() {
        modal.style.display = "none";
    }, 3000);
</script>
<script>
    <?php if (!isset($_SESSION['modal_displayed']) || $_SESSION['modal_displayed'] == false): ?>
        $(document).ready(function() {
            $('#welcomeModal').show();
        });
        <?php $_SESSION['modal_displayed'] = true; ?>
    <?php endif; ?>
</script>
<script>
    <?php if (isset($_SESSION['added_to_cart']) && $_SESSION['added_to_cart'] === true): ?>
        $(document).ready(function() {
            $('#successModal').show();
            setTimeout(function() {
                $('#successModal').hide();
            }, 1500);
            <?php $_SESSION['added_to_cart'] = false; ?>
        });
    <?php endif; ?>
    $(document).on('click', '.close', function() {
        $('#successModal').hide();
    });
    $(window).on('click', function(event) {
        if (event.target == document.getElementById('successModal')) {
            $('#successModal').hide();
        }
    });
</script>
</body>
</html>