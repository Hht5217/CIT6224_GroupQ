<?php
session_start();
require_once 'config/database.php';
include 'includes/timeout.php';

// 获取所有公告
$sql = "SELECT * FROM announcements ORDER BY created_at DESC";
$announcements = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>News & Announcements - MMU Talent Showcase</title>
        <link rel="stylesheet" href="assets/css/style.css">
    </head>

    <body>
        <?php include 'includes/header-inc.php'; ?>
        <?php include 'includes/navbar.php'; ?>

        <div class="container">
            <div class="card">
                <h2>News & Announcements</h2>
                <div class="announcements-list">
                    <?php while ($announcement = mysqli_fetch_assoc($announcements)): ?>
                        <div class="announcement-item">
                            <h3 class="announcement-title"><?php echo htmlspecialchars($announcement['title']); ?></h3>
                            <div class="announcement-meta">
                                <span class="announcement-date">Posted on:
                                    <?php echo date('F j, Y', strtotime($announcement['created_at'])); ?></span>
                            </div>
                            <div class="announcement-content">
                                <?php echo nl2br(htmlspecialchars($announcement['content'])); ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
        <?php include 'includes/footer-inc.php'; ?>
    </body>

</html>