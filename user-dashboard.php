<?php
session_start();
require_once 'config/database.php';
require_once 'includes/file-upload-delete.php';
include 'includes/timeout.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get current page
$current_page = isset($_GET['page']) ? $_GET['page'] : 'overview';

// Get user data
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Get user's profile
$stmt = $conn->prepare("SELECT * FROM profiles WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();

// Get user's products
$stmt = $conn->prepare("SELECT * FROM products WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get user's orders
$stmt = $conn->prepare("SELECT o.*, COUNT(oi.id) as item_count 
                       FROM orders o 
                       LEFT JOIN order_items oi ON o.id = oi.order_id 
                       WHERE o.user_id = ? 
                       GROUP BY o.id 
                       ORDER BY o.created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get user's cart items
$stmt = $conn->prepare("SELECT c.*, p.title, p.price, p.image_url as image 
                       FROM cart c 
                       JOIN products p ON c.product_id = p.id 
                       WHERE c.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get user's talents
$sql = "SELECT * FROM talents WHERE user_id = ? ORDER BY created_at DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$talents = mysqli_stmt_get_result($stmt);

// Get user's resources
$sql = "SELECT * FROM resources WHERE user_id = ? ORDER BY created_at DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$resources = mysqli_stmt_get_result($stmt);

// Get user's favorites
$sql = "SELECT f.talent_id, t.title, t.category, u.full_name
            FROM favorites f 
            JOIN talents t ON f.talent_id = t.id 
            JOIN users u ON t.user_id = u.id 
            WHERE f.user_id = ? 
            ORDER BY f.created_at DESC";
$favorites = [];
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $favorites[] = $row;
    }
}

// Get comments for user's talents
$sql = "SELECT c.*, u.username, u.full_name, p.profile_picture, t.title as talent_title
        FROM comments c
        JOIN users u ON c.user_id = u.id
        LEFT JOIN profiles p ON u.id = p.user_id
        JOIN talents t ON c.talent_id = t.id
        WHERE t.user_id = ?
        ORDER BY c.created_at DESC";
$comments = [];
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $comments[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>User Dashboard - MMU Talent Hub</title>
        <link rel="stylesheet" href="assets/css/style.css">
        <link rel="stylesheet" href="assets/css/dashboard.css">
        <link rel="stylesheet" href="assets/css/resources.css">
        <link rel="stylesheet" href="assets/css/products.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    </head>

    <body>
        <?php include 'includes/header-inc.php'; ?>
        <?php include 'includes/navbar.php'; ?>

        <div class="dashboard-container">
            <div class="dashboard-sidebar">
                <div class="user-info">
                    <img src="<?php echo !empty($profile['profile_picture']) ? $profile['profile_picture'] : 'assets/images/default-avatar.png'; ?>"
                        alt="Profile Picture" class="profile-picture">
                    <div>
                        <h3><?php echo htmlspecialchars($user['full_name']); ?></h3>
                        <p class="user-role"><?php echo ucfirst($user['role']); ?></p>
                    </div>
                </div>

                <nav class="dashboard-nav">
                    <a href="?page=overview" class="<?php echo $current_page == 'overview' ? 'active' : ''; ?>">
                        <i class="fas fa-home"></i> Overview
                    </a>
                    <a href="?page=profile" class="<?php echo $current_page == 'profile' ? 'active' : ''; ?>">
                        <i class="fas fa-user"></i> My Profile
                    </a>
                    <a href="?page=talents" class="<?php echo $current_page == 'talents' ? 'active' : ''; ?>">
                        <i class="fas fa-star"></i> My Talents
                    </a>
                    <a href="?page=resources" class="<?php echo $current_page == 'resources' ? 'active' : ''; ?>">
                        <i class="fas fa-file-alt"></i> My Resources
                    </a>
                    <a href="?page=favorites" class="<?php echo $current_page == 'favorites' ? 'active' : ''; ?>">
                        <i class="fas fa-heart"></i> My Favorites
                    </a>
                    <a href="?page=comments" class="<?php echo $current_page == 'comments' ? 'active' : ''; ?>">
                        <i class="fas fa-comments"></i> My Comments
                    </a>
                    <a href="?page=products" class="<?php echo $current_page == 'products' ? 'active' : ''; ?>">
                        <i class="fas fa-box"></i> My Products
                    </a>
                    <a href="?page=orders" class="<?php echo $current_page == 'orders' ? 'active' : ''; ?>">
                        <i class="fas fa-shopping-bag"></i> My Orders
                    </a>
                </nav>
            </div>

            <div class="dashboard-content">
                <?php
                $page = isset($_GET['page']) ? $_GET['page'] : 'overview';
                $page_file = "includes/dashboard/{$page}.php";

                if (file_exists($page_file)) {
                    include $page_file;
                } else {
                    include 'includes/dashboard/overview.php';
                }
                ?>
            </div>
        </div>

        <script src="assets/js/script.js"></script>
        <?php include 'includes/footer-inc.php'; ?>
    </body>

</html>