<?php
// category.php
// Single-file page that supports normal loads and AJAX (search/sort/pagination).
include './../includes/db.php';
$conn = getDbConnection();

// Validate slug
if (!isset($_GET['slug']) || empty($_GET['slug'])) {
    header("Location: index.php");
    exit();
}

$slug = $_GET['slug'];

// Fetch category
$cat_stmt = $conn->prepare("SELECT * FROM categories WHERE slug = ?");
$cat_stmt->bind_param("s", $slug);
$cat_stmt->execute();
$cat_result = $cat_stmt->get_result();
$category = $cat_result->fetch_assoc();
if (!$category) {
    echo "<h2 class='text-center text-red-500 mt-10'>Category not found.</h2>";
    exit();
}
$cat_id = (int)$category['id'];

// Shared settings for layout / assets
$projectRoot = rtrim(str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME']))), '/');
$assetBase = $projectRoot . '/assets/frontend/css';
$settingsRes = $conn->query("SELECT * FROM settings LIMIT 1");
$settings = $settingsRes ? $settingsRes->fetch_assoc() : [];
$logoUrl = !empty($settings['logo']) ? $projectRoot . '/uploads/' . $settings['logo'] : $projectRoot . '/public/images/logo-light.svg';

// ---- Parameters (search, page, limit, sort) ----
// Accept both GET (normal) and AJAX GET requests
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page   = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit  = isset($_GET['limit']) ? max(1, (int)$_GET['limit']) : 9; // default per-page
$sort   = isset($_GET['sort']) ? $_GET['sort'] : 'newest'; // newest or oldest

// Validate sort
$allowedSort = ['newest','oldest'];
if (!in_array($sort, $allowedSort)) $sort = 'newest';

$offset = ($page - 1) * $limit;

// Build search sql and params
$searchSql = "";
$params = [$cat_id];
$paramTypes = "i";
if ($search !== "") {
    // We'll search title and description
    $searchSql = " AND (title LIKE CONCAT('%', ?, '%') OR description LIKE CONCAT('%', ?, '%')) ";
    $params[] = $search;
    $params[] = $search;
    $paramTypes .= "ss";
}

// Sorting
$orderBy = ($sort === 'oldest') ? "created_at ASC" : "created_at DESC";

// Count total
$countSql = "SELECT COUNT(*) AS total FROM blogs WHERE published != 0 AND cat_id = ? {$searchSql}";
$countStmt = $conn->prepare($countSql);
if ($search !== "") {
    $countStmt->bind_param($paramTypes, ...$params);
} else {
    $countStmt->bind_param("i", $cat_id);
}
$countStmt->execute();
$total = (int)$countStmt->get_result()->fetch_assoc()['total'];
$totalPages = ($total === 0) ? 1 : (int)ceil($total / $limit);

// Fetch results for current page
$fetchSql = "SELECT * FROM blogs WHERE published != 0 AND cat_id = ? {$searchSql} ORDER BY {$orderBy} LIMIT ? OFFSET ?";
$paramsForFetch = $params;
$paramTypesForFetch = $paramTypes . "ii";
$paramsForFetch[] = $limit;
$paramsForFetch[] = $offset;

$stmt = $conn->prepare($fetchSql);
$stmt->bind_param($paramTypesForFetch, ...$paramsForFetch);
$stmt->execute();
$blogs = $stmt->get_result();

// Helper to render blog grid HTML
function render_blog_grid($blogs_result, $projectRoot, $categorySlug) {
    ob_start();
    if ($blogs_result->num_rows > 0) {
        echo '<div class="wc qf pn xo zf iq">';
        while ($blog = $blogs_result->fetch_assoc()) {
            // banner_images may be stored as JSON or string - handle both
            $blogBanner = '';

            if (!empty($blog['banner_images'])) {
                // if JSON, decode; else assume string path
                $tmp = json_decode($blog['banner_images'], true);
                if (is_array($tmp) && count($tmp) > 0) {
                    $blogBanner = $tmp[0];
                } 
            }
            if (empty($blogBanner)) {
                $blogBanner = './assets/images/place_holder_img.jpg';
            } else {
                $blogBanner = htmlspecialchars($blogBanner);
            }
            $blogUrl = htmlspecialchars($categorySlug . '/' . $blog['slug']);
            $dateStr = !empty($blog['created_at']) ? date('d M, Y', strtotime($blog['created_at'])) : '';

            // Determine display author: prefer 'author', then 'author_name', else fallback.
            $displayAuthor = 'Jagan Karthik';
            if (isset($blog['author']) && trim($blog['author']) !== '') {
              $displayAuthor = $blog['author'];
            } elseif (isset($blog['author_name']) && trim($blog['author_name']) !== '') {
              $displayAuthor = $blog['author_name'];
            }
            ?>
            <div class="blogItem animate_top sg vk rm xm">
              <div class="c rc i z-1 pg">
                <img class="w-full blogIimg" src="<?= $blogBanner ?>" alt="<?= htmlspecialchars($blog['title']) ?>" />
                <div class="im h r s df vd yc wg tc wf xf al hh/20 nl il z-10">
                  <a href="<?= $blogUrl ?>" class="vc ek rg lk gh sl ml il gi hi">Read More</a>
                </div>
              </div>
              <div class="yh">
                <div class="tc uf wf ag jq">
                  <div class="tc wf ag">
                    <img src="<?= htmlspecialchars($projectRoot . '/public/images/icon-man.svg') ?>" alt="User" />
                    <p><?= htmlspecialchars($displayAuthor) ?></p>
                  </div>
                  <div class="tc wf ag">
                    <img src="<?= htmlspecialchars($projectRoot . '/public/images/icon-calender.svg') ?>" alt="Calender" />
                    <p><?= htmlspecialchars($dateStr) ?></p>
                  </div>
                </div>
                <h4 class="ek tj ml il kk wm xl eq lb">
                  <a href="<?= $blogUrl ?>"><?= htmlspecialchars($blog['title']) ?></a>
                </h4>
              </div>
            </div>
            <?php
        }
        echo '</div>';
    } else {
        echo '<p class="text-center text-gray-500 mt-10 no-results-block">No blogs found under this category.</p>';
    }
    return ob_get_clean();
}

// Helper to render pagination HTML
function render_pagination($slug, $search, $page, $totalPages, $sort, $limit) {
    ob_start();
    if ($totalPages > 1) {
        echo '<div class="mt-10 flex justify-center space-x-2 pagination-wrapper">';
        if ($page > 1) {
            $prev = $page - 1;
            echo '<button data-page="'. $prev .'" class="ajax-page px-4 py-2 bg-gray-200 rounded hover:bg-gray-300"><svg class="th lm ml il" width="8" height="14" viewBox="0 0 8 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M2.93884 6.99999L7.88884 11.95L6.47484 13.364L0.11084 6.99999L6.47484 0.635986L7.88884 2.04999L2.93884 6.99999Z"></path></svg></button>';
        }
        // simple numeric pages (you can improve windowing if needed)
        for ($i = 1; $i <= $totalPages; $i++) {
            $active = ($i === $page) ? 'active text-white' : 'bg-gray-200 hover:bg-gray-300';
            echo '<button data-page="'. $i .'" class="ajax-page px-4 py-2 rounded ' . $active . '">' . $i . '</button>';
        }
        if ($page < $totalPages) {
            $next = $page + 1;
            echo '<button data-page="'. $next .'" class="ajax-page px-4 py-2 bg-gray-200 rounded hover:bg-gray-300"><svg class="th lm ml il" width="8" height="14" viewBox="0 0 8 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5.06067 7.00001L0.110671 2.05001L1.52467 0.636014L7.88867 7.00001L1.52467 13.364L0.110672 11.95L5.06067 7.00001Z" fill="#fefdfo"></path></svg></button>';
        }
        echo '</div>';
    }
    return ob_get_clean();
}

// If this is an AJAX request, return JSON fragments
$isAjax = (isset($_GET['ajax']) && $_GET['ajax'] == '1') || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

if ($isAjax) {
    // We need to re-run the fetch because earlier $blogs result may be consumed
    // Rebuild stmt for current request (same as above)
    $stmt2 = $conn->prepare($fetchSql);
    $stmt2->bind_param($paramTypesForFetch, ...$paramsForFetch);
    $stmt2->execute();
    $blogs2 = $stmt2->get_result();

    // Render fragments
    $gridHtml = render_blog_grid($blogs2, $projectRoot, $slug);
    $paginationHtml = render_pagination($slug, $search, $page, $totalPages, $sort, $limit);

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => true,
        'gridHtml' => $gridHtml,
        'paginationHtml' => $paginationHtml,
        'total' => $total,
        'page' => $page,
        'totalPages' => $totalPages
    ]);
    exit;
}

