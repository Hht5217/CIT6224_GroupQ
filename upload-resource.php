<?php
session_start();
require_once 'config/database.php';
include 'includes/timeout.php';
include 'includes/login-required.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);

    if (empty($title)) {
        $error = "Please enter a title";
    } elseif (!isset($_FILES['resource']) || $_FILES['resource']['error'] == UPLOAD_ERR_NO_FILE) {
        $error = "Please select a file to upload";
    } else {
        $file = $_FILES['resource'];
        $file_name = $file['name'];
        $file_tmp = $file['tmp_name'];
        $file_size = $file['size'];
        $file_type = $file['type'];

        // Create uploads directory if it doesn't exist
        $upload_dir = 'uploads/resources/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Generate unique filename
        $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
        $new_file_name = uniqid() . '.' . $file_extension;
        $file_path = $upload_dir . $new_file_name;

        // Move uploaded file
        if (move_uploaded_file($file_tmp, $file_path)) {
            $sql = "INSERT INTO resources (user_id, title, description, file_name, file_path, file_type, file_size) VALUES (?, ?, ?, ?, ?, ?, ?)";
            if ($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "isssssi", $_SESSION['user_id'], $title, $description, $file_name, $file_path, $file_type, $file_size);
                if (mysqli_stmt_execute($stmt)) {
                    $success = "Resource uploaded successfully";
                    $title = '';
                    $description = '';
                } else {
                    $error = "Error uploading resource";
                    // Delete uploaded file if database insert fails
                    unlink($file_path);
                }
            }
        } else {
            $error = "Error uploading file";
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
                    enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="title" class="form-control"
                            value="<?php echo isset($title) ? htmlspecialchars($title) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control"
                            rows="4"><?php echo isset($description) ? htmlspecialchars($description) : ''; ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>File</label>
                        <input type="file" name="resource" class="form-control" required>
                        <small class="form-text text-muted">Supported file types: PDF, DOC, DOCX, ZIP, RAR, MP4, MP3,
                            JPG, PNG</small>
                    </div>
                    <div class="form-group">
                        <input type="submit" class="btn" value="Upload Resource">
                    </div>
                </form>
            </div>
        </div>
        <?php include 'includes/footer-inc.php'; ?>
    </body>

</html>