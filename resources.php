<?php
session_start();
require_once 'config/database.php';
include 'includes/timeout.php';

// Get all resources
$sql = "SELECT r.*, u.username FROM resources r JOIN users u ON r.user_id = u.id ORDER BY r.created_at DESC";
$resources = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Resources - MMU Talent Showcase</title>
        <link rel="stylesheet" href="assets/css/style.css">
    </head>

    <body>
        <?php include 'includes/header-inc.php'; ?>
        <?php include 'includes/navbar.php'; ?>

        <div class="container">
            <div class="card">
                <div class="resource-header">
                    <h2>Resources</h2>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="upload-resource.php" class="btn">Upload Resource</a>
                    <?php endif; ?>
                </div>
                <div class="resource-list">
                    <?php while ($resource = mysqli_fetch_assoc($resources)): ?>
                        <div class="resource-item">
                            <div class="resource-info">
                                <h3><?php echo htmlspecialchars($resource['title']); ?></h3>
                                <div class="resource-meta">
                                    <span>Uploaded by: <?php echo htmlspecialchars($resource['username']); ?></span>
                                    <span>Date: <?php echo date('F j, Y', strtotime($resource['created_at'])); ?></span>
                                    <span>Downloads: <?php echo $resource['download_count']; ?></span>
                                    <span>Size: <?php echo formatFileSize($resource['file_size']); ?></span>
                                </div>
                                <?php if ($resource['description']): ?>
                                    <p class="resource-description">
                                        <?php echo nl2br(htmlspecialchars($resource['description'])); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            <div class="resource-actions">
                                <a href="download-resource.php?id=<?php echo $resource['id']; ?>" class="btn">Download</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
        <?php include 'includes/footer-inc.php'; ?>
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