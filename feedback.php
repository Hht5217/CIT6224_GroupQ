<?php
session_start();
require_once 'config/database.php';
include 'includes/timeout.php';
include 'includes/login-required.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    $user_id = $_SESSION['user_id'];

    if (empty($subject) || empty($message)) {
        $error = "Please fill in all fields";
    } else {
        $sql = "INSERT INTO feedback (user_id, subject, message) VALUES (?, ?, ?)";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "iss", $user_id, $subject, $message);
            if (mysqli_stmt_execute($stmt)) {
                $success = "Thank you for your feedback!";
                $subject = '';
                $message = '';
            } else {
                $error = "Error submitting feedback";
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
        <title>Feedback - MMU Talent Showcase</title>
        <link rel="stylesheet" href="assets/css/style.css">
    </head>

    <body>
        <?php include 'includes/header-inc.php'; ?>
        <?php include 'includes/navbar.php'; ?>

        <div class="container">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <div class="card">
                <h2>Submit Feedback</h2>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="form-group">
                        <label>Subject</label>
                        <input type="text" name="subject" class="form-control"
                            value="<?php echo isset($subject) ? htmlspecialchars($subject) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Message</label>
                        <textarea name="message" class="form-control" rows="4"
                            required><?php echo isset($message) ? htmlspecialchars($message) : ''; ?></textarea>
                    </div>
                    <div class="form-group">
                        <input type="submit" class="btn" value="Submit Feedback">
                    </div>
                </form>
            </div>
        </div>
        <?php include 'includes/footer-inc.php'; ?>
    </body>

</html>