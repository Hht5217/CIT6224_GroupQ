<?php
// Handle quantity update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_quantity') {
    $cart_id = $_POST['cart_id'];
    $quantity = $_POST['quantity'];
    
    $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("iii", $quantity, $cart_id, $user_id);
    $stmt->execute();
    
    // Refresh the page
    header("Location: user-dashboard.php?page=cart");
    exit();
}

// Calculate total
$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}
?>

<link rel="stylesheet" href="assets/css/cart.css">

<div class="dashboard-card">
    <h2>Shopping Cart</h2>
    
    <?php if (empty($cart_items)): ?>
        <div class="empty-cart">
            <i class="fas fa-shopping-cart"></i>
            <p>Your cart is empty</p>
            <a href="products.php" class="btn btn-primary">Browse Products</a>
        </div>
    <?php else: ?>
        <div class="cart-items">
            <?php foreach ($cart_items as $item): ?>
                <div class="cart-item">
                    <img src="<?php echo $item['image']; ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="item-image">
                    <div class="item-details">
                        <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                        <p class="item-price">$<?php echo number_format($item['price'], 2); ?></p>
                    </div>
                    <div class="item-quantity">
                        <form method="POST" class="quantity-form">
                            <input type="hidden" name="action" value="update_quantity">
                            <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                            <button type="button" onclick="updateQuantity(this, -1)" class="quantity-btn">-</button>
                            <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" max="99" onchange="this.form.submit()">
                            <button type="button" onclick="updateQuantity(this, 1)" class="quantity-btn">+</button>
                        </form>
                    </div>
                    <div class="item-total">
                        $<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                    </div>
                    <button onclick="removeFromCart(<?php echo $item['id']; ?>)" class="remove-btn">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="cart-summary">
            <div class="summary-row">
                <span>Subtotal:</span>
                <span>$<?php echo number_format($total, 2); ?></span>
            </div>
            <div class="summary-row">
                <span>Shipping:</span>
                <span>Free</span>
            </div>
            <div class="summary-row total">
                <span>Total:</span>
                <span>$<?php echo number_format($total, 2); ?></span>
            </div>
            <a href="checkout.php" class="btn btn-primary checkout-btn">
                Proceed to Checkout
            </a>
        </div>
    <?php endif; ?>
</div>

<script>
function updateQuantity(button, change) {
    const input = button.parentElement.querySelector('input[type="number"]');
    const newValue = parseInt(input.value) + change;
    if (newValue >= 1 && newValue <= 99) {
        input.value = newValue;
        input.form.submit();
    }
}

function removeFromCart(cartId) {
    if (confirm('Are you sure you want to remove this item from your cart?')) {
        fetch('remove-from-cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'cart_id=' + cartId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error removing item: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while removing the item');
        });
    }
}
</script> 