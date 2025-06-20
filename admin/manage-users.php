<?php
session_start();
require_once '../config/database.php';
include '../includes/timeout.php';
include_once '../includes/talent-categories.php';

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

// Handle user role update
if (isset($_POST['update_role'])) {
    $user_id = $_POST['user_id'];
    $new_role = $_POST['role'];

    $sql = "UPDATE users SET role = ? WHERE id = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "si", $new_role, $user_id);
        if (mysqli_stmt_execute($stmt)) {
            $success = "User role updated successfully";
            // Check if the updated user is the current user
            if ($user_id == $_SESSION['user_id']) {
                // Refresh the current user's role from the database
                $sql = "SELECT role FROM users WHERE id = ?";
                if ($stmt = mysqli_prepare($conn, $sql)) {
                    mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_bind_result($stmt, $current_role);
                    mysqli_stmt_fetch($stmt);
                    mysqli_stmt_close($stmt);
                    $_SESSION['role'] = $current_role; // Update session role
                    if ($current_role !== 'admin') {
                        // Redirect to index.php if no longer admin
                        header("Location: ../index.php");
                        exit();
                    }
                }
            }
            // Redirect to prevent form resubmission for other users
            header("Location: " . htmlspecialchars($_SERVER["PHP_SELF"]) . "?success=role_updated");
            exit();
        } else {
            $error = "Error updating user role";
        }
        mysqli_stmt_close($stmt);
    }
}

// Handle user deletion
if (isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];

    // Prevent deletion of user_id = 1 (admin)
    if ($user_id == 1) {
        $error = "Cannot delete the primary admin account.";
    } else {
        // Delete related records from all referenced tables
        $tables = ['cart', 'comments', 'favorites', 'feedback', 'orders', 'products', 'profiles', 'resources', 'talents', 'user_questions'];
        foreach ($tables as $table) {
            $sql = "DELETE FROM $table WHERE user_id = ?";
            if ($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "i", $user_id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }

        // Delete the user
        $sql = "DELETE FROM users WHERE id = ?";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            if (mysqli_stmt_execute($stmt)) {
                $success = "User deleted successfully";
            } else {
                $error = "Error deleting user: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// Get all users with their profile information, excluding user_id = 1
$sql = "SELECT u.*, p.profile_picture, p.bio 
        FROM users u 
        LEFT JOIN profiles p ON u.id = p.user_id 
        WHERE u.id != 1
        ORDER BY u.created_at DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Manage Users - Admin Dashboard</title>
        <link rel="stylesheet" href="../assets/css/style.css">
    </head>

    <body>
        <header class="header">
            <h1>MMU Talent Showcase Portal - Manage Users</h1>
        </header>

        <?php include '../includes/admin-navbar.php'; ?>

        <div class="container">
            <div class="card">
                <h2>Manage Users</h2>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <div class="user-list">
                    <?php while ($user = mysqli_fetch_assoc($result)): ?>
                        <div class="user-card">
                            <div class="user-info">
                                <?php if (!empty($user['profile_picture'])): ?>
                                    <img src="../<?php echo htmlspecialchars($user['profile_picture']); ?>"
                                        alt="Profile Picture" class="user-avatar">
                                <?php else: ?>
                                    <img src="../assets/images/default-avatar.png" alt="Default Avatar" class="user-avatar">
                                <?php endif; ?>
                                <div class="user-details">
                                    <h3><?php echo htmlspecialchars($user['full_name']); ?></h3>
                                    <p>Username: <?php echo htmlspecialchars($user['username']); ?></p>
                                    <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
                                    <p>Role: <?php echo htmlspecialchars(ucfirst($user['role'])); ?></p>
                                    <p>Joined: <?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
                                </div>
                            </div>

                            <div class="user-actions">
                                <!-- Role Update Form -->
                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post"
                                    class="action-form">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <select name="role" class="form-control">
                                        <option value="user" <?php echo $user['role'] == 'user' ? 'selected' : ''; ?>>User
                                        </option>
                                        <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin
                                        </option>
                                    </select>
                                    <button type="submit" name="update_role" class="btn btn-primary">Update Role</button>
                                </form>

                                <!-- Delete User Form -->
                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post"
                                    class="action-form"
                                    onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" name="delete_user" class="btn btn-danger">Delete User</button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

        <?php include '../includes/footer-inc.php'; ?>
    </body>

</html>