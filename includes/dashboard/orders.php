<?php
// Get order details
function getOrderDetails($order_id) {
    global $conn;
    $stmt = $conn->prepare("
        SELECT oi.*, p.title, p.price, p.image_url as image, u.username as seller_name
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        JOIN users u ON p.user_id = u.id
        WHERE oi.order_id = ?
    ");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Get user's orders
$stmt = $conn->prepare("
    SELECT o.*, oi.product_id, oi.quantity, oi.price as item_price,
           p.title, p.image_url as image, u.username as seller_name
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    JOIN users u ON p.user_id = u.id
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<link rel="stylesheet" href="assets/css/orders.css">

<div class="dashboard-card">
    <div class="card-header">
        <h2>My Orders</h2>
    </div>

    <?php if (empty($orders)): ?>
        <div class="empty-state">
            <i class="fas fa-shopping-bag"></i>
            <p>No orders found. Start shopping!</p>
        </div>
    <?php else: ?>
        <div class="orders-list">
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-info">
                            <h3>Order #<?php echo $order['id']; ?></h3>
                            <p class="order-date"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></p>
                        </div>
                        <div class="order-status <?php echo $order['status']; ?>">
                            <?php echo ucfirst($order['status']); ?>
                        </div>
                    </div>
                    
                    <div class="order-details">
                        <div class="product-info">
                            <?php if($order['image']): ?>
                                <img src="<?php echo $order['image']; ?>" alt="<?php echo htmlspecialchars($order['title']); ?>" class="product-image">
                            <?php else: ?>
                                <div class="no-image">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php endif; ?>
                            <div class="product-details">
                                <h4><?php echo htmlspecialchars($order['title']); ?></h4>
                                <p class="seller">Seller: <?php echo htmlspecialchars($order['seller_name']); ?></p>
                                <p class="price">$<?php echo number_format($order['item_price'], 2); ?></p>
                            </div>
                        </div>
                        
                        <div class="order-meta">
                            <div class="meta-item">
                                <span class="label">Quantity:</span>
                                <span class="value"><?php echo $order['quantity']; ?></span>
                            </div>
                            <div class="meta-item">
                                <span class="label">Total:</span>
                                <span class="value">$<?php echo number_format($order['total_amount'], 2); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div> 