<?php
// Get user profile information
$sql = "SELECT p.* FROM profiles p WHERE p.user_id = ?";
$profile = [];
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_assoc($result)) {
        $profile = $row;
    }
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_profile') {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $bio = $_POST['bio'];

    // Handle profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $max_size = 5 * 1024 * 1024; // 5MB
        $filename = $_FILES['profile_picture']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        // Validate file type
        if (!in_array($ext, $allowed)) {
            $error = "Invalid file type. Allowed types: " . implode(', ', $allowed);
        }
        // Validate file size
        elseif ($_FILES['profile_picture']['size'] > $max_size) {
            $error = "File is too large. Maximum size is 5MB.";
        } else {
            // Make upload directory string
            $upload_dir = 'assets/images/profiles/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            // Generate a safe file name
            $new_filename = 'profile_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;
            $upload_path = $upload_dir . $new_filename;

            // Validate image size
            $image_info = getimagesize($_FILES['profile_picture']['tmp_name']);
            if ($image_info === false) {
                $error = "Invalid image file.";
            } else {
                $max_width = 1000;
                $max_height = 1000;
                if ($image_info[0] > $max_width || $image_info[1] > $max_height) {
                    // Create temp image
                    $source_image = imagecreatefromstring(file_get_contents($_FILES['profile_picture']['tmp_name']));
                    $new_image = imagecreatetruecolor($max_width, $max_height);

                    // Maintain aspect ratio
                    $ratio = min($max_width / $image_info[0], $max_height / $image_info[1]);
                    $new_width = $image_info[0] * $ratio;
                    $new_height = $image_info[1] * $ratio;

                    // Resize the image
                    imagecopyresampled(
                        $new_image,
                        $source_image,
                        ($max_width - $new_width) / 2,
                        ($max_height - $new_height) / 2,
                        0,
                        0,
                        $new_width,
                        $new_height,
                        $image_info[0],
                        $image_info[1]
                    );

                    // Save the resized image
                    switch ($ext) {
                        case 'jpg':
                        case 'jpeg':
                            imagejpeg($new_image, $upload_path, 90);
                            break;
                        case 'png':
                            imagepng($new_image, $upload_path, 9);
                            break;
                        case 'gif':
                            imagegif($new_image, $upload_path);
                            break;
                    }

                    // Release memory
                    imagedestroy($source_image);
                    imagedestroy($new_image);
                } else {
                    // Move the file directly
                    if (!move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
                        $error = "Failed to upload image.";
                    }
                }

                if (!isset($error)) {
                    // Delete old profile picture
                    if (!empty($profile['profile_picture']) && file_exists($profile['profile_picture'])) {
                        unlink($profile['profile_picture']);
                    }

                    // Update profile picture in database
                    $stmt = $conn->prepare("UPDATE profiles SET profile_picture = ? WHERE user_id = ?");
                    $stmt->bind_param("si", $upload_path, $_SESSION['user_id']);
                    $stmt->execute();
                }
            }
        }
    }

    if (!isset($error)) {
        // Update user information
        $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssi", $full_name, $email, $_SESSION['user_id']);
        $stmt->execute();

        // Update or insert profile information
        if (empty($profile)) {
            $stmt = $conn->prepare("INSERT INTO profiles (user_id, phone, bio) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $_SESSION['user_id'], $phone, $bio);
        } else {
            $stmt = $conn->prepare("UPDATE profiles SET phone = ?, bio = ? WHERE user_id = ?");
            $stmt->bind_param("ssi", $phone, $bio, $_SESSION['user_id']);
        }
        $stmt->execute();

        // Refresh the page
        header("Location: user-dashboard.php?page=profile");
        exit();
    }
}
?>

<link rel="stylesheet" href="assets/css/profile.css">

<div class="dashboard-card">
    <h2>My Profile</h2>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="profile-form">
        <input type="hidden" name="action" value="update_profile">

        <div class="form-group">
            <div class="profile-picture-upload">
                <img src="<?php echo $profile['profile_picture'] ?? 'assets/images/default-avatar.png'; ?>"
                    alt="Profile Picture" id="preview">
                <input type="file" name="profile_picture" id="profile_picture" accept="image/jpeg,image/png,image/gif"
                    onchange="previewImage(this)">
                <small class="form-text text-muted">Maximum file size: 5MB. Allowed formats: JPG, PNG, GIF</small>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
            </div>

            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" name="full_name" id="full_name"
                    value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>"
                    required>
            </div>

            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="tel" name="phone" id="phone"
                    value="<?php echo htmlspecialchars($profile['phone'] ?? ''); ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="bio">Bio</label>
            <textarea name="bio" id="bio" rows="4"><?php echo htmlspecialchars($profile['bio'] ?? ''); ?></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
    </form>
</div>

<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('preview').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>