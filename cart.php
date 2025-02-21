<?php
include "conn.php";
include "ck_session.php";

$userName = $_SESSION['userSession'];

if (!isSessionAction()) {
    header("location: homepage.php");
}

if (isset($_POST['update_cart'])) {
    $cart_id = $_POST['cart_id'];
    $cart_quantity = $_POST['cart_quantity'];

    // Get the current stock for the product
    $query = "SELECT stocks FROM products WHERE id = (SELECT product_id FROM cart WHERE id = '$cart_id')";
    $result = mysqli_query($conn, $query) or die('Query failed');
    $product = mysqli_fetch_assoc($result);

    // If the cart quantity is greater than available stock, don't allow the update
    if ($cart_quantity > $product['stocks']) {
        echo "<script>alert('Not enough stock available');</script>";
    } else {
        mysqli_query($conn, "UPDATE cart SET quantity = '$cart_quantity' WHERE id = '$cart_id'") or die('Query failed');
    }
}

if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM cart WHERE id = '$delete_id'") or die('Query failed');
    header('location:cart.php');
}

if (isset($_GET['delete_all'])) {
    mysqli_query($conn, "DELETE FROM cart WHERE userSession = '$userName'") or die('Query failed');
    header('location:cart.php');
}

$order_count = 0;
$order_count_query = mysqli_query($conn, "SELECT COUNT(*) as total_orders FROM `orders` WHERE userSession = '$userName' AND payment_status != 'delivered'") or die('Query failed');
if ($order_count_row = mysqli_fetch_assoc($order_count_query)) {
    $order_count = $order_count_row['total_orders'];
}
$cart_count = 0;
$cart_count_query = mysqli_query($conn, "SELECT COUNT(*) as total_items FROM cart WHERE userSession = '$userName'") or die('Query failed');
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
    <link rel="icon" href="images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style1.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
                <li><a href="cart.php">
                    <i class="fas fa-shopping-cart" aria-hidden="true"></i>
                    <?php if($cart_count > 0): ?>
                        <span class="cart-count"><?php echo $cart_count; ?></span>
                    <?php endif; ?>
                    </a>
                </li>
                <li><a href="homepage.php#home">Home</a></li>
                <li><a href="order_status.php">Order
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

<section class="shopping-cart">
    <h1 class="title">Products Added</h1>
    <div class="box-container">
        <?php
        $grand_total = 0;
        $select_cart = mysqli_query($conn, "SELECT * FROM cart WHERE userSession = '$userName'") or die('Query failed');
        if (mysqli_num_rows($select_cart) > 0) {
            while ($fetch_cart = mysqli_fetch_assoc($select_cart)) {
                $sub_total = $fetch_cart['quantity'] * $fetch_cart['price'];
                $grand_total += $sub_total;
                ?>
                <div class="box">
                <a href="cart.php" class="fas fa-times delete-item" data-id="<?php echo $fetch_cart['id']; ?>"></a>
                    <img src="uploads/<?php echo $fetch_cart['image']; ?>" alt="">
                    <div class="name"><?php echo $fetch_cart['product_name']; ?></div>
                    <div class="price">₱<span class="item-price"><?php echo $fetch_cart['price']; ?></span></div>
                    <form action="" method="post">
                        <input type="hidden" name="cart_id" value="<?php echo $fetch_cart['id']; ?>">
                        <input type="number" min="1" name="cart_quantity" class="cart-quantity" value="<?php echo $fetch_cart['quantity']; ?>" data-price="<?php echo $fetch_cart['price']; ?>" data-id="<?php echo $fetch_cart['id']; ?>" />
                    </form>
                    <div class="sub-total">Sub total: ₱<span class="sub-total-value"><?php echo $sub_total; ?></span></div>
                    <label class="check-btn-container">
                        <input type="checkbox" class="check-btn" data-id="<?php echo $fetch_cart['id']; ?>" data-price="<?php echo $fetch_cart['price']; ?>" data-quantity="<?php echo $fetch_cart['quantity']; ?>" data-subtotal="<?php echo $sub_total; ?>" />
                        <span class="checkmark"></span>
                    </label>
                </div>
                <?php
            }
        } else {
            echo '<p class="empty">Your cart is empty</p>';
        }
        ?>
    </div>

    <div class="cart-total">
        <p>Grand Total: ₱<span id="grand-total"><?php echo $grand_total; ?></span></p>
        <div class="flex">
            <a href="homepage.php" class="btn">Continue Shopping</a>
            <a href="checkout.php" class="btn" id="checkout-btn" <?php echo ($grand_total > 1) ? '' : 'style="pointer-events: none; opacity: 0.5;"'; ?>>Proceed to Checkout</a>
        </div>
    </div>
