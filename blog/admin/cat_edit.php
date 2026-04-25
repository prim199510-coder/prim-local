<?php
include './../includes/auth.php';
include './../includes/db.php';

$conn = getDbConnection();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$pageTitle = $id > 0 ? "Edit Category" : "Add Category";
$success = $error = "";

$catData = [
  'name' => '',
  'meta_title' => '',
  'meta_desc' => '',
  'meta_keywords' => ''
];

if ($id > 0) {
  $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($result->num_rows) {
    $catData = $result->fetch_assoc();
  } else {
    $error = "Category not found.";
  }
}

// Helper function to create slug
function generateSlug($text) {
  $slug = strtolower(trim($text));
  $slug = preg_replace('/[^\w\s-]/', '', $slug); // remove special chars
  $slug = preg_replace('/\s+/', '-', $slug);     // spaces → dash
  $slug = preg_replace('/-+/', '-', $slug);      // collapse dashes
  return $slug;
}

// Save / Update
// Save / Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name']);
  $meta_title = trim($_POST['meta_title']);
  $meta_desc = trim($_POST['meta_desc']);
  $meta_keywords = trim($_POST['meta_keywords']);

  $slug = generateSlug($name);

  if ($name) {
    $exists = $conn->prepare("SELECT id FROM categories WHERE (name = ? OR slug = ?) AND id != ?");
    $exists->bind_param("ssi", $name, $slug, $id);
    $exists->execute();
    $exists->store_result();

    if ($exists->num_rows > 0) {
      $error = "Category name already exists. Please choose a different name.";
    } else {
      if ($id > 0) {
        $stmt = $conn->prepare("UPDATE categories SET name=?, slug=?, meta_title=?, meta_desc=?, meta_keywords=? WHERE id=?");
        $stmt->bind_param("sssssi", $name, $slug, $meta_title, $meta_desc, $meta_keywords, $id);
        if ($stmt->execute()) {
          $success = "Category updated successfully.";

          // ✅ Refresh category data to show latest values in form
          $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
          $stmt->bind_param("i", $id);
          $stmt->execute();
          $result = $stmt->get_result();
          if ($result->num_rows) {
            $catData = $result->fetch_assoc();
          }

        } else {
          $error = "Failed to update category.";
        }
      } else {
        $stmt = $conn->prepare("INSERT INTO categories (name, slug, meta_title, meta_desc, meta_keywords) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $slug, $meta_title, $meta_desc, $meta_keywords);
        if ($stmt->execute()) {
          $id = $conn->insert_id;
          $success = "Category added successfully.";
        } else {
          $error = "Failed to add category.";
        }
      }
    }
  } else {
    $error = "Please enter a category name.";
  }
}

include '_header.php';
?>

<main class="p-6 mt-16 space-y-4">
  <div class="wrapper max-w-4xl mx-auto">
    <div class="flex justify-between items-center">
      <h2 class="text-2xl font-bold"></h2>
      <div class="space-x-3">
        <a href="cat_list.php" class="bg-gray-500 text-white font-medium px-4 py-2 rounded">
          <i class="fa-solid fa-arrow-left"></i> Back
        </a>
      </div>
    </div>

    <section class="overflow-x-auto bg-white shadow rounded-lg p-4">
      <?php if ($success): ?>
        <p class="bg-green-100 text-green-700 p-3 mb-4 rounded"><?= htmlspecialchars($success) ?></p>
        <script>
          // Redirect after 1.5 seconds
          setTimeout(() => {
            window.location.href = "cat_list.php?newId=<?= $id ?>";
          }, 1500);
        </script>
      <?php elseif ($error): ?>
        <p class="bg-red-100 text-red-700 p-3 mb-4 rounded"><?= htmlspecialchars($error) ?></p>
      <?php endif; ?>

      <form method="POST">
        <label class="block font-semibold mb-1">Category Name</label>
        <input type="text" name="name" value="<?= htmlspecialchars($catData['name']) ?>" class="w-full border px-3 py-2 rounded mb-4" required>

        <h3 class="font-semibold text-lg border-b pb-2 mt-6 mb-4">SEO Settings</h3>

        <label class="block mb-1">Meta Title</label>
        <input type="text" name="meta_title" value="<?= htmlspecialchars($catData['meta_title']) ?>" class="w-full border p-2 rounded mb-3">

        <label class="block mb-1">Meta Description</label>
        <textarea name="meta_desc" rows="2" class="w-full border p-2 rounded mb-3"><?= htmlspecialchars($catData['meta_desc']) ?></textarea>

        <label class="block mb-1">Meta Keywords</label>
        <textarea name="meta_keywords" rows="2" class="w-full border p-2 rounded mb-3"><?= htmlspecialchars($catData['meta_keywords']) ?></textarea>

        <div class="mt-6 row">
          <button type="submit" class="bg-primary font-semibold uppercase text-white px-4 py-2 rounded w-auto mr-4">
            <?= ($id > 0) ? 'Update Category' : 'Save Category' ?>
          </button>
          <button type="button" class="bg-gray-500 font-semibold uppercase text-white px-4 py-2 rounded w-auto" onclick="window.location.href='cat_list.php'">
            Cancel
          </button>
        </div>
      </form>
    </section>
  </div>
</main>

<?php include '_footer.php'; ?>
