<?php
session_start();
header('Content-Type: application/json');
include '../includes/db.php';
$conn = getDbConnection();

$admin_id = $_SESSION['admin_id'] ?? null;
if (!$admin_id) {
    echo json_encode(["success" => false, "message" => "Unauthorized access"]);
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'update_profile') {
    $email = $conn->real_escape_string(trim($_POST['email'] ?? ''));
    $contact = $conn->real_escape_string(trim($_POST['contact'] ?? ''));

    $query = "UPDATE admins SET email='$email', contact='$contact' WHERE id=$admin_id";
    if ($conn->query($query)) {
        echo json_encode(["success" => true, "message" => "Profile updated successfully."]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update profile."]);
    }
    exit;
}

if ($action === 'change_password') {
    $old = $_POST['old_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $result = $conn->query("SELECT password FROM admins WHERE id = $admin_id");
    $admin = $result->fetch_assoc();

    if (!$admin || !password_verify($old, $admin['password'])) {
        echo json_encode(["success" => false, "message" => "Old password is incorrect."]);
        exit;
    }

    if ($new !== $confirm) {
        echo json_encode(["success" => false, "message" => "New passwords do not match."]);
        exit;
    }

    $hashed = password_hash($new, PASSWORD_BCRYPT);
    $conn->query("UPDATE admins SET password='$hashed' WHERE id=$admin_id");

    echo json_encode(["success" => true, "message" => "Password changed successfully."]);
    exit;
}

echo json_encode(["success" => false, "message" => "Invalid action."]);
