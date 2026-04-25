<?php


include './../includes/auth.php';
include './../includes/db.php';
$conn = getDbConnection();

$success = "";
$error = "";
$pageTitle = "New Blog";

$id = isset($_GET['id']) ? intval($_GET['id']) : -1;

// Default blog structure
$blogData = [
    'cat_id' => '',
    'title' => '',
    'author' => '',
    'slug' => '',
    'content' => '',
    'tags' => '',
    'meta_title' => '',
    'meta_desc' => '',
    'meta_keywords' => '',
    'banner_images' => [],            // JSON
    'mobile_banner_images' => [],     // NEW: JSON for mobile banners
    'video_links' => [],              // JSON
    'documents' => [],                // JSON (single PDF stored as single entry array)
    'published' => 0,
    'parent_id' => -1
];

// Function: check duplicate
function blogExists($conn, $cat_id, $title, $slug, $exclude_id = 0)
{
    $stmt = $conn->prepare("
        SELECT id 
        FROM blogs 
        WHERE cat_id = ?
            AND (title = ? OR slug = ?)
            AND id NOT IN (
                ?,                          
                COALESCE((SELECT parent_id FROM blogs WHERE id = ?), -1),
                COALESCE((SELECT id FROM blogs WHERE parent_id = ? LIMIT 1), -1)
            )
        LIMIT 1
    ");
    $stmt->bind_param("issiii", $cat_id, $title, $slug, $exclude_id, $exclude_id, $exclude_id);
    $stmt->execute();
    $exists = $stmt->get_result()->num_rows > 0;
    $stmt->close();
    return $exists;
}

// Load blog if ID given
if ($id >= 0) {
    $stmt = $conn->prepare("SELECT * FROM blogs WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $blogData = $res->fetch_assoc();
        $pageTitle = ($blogData['published'] == 1) ? "Edit Blog" : "Edit Blog (Draft)";

        // decode JSON fields if present
        $blogData['banner_images'] = json_decode($blogData['banner_images'] ?? '[]', true);
        if (!is_array($blogData['banner_images'])) $blogData['banner_images'] = [];

        $blogData['mobile_banner_images'] = json_decode($blogData['mobile_banner_images'] ?? '[]', true);
        if (!is_array($blogData['mobile_banner_images'])) $blogData['mobile_banner_images'] = [];

        $blogData['video_links'] = json_decode($blogData['video_links'] ?? '[]', true);
        if (!is_array($blogData['video_links'])) $blogData['video_links'] = [];

        $blogData['documents'] = json_decode($blogData['documents'] ?? '[]', true);
        if (!is_array($blogData['documents'])) $blogData['documents'] = [];
    }
    $stmt->close();

    // If published, load draft version (if exists)
    if ($blogData['published'] == 1) {
        $draftStmt = $conn->prepare("SELECT * FROM blogs WHERE parent_id=? AND published=0 LIMIT 1");
        $draftStmt->bind_param("i", $id);
        $draftStmt->execute();
        $draftRes = $draftStmt->get_result();
        if ($draftRes->num_rows > 0) {
            $blogData = $draftRes->fetch_assoc();
            // decode JSON fields for draft
            $blogData['banner_images'] = json_decode($blogData['banner_images'] ?? '[]', true);
            if (!is_array($blogData['banner_images'])) $blogData['banner_images'] = [];

            $blogData['mobile_banner_images'] = json_decode($blogData['mobile_banner_images'] ?? '[]', true);
            if (!is_array($blogData['mobile_banner_images'])) $blogData['mobile_banner_images'] = [];

            $blogData['video_links'] = json_decode($blogData['video_links'] ?? '[]', true);
            if (!is_array($blogData['video_links'])) $blogData['video_links'] = [];

            $blogData['documents'] = json_decode($blogData['documents'] ?? '[]', true);
            if (!is_array($blogData['documents'])) $blogData['documents'] = [];

            $pageTitle = "Edit Blog (Draft)";
        }
        $draftStmt->close();
    }
}

// Handle category add (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_category'])) {
    $newCat = trim($_POST['new_category']);
    if ($newCat != '') {
        $exists = $conn->query("SELECT id FROM categories WHERE name='" . $conn->real_escape_string($newCat) . "'");
        if ($exists->num_rows == 0) {
            $conn->query("INSERT INTO categories (name) VALUES ('" . $conn->real_escape_string($newCat) . "')");
            echo json_encode(['success' => true, 'id' => $conn->insert_id, 'name' => $newCat]);
        } else {
            $row = $exists->fetch_assoc();
            echo json_encode(['success' => true, 'id' => $row['id'], 'name' => $newCat]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid name']);
    }
    exit;
}

// Save logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['new_category'])) {
    $cat_id = intval($_POST['category_id']);
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $slug = trim($_POST['slug']);
    $content = $_POST['content'];
    $tags = trim($_POST['tags']);
    // meta_keywords will be posted as comma separated by JS tag UI
    $meta_title = trim($_POST['meta_title']);
    $meta_desc = trim($_POST['meta_description']);
    $meta_keywords = isset($_POST['meta_keywords']) ? trim($_POST['meta_keywords']) : '';

    // Existing desktop banner images from DB that user wants to keep
    $existingImages = $blogData['banner_images'] ?? [];

    // Remove selected desktop images
    $removeImages = isset($_POST['remove_images']) ? (array)$_POST['remove_images'] : [];
    if (!empty($removeImages)) {
        $existingImages = array_values(array_filter($existingImages, function($img) use ($removeImages) {
            return !in_array($img, $removeImages);
        }));
        // Optionally delete files from disk (commented)
    }

    // Handle new banner image uploads (multiple)
    if (!empty($_FILES['banner_images']) && !empty($_FILES['banner_images']['name'][0])) {
        $uploadDir = './../uploads/blogs/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        foreach ($_FILES['banner_images']['name'] as $index => $name) {
            if (empty($name)) continue;
            $ext = pathinfo($name, PATHINFO_EXTENSION);
            $safeBase = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', pathinfo($name, PATHINFO_FILENAME));
            $fileName = time() . '_' . bin2hex(random_bytes(4)) . '_' . $safeBase . '.' . $ext;
            $target = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['banner_images']['tmp_name'][$index], $target)) {
                $existingImages[] = 'uploads/blogs/' . $fileName;
            }
        }
    }

    // --- Mobile banner images handling (new) ---
    $existingMobileImages = $blogData['mobile_banner_images'] ?? [];

    // Remove selected mobile images
    $removeMobileImages = isset($_POST['remove_mobile_images']) ? (array)$_POST['remove_mobile_images'] : [];
    if (!empty($removeMobileImages)) {
        $existingMobileImages = array_values(array_filter($existingMobileImages, function($img) use ($removeMobileImages) {
            return !in_array($img, $removeMobileImages);
        }));
    }

    // Handle new mobile banner image uploads (multiple)
    if (!empty($_FILES['mobile_banner_images']) && !empty($_FILES['mobile_banner_images']['name'][0])) {
        $uploadDirMobile = './../uploads/blogs/mobile/';
        if (!is_dir($uploadDirMobile)) mkdir($uploadDirMobile, 0777, true);

        foreach ($_FILES['mobile_banner_images']['name'] as $index => $name) {
            if (empty($name)) continue;

            $ext = pathinfo($name, PATHINFO_EXTENSION);
            $safeBase = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', pathinfo($name, PATHINFO_FILENAME));
            $fileName = time() . '_' . bin2hex(random_bytes(4)) . '_' . $safeBase . '.' . $ext;
            $target = $uploadDirMobile . $fileName;

            if (move_uploaded_file($_FILES['mobile_banner_images']['tmp_name'][$index], $target)) {
                $existingMobileImages[] = 'uploads/blogs/mobile/' . $fileName;
            }
        }
    }

    // documents: existing and removal (single-PDF flow)
    $existingDocs = $blogData['documents'] ?? [];
    $removeDocs = isset($_POST['remove_documents']) ? (array)$_POST['remove_documents'] : [];
    if (!empty($removeDocs)) {
        $existingDocs = array_values(array_filter($existingDocs, function($d) use ($removeDocs) {
            return !in_array($d, $removeDocs);
        }));
    }

    // ---- SINGLE PDF UPLOAD ----
    if (!empty($_FILES['document']) && $_FILES['document']['error'] == 0) {
        $uploadDirDocs = './../uploads/docs/';
        if (!is_dir($uploadDirDocs)) mkdir($uploadDirDocs, 0777, true);

        $name = $_FILES['document']['name'];
        $tmp = $_FILES['document']['tmp_name'];

        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        if ($ext === 'pdf') {
            $safeName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', pathinfo($name, PATHINFO_FILENAME));
            $fileName = time() . '_' . bin2hex(random_bytes(4)) . '_' . $safeName . '.pdf';
            $target = $uploadDirDocs . $fileName;

            if (move_uploaded_file($tmp, $target)) {
                // Since only 1 PDF allowed → replace old PDF
                $existingDocs = ['uploads/docs/' . $fileName];
            }
        }
    }

    // video links array (clean empties)
    $video_links = isset($_POST['video_links']) ? (array)$_POST['video_links'] : [];
    $video_links = array_values(array_filter(array_map('trim', $video_links)));

    // Prepare JSON to save
    $bannerJSON = json_encode(array_values($existingImages));
    $mobileBannerJSON = json_encode(array_values($existingMobileImages));
    $videoJSON = json_encode($video_links);
    $docsJSON = json_encode(array_values($existingDocs));

    if ($title && $slug && $content) {
        if (blogExists($conn, $cat_id, $title, $slug, $id)) {
            $error = "URL or title already exists in this category.";
        } else {
            // CASE HANDLING (keeps your existing branching logic)
            if (($id <= 0) || ($blogData['parent_id'] == -1 && $blogData['published'] == 0)) {
                // New draft or update draft when parent_id == -1 and published == 0
                if ($id > 0) {
                    // Update existing row
                    $stmt = $conn->prepare("
                        UPDATE blogs SET
                            cat_id=?,
                            title=?,
                            author=?,
                            slug=?,
                            content=?,
                            tags=?,
                            meta_title=?,
                            meta_desc=?,
                            meta_keywords=?,
                            banner_images=?,
                            mobile_banner_images=?,
                            video_links=?,
                            documents=?,
                            updated_at=NOW()
                        WHERE id=?
                    ");
                    // types: i, then 12 s, then i
                    $stmt->bind_param("issssssssssssi",
                        $cat_id, $title, $author, $slug, $content, $tags,
                        $meta_title, $meta_desc, $meta_keywords,
                        $bannerJSON, $mobileBannerJSON, $videoJSON, $docsJSON,
                        $id
                    );
                    $stmt->execute();
                    $stmt->close();
                    $redirectId = $id;
                    $success = "Draft updated successfully.";
                } else {
                    // Insert new draft
                    $stmt = $conn->prepare("
                        INSERT INTO blogs
                        (cat_id, title, author, slug, content, tags, meta_title, meta_desc, meta_keywords, banner_images, mobile_banner_images, video_links, documents, published, parent_id, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, -1, NOW())
                    ");
                    // types: i + 12 s
                    $stmt->bind_param("issssssssssss",
                        $cat_id, $title, $author, $slug, $content, $tags,
                        $meta_title, $meta_desc, $meta_keywords,
                        $bannerJSON, $mobileBannerJSON, $videoJSON, $docsJSON
                    );
                    $stmt->execute();
                    $redirectId = $conn->insert_id;
                    $stmt->close();
                    $success = "New draft created.";
                }
            } else if ($blogData['published'] == 1 && $blogData['parent_id'] == -1) {
                // Published original — create/update a draft (parent-child)
                $originalId = $id;
                $check = $conn->prepare("SELECT id FROM blogs WHERE parent_id=? AND published=0 LIMIT 1");
                $check->bind_param("i", $originalId);
                $check->execute();
                $draftRes = $check->get_result();
                if ($draftRes->num_rows > 0) {
                    $draft = $draftRes->fetch_assoc();
                    $draftId = $draft['id'];
                    $stmt = $conn->prepare("
                        UPDATE blogs SET
                        cat_id=?, title=?, author=?, slug=?, content=?, tags=?, meta_title=?, meta_desc=?, meta_keywords=?, banner_images=?, mobile_banner_images=?, video_links=?, documents=?, updated_at=NOW()
                        WHERE id=?
                    ");
                    $stmt->bind_param("issssssssssssi",
                        $cat_id, $title, $author, $slug, $content, $tags,
                        $meta_title, $meta_desc, $meta_keywords,
                        $bannerJSON, $mobileBannerJSON, $videoJSON, $docsJSON,
                        $draftId
                    );
                    $stmt->execute();
                    $stmt->close();
                    $redirectId = $draftId;
                    $success = "Draft updated.";
                } else {
                    $stmt = $conn->prepare("
                        INSERT INTO blogs
                        (cat_id, title, author, slug, content, tags, meta_title, meta_desc, meta_keywords, banner_images, mobile_banner_images, video_links, documents, published, parent_id, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, NOW())
                    ");
                    $stmt->bind_param("issssssssssssi",
                        $cat_id, $title, $author, $slug, $content, $tags,
                        $meta_title, $meta_desc, $meta_keywords,
                        $bannerJSON, $mobileBannerJSON, $videoJSON, $docsJSON,
                        $originalId
                    );
                    $stmt->execute();
                    $redirectId = $conn->insert_id;
                    $stmt->close();
                    $success = "Draft created.";
                }
                $check->close();
            } else if ($blogData['published'] == 0 && $blogData['parent_id'] != -1) {
                // Updating existing draft (child)
                $stmt = $conn->prepare("
                    UPDATE blogs SET
                    cat_id=?, title=?, author=?, slug=?, content=?, tags=?, meta_title=?, meta_desc=?, meta_keywords=?, banner_images=?, mobile_banner_images=?, video_links=?, documents=?, updated_at=NOW()
                    WHERE id=?
                ");
                $stmt->bind_param("issssssssssssi",
                    $cat_id, $title, $author, $slug, $content, $tags,
                    $meta_title, $meta_desc, $meta_keywords,
                    $bannerJSON, $mobileBannerJSON, $videoJSON, $docsJSON,
                    $id
                );
                $stmt->execute();
                $stmt->close();
                $redirectId = $id;
                $success = "Draft updated.";
            }

            header("Location: blog_list.php?new_id=" . $redirectId);
            exit;
        }
    } else {
        $error = "Please fill required fields.";
    }
}

// Preserve input if validation failed
if (!empty($error) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // simple sanitization for fields
    foreach (['cat_id','title','author','slug','content','tags','meta_title','meta_description','meta_keywords'] as $key) {
        if (isset($_POST[$key])) {
            if ($key === 'content') {
                $blogData[$key] = $_POST[$key];
            } else {
                $blogData[$key] = htmlspecialchars($_POST[$key], ENT_QUOTES);
            }
        }
    }
    // For lists: desktop banner images, mobile banner images, video links, documents
    $blogData['banner_images'] = isset($_POST['existing_images']) ? (array)$_POST['existing_images'] : ($blogData['banner_images'] ?? []);
    $blogData['mobile_banner_images'] = isset($_POST['existing_mobile_images']) ? (array)$_POST['existing_mobile_images'] : ($blogData['mobile_banner_images'] ?? []);
    $blogData['video_links'] = isset($_POST['video_links']) ? array_values(array_filter((array)$_POST['video_links'])) : ($blogData['video_links'] ?? []);
    $blogData['documents'] = isset($_POST['existing_documents']) ? (array)$_POST['existing_documents'] : ($blogData['documents'] ?? []);
}

// categories
$categories = $conn->query("SELECT id,name FROM categories ORDER BY name ASC");

include '_header.php';
?>

<main class="p-6 mt-16 space-y-4">
    <div class="wrapper">
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold"></h2>
        <div class="space-x-3">
            <a href="blog_list.php" class="bg-gray-500 text-white font-medium px-4 py-2 rounded"><i class="fa-solid fa-arrow-left"></i> Back</a>
        </div>
    </div>

    <section class="bg-white shadow rounded-lg p-5">
        <?php if ($success): ?>
            <p class="bg-green-100 text-green-700 p-3 mb-4 rounded"><?= htmlspecialchars($success) ?></p>
        <?php elseif ($error): ?>
            <p class="bg-red-100 text-center text-red-700 p-3 mb-4 rounded"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <label class="block font-semibold mb-1">Category</label>
            <select id="categorySelect" name="category_id" class="border px-3 py-2 rounded mb-4 w-full" required>
                <option value="">Select Blog Category</option>
                <?php while ($cat = $categories->fetch_assoc()): ?>
                    <option value="<?= $cat['id'] ?>" <?= ($cat['id'] == $blogData['cat_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label class="block mb-2 font-semibold">Title</label>
            <input type="text" name="title" id="titleField" class="w-full p-2 border rounded mb-4"
                value="<?= htmlspecialchars($blogData['title']) ?>" required>

            <label class="block mb-2 font-semibold">Author Name</label>
            <input type="text" name="author" class="w-full p-2 border rounded mb-4"
                value="<?= htmlspecialchars($blogData['author']) ?>">

            <label class="block mb-2 font-semibold">URL</label>
            <input type="text" name="slug" id="slugField" class="w-full p-2 border rounded mb-4"
                value="<?= htmlspecialchars($blogData['slug']) ?>" required>

            <label class="block mb-2 font-semibold">Content</label>
            <textarea name="content" id="editor"><?= htmlspecialchars_decode($blogData['content'], ENT_QUOTES) ?></textarea>

            <label class="block mt-4 mb-2 font-semibold">Tags (comma separated)</label>
            <input type="text" name="tags" class="w-full border p-2 rounded mb-4"
                value="<?= htmlspecialchars($blogData['tags']) ?>">

            <label class="block font-semibold">Banner Images (Desktop)</label>
            <p class="italic text-sm mb-2 text-primary">(Recommended banner size: 1000 × 400 px for best visibility.)</p>

            <?php if (!empty($blogData['banner_images'])): ?>
                <div class="flex gap-3 flex-wrap mb-3">
                    <?php foreach ($blogData['banner_images'] as $img): ?>
                        <div class="relative border p-1 rounded" style="width:120px;">
                            <img src="../<?= htmlspecialchars($img) ?>" class="w-full h-24 object-cover rounded mb-1">
                            <div class="flex items-center justify-between">
                                <label style="font-size:12px;">
                                    <input type="checkbox" name="remove_images[]" value="<?= htmlspecialchars($img) ?>"> Remove
                                </label>
                                <!-- Keep a hidden field to preserve image path if not removed -->
                                <input type="hidden" name="existing_images[]" value="<?= htmlspecialchars($img) ?>">
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <input type="file" name="banner_images[]" multiple class="mb-0">
            <small class="text-gray-500 block mb-4">You can upload multiple desktop images. Check "Remove" to delete an existing image.</small>

            <label class="block mt-4 font-semibold">Mobile Banner Images (Optional)</label>
            <p class="text-primary italic text-sm mb-2">(Recommended banner size: 1024 × 420 px for best visibility.)</p>

            <?php if (!empty($blogData['mobile_banner_images'])): ?>
                <div class="flex gap-3 flex-wrap mb-3">
                    <?php foreach ($blogData['mobile_banner_images'] as $img): ?>
                        <div class="relative border p-1 rounded" style="width:120px;">
                            <img src="../<?= htmlspecialchars($img) ?>" class="w-full h-24 object-cover rounded mb-1">
                            <div class="flex items-center justify-between">
                                <label style="font-size:12px;">
                                    <input type="checkbox" name="remove_mobile_images[]" value="<?= htmlspecialchars($img) ?>"> Remove
                                </label>
                                <!-- Keep a hidden field to preserve mobile image path if not removed -->
                                <input type="hidden" name="existing_mobile_images[]" value="<?= htmlspecialchars($img) ?>">
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <input type="file" name="mobile_banner_images[]" multiple class="mb-0">
            <small class="text-gray-500 block mb-4">Optional mobile-specific banners (multiple). Provide images optimized for mobile.</small>



              <!-- Video Link section -->
            <h3 class="font-semibold text-lg border-b pb-2 mt-6 mb-4">Video Link</h3>

            <div id="videoLinksContainer" class="space-y-2 mb-4">
                <?php if (!empty($blogData['video_links'])): ?>
                    <?php foreach ($blogData['video_links'] as $v): ?>
                        <div class="flex gap-2 video-row">
                            <input type="text" name="video_links[]" value="<?= htmlspecialchars($v) ?>"
                                   class="w-full p-2 border rounded" placeholder="YouTube or Video URL">
                            <!-- <button type="button" class="bg-red-500 text-white px-3 rounded remove-video">X</button> -->
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="flex gap-2 video-row">
                        <input type="text" name="video_links[]" value="" class="w-full p-2 border rounded" placeholder="YouTube or Video URL">
                        <!-- <button type="button" class="bg-red-500 text-white px-3 rounded remove-video">X</button> -->
                    </div>
                <?php endif; ?>
            </div>

            <!-- <button type="button" id="addVideoBtn" class="bg-blue-600 text-white px-3 py-1 rounded mb-4">+ Add Video Link</button> -->



            <!-- Documents (PDF) -->
            <h3 class="font-semibold text-lg border-b pb-2 mt-6 mb-4">Documents (PDF)</h3>

            <?php if (!empty($blogData['documents'])): ?>
                <div class="space-y-2 mb-3">
                    <?php foreach ($blogData['documents'] as $doc): ?>
                        <div class="flex items-center gap-3 border p-2 rounded">
                            <a href="../<?= htmlspecialchars($doc) ?>" target="_blank" class="text-blue-600 underline mr-2">
                                <?= htmlspecialchars(basename($doc)) ?>
                            </a>

                            <label style="font-size:12px;">
                                <input type="checkbox" name="remove_documents[]" value="<?= htmlspecialchars($doc) ?>"> Remove
                            </label>

                            <input type="hidden" name="existing_documents[]" value="<?= htmlspecialchars($doc) ?>">
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <label class="block font-semibold">Upload PDF Document (Single File)</label>
            <small class="text-primary italic block mb-4">Upload only one PDF document. (max size: 10 MB).</small>
            <input type="file" name="document" accept="application/pdf" class="mb-4" onchange="validatePDFSize(this)">

            <h3 class="font-semibold text-lg border-b pb-2 mt-6 mb-4">SEO Settings</h3>

            <label class="block mb-1">Meta Title</label>
            <input type="text" name="meta_title" class="w-full border p-2 rounded mb-3"
                value="<?= htmlspecialchars($blogData['meta_title']) ?>">

            <label class="block mb-1">Meta Description</label>
            <textarea name="meta_description" rows="2" class="w-full border p-2 rounded mb-3"><?= htmlspecialchars($blogData['meta_desc']) ?></textarea>

            <!-- Meta Keywords multi-tag UI (Option A) -->
            <label class="block mb-1 ">Meta Keywords</label>
            <div id="metaKeywordsTags" class="border p-2 rounded mb-2 min-h-[44px]">
                <!-- existing tags will be rendered by JS -->
                <input id="metaKeywordInput" type="text" class="border-0 p-1 outline-none w-full" placeholder="Type keyword and press Enter">
            </div>
            <!-- hidden input that actually gets submitted -->
            <input type="hidden" name="meta_keywords" id="meta_keywords_hidden" value="<?= htmlspecialchars($blogData['meta_keywords']) ?>">

            <label class="block mb-1 font-semibold">Meta Keywords (raw)</label>
            <small class="text-gray-500 block mb-3">Use the tag box above to add / remove keywords. They will be saved as comma-separated keywords.</small>

          

            <div class="mt-6 row">
                <button type="submit" class="bg-primary font-semibold uppercase text-white px-4 py-2 rounded w-auto mr-4">
                    <?= ($id > 0) ? 'Update Blog' : 'Save Blog' ?>
                </button>
                <button type="button" class="bg-gray-500 font-semibold uppercase text-white px-4 py-2 rounded w-auto" onclick="window.location.href='blog_list.php'">
                    Cancel
                </button>
            </div>
        </form>
    </section>
</div>
</main>

<script src="https://cdn.ckeditor.com/4.22.1/full/ckeditor.js"></script>

<script>
    CKEDITOR.replace('content', {
        // IMPORTANT: ensure ONLY selected text font changes
        fullPage: false,
        allowedContent: true,    // keep custom HTML safe
        extraPlugins: 'font,colorbutton,justify',

        // Enable inline styles for selections
        font_defaultLabel: 'Arial',
        fontSize_defaultLabel: '14px',

        // Avoid replacing entire editor content
        removePlugins: 'stylescombo',  // forces inline <span> for selected text

        // Toolbar
        toolbar: [
            { name: 'document', items: [ 'Source' ] },
            { name: 'clipboard', items: [ 'Undo', 'Redo' ] },
            { name: 'styles', items: [ 'Format', 'Font', 'FontSize' ] },
            { name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline' ] },
            { name: 'colors', items: [ 'TextColor', 'BGColor' ] },
            { name: 'paragraph', items: [ 'NumberedList', 'BulletedList', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ] },
            { name: 'insert', items: [ 'Image', 'Table', 'Link', 'Unlink' ] }
        ],

        // Allow inline span for selected-text font styles
        extraAllowedContent: 'span{*}(*);h1 h2 h3 h4 h5 h6',
        
        // Keep paragraph formatting clean
        forcePasteAsPlainText: false,
        enterMode: CKEDITOR.ENTER_P,
        shiftEnterMode: CKEDITOR.ENTER_BR,

        // Prevent CKEditor from modifying entire text
        autoParagraph: true
    });
</script>

<!-- ✅ AUTO SLUG SCRIPT -->
<script>
const titleInput = document.getElementById('titleField');
const slugInput = document.getElementById('slugField');
let slugManuallyEdited = false;

// If user edits slug manually, stop auto-update
slugInput.addEventListener('input', () => slugManuallyEdited = true);

// Auto-update slug when title changes (for new/draft blogs)
titleInput.addEventListener('input', function () {
    const isDraft = <?= ($id <= 0 || $blogData['published'] == 0) ? 'true' : 'false' ?>;
    if (!slugManuallyEdited && isDraft) {
        let slug = this.value
            .toLowerCase()
            .trim()
            .replace(/[^\w\s-]/g, '')   // remove special chars
            .replace(/\s+/g, '-')       // replace spaces with dashes
            .replace(/-+/g, '-');       // remove duplicate dashes
        slugInput.value = slug;
    }
});
</script>

<!-- Video links dynamic add/remove -->
<script>
document.getElementById('addVideoBtn').addEventListener('click', function () {
    const container = document.getElementById('videoLinksContainer');
    const row = document.createElement('div');
    row.className = "flex gap-2 video-row";
    row.innerHTML = `
        <input type="text" name="video_links[]" class="w-full p-2 border rounded" placeholder="YouTube or Video URL">
        <button type="button" class="bg-red-500 text-white px-3 rounded remove-video">X</button>
    `;
    container.appendChild(row);
});

document.addEventListener('click', function (e) {
    if (e.target.classList.contains('remove-video')) {
        const row = e.target.closest('.video-row');
        if (row) row.remove();
    }
});
</script>

<!-- Meta Keywords multi-tag UI -->
<script>
(function() {
    const hiddenInput = document.getElementById('meta_keywords_hidden');
    const tagContainer = document.getElementById('metaKeywordsTags');
    const input = document.getElementById('metaKeywordInput');

    function createTagEl(text) {
        const tag = document.createElement('span');
        tag.className = 'inline-flex items-center bg-gray-200 text-gray-800 px-2 py-1 mr-2 mb-2 rounded';
        tag.style.fontSize = '13px';
        tag.textContent = text;
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'ml-2 text-red-600';
        btn.style.border = 'none';
        btn.style.background = 'transparent';
        btn.style.cursor = 'pointer';
        btn.style.fontSize = '13px';
        btn.textContent = '×';
        btn.addEventListener('click', function () {
            tag.remove();
            syncHidden();
        });
        tag.appendChild(btn);
        return tag;
    }

    function syncHidden() {
        const tags = Array.from(tagContainer.querySelectorAll('span'))
            .filter(el => el !== input.parentElement)
            .map(el => {
                const txt = el.childNodes[0].nodeValue || '';
                return txt.trim();
            })
            .filter(t => t.length > 0);

        hiddenInput.value = tags.join(',');
    }

    const existing = hiddenInput.value ? hiddenInput.value.split(',').map(s => s.trim()).filter(Boolean) : [];
    tagContainer.innerHTML = '';
    existing.forEach(k => {
        const t = createTagEl(k);
        tagContainer.appendChild(t);
    });
    tagContainer.appendChild(input);

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ',') {
            e.preventDefault();
            const val = input.value.trim().replace(/,+$/, '');
            if (!val) return;
            const tagEl = createTagEl(val);
            tagContainer.insertBefore(tagEl, input);
            input.value = '';
            syncHidden();
        } else if (e.key === 'Backspace' && input.value === '') {
            const tags = tagContainer.querySelectorAll('span');
            if (tags.length > 0) {
                const lastTag = tags[tags.length - 1];
                lastTag.remove();
                syncHidden();
            }
        }
    });

    input.addEventListener('blur', function () {
        const val = input.value.trim();
        if (!val) return;
        val.split(',').map(s => s.trim()).filter(Boolean).forEach(v => {
            const tagEl = createTagEl(v);
            tagContainer.insertBefore(tagEl, input);
        });
        input.value = '';
        syncHidden();
    });

    const form = input.closest('form');
    if (form) {
        form.addEventListener('submit', function () {
            syncHidden();
        });
    }
})();

function validatePDFSize(input) {
    if (input.files.length === 0) return;

    let file = input.files[0];
    let max = 5 * 1024 * 1024; // 5MB

    if (file.size > max) {
        alert("PDF size must be 5MB or less.");
        input.value = ""; // clear file
    }
}
</script>
<?php include '_footer.php'; ?>