// Normal page render (non-AJAX)
$gridHtmlInitial = render_blog_grid($blogs, $projectRoot, $slug);
$paginationHtmlInitial = render_pagination($slug, $search, $page, $totalPages, $sort, $limit);

?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <title><?= htmlspecialchars($category['meta_title'] ?: $category['name']) ?> - Blog</title>
    <meta name="description" content="<?= htmlspecialchars(substr(strip_tags($category['description']), 0, 160)) ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="description" content="<?= htmlspecialchars($category['meta_desc'] ?: substr(strip_tags($category['description']),0,160)) ?>">
    <meta name="keywords" content="<?= htmlspecialchars($category['meta_keywords']) ?>">

    
     <script src="https://cdn.tailwindcss.com"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

  <link rel="stylesheet" href="<?= htmlspecialchars($assetBase) ?>/style.css">
  <link rel="icon" type="image/png" href="<?= htmlspecialchars($projectRoot) ?>/public/images/favicon.ico">
  <style>
    /* small adjustments */
    .no-results-block { padding: 40px 0; }
    .ajax-controls { gap: 8px; }
    .loading-overlay {
      position: absolute; inset:0; display:flex; align-items:center; justify-content:center;
      background: rgba(255,255,255,0.6); z-index: 50; border-radius:6px;
    }
  </style>
