<?php
session_start();
require_once 'config/database.php';
include 'includes/timeout.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_POST['comment_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$comment_id = (int) $_POST['comment_id'];
$user_id = $_SESSION['user_id'];

// Check if the comment exists and belongs to the user or is a reply to the user
$sql = "SELECT * FROM comments WHERE id = ?";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $comment_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $comment = mysqli_fetch_assoc($result);

    if ($comment && ($comment['user_id'] == $user_id || $comment['commented_user_id'] == $user_id)) {
        // Delete the comment and all its replies
        $sql = "DELETE FROM comments WHERE id = ? OR parent_id = ?";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "ii", $comment_id, $comment_id);
            if (mysqli_stmt_execute($stmt)) {
                echo json_encode(['success' => true]);
                exit();
            }
        }
    }
}

echo json_encode(['success' => false, 'message' => 'Failed to delete comment']);