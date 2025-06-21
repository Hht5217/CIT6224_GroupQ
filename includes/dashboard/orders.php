<?php
// Get order details
function getOrderDetails($order_id)
{
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

// Get buyer's orders
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
$buyer_orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get seller's orders
$stmt = $conn->prepare("
    SELECT o.*, oi.product_id, oi.quantity, oi.price as item_price,
           p.title, p.image_url as image, u.username as buyer_name
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    JOIN users u ON o.user_id = u.id
    WHERE p.user_id = ?
    ORDER BY o.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$seller_orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Handle order actions
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $order_id = (int) $_POST['order_id'];

    switch ($_POST['action']) {
        case 'cancel':
            $stmt = $conn->prepare("UPDATE orders SET status = 'cancelled', payment_status = 'refunded' WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $order_id, $user_id);
            $stmt->execute();
            break;

        case 'process':
            $stmt = $conn->prepare("
                UPDATE orders o
                JOIN order_items oi ON o.id = oi.order_id
                JOIN products p ON oi.product_id = p.id
                SET o.status = 'processed'
                WHERE o.id = ? AND p.user_id = ?
            ");
            $stmt->bind_param("ii", $order_id, $user_id);
            $stmt->execute();
            break;

        case 'accept':
            $stmt = $conn->prepare("UPDATE orders SET status = 'completed' WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $order_id, $user_id);
            $stmt->execute();
            break;

        case 'reject':
            $stmt = $conn->prepare("UPDATE orders SET status = 'rejected', payment_status = 'refunded' WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $order_id, $user_id);
            $stmt->execute();
            break;
    }
    header("Location: user-dashboard.php?page=orders&tab=" . ($_GET['tab'] ?? 'purchases'));
    exit();
}
?>

<link rel="stylesheet" href="assets/css/orders.css">

<div class="dashboard-card">
    <div class="card-header">
        <h2>My Orders</h2>
        <div class="order-tabs">
            <button
                class="tab-button <?php echo (!isset($_GET['tab']) || $_GET['tab'] === 'purchases') ? 'active' : ''; ?>"
                onclick="showTab('purchases')">My Purchases</button>
            <button class="tab-button <?php echo (isset($_GET['tab']) && $_GET['tab'] === 'sales') ? 'active' : ''; ?>"
                onclick="showTab('sales')">My Sales</button>
        </div>
    </div>

    <!-- Buyer Orders -->
    <div id="purchases"
        class="tab-content <?php echo (!isset($_GET['tab']) || $_GET['tab'] === 'purchases') ? 'active' : ''; ?>">
        <?php if (empty($buyer_orders)): ?>
            <div class="empty-state">
                <i class="fas fa-shopping-bag"></i>
                <p>No purchases found. Start shopping!</p>
            </div>
        <?php else: ?>
            <div class="orders-list">
                <?php foreach ($buyer_orders as $order): ?>
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
                                <?php if ($order['image']): ?>
                                    <img src="<?php echo $order['image']; ?>" alt="<?php echo htmlspecialchars($order['title']); ?>"
                                        class="product-image">
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
                            <div class="order-actions">
                                <?php if ($order['status'] === 'pending'): ?>
                                    <form method="POST" class="action-form">
                                        <input type="hidden" name="action" value="cancel">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Cancel Order</button>
                                    </form>
                                <?php elseif ($order['status'] === 'processed'): ?>
                                    <form method="POST" class="action-form">
                                        <input type="hidden" name="action" value="accept">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <button type="submit" class="btn btn-success btn-sm">Accept</button>
                                    </form>
                                    <form method="POST" class="action-form">
                                        <input type="hidden" name="action" value="reject">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Seller Orders -->
    <div id="sales"
        class="tab-content <?php echo (isset($_GET['tab']) && $_GET['tab'] === 'sales') ? 'active' : ''; ?>">
        <?php if (empty($seller_orders)): ?>
            <div class="empty-state">
                <i class="fas fa-shopping-bag"></i>
                <p>No sales found.</p>
            </div>
        <?php else: ?>
            <div class="orders-list">
                <?php foreach ($seller_orders as $order): ?>
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
                                <?php if ($order['image']): ?>
                                    <img src="<?php echo $order['image']; ?>" alt="<?php echo htmlspecialchars($order['title']); ?>"
                                        class="product-image">
                                <?php else: ?>
                                    <div class="no-image">
                                        <i class="fas fa-image"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="product-details">
                                    <h4><?php echo htmlspecialchars($order['title']); ?></h4>
                                    <p class="buyer">Buyer: <?php echo htmlspecialchars($order['buyer_name']); ?></p>
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
                            <div class="order-actions">
                                <?php if ($order['status'] === 'pending'): ?>
                                    <form method="POST" class="action-form">
                                        <input type="hidden" name="action" value="process">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <button type="submit" class="btn btn-primary btn-sm">Mark as Processed</button>
                                    </form>
                                    <form method="POST" class="action-form">
                                        <input type="hidden" name="action" value="cancel">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Cancel Order</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    function showTab(tabId) {
        document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
        document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
        document.getElementById(tabId).classList.add('active');
        document.querySelector(`.tab-button[onclick="showTab('${tabId}')"]`).classList.add('active');
    }

    // Set initial tab based on URL parameter
    document.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab') || 'purchases';
        showTab(tab);
    });
</script>