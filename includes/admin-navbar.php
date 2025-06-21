<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="admin-nav">
    <ul>
        <li><a href="../index.php" <?php echo $current_page == 'index.php' ? 'class="active"' : ''; ?>>Home</a></li>
        <li><a href="dashboard.php" <?php echo $current_page == 'dashboard.php' ? 'class="active"' : ''; ?>>Dashboard</a>
        </li>
        <li><a href="manage-users.php" <?php echo $current_page == 'manage-users.php' ? 'class="active"' : ''; ?>>Manage
                Users</a></li>
        <li><a href="manage-faq.php" <?php echo $current_page == 'manage-faq.php' ? 'class="active"' : ''; ?>>Manage
                FAQ</a></li>
        <li><a href="manage-announcements.php" <?php echo $current_page == 'manage-announcements.php' ? 'class="active"' : ''; ?>>Manage Announcements</a></li>
        <li><a href="manage-feedback.php" <?php echo $current_page == 'manage-feedback.php' ? 'class="active"' : ''; ?>>Manage Feedback</a></li>
        <li><a href="manage-resources.php" <?php echo $current_page == 'manage-resources.php' ? 'class="active"' : ''; ?>>Manage Resources</a></li>
        <li><a href="../logout.php">Logout</a></li>
    </ul>
</nav>