</head>

<body x-data="{ page: 'home', 'darkMode': true, 'stickyMenu': false, 'navigationOpen': false, 'scrollTop': false }"
  x-init="
         darkMode = JSON.parse(localStorage.getItem('darkMode'));
         $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)))"
  :class="{'b eh': darkMode === true}">

  <header class="g s r vd ya" :class="{ 'hh sm _k bl ll' : stickyMenu }"
    @scroll.window="stickyMenu = (window.pageYOffset > 20) ? true : false">
    <div class="bb ze ki xn 2xl:ud-px-0 oo wf yf i menusection">
      <div class=" tc wf yf py-2">
        <a href="/" class="brandLogo">
            <img class="om" src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo" />
           
        </a>

        <!-- Hamburger Toggle BTN -->
        <button class="po rc" @click="navigationOpen = !navigationOpen">
          <span class="rc i pf re pd">
            <span class="du-block h q vd yc">
              <span class="rc i r s eh um tg te rd eb ml jl dl" :class="{ 'ue el': !navigationOpen }"></span>
              <span class="rc i r s eh um tg te rd eb ml jl fl" :class="{ 'ue qr': !navigationOpen }"></span>
              <span class="rc i r s eh um tg te rd eb ml jl gl" :class="{ 'ue hl': !navigationOpen }"></span>
            </span>
            <span class="du-block h q vd yc lf">
              <span class="rc eh um tg ml jl el h na r ve yc" :class="{ 'sd dl': !navigationOpen }"></span>
              <span class="rc eh um tg ml jl qr h s pa vd rd" :class="{ 'sd rr': !navigationOpen }"></span>
            </span>
          </span>
        </button>
        <!-- Hamburger Toggle BTN -->
      </div>

      <div class="sd qo f ho oo wf yf" :class="{ 'd hh rm sr td ud qg ug jc yh': navigationOpen }">
        <nav>
          <ul class="tc _o sf yo cg ep">
            <li><a href="https://www.padmarajam.org/" class="xl" >Home</a></li>
            <li><a href="https://www.padmarajam.org/#about-section" class="xl">About Us</a></li>
            <li><a href="https://www.padmarajam.org/courses" class="xl">Courses</a></li>
            <li><a href="https://www.padmarajam.org/blog" class="xl mk">Blogs</a></li>
            <li><a href="https://www.padmarajam.org/#gallery-section" class="xl">Gallery</a></li>
            <li><a href="https://www.padmarajam.org/#contact" class="xl">Contact Us</a></li>
           

           

          </ul>
        </nav>

       
      </div>
    </div>
  </header>
  
  




 <main>
     <section class="ji gp uq mainSection">
      <div class="bb ze ki xn vq jb jo">

        <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center space-y-3 md:space-y-0">
            <h2 class="text-2xl font-semibold text-gray-700"><?= htmlspecialchars($category['name']) ?></h2>
