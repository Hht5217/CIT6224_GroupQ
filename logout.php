<?php
session_start();

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to login page
$redirect = "login.php";
if (isset($_GET['timeout'])) {
    $redirect .= "?timeout=1";
}
header("Location: $redirect");
exit;
?>