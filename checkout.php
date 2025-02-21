<?php
include "conn.php";
include "ck_session.php";

$userName = $_SESSION['userSession'];

if(!isSessionAction()){
   header("location: homepage.php");
}

// Initialize cart variables
$cart_total = 0;
$cart_items = [];

// Check if products are selected from a query parameter
if (isset($_GET['products'])) {
    // Extract selected product IDs from the URL
    $selected_products = explode(',', $_GET['products']);
    $product_id = implode(',', $selected_products);

    // Fetch the details of the selected products from the cart
    $select_cart = mysqli_query($conn, "SELECT * FROM `cart` WHERE userSession = '$userName' AND id IN ($product_id)") or die('query failed');
    
    // Process cart items
    while ($cart_item = mysqli_fetch_assoc($select_cart)) {
        $cart_items[] = $cart_item;
        $cart_total += ($cart_item['price'] * $cart_item['quantity']);
    }
}
// Process the checkout when the form is submitted
if (isset($_POST['order_btn'])) {
   $user_query = mysqli_query($conn, "SELECT first_name, middle_name, last_name, suffix, email, number FROM `users` WHERE username = '$userName'") or die('Query failed');
   $fetch_name = mysqli_fetch_assoc($user_query);

   if (!$fetch_name) {
       $message[] = 'User information not found.';
   } else {
       $name = mysqli_real_escape_string($conn, ' ' . $fetch_name['first_name'] . ' ' . $fetch_name['middle_name'] . ' ' . $fetch_name['last_name'] . ' ' . $fetch_name['suffix']);
       $email = $fetch_name['email'];
       $number = $fetch_name['number'];
       $shipping = mysqli_real_escape_string($conn, $_POST['shipping']);
       $method = mysqli_real_escape_string($conn, $_POST['method']);
       $address = mysqli_real_escape_string($conn, $_POST['street'] . ', ' . $_POST['city'] . ', ' . $_POST['country']);
       $placed_on = date('d-M-Y');

       // Create product names and IDs for the order
       $product_names = [];
       $product_ids = [];
       $product_quantities = []; // Store product quantities

       foreach ($cart_items as $item) {
           // Fetch the product details from the products table
           $product_query = mysqli_query($conn, "SELECT * FROM `products` WHERE id = '{$item['product_id']}'") or die('Query failed');
           $product_data = mysqli_fetch_assoc($product_query);

           $product_names[] = $product_data['product_name'] . ' ' . $item['quantity']; // Product name and quantity
           $product_ids[] = $product_data['id']; // Correct product_id from products table
           $product_quantities[] = $item['quantity']; // Store the quantity of the product
       }

       $product_name = implode(', ', $product_names);
       $product_id = implode(',', $product_ids); // Now it has the correct product_id

       // Check if the order already exists
       $order_query = mysqli_query($conn, "SELECT * FROM `orders` WHERE first_name = '$fetch_name[first_name]' AND middle_name = '$fetch_name[middle_name]' AND last_name = '$fetch_name[last_name]' AND suffix = '$fetch_name[suffix]' AND number = '$number' AND email = '$email'AND shipping = '$shipping' AND method = '$method' AND address = '$address' AND product_id = '$product_id' AND total_price = '$cart_total'") or die('query failed');

       if ($cart_total == 0) {
           $message[] = 'Your cart is empty.';
       } elseif (mysqli_num_rows($order_query) > 0) {
           $message[] = 'Order already placed!';
       } else {
           // Insert new order
           mysqli_query($conn, "INSERT INTO `orders`(userSession, first_name, middle_name, last_name, suffix, number, email, shipping, method, address, product_name, product_id, total_price, placed_on) VALUES ('$userName', '$fetch_name[first_name]', '$fetch_name[middle_name]', '$fetch_name[last_name]', '$fetch_name[suffix]', '$number', '$email', '$shipping', '$method', '$address', '$product_name', '$product_id', '$cart_total', '$placed_on')") or die('query failed');

           // Deduct the stock for each ordered product
           foreach ($cart_items as $index => $item) {
               $product_id = $item['product_id'];
               $ordered_quantity = $item['quantity'];
               
               // Fetch the current stock level for the product
               $stock_query = mysqli_query($conn, "SELECT stocks FROM `products` WHERE id = '$product_id'") or die('Query failed');
               $stock_data = mysqli_fetch_assoc($stock_query);

               if ($stock_data) {
                   $current_stock = $stock_data['stocks'];
                   $new_stock = $current_stock - $ordered_quantity;

                   // Update the stock level in the products table
                   mysqli_query($conn, "UPDATE `products` SET stocks = '$new_stock' WHERE id = '$product_id'") or die('Query failed');
               }
           }

           // Delete the items from the cart based on the product IDs after the order is placed
         // After placing an order, clear the cart
mysqli_query($conn, "DELETE FROM `cart` WHERE userSession = '$userName'") or die('query failed');

           
           // Redirect to homepage after order and cart are processed
           header("location: homepage.php");
           exit; // Ensure no further code is executed after this.
       }
   }
}


