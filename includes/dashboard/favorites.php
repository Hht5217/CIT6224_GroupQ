<?php
// Handle favorite removal
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'remove_favorite') {
    $talent_id = (int) $_POST['talent_id'];

    $stmt = $conn->prepare("DELETE FROM favorites WHERE user_id = ? AND talent_id = ?");
    $stmt->bind_param("ii", $user_id, $talent_id);
    if ($stmt->execute()) {
        // Update talents favorites_count
        $stmt = $conn->prepare("UPDATE talents SET favorites_count = favorites_count - 1 WHERE id = ? AND favorites_count > 0");
        $stmt->bind_param("i", $talent_id);
        $stmt->execute();
    }

    // Refresh the page
    header("Location: user-dashboard.php?page=favorites");
    exit();
}

// Include talent categories for mapping
include_once 'includes/talent-categories.php';
?>

<link rel="stylesheet" href="assets/css/favorites.css">

<div class="dashboard-card">
    <h2>My Favorites</h2>

    <?php if (empty($favorites)): ?>
        <div class="empty-state">
            <i class="fas fa-heart"></i>
            <p>No favorites yet.</p>
            <a href="talent-catalogue.php" class="btn btn-primary">Browse Talents</a>
        </div>
    <?php else: ?>
        <div class="favorites-grid">
            <?php foreach ($favorites as $favorite): ?>
                <div class="favorite-card">
                    <div class="favorite-info">
                        <h3><?php echo htmlspecialchars($favorite['title']); ?></h3>
                        <p class="talent-category">
                            <i class="fas fa-star"></i>
                            <?php echo isset($talent_categories[$favorite['category']]) ? htmlspecialchars($talent_categories[$favorite['category']]) : ($favorite['category'] ?? 'Not specified'); ?>
                        </p>
                        <p>By: <?php echo htmlspecialchars($favorite['full_name']); ?></p>
                        <div class="favorite-actions">
                            <a href="talent-details.php?id=<?php echo $favorite['talent_id']; ?>" class="btn btn-primary">View
                                Details</a>
                            <button onclick="removeFavorite(<?php echo $favorite['talent_id']; ?>)" class="btn btn-danger">
                                <i class="fas fa-heart-broken"></i> Remove
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
    function removeFavorite(talentId) {
        if (confirm('Are you sure you want to remove this talent from your favorites?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
            <input type="hidden" name="action" value="remove_favorite">
            <input type="hidden" name="talent_id" value="${talentId}">
        `;
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>