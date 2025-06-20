<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isAdmin($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get current page
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Get admin info
$admin_id = $_SESSION['user_id'];
$admin = getUserById($admin_id);
?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Dashboard - TalentHub</title>
        <link rel="stylesheet" href="assets/css/style.css">
        <link rel="stylesheet" href="assets/css/admin.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    </head>

    <body>
        <div class="admin-container">
            <!-- Sidebar -->
            <div class="admin-sidebar">
                <div class="sidebar-header">
                    <h2>Admin Panel</h2>
                </div>
                <nav class="sidebar-nav">
                    <a href="?page=dashboard" class="<?php echo $page == 'dashboard' ? 'active' : ''; ?>">
                        <i class="fas fa-chart-line"></i> Dashboard
                    </a>
                    <a href="?page=users" class="<?php echo $page == 'users' ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i> Manage Users
                    </a>
                    <a href="?page=products" class="<?php echo $page == 'products' ? 'active' : ''; ?>">
                        <i class="fas fa-box"></i> Manage Products
                    </a>
                    <a href="?page=orders" class="<?php echo $page == 'orders' ? 'active' : ''; ?>">
                        <i class="fas fa-shopping-cart"></i> Manage Orders
                    </a>
                    <a href="?page=reports" class="<?php echo $page == 'reports' ? 'active' : ''; ?>">
                        <i class="fas fa-chart-bar"></i> Reports
                    </a>
                    <a href="?page=settings" class="<?php echo $page == 'settings' ? 'active' : ''; ?>">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="admin-main">
                <!-- Top Navigation -->
                <div class="admin-header">
                    <div class="header-left">
                        <h1><?php echo ucfirst($page); ?></h1>
                    </div>
                    <div class="header-right">
                        <div class="admin-profile">
                            <img src="<?php echo !empty($admin['profile_picture']) ? htmlspecialchars($admin['profile_picture']) : 'assets/images/default-avatar.png'; ?>"
                                alt="Admin Profile" class="profile-picture">
                            <div class="profile-info">
                                <span class="admin-name"><?php echo htmlspecialchars($admin['username']); ?></span>
                                <span class="admin-role">Administrator</span>
                            </div>
                        </div>
                        <a href="logout.php" class="logout-btn">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>

                <!-- Content Area -->
                <div class="admin-content">
                    <?php
                    switch ($page) {
                        case 'dashboard':
                            include 'includes/admin/dashboard.php';
                            break;
                        case 'users':
                            include 'includes/admin/users.php';
                            break;
                        case 'products':
                            include 'includes/admin/products.php';
                            break;
                        case 'orders':
                            include 'includes/admin/orders.php';
                            break;
                        case 'reports':
                            include 'includes/admin/reports.php';
                            break;
                        case 'settings':
                            include 'includes/admin/settings.php';
                            break;
                        default:
                            include 'includes/admin/dashboard.php';
                    }
                    ?>
                </div>
            </div>
        </div>

        <script src="assets/js/admin.js"></script>
    </body>

</html>