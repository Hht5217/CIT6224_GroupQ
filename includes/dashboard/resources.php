<?php
//require_once 'includes/file-upload-delete.php';

// Handle resource deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete_resource') {
    $resource_id = isset($_POST['resource_id']) ? (int) $_POST['resource_id'] : 0;
    $stmt = $conn->prepare("SELECT file_path, user_id FROM resources WHERE id = ?");
    $stmt->bind_param("i", $resource_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $resource = $result->fetch_assoc();
    $stmt->close();

    if ($resource && $resource['user_id'] == $user_id) {
        if (!empty($resource['file_path']) && file_exists($resource['file_path'])) {
            unlink($resource['file_path']);
        }
        $stmt = $conn->prepare("DELETE FROM resources WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $resource_id, $user_id);
        if ($stmt->execute()) {
            $success = "Resource deleted successfully.";
        } else {
            $error = "Error deleting resource.";
        }
        $stmt->close();
    } else {
        $error = "You do not have permission to delete this resource.";
    }
    header("Location: user-dashboard.php?page=resources");
    exit();
}

// Handle downloadable status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_downloadable') {
    $resource_id = isset($_POST['resource_id']) ? (int) $_POST['resource_id'] : 0;
    $is_downloadable = isset($_POST['is_downloadable']) ? 1 : 0;

    $stmt = $conn->prepare("SELECT user_id FROM resources WHERE id = ?");
    $stmt->bind_param("i", $resource_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $resource = $result->fetch_assoc();
    $stmt->close();

    if ($resource && $resource['user_id'] == $user_id) {
        $stmt = $conn->prepare("UPDATE resources SET is_downloadable = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("iii", $is_downloadable, $resource_id, $user_id);
        if ($stmt->execute()) {
            $success = "Downloadable status updated successfully.";
        } else {
            $error = "Error updating downloadable status.";
        }
        $stmt->close();
    } else {
        $error = "You do not have permission to update this resource.";
    }
    header("Location: user-dashboard.php?page=resources");
    exit();
}

// Fetch user's resources
$sql = "SELECT id, title, file_type, file_size, download_count, created_at, description, is_downloadable
        FROM resources WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$resources = [];
while ($row = $result->fetch_assoc()) {
    $resources[] = $row;
}
$stmt->close();

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
                        <?php if ($resource['description']): ?>
                            <p class="resource-description"><?php echo nl2br(htmlspecialchars($resource['description'])); ?></p>
                        <?php endif; ?>
                        <p class="resource-meta">
                            <span><i class="fas fa-download"></i> <?php echo $resource['download_count']; ?></span>
                            <span><i class="fas fa-calendar"></i>
                                <?php echo date('M d, Y', strtotime($resource['created_at'])); ?></span>
                            <span><i class="fas fa-weight"></i> <?php echo formatFileSize($resource['file_size']); ?></span>
                        </p>
                    </div>
                    <div class="resource-actions">
                        <form method="POST">
                            <input type="hidden" name="action" value="update_downloadable">
                            <input type="hidden" name="resource_id" value="<?php echo $resource['id']; ?>">
                            <label>
                                <input type="checkbox" name="is_downloadable" value="1" <?php echo $resource['is_downloadable'] ? 'checked' : ''; ?> onchange="this.form.submit()">
                                Make downloadable
                            </label>
                        </form>
                        <div>
                            <a href="download-resource.php?id=<?php echo $resource['id']; ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-download"></i> Download
                            </a>
                            <button onclick="deleteResource(<?php echo $resource['id']; ?>)" class="btn btn-sm btn-danger">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
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