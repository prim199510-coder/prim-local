<?php
session_start();

// Destroy all session variables
$_SESSION = [];
session_unset();
session_destroy();

// Redirect to admin login page
header("Location: login.php");
exit;
