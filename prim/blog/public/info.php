<?php
// info.php
session_start();
include './../includes/db.php';
$conn = getDbConnection();

// Get slug from URL (e.g., ?page=about)
$slug = $_GET['page'] ?? '';

// Prepare and execute query securely
$stmt = $conn->prepare("SELECT title, content FROM cms_pages WHERE slug = ?");
$stmt->bind_param("s", $slug);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Show 404 if not found
    http_response_code(404);
    echo "<h1>404 - Page Not Found</h1>";
    exit;
}

$page = $result->fetch_assoc();
include '_header.php';
?>



<main class="container text-gray-800 mt-8">
  <div class="mx-auto px-4">
   <h1 class="text-3xl font-bold mb-6"><?= htmlspecialchars($page['title']) ?></h1>
    <div class="bg-white p-6 rounded shadow leading-relaxed text-gray-700">
      <?= nl2br(htmlspecialchars($page['content'])) ?>
    </div>
  </div>
</main>



<?php include '_footer.php'; ?>
