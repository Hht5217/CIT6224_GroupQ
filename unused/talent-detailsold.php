<?php
session_start();
require_once 'config/database.php';

// Check if ID is provided
if (!isset($_GET['id'])) {
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

// Get user's talents
$sql = "SELECT * FROM talents WHERE user_id = ? ORDER BY created_at DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$talents = mysqli_stmt_get_result($stmt);

// Get user's resources
$sql = "SELECT * FROM resources WHERE user_id = ? ORDER BY created_at DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$resources = mysqli_stmt_get_result($stmt);

// Check if user has favorited this talent
$is_favorited = false;
if (isset($_SESSION['user_id'])) {
    $sql = "SELECT id FROM favorites WHERE user_id = ? AND talent_id = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "ii", $_SESSION['user_id'], $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $is_favorited = mysqli_stmt_num_rows($stmt) > 0;
    }
}

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $comment = trim($_POST['comment']);
    $parent_id = !empty($_POST['parent_id']) ? (int) $_POST['parent_id'] : null;

    if (!empty($comment)) {
        // Insert the comment
        $sql = "INSERT INTO comments (user_id, commented_user_id, comment, parent_id) VALUES (?, ?, ?, ?)";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "iiss", $_SESSION['user_id'], $user_id, $comment, $parent_id);
            if (mysqli_stmt_execute($stmt)) {

                header("Location: " . $_SERVER['REQUEST_URI']);
                exit();
            } else {
                $error = "Error posting comment: " . mysqli_error($conn);
            }
        }
    }
}

// Get comments with replies
$sql = "SELECT c.*, u.username, u.full_name, p.profile_picture,
        (SELECT COUNT(*) FROM comments r WHERE r.parent_id = c.id) as reply_count
        FROM comments c 
        JOIN users u ON c.user_id = u.id 
        LEFT JOIN profiles p ON u.id = p.user_id
        WHERE c.commented_user_id = ? AND c.parent_id IS NULL
        ORDER BY c.created_at DESC";
$comments = [];
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        // Get replies for each comment
        $reply_sql = "SELECT r.*, u.username, u.full_name, p.profile_picture
                     FROM comments r 
                     JOIN users u ON r.user_id = u.id 
                     LEFT JOIN profiles p ON u.id = p.user_id
                     WHERE r.parent_id = ? 
                     ORDER BY r.created_at ASC";
        if ($reply_stmt = mysqli_prepare($conn, $reply_sql)) {
            mysqli_stmt_bind_param($reply_stmt, "i", $row['id']);
            mysqli_stmt_execute($reply_stmt);
            $reply_result = mysqli_stmt_get_result($reply_stmt);
            $replies = [];
            while ($reply = mysqli_fetch_assoc($reply_result)) {
                $replies[] = $reply;
            }
            $row['replies'] = $replies;
            $row['reply_count'] = count($replies); // 确保reply_count存在
        }
        $comments[] = $row;
    }
}

// Get notifications
$sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 10";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$notifications = mysqli_stmt_get_result($stmt);

// Debug information
echo "<!-- Debug Info: -->";
echo "<!-- User ID: " . $user_id . " -->";
echo "<!-- Comments count: " . count($comments) . " -->";
?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($user['full_name']); ?> - Talent Details</title>
        <link rel="stylesheet" href="assets/css/style.css">
        <link rel="stylesheet" href="assets/css/talent-details.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    </head>

    <body>
        <header class="header">
            <h1>MMU Talent Showcase Portal</h1>
        </header>

        <?php include 'includes/navbar.php'; ?>

        <div class="container">
            <div class="talent-profile-container">
                <!-- Profile Header -->
                <div class="profile-header">
                    <div class="profile-image-container">
                        <img src="<?php echo !empty($user['profile_picture']) ? htmlspecialchars($user['profile_picture']) : 'assets/images/default-avatar.png'; ?>"
                            alt="<?php echo htmlspecialchars($user['full_name']); ?>" class="profile-image">
                    </div>
                    <div class="profile-info">
                        <h1><?php echo htmlspecialchars($user['full_name']); ?></h1>
                        <p class="talent-category">
                            <i class="fas fa-star"></i>
                            <?php echo htmlspecialchars($user['talent_category'] ?? 'Not specified'); ?>
                        </p>
                        <div class="profile-stats">
                            <span><i class="fas fa-eye"></i> <?php echo $stats['views'] ?? 0; ?> Views</span>
                            <span><i class="fas fa-heart"></i> <?php echo $stats['favorites'] ?? 0; ?> Favorites</span>
                        </div>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <button
                                class="btn <?php echo $is_favorited ? 'btn-secondary' : 'btn-primary'; ?> toggle-favorite"
                                data-talent-id="<?php echo $user_id; ?>"
                                data-action="<?php echo $is_favorited ? 'remove' : 'add'; ?>">
                                <i class="fas <?php echo $is_favorited ? 'fa-heart-broken' : 'fa-heart'; ?>"></i>
                                <?php echo $is_favorited ? 'Remove from Favorites' : 'Add to Favorites'; ?>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Profile Content -->
                <div class="profile-content">
                    <!-- About Section -->
                    <div class="profile-section">
                        <h2><i class="fas fa-user"></i> About</h2>
                        <div class="section-content">
                            <p><?php echo nl2br(htmlspecialchars($user['bio'] ?? 'No bio available.')); ?></p>
                        </div>
                    </div>

                    <!-- Talents Section -->
                    <?php if (!empty($talents)): ?>
                        <div class="profile-section">
                            <h2><i class="fas fa-star"></i> Talents</h2>
                            <div class="talents-grid">
                                <?php while ($row = mysqli_fetch_assoc($talents)): ?>
                                    <div class="talent-card">
                                        <?php if (!empty($row['image_url'])): ?>
                                            <img src="<?php echo htmlspecialchars($row['image_url']); ?>"
                                                alt="<?php echo htmlspecialchars($row['title']); ?>">
                                        <?php endif; ?>
                                        <div class="talent-info">
                                            <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                                            <p><?php echo htmlspecialchars($row['description']); ?></p>
                                            <span
                                                class="talent-category"><?php echo htmlspecialchars($row['category']); ?></span>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Resources Section -->
                    <?php if (!empty($resources)): ?>
                        <div class="profile-section">
                            <h2><i class="fas fa-file-alt"></i> Resources</h2>
                            <div class="resources-grid">
                                <?php while ($row = mysqli_fetch_assoc($resources)): ?>
                                    <div class="resource-card">
                                        <div class="resource-icon">
                                            <i class="fas fa-file"></i>
                                        </div>
                                        <div class="resource-info">
                                            <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                                            <p><?php echo htmlspecialchars($row['description']); ?></p>
                                            <div class="resource-meta">
                                                <span><i class="fas fa-download"></i>
                                                    <?php echo $row['download_count']; ?></span>
                                                <span><i class="fas fa-calendar"></i>
                                                    <?php echo date('M d, Y', strtotime($row['created_at'])); ?></span>
                                            </div>
                                            <a href="download_resource.php?id=<?php echo $row['id']; ?>"
                                                class="btn btn-primary">
                                                <i class="fas fa-download"></i> Download
                                            </a>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Comments Section -->
                    <div class="comments-section">
                        <h3>Comments (<?php echo count($comments); ?>)</h3>

                        <?php if (isset($_SESSION['user_id'])): ?>
                            <form method="POST" class="comment-form">
                                <div class="form-group">
                                    <textarea name="comment" placeholder="Write a comment..." required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Post Comment</button>
                            </form>
                        <?php else: ?>
                            <div class="login-prompt">
                                <p>Please <a href="login.php">login</a> to leave a comment.</p>
                            </div>
                        <?php endif; ?>

                        <div class="comments-list">
                            <?php if (empty($comments)): ?>
                                <div class="no-comments">
                                    <i class="fas fa-comments"></i>
                                    <p>No comments yet. Be the first to comment!</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($comments as $comment): ?>
                                    <div class="comment" id="comment-<?php echo $comment['id']; ?>">
                                        <div class="comment-header">
                                            <div class="user-info">
                                                <img src="<?php echo !empty($comment['profile_picture']) ? $comment['profile_picture'] : 'assets/images/default-avatar.png'; ?>"
                                                    alt="<?php echo htmlspecialchars($comment['username']); ?>" class="avatar">
                                                <div>
                                                    <h4><?php echo htmlspecialchars($comment['full_name'] ?: $comment['username']); ?>
                                                    </h4>
                                                    <span
                                                        class="timestamp"><?php echo date('M d, Y H:i', strtotime($comment['created_at'])); ?></span>
                                                </div>
                                            </div>
                                            <?php if (isset($_SESSION['user_id']) && ($_SESSION['user_id'] == $comment['user_id'] || $_SESSION['user_id'] == $user_id)): ?>
                                                <div class="comment-actions">
                                                    <button class="reply-btn"
                                                        onclick="showReplyForm(<?php echo $comment['id']; ?>)">
                                                        <i class="fas fa-reply"></i> Reply
                                                    </button>
                                                    <?php if ($_SESSION['user_id'] == $comment['user_id']): ?>
                                                        <button class="delete-btn"
                                                            onclick="deleteComment(<?php echo $comment['id']; ?>)">
                                                            <i class="fas fa-trash"></i> Delete
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="comment-content">
                                            <?php echo nl2br(htmlspecialchars($comment['comment'])); ?>
                                        </div>

                                        <?php if ($comment['reply_count'] > 0): ?>
                                            <button class="view-replies-btn" onclick="toggleReplies(<?php echo $comment['id']; ?>)">
                                                <i class="fas fa-comments"></i> View Replies
                                                (<?php echo $comment['reply_count']; ?>)
                                            </button>
                                        <?php endif; ?>

                                        <div id="reply-form-<?php echo $comment['id']; ?>" class="reply-form"
                                            style="display: none;">
                                            <form method="POST">
                                                <input type="hidden" name="parent_id" value="<?php echo $comment['id']; ?>">
                                                <div class="form-group">
                                                    <textarea name="comment" placeholder="Write a reply..." required></textarea>
                                                </div>
                                                <div class="form-actions">
                                                    <button type="submit" class="btn btn-primary">Post Reply</button>
                                                    <button type="button" class="btn btn-secondary"
                                                        onclick="hideReplyForm(<?php echo $comment['id']; ?>)">Cancel</button>
                                                </div>
                                            </form>
                                        </div>

                                        <div id="replies-<?php echo $comment['id']; ?>" class="replies" style="display: none;">
                                            <?php foreach ($comment['replies'] as $reply): ?>
                                                <div class="reply">
                                                    <div class="reply-header">
                                                        <div class="user-info">
                                                            <img src="<?php echo !empty($reply['profile_picture']) ? $reply['profile_picture'] : 'assets/images/default-avatar.png'; ?>"
                                                                alt="<?php echo htmlspecialchars($reply['username']); ?>"
                                                                class="avatar">
                                                            <div>
                                                                <h4><?php echo htmlspecialchars($reply['full_name'] ?: $reply['username']); ?>
                                                                </h4>
                                                                <span
                                                                    class="timestamp"><?php echo date('M d, Y H:i', strtotime($reply['created_at'])); ?></span>
                                                            </div>
                                                        </div>
                                                        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $reply['user_id']): ?>
                                                            <button class="delete-btn"
                                                                onclick="deleteComment(<?php echo $reply['id']; ?>)">
                                                                <i class="fas fa-trash"></i> Delete
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="reply-content">
                                                        <?php echo nl2br(htmlspecialchars($reply['comment'])); ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <footer class="footer">
            <div class="container">
                <p>&copy; <?php echo date('Y'); ?> MMU Talent Showcase Portal. All rights reserved.</p>
            </div>
        </footer>

        <script>
            function showReplyForm(commentId) {
                document.getElementById('reply-form-' + commentId).style.display = 'block';
            }

            function hideReplyForm(commentId) {
                document.getElementById('reply-form-' + commentId).style.display = 'none';
            }

            function toggleReplies(commentId) {
                const repliesDiv = document.getElementById('replies-' + commentId);
                const button = event.target;

                if (repliesDiv.style.display === 'none') {
                    repliesDiv.style.display = 'block';
                    button.innerHTML = '<i class="fas fa-comments"></i> Hide Replies';
                } else {
                    repliesDiv.style.display = 'none';
                    button.innerHTML = '<i class="fas fa-comments"></i> View Replies';
                }
            }

            function deleteComment(commentId) {
                if (confirm('Are you sure you want to delete this comment?')) {
                    fetch('delete_comment.php', {
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

            // Toggle favorite
            document.querySelector('.toggle-favorite')?.addEventListener('click', function () {
                const talentId = this.dataset.talentId;
                const action = this.dataset.action;

                fetch('toggle_favorite.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `talent_id=${talentId}&action=${action}`
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert(data.message);
                        }
                    });
            });
        </script>
    </body>

</html>