<?php
include './../includes/auth.php';  // Ensure user is logged in
include './../includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid blog ID']);
    exit;
}

$blogId = (int) $_POST['id'];
$conn = getDbConnection();

// Optional: fetch blog first if you want to delete banner images
$result = $conn->query("SELECT banner_images FROM blogs WHERE id = $blogId");
if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Blog not found']);
    exit;
}
$blog = $result->fetch_assoc();

// Delete banner images from server if exists
if (!empty($blog['banner_images']) && file_exists('../' . $blog['banner_images'])) {
    @unlink('../' . $blog['banner_images']);
}

// Get blog record first
$stmt = $conn->prepare("SELECT id, parent_id FROM blogs WHERE id = ?");
$stmt->bind_param('i', $blogId);
$stmt->execute();
$result = $stmt->get_result();
$blog = $result->fetch_assoc();
$stmt->close();

if ($blog) {
    // Determine the original blog id
    $originalId = ($blog['parent_id'] == -1) ? $blog['id'] : $blog['parent_id'];

    // Delete both original and all drafts linked to it
    $deleteStmt = $conn->prepare("DELETE FROM blogs WHERE id = ? OR parent_id = ?");
    $deleteStmt->bind_param('ii', $originalId, $originalId);

    if ($deleteStmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Blog and its drafts deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete blog']);
    }
    $deleteStmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Blog not found']);
}

$conn->close();

