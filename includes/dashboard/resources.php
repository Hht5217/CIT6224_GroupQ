<?php
// Helper function to format file size
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

// Handle resource deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete_resource') {
    $resource_id = $_POST['resource_id'];

    // Get resource info before deletion
    $stmt = $conn->prepare("SELECT file_path FROM resources WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $resource_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $resource = $result->fetch_assoc();

    if ($resource) {
        // Delete file from server
        if (file_exists($resource['file_path'])) {
            unlink($resource['file_path']);
        }

        // Delete from database
        $stmt = $conn->prepare("DELETE FROM resources WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $resource_id, $user_id);
        $stmt->execute();
    }

    // Refresh the page
    header("Location: user-dashboard.php?page=resources");
    exit();
}

// Handle resource upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'upload-resource') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $file = $_FILES['resource_file'];

    // Validate file
    $allowed_types = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'image/jpeg',
        'image/png',
        'video/mp4',
        'audio/mpeg'
    ];

    if (!in_array($file['type'], $allowed_types)) {
        $error = "Invalid file type. Allowed types: PDF, Word, Images, Video, Audio";
    } elseif ($file['size'] > 100 * 1024 * 1024) { // 100MB limit
        $error = "File size too large. Maximum size is 100MB";
    } else {
        // Create upload directory if it doesn't exist
        $upload_dir = 'uploads/resources/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Generate unique filename
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $file_extension;
        $filepath = $upload_dir . $filename;

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            // Insert into database
            $stmt = $conn->prepare("INSERT INTO resources (user_id, title, description, file_name, file_path, file_type, file_size) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssssi", $user_id, $title, $description, $file['name'], $filepath, $file['type'], $file['size']);

            if ($stmt->execute()) {
                $success = "Resource uploaded successfully";
            } else {
                $error = "Error uploading resource";
                // Delete uploaded file if database insert fails
                unlink($filepath);
            }
        } else {
            $error = "Error uploading file";
        }
    }
}
?>

<div class="dashboard-card">
    <div class="card-header">
        <h2>My Resources</h2>
        <button class="btn btn-primary" onclick="showUploadForm()">
            <i class="fas fa-upload"></i> Upload New Resource
        </button>
    </div>

    <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <!-- Upload Form -->
    <div id="uploadForm" class="upload-form" style="display: none;">
        <form action="user-dashboard.php?page=resources" method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="upload-resource">

            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="3"></textarea>
            </div>

            <div class="form-group">
                <label for="resource_file">File</label>
                <input type="file" id="resource_file" name="resource_file" class="form-control" required>
                <small class="form-text">Maximum file size: 100MB. Allowed types: PDF, Word, Images, Video,
                    Audio</small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Upload</button>
                <button type="button" class="btn btn-secondary" onclick="hideUploadForm()">Cancel</button>
            </div>
        </form>
    </div>

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
    function showUploadForm() {
        document.getElementById('uploadForm').style.display = 'block';
    }

    function hideUploadForm() {
        document.getElementById('uploadForm').style.display = 'none';
    }

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