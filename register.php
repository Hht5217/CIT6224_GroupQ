<?php
session_start();
require_once 'config/database.php';
if (isset($_SESSION['user_id'])) {
    header("location: index.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $email = trim($_POST['email']);
    $full_name = trim($_POST['full_name']);

    // Validate input
    if (empty($username) || empty($password) || empty($confirm_password) || empty($email) || empty($full_name)) {
        $error = "Please fill in all fields";
    } elseif ($password != $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long";
    } else {
        // Check if username exists
        $sql = "SELECT id FROM users WHERE username = ?";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) > 0) {
                $error = "This username is already taken";
            } else {
                // Check if email exists
                $sql = "SELECT id FROM users WHERE email = ?";
                if ($stmt = mysqli_prepare($conn, $sql)) {
                    mysqli_stmt_bind_param($stmt, "s", $email);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_store_result($stmt);

                    if (mysqli_stmt_num_rows($stmt) > 0) {
                        $error = "This email is already registered";
                    } else {
                        // Insert new user
                        $sql = "INSERT INTO users (username, password, email, full_name) VALUES (?, ?, ?, ?)";
                        if ($stmt = mysqli_prepare($conn, $sql)) {
                            mysqli_stmt_bind_param($stmt, "ssss", $username, $password, $email, $full_name);

                            if (mysqli_stmt_execute($stmt)) {
                                $success = "Registration successful! You can now login.";
                            } else {
                                $error = "Something went wrong. Please try again later.";
                            }
                        }
                    }
                }
            }
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Register - MMU Talent Showcase</title>
        <link rel="stylesheet" href="assets/css/style.css">
    </head>

    <body>
        <?php include 'includes/header-inc.php'; ?>
        <?php include 'includes/navbar.php'; ?>

        <div class="container">
            <div class="card">
                <h2>Register</h2>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <input type="submit" class="btn" value="Register">
                    </div>
                    <p>Already have an account? <a href="login.php">Login here</a></p>
                    <p><a href="index.php">Return to Home</a></p>
                </form>
            </div>
        </div>
        <?php include 'includes/footer-inc.php'; ?>
    </body>

</html>