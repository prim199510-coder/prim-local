<?php
include './../includes/auth.php';
include './../includes/db.php';
$conn = getDbConnection();

// Get blog slug from URL (SEO-friendly)
$slug = $_GET['slug'] ?? '';
if (empty($slug)) {
    echo "<p class='text-center text-red-500 mt-20'>Invalid blog URL.</p>";
    exit;
}

// Fetch blog details with category name
$stmt = $conn->prepare("
    SELECT b.*, c.name AS category_name 
    FROM blogs b
    LEFT JOIN categories c ON b.cat_id = c.id
    WHERE b.slug = ? 
");
$stmt->bind_param("s", $slug);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<p class='text-center text-red-600 mt-20'>Blog not found or unpublished.</p>";
    exit;
}

$blog = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($blog['meta_title'] ?: $blog['title']) ?></title>
<meta name="description" content="<?= htmlspecialchars($blog['meta_desc'] ?: '') ?>">
<meta name="keywords" content="<?= htmlspecialchars($blog['meta_keywords'] ?: '') ?>">
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800">

<!-- Header -->
<header class="bg-white shadow">
  <div class="max-w-6xl mx-auto px-6 py-4 flex justify-between items-center">
    <a href="/" class="text-2xl font-bold text-blue-600">MyBlogSite</a>
    <nav class="space-x-6 text-gray-700 font-medium">
      <a href="/" class="hover:text-blue-600">Home</a>
      <a href="/blogs.php" class="hover:text-blue-600">Blogs</a>
      <a href="/about.php" class="hover:text-blue-600">About</a>
      <a href="/contact.php" class="hover:text-blue-600">Contact</a>
    </nav>
  </div>
</header>

<!-- Blog Container -->
<main class="max-w-4xl mx-auto px-6 py-10 bg-white mt-8 rounded-lg shadow-sm">
  <!-- Title -->
  <h1 class="text-4xl font-bold text-gray-900 mb-2">
    <?= htmlspecialchars($blog['title']) ?>
  </h1>

  <!-- Meta Info -->
  <div class="text-sm text-gray-500 mb-6">
    <span>Category: <strong><?= htmlspecialchars($blog['category_name'] ?? 'Uncategorized') ?></strong></span>
    <span class="mx-2">•</span>
    <span>Published: <?= date('M d, Y', strtotime($blog['created_at'])) ?></span>
  </div>

  <!-- Banner -->
  <?php if (!empty($blog['banner_images'])): ?>
    <img src="<?= htmlspecialchars($blog['banner_images']) ?>" alt="Blog Banner"
         class="w-full max-h-96 object-cover rounded-lg shadow mb-8">
  <?php endif; ?>

  <!-- Description -->
  <?php if (!empty($blog['meta_desc'])): ?>
  <p class="text-lg text-gray-700 leading-relaxed mb-6 italic">
    <?= htmlspecialchars($blog['meta_desc']) ?>
  </p>
  <?php endif; ?>

  <!-- Main Content -->
  <article class="prose max-w-none prose-lg text-gray-800">
    <?= $blog['content'] /* Display HTML content safely */ ?>
  </article>

  <!-- Tags -->
  <?php if (!empty($blog['tags'])): ?>
  <div class="mt-8 border-t pt-4">
    <p class="text-sm text-gray-600">
      <strong>Tags:</strong>
      <?php foreach (explode(',', $blog['tags']) as $tag): ?>
        <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full mr-2">
          <?= htmlspecialchars(trim($tag)) ?>
        </span>
      <?php endforeach; ?>
    </p>
  </div>
  <?php endif; ?>

  <!-- Back to Blogs -->
  <div class="mt-10 text-center">
    <a href="/blogs.php" class="inline-block bg-blue-600 text-white px-5 py-2 rounded hover:bg-blue-700 transition">
      ← Back to Blogs
    </a>
  </div>
</main>

<!-- Footer -->
<footer class="bg-gray-800 text-gray-300 mt-16 py-6">
  <div class="max-w-6xl mx-auto px-6 text-center">
    <p>© <?= date('Y') ?> MyBlogSite. All rights reserved.</p>
  </div>
</footer>

</body>
</html>
