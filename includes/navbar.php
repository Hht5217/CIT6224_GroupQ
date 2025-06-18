<?php
// Get current page for active link highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="nav">
    <ul>
        <li><a href="index.php" <?php echo $current_page == 'index.php' ? 'class="active"' : ''; ?>>Home</a></li>
        <li><a href="talent-catalogue.php" <?php echo $current_page == 'talent-catalogue.php' ? 'class="active"' : ''; ?>>Talent Catalogue</a></li>
        <li><a href="products.php" <?php echo $current_page == 'products.php' ? 'class="active"' : ''; ?>>Products</a></li>
        <li><a href="faq.php" <?php echo $current_page == 'faq.php' ? 'class="active"' : ''; ?>>FAQ</a></li>
        <li><a href="announcements.php" <?php echo $current_page == 'announcements.php' ? 'class="active"' : ''; ?>>News & Announcements</a></li>
        <li><a href="feedback.php" <?php echo $current_page == 'feedback.php' ? 'class="active"' : ''; ?>>Feedback</a></li>
        <li><a href="resources.php" <?php echo $current_page == 'resources.php' ? 'class="active"' : ''; ?>>Resources</a></li>
        <?php if(isset($_SESSION['user_id'])): ?>
            <li><a href="user-dashboard.php" <?php echo $current_page == 'user-dashboard.php' ? 'class="active"' : ''; ?>>Dashboard</a></li>
            <li><a href="cart.php" <?php echo $current_page == 'cart.php' ? 'class="active"' : ''; ?>>Cart</a></li>
            <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <li><a href="admin/dashboard.php" <?php echo $current_page == 'dashboard.php' ? 'class="active"' : ''; ?>>Admin Dashboard</a></li>
            <?php endif; ?>
            <li><a href="logout.php">Logout</a></li>
        <?php else: ?>
            <li><a href="login.php" <?php echo $current_page == 'login.php' ? 'class="active"' : ''; ?>>Login</a></li>
            <li><a href="register.php" <?php echo $current_page == 'register.php' ? 'class="active"' : ''; ?>>Register</a></li>
        <?php endif; ?>
    </ul>
</nav> 