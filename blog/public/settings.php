<?php
require_once '../includes/db.php';
$conn = getDbConnection();

// Fetch site settings once
$settingsRes = $conn->query("SELECT * FROM settings LIMIT 1");
$settings = $settingsRes->fetch_assoc();
?>