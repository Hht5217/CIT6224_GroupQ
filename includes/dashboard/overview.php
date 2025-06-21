<?php
// Calculate total sales (for seller)
$total_sales = 0;
$stmt = $conn->prepare("
    SELECT SUM(o.total_amount) as total
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE o.status = 'completed' AND p.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$total_sales = $result['total'] ?? 0;

// Count active products
$active_products = 0;
foreach ($products as $product) {
    if ($product['status'] == 'active') {
        $active_products++;
    }
}

// Count pending orders (buyer)
$pending_orders = 0;
foreach ($orders as $order) {
    if ($order['status'] == 'pending') {
        $pending_orders++;
    }
}

// Get seller's orders
$stmt = $conn->prepare("
    SELECT o.*, COUNT(oi.id) as item_count
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE p.user_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$seller_orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<div class="dashboard-stats">
    <div class="stat-card">
        <i class="fas fa-box"></i>
        <h3><?php echo count($products); ?></h3>
        <p>Total Products</p>
    </div>
    <div class="stat-card">
        <i class="fas fa-shopping-cart"></i>
        <h3><?php echo count($orders); ?></h3>
        <p>Total Purchases</p>
    </div>
    <div class="stat-card">
        <i class="fas fa-dollar-sign"></i>
        <h3>$<?php echo number_format($total_sales, 2); ?></h3>
        <p>Total Sales</p>
    </div>
</div>

<div class="dashboard-card">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h2>Recent Purchases</h2>
        <a href="user-dashboard.php?page=orders&tab=purchases" class="btn btn-primary">View Detail</a>
    </div>
    <?php if (empty($orders)): ?>
        <p>No purchases found.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="order-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($orders, 0, 5) as $order): ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                            <td><?php echo $order['item_count']; ?></td>
                            <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td>
                                <span class="status-badge <?php echo $order['status']; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<div class="dashboard-card">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h2>Recent Sales</h2>
        <a href="user-dashboard.php?page=orders&tab=sales" class="btn btn-primary">View Detail</a>
    </div>
    <?php if (empty($seller_orders)): ?>
        <p>No sales found.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="order-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($seller_orders, 0, 5) as $order): ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                            <td><?php echo $order['item_count']; ?></td>
                            <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td>
                                <span class="status-badge <?php echo $order['status']; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<div class="dashboard-card">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h2>Recent Products</h2>
        <a href="user-dashboard.php?page=products" class="btn btn-primary">View Detail</a>
    </div>
    <?php if (empty($products)): ?>
        <p>No products found.</p>
    <?php else: ?>
        <div class="products-grid">
            <?php foreach (array_slice($products, 0, 4) as $product): ?>
                <div class="product-card">
                    <div class="product-image">
                        <img src="<?php echo $product['image_url']; ?>"
                            alt="<?php echo htmlspecialchars($product['title']); ?>">
                    </div>
                    <div class="product-info">
                        <h3><?php echo htmlspecialchars($product['title']); ?></h3>
                        <p class="price">$<?php echo number_format($product['price'], 2); ?></p>
                        <span class="status-badge <?php echo $product['status']; ?>">
                            <?php echo ucfirst($product['status']); ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>