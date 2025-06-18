<?php
session_start();
require_once '../config/database.php';

// 检查是否是管理员
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("location: ../login.php");
    exit;
}

$error = '';
$success = '';

// 处理FAQ添加
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    if ($_POST['action'] == 'add') {
        $question = trim($_POST['question']);
        $answer = trim($_POST['answer']);

        if (empty($question) || empty($answer)) {
            $error = "Please fill in all fields";
        } else {
            $sql = "INSERT INTO faq (question, answer) VALUES (?, ?)";
            if ($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "ss", $question, $answer);
                if (mysqli_stmt_execute($stmt)) {
                    $success = "FAQ added successfully";
                } else {
                    $error = "Error adding FAQ";
                }
            }
        }
    }
    // 处理FAQ编辑
    elseif ($_POST['action'] == 'edit') {
        $id = $_POST['id'];
        $question = trim($_POST['question']);
        $answer = trim($_POST['answer']);

        if (empty($question) || empty($answer)) {
            $error = "Please fill in all fields";
        } else {
            $sql = "UPDATE faq SET question = ?, answer = ? WHERE id = ?";
            if ($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "ssi", $question, $answer, $id);
                if (mysqli_stmt_execute($stmt)) {
                    $success = "FAQ updated successfully";
                } else {
                    $error = "Error updating FAQ";
                }
            }
        }
    }
    // 处理FAQ删除
    elseif ($_POST['action'] == 'delete') {
        $id = $_POST['id'];

        $sql = "DELETE FROM faq WHERE id = ?";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $id);
            if (mysqli_stmt_execute($stmt)) {
                $success = "FAQ deleted successfully";
            } else {
                $error = "Error deleting FAQ";
            }
        }
    }
}

// 获取所有FAQ
$sql = "SELECT * FROM faq ORDER BY created_at DESC";
$faqs = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Manage FAQ - MMU Talent Showcase</title>
        <link rel="stylesheet" href="../assets/css/style.css">
    </head>

    <body>
        <header class="header">
            <h1>MMU Talent Showcase Portal - Manage FAQ</h1>
        </header>

        <?php include '../includes/admin-navbar.php'; ?>

        <div class="container">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <!-- 添加新FAQ -->
            <div class="card">
                <h3>Add New FAQ</h3>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label>Question</label>
                        <input type="text" name="question" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Answer</label>
                        <textarea name="answer" class="form-control" rows="4" required></textarea>
                    </div>
                    <div class="form-group">
                        <input type="submit" class="btn" value="Add FAQ">
                    </div>
                </form>
            </div>

            <!-- FAQ列表 -->
            <div class="card">
                <h3>FAQ List</h3>
                <div class="faq-list">
                    <?php while ($faq = mysqli_fetch_assoc($faqs)): ?>
                        <div class="faq-item">
                            <h4><?php echo htmlspecialchars($faq['question']); ?></h4>
                            <p><?php echo nl2br(htmlspecialchars($faq['answer'])); ?></p>
                            <div class="faq-actions">
                                <button
                                    onclick="editFAQ(<?php echo $faq['id']; ?>, '<?php echo htmlspecialchars(addslashes($faq['question'])); ?>', '<?php echo htmlspecialchars(addslashes($faq['answer'])); ?>')"
                                    class="btn">Edit</button>
                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post"
                                    style="display: inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $faq['id']; ?>">
                                    <input type="submit" class="btn btn-danger" value="Delete"
                                        onclick="return confirm('Are you sure you want to delete this FAQ?')">
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

        <!-- 编辑FAQ的模态框 -->
        <div id="editModal" class="modal" style="display: none;">
            <div class="modal-content">
                <h3>Edit FAQ</h3>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="form-group">
                        <label>Question</label>
                        <input type="text" name="question" id="edit_question" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Answer</label>
                        <textarea name="answer" id="edit_answer" class="form-control" rows="4" required></textarea>
                    </div>
                    <div class="form-group">
                        <input type="submit" class="btn" value="Update FAQ">
                        <button type="button" class="btn" onclick="closeModal()">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
        <?php include 'includes/footer-inc.php'; ?>

        <script>
            function editFAQ(id, question, answer) {
                document.getElementById('edit_id').value = id;
                document.getElementById('edit_question').value = question;
                document.getElementById('edit_answer').value = answer;
                document.getElementById('editModal').style.display = 'block';
            }

            function closeModal() {
                document.getElementById('editModal').style.display = 'none';
            }
        </script>
    </body>

</html>