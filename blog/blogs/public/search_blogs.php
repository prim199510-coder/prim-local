<?php
include './../includes/db.php';
$conn = getDbConnection();

// Get filter values
$search = isset($_GET['search']) ? $_GET['search'] : '';
$tag = isset($_GET['tag']) ? $_GET['tag'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : 'all';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$blogsPerPage = 9;
$offset = ($page - 1) * $blogsPerPage;

// Build SQL query with filters
$sql = "SELECT b.*, c.name AS category_name , c.slug AS category_slug FROM blogs b JOIN categories c ON b.cat_id = c.id AND b.published != 0 WHERE 1";
$params = [];
$types = "";

if ($category !== 'all') {
    $sql .= " AND c.name = ?";
    $params[] = $category;
    $types .= "s";
}
if (!empty($search)) {
    $sql .= " AND (b.title LIKE ? OR b.description LIKE ? OR b.tags LIKE ?)";
    $searchTerm = '%' . $search . '%';
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "sss";
}
/*
if (!empty($tag)) {
    $sql .= " AND b.tags LIKE ?";
    $searchTag = '%' . $tag . '%';
    $params[] = $searchTag;
    $types .= "s";
}
*/
$sql .= " ORDER BY b.created_at DESC, b.id DESC LIMIT ? OFFSET ?";
$params[] = $blogsPerPage;
$params[] = $offset;
$types .= "ii";

// Prepare and execute

//echo $sql; // Debug: Output the final SQL query

$stmt = $conn->prepare($sql);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Count total blogs for pagination
$countSql = "SELECT COUNT(*) AS total FROM blogs b JOIN categories c ON b.cat_id = c.id AND b.published != 0 WHERE 1";
$countParams = [];
$countTypes = "";
if ($category !== 'all') {
    $countSql .= " AND c.name = ?";
    $countParams[] = $category;
    $countTypes .= "s";
}
if (!empty($search)) {
    $countSql .= " AND (b.title LIKE ? OR b.description LIKE ?)";
    $countParams[] = $searchTerm;
    $countParams[] = $searchTerm;
    $countTypes .= "ss";
}
$countStmt = $conn->prepare($countSql);
if ($countTypes) {
    $countStmt->bind_param($countTypes, ...$countParams);
}
$countStmt->execute();
$totalBlogs = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalBlogs / $blogsPerPage);

