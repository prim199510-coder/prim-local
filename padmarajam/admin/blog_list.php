<?php
include './../includes/auth.php';
include './../includes/db.php';

$conn = getDbConnection();

/**
 * ✅ Logic:
 * 1. Show newly added drafts (parent_id = -1)
 * 2. Show existing drafts for blogs that have a published version
 * 3. Show published blogs that do NOT have a draft
 */
$blogs = $conn->query("
    (
        SELECT 
            b.id AS id,
            b.title AS title,
            b.slug AS slug,
            b.banner_images AS banner_images,
            b.cat_id AS cat_id,
            b.published AS published,
            b.parent_id AS parent_id,
            b.id AS base_id,
            1 AS is_newormodified
        FROM blogs b
        WHERE b.published = 0 AND b.parent_id = -1
    )

    UNION ALL

    (
        SELECT 
            d.id AS id,
            d.title AS title,
            d.slug AS slug,
            d.banner_images AS banner_images,
            d.cat_id AS cat_id,
            d.published AS published,
            d.parent_id AS parent_id,
            b.id AS base_id,
            1 AS is_newormodified
        FROM blogs b
        JOIN blogs d ON d.parent_id = b.id AND d.published = 0
    )

    UNION ALL

    (
        SELECT 
            b.id AS id,
            b.title AS title,
            b.slug AS slug,
            b.banner_images AS banner_images,
            b.cat_id AS cat_id,
            b.published AS published,
            b.parent_id AS parent_id,
            b.id AS base_id,
            0 AS is_newormodified
        FROM blogs b
        WHERE b.published = 1
        AND NOT EXISTS (
            SELECT 1 FROM blogs d WHERE d.parent_id = b.id AND d.published = 0
        )
    )
    ORDER BY id DESC
");

// Fetch categories
$categories = [];
$catResult = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
while ($cat = $catResult->fetch_assoc()) {
    $categories[$cat['id']] = $cat['name'];
}

include '_header.php';
?>

<!-- ✅ DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">

<!-- ✅ jQuery + DataTables JS -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

<main class="p-6 mt-16 space-y-4" id="blogs-container" 
      data-new-id="<?= isset($_GET['new_id']) ? intval($_GET['new_id']) : '' ?>">

<div class="flex justify-between items-center mb-4">
    <h2 class="text-2xl font-bold"></h2>
    <div class="flex space-x-3 items-center">
        <!-- 🔍 Category Filter -->
        <select id="categoryFilter" class="border text-medium font-medium h-10 rounded px-2 py-1 text-sm">
            <option value="">All Categories</option>
            <?php foreach ($categories as $id => $name): ?>
                <option value="<?= htmlspecialchars($name) ?>"><?= htmlspecialchars($name) ?></option>
            <?php endforeach; ?>
        </select>

        <a href="blog_edit.php" class="bg-primary text-white font-medium px-4 py-2 rounded">
            <i class="fa-solid fa-plus"></i> Add New Blog
        </a>
    </div>
</div>

<section class="overflow-x-auto bg-white shadow rounded-lg">
    <table id="blogs-table" class="display nowrap w-full">
        <thead class="bg-gray-100 text-gray-700 text-sm">
            <tr>
                <th class="!text-center w-40">No</th>
                <th class="!text-center w-80">Banner</th>
                <th class="w-2/5">Title</th>
                <th class="w-1/5">Category</th>
                <th class="w-1/5">URL</th>
                <th class="w-1/5">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($blog = $blogs->fetch_assoc()): ?>
                <?php
                    $currentId = $blog['id'];
                    $originalId = $blog['base_id'];
                ?>
                <tr data-id="<?= $currentId ?>" data-base-id="<?= $originalId ?>">
                    <td class="!text-center"><?= $originalId ?></td>
                    <td class="text-center">
                        <?php  $blog_banner_imgs = json_decode($blog['banner_images'] ?? '[]', true);?>
                        <?php if (!empty($blog_banner_imgs)): ?> 
                            <?php $banner_img = $blog_banner_imgs[0] ?? '';?>
                            <img src="../<?= htmlspecialchars($banner_img) ?>" 
                                 alt="Banner" class="w-10 h-10 object-cover rounded mx-auto">
                        <?php else: ?>
                            <img src="../assets/images/place_holder_img.jpg" 
                                 alt="No Image" class="w-10 h-10 object-cover rounded mx-auto">
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($blog['title']) ?></td>
                    <td><?= htmlspecialchars($categories[$blog['cat_id']] ?? 'Uncategorized') ?></td>
                    <td><?= htmlspecialchars($blog['slug']) ?></td>
                    <td class="space-x-2 cta min-w-[250px]">
                        <a href="blog_view.php?id=<?= $currentId ?>" class="px-2 py-1 text-sm hover:text-primary"><i class="fa-regular fa-eye"></i></a>
                        <a href="blog_edit.php?id=<?= $currentId ?>" class="px-2 py-1 text-sm hover:text-primary"><i class="fa-regular fa-pen-to-square"></i></a>
                        <button class="delete-blog px-2 py-1 text-sm hover:text-primary" data-id="<?= $originalId ?>"><i class="fa-regular fa-trash-can"></i></button>
                        <?php if ($blog['is_newormodified']): ?>
                            <button class="publish-blog bg-green text-white px-3 py-1 rounded text-sm hover:bg-primary" data-id="<?= $originalId ?>">Publish</button>
                        <?php elseif (!empty($blog['published']) && $blog['published'] == 1): ?>
                            <button class="published-badge px-3 py-1 rounded text-sm hover:bg-primary cursor-default" disabled>
                                <span class=" font-medium text-sm">Published</span>
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</section>
</main>

<style>
tr.bg-yellow-100 {
  background-color: #fff3cd !important;
  animation: fadeOutHighlight 5s forwards;
}

@keyframes fadeOutHighlight {
  0% { background-color: #fff3cd; }
  100% { background-color: transparent; }
}
</style>
<script>
$(document).ready(function () {
    // ✅ Initialize DataTable
    const table = $('#blogs-table').DataTable({
        responsive: false,
        pageLength: 10,
        order: [[0, 'desc']],
        columnDefs: [{ orderable: false, targets: [1, 5] }],
        language: {
            search: "Search Blogs:",
            lengthMenu: "Show _MENU_ Blogs per page",
            info: "Showing _START_ to _END_ of _TOTAL_ blogs",
            paginate: { previous: "← Prev", next: "Next →" }
        }
    });

    // ✅ Category filter
    $('#categoryFilter').on('change', function () {
        const val = $.fn.dataTable.util.escapeRegex($(this).val());
        table.column(3).search(val ? '^' + val + '$' : '', true, false).draw();
    });

    // ✅ Toast notification helper
    function showToast(message, type = 'success') {
        const toast = $('#toast-success');
        toast.find('.ms-3.text-sm.font-medium').text(message);
        toast.removeClass('bg-green-100 bg-red-100');
        toast.addClass(type === 'success' ? 'bg-green-100' : 'bg-red-100');
        toast.stop(true, true).fadeIn(200);
        setTimeout(() => toast.fadeOut(400), 3200);
    }

    // ✅ Modal helpers
    function showModal(message) {
        $('#default-modal .popupContent .text-center p').text(message);
        $('#default-modal').removeClass('hidden').addClass('flex');
    }

    function hideModal() {
        $('#default-modal').addClass('hidden').removeClass('flex');
    }

    let pendingAction = null;
    let pendingBlogId = null;

    // ✅ Delete Button → show modal
    $(document).on('click', '.delete-blog', function () {
        pendingAction = 'delete';
        pendingBlogId = $(this).data('id');
        showModal('Are you sure you want to delete this blog?');
    });

    // ✅ Publish Button → show modal
    $(document).on('click', '.publish-blog', function () {
        pendingAction = 'publish';
        pendingBlogId = $(this).data('id');
        showModal('Are you sure you want to publish this blog?');
    });

    // ✅ Cancel button
    $(document).on('click', '#default-modal .py-2.5.ms-3, [data-modal-hide="default-modal"]:not(.bg-blue-700)', function () {
        hideModal();
        pendingAction = null;
        pendingBlogId = null;
    });

    // ✅ Confirm “Yes” button
    $(document).on('click', '#default-modal button.bg-blue-700', function (e) {
        e.preventDefault();
        if (!pendingAction || !pendingBlogId) return;

        if (pendingAction === 'delete') {
            fetch('blog_delete.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + encodeURIComponent(pendingBlogId)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const row = $(`button.delete-blog[data-id="${pendingBlogId}"]`).closest('tr');
                    table.row(row).remove().draw(false);
                    showToast('Blog deleted successfully');
                } else {
                    showToast('Error: ' + data.message, 'error');
                }
            })
            .catch(() => showToast('Error deleting blog', 'error'))
            .finally(() => hideModal());
        }

        else if (pendingAction === 'publish') {
            fetch('blog_publish.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + encodeURIComponent(pendingBlogId)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const btnPublish = $(`button.publish-blog[data-id='${pendingBlogId}']`);
                    btnPublish.replaceWith('<button class="bg-gray-100 text-white px-3 py-1 rounded text-sm cursor-default" disabled><span class="text-primary font-medium text-sm">Published</span></button>');
                 
                    const viewLink = $(`a[href='blog_view.php?id=${data.blog_id}']`);
                    console.log("View link before replace:", viewLink, ":::blog id :::", pendingBlogId);
                    viewLink.replaceWith(`<a href="blog_view.php?id=${pendingBlogId}" class="px-2 py-1 text-sm hover:text-primary"><i class="fa-regular fa-eye"></i></a>`);

                    const editLink = $(`a[href='blog_edit.php?id=${data.blog_id}']`);
                    console.log("View link before replace:", viewLink, ":::blog id :::", pendingBlogId);
                    editLink.replaceWith(`<a href="blog_edit.php?id=${pendingBlogId}" class="px-2 py-1 text-sm hover:text-primary"><i class="fa-regular fa-pen-to-square"></i></a>`);
                    showToast('Blog published successfully');
                } else {
                    showToast('Error: ' + data.message, 'error');
                }
            })
            .catch(() => showToast('Error publishing blog', 'error'))
            .finally(() => hideModal());
        }
    });

    // ✅ Ensure highlight logic runs only after DataTable is fully ready
