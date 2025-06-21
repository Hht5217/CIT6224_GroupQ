<?php
session_start();
require_once 'config/database.php';
include 'includes/timeout.php';
include_once 'includes/talent-categories.php';

// Get filter parameters
$category = isset($_GET['category']) ? $_GET['category'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build query
$sql = "SELECT t.*, u.full_name, u.username, p.profile_picture
        FROM talents t
        JOIN users u ON t.user_id = u.id
        LEFT JOIN profiles p ON u.id = p.user_id
        WHERE 1";
$params = [];
$types = "";

if (!empty($category)) {
    $sql .= " AND t.category = ?";
    $params[] = $category;
    $types .= "s";
}
if (!empty($search)) {
    $sql .= " AND (t.title LIKE ? OR t.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= "ss";
}
$sql .= " ORDER BY t.created_at DESC";

// Prepare and execute query
if ($stmt = mysqli_prepare($conn, $sql)) {
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Talent Catalogue - MMU Talent Showcase</title>
        <link rel="stylesheet" href="assets/css/style.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    </head>

    <body>
        <?php include 'includes/header-inc.php'; ?>
        <?php include 'includes/navbar.php'; ?>

        <div class="container">
            <div class="card">
                <h2>Talent Catalogue</h2>

                <!-- Search and Filter Form -->
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="get" class="search-form">
                    <div class="form-group">
                        <input type="text" name="search" class="form-control" placeholder="Search talents..."
                            value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="form-group">
                        <select name="category" class="form-control">
                            <option value="">All Categories</option>
                            <?php
                            showTalentCategoryOptions($selectedCategory ?? '');
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <input type="submit" class="btn" value="Search">
                    </div>
                </form>

                <?php
                if (!isset($result)) {
                    echo "<p style='color:red;'>Query did not run or \$result is not set.</p>";
                } elseif (mysqli_num_rows($result) == 0) {
                    echo "<p>No talents found.</p>";
                }
                ?>
                <div class="grid">
                    <?php while ($talent = mysqli_fetch_assoc($result)): ?>
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
                                <p>By: <?php echo htmlspecialchars($talent['full_name']); ?></p>
                                <a href="talent-details.php?id=<?php echo $talent['id']; ?>" class="btn btn-primary">View
                                    Details</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

        <?php include 'includes/footer-inc.php'; ?>
    </body>

</html>