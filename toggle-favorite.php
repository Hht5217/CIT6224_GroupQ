<?php
session_start();
require_once 'config/database.php';
include 'includes/timeout.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $talent_id = $_POST['talent_id'];
    $action = $_POST['action']; // 'add' or 'remove'

    try {
        if ($action === 'add') {
            // Check if already favorited
            $check_sql = "SELECT id FROM favorites WHERE user_id = ? AND talent_id = ?";
            if ($check_stmt = mysqli_prepare($conn, $check_sql)) {
                mysqli_stmt_bind_param($check_stmt, "ii", $user_id, $talent_id);
                mysqli_stmt_execute($check_stmt);
                mysqli_stmt_store_result($check_stmt);

                if (mysqli_stmt_num_rows($check_stmt) == 0) {
                    // Add to favorites
                    $sql = "INSERT INTO favorites (user_id, talent_id) VALUES (?, ?)";
                    if ($stmt = mysqli_prepare($conn, $sql)) {
                        mysqli_stmt_bind_param($stmt, "ii", $user_id, $talent_id);
                        mysqli_stmt_execute($stmt);

                        // Update statistics
                        $sql = "INSERT INTO statistics (talent_id, favorites) VALUES (?, 1) 
                               ON DUPLICATE KEY UPDATE favorites = favorites + 1";
                        if ($stmt = mysqli_prepare($conn, $sql)) {
                            mysqli_stmt_bind_param($stmt, "i", $talent_id);
                            mysqli_stmt_execute($stmt);
                        }

                        echo json_encode(['success' => true, 'message' => 'Added to favorites']);
                    }
                } else {
                    echo json_encode(['success' => false, 'message' => 'Already in favorites']);
                }
            }
        } else {
            // Check if exists before removing
            $check_sql = "SELECT id FROM favorites WHERE user_id = ? AND talent_id = ?";
            if ($check_stmt = mysqli_prepare($conn, $check_sql)) {
                mysqli_stmt_bind_param($check_stmt, "ii", $user_id, $talent_id);
                mysqli_stmt_execute($check_stmt);
                mysqli_stmt_store_result($check_stmt);

                if (mysqli_stmt_num_rows($check_stmt) > 0) {
                    // Remove from favorites
                    $sql = "DELETE FROM favorites WHERE user_id = ? AND talent_id = ?";
                    if ($stmt = mysqli_prepare($conn, $sql)) {
                        mysqli_stmt_bind_param($stmt, "ii", $user_id, $talent_id);
                        mysqli_stmt_execute($stmt);

                        // Update statistics
                        $sql = "UPDATE statistics SET favorites = favorites - 1 
                               WHERE talent_id = ? AND favorites > 0";
                        if ($stmt = mysqli_prepare($conn, $sql)) {
                            mysqli_stmt_bind_param($stmt, "i", $talent_id);
                            mysqli_stmt_execute($stmt);
                        }

                        echo json_encode(['success' => true, 'message' => 'Removed from favorites']);
                    }
                } else {
                    echo json_encode(['success' => false, 'message' => 'Not in favorites']);
                }
            }
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error processing request']);
    }
}
?>