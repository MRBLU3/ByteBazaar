<?php
include "conn.php";
include "ck_session.php";

$userName = $_SESSION['userSession'];

$search_query = '';
$search_results = [];

if (isset($_GET['query'])) {
    $search_query = mysqli_real_escape_string($conn, $_GET['query']);
    
    // Search for products that match the query
    $search_results = mysqli_query($conn, "SELECT * FROM `products` WHERE product_name LIKE '%$search_query%'") or die('Query failed.');
}
// Get the logged-in user session


if (isset($_POST['add_to_cart'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $product_price = mysqli_real_escape_string($conn, $_POST['product_price']);
    $product_image = mysqli_real_escape_string($conn, $_POST['product_image']);
    $product_quantity = mysqli_real_escape_string($conn, $_POST['product_quantity']);

    // Get the current user session (assuming session is already started and the user is logged in)
    $userSession = $_SESSION['userSession']; // Make sure this session variable is set correctly

    // Check if the product is already in the cart
    $check_cart_numbers = mysqli_query($conn, "SELECT * FROM `cart` WHERE product_id = '$product_id' AND userSession = '$userSession'") or die('Query failed.');

    if (mysqli_num_rows($check_cart_numbers) > 0) {
        // Product is already in the cart, update the quantity
        $cart_row = mysqli_fetch_assoc($check_cart_numbers);
        $new_quantity = $cart_row['quantity'] + $product_quantity;

        // Update the quantity in the cart
        $update_cart_query = "UPDATE `cart` SET quantity = '$new_quantity' WHERE product_id = '$product_id' AND userSession = '$userSession'";
        mysqli_query($conn, $update_cart_query) or die('Query failed.');
    } else {
        // Product is not in the cart, add a new record
        $add_to_cart_query = "INSERT INTO `cart` (userSession, product_id, product_name, price, quantity, image) 
                              VALUES ('$userSession', '$product_id', '$product_name', '$product_price', '$product_quantity', '$product_image')";
        mysqli_query($conn, $add_to_cart_query) or die('Query failed.');
    }

    // Set a session variable to indicate a successful addition to the cart
    $_SESSION['added_to_cart'] = true;
}


$cart_count = 0;
$cart_count_query = mysqli_query($conn, "SELECT COUNT(*) as total_items FROM `cart` WHERE userSession = '$userName'") or die('query failed');
if ($cart_count_row = mysqli_fetch_assoc($cart_count_query)) {
    $cart_count = $cart_count_row['total_items'];
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>Search Results</title>
    <link rel="stylesheet" href="css/landing.css">
</head>
<body>

<header>
    <a href="homepage.php" class="logo">ByteBazaar</a>
    <nav class="navbar">
        <ul class="nav-links">
            <input type="checkbox" id="checkbox_toggle" />
            <label for="checkbox_toggle" class="hamburger">&#9776;</label>
            <div class="menu"> 
                <li>
                    <form action="search.php" method="get" class="search-form">
                        <input type="text" name="query" placeholder="Search..." required><br>
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
                <li><a class="" href="homepage.php#home">Home</a></li>
                <li><a href="order_status.php">Order</a></li>
                <li><a href="homepage.php#about">About</a></li>
                <li><a href="homepage.php#product">Product</a></li>
                <li><a href="homepage.php#contact">Contact</a></li>
                <li><a href="logoutuser.php">Logout</a></li>
            </div>
        </ul>
    </nav>
</header>

<section class="search-results">
    <h1 class="title">Search Results</h1>

    <?php if ($search_query): ?>
        <p>Showing results for "<strong><?php echo htmlspecialchars($search_query); ?></strong>":</p>
        <div class="box-container">
            <?php if (mysqli_num_rows($search_results) > 0): ?>
                <?php while($fetch_product = mysqli_fetch_assoc($search_results)): ?>
                    <form action="" method="post" class="box">
                        <img class="image" src="uploads/<?php echo htmlspecialchars($fetch_product['image']); ?>" alt="<?php echo htmlspecialchars($fetch_product['product_name']); ?>">
                        <div class="name"><?php echo htmlspecialchars($fetch_product['product_name']); ?></div>
                        <div class="price">₱<?php echo htmlspecialchars($fetch_product['price']); ?></div>
                        <input type="number" min="1" name="product_quantity" value="1" class="qty">
                        <input type="hidden" name="product_id" value="<?php echo $fetch_products['id']; ?>">
                        <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($fetch_product['product_name']); ?>">
                        <input type="hidden" name="product_price" value="<?php echo htmlspecialchars($fetch_product['price']); ?>">
                        <input type="hidden" name="product_image" value="<?php echo htmlspecialchars($fetch_product['image']); ?>">
                        <input type="submit" class="btn" value="Add to Cart" name="add_to_cart">
                    </form>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="empty">No products found for your search.</p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <p>Please enter a search query.</p>
    <?php endif; ?>
</section>


<div id="successModal" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close">&times;</span>
        <p id="successMessage">Product successfully added to cart!</p>
    </div>
</div>


<!-- footer section starts  -->

<section class="footer">

<div class="box-container">

    <div class="box">
        <h3>why choose us?</h3>
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
        <p> <i class="fas fa-map-marker-alt"></i> Barobo, Surigao del Sur, Philippines </p>
        <p> <i class="fas fa-globe"></i> www.ByteBazaar.com </p>
        <p> <i class="fas fa-phone"></i> +639383479055 </p>
    </div>

</div>

<h1 class="credit"> created by <a href="https://github.com/MRBLU3">MRBLU3</a> | all rights reserved. </h1>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    // Check if the session variable for successful cart addition is set
    <?php if (isset($_SESSION['added_to_cart']) && $_SESSION['added_to_cart'] === true): ?>
        // Show the success modal
        $('#successModal').fadeIn();

        // Set a timeout to automatically close the modal after 3 seconds
        setTimeout(function() {
            $('#successModal').fadeOut();
        }, 1000);

        // Reset the session flag after showing the modal
        <?php $_SESSION['added_to_cart'] = false; ?>
    <?php endif; ?>

    // Close the modal when the user clicks the close button
    $(document).on('click', '.close', function() {
        $('#successModal').fadeOut();
    });

    // Close the modal if the user clicks outside of it
    $(window).on('click', function(event) {
        if (event.target == document.getElementById('successModal')) {
            $('#successModal').fadeOut();
        }
    });
});

</script>

</body>
</html>
