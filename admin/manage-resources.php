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

// For deleting resource
if (isset($_POST['delete_resource'])) {
    $resource_id = $_POST['resource_id'];

    // Get file path
    $stmt = $conn->prepare("SELECT file_path FROM resources WHERE id = ?");
    $stmt->bind_param("i", $resource_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $resource = $result->fetch_assoc();

    if ($resource) {
        // Delete the file
        if (file_exists($resource['file_path'])) {
            unlink($resource['file_path']);
        }

        $stmt = $conn->prepare("DELETE FROM resources WHERE id = ?");
        $stmt->bind_param("i", $resource_id);
        $stmt->execute();

        header('Location: manage-resources.php?success=1');
        exit();
    }
}

// Get all resources
$sql = "SELECT r.*, u.username 
        FROM resources r 
        LEFT JOIN users u ON r.user_id = u.id 
        ORDER BY r.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Manage Resources - Admin Dashboard</title>
        <link rel="stylesheet" href="../assets/css/style.css">
    </head>

    <body>
        <header class="header">
            <h1>MMU Talent Showcase Portal - Manage Resources</h1>
        </header>

        <?php include '../includes/admin-navbar.php'; ?>

        <main class="main">
            <div class="container">
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success">Resource deleted successfully.</div>
                <?php endif; ?>

                <div class="resource-list">
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($resource = $result->fetch_assoc()): ?>
                            <div class="resource-item">
                                <div class="resource-info">
                                    <h3><?php echo htmlspecialchars($resource['title']); ?></h3>
                                    <div class="resource-meta">
                                        <span>Uploaded by:
                                            <?php echo htmlspecialchars($resource['username'] ?? 'Anonymous'); ?></span>
                                        <span>Date: <?php echo date('Y-m-d H:i', strtotime($resource['created_at'])); ?></span>
                                        <span>Downloads: <?php echo $resource['download_count']; ?></span>
                                        <span>Size: <?php echo formatFileSize($resource['file_size']); ?></span>
                                    </div>
                                    <p class="resource-description"><?php echo htmlspecialchars($resource['description']); ?>
                                    </p>
                                </div>
                                <div class="resource-actions">
                                    <a href="../download-resource.php?id=<?php echo $resource['id']; ?>"
                                        class="btn">Download</a>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="resource_id" value="<?php echo $resource['id']; ?>">
                                        <button type="submit" name="delete_resource" class="btn btn-danger"
                                            onclick="return confirm('Are you sure you want to delete this resource?')">Delete</button>
                                    </form>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>No resources found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>

        <?php include '../includes/footer-inc.php'; ?>
    </body>

</html>

<?php
function formatFileSize($bytes)
{
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}
?>