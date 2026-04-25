<?php
include './../includes/auth.php';
include './../includes/db.php';

header('Content-Type: application/json');

if (!isset($_POST['id']) || empty($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid category ID']);
    exit;
}

$categoryId = intval($_POST['id']);
$conn = getDbConnection();

// Check if category has any active blogs associated with it
$checkBlogs = $conn->prepare("SELECT COUNT(*) AS blog_count FROM blogs WHERE cat_id = ?");
$checkBlogs->bind_param("i", $categoryId);
$checkBlogs->execute();
$result = $checkBlogs->get_result();
$row = $result->fetch_assoc();

if ($row['blog_count'] > 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Cannot delete this category. It has active blogs associated with it.'
    ]);
    exit;
}

// Proceed to delete category
$delete = $conn->prepare("DELETE FROM categories WHERE id = ?");
$delete->bind_param("i", $categoryId);

if ($delete->execute()) {
    echo json_encode(['success' => true, 'message' => 'Category deleted successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete category.']);
}

$delete->close();
$conn->close();
?>
