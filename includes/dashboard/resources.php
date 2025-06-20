<?php
//require_once 'includes/file-upload-delete.php';

// Handle resource deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete_resource') {
    if (deleteResource($conn, $_POST['resource_id'], $user_id)) {
        header("Location: user-dashboard.php?page=resources");
        exit();
    }
}
?>

<div class="dashboard-card">
    <div class="card-header">
        <h2>My Resources</h2>
        <a href="upload-resource.php" class="btn btn-primary">
            <i class="fas fa-upload"></i> Upload New Resource
        </a>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <?php if (empty($resources)): ?>
        <div class="empty-state">
            <i class="fas fa-file"></i>
            <p>No resources found. Start by uploading your first resource!</p>
        </div>
    <?php else: ?>
        <div class="resources-grid">
            <?php foreach ($resources as $resource): ?>
                <div class="resource-card">
                    <div class="resource-icon">
                        <?php
                        $icon = 'fa-file';
                        if (strpos($resource['file_type'], 'pdf') !== false) {
                            $icon = 'fa-file-pdf';
                        } elseif (strpos($resource['file_type'], 'word') !== false) {
                            $icon = 'fa-file-word';
                        } elseif (strpos($resource['file_type'], 'image') !== false) {
                            $icon = 'fa-file-image';
                        } elseif (strpos($resource['file_type'], 'video') !== false) {
                            $icon = 'fa-file-video';
                        } elseif (strpos($resource['file_type'], 'audio') !== false) {
                            $icon = 'fa-file-audio';
                        }
                        ?>
                        <i class="fas <?php echo $icon; ?>"></i>
                    </div>
                    <div class="resource-info">
                        <h3><?php echo htmlspecialchars($resource['title']); ?></h3>
                        <p class="resource-meta">
                            <span><i class="fas fa-download"></i> <?php echo $resource['download_count']; ?></span>
                            <span><i class="fas fa-calendar"></i>
                                <?php echo date('M d, Y', strtotime($resource['created_at'])); ?></span>
                            <span><i class="fas fa-weight"></i> <?php echo formatFileSize($resource['file_size']); ?></span>
                        </p>
                        <?php if ($resource['description']): ?>
                            <p class="resource-description"><?php echo nl2br(htmlspecialchars($resource['description'])); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="resource-actions">
                        <a href="download-resource.php?id=<?php echo $resource['id']; ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-download"></i> Download
                        </a>
                        <button onclick="deleteResource(<?php echo $resource['id']; ?>)" class="btn btn-sm btn-danger">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
    function deleteResource(resourceId) {
        if (confirm('Are you sure you want to delete this resource?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
            <input type="hidden" name="action" value="delete_resource">
            <input type="hidden" name="resource_id" value="${resourceId}">
        `;
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>