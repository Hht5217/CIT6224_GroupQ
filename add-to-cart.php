<?php
session_start();
require_once 'config/database.php';
include 'includes/timeout.php';
include 'includes/login-required.php';

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];

    // Validate quantity
    if ($quantity < 1) {
        $_SESSION['error'] = "Invalid quantity";
        header("location: products.php");
        exit;
    }

    // Check if product exists and is active, and also if the user is trying to add their own product
    $sql = "SELECT id, user_id FROM products WHERE id = ? AND status = 'active'";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $product_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            // Check if current user is the product owner
            if ($row['user_id'] == $user_id) {
                $_SESSION['warning'] = "You can't add your own products to cart";
                header("location: products.php");
                exit;
            }
        } else {
            $_SESSION['error'] = "Product not found or not available";
            header("location: products.php");
            exit;
        }
    }

    // Check if item already exists in cart
    $sql = "SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "ii", $user_id, $product_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            // Update quantity
            $new_quantity = $row['quantity'] + $quantity;
            $sql = "UPDATE cart SET quantity = ? WHERE id = ?";
            if ($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "ii", $new_quantity, $row['id']);
                if (!mysqli_stmt_execute($stmt)) {
                    $_SESSION['error'] = "Error updating cart";
                    header("location: products.php");
                    exit;
                }
            }
        } else {
            // Add new item to cart
            $sql = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
            if ($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "iii", $user_id, $product_id, $quantity);
                if (!mysqli_stmt_execute($stmt)) {
                    $_SESSION['error'] = "Error adding to cart";
                    header("location: products.php");
                    exit;
                }
            }
        }

        $_SESSION['success'] = "Item added to cart successfully";
        header("location: cart.php");
        exit;
    }
}

// If we get here, something went wrong
$_SESSION['error'] = "Invalid request";
header("location: products.php");
exit;