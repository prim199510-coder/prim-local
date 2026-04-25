<?php
// Get form data
$title = $_POST['title'];
$content = $_POST['content'];
$slug = $_POST['slug'];
$tags = $_POST['tags'];
$meta_title = $_POST['meta_title'];
$meta_description = $_POST['meta_description'];
$meta_keywords = $_POST['meta_keywords'];
$banner_image = $_POST['banner_image'];
$cat_id = intval($_POST['cat_id']);
$published = $_POST['published']; // draft or published

if(isset($_POST['blog_id'])) {
    // Update existing blog
    $blog_id = intval($_POST['blog_id']);
    $sql = "UPDATE blogs SET title=?, content=?, status=?, published_at=IF(?='published', NOW(), NULL) WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $title, $content, $status, $status, $blog_id);
    $stmt->execute();
} else {
    // Create new blog
    $sql = "INSERT INTO blogs (title, content, status, published_at) VALUES (?, ?, ?, IF(?='published', NOW(), NULL))";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $title, $content, $status, $status);
    $stmt->execute();
}
?>
