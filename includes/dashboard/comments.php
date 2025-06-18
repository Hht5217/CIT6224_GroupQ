<?php
// Get user's comments
$sql = "SELECT c.*, 
               u.username AS commenter_username, u.full_name AS commenter_full_name,
               owner.username AS owner_username, owner.full_name AS owner_full_name,
               p.profile_picture AS commenter_profile_picture,
               owner_p.profile_picture AS owner_profile_picture
        FROM comments c 
        JOIN users u ON c.user_id = u.id 
        LEFT JOIN profiles p ON u.id = p.user_id
        JOIN users owner ON c.commented_user_id = owner.id
        LEFT JOIN profiles owner_p ON owner.id = owner_p.user_id
        WHERE c.user_id = ? 
        ORDER BY c.created_at DESC";
$user_comments = [];
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $user_comments[] = $row;
    }
}

// Debug information
echo "<!-- Debug Info: -->";
echo "<!-- User ID: " . $_SESSION['user_id'] . " -->";
echo "<!-- User comments count: " . count($user_comments) . " -->";

// Handle comment deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete-comment'])) {
    $comment_id = (int) $_POST['comment_id'];
    $sql = "DELETE FROM comments WHERE id = ? AND user_id = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "ii", $comment_id, $_SESSION['user_id']);
        if (mysqli_stmt_execute($stmt)) {
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit();
        }
    }
}
?>

<link rel="stylesheet" href="assets/css/comments.css">

<div class="dashboard-card">
    <h2>My Comments</h2>

    <!-- User's Comments -->
    <div class="comments-section">
        <?php if (empty($user_comments)): ?>
            <p class="no-data">You haven't made any comments yet.</p>
        <?php else: ?>
            <div class="comments-list">
                <?php foreach ($user_comments as $comment): ?>
                    <div class="comment-card">
                        <div class="comment-header">
                            <div>
                                <img src="<?php echo !empty($comment['owner_profile_picture']) ? $comment['owner_profile_picture'] : 'assets/images/default-avatar.png'; ?>"
                                    alt="<?php echo htmlspecialchars($comment['owner_username']); ?>" class="avatar">
                                <h3><?php echo htmlspecialchars($comment['owner_full_name']); ?></h3>
                                <div>
                                    <?php echo nl2br(htmlspecialchars($comment['comment'])); ?>
                                    </br>
                                    <span
                                        class="timestamp"><?php echo date('M d, Y H:i', strtotime($comment['created_at'])); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="comment-actions">
                            <a class="btn"
                                href="talent-details.php?id=<?php echo $comment['commented_user_id']; ?>#comments-section">
                                Go to comment</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    function deleteComment(commentId) {
        if (confirm('Are you sure you want to delete this comment?')) {
            fetch('delete-comment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `comment_id=${commentId}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message);
                    }
                });
        }
    }
</script>