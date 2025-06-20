<?php
session_start();
require_once '../config/database.php';
include '../includes/timeout.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
} elseif ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Get statistics
$stats = array();

// Total users (excluding user_id = 1)
$sql = "SELECT COUNT(*) as total FROM users WHERE id != 1";
$result = mysqli_query($conn, $sql);
$stats['users'] = mysqli_fetch_assoc($result)['total'];

// Total admins (excluding user_id = 1)
$sql = "SELECT COUNT(*) as total FROM users WHERE role = 'admin' AND id != 1";
$result = mysqli_query($conn, $sql);
$stats['admins'] = mysqli_fetch_assoc($result)['total'];

// Total talents
$sql = "SELECT COUNT(*) as total FROM talents";
$result = mysqli_query($conn, $sql);
$stats['talents'] = mysqli_fetch_assoc($result)['total'];

// Recent users
$sql = "SELECT * FROM users WHERE role = 'user' ORDER BY created_at DESC LIMIT 5";
$recent_users = mysqli_query($conn, $sql);

// Recent talents
$sql = "SELECT t.*, u.username FROM talents t JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC LIMIT 5";
$recent_talents = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Dashboard - MMU Talent Showcase</title>
        <link rel="stylesheet" href="../assets/css/style.css">
    </head>

    <body>
        <header class="header">
            <h1>MMU Talent Showcase Portal - Admin Dashboard</h1>
        </header>

        <?php include '../includes/admin-navbar.php'; ?>

        <div class="container">
            <!-- Statistics -->
            <div class="stats-grid">
                <div class="card">
                    <h3>Total Users</h3>
                    <p class="stat-number"><?php echo "{$stats['users']} ({$stats['admins']} admins)"; ?>
                    </p>
                </div>
                <div class="card">
                    <h3>Total Talents</h3>
                    <p class="stat-number"><?php echo $stats['talents']; ?></p>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="grid">
                <!-- Recent Users -->
                <div class="card">
                    <h3>Recent Users</h3>
                    <div class="list">
                        <?php while ($user = mysqli_fetch_assoc($recent_users)): ?>
                            <div class="list-item">
                                <p><?php echo htmlspecialchars($user['username']); ?></p>
                                <small>Joined: <?php echo date('M j, Y', strtotime($user['created_at'])); ?></small>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>

                <!-- Recent Talents -->
                <div class="card">
                    <h3>Recent Talents</h3>
                    <div class="list">
                        <?php while ($talent = mysqli_fetch_assoc($recent_talents)): ?>
                            <div class="list-item">
                                <p><?php echo htmlspecialchars($talent['title']); ?></p>
                                <small>By: <?php echo htmlspecialchars($talent['username']); ?></small>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php include '../includes/footer-inc.php'; ?>
    </body>

</html>