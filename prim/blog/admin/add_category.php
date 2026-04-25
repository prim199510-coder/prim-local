<?php
include './../includes/auth.php';
include './../includes/db.php';

header('Content-Type: application/json');

// DB connection
$conn = getDbConnection();

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "DB connection failed"]);
    exit;
}

// Validate input
if (!isset($_POST['new_category']) || trim($_POST['new_category']) === "") {
    echo json_encode(["success" => false, "message" => "Category name required"]);
    exit;
}

$newCategory = $conn->real_escape_string(trim($_POST['new_category']));

// Insert into DB
$sql = "INSERT INTO categories (name) VALUES ('$newCategory')";
if ($conn->query($sql)) {
    $id = $conn->insert_id;
    echo json_encode(["success" => true, "id" => $id, "name" => $newCategory]);
} else {
    echo json_encode(["success" => false, "message" => "Database error"]);
}
$conn->close();
?>