const newId = $('#blogs-container').data('new-id');

if (newId && newId !== "") {
 //   console.log("Highlight target ID:", newId); // debugging

    function highlightRow() {
        const row = $(`tr[data-id='${newId}']`);
        if (row.length) {
            console.log("Row found:", row);
            row.addClass('bg-yellow-100');
            $('html, body').animate({ scrollTop: row.offset().top - 200 }, 800);
            setTimeout(() => row.removeClass('bg-yellow-100'), 4000);
        } else {
            console.log("Row not found yet, retrying...");
            setTimeout(highlightRow, 300); // retry until found
        }
    }

    // Run once DataTables fully initialized
    table.on('init', function () {
        highlightRow();
    });

    // Also reapply after any redraw (sorting / pagination)
    table.on('draw', function () {
        highlightRow();
    });

    // As a fallback, try once after 1s (in case DataTables init is delayed)
    setTimeout(highlightRow, 1000);
}



    // ✅ Manual close for toast
    $(document).on('click', '[data-dismiss-target="#toast-success"]', function () {
        $('#toast-success').fadeOut();
    });
});
</script>
<?php include '_footer.php'; ?>

<!-- Popup modal -->
<div id="default-modal" tabindex="-1" aria-hidden="true" class="popupModel hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-xl max-h-full popupContent">
        <!-- Modal content -->
        <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
            <!-- Modal header -->
            <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                    Alert
                </h3>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="default-modal">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            <!-- Modal body -->
            <div class="p-4 md:p-5 space-y-4 text-center">
                <p class="text-base font-medium leading-relaxed text-gray-500 dark:text-gray-400">
                  Are you sure you want to delete this blog?
                </p>
               
            </div>
            <!-- Modal footer -->
            <div class="flex justify-center items-center p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600">
                <button data-modal-hide="default-modal" type="button" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Yes</button>
                <button data-modal-hide="default-modal" type="button" class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">Cancel</button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notification -->

<div id="toast-success" class="hidden">   

<div class="toasterAlert flex items-center w-full max-w-xs p-4 mb-4 text-gray-500 bg-green-100 rounded-lg shadow-sm dark:text-gray-400 dark:bg-green-800 top-2" role="alert">
    <div class="inline-flex items-center justify-center shrink-0 w-8 h-8 text-green-500 rounded-lg dark:bg-green-800 dark:text-green-200">
        <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/>
        </svg>
        <span class="sr-only">Check icon</span>
    </div>
    <div class="ms-3 text-sm font-medium">Blog deleted successfully.</div>

    <button type="button" class="ms-auto -mx-1.5 -my-1.5 bg-green-100 text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-green-300 p-1.5 hover:bg-green-200 inline-flex items-center justify-center h-8 w-8 dark:text-gray-500 dark:hover:text-white dark:bg-gray-800 dark:hover:bg-gray-700" data-dismiss-target="#toast-success" aria-label="Close">
        <span class="sr-only">Close</span>
        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
        </svg>
    </button>
</div>
</div>



