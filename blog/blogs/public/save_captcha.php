<?php
session_start();
if (isset($_GET['code'])) {
    $_SESSION['captcha'] = strtoupper(trim($_GET['code']));
}
?>