<div class="jn/2 so">
            <div class="flex items-center ajax-controls animate_top">
                <!-- Search -->
                <input id="ajaxSearch" type="text" name="search" placeholder="Search blogs..."
                       value="<?= htmlspecialchars($search) ?>"
                       class="vd sm _g ch pm vk xm rg gm dm/40 dn/40 li mi" />

                       <button class="h r q _h" disabled>
                    <svg class="th ul ml il" width="21" height="21" viewBox="0 0 21 21" fill="none"
                      xmlns="http://www.w3.org/2000/svg">
                      <path
                        d="M16.031 14.617L20.314 18.899L18.899 20.314L14.617 16.031C13.0237 17.3082 11.042 18.0029 9 18C4.032 18 0 13.968 0 9C0 4.032 4.032 0 9 0C13.968 0 18 4.032 18 9C18.0029 11.042 17.3082 13.0237 16.031 14.617ZM14.025 13.875C15.2941 12.5699 16.0029 10.8204 16 9C16 5.132 12.867 2 9 2C5.132 2 2 5.132 2 9C2 12.867 5.132 16 9 16C10.8204 16.0029 12.5699 15.2941 13.875 14.025L14.025 13.875Z" />
                    </svg>
                  </button>


                <!-- Sort -->
                <!-- <select id="ajaxSort" class="border px-3 py-2 rounded-lg">
                    <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest</option>
                    <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>Oldest</option>
                </select> -->

                <!-- Limit per page -->
                <!-- <select id="ajaxLimit" class="border px-3 py-2 rounded-lg">
                    <?php foreach ([6,9,12] as $opt): ?>
                        <option value="<?= $opt ?>" <?= $limit == $opt ? 'selected' : '' ?>><?= $opt ?> / page</option>
                    <?php endforeach; ?>
                </select> -->
            </div>
                    </div>
        </div>

        <!-- container where grid will be replaced -->
        <div id="blogsContainer" style="position:relative;">
            <?= $gridHtmlInitial ?>
        </div>

        <!-- container where pagination will be replaced -->
        <div id="paginationContainer">
            <?= $paginationHtmlInitial ?>
        </div>

      </div>
     </section>


     <section class="i pg gh ji">
    <!-- Bg Shape -->
    <?php
    // Ensure $projectRoot is available (header may have set it). Fall back to compute here.
    if (!isset($projectRoot)) {
      $projectRoot = rtrim(str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME']))), '/');
    }
    $shapePath = $projectRoot . '/public/images/shape-16.svg';
    ?>
    <img class="h p q" src="<?= htmlspecialchars($shapePath) ?>" alt="Bg Shape" />

      <div class="bb ze i z-10 ki xn dr">
        <div class="tc uf sn tn un gg">
          <div class="animate_left to/2">
            <h2 class="fk vj zp pr lk ac">
              Join 5,000+ Students Shaping Their Future with PRIM.
            </h2>
            <p class="lk">
          Padmarajam Institute of Management (PRIM) has empowered thousands of students to build successful
careers in CA, CMA, CS, MBA, and various management programs.
            </p>
          </div>
          <div class="animate_right bf">
            <a href="https://wa.me/918122247567?text=Can I get more details!"  target="_blank" class="vc ek kk hh rg ol il cm gi hi">
              Enroll Now
            </a>
          </div>
        </div>
      </div>
    </section>

      

 </main>

<script>
(function($){
    const slug = <?= json_encode($slug) ?>;
    let debounceTimer = null;

    // Read initial state from server-side rendered values
    let currentPage = <?= (int)$page ?>;
    let currentSearch = <?= json_encode($search) ?>;
    let currentSort = <?= json_encode($sort) ?>;
    let currentLimit = <?= (int)$limit ?>;

    function ajaxLoad(page = 1, search = '', sort = 'newest', limit = <?= (int)$limit ?>) {
        currentPage = page;
        currentSearch = search;
        currentSort = sort;
        currentLimit = limit;

        const $container = $('#blogsContainer');
        const $pagination = $('#paginationContainer');

        // show loading overlay
        let $overlay = $('<div class="loading-overlay">Loading...</div>');
        $container.css('position','relative').append($overlay);

        $.ajax({
            url: '?ajax=1',
            method: 'GET',
            data: {
                slug: slug,
                page: page,
                search: search,
                sort: sort,
                limit: limit
            },
            dataType: 'json'
        }).done(function(res){
            if (res && res.success) {
                $container.html(res.gridHtml);
                $pagination.html(res.paginationHtml);
                // scroll to top of grid
                window.scrollTo({ top: $container.offset().top - 80, behavior: 'smooth' });
            } else {
                $container.html('<p class="text-center text-gray-500 mt-10">No results</p>');
                $pagination.html('');
            }
        }).fail(function(){
            $container.html('<p class="text-center text-red-500 mt-10">An error occurred. Try again.</p>');
            $pagination.html('');
        }).always(function(){
            $overlay.remove();
        });
    }

    // Debounced search
    $('#ajaxSearch').on('input', function(){
        const val = $(this).val();
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function(){
            ajaxLoad(1, val, $('#ajaxSort').val(), $('#ajaxLimit').val());
        }, 450); // 450ms debounce
    });

    // Sort change
    $('#ajaxSort').on('change', function(){
        ajaxLoad(1, $('#ajaxSearch').val(), $(this).val(), $('#ajaxLimit').val());
    });

    // Limit change
    $('#ajaxLimit').on('change', function(){
        ajaxLoad(1, $('#ajaxSearch').val(), $('#ajaxSort').val(), $(this).val());
    });

    // Pagination clicks (event delegation)
    $(document).on('click', '.ajax-page', function(e){
        e.preventDefault();
        const page = parseInt($(this).data('page')) || 1;
        ajaxLoad(page, $('#ajaxSearch').val(), $('#ajaxSort').val(), $('#ajaxLimit').val());
    });

    // Optional: trigger initial bind for keyboard "Enter" on search to immediately search
    $('#ajaxSearch').on('keydown', function(e){
        if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(debounceTimer);
            ajaxLoad(1, $(this).val(), $('#ajaxSort').val(), $('#ajaxLimit').val());
        }
    });

})(jQuery);
</script>

<?php include '_footer.php'; ?>
