<?php
session_start();
require_once '../config/database.php';
include '../includes/timeout.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
} elseif ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$error = '';
$success = '';

// Handle status update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'update_status') {
    $id = $_POST['id'];
    $status = $_POST['status'];

    // Store reply form state if provided
    $reply_form_id = isset($_POST['reply_form_id']) ? $_POST['reply_form_id'] : '';
    $reply_content = isset($_POST['reply_content']) ? $_POST['reply_content'] : '';
    if ($reply_form_id) {
        $_SESSION['reply_form_state'] = [
            'id' => $reply_form_id,
            'content' => $reply_content
        ];
    }

    $sql = "UPDATE feedback SET status = ? WHERE id = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "si", $status, $id);
        if (mysqli_stmt_execute($stmt)) {
            $success = "Status updated successfully";
        } else {
            $error = "Error updating status";
        }
        mysqli_stmt_close($stmt);
    }
}

// Handle reply submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'submit_reply') {
    $id = $_POST['id'];
    $reply = trim($_POST['reply']);

    if (empty($reply)) {
        $error = "Reply cannot be empty";
    } else {
        $sql = "UPDATE feedback SET reply = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "si", $reply, $id);
            if (mysqli_stmt_execute($stmt)) {
                $success = "Reply submitted successfully";
                // Clear reply form state after successful reply
                unset($_SESSION['reply_form_state']);
            } else {
                $error = "Error submitting reply";
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// Fetch all feedback
$sql = "SELECT f.*, u.username FROM feedback f LEFT JOIN users u ON f.user_id = u.id ORDER BY f.created_at DESC";
$feedback = mysqli_query($conn, $sql);

// Get reply form state
$reply_form_state = isset($_SESSION['reply_form_state']) ? $_SESSION['reply_form_state'] : null;
?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Manage Feedback - MMU Talent Showcase</title>
        <link rel="stylesheet" href="../assets/css/style.css">
    </head>

    <body>
        <header class="header">
            <h1>MMU Talent Showcase Portal - Manage Feedback</h1>
        </header>

        <?php include '../includes/admin-navbar.php'; ?>

        <div class="container">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <div class="card">
                <h3>Feedback List</h3>
                <div class="feedback-list">
                    <?php while ($item = mysqli_fetch_assoc($feedback)): ?>
                        <?php
                        // Check if this feedback's reply form should be open
                        $is_reply_form_open = $reply_form_state && $reply_form_state['id'] == $item['id'];
                        $reply_content = $is_reply_form_open ? htmlspecialchars($reply_form_state['content']) : '';
                        ?>
                        <div class="feedback-item">
                            <h4><?php echo htmlspecialchars($item['subject']); ?></h4>
                            <div class="feedback-meta">
                                <span>From:
                                    <?php echo $item['username'] ? htmlspecialchars($item['username']) : 'Anonymous'; ?></span>
                                <span>Posted on: <?php echo date('F j, Y', strtotime($item['created_at'])); ?></span>
                                <span>Status: <?php echo ucfirst($item['status']); ?></span>
                            </div>
                            <div class="feedback-content">
                                <?php echo nl2br(htmlspecialchars($item['message'])); ?>
                            </div>
                            <div class="feedback-reply">
                                <strong>Admin Reply: <button class="btn btn-secondary reply-btn"
                                        data-feedback-id="<?php echo $item['id']; ?>">Reply</button></strong>
                                <p><?php echo !empty($item['reply']) ? nl2br(htmlspecialchars($item['reply'])) : 'No reply yet'; ?>
                                </p>
                                <div class="reply-form" id="reply-form-<?php echo $item['id']; ?>"
                                    style="display: <?php echo $is_reply_form_open ? 'block' : 'none'; ?>;">
                                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post"
                                        onsubmit="storeReplyFormState(<?php echo $item['id']; ?>)">
                                        <input type="hidden" name="action" value="submit_reply">
                                        <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                        <div class="form-group">
                                            <label for="reply-<?php echo $item['id']; ?>"></label>
                                            <textarea name="reply" id="reply-<?php echo $item['id']; ?>" rows="4"
                                                class="form-control" required><?php echo $reply_content; ?></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Submit Reply</button>
                                        <button type="button" class="btn btn-secondary cancel-reply-btn"
                                            data-feedback-id="<?php echo $item['id']; ?>">Cancel</button>
                                    </form>
                                </div>
                            </div>
                            <div class="feedback-actions">
                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post"
                                    style="display: inline;">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                    <input type="hidden" name="reply_form_id" id="reply_form_id-<?php echo $item['id']; ?>">
                                    <input type="hidden" name="reply_content" id="reply_content-<?php echo $item['id']; ?>">
                                    <select name="status"
                                        onchange="updateReplyFormState(<?php echo $item['id']; ?>); this.form.submit()">
                                        <option value="pending" <?php echo $item['status'] == 'pending' ? 'selected' : ''; ?>>
                                            Pending</option>
                                        <option value="in_progress" <?php echo $item['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                        <option value="resolved" <?php echo $item['status'] == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                    </select>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

        <?php include '../includes/footer-inc.php'; ?>

        <script>
            // Toggle reply form visibility
            document.querySelectorAll('.reply-btn').forEach(button => {
                button.addEventListener('click', () => {
                    const feedbackId = button.getAttribute('data-feedback-id');
                    const replyForm = document.getElementById(`reply-form-${feedbackId}`);
                    replyForm.style.display = replyForm.style.display === 'none' ? 'block' : 'none';
                });
            });

            // Cancel reply form
            document.querySelectorAll('.cancel-reply-btn').forEach(button => {
                button.addEventListener('click', () => {
                    const feedbackId = button.getAttribute('data-feedback-id');
                    const replyForm = document.getElementById(`reply-form-${feedbackId}`);
                    replyForm.style.display = 'none';
                    replyForm.querySelector('textarea').value = '';
                });
            });

            // Update reply form state before status submission
            function updateReplyFormState(feedbackId) {
                const replyForm = document.getElementById(`reply-form-${feedbackId}`);
                const isOpen = replyForm.style.display === 'block';
                const textarea = replyForm.querySelector('textarea');
                document.getElementById(`reply_form_id-${feedbackId}`).value = isOpen ? feedbackId : '';
                document.getElementById(`reply_content-${feedbackId}`).value = isOpen ? textarea.value : '';
            }

            // Store reply form state before reply submission
            function storeReplyFormState(feedbackId) {
                sessionStorage.setItem('reply_form_id', feedbackId);
                const textarea = document.getElementById(`reply-${feedbackId}`);
                sessionStorage.setItem('reply_content', textarea.value);
            }
        </script>
    </body>

</html>