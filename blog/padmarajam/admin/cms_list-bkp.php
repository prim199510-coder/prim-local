<?php
include './../includes/auth.php';
include './../includes/db.php';

$conn = getDbConnection();
$pages = $conn->query("SELECT * FROM cms_pages ORDER BY id DESC");
include '_header.php';
?>

<main class="p-6 mt-16 space-y-4">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">CMS Pages</h2>
        <div class="space-x-3">           
            <a href="cms_edit.php" class="bg-primary text-white font-medium px-4 py-2 rounded">
                + Create New Page
            </a>
        </div>
    </div>

    <section class="overflow-x-auto bg-white shadow rounded-lg">
    <table id="order-table" class="min-w-full bg-white rounded-lg">
      <thead class="bg-gray-100 text-gray-700  text-sm">
        <tr>
          <th class="p-3 text-left">Title</th>
          <th class="p-3 text-left">Url</th>
          <th class="p-3 text-left">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($page = $pages->fetch_assoc()): ?>
        <tr class="border-t">
          <td class="p-3"><?= htmlspecialchars($page['title']) ?></td>
          <td class="p-3"><?= htmlspecialchars($page['slug']) ?></td>
          <td class="p-3">
            <a href="cms_edit.php?id=<?= $page['id'] ?>" class="bg-gray-600 text-white px-3 py-1 rounded view-btn text-sm"> Edit</a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
    </section>
</main>
<?php include '_footer.php'; ?>