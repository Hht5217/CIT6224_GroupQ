<?php

include 'includes/timeout.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Set custom max size (5MB)
$maxSizeMB = 5;

// Retrieve success/error messages from session
$success = isset($_SESSION['success']) ? $_SESSION['success'] : '';
$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
unset($_SESSION['success'], $_SESSION['error']);

// Get user information
$sql = "SELECT full_name, username, email FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

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
    mysqli_stmt_close($stmt);
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_profile') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $bio = trim($_POST['bio']);

    // Validate phone format (numbers and hyphens only)
    if (!empty($phone) && !preg_match('/^[0-9-]+$/', $phone)) {
        $_SESSION['error'] = "Phone number can only contain numbers and hyphens.";
        header("Location: user-dashboard.php?page=profile");
        exit();
    }

    // Handle profile picture upload
    if (isset($_FILES['file']) && $_FILES['file']['error'] == UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $result = uploadFile($_FILES['file'], 'assets/images/profiles/', $allowed_types, $maxSizeMB);

        if ($result['error']) {
            $_SESSION['error'] = $result['error'];
            header("Location: user-dashboard.php?page=profile");
            exit();
        } else {
            // Resize image if needed
            $image_info = getimagesize($result['filepath']);
            if ($image_info) {
                $max_width = 1000;
                $max_height = 1000;
                if ($image_info[0] > $max_width || $image_info[1] > $max_height) {
                    $source_image = imagecreatefromstring(file_get_contents($result['filepath']));
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

                    // Save resized image
                    $ext = strtolower(pathinfo($result['filepath'], PATHINFO_EXTENSION));
                    switch ($ext) {
                        case 'jpg':
                        case 'jpeg':
                            imagejpeg($new_image, $result['filepath'], 90);
                            break;
                        case 'png':
                            imagepng($new_image, $result['filepath'], 9);
                            break;
                        case 'gif':
                            imagegif($new_image, $result['filepath']);
                            break;
                    }

                    // Release memory
                    imagedestroy($source_image);
                    imagedestroy($new_image);
                }

                // Delete old profile picture
                if (!empty($profile['profile_picture']) && file_exists($profile['profile_picture'])) {
                    unlink($profile['profile_picture']);
                }

                // Update profile picture in database
                $stmt = $conn->prepare("UPDATE profiles SET profile_picture = ? WHERE user_id = ?");
                $stmt->bind_param("si", $result['filepath'], $_SESSION['user_id']);
                $stmt->execute();
                $stmt->close();
            } else {
                $_SESSION['error'] = "Invalid image file.";
                if (file_exists($result['filepath'])) {
                    unlink($result['filepath']);
                }
                header("Location: user-dashboard.php?page=profile");
                exit();
            }
        }
    }

    // Update user information
    $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ? WHERE id = ?");
    $stmt->bind_param("ssi", $full_name, $email, $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();

    // Update or insert profile information
    if (empty($profile)) {
        $stmt = $conn->prepare("INSERT INTO profiles (user_id, phone, bio) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $_SESSION['user_id'], $phone, $bio);
    } else {
        $stmt = $conn->prepare("UPDATE profiles SET phone = ?, bio = ? WHERE user_id = ?");
        $stmt->bind_param("ssi", $phone, $bio, $_SESSION['user_id']);
    }
    $stmt->execute();
    $stmt->close();

    $_SESSION['success'] = "Profile updated successfully.";
    header("Location: user-dashboard.php?page=profile");
    exit();
}
?>

<link rel="stylesheet" href="assets/css/profile.css">

<div class="dashboard-card">
    <h2>My Profile</h2>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="profile-form" id="profileForm"
        onsubmit="return validateFileSize();">
        <input type="hidden" name="action" value="update_profile">

        <div class="form-group">
            <div class="profile-picture-upload">
                <img src="<?php echo $profile['profile_picture'] ?? 'assets/images/default-avatar.png'; ?>"
                    alt="Profile Picture" id="preview">
                <input type="file" name="file" id="file" accept="image/jpeg,image/png,image/gif"
                    data-max-size="<?php echo $maxSizeMB; ?>" onchange="previewImage(this)">
                <small class="form-text text-muted">Supported types: JPG, PNG, GIF. Max size:
                    <?php echo number_format($maxSizeMB, 2); ?>MB</small>
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
                    value="<?php echo htmlspecialchars($profile['phone'] ?? ''); ?>" pattern="[0-9-]*"
                    title="Phone number can only contain numbers and hyphens (e.g., 123-456-7890)">
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

<script src="assets/js/validateFileSize.js"></script>
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
<script>
    // Track form changes
    let formIsDirty = false;
    const form = document.getElementById('profileForm');
    const inputs = form.querySelectorAll('input, textarea');

    inputs.forEach(input => {
        input.addEventListener('change', () => {
            formIsDirty = true;
        });
        input.addEventListener('input', () => {
            formIsDirty = true;
        });
    });

    // Clear dirty flag on form submission
    form.addEventListener('submit', () => {
        formIsDirty = false;
    });

    // Prompt on navigation if form is dirty
    window.addEventListener('beforeunload', (event) => {
        if (formIsDirty) {
            event.preventDefault();
            event.returnValue = 'You have unsaved changes. Are you sure you want to leave without saving?';
        }
    });
</script>