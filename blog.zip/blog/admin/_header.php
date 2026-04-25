<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}
require_once '../includes/db.php';
$conn = getDbConnection();

// Fetch site settings once
$settingsRes = $conn->query("SELECT * FROM settings LIMIT 1");
$settings = $settingsRes->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($settings['meta_title'] ?? 'Admin Panel') ?></title>
    <link rel="icon" href="../assets/images/favicon.ico" type="image/x-icon">


    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/tailwindcss@^1.0/dist/tailwind.min.css" >
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="../assets/admin/css/custom.css">
    <!-- jQuery (latest recommended via CDN) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!--
  <script src="https://cdn.jsdelivr.net/npm/simple-datatables@9.0.3"></script>
   
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const table = document.querySelector("#order-table");
            const dataTable = new simpleDatatables.DataTable(table, {
                searchable: true,
                sortable: true,
                perPage: 5,
                perPageSelect: [5, 10, 20],
                labels: {
                    placeholder: "Search...",
                    perPage: "entries per page",
                    noRows: "No data found",
                    info: "Showing {start} to {end} of {rows} entries"
                }
            });
        });
    </script>
-->


 <script>
    $( document ).ready(function() {
        $("#mobileMenuBtn").click(function(){
            $("body").toggleClass("sidebar-open");
        });
    });
</script>



</head>
<body class="flex bg-gray-100 min-h-screen">
 
<?php
$currentPage = basename($_SERVER['PHP_SELF']); // e.g., 'dashboard.php'
include '_sidebar.php';
?>

<div class="flex flex-col text-gray-800 w-full">
    <header class="flex items-center h-16 px-6 fixed z-10">        
        <button id="mobileMenuBtn" aria-controls="sidebar" aria-expanded="false"
            class="block md:hidden relative flex-shrink-0 p-2 mr-2 text-gray-600 hover:bg-gray-100 hover:text-gray-800 focus:bg-gray-100 focus:text-gray-800 rounded-full">
            <span class="sr-only">Menu</span>
            <svg aria-hidden="true" fill="none" viewbox="0 0 24 24" stroke="currentColor" class="h-6 w-6">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
            </svg>
        </button>

        <?php
// Get current filename (without extension)
$currentPage = basename($_SERVER['PHP_SELF'], ".php");

// Define readable titles for each page
$pageTitles = [
    'dashboard' => 'Dashboard',
    'blog_list' => 'Blogs',
    'blog_edit' => 'Edit Blog',
    'blog_view' => 'View Blog',
    'cat_list' => 'Categories',
    'cat_edit' => 'Edit Category',
    'settings' => 'Settings',
];

// Pick the title or fallback to filename
$adpageTitle = ""; 
if (isset($pageTitle)) {
    $adpageTitle = $pageTitle;
    }
else  
    {
    $adpageTitle = $pageTitles[$currentPage] ?? ucfirst(str_replace('_', ' ', $currentPage));
    }

?>
<div class="flex flex-col space-y-6 md:space-y-0 md:flex-row justify-between">
    <div class="mr-6">
        <h1 id="adPageTitle" class="text-2xl uppercase text-primary">
            <?= htmlspecialchars($adpageTitle) ?>
        </h1>
    </div>
</div>


        <div class="flex flex-shrink-0 items-center ml-auto">
            
            <!-- Profile button -->
            <a href="admin_profile.php" class="inline-flex items-center focus:outline-none rounded-lg hover:text-gray-300">
                <span class="sr-only">User Menu</span>
                <div class="hidden md:flex md:flex-col md:items-end md:leading-tight">
                    <span class="font-semibold">Admin</span>
                </div>
                <span class="h-12 w-12 flex items-center justify-center  rounded-full overflow-hidden">
                    <i class="fa-regular fa-user"></i>
                </span>
            </a>

            <!-- Logout button -->
            <div class="space-x-1">
                <a href="logout.php"
                    class="h-8 w-12 flex items-center justify-center relative  border-l border-gray-300 hover:text-gray-300">
                    <span class="sr-only">Log out</span>
                    <i class="fa-solid fa-power-off"></i>
                </a>
            </div>
        </div>

    </header>


