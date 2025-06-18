<?php
$timeout_duration = 300; // 5 minutes timeout

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    header("Location: logout.php?timeout=1");
    exit;
}
$_SESSION['LAST_ACTIVITY'] = time();
?>