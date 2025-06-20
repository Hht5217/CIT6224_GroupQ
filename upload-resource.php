<?php
session_start();
require_once 'config/database.php';
require_once 'includes/file-upload-delete.php';
include 'includes/timeout.php';
include 'includes/login-required.php';

// Get default max size from php.ini
$defaultMaxSize = ini_get('upload_max_filesize');
$maxSizeBytes = convertToBytes($defaultMaxSize);
$maxSizeMB = $maxSizeBytes / (1024 * 1024); // Convert to MB for display

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $is_downloadable = isset($_POST['is_downloadable']) ? 1 : 0;

    if (empty($title)) {
        $error = "Please enter a title";
    } elseif (!isset($_FILES['resource']) || $_FILES['resource']['error'] == UPLOAD_ERR_NO_FILE) {
        $error = "Please select a file to upload";
    } else {
        $file = $_FILES['resource'];
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

        $result = uploadFile($file, 'uploads/resources/', $allowed_types, $maxSizeMB);
        if ($result['error']) {
            $error = $result['error'];
        } else {
            $sql = "INSERT INTO resources (user_id, title, description, file_name, file_path, file_type, file_size, is_downloadable) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            if ($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "isssssii", $_SESSION['user_id'], $title, $description, $result['filename'], $result['filepath'], $result['file_type'], $result['file_size'], $is_downloadable);
                if (mysqli_stmt_execute($stmt)) {
                    $success = "Resource uploaded successfully";
                    $title = '';
                    $description = '';
                } else {
                    $error = "Error uploading resource";
                    if (file_exists($result['filepath'])) {
                        unlink($result['filepath']);
                    }
                }
                mysqli_stmt_close($stmt);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Upload Resource - MMU Talent Showcase</title>
        <link rel="stylesheet" href="assets/css/style.css">
    </head>

    <body>
        <?php include 'includes/header-inc.php'; ?>
        <?php include 'includes/navbar.php'; ?>

        <div class="container">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <div class="card">
                <h2>Upload Resource</h2>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post"
                    enctype="multipart/form-data" onsubmit="return validateFileSize($maxSizeMB);">
                    <div class="form-group">
                        <label for="title">Title</label>
                        <input type="text" name="title" id="title" class="form-control"
                            value="<?php echo isset($title) ? htmlspecialchars($title) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea name="description" id="description" class="form-control"
                            rows="4"><?php echo isset($description) ? htmlspecialchars($description) : ''; ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="resource">File</label>
                        <input type="file" id="resource" name="resource" class="form-control"
                            data-max-size="<?php echo $maxSizeMB; ?>" required>
                        <small class="form-text text-muted">Supported types: JPG, PNG, GIF, MP4, WEBM, OGG, MP3, WAV,
                            HTML, CSS, TXT, CSV, JS, PDF, ZIP, JSON. Max size:
                            <?php echo number_format($maxSizeMB, 2); ?>MB</small>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_downloadable" value="1" <?php echo isset($_POST['is_downloadable']) ? 'checked' : ''; ?>> Allow download
                        </label>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Upload Resource</button>
                    </div>
                </form>
            </div>
        </div>
        <?php include 'includes/footer-inc.php'; ?>


        <script src="assets/js/validateFileSize.js"></script>

    </body>

</html>