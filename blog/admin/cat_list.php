<?php
include './../includes/auth.php';
include './../includes/db.php';

$conn = getDbConnection();
$categories = $conn->query("SELECT * FROM categories ORDER BY id DESC");

// Get newId parameter (if present)
$newId = isset($_GET['newId']) ? intval($_GET['newId']) : 0;

include '_header.php';
?>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<main class="p-6 mt-16 space-y-4">
  <div class="wrapper max-w-4xl mx-auto">
    <div class="flex justify-between items-center">
      <h2 class="text-2xl font-bold"></h2>

      <div class="space-x-3">
        <a href="cat_edit.php" class="bg-primary text-white font-medium px-4 py-2 rounded">
          <i class="fa-solid fa-plus"></i> Add New Category
        </a>
      </div>
    </div>

    <section class="overflow-x-auto bg-white shadow rounded-lg tableWrap">
      <table id="categories-table" class="min-w-full bg-white rounded-lg">
        <thead class="bg-gray-100 text-gray-700 text-sm">
          <tr>
            <th class="p-3 text-left w-40">#</th>
            <th class="p-3 text-left">Name</th>
            <th class="p-3 text-left w-150">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($categories && $categories->num_rows > 0): ?>
            <?php $i = 1; while ($cat = $categories->fetch_assoc()): ?>
              <tr class="border-t hover:bg-gray-50 transition-colors duration-300" id="cat-row-<?= $cat['id'] ?>">
                <td class="p-3 text-gray-600"><?= $i++; ?></td>
                <td class="p-3 font-medium"><?= htmlspecialchars($cat['name']) ?></td>
                <td class="p-3 space-x-2 cta">
                  <a href="cat_edit.php?id=<?= $cat['id'] ?>" 
                     class="px-2 py-1 text-sm hover:text-primary"><i class="fa-regular fa-pen-to-square"></i></a>

                  <button 
                     class="px-2 py-1 text-sm hover:text-primary delete-btn" 
                     data-id="<?= $cat['id'] ?>"><i class="fa-regular fa-trash-can"></i></button>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="4" class="text-center p-4 text-gray-500">No categories found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </section>
  </div>
</main>

<script>
let pendingAction = '';
let pendingCatId = 0;

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

// ✅ DELETE BUTTON — open modal
$(document).on('click', '.delete-btn', function (e) {
    e.preventDefault();
    pendingAction = 'delete';
    pendingCatId = $(this).data('id');
    console.log('🗑 Delete clicked for category ID:', pendingCatId);
    showModal('Are you sure you want to delete this category?');
});

// ✅ YES button — confirm delete
$(document).on('click', '#default-modal button.bg-blue-700', function (e) {
    e.preventDefault();
    if (pendingAction !== 'delete' || !pendingCatId) return;
    const catIdToDelete = pendingCatId; // ✅ Capture before async operations
    fetch('cat_delete.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + encodeURIComponent(pendingCatId)
    })
    .then(res => res.json())
    .then(data => {
        console.log('🧾 Delete response:', data);
        if (data.success) {
            const row = $('#cat-row-' + catIdToDelete);
            if (row.length) {
                row.fadeOut(400, function() {
                    $(this).remove();
                    showToast('Category deleted successfully');
                });
            } else {
                showToast('Category deleted successfully');
            }
        } else {
            showToast('Error: ' + (data.message || 'Unknown error'), 'error');
        }
    })
    .catch(err => {
        console.error('❌ Delete error:', err);
        showToast('Error deleting category', 'error');
    })
    .finally(() => {
        hideModal();
        pendingAction = '';
        pendingCatId = 0;
    });
});

// ✅ CANCEL button or close (X)
$(document).on('click', '#default-modal [data-modal-hide="default-modal"]', function () {
    hideModal();
    pendingAction = '';
    pendingCatId = 0;
});

// ✅ Highlight newly added/edited category
$(document).ready(function () {
    const newId = <?= json_encode($newId) ?>;
    if (newId > 0) {
        const row = $('#cat-row-' + newId);
        if (row.length) {
            row.css({
                backgroundColor: '#fff8c2',
                transition: 'background-color 0.5s ease'
            });
            $('html, body').animate({ scrollTop: row.offset().top - 200 }, 800);
            setTimeout(() => row.css('backgroundColor', ''), 5000);
        }
    }
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

<div class="toasterAlert flex items-center w-full max-w-xs p-4 mb-4 text-gray-500 bg-green-100 rounded-lg shadow-sm dark:text-gray-400 dark:bg-green-800" role="alert">
    <div class="inline-flex items-center justify-center shrink-0 w-8 h-8 text-green-500 rounded-lg dark:bg-green-800 dark:text-green-200">
        <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/>
        </svg>
        <span class="sr-only">Check icon</span>
    </div>
    <div class="ms-3 text-sm font-medium">Category deleted successfully.</div>

    <button type="button" class="ms-auto -mx-1.5 -my-1.5 bg-green-100 text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-green-300 p-1.5 hover:bg-green-200 inline-flex items-center justify-center h-8 w-8 dark:text-gray-500 dark:hover:text-white dark:bg-gray-800 dark:hover:bg-gray-700" data-dismiss-target="#toast-success" aria-label="Close">
        <span class="sr-only">Close</span>
        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
        </svg>
    </button>
</div>
</div>