$order_count = 0; // Default value if no orders exist
$order_count_query = mysqli_query($conn, "SELECT COUNT(*) as total_orders FROM `orders` WHERE userSession = '$userName' AND payment_status != 'delivered'") or die('Query failed');
if ($order_count_row = mysqli_fetch_assoc($order_count_query)) {
    $order_count = $order_count_row['total_orders'];
} else {
    $order_count = 0; // Default value if no orders exist
}
// Fetch cart count
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
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>ByteBazaar</title>

   <link rel="icon" href="images/code.png">
   <link rel="preconnect" href="https://fonts.gstatic.com">
   <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600&display=swap" rel="stylesheet">
   <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous" />
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
   <link rel="stylesheet" href="css/checkout.css">
</head>
<body class="anybody">
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
         <?php if($cart_count > 0): ?>
            <span class="cart-count"><?php echo $cart_count; ?></span>
         <?php endif; ?>
         </a>
         </li>
         <li><a href="homepage.php#home">Home</a></li>
         <li>
                <a href="order_status.php">
    Order
    <?php if($order_count > 0): ?>
            <span class="cart-count"><?php echo $order_count; ?></span>
    <?php endif; ?>
</a>
</li>
         <li><a href="homepage.php#about">About</a></li>
         <li><a href="homepage.php#product">Product</a></li>
         <li><a href="homepage.php#contact">Contact</a></li>
         <li><a href="logoutuser.php">Logout</a></li>
         </div>
      </ul>
   </nav>
</header>

<section class="display-order">
   <div class="heading">
      <h3>checkout</h3>
   </div>
   <?php  
      // Initialize the grand total variable
      $grand_total = 0;

      if(count($cart_items) > 0){
         foreach($cart_items as $item){
            $total_price = ($item['price'] * $item['quantity']);
            $grand_total += $total_price;  // Accumulate the total price
   ?>
      <div class="product-item">
         <p><?php echo $item['product_name']; ?> (Quantity: <?php echo $item['quantity']; ?>)</p>
         <img src="images/<?php echo $item['image']; ?>" alt="<?php echo $item['product_name']; ?>" class="product-image">
         <p>Subtotal: ₱<?php echo number_format($total_price, 2); ?></p>
      </div>
   <?php
         }
      } else {
         echo '<p class="empty">your cart is empty</p>';
      }
   ?>

   <p><strong>Grand Total: ₱<?php echo number_format($grand_total, 2); ?></strong></p>
</section>


<section class="checkout">
      <form action="" method="POST">
         <h3>place your order</h3>
         <div class="flex">
            <div class="inputBox">
            <?php

      $select_users = mysqli_query($conn, "SELECT * FROM `users` WHERE username = '$userName'") or die('query failed');
      if(mysqli_num_rows($select_users) > 0){
         while($fetch_users = mysqli_fetch_assoc($select_users)){

            $email = $fetch_users['email'];
            $number = $fetch_users['number'];
      ?>
               <span>Your Name :</span>
               <p><br><span><?php echo $fetch_users['first_name']; ?> <?php echo $fetch_users['middle_name']; ?> <?php echo $fetch_users['last_name']; ?> <?php echo $fetch_users['suffix']; ?></span></p>
               <?php
         }
      }else{
      }
      ?>
               </div>
            <div class="inputBox">
               <span>Your Number :</span>
               <input type="number" name="number" value="<?php echo $number; ?>" required placeholder="Enter your number">
            </div>
            <div class="inputBox">
               <span>Your Email :</span>
               <input type="email" name="email" value="<?php echo $email; ?>" required placeholder="Enter your email">
            </div>

            <div class="inputBox">
            <span>Shipping Provider:</span>
            <select name="shipping" aria-readonly="true">
               <option value="J&T Express" selected>J&T Express</option>
            </select>
         </div>

            <div class="inputBox">
               <span>Payment Method:</span>
               <select name="method">
                  <option value="cash on delivery">cash on delivery</option>

               </select>
            </div>
            <div class="inputBox">
               <span>Street Address:</span>
               <input type="text" name="street" required placeholder="e.g. street name">
            </div>
            <div class="inputBox">
               <span>City :</span>
               <input type="text" name="city" required placeholder="e.g. Bislig City">
            </div>
            <div class="inputBox">
               <span>State :</span>
               <input type="text" name="state" required placeholder="e.g. Surigao del Sur">
            </div>
            <div class="inputBox">
               <span>Country :</span>
               <input type="text" name="country" required placeholder="e.g. Phillipines">
            </div>
         </div>
         <button type="button" class="btn" onclick="history.back();">Back to Cart</button>
         <input type="submit" value="order now" class="btn1" name="order_btn">
      </form>

   </section>

</body>
</html>
