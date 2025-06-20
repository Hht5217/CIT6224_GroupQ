<?php
//require_once 'includes/file-upload-delete.php';
include_once __DIR__ . '/../talent-categories.php';

// Get default max size from php.ini
$defaultMaxSize = ini_get('upload_max_filesize');
$maxSizeBytes = convertToBytes($defaultMaxSize);
$maxSizeMB = $maxSizeBytes / (1024 * 1024);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete_resource') {
    if (deleteResource($conn, $_POST['resource_id'], $_SESSION['user_id'])) {
        header("Location: user-dashboard.php?page=resources");
        exit();
    }
}

// Handle talent upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'upload-talent') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category = $_POST['category'];
    $is_downloadable = isset($_POST['is_downloadable']) ? 1 : 0;
    $file = $_FILES['media'];

    if (empty($title) || empty($description) || empty($category)) {
        $error = "All fields are required";
    } elseif (!isset($file) || $file['error'] == UPLOAD_ERR_NO_FILE) {
        $error = "Please select a file to upload";
    } else {
        $allowed_types = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'video/mp4',
            'video/webm',
            'video/ogg',
            'audio/mp3',
            'audio/wav',
            'audio/ogg',
            'text/plain',
            'text/html',
            'text/css',
            'text/csv',
            'application/javascript',
            'application/pdf',
            'application/zip',
            'application/json'
        ];

        $result = uploadFile($file, 'uploads/talents/', $allowed_types, $maxSizeMB);
        if ($result['error']) {
            $error = $result['error'];
        } else {
            $stmt = $conn->prepare("INSERT INTO talents (user_id, title, description, category, media_path) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issss", $_SESSION['user_id'], $title, $description, $category, $result['filepath']);

            if ($stmt->execute()) {
                $talent_id = $stmt->insert_id;
                // Always create a resource
                $stmt2 = $conn->prepare("INSERT INTO resources (user_id, talent_id, title, description, file_name, file_path, file_type, file_size, is_downloadable) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt2->bind_param("iisssssii", $_SESSION['user_id'], $talent_id, $title, $description, $result['filename'], $result['filepath'], $result['file_type'], $result['file_size'], $is_downloadable);
                if ($stmt2->execute()) {
                    $success = "Talent uploaded successfully";
                } else {
                    $error = "Error creating resource";
                    if (file_exists($result['filepath'])) {
                        unlink($result['filepath']);
                    }
                }
                mysqli_stmt_close($stmt2);
            } else {
                $error = "Error uploading talent";
                if (file_exists($result['filepath'])) {
                    unlink($result['filepath']);
                }
            }
            mysqli_stmt_close($stmt);
        }
    }
}
?>

<div class="dashboard-card">
    <div class="card-header">
        <h2>My Talents</h2>
        <div>
            <button class="btn btn-primary" onclick="showUploadForm()">
                <i class="fas fa-upload"></i> Upload New Talent
            </button>
            <a href="talent-catalogue.php" class="btn btn-secondary">
                <i class="fas fa-list"></i> Browse Talent Catalogue
            </a>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <!-- Upload Form -->
    <div id="uploadForm" class="upload-form" style="display: none;">
        <form action="user-dashboard.php?page=talents" method="post" enctype="multipart/form-data"
            onsubmit="return validateFileSize();">
            <input type="hidden" name="action" value="upload-talent">

            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="3" required></textarea>
            </div>

            <div class="form-group">
                <label for="category">Category</label>
                <select id="category" name="category" class="form-control" required>
                    <option value="">Select a category</option>
                    <?php renderTalentCategoryOptions($selectedCategory ?? ''); ?>
                </select>
            </div>

            <div class="form-group">
                <label for="media">File</label>
                <input type="file" id="media" name="media" class="form-control"
                    data-max-size="<?php echo $maxSizeMB; ?>" required>
                <small class="form-text">Supported types: JPEG, PNG, GIF, MP4, WEBM, OGG, MP3, WAV, TXT, HTML, CSS, CSV,
                    JS, PDF, ZIP, JSON. Max size: <?php echo number_format($maxSizeMB, 2); ?>MB</small>
            </div>

            <label>
                <input type="checkbox" name="is_downloadable" value="1"> Allow download
            </label>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Upload Talent</button>
                <button type="button" class="btn btn-secondary" onclick="hideUploadForm()">Cancel</button>
            </div>
        </form>
    </div>

    <?php if (empty($talents)): ?>
        <div class="empty-state">
            <i class="fas fa-file"></i>
            <p>No talents found. Start by uploading your first talent!</p>
        </div>
    <?php else: ?>
        <div class="grid">
            <?php foreach ($talents as $talent): ?>
                <div class="card">
                    <div class="talent-icon">
                        <?php
                        $icon = 'fa-file';
                        if (!empty($talent['media_path'])) {
                            $ext = strtolower(pathinfo($talent['media_path'], PATHINFO_EXTENSION));
                            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                                $icon = 'fa-file-image';
                            } elseif (in_array($ext, ['mp4', 'webm', 'ogg'])) {
                                $icon = 'fa-file-video';
                            } elseif (in_array($ext, ['mp3', 'wav', 'ogg'])) {
                                $icon = 'fa-file-audio';
                            } elseif (in_array($ext, ['txt', 'py', 'js', 'html', 'css', 'csv'])) {
                                $icon = 'fa-file-code';
                            } elseif (in_array($ext, ['pdf', 'zip', 'json'])) {
                                $icon = 'fa-file-alt';
                            }
                        }
                        ?>
                        <i class="fas <?php echo $icon; ?>" style="font-size: 3rem; color: #555;"></i>
                    </div>
                    <h3><?php echo htmlspecialchars($talent['title']); ?></h3>
                    <p><?php echo htmlspecialchars($talent['description'] ?? ''); ?></p>
                    <div class="talent-meta">
                        <p>Category: <?php
                        $cat = $talent['category'] ?? '';
                        echo isset($talent_categories[$cat]) ? $talent_categories[$cat] : 'Not specified';
                        ?></p>
                        <a href="talent-details.php?id=<?php echo $talent['id']; ?>" class="btn">View Details</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script src="assets/js/validateFileSize.js"></script>
<script>
    function showUploadForm() {
        document.getElementById('uploadForm').style.display = 'block';
    }

    function hideUploadForm() {
        document.getElementById('uploadForm').style.display = 'none';
    }
</script>