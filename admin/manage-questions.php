<?php
session_start();
require_once '../config/database.php';
include '../includes/timeout.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("location: ../login.php");
    exit;
}

$error = '';
$success = '';

// Handle answer submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'answer') {
    $id = $_POST['id'];
    $answer = trim($_POST['answer']);

    if (empty($answer)) {
        $error = "Please provide an answer";
    } else {
        $sql = "UPDATE user_questions SET answer = ?, status = 'answered' WHERE id = ?";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "si", $answer, $id);
            if (mysqli_stmt_execute($stmt)) {
                $success = "Answer submitted successfully";
            } else {
                $error = "Error submitting answer";
            }
        }
    }
}

// Handle question rejection
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'reject') {
    $id = $_POST['id'];

    $sql = "UPDATE user_questions SET status = 'rejected' WHERE id = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        if (mysqli_stmt_execute($stmt)) {
            $success = "Question rejected successfully";
        } else {
            $error = "Error rejecting question";
        }
    }
}

// Fetch all questions
$sql = "SELECT q.*, u.username FROM user_questions q LEFT JOIN users u ON q.user_id = u.id ORDER BY q.created_at DESC";
$questions = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Manage Questions - MMU Talent Showcase</title>
        <link rel="stylesheet" href="../assets/css/style.css">
    </head>

    <body>
        <header class="header">
            <h1>MMU Talent Showcase Portal - Manage Questions</h1>
        </header>

        <?php include '../includes/admin-navbar.php'; ?>

        <div class="container">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <div class="card">
                <h3>User Questions</h3>
                <div class="question-list">
                    <?php while ($question = mysqli_fetch_assoc($questions)): ?>
                        <div class="question-item">
                            <h4><?php echo htmlspecialchars($question['question']); ?></h4>
                            <div class="question-meta">
                                <span>From:
                                    <?php echo $question['username'] ? htmlspecialchars($question['username']) : 'Anonymous'; ?></span>
                                <span>Posted on: <?php echo date('F j, Y', strtotime($question['created_at'])); ?></span>
                                <span>Status: <?php echo ucfirst($question['status']); ?></span>
                            </div>
                            <?php if ($question['answer']): ?>
                                <div class="question-answer">
                                    <strong>Answer:</strong>
                                    <?php echo nl2br(htmlspecialchars($question['answer'])); ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($question['status'] == 'pending'): ?>
                                <div class="question-actions">
                                    <button onclick="showAnswerForm(<?php echo $question['id']; ?>)" class="btn">Answer</button>
                                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post"
                                        style="display: inline;">
                                        <input type="hidden" name="action" value="reject">
                                        <input type="hidden" name="id" value="<?php echo $question['id']; ?>">
                                        <input type="submit" class="btn btn-danger" value="Reject"
                                            onclick="return confirm('Are you sure you want to reject this question?')">
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

        <!-- Answer Form Modal -->
        <div id="answerModal" class="modal" style="display: none;">
            <div class="modal-content">
                <h3>Answer Question</h3>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <input type="hidden" name="action" value="answer">
                    <input type="hidden" name="id" id="question_id">
                    <div class="form-group">
                        <label>Your Answer</label>
                        <textarea name="answer" class="form-control" rows="4" required></textarea>
                    </div>
                    <div class="form-group">
                        <input type="submit" class="btn" value="Submit Answer">
                        <button type="button" class="btn" onclick="closeModal()">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <?php include '../includes/footer-inc.php'; ?>

        <script>
            function showAnswerForm(id) {
                document.getElementById('question_id').value = id;
                document.getElementById('answerModal').style.display = 'block';
            }

            function closeModal() {
                document.getElementById('answerModal').style.display = 'none';
            }
        </script>
    </body>

</html>