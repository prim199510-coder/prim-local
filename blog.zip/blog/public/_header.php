<?php
include './../includes/db.php';
include_once __DIR__ . '/../includes/config.php';
include_once 'settings.php';
$conn = getDbConnection();

// Get all categories for dropdown
$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC");

// Get filters from query string (for when user comes from blog page)
$initialSearch = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
$initialTag = isset($_GET['tag']) ? htmlspecialchars($_GET['tag']) : '';
$initialCategory = isset($_GET['category']) ? htmlspecialchars($_GET['category']) : 'all';
?>
<!DOCTYPE html>
<html lang="en">


<head>
   
   <title><?= htmlspecialchars($settings['meta_title'] ?? 'Blog') ?></title>
   <meta name="keywords" content="<?= htmlspecialchars($settings['meta_keywords'] ?? '') ?>">
   <meta name="description" content="<?= htmlspecialchars($settings['meta_description'] ?? '') ?>">

  <meta property="og:title" content="<?= htmlspecialchars($settings['meta_title'] ?? 'Blog') ?>">
  <meta property="og:description" content="<?= htmlspecialchars($settings['meta_description'] ?? '') ?>">
  <meta property="og:url" content="<?= htmlspecialchars(BASE_URL) ?>">

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

  <link rel="icon" type="image/png" href="<?= $projectRoot ?>/public/images/favicon.png">
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
  