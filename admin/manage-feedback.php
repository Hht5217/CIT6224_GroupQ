<?php
session_start();
require_once '../config/database.php';
include '../includes/timeout.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("location: ../login.php");
    exit;
}

$error = '';
$success = '';

// Handle status update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'update_status') {
    $id = $_POST['id'];
    $status = $_POST['status'];

    $sql = "UPDATE feedback SET status = ? WHERE id = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "si", $status, $id);
        if (mysqli_stmt_execute($stmt)) {
            $success = "Status updated successfully";
        } else {
            $error = "Error updating status";
        }
    }
}

// Handle feedback deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'delete') {
    $id = $_POST['id'];

    $sql = "DELETE FROM feedback WHERE id = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        if (mysqli_stmt_execute($stmt)) {
            $success = "Feedback deleted successfully";
        } else {
            $error = "Error deleting feedback";
        }
    }
}

// Fetch all feedback
$sql = "SELECT f.*, u.username FROM feedback f LEFT JOIN users u ON f.user_id = u.id ORDER BY f.created_at DESC";
$feedback = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Manage Feedback - MMU Talent Showcase</title>
        <link rel="stylesheet" href="../assets/css/style.css">
    </head>

    <body>
        <header class="header">
            <h1>MMU Talent Showcase Portal - Manage Feedback</h1>
        </header>

        <?php include '../includes/admin-navbar.php'; ?>

        <div class="container">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <div class="card">
                <h3>Feedback List</h3>
                <div class="feedback-list">
                    <?php while ($item = mysqli_fetch_assoc($feedback)): ?>
                        <div class="feedback-item">
                            <h4><?php echo htmlspecialchars($item['subject']); ?></h4>
                            <div class="feedback-meta">
                                <span>From:
                                    <?php echo $item['username'] ? htmlspecialchars($item['username']) : 'Anonymous'; ?></span>
                                <span>Posted on: <?php echo date('F j, Y', strtotime($item['created_at'])); ?></span>
                                <span>Status: <?php echo ucfirst($item['status']); ?></span>
                            </div>
                            <div class="feedback-content">
                                <?php echo nl2br(htmlspecialchars($item['message'])); ?>
                            </div>
                            <div class="feedback-actions">
                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post"
                                    style="display: inline;">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                    <select name="status" onchange="this.form.submit()">
                                        <option value="pending" <?php echo $item['status'] == 'pending' ? 'selected' : ''; ?>>
                                            Pending</option>
                                        <option value="in_progress" <?php echo $item['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                        <option value="resolved" <?php echo $item['status'] == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                    </select>
                                </form>
                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post"
                                    style="display: inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                    <input type="submit" class="btn btn-danger" value="Delete"
                                        onclick="return confirm('Are you sure you want to delete this feedback?')">
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
        <?php include 'includes/footer-inc.php'; ?>
    </body>

</html>