</section>

<div id="deleteModal" class="modal">
    <div class="modal-content">
        <h2>Delete Confirmation</h2>
        <p>Are you sure you want to delete this item from your cart?</p>
        <div class="modal-actions">
            <button id="confirmDelete" class="btn danger">Delete</button>
            <button id="cancelDelete" class="btn">Cancel</button>
        </div>
    </div>
</div>


<script>
    $(document).ready(function() {
        let deleteId; // Store the ID of the item to be deleted

        // Open modal on delete icon click
        $('.delete-item').on('click', function(e) {
            e.preventDefault(); // Prevent default link action
            deleteId = $(this).data('id'); // Get the cart item ID
            $('#deleteModal').fadeIn(); // Show the modal
        });

        // Confirm deletion
        $('#confirmDelete').on('click', function() {
            // Redirect to the delete URL with the item ID
            window.location.href = `cart.php?delete=${deleteId}`;
        });

        // Cancel deletion
        $('#cancelDelete').on('click', function() {
            $('#deleteModal').fadeOut(); // Hide the modal
        });

        // Close modal when clicking outside it
        $(window).on('click', function(event) {
            if ($(event.target).is('#deleteModal')) {
                $('#deleteModal').fadeOut(); // Hide the modal
            }
        });
    });
</script>


<script>
$(document).ready(function() {
    let selectedTotal = 0;
    const selectedProducts = [];

    // Function to update the grand total and enable/disable the checkout button
    function updateTotal() {
        selectedTotal = 0;
        selectedProducts.length = 0;

        // Loop through each product and check if it is selected
        $('.check-btn:checked').each(function() {
            const price = $(this).data('price');
            const quantity = parseInt($(this).data('quantity')); // Ensure quantity is an integer
            const subtotal = price * quantity;

            selectedTotal += subtotal;
            selectedProducts.push($(this).data('id'));

            // Update the subtotal display for the product
            $(this).closest('.box').find('.sub-total-value').text(subtotal.toFixed(2));
        });

        // Update the grand total display
        $('#grand-total').text(selectedTotal.toFixed(2));

        // Enable/disable the checkout button
        if (selectedTotal > 0) {
            $('#checkout-btn').removeClass('disabled');
            $('#checkout-btn').attr('href', 'checkout.php?products=' + selectedProducts.join(','));
        } else {
            $('#checkout-btn').addClass('disabled');
            $('#checkout-btn').attr('href', '#');
        }
    }

    // When a checkbox is clicked, toggle its selected state
    $('.check-btn').on('change', function() {
        const quantityInput = $(this).closest('.box').find('.cart-quantity');
        const quantity = parseInt(quantityInput.val());  // Get the updated quantity from the input field

        // Update the data-quantity attribute with the correct quantity
        $(this).data('quantity', quantity);

        // Update the grand total and checkout button state
        updateTotal();
    });

    // Update the quantity dynamically when the user changes it
    $('.cart-quantity').on('input', function() {
        const quantity = $(this).val();
        const cartId = $(this).data('id');
        const checkbox = $(this).closest('.box').find('.check-btn');

        // Update the data-quantity attribute with the new quantity
        checkbox.data('quantity', quantity);

        // Update the subtotal for the product
        const price = $(this).data('price');
        const subtotal = price * quantity;
        $(this).closest('.box').find('.sub-total-value').text(subtotal.toFixed(2));

        // Recalculate the grand total
        updateTotal();

        // Optionally update the backend with the new quantity via AJAX
        $.ajax({
            url: 'cart.php',
            method: 'POST',
            data: {
                update_cart: true,
                cart_id: cartId,
                cart_quantity: quantity
            },
            success: function(response) {
                // Handle the success if needed
            }
        });
    });

    // Initial update on page load
    updateTotal();
});


</script>

</body>
</html>
