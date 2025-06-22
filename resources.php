<?php
session_start();
require_once 'config/database.php';
require_once 'includes/file-upload-delete.php';
include 'includes/timeout.php';

// Get downloadable resources
$sql = "SELECT r.*, u.username FROM resources r JOIN users u ON r.user_id = u.id WHERE r.is_downloadable = 1 ORDER BY r.created_at DESC";
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
                        <a href="upload-resource.php" class="btn-primary"><i class="fas fa-upload"></i> Upload Resource</a>
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
                                <a href="download-resource.php?id=<?php echo $resource['id']; ?>" class="btn-primary"><i
                                        class="fas fa-download"></i> Download</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
        <?php include 'includes/footer-inc.php'; ?>
    </body>

</html>