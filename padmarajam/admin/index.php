<?php
session_start();

// Check if user is logged in
if (isset($_SESSION['admin_id'])) {
    // Redirect to dashboard if admin logged in
    header('Location: dashboard.php');
    exit;
} else {
    // Redirect to login page if not admin logged in
    header('Location: login.php');
    exit;
}
?>
