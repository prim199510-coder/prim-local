<?php
include './../includes/db.php';
include_once __DIR__ . '/../includes/config.php';
include_once 'settings.php';

$conn = getDbConnection();

// ✅ Validate and extract slug
if (!isset($_GET['slug']) || empty($_GET['slug'])) {
    header("Location: ../index.php");
    exit();
}

$slug = $_GET['slug']; // e.g. "category1/blog1"
$parts = explode('/', $slug);

if (count($parts) < 2) {
    echo "<p class='text-center text-red-600 mt-10'>Invalid blog URL format.</p>";
    exit();
}

$categorySlug = $parts[0];
$blogSlug = $parts[1];

// ✅ Fetch category info
$cat_stmt = $conn->prepare("SELECT id, name FROM categories WHERE slug = ?");
$cat_stmt->bind_param("s", $categorySlug);
$cat_stmt->execute();
$cat_result = $cat_stmt->get_result();

if ($cat_result->num_rows === 0) {
    echo "<p class='text-center text-red-600 mt-10'>Category not found.</p>";
    exit();
}

$category = $cat_result->fetch_assoc();
$cat_id = $category['id'];

// ✅ Fetch blog post
$blog_stmt = $conn->prepare("
    SELECT b.*, c.name AS category_name, c.slug AS category_slug 
    FROM blogs b
    LEFT JOIN categories c ON b.cat_id = c.id
    WHERE b.cat_id = ? AND b.slug = ?
");
$blog_stmt->bind_param("is", $cat_id, $blogSlug);
$blog_stmt->execute();
$blog_result = $blog_stmt->get_result();

if ($blog_result->num_rows === 0) {
    echo "<p class='text-center text-red-600 mt-10'>Blog not found.</p>";
    exit();
}

$blog = $blog_result->fetch_assoc();

// ✅ Fetch all categories
$categories = [];
$cat_all = $conn->query("SELECT name, slug FROM categories ORDER BY name ASC");
while ($row = $cat_all->fetch_assoc()) {
     $categories[$row['name']] = $row['slug'];
}

// ✅ Fetch 3 latest published blogs
$latest_stmt = $conn->prepare("
    SELECT b.id, b.title, b.slug, b.banner_images, b.created_at, c.name AS category_name, c.slug AS category_slug
    FROM blogs b
    LEFT JOIN categories c ON b.cat_id = c.id
    WHERE b.published = 1
    ORDER BY b.created_at DESC
    LIMIT 3
");
$latest_stmt->execute();
$latest_result = $latest_stmt->get_result();
$latest_posts = [];
while ($row = $latest_result->fetch_assoc()) {
    $latest_posts[] = $row;
}

// ✅ Social share variables
$currentUrl = "https://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
$shareTitle = urlencode($blog['title']);
$shareUrl = urlencode($currentUrl);

// ✅ Prepare tags
$tags = !empty($blog['tags']) ? explode(',', $blog['tags']) : [];

// Slider images
// Decode JSON fields
$bannerImages = json_decode($blog['banner_images'], true);
$mobileBannerImages = json_decode($blog['mobile_banner_images'], true);
$bannerVideos = json_decode($blog['video_links'], true);
$documents = json_decode($blog['documents'], true);

if (!is_array($bannerImages)) $bannerImages = [];
if (!is_array($mobileBannerImages)) $mobileBannerImages = [];
if (!is_array($bannerVideos)) $bannerVideos = [];
if (!is_array($documents)) $documents = [];


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($blog['meta_title'] ?: $blog['title']) ?> - Blog</title>
    <meta name="description" content="<?= htmlspecialchars($blog['meta_desc'] ?: substr(strip_tags($blog['description']),0,160)) ?>">
    <meta name="keywords" content="<?= htmlspecialchars($blog['meta_keywords']) ?>">
    
      
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link rel="icon" href="<?= htmlspecialchars(BASE_URL . 'uploads/' . $settings['favicon'] ?? 'favicon.ico') ?>" type="image/x-icon">

    
     <script src="https://cdn.tailwindcss.com"></script>
    <!-- jQuery for AJAX -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

  <?php
  // Compute project root (one level above the public/ folder) so asset URLs work
  $projectRoot = rtrim(str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME']))), '/');
  $assetBase = $projectRoot . '/assets/frontend/css';
  ?>
  <link rel="stylesheet" href="<?= $assetBase ?>/style.css">

  <?php
  // Load site settings (logo, company name) for dynamic header/logo
  $settingsRes = $conn->query("SELECT * FROM settings LIMIT 1");
  $settings = $settingsRes ? $settingsRes->fetch_assoc() : [];
  $logoUrl = '';
  if (!empty($settings['logo'])) {
      $logoUrl = $projectRoot . '/uploads/' . $settings['logo'];
  } else {
      // fallback to the bundled images under public/images
      $logoUrl = $projectRoot . '/public/images/logo-light.svg';
  }
  ?>

<script type='text/javascript' src='https://platform-api.sharethis.com/js/sharethis.js#property=6910b9b850eca9f24072513b&product=sop' async='async'></script>
  <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>

    <style>


      

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



    <!-- ===== Blog Single Start ===== -->
    <section class="blogSingle">
      <div class="bb ze ki xn 2xl:ud-px-0">
        <div class="grid grid-cols-12 gap-20 ">
          <div class="col-span-12 md:col-span-9">
            <div
              class="blogDetails animate_top">
              <h1 class="ek vj 2xl:ud-text-title-lg kk wm nb gb"><?= htmlspecialchars($blog['title']) ?></h1>
               <!-- Banner -->

               <div class="blogSlider">
            <!-- SLIDER -->
                  <?php if (!empty($bannerImages) || !empty($mobileBannerImages) || !empty($bannerVideos)): ?>
                  <div class="swiper">
                      <div class="swiper-wrapper">

                          <?php
                          // ---------------------------
                          // 1. Add IMAGE slides
                          // ---------------------------
                            foreach ($bannerImages as $img) {
                              $raw = str_replace('\\/', '/', $img);
                              $imgPath = '../' . ltrim($raw, './'); // fix paths like uploads\/blogs
                              echo "\n <div class='swiper-slide desktop-only'>\n                                  <img src='" . htmlspecialchars($imgPath) . "' alt='" . htmlspecialchars($blog['title']) . "'>\n                              </div>";
                            }

                          // ---------------------------
                          // 2. Add Mobile IMAGE slides
                          // ---------------------------
                            foreach ($mobileBannerImages as $img) {
                              $raw = str_replace('\\/', '/', $img);
                              $imgPath = '../' . ltrim($raw, './'); // fix paths like uploads\/blogs
                              echo "\n                              <div class='swiper-slide mobile-only'>\n                                  <img src='" . htmlspecialchars($imgPath) . "' alt='" . htmlspecialchars($blog['title']) . "'>\n                              </div>";
                            }

                          // ---------------------------
                          // 3. Add VIDEO slides
                          // ---------------------------
                          foreach ($bannerVideos as $vid) {

                              $vid = str_replace('\/', '/', $vid);

                              // Detect YouTube URL
                              if (preg_match('/youtu\.be|youtube\.com/', $vid)) {

                                  $videoId = "";

                                  // extract YouTube ID
                                  if (preg_match('/(?:v=|youtu\.be\/)([^&]+)/', $vid, $m)) {
                                      $videoId = $m[1];
                                  }

                                  if ($videoId !== "") {
                                      echo "
                                      <div class='swiper-slide'>
                                          <iframe src='https://www.youtube.com/embed/$videoId' allowfullscreen></iframe>
                                      </div>";
                                  }

                              } else {
                                  // Future support for mp4 or other formats
                                  echo "
                                  <div class='swiper-slide'>
                                      <p style='padding:20px; background:#eee;'>Unsupported video format: $vid</p>
                                  </div>";
                              }
                          }
                          ?>

                      </div>

                      <!-- Navigation -->
                      <div class="swiper-pagination"></div>
                      <div class="swiper-button-prev"></div>
                      <div class="swiper-button-next"></div>
                  </div>
                  <?php else: ?>
                       <?php 
                       $banner =  '../assets/images/place_holder_img.jpg';
                       echo "
                              <div class='place_holder_img'>
                                  <img src='$banner' alt='" . htmlspecialchars($blog['title']) . "'>
                              </div>";  
                      ?>        
                  <?php endif; ?>
                      
        </div>

              <?php
                // Determine author to display: prefer explicit 'author', then 'author_name', else fallback.
                $displayAuthor = 'Jagan Karthik';
                if (isset($blog['author']) && trim($blog['author']) !== '') {
                    $displayAuthor = $blog['author'];
                } elseif (isset($blog['author_name']) && trim($blog['author_name']) !== '') {
                    $displayAuthor = $blog['author_name'];
                }
              ?>
              <ul class="tc uf cg 2xl:ud-gap-15 fb blogAutherDetails">
                <li><span class="rc kk wm">Author: </span> <?= htmlspecialchars($displayAuthor) ?></li>
                <li><span class="rc kk wm">Published On: </span> <?= date('F j, Y', strtotime($blog['created_at'])) ?></li>
                <li><span class="rc kk wm">Category: </span>  <?= htmlspecialchars($blog['category_name'] ?? 'Mentoring') ?></li>

                <li class="lg:!ml-auto">

                   <!-- ===== PDF Download Section ===== -->
            <?php if (!empty($documents)): ?>
                <?php
                    //
                    // currently only single PDF supported
                    //

                    $pdfFile = str_replace('\/', '/', '../'.trim($documents[0])); // fix paths like uploads\/blogs
        
                    // Check file exists in server
                     
                    if (file_exists($pdfFile)):
                ?>

                        <a target="_blank" href="<?= $pdfFile ?>" 
                          target="_blank"  
                          class="inline-block bg-white text-red-600 px-4 py-2 rounded hover:shadow transition border border-red-600 font-medium">
                            <img src="<?= htmlspecialchars($projectRoot . '/public/images/pdf_icon.svg') ?>" alt="Download PDF" class="inline-block w-5 h-5 mr-2 align-middle"> Download
                        </a>
                <?php else: ?>
                    <p class="mt-4 text-red-600">Document file missing from server.</p>
                <?php endif; ?>
            <?php endif; ?>
</li>
            

              </ul>


              
             

              <!-- Description -->
            <?php if (!empty($blog['description'])): ?>
                <p class="text-lg text-gray-700 mb-6 leading-relaxed">
                    <?= nl2br(htmlspecialchars($blog['description'])) ?>
                </p>
            <?php endif; ?>

             <!-- Content -->
            <article class="prose prose-blue max-w-none">
                <?= $blog['content'] /* HTML displayed directly */ ?>
            </article>

         


            <!-- ✅ Tags moved to bottom -->
            <?php if (!empty($tags)): ?>
                <div class="mt-8 border-t pt-5">
                    <span class="font-semibold text-gray-700">Tags:</span>
                    <div class="mt-2 tagsBlock">
                    <?php foreach ($tags as $tag): ?>
                        <!-- <a href="../?tag=<?= urlencode(trim($tag)) ?>" 
                           class="inline-block bg-gray-200 text-gray-700 text-sm px-4 py-1 mr-2 mt-2 rounded-full hover:bg-blue-200">
                            <?= htmlspecialchars(trim($tag)) ?>
                        </a> -->
                        <span href="../?tag=<?= urlencode(trim($tag)) ?>" 
                           class="inline-block bg-gray-200 text-gray-700 text-sm px-4 py-1 mr-2 mt-2 rounded-full hover:bg-blue-200">
                            <?= htmlspecialchars(trim($tag)) ?>
                        </span>
                    <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            </div>

             <!-- Social Share Buttons -->
            <div class="mt-8 flex items-center space-x-4">
                <span class="font-semibold text-gray-700">Share On:</span>

               <!-- ShareThis BEGIN --><div class="sharethis-inline-share-buttons"></div><!-- ShareThis END -->
            </div>


          </div>

          <div class="col-span-12 md:col-span-3 blogSidebar mt-10 md:mt-0 border-t md:border-t-0 pt-10 md:pt-0 md:pl-10">
            

            <div class="animate_top fb">
              <h4 class="tj kk wm qb">Categories</h4>
              <ul class="space-y-2 category-group">
                    <?php foreach ($categories as $name => $slug): ?>
                        <li class="ql vb du-ease-in-out il xl">
                            <a href="../<?= urlencode($slug) ?>" 
                            class="inline-block py-1 text-gray-700 font-medium hover:text-blue-700">
                                <?= htmlspecialchars($name) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>           
            </div>

            <div class="animate_top">
              <h4 class="tj kk wm qb">Latest Posts</h4>


               <!-- Latest Posts -->
            <div class="p-0">
                <ul class="space-y-4">
                    <?php if (count($latest_posts) > 0): ?>
                        <?php foreach ($latest_posts as $post): ?>
                            <li class="flex space-x-3">
                                <?php
                                  // banner_images can be JSON array or a plain string. Normalize and pick first image.
                                  $imgSrc = '';
                                  $bi = $post['banner_images'];
                                  $decoded = json_decode($bi, true);
                                  if (!empty($decoded)) {
                                    
                                    if (is_array($decoded) && count($decoded) > 0) {
                                      $first = $decoded[0];
                                    } else {
                                      // try comma separated or raw string
                                      $first = strtok($bi, ',');
                                    }

                                    if (!empty($first)) {
                                      // normalize escaped slashes and relative path
                                      $first = str_replace('\\/', '/', $first);
                                      $first = ltrim($first, './');
                                      // ensure path is relative to public/ (use ../ to go up from public folder)
                                      $imgSrc = '../' . $first;
                                    }
                                  }

                                  if (empty($imgSrc)) {
                                    $imgSrc = '../assets/images/place_holder_img.jpg';
                                  }
                                ?>
                                <img src="<?= htmlspecialchars($imgSrc) ?>" 
                                   alt="<?= htmlspecialchars($post['title']) ?>" 
                                   class="w-16 h-16 object-cover rounded">
                                <div>
                                <div>
                                    <a href="../<?= htmlspecialchars($post['category_slug']) ?>/<?= htmlspecialchars($post['slug']) ?>"
                                       class="font-medium text-sm hover:text-blue-700 block">
                                       <?= htmlspecialchars($post['title']) ?>
                                    </a>
                                    <p class="text-xs text-gray-500">
                                        <?= date('M j, Y', strtotime($post['created_at'])) ?>
                                    </p>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="text-gray-500 text-sm">No recent posts available.</li>
                    <?php endif; ?>
                </ul>
            </div>




            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- ===== Blog Single End ===== -->



     <section class="i pg gh ji mt60">
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



  

    <!-- Swiper JS -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<script>
new Swiper('.swiper', {
    loop: true,
    pagination: { el: '.swiper-pagination', clickable: true },
    navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
    slidesPerView: 1,
});
</script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<script>
    // Initialize Swiper
    document.addEventListener("DOMContentLoaded", function () {
        const swiper = new Swiper('.swiper', {
            pagination: { el: ".swiper-pagination" },
            navigation: { nextEl: ".swiper-button-next", prevEl: ".swiper-button-prev" },
            loop: false,
            on: {
                slideChange: function () {
                    document.querySelectorAll('.swiper-slide iframe').forEach((frame) => {
                        const currentSrc = frame.src;
                        frame.src = currentSrc; // resets and stops video without blink
                    });
                }
            }
        });
    });

</script>

  </main>



   <?php include '_footer.php'; ?>