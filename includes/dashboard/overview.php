<?php
// Calculate total sales
$total_sales = 0;
foreach ($orders as $order) {
    if ($order['status'] == 'completed') {
        $total_sales += $order['total_amount'];
    }
}

// Count active products
$active_products = 0;
foreach ($products as $product) {
    if ($product['status'] == 'active') {
        $active_products++;
    }
}

// Count pending orders
$pending_orders = 0;
foreach ($orders as $order) {
    if ($order['status'] == 'pending') {
        $pending_orders++;
    }
}
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
        <p>Total Orders</p>
    </div>
    <div class="stat-card">
        <i class="fas fa-dollar-sign"></i>
        <h3>$<?php echo number_format($total_sales, 2); ?></h3>
        <p>Total Sales</p>
    </div>
</div>

<div class="dashboard-card">
    <h2>Recent Orders</h2>
    <?php if (empty($orders)): ?>
        <p>No orders found.</p>
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
    <h2>Recent Products</h2>
    <?php if (empty($products)): ?>
        <p>No products found.</p>
    <?php else: ?>
        <div class="products-grid">
            <?php foreach (array_slice($products, 0, 4) as $product): ?>
                <div class="product-card">
                    <img src="<?php echo $product['image_url']; ?>" alt="<?php echo htmlspecialchars($product['title']); ?>">
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