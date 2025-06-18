<?php
session_start();
require_once 'config/database.php';
include 'includes/timeout.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if resource ID is provided
if (!isset($_GET['id'])) {
    header("Location: user-dashboard.php?page=resources");
    exit();
}

$resource_id = $_GET['id'];

// Get resource information
$stmt = $conn->prepare("SELECT * FROM resources WHERE id = ?");
$stmt->bind_param("i", $resource_id);
$stmt->execute();
$result = $stmt->get_result();
$resource = $result->fetch_assoc();

if (!$resource) {
    header("Location: user-dashboard.php?page=resources");
    exit();
}

// Update download count
$stmt = $conn->prepare("UPDATE resources SET download_count = download_count + 1 WHERE id = ?");
$stmt->bind_param("i", $resource_id);
$stmt->execute();

// Set headers for file download
header('Content-Type: ' . $resource['file_type']);
header('Content-Disposition: attachment; filename="' . $resource['file_name'] . '"');
header('Content-Length: ' . $resource['file_size']);
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Output file
readfile($resource['file_path']);
exit();
?>