// Build blog HTML (new card layout)
$blogHtml = "";
if ($result->num_rows > 0) {
    while ($blog = $result->fetch_assoc()) {
        // Compute project root so asset URLs are root-relative and consistent
        $projectRoot = rtrim(str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME']))), '/');

        // Banner image (public pages expect uploads path stored in DB like 'uploads/...')
        $blog_banner_imgs = json_decode($blog['banner_images'] ?? '[]', true);
        if (!empty($blog_banner_imgs )) {
                                    
            $blog_banner_imgs = json_decode($blog['banner_images'] ?? '[]', true);
            $banner_img = $blog_banner_imgs[0] ?? '';
                            
            $banner = $projectRoot . '/' . ltrim(htmlspecialchars($banner_img), '/');
        } else {
            $banner = $projectRoot . '/assets/images/place_holder_img.jpg';
        }

        // Format date
        $createdAt = !empty($blog['created_at']) ? $blog['created_at'] : '';
        $dateStr = $createdAt ? date('d M, Y', strtotime($createdAt)) : '';

        // Permalink (category/slug)
        $permalink = htmlspecialchars($blog['category_slug']) . '/' . htmlspecialchars($blog['slug']);

        // Determine display author: prefer 'author', then 'author_name', else fallback.
        $displayAuthor = 'Jagan Karthik';
        if (isset($blog['author']) && trim($blog['author']) !== '') {
            $displayAuthor = $blog['author'];
        } elseif (isset($blog['author_name']) && trim($blog['author_name']) !== '') {
            $displayAuthor = $blog['author_name'];
        }

        $blogHtml .= '<div class="blogItem animate_top sg vk rm xm">';
        $blogHtml .= '  <div class="c rc i z-1 pg">';
        $blogHtml .= '    <img class="blogIimg w-full" src="' . $banner . '" alt="' . htmlspecialchars($blog['title']) . '" />';
        $blogHtml .= '    <div class="im h r s df vd yc wg tc wf xf al hh/20 nl il z-10">';
        $blogHtml .= '      <a  href="' . $permalink . '" class="vc ek rg lk gh sl ml il gi hi">Read More</a>';
        $blogHtml .= '    </div>';
        $blogHtml .= '  </div>';

        $blogHtml .= '  <div class="yh">';
        $blogHtml .= '    <div class="tc uf wf ag jq">';
    $blogHtml .= '      <div class="tc wf ag">';
    $blogHtml .= '        <img src="' . $projectRoot . '/public/images/icon-man.svg" alt="User" />';
    $blogHtml .= '        <p> ' . htmlspecialchars($displayAuthor) . '</p>';
    $blogHtml .= '      </div>';
    $blogHtml .= '      <div class="tc wf ag">';
    $blogHtml .= '        <img src="' . $projectRoot . '/public/images/icon-calender.svg" alt="Calender" />';
    $blogHtml .= '        <p>' . htmlspecialchars($dateStr) . '</p>';
    $blogHtml .= '      </div>';
        $blogHtml .= '    </div>';

        $blogHtml .= '    <h4 class="ek tj ml il kk wm xl eq lb">';
        $blogHtml .= '      <a title="' . htmlspecialchars($blog['title']) . '" href="' . $permalink . '">' . htmlspecialchars($blog['title']) . '</a>';
        $blogHtml .= '    </h4>';
        $blogHtml .= '  </div>';
        $blogHtml .= '</div>';
    }
} else {
    $blogHtml = '<p class="text-center text-gray-500 mt-10">No blogs found.</p>';
}

// Build pagination HTML (styled block)
$paginationHtml = "";
if ($totalPages > 1) {
    $btnClass = 'c tc wf xf wd in zc hn rg uj fo wk xm ml il hh rm tl zm yl an';
    // keep compatibility with existing frontend code by adding 'page-link'
    $btnClassPage = $btnClass . ' page-link';

    $paginationHtml .= '<div class="mt-6"><nav><ul class="tc wf xf bg">';

    // Prev arrow
    $prevPage = max(1, $page - 1);
    $paginationHtml .= '<li><a href="#" class="' . $btnClassPage . '" data-page="' . $prevPage . '">'
        . '<svg class="th lm ml il" width="8" height="14" viewBox="0 0 8 14" fill="none" xmlns="http://www.w3.org/2000/svg">'
        . '<path d="M2.93884 6.99999L7.88884 11.95L6.47484 13.364L0.11084 6.99999L6.47484 0.635986L7.88884 2.04999L2.93884 6.99999Z" />'
        . '</svg></a></li>';

    // Decide which page numbers to show (compact when many pages)
    $pagesToShow = [];
    if ($totalPages <= 7) {
        for ($i = 1; $i <= $totalPages; $i++) $pagesToShow[] = $i;
    } else {
        // always include 1
        $pagesToShow[] = 1;
        $start = max(2, $page - 1);
        $end = min($totalPages - 1, $page + 1);

        if ($start > 2) {
            $pagesToShow[] = '...';
        } else {
            $start = 2;
        }

        for ($i = $start; $i <= $end; $i++) $pagesToShow[] = $i;

        if ($end < $totalPages - 1) {
            $pagesToShow[] = '...';
        }

        $pagesToShow[] = $totalPages;
    }

    foreach ($pagesToShow as $p) {
    if ($p === '...') {
        $paginationHtml .= '<li><a href="#" class="' . $btnClassPage . '">...</a></li>';
        continue;
    }

    // Add active class if current page matches
    $isActive = ($p == $page);
    $activeClass = $isActive ? ' active bg-primary text-white' : '';
    
    $paginationHtml .= '<li><a href="#" class="' . $btnClassPage . $activeClass . '" data-page="' . $p . '">' . $p . '</a></li>';
}


    // Next arrow
    $nextPage = min($totalPages, $page + 1);
    $paginationHtml .= '<li><a href="#" class="' . $btnClassPage . '" data-page="' . $nextPage . '">'
        . '<svg class="th lm ml il" width="8" height="14" viewBox="0 0 8 14" fill="none" xmlns="http://www.w3.org/2000/svg">'
        . '<path d="M5.06067 7.00001L0.110671 2.05001L1.52467 0.636014L7.88867 7.00001L1.52467 13.364L0.110672 11.95L5.06067 7.00001Z" fill="#fefdfo" />'
        . '</svg></a></li>';

    $paginationHtml .= '</ul></nav></div>';
}

echo json_encode([
    'html' => $blogHtml,
    'pagination' => $paginationHtml
]);
?>
