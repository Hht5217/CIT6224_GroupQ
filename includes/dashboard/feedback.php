<?php
// Assumes $feedback is available from user-dashboard.php
?>

<link rel="stylesheet" href="assets/css/comments.css">

<div class="dashboard-card">
    <h2>My Feedback</h2>

    <?php if (empty($feedback)): ?>
        <div class="empty-state">
            <i class="fas fa-comment-alt"></i>
            <p>You haven't submitted any feedback yet.</p>
        </div>
    <?php else: ?>
        <div class="feedback-list">
            <?php foreach ($feedback as $item): ?>
                <div class="feedback-item">
                    <h4><?php echo htmlspecialchars($item['subject']); ?></h4>
                    <div class="feedback-meta">
                        <span>Posted on: <?php echo date('F j, Y', strtotime($item['created_at'])); ?></span>
                        <span>Status: <?php echo ucfirst($item['status']); ?></span>
                    </div>
                    <div class="feedback-content">
                        <?php echo nl2br(htmlspecialchars($item['message'])); ?>
                    </div>
                    <div class="feedback-reply">
                        <strong>Admin Reply:</strong>
                        <p><?php echo !empty($item['reply']) ? nl2br(htmlspecialchars($item['reply'])) : 'No reply yet'; ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>