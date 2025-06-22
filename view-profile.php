<?php
session_start();
require_once 'config/database.php';
include 'includes/timeout.php';

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: talent-catalogue.php");
    exit();
}

$user_id = $_GET['id'];

// Get user and profile information
$sql = "SELECT u.*, p.*
        FROM users u 
        LEFT JOIN profiles p ON u.id = p.user_id 
        WHERE u.id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    header("Location: talent-catalogue.php");
    exit();
}

// Get user's talents
$sql = "SELECT * FROM talents WHERE user_id = ? ORDER BY created_at DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$talents = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($user['full_name']); ?> - Profile</title>
        <link rel="stylesheet" href="assets/css/style.css">
        <link rel="stylesheet" href="assets/css/details.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    </head>

    <body>
        <?php include 'includes/header-inc.php'; ?>
        <?php include 'includes/navbar.php'; ?>

        <div class="container">
            <div class="details-container">
                <!-- Profile Header -->
                <div class="details-header">
                    <div class="profile-image-container">
                        <img src="<?php echo !empty($user['profile_picture']) ? htmlspecialchars($user['profile_picture']) : 'assets/images/default-avatar.png'; ?>"
                            alt="<?php echo htmlspecialchars($user['username']); ?>" class="profile-image">
                    </div>
                    <div class="details-info">
                        <h1><?php echo htmlspecialchars($user['username']); ?></h1>
                        <p class="talent-category">
                            <i class="fas fa-user"></i>
                            <?php echo htmlspecialchars($user['full_name']); ?>
                        </p>
                    </div>
                </div>

                <!-- Profile Content -->
                <div class="details-content">
                    <!-- About Section -->
                    <div class="details-section">
                        <h2><i class="fas fa-user"></i> About</h2>
                        <div class="section-content">
                            <p><?php echo !empty(trim($user['bio'])) ? nl2br(htmlspecialchars($user['bio'])) : 'No bio available.'; ?>
                            </p>
                        </div>
                    </div>

                    <!-- Contacts Section -->
                    <div class="details-section">
                        <h2><i class="fas fa-address-book"></i> Contact</h2>
                        <div class="section-content">
                            <p><strong>Email:</strong>
                                <?php
                                if (!empty(trim($user['email']))) {
                                    $email = htmlspecialchars($user['email']);
                                    echo '<a href="mailto:' . $email . '">' . $email . '</a>';
                                } else {
                                    echo 'Not provided';
                                }
                                ?>
                            </p>
                            <p><strong>Phone:</strong>
                                <?php echo !empty(trim($user['phone'])) ? htmlspecialchars($user['phone']) : 'Not provided'; ?>
                            </p>
                        </div>
                    </div>

                    <!-- Talents Section -->
                    <?php if (mysqli_num_rows($talents) > 0): ?>
                        <div class="details-section">
                            <h2><i class="fas fa-star"></i> Talents</h2>
                            <div class="grid">
                                <?php while ($talent = mysqli_fetch_assoc($talents)): ?>
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
                                            <p>Category: <?php echo htmlspecialchars($talent['category'] ?? 'Not specified'); ?>
                                            </p>
                                            <a href="talent-details.php?id=<?php echo $talent['id']; ?>"
                                                class="btn btn-primary">View Details</a>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="details-section">
                            <h2><i class="fas fa-star"></i> Talents</h2>
                            <div class="empty-state">
                                <i class="fas fa-star"></i>
                                <p>No talents available.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php include 'includes/footer-inc.php'; ?>
    </body>

</html>