<?php
require_once '../includes/db.php';
$conn = getDbConnection();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $oid = (int)$_POST['order_id'];
    $status = $conn->real_escape_string($_POST['status']);
    $conn->query("UPDATE orders SET status = '$status' WHERE id = $oid");
	header("Location: dashboard.php?statusupdated=1");
	exit;
	}

// Fetch metrics
$blogCount   = (int)$conn->query("SELECT COUNT(*) AS count FROM blogs WHERE parent_id = -1")->fetch_assoc()['count'];
$categoryCount = (int)$conn->query("SELECT COUNT(*) AS count FROM categories")->fetch_assoc()['count'];


// Fetch recent blogs
$blogs = $conn->query("
    SELECT b.*,
        (b.published = 0 OR EXISTS (
            SELECT 1 FROM blogs d WHERE d.parent_id = b.id AND d.published = 0
        )) AS is_newormodified
    FROM blogs b
    WHERE (b.published = 1 OR b.parent_id = -1)
    ORDER BY b.id DESC LIMIT 10");

// Fetch all categories and store them in an associative array for quick lookup
$categories = [];
$catResult = $conn->query("SELECT id, name FROM categories");
while ($cat = $catResult->fetch_assoc()) {
    $categories[$cat['id']] = $cat['name'];
}

// Include shared header
include '_header.php';
?>

<main class="p-6 mt-16 space-y-4">
<div class="wrapper">
			
             <!-- KPI -->

             <section class="grid sm:grid-cols-2 gap-6">
                <div class="flex items-center p-4 sm:p-8 bg-white shadow rounded-lg">
                    <div
                        class="inline-flex flex-shrink-0 items-center justify-center h-16 w-16 text-purple-600 bg-purple-100 rounded-full mr-6">
                     <i class="fa-solid fa-blog text-2xl"></i>
                    </div>
                    <div>
                        <span class="block text-2xl font-bold"><?= $blogCount ?></span>
                        <span class="block text-gray-500">Total Blogs</span>
                    </div>
                </div>
                <div class="flex items-center p-4 sm:p-8 bg-white shadow rounded-lg">
                    <div
                        class="inline-flex flex-shrink-0 items-center justify-center h-16 w-16 text-green-600 bg-green-100 rounded-full mr-6">
                         <i class="fa-solid fa-layer-group text-2xl"></i>
                    </div>
                    <div>
                        <span class="block text-2xl font-bold"><?= $categoryCount ?></span>
                        <span class="block text-gray-500">Total Categories</span>
                    </div>
                </div>
               
            </section>

             <!-- Recent Orders -->

             <section class="grid grid-cols-1 gap-6">
                <div class="flex flex-col md:col-span-2 md:row-span-2 bg-white shadow rounded-lg">
                    <div class="flex justify-between items-center px-6 py-3 border-b border-gray-100">
                    <h2 class="text-xl uppercase">Recent Blogs</h2>
                    <a href="blog_list.php" class="bg-primary text-white px-3 py-1 rounded viewall-btn text-sm">View All</a>

                    </div>
                    

                    <div class="flex-grow recent_blogs_table overflow-x-auto">
                       <table class="min-w-full bg-white shadow rounded">
                          <thead>
                            <tr class="bg-gray-200 text-left text-sm">
                               <th class="!text-center w-40">No</th>
                                <th class="!text-center w-80">Banner</th>
                                <th class="w-2/5">Title</th>
                                <th class="w-1/5">Category</th>
                                <th class="w-1/5">URL</th>
                                <th class="w-1/5 min-w-[200px]">Actions</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php while ($blog = $blogs->fetch_assoc()): ?>
                              <tr class="border-t hover:bg-gray-50">
                                 <td class="p-3"><?= $blog['id'] ?></td>
                                 <td class="p-3">
                                      <?php  $blog_banner_imgs = json_decode($blog['banner_images'] ?? '[]', true);?>
                                        <?php if (!empty($blog_banner_imgs)): ?> 
                                            <?php $banner_img = $blog_banner_imgs[0] ?? '';?>
                                            <img src="../<?= htmlspecialchars($banner_img) ?>" alt="Banner" class="w-10 h-10 object-cover rounded">
                                        <?php else: ?>
                                            <img src="../assets/images/place_holder_img.jpg" alt="No Image" class="w-10 h-10 object-cover rounded">
                                        <?php endif; ?>
                                  </td>
                                  <td class="p-3 font-medium"><?= htmlspecialchars($blog['title']) ?></td>
                                  <td class="p-3"><?= htmlspecialchars( $categories[$blog['cat_id']]) ?></td>
                                  <td class="p-3"><?= htmlspecialchars($blog['slug']) ?></td>
                                 

                                  <td class="p-3 space-x-2 cta">
                           <a href="blog_view.php?id=<?= $blog['id'] ?>"
                               class="px-2 py-1 text-sm hover:text-primary"><i class="fa-regular fa-eye"></i></a>

                           <a href="blog_edit.php?id=<?= $blog['id'] ?>"
                               class="px-2 py-1 text-sm hover:text-primary"><i class="fa-regular fa-pen-to-square"></i></a>

                            <button class="delete-blog px-2 py-1 text-sm hover:text-primary"
                                    data-id="<?= $blog['id'] ?>"><i class="fa-regular fa-trash-can"></i></button>

                           
                        </td>

                              </tr>
                            <?php endwhile; ?>
                          </tbody>
                        </table>

                    </div>
                </div>
            </section>

                                      </div>     			
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const deleteButtons = document.querySelectorAll('.delete-blog');
  deleteButtons.forEach(btn => {
      btn.addEventListener('click', function() {
          const blogId = this.dataset.id;
          if (!confirm('Are you sure you want to delete this blog?')) return;

          fetch('blog_delete.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
              body: 'id=' + encodeURIComponent(blogId)
          })
          .then(res => res.json())
          .then(data => {
              if (data.success) {
                  btn.closest('tr').remove();
                  alert('Blog deleted successfully.');
              } else {
                  alert('Error: ' + data.message);
              }
          }).catch(err => { alert('Error deleting blog'); console.error(err); });
      });
  });
  });

	function closeModal() {
	document.getElementById('orderModal').classList.add('hidden');
	}

	document.querySelectorAll('.view-btn').forEach(btn => {
	  btn.addEventListener('click', function () {
	/*	const orderId = this.dataset.orderId;
		const orderStatus = this.dataset.status;

		document.getElementById('modalOrderId').innerText = orderId;
		document.getElementById('formOrderId').value = orderId;
		document.querySelector('select[name="status"]').value = orderStatus;

		document.getElementById('orderModal').classList.remove('hidden');

		fetch('get_order_details.php?order_id=' + orderId)
		  .then(res => res.text())
		  .then(html => {
			document.getElementById('orderDetails').innerHTML = html;
		  });*/
	  });
	});
	//
	// order status modal handlers
	//
	function closeStatusModal() {
	  document.getElementById('statusModal').classList.add('hidden');
	  // Remove query string from URL without reloading
	  const url = new URL(window.location);
	  url.searchParams.delete('statusupdated');
	  window.history.replaceState({}, document.title, url.pathname);
	}

	window.addEventListener('DOMContentLoaded', () => {
	  const urlParams = new URLSearchParams(window.location.search);
	  if (urlParams.get('statusupdated') === '1') {
		document.getElementById('statusModal').classList.remove('hidden');
	  }
	});
</script>

<?php include '_footer.php'; ?>


