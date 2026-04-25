<?php
include './../includes/auth.php';
include './../includes/db.php';

$conn = getDbConnection();

// Validate and get blog ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: blogs_list.php");
    exit();
}

$blog_id = intval($_GET['id']);

// Fetch blog details with category name
$stmt = $conn->prepare("
    SELECT b.*, c.name AS category_name 
    FROM blogs b 
    LEFT JOIN categories c ON b.cat_id = c.id 
    WHERE b.id = ?
");
$stmt->bind_param("i", $blog_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<p class='text-center text-red-600 mt-10'>Blog not found.</p>";
    exit();
}

$blog = $result->fetch_assoc();

// Set fallback meta values if not present
$meta_title = !empty($blog['meta_title']) ? htmlspecialchars($blog['meta_title']) : htmlspecialchars($blog['title']);
$meta_desc = !empty($blog['meta_desc']) ? htmlspecialchars($blog['meta_desc']) : substr(strip_tags($blog['content']), 0, 160);
$meta_keywords = !empty($blog['meta_keywords']) ? htmlspecialchars($blog['meta_keywords']) : htmlspecialchars($blog['tags'] ?? '');
// Normalize banner image (may be JSON array, comma-separated or raw string)
$bannerPath = '';
if (!empty($blog['banner_images'])) {
    $bi = $blog['banner_images'];
    $decoded = json_decode($bi, true);
    if (is_array($decoded) && count($decoded) > 0) {
        $first = $decoded[0];
    } elseif (is_array($decoded) && empty($decoded)) {
        $first = '';
    }
    else {
        // fallback: comma-separated list or raw string
        $first = strtok($bi, ',');
    }

    if (!empty($first)) {
        $first = str_replace('\\/', '/', $first);
        $first = ltrim($first, './');
        // web path used in admin area should point up from admin/ -> ../<path>
        $candidateWeb = '../' . $first;

        // Build filesystem path to check existence
        $fsCandidate = $_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($first, '/');
        if (@file_exists($fsCandidate)) {
            $bannerPath = $candidateWeb;
        } else {
            // try also if the value already contains a leading ../ or /
            $alt = $first;
            $fsAlt = $_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($alt, '/');
            if (@file_exists($fsAlt)) {
                $bannerPath = '../' . $alt;
            } else {
                // fallback to using the candidate web path even if file_exists fails (common in non-prod)
                $bannerPath = $candidateWeb;
            }
        }
    }
}

if (empty($bannerPath)) {
    $bannerPath = '../assets/images/place_holder_img.jpg';
}

$meta_image = htmlspecialchars($bannerPath);

?>
<?php include '_header.php'; ?>

<main class="p-6 mt-16">
    <div class="wrapper">
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800"><?= htmlspecialchars($blog['title']) ?></h2>
        
        <div class="space-x-3">
            <a href="blog_list.php" class="bg-gray-500 text-white font-medium px-4 py-2 rounded"><i class="fa-solid fa-arrow-left"></i> Back</a>
        </div>
    </div>

    <section class="bg-white blogView shadow rounded-lg p-6 space-y-4">
        <!-- Blog Banner -->
        <div class="flex justify-center mb-4 blogView_banner">
            <img src="<?= $meta_image ?>" 
                 alt="<?= htmlspecialchars($blog['title']) ?>" 
                 class="rounded-lg max-h-64 w-full object-cover shadow">
        </div>

        <!-- Blog Details -->
        <div class="space-y-2">
            <p class="text-sm text-gray-600"><strong>URL:</strong> <?= htmlspecialchars($blog['slug']) ?></p>
            <p class="text-sm text-gray-600"><strong>Category:</strong> <?= htmlspecialchars($blog['category_name'] ?? 'Uncategorized') ?></p>
        </div>

        <!-- Blog Description -->
        <div class="mt-6 border-t pt-4">
            <h4 class="text-lg font-semibold text-gray-800 mb-2">Content</h4>
            <div class="prose max-w-none">
                <?= $blog['content'] ?>
            </div>
        </div>

        <!-- Blog Tags -->
        <?php if (!empty($blog['tags'])): ?>
            <div class="mt-6 border-t pt-4">
                <h4 class="text-lg font-semibold text-gray-800 mb-2">Tags</h4>
                <div class="flex flex-wrap gap-2">
                    <?php 
                        $tags = array_map('trim', explode(',', $blog['tags']));
                        foreach ($tags as $tag): 
                    ?>
                        <a href="#" 
                           class="bg-gray-200 text-gray-700 text-sm px-3 py-1 rounded-full hover:bg-gray-300">
                           <?= htmlspecialchars($tag) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Action Buttons -->
         <div class="mt-6 row border-t pt-5">
            <a href="blog_edit.php?id=<?= $blog['id'] ?>" 
               class="bg-primary font-semibold uppercase text-white px-4 py-2 rounded w-auto mr-4">Edit</a>
            <a href="blog_delete.php?id=<?= $blog['id'] ?>" 
               onclick="return confirm('Are you sure you want to delete this blog?');"
               class="bg-red-400 font-semibold uppercase text-white px-4 py-2 rounded w-auto">Delete</a>
        </div>

        




    </section>
    </div>
</main>

<?php include '_footer.php'; ?>

