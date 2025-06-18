<?php
session_start();
require_once 'config/database.php';
include 'includes/timeout.php';

// Get filter parameters
$category = isset($_GET['category']) ? $_GET['category'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build query
$sql = "SELECT u.id, u.username, u.full_name, p.profile_picture, p.talent_category, p.bio as description
        FROM users u 
        LEFT JOIN profiles p ON u.id = p.user_id 
        WHERE u.role = 'user'";

if (!empty($category)) {
    $sql .= " AND p.talent_category = ?";
}
if (!empty($search)) {
    $sql .= " AND (p.bio LIKE ? OR u.full_name LIKE ?)";
}
$sql .= " ORDER BY u.created_at DESC";

// Prepare and execute query
if ($stmt = mysqli_prepare($conn, $sql)) {
    if (!empty($category) && !empty($search)) {
        $search_param = "%$search%";
        mysqli_stmt_bind_param($stmt, "sss", $category, $search_param, $search_param);
    } elseif (!empty($category)) {
        mysqli_stmt_bind_param($stmt, "s", $category);
    } elseif (!empty($search)) {
        $search_param = "%$search%";
        mysqli_stmt_bind_param($stmt, "ss", $search_param, $search_param);
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
                            <option value="Music" <?php echo $category == 'Music' ? 'selected' : ''; ?>>Music</option>
                            <option value="Art" <?php echo $category == 'Art' ? 'selected' : ''; ?>>Art</option>
                            <option value="Tech" <?php echo $category == 'Tech' ? 'selected' : ''; ?>>Tech</option>
                            <option value="Writing" <?php echo $category == 'Writing' ? 'selected' : ''; ?>>Writing
                            </option>
                        </select>
                    </div>
                    <div class="form-group">
                        <input type="submit" class="btn" value="Search">
                    </div>
                </form>

                <!-- Talent Grid -->
                <div class="grid">
                    <?php while ($talent = mysqli_fetch_assoc($result)): ?>
                        <div class="card">
                            <?php if (!empty($talent['profile_picture'])): ?>
                                <img src="<?php echo $talent['profile_picture']; ?>" alt="<?php echo $talent['full_name']; ?>"
                                    style="width: 100%; height: 200px; object-fit: cover;">
                            <?php endif; ?>
                            <h3><?php echo htmlspecialchars($talent['full_name']); ?></h3>
                            <p><?php echo htmlspecialchars($talent['description'] ?? ''); ?></p>
                            <div class="talent-meta">
                                <p>Category: <?php echo htmlspecialchars($talent['talent_category'] ?? 'Not specified'); ?>
                                </p>
                                <a href="talent-details.php?id=<?php echo $talent['id']; ?>" class="btn">View Details</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

        <?php include 'includes/footer-inc.php'; ?>
    </body>

</html>