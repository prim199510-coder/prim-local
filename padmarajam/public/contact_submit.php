<?php
// contact_submit.php
include '../includes/db.php'; // adjust path as needed
$conn = getDbConnection();  // assumes you have a getDbConnection() function

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = htmlspecialchars(trim($_POST['name'] ?? ''));
    $email   = htmlspecialchars(trim($_POST['email'] ?? ''));
    $message = htmlspecialchars(trim($_POST['message'] ?? ''));

    if (!$name || !$email || !$message) {
        echo "error";
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO enquiries (name, email, message) VALUES (?, ?, ?)");
    if (!$stmt) {
        echo "error";
        exit;
    }

    $stmt->bind_param("sss", $name, $email, $message);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }

    $stmt->close();
    $conn->close();
    exit;
}

echo "error";
