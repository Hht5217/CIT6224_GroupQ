<?php
// Requires user to be logged in to access certain pages
if (!isset($_SESSION['user_id'])) {
    header("location: login.php?login-required=1");
    exit;
}
?>