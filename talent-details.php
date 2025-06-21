<?php
session_start();
require_once 'config/database.php';
require_once 'includes/file-upload-delete.php';
include 'includes/timeout.php';
include_once 'includes/talent-categories.php';

// Get default max size from php.ini
$defaultMaxSize = ini_get('upload_max_filesize');
$maxSizeBytes = convertToBytes($defaultMaxSize);
$maxSizeMB = $maxSizeBytes / (1024 * 1024);

// Check if ID is provided
if (!isset($_GET['id'])) {
    header("Location: talent-catalogue.php");
    exit();
}

$talent_id = (int) $_GET['id'];

// Get talent info
$sql = "SELECT t.*, u.full_name, u.username, p.profile_picture
        FROM talents t
        JOIN users u ON t.user_id = u.id
        LEFT JOIN profiles p ON u.id = p.user_id
        WHERE t.id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $talent_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$talent = mysqli_fetch_assoc($result);

if (!$talent) {
    header("Location: talent-catalogue.php");
    exit();
}

// Get resource info for downloadable status
$sql = "SELECT is_downloadable FROM resources WHERE talent_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $talent_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$resource = mysqli_fetch_assoc($result);
$resource_is_downloadable = $resource['is_downloadable'] ?? 0;

// Handle downloadable status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_downloadable') {
    if (!isset($_SESSION['user_id']) || $talent['user_id'] != $_SESSION['user_id']) {
        header("Location: login.php");
        exit();
    }
    $is_downloadable = isset($_POST['is_downloadable']) ? 1 : 0;
    $stmt = $conn->prepare("UPDATE resources SET is_downloadable = ? WHERE talent_id = ? AND user_id = ?");
    $stmt->bind_param("iii", $is_downloadable, $talent_id, $_SESSION['user_id']);
    if ($stmt->execute()) {
        $success = "Downloadable status updated successfully";
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    } else {
        $error = "Error updating downloadable status";
    }
    mysqli_stmt_close($stmt);
}

// Handle talent deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete-talent') {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    // Verify talent belongs to user
    $stmt = $conn->prepare("SELECT user_id, media_path FROM talents WHERE id = ?");
    $stmt->bind_param("i", $talent_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $talent_check = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($talent_check && $talent_check['user_id'] == $_SESSION['user_id']) {
        // Delete file only once (assume talents.media_path and resources.file_path are the same)
        if (!empty($talent_check['media_path']) && $talent_check['media_path'] !== 'deleted' && file_exists($talent_check['media_path'])) {
            if (!unlink($talent_check['media_path'])) {
                $error = "Failed to delete associated file.";
            }
        }

        // Delete related resources
        $stmt = $conn->prepare("DELETE FROM resources WHERE talent_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $talent_id, $_SESSION['user_id']);
        if (!mysqli_stmt_execute($stmt)) {
            $error = "Error deleting related resources: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);

        // Delete favorites
        $stmt = $conn->prepare("DELETE FROM favorites WHERE talent_id = ?");
        $stmt->bind_param("i", $talent_id);
        if (!mysqli_stmt_execute($stmt)) {
            $error = $error ?? "Error deleting favorites: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);

        // Delete comments
        $stmt = $conn->prepare("DELETE FROM comments WHERE talent_id = ?");
        $stmt->bind_param("i", $talent_id);
        if (!mysqli_stmt_execute($stmt)) {
            $error = $error ?? "Error deleting comments: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);

        // Delete talent
        $stmt = $conn->prepare("DELETE FROM talents WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $talent_id, $_SESSION['user_id']);
        if (!mysqli_stmt_execute($stmt)) {
            $error = $error ?? "Error deleting talent: " . mysqli_error($conn);
        } else {
            $success = "Talent deleted successfully";
            header("Location: user-dashboard.php?page=talents");
            exit();
        }
        mysqli_stmt_close($stmt);
    } else {
        $error = "You do not have permission to delete this talent or it does not exist.";
    }
}

// Handle file replacement
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'replace_file') {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $stmt = $conn->prepare("SELECT user_id FROM talents WHERE id = ?");
    $stmt->bind_param("i", $talent_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $talent_check = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($talent_check && $talent_check['user_id'] == $_SESSION['user_id']) {
        $is_downloadable = isset($_POST['is_downloadable']) ? 1 : $resource_is_downloadable; // Retain current downloadable status unless changed
        $file = $_FILES['file'];

        $allowed_types = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'video/mp4',
            'video/webm',
            'video/ogg',
            'audio/mp3',
            'audio/wav',
            'audio/ogg',
            'text/plain',
            'text/html',
            'text/css',
            'text/csv',
            'application/javascript',
            'application/pdf',
            'application/zip',
            'application/json'
        ];

        $result = uploadFile($file, 'uploads/talents/', $allowed_types, $maxSizeMB);
        if ($result['error']) {
            $error = $result['error'];
        } else {
            $stmt = $conn->prepare("UPDATE talents SET media_path = ? WHERE id = ?");
            $stmt->bind_param("si", $result['filepath'], $talent_id);
            if ($stmt->execute()) {
                $stmt = $conn->prepare("UPDATE resources SET file_name = ?, file_path = ?, file_type = ?, file_size = ?, is_downloadable = ? WHERE talent_id = ?");
                $stmt->bind_param("sssiii", $result['filename'], $result['filepath'], $result['file_type'], $result['file_size'], $is_downloadable, $talent_id);
                if ($stmt->execute()) {
                    $success = "File replaced successfully";
                } else {
                    $error = "Error updating resource";
                    if (file_exists($result['filepath'])) {
                        unlink($result['filepath']);
                    }
                }
                mysqli_stmt_close($stmt);
            } else {
                $error = "Error updating talent";
                if (file_exists($result['filepath'])) {
                    unlink($result['filepath']);
                }
            }
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit();
        }
    }
}

// Increment view count
mysqli_query($conn, "UPDATE talents SET view_count = view_count + 1 WHERE id = $talent_id");

// Get favorite count
$sql = "SELECT COUNT(*) as favorites_count FROM favorites WHERE talent_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $talent_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$favorites_count = mysqli_fetch_assoc($result)['favorites_count'];
mysqli_stmt_close($stmt);

// Check if user has favorited this talent
$is_favorited = false;
if (isset($_SESSION['user_id'])) {
    $sql = "SELECT id FROM favorites WHERE user_id = ? AND talent_id = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "ii", $_SESSION['user_id'], $talent_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $is_favorited = mysqli_stmt_num_rows($stmt) > 0;
        mysqli_stmt_close($stmt);
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
        $sql = "INSERT INTO comments (user_id, talent_id, comment, parent_id) VALUES (?, ?, ?, ?)";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "iiss", $_SESSION['user_id'], $talent_id, $comment, $parent_id);
            if (mysqli_stmt_execute($stmt)) {
                header("Location: " . $_SERVER['REQUEST_URI']);
                exit();
            } else {
                $error = "Error posting comment: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// Get comments with replies
$sql = "SELECT c.*, u.username, u.full_name, p.profile_picture,
        (SELECT COUNT(*) FROM comments r WHERE r.parent_id = c.id) as reply_count
        FROM comments c 
        JOIN users u ON c.user_id = u.id 
        LEFT JOIN profiles p ON u.id = p.user_id
        WHERE c.talent_id = ? AND c.parent_id IS NULL
        ORDER BY c.created_at DESC";
$comments = [];
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $talent_id);
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
            $row['reply_count'] = count($replies);
            mysqli_stmt_close($reply_stmt);
        }
        $comments[] = $row;
    }
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($talent['title']); ?> - Talent Details</title>
        <link rel="stylesheet" href="assets/css/style.css">
        <link rel="stylesheet" href="assets/css/talent-details.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    </head>

    <body>
        <?php include 'includes/header-inc.php'; ?>
        <?php include 'includes/navbar.php'; ?>

        <div class="container">
            <?php if (isset($error)): ?>
                <div class="alert alert-warning"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <div class="talent-profile-container">
                <!-- Profile Header -->
                <div class="profile-header">
                    <div class="profile-info">
                        <h1><?php echo htmlspecialchars($talent['title']); ?></h1>
                        <p class="talent-category">
                            <i class="fas fa-star"></i>
                            <?php echo isset($talent_categories[$talent['category']]) ? htmlspecialchars($talent_categories[$talent['category']]) : 'Not specified'; ?>
                        </p>
                        <div class="profile-stats">
                            <span><i class="fas fa-eye"></i> <?php echo $talent['view_count']; ?> Views</span>
                            <span><i class="fas fa-heart"></i> <?php echo $favorites_count; ?> Favorites</span>
                        </div>
                        <p>By: <?php echo htmlspecialchars($talent['full_name']); ?></p>
                        <a href="view-profile.php?id=<?php echo $talent['user_id']; ?>" class="btn btn-primary">
                            <i class="fas fa-user"></i> View Profile
                        </a>
                        <?php if (!empty($_SESSION['user_id'])): ?>
                            <button
                                class="btn <?php echo $is_favorited ? 'btn-secondary' : 'btn-primary'; ?> toggle-favorite"
                                data-talent-id="<?php echo $talent_id; ?>"
                                data-action="<?php echo $is_favorited ? 'remove' : 'add'; ?>">
                                <i class="fas <?php echo $is_favorited ? 'fa-heart-broken' : 'fa-heart'; ?>"></i>
                                <?php echo $is_favorited ? 'Remove from Favorites' : 'Add to Favorites'; ?>
                            </button>
                            <?php if ($talent['user_id'] == $_SESSION['user_id']): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="delete-talent">
                                    <button type="submit" class="btn btn-danger"
                                        onclick="return confirm('Are you sure you want to delete this talent?');">
                                        <i class="fas fa-trash"></i> Delete Talent
                                    </button>
                                </form>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Profile Content -->
                <div class="profile-content">
                    <!-- File Preview Section -->
                    <div class="profile-section">
                        <h2><i class="fas fa-file"></i> File</h2>
                        <div class="section-content">
                            <?php if ($talent['media_path'] === 'deleted'): ?>
                                <p class="alert alert-warning">The original file has been deleted.</p>
                                <?php if (!empty($_SESSION['user_id']) && $talent['user_id'] == $_SESSION['user_id']): ?>
                                    <button class="btn btn-primary" onclick="showUploadForm()">
                                        <i class="fas fa-upload"></i> Upload New File
                                    </button>
                                    <div id="uploadForm" class="upload-form" style="display: none;">
                                        <form action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" method="post"
                                            enctype="multipart/form-data" onsubmit="return validateFileSize();">
                                            <input type="hidden" name="action" value="replace_file">
                                            <div class="form-group">
                                                <label for="file">New File</label>
                                                <input type="file" id="file" name="file" class="form-control"
                                                    data-max-size="<?php echo $maxSizeMB; ?>" required>
                                                <small class="form-text">Supported types: JPEG, PNG, GIF, MP4, WEBM, OGG, MP3,
                                                    WAV, TXT, HTML, CSS, JS, PDF, ZIP, CSV, JSON. Max size:
                                                    <?php echo number_format($maxSizeMB, 2); ?>MB</small>
                                            </div>
                                            <div class="form-actions">
                                                <button type="submit" class="btn btn-primary">Upload File</button>
                                                <button type="button" class="btn btn-secondary"
                                                    onclick="hideUploadForm()">Cancel</button>
                                            </div>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <?php
                                $file = $talent['media_path'];
                                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                                    echo '<img src="' . htmlspecialchars($file) . '" alt="Talent Image" style="max-width:100%;max-height:350px;">';
                                } elseif (in_array($ext, ['mp4', 'webm', 'ogg'])) {
                                    echo '<video controls style="max-width:100%;max-height:350px;"><source src="' . htmlspecialchars($file) . '"></video>';
                                } elseif (in_array($ext, ['mp3', 'wav', 'ogg'])) {
                                    echo '<audio controls style="width:100%;"><source src="' . htmlspecialchars($file) . '"></audio>';
                                } elseif (in_array($ext, ['txt', 'py', 'js', 'html', 'css', 'csv'])) {
                                    echo '<pre style="background:#f8f9fa;padding:1rem;border-radius:4px;max-height:350px;overflow:auto;">' . htmlspecialchars(file_get_contents($file)) . '</pre>';
                                } else {
                                    echo '<span>No preview available.</span>';
                                }
                                ?>
                                <div>
                                    <?php if ($resource_is_downloadable && !empty($talent['media_path'])): ?>
                                        <a href="<?php echo htmlspecialchars($talent['media_path']); ?>" download
                                            class="btn btn-success" style="margin-top:1rem;display:inline-block;">
                                            <i class="fas fa-download"></i> Download File
                                        </a>
                                    <?php endif; ?>
                                    <?php if (!empty($_SESSION['user_id']) && $talent['user_id'] == $_SESSION['user_id']): ?>
                                        <button class="btn btn-primary" onclick="showUploadForm()">
                                            <i class="fas fa-upload"></i> Replace File
                                        </button>
                                        <div id="uploadForm" class="upload-form" style="display: none;">
                                            <form action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>"
                                                method="post" enctype="multipart/form-data"
                                                onsubmit="return validateFileSize();">
                                                <input type="hidden" name="action" value="replace_file">
                                                <div class="form-group">
                                                    <label for="file">New File</label>
                                                    <input type="file" id="file" name="file" class="form-control"
                                                        data-max-size="<?php echo $maxSizeMB; ?>" required>
                                                    <small class="form-text">Supported types: JPEG, PNG, GIF, MP4, WEBM, OGG,
                                                        MP3,
                                                        WAV, TXT, HTML, CSS, JS, PDF, ZIP, CSV, JSON. Max size:
                                                        <?php echo number_format($maxSizeMB, 2); ?>MB</small>
                                                </div>
                                                <div class="form-actions">
                                                    <button type="submit" class="btn btn-primary">Upload File</button>
                                                    <button type="button" class="btn btn-secondary"
                                                        onclick="hideUploadForm()">Cancel</button>
                                                </div>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($_SESSION['user_id']) && $talent['user_id'] == $_SESSION['user_id']): ?>
                                <form method="POST" class="downloadable-form">
                                    <input type="hidden" name="action" value="update_downloadable">
                                    <label>
                                        <input type="checkbox" name="is_downloadable" value="1" <?php echo $resource_is_downloadable ? 'checked' : ''; ?>> Make file downloadable
                                    </label>
                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Description Section -->
                    <div class="profile-section">
                        <h2><i class="fas fa-info-circle"></i> Description</h2>
                        <div class="section-content">
                            <p><?php echo nl2br(htmlspecialchars($talent['description'] ?? 'No description available.')); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Comments Section -->
                    <div class="comments-section" id="comments-section">
                        <h2><i class="fas fa-comments"></i> Comments (<?php echo count($comments); ?>)</h2>
                        <div class="section-content">
                            <?php if (!empty($_SESSION['user_id'])): ?>
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
                                                        alt="<?php echo htmlspecialchars($comment['username']); ?>"
                                                        class="avatar">
                                                    <div>
                                                        <h4><?php echo htmlspecialchars($comment['full_name'] ?: $comment['username']); ?>
                                                        </h4>
                                                        <span
                                                            class="timestamp"><?php echo date('M d, Y H:i', strtotime($comment['created_at'])); ?></span>
                                                    </div>
                                                </div>
                                                <?php if (!empty($_SESSION['user_id'])): ?>
                                                    <div class="comment-actions">
                                                        <button class="reply-btn"
                                                            onclick="showReplyForm(<?php echo $comment['id']; ?>)">
                                                            <i class="fas fa-reply"></i> Reply
                                                        </button>
                                                        <?php if ($_SESSION['user_id'] == $comment['user_id']): ?>
                                                            <form action="delete-comment.php" method="POST" style="display:inline;">
                                                                <input type="hidden" name="comment_id"
                                                                    value="<?php echo $comment['id']; ?>">
                                                                <button type="submit" class="delete-btn">
                                                                    <i class="fas fa-trash"></i> Delete
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="comment-content">
                                                <?php echo nl2br(htmlspecialchars($comment['comment'])); ?>
                                            </div>
                                            <?php if ($comment['reply_count'] > 0): ?>
                                                <button class="view-replies-btn"
                                                    onclick="toggleReplies(<?php echo $comment['id']; ?>)">
                                                    <i class="fas fa-comments"></i> View Replies
                                                    (<?php echo $comment['reply_count']; ?>)
                                                </button>
                                            <?php endif; ?>
                                            <div id="reply-form-<?php echo $comment['id']; ?>" class="reply-form"
                                                style="display: none;">
                                                <form method="POST">
                                                    <input type="hidden" name="parent_id" value="<?php echo $comment['id']; ?>">
                                                    <div class="form-group">
                                                        <textarea name="comment" placeholder="Write a reply..."
                                                            required></textarea>
                                                    </div>
                                                    <div class="form-actions">
                                                        <button type="submit" class="btn btn-primary">Post Reply</button>
                                                        <button type="button" class="btn btn-secondary cancel-reply-btn"
                                                            onclick="hideReplyForm(<?php echo $comment['id']; ?>)">Cancel</button>
                                                    </div>
                                                </form>
                                            </div>
                                            <div id="replies-<?php echo $comment['id']; ?>" class="replies"
                                                style="display: none;">
                                                <?php foreach ($comment['replies'] as $reply): ?>
                                                    <div class="reply" id="reply-<?php echo $reply['id']; ?>">
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
                                                            <?php if (!empty($_SESSION['user_id']) && $_SESSION['user_id'] == $reply['user_id']): ?>
                                                                <form action="delete-comment.php" method="POST" style="display:inline;">
                                                                    <input type="hidden" name="comment_id"
                                                                        value="<?php echo $reply['id']; ?>">
                                                                    <button type="submit" class="delete-btn">
                                                                        <i class="fas fa-trash"></i> Delete
                                                                    </button>
                                                                </form>
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

            <?php include 'includes/footer-inc.php'; ?>

            <script src="assets/js/validateFileSize.js"></script>
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
                        button.innerHTML = '<i class="fas fa-comments"></i> Hide Replies (' + repliesDiv.querySelectorAll('.reply').length + ')';
                    } else {
                        repliesDiv.style.display = 'none';
                        button.innerHTML = '<i class="fas fa-comments"></i> View Replies (' + repliesDiv.querySelectorAll('.reply').length + ')';
                    }
                }

                function showUploadForm() {
                    document.getElementById('uploadForm').style.display = 'block';
                }

                function hideUploadForm() {
                    document.getElementById('uploadForm').style.display = 'none';
                }

                // Auto-show replies if hash starts with #reply-
                window.addEventListener('load', () => {
                    if (window.location.hash.startsWith('#reply-')) {
                        const replyId = window.location.hash.replace('#reply-', '');
                        const replyElement = document.getElementById('reply-' + replyId);
                        if (replyElement) {
                            const repliesDiv = replyElement.closest('.replies');
                            if (repliesDiv && repliesDiv.style.display === 'none') {
                                repliesDiv.style.display = 'block';
                                const button = repliesDiv.previousElementSibling;
                                if (button && button.classList.contains('view-replies-btn')) {
                                    button.innerHTML = '<i class="fas fa-comments"></i> Hide Replies (' + repliesDiv.querySelectorAll('.reply').length + ')';
                                }
                            }
                        }
                    }
                });

                // Toggle favorite
                document.querySelector('.toggle-favorite')?.addEventListener('click', function () {
                    const talentId = this.dataset.talentId;
                    const action = this.dataset.action;
                    fetch('toggle-favorite.php', {
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

                window.addEventListener('load', () => {
                    if (window.location.hash.startsWith('#reply-')) {
                        const replyId = window.location.hash.replace('#reply-', '');
                        const replyElement = document.getElementById('reply-' + replyId);
                        if (replyElement) {
                            const repliesDiv = replyElement.closest('.replies');
                            if (repliesDiv && repliesDiv.style.display === 'none') {
                                repliesDiv.style.display = 'block';
                                const button = repliesDiv.previousElementSibling;
                                if (button && button.classList.contains('view-replies-btn')) {
                                    button.innerHTML = '<i class="fas fa-comments"></i> Hide Replies (' + repliesDiv.querySelectorAll('.reply').length + ')';
                                }
                            }
                        }
                    }
                });
            </script>
    </body>

</html>