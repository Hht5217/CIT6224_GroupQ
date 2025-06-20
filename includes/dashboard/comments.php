<?php
// Get user's comments
$sql = "SELECT c.id, c.comment, c.created_at, c.talent_id, c.parent_id, t.title AS talent_title
        FROM comments c
        JOIN talents t ON c.talent_id = t.id
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
                                <h3><?php echo htmlspecialchars($comment['talent_title']); ?></h3>
                                <div class="comment-content">
                                    <?php echo nl2br(htmlspecialchars($comment['comment'])); ?>
                                </div>
                                <span class="timestamp">
                                    <?php echo date('M d, Y H:i', strtotime($comment['created_at'])); ?>
                                </span>
                            </div>
                        </div>
                        <div class="comment-actions">
                            <a class="btn btn-secondary"
                                href="talent-details.php?id=<?php echo $comment['talent_id']; ?>#<?php echo !empty($comment['parent_id']) ? 'reply-' : 'comment-'; ?><?php echo $comment['id']; ?>">
                                View Comment
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>