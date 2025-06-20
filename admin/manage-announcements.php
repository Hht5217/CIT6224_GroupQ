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
$error = '';
$success = '';

// Handling announcements
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    if ($_POST['action'] == 'add') {
        $title = trim($_POST['title']);
        $content = trim($_POST['content']);

        if (empty($title) || empty($content)) {
            $error = "Please fill in all fields";
        } else {
            $sql = "INSERT INTO announcements (title, content) VALUES (?, ?)";
            if ($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "ss", $title, $content);
                if (mysqli_stmt_execute($stmt)) {
                    $success = "Announcement added successfully";
                } else {
                    $error = "Error adding announcement";
                }
            }
        }
    }
    // Edit announcement
    elseif ($_POST['action'] == 'edit') {
        $id = $_POST['id'];
        $title = trim($_POST['title']);
        $content = trim($_POST['content']);

        if (empty($title) || empty($content)) {
            $error = "Please fill in all fields";
        } else {
            $sql = "UPDATE announcements SET title = ?, content = ? WHERE id = ?";
            if ($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "ssi", $title, $content, $id);
                if (mysqli_stmt_execute($stmt)) {
                    $success = "Announcement updated successfully";
                } else {
                    $error = "Error updating announcement";
                }
            }
        }
    }
    // Delete announcment
    elseif ($_POST['action'] == 'delete') {
        $id = $_POST['id'];

        $sql = "DELETE FROM announcements WHERE id = ?";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $id);
            if (mysqli_stmt_execute($stmt)) {
                $success = "Announcement deleted successfully";
            } else {
                $error = "Error deleting announcement";
            }
        }
    }
}

// Get all announcements
$sql = "SELECT * FROM announcements ORDER BY created_at DESC";
$announcements = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Manage Announcements - MMU Talent Showcase</title>
        <link rel="stylesheet" href="../assets/css/style.css">
    </head>

    <body>
        <header class="header">
            <h1>MMU Talent Showcase Portal - Manage Announcements</h1>
        </header>

        <?php include '../includes/admin-navbar.php'; ?>

        <div class="container">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <!-- 添加新公告 -->
            <div class="card">
                <h3>Add New Announcement</h3>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Content</label>
                        <textarea name="content" class="form-control" rows="6" required></textarea>
                    </div>
                    <div class="form-group">
                        <input type="submit" class="btn" value="Add Announcement">
                    </div>
                </form>
            </div>

            <!-- 公告列表 -->
            <div class="card">
                <h3>Announcements List</h3>
                <div class="announcements-list">
                    <?php while ($announcement = mysqli_fetch_assoc($announcements)): ?>
                        <div class="announcement-item">
                            <h4><?php echo htmlspecialchars($announcement['title']); ?></h4>
                            <div class="announcement-meta">
                                <span>Posted on:
                                    <?php echo date('F j, Y', strtotime($announcement['created_at'])); ?></span>
                            </div>
                            <p><?php echo nl2br(htmlspecialchars($announcement['content'])); ?></p>
                            <div class="announcement-actions">
                                <button
                                    onclick="editAnnouncement(<?php echo $announcement['id']; ?>, '<?php echo htmlspecialchars(addslashes($announcement['title'])); ?>', '<?php echo htmlspecialchars(addslashes($announcement['content'])); ?>')"
                                    class="btn">Edit</button>
                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post"
                                    style="display: inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $announcement['id']; ?>">
                                    <input type="submit" class="btn btn-danger" value="Delete"
                                        onclick="return confirm('Are you sure you want to delete this announcement?')">
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

        <!-- 编辑公告的模态框 -->
        <div id="editModal" class="modal" style="display: none;">
            <div class="modal-content">
                <h3>Edit Announcement</h3>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="title" id="edit_title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Content</label>
                        <textarea name="content" id="edit_content" class="form-control" rows="6" required></textarea>
                    </div>
                    <div class="form-group">
                        <input type="submit" class="btn" value="Update Announcement">
                        <button type="button" class="btn" onclick="closeModal()">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
        <?php include '../includes/footer-inc.php'; ?>

        <script>
            function editAnnouncement(id, title, content) {
                document.getElementById('edit_id').value = id;
                document.getElementById('edit_title').value = title;
                document.getElementById('edit_content').value = content;
                document.getElementById('editModal').style.display = 'block';
            }

            function closeModal() {
                document.getElementById('editModal').style.display = 'none';
            }
        </script>
    </body>

</html>