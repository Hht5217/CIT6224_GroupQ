<?php
session_start();
require_once 'config/database.php';
include 'includes/timeout.php';
include 'includes/login-required.php';

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Handle cart actions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update':
                $cart_id = $_POST['cart_id'];
                $quantity = $_POST['quantity'];

                if ($quantity > 0) {
                    $sql = "UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?";
                    if ($stmt = mysqli_prepare($conn, $sql)) {
                        mysqli_stmt_bind_param($stmt, "iii", $quantity, $cart_id, $user_id);
                        if (!mysqli_stmt_execute($stmt)) {
                            $error = "Error updating cart";
                        }
                    }
                }
                break;

            case 'remove':
                $cart_id = $_POST['cart_id'];

                $sql = "DELETE FROM cart WHERE id = ? AND user_id = ?";
                if ($stmt = mysqli_prepare($conn, $sql)) {
                    mysqli_stmt_bind_param($stmt, "ii", $cart_id, $user_id);
                    if (!mysqli_stmt_execute($stmt)) {
                        $error = "Error removing item from cart";
                    }
                }
                break;

            case 'checkout':
                // Start transaction
                mysqli_begin_transaction($conn);

                try {
                    // Get cart items
                    $sql = "SELECT c.*, p.price, p.title FROM cart c 
                           JOIN products p ON c.product_id = p.id 
                           WHERE c.user_id = ?";
                    if ($stmt = mysqli_prepare($conn, $sql)) {
                        mysqli_stmt_bind_param($stmt, "i", $user_id);
                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt);

                        if (mysqli_num_rows($result) > 0) {
                            // Calculate total
                            $total_amount = 0;
                            $cart_items = [];

                            while ($item = mysqli_fetch_assoc($result)) {
                                $total_amount += $item['price'] * $item['quantity'];
                                $cart_items[] = $item;
                            }

                            // Create order with payment_status = 'paid'
                            $sql = "INSERT INTO orders (user_id, total_amount, payment_status) VALUES (?, ?, 'paid')";
                            if ($stmt = mysqli_prepare($conn, $sql)) {
                                mysqli_stmt_bind_param($stmt, "id", $user_id, $total_amount);
                                mysqli_stmt_execute($stmt);
                                $order_id = mysqli_insert_id($conn);

                                // Create order items
                                foreach ($cart_items as $item) {
                                    $sql = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                                           VALUES (?, ?, ?, ?)";
                                    if ($stmt = mysqli_prepare($conn, $sql)) {
                                        mysqli_stmt_bind_param(
                                            $stmt,
                                            "iiid",
                                            $order_id,
                                            $item['product_id'],
                                            $item['quantity'],
                                            $item['price']
                                        );
                                        mysqli_stmt_execute($stmt);
                                    }
                                }

                                // Clear cart
                                $sql = "DELETE FROM cart WHERE user_id = ?";
                                if ($stmt = mysqli_prepare($conn, $sql)) {
                                    mysqli_stmt_bind_param($stmt, "i", $user_id);
                                    mysqli_stmt_execute($stmt);
                                }

                                mysqli_commit($conn);
                                $success = "Order placed successfully!";
                            }
                        } else {
                            $error = "Your cart is empty";
                        }
                    }
                } catch (Exception $e) {
                    mysqli_rollback($conn);
                    $error = "Error processing order";
                }
                break;
        }
    }
}

// Get cart items
$sql = "SELECT c.*, p.title, p.price, p.image_url, u.username as seller_name 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        JOIN users u ON p.user_id = u.id 
        WHERE c.user_id = ?";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $cart_items = [];
    $total = 0;

    while ($item = mysqli_fetch_assoc($result)) {
        $cart_items[] = $item;
        $total += $item['price'] * $item['quantity'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Shopping Cart - MMU Talent Showcase</title>
        <link rel="stylesheet" href="assets/css/style.css">
        <link rel="stylesheet" href="assets/css/cart.css">
    </head>

    <body>
        <?php include 'includes/header-inc.php'; ?>
        <?php include 'includes/navbar.php'; ?>

        <div class="container">
            <div class="card">
                <h2>Shopping Cart</h2>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <?php if (empty($cart_items)): ?>
                    <div class="empty-cart">
                        <p>Your cart is empty.</p>
                        <i class="fas fa-shopping-cart"></i>
                        <div class="cart-actions">
                            <a href="products.php" class="btn btn-primary">Browse Products</a>
                            <a href="user-dashboard.php?page=orders&tab=purchases" class="btn btn-secondary">View My
                                Orders</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="cart-items">
                        <?php foreach ($cart_items as $item): ?>
                            <div class="cart-item">
                                <div class="item-image">
                                    <?php if (!empty($item['image_url'])): ?>
                                        <img src="<?php echo $item['image_url']; ?>" alt="<?php echo $item['title']; ?>">
                                    <?php else: ?>
                                        <img src="assets/images/placeholder.jpg" alt="No image available">
                                    <?php endif; ?>
                                </div>
                                <div class="item-details">
                                    <h3><?php echo $item['title']; ?></h3>
                                    <p class="seller">Seller: <?php echo $item['seller_name']; ?></p>
                                    <p class="price">Price: $<?php echo number_format($item['price'], 2); ?></p>

                                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post"
                                        class="quantity-form">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                        <label>Quantity:</label>
                                        <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1"
                                            class="form-control quantity-input">
                                        <button type="submit" class="btn btn-secondary">Update</button>
                                    </form>

                                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post"
                                        class="remove-form">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                        <button type="submit" class="btn btn-danger">Remove</button>
                                    </form>
                                </div>
                                <div class="item-total">
                                    <p>Total: $<?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <div class="cart-summary">
                            <div class="total">
                                <h3>Total Amount: $<?php echo number_format($total, 2); ?></h3>
                            </div>
                            <div>
                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                    <input type="hidden" name="action" value="checkout">
                                    <button type="submit" class="btn btn-primary checkout-btn">Proceed to Checkout</button>
                                </form>
                                <a href="user-dashboard.php?page=orders&tab=purchases" class="btn btn-secondary">View My
                                    Orders</a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php include 'includes/footer-inc.php'; ?>
    </body>

</html>