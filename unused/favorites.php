<?php
session_start();
require_once 'config/database.php';
include 'includes/timeout.php';
include 'includes/login-required.php';

$user_id = $_SESSION['user_id'];

// Get user's favorites
$sql = "SELECT DISTINCT u.id, u.username, u.full_name, p.profile_picture, p.talent_category, p.bio,
        s.views, s.downloads, s.favorites
        FROM favorites f 
        JOIN users u ON f.talent_id = u.id 
        LEFT JOIN profiles p ON u.id = p.user_id
        LEFT JOIN statistics s ON u.id = s.talent_id
        WHERE f.user_id = ?
        GROUP BY u.id
        ORDER BY f.created_at DESC";

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>My Favorites - MMU Talent Showcase</title>
        <link rel="stylesheet" href="assets/css/style.css">
    </head>

    <body>
        <?php include 'includes/header-inc.php'; ?>
        <?php include 'includes/navbar.php'; ?>

        <div class="container">
            <div class="card">
                <h2>My Favorites</h2>

                <?php if (mysqli_num_rows($result) > 0): ?>
                    <div class="grid">
                        <?php while ($talent = mysqli_fetch_assoc($result)): ?>
                            <div class="card">
                                <?php if (!empty($talent['profile_picture'])): ?>
                                    <img src="<?php echo $talent['profile_picture']; ?>" alt="<?php echo $talent['full_name']; ?>"
                                        style="width: 100%; height: 200px; object-fit: cover;">
                                <?php endif; ?>
                                <h3><?php echo htmlspecialchars($talent['full_name']); ?></h3>
                                <p><?php echo htmlspecialchars($talent['bio'] ?? ''); ?></p>
                                <div class="talent-meta">
                                    <p>Category: <?php echo htmlspecialchars($talent['talent_category'] ?? 'Not specified'); ?>
                                    </p>
                                    <div class="talent-stats">
                                        <span><i class="fas fa-eye"></i> <?php echo $talent['views'] ?? 0; ?> views</span>
                                    </div>
                                    <div class="talent-actions">
                                        <a href="talent-details.php?id=<?php echo $talent['id']; ?>" class="btn">View
                                            Details</a>
                                        <button class="btn btn-secondary remove-favorite"
                                            data-talent-id="<?php echo $talent['id']; ?>">Remove</button>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <p>You haven't added any favorites yet.</p>
                        <a href="talent-catalogue.php" class="btn">Browse Talents</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php include 'includes/footer-inc.php'; ?>

        <script>
            document.querySelectorAll('.remove-favorite').forEach(button => {
                button.addEventListener('click', function () {
                    const talentId = this.dataset.talentId;
                    if (confirm('Are you sure you want to remove this talent from your favorites?')) {
                        fetch('toggle-favorite.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `talent_id=${talentId}&action=remove`
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    this.closest('.card').remove();
                                    if (document.querySelectorAll('.card').length === 0) {
                                        location.reload();
                                    }
                                } else {
                                    alert(data.message);
                                }
                            });
                    }
                });
            });
        </script>
    </body>

</html>