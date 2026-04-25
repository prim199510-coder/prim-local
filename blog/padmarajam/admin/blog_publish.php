<?php
include './../includes/auth.php';
include './../includes/db.php';

header('Content-Type: application/json');

// --- Step 1: Validate request ---
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
$message = "";

// --- Step 2: Fetch the main blog record ---
$stmt = $conn->prepare("SELECT * FROM blogs WHERE id = ?");
$stmt->bind_param('i', $blogId);
$stmt->execute();
$result = $stmt->get_result();

if ($result === false || $result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Blog not found']);
    $stmt->close();
    $conn->close();
    exit;
}

$blog = $result->fetch_assoc();
$stmt->close();

// --- Step 3: Check if there is a draft version ---
$draftStmt = $conn->prepare("
    SELECT id, cat_id, title, author, slug, content, tags,
           meta_title, meta_desc, meta_keywords, banner_images, mobile_banner_images, video_links, documents
    FROM blogs
    WHERE parent_id = ? AND published = 0
    LIMIT 1
");
$draftStmt->bind_param('i', $blogId);
$draftStmt->execute();
$draftStmt->store_result();

$draftCount = $draftStmt->num_rows;
$message .= "Drafts found: " . $draftCount;

if ($draftCount > 0) {
    // --- Step 4: Fetch draft data ---
    $draftStmt->bind_result(
        $draftId, $cat_id, $title, $author, $slug, $content, $tags,
        $meta_title, $meta_desc, $meta_keywords, $banner_images, $mobile_banner_images, $video_links,$documents
    );
    $draftStmt->fetch();
    $draftStmt->close();

    $message .= " | Merging draft ID: " . $draftId;

    // --- Step 5: Update main blog with draft content ---
    $updateStmt = $conn->prepare("
        UPDATE blogs SET 
            cat_id = ?, title = ?, author = ?, slug = ?, content = ?, tags = ?, 
            meta_title = ?, meta_desc = ?, meta_keywords = ?, banner_images = ?, mobile_banner_images = ?, video_links = ?, documents = ?, published = 1
        WHERE id = ?
    ");
    $updateStmt->bind_param(
        "issssssssssssi",
        $cat_id, $title, $author, $slug, $content, $tags,
        $meta_title, $meta_desc, $meta_keywords, $banner_images, $mobile_banner_images, $video_links, $documents, $blogId
    );

    if ($updateStmt->execute()) {
        $updateStmt->close();

        // --- Step 6: Delete draft after successful merge ---
        $delStmt = $conn->prepare("DELETE FROM blogs WHERE id = ?");
        $delStmt->bind_param('i', $draftId);
        $delStmt->execute();
        $delStmt->close();

        echo json_encode([
            'success' => true,
            'blog_id' => $draftId,
            'message' => 'Blog published and draft merged successfully :: ' . $message
        ]);
    } else {
        $updateStmt->close();
        echo json_encode([
            'success' => false,
            'blog_id' => $draftId,
            'message' => 'Failed to update main blog with draft :: ' . $message
        ]);
    }

} else {
    $draftStmt->close();

    // --- Step 7: No draft found; publish directly if unpublished ---
    if ((int)$blog['published'] === 0) {
        $pubStmt = $conn->prepare("UPDATE blogs SET published = 1 WHERE id = ?");
        $pubStmt->bind_param('i', $blogId);

        if ($pubStmt->execute()) {
            echo json_encode([
                'success' => true,
                'blog_id' => $blogId,
                'message' => 'Blog published successfully :: ' . $message
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'blog_id' => $blogId,
                'message' => 'Failed to publish blog :: ' . $message
            ]);
        }

        $pubStmt->close();
    } else {
        echo json_encode([
            'success' => false,
            'blog_id' => $blogId,
            'message' => 'Blog is already published :: ' . $message
        ]);
    }
}

$conn->close();
?>
