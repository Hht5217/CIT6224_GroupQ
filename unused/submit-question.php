<?php
session_start();
require_once 'config/database.php';
include 'includes/timeout.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $question = trim($_POST['question']);
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

    if (empty($question)) {
        $error = "Please enter your question";
    } else {
        $sql = "INSERT INTO user_questions (user_id, question, status) VALUES (?, ?, 'pending')";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "is", $user_id, $question);
            if (mysqli_stmt_execute($stmt)) {
                $success = "Your question has been submitted successfully!";
                $question = '';
            } else {
                $error = "Error submitting question";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Submit Question - MMU Talent Showcase</title>
        <link rel="stylesheet" href="assets/css/style.css">
    </head>

    <body>
        <?php include 'includes/header-inc.php'; ?>
        <!-- <nav class="nav">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="talent-catalogue.php">Talent Catalogue</a></li>
                <li><a href="faq.php">FAQ</a></li>
                <li><a href="announcements.php">News & Announcements</a></li>
                <li><a href="feedback.php">Feedback</a></li>
                <?php /*if (isset($_SESSION['user_id'])): ?>
          <li><a href="profile.php">My Profile</a></li>
          <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
              <li><a href="admin/dashboard.php">Admin Dashboard</a></li>
          <?php endif; ?>
          <li><a href="logout.php">Logout</a></li>
      <?php else: ?>
          <li><a href="login.php">Login</a></li>
          <li><a href="register.php">Register</a></li>
      <?php endif; */ ?>
            </ul>
        </nav> -->
        <?php include 'includes/navbar.php'; ?>

        <div class="container">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <div class="card">
                <h2>Submit Your Question</h2>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="form-group">
                        <label>Your Question</label>
                        <textarea name="question" class="form-control" rows="4"
                            required><?php echo isset($question) ? htmlspecialchars($question) : ''; ?></textarea>
                    </div>
                    <div class="form-group">
                        <input type="submit" class="btn" value="Submit Question">
                    </div>
                </form>
            </div>
        </div>
    </body>

</html>