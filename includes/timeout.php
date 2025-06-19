<?php
$timeout_duration = 300; // 5 minutes timeout

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    // Get the current directory
    $current_dir = dirname($_SERVER['SCRIPT_NAME']);

    // If in /admin or a subdirectory, go up one level for logout.php
    if (strpos($current_dir, '/admin') !== false) {
        header("Location: ../logout.php?timeout=1");
    } else {
        header("Location: logout.php?timeout=1");
    }
    exit;
}
$_SESSION['LAST_ACTIVITY'] = time();
?>