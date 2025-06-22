<?php
// Get current page for active link highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<nav class="nav">
    <ul class="nav-list">
        <li><a href="index.php" <?php echo $current_page == 'index.php' ? 'class="active"' : ''; ?>><i
                    class="fas fa-home"></i> Home</a></li>
        <li class="dropdown">
            <a href="#" class="dropdown-toggle"><i class="fas fa-star"></i> Talents & Resources</a>
            <ul class="dropdown-menu">
                <li><a href="talent-catalogue.php" <?php echo $current_page == 'talent-catalogue.php' ? 'class="active"' : ''; ?>><i class="fas fa-receipt"></i> Talent Catalogue</a></li>
                <li><a href="resources.php" <?php echo $current_page == 'resources.php' ? 'class="active"' : ''; ?>><i
                            class="fas fa-file-alt"></i> Resources</a></li>
            </ul>
        </li>
        <li><a href="products.php" <?php echo $current_page == 'products.php' ? 'class="active"' : ''; ?>><i
                    class="fas fa-box"></i> Products</a></li>
        <li class="dropdown">
            <a href="#" class="dropdown-toggle"><i class="fas fa-info-circle"></i> Information</a>
            <ul class="dropdown-menu">
                <li><a href="announcements.php" <?php echo $current_page == 'announcements.php' ? 'class="active"' : ''; ?>><i class="fas fa-newspaper"></i> News & Announcements</a></li>
                <li><a href="faq.php" <?php echo $current_page == 'faq.php' ? 'class="active"' : ''; ?>><i
                            class="fas fa-question-circle"></i> FAQ</a></li>
                <li><a href="faq.php" <?php echo $current_page == 'feedback.php' ? 'class="active"' : ''; ?>><i
                            class="fas fa-comment-alt"></i> Feedback</a></li>
                <li><a href="about-us.php" <?php echo $current_page == 'about-us.php' ? 'class="active"' : ''; ?>><i
                            class="fas fa-users"></i> About Us</a></li>
            </ul>
        </li>
        <li class="dropdown">
            <a href="#" class="dropdown-toggle"><i class="fas fa-user"></i> Account</a>
            <ul class="dropdown-menu">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="user-dashboard.php" <?php echo $current_page == 'user-dashboard.php' ? 'class="active"' : ''; ?>><i class="fas fa-table-list"></i> Dashboard</a></li>
                    <li><a href="cart.php" <?php echo $current_page == 'cart.php' ? 'class="active"' : ''; ?>><i
                                class="fas fa-shopping-cart"></i> Cart</a></li>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <li><a href="admin/dashboard.php" <?php echo $current_page == 'dashboard.php' ? 'class="active"' : ''; ?>><i class="fas fa-wrench"></i> Admin Dashboard</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php"><i class="fas fa-right-from-bracket"></i> Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php" <?php echo $current_page == 'login.php' ? 'class="active"' : ''; ?>><i
                                class="fas fa-right-to-bracket"></i> Login</a></li>
                    <li><a href="register.php" <?php echo $current_page == 'register.php' ? 'class="active"' : ''; ?>><i
                                class="fas fa-user-plus"></i> Register</a></li>
                <?php endif; ?>
            </ul>
        </li>
    </ul>
</nav>