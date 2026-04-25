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

<div class="hidden fixed inset-0 bg-black opacity-50 z-10 hidden md:hidden sidebar-overlay"></div>

<aside id="sidebar" class="hidden md:flex md:flex-col w-64 fixed h-full z-20 bg-gray-800 sidebar">

        <a href="dashboard.php" class="inline-flex flex-col items-center justify-center  w-full  gap-4">
               <img src="../assets/images/logo_icon.png" alt="Logo" class="h-20 w-20 p-1 mx-auto sm:mx-0 rounded-full border-2 border-gray-300/50 object-cover mt-4">
            <div class="font-bold text-white flex flex-col text-center">
                <?= htmlspecialchars($settings['company_name'] ?? 'Admin Panel') ?>
                <span class="font-normal text-xs text-white">Admin Panel</span>
            </div>
        </a>
       
        <div class="flex-grow flex flex-col justify-between text-gray-500 ">

       

            <nav class="flex flex-col mx-2 my-6 space-y-3">
             <a href="dashboard.php" class="inline-flex items-center justify-start py-2 px-3 gap-2 hover:bg-gray-700 rounded-lg <?= ($currentPage == 'dashboard.php') ? 'active' : '' ?>">
                    <i class="fa-solid w-6 flex justify-center fa-chart-pie"></i> 
                    <span class="ft-medium">Dashboard</span>
                </a>   
                 <!-- <a href="orders.php"
                    class="inline-flex items-center justify-start py-2 px-3 hover:text-gray-400 hover:bg-gray-700 focus:text-gray-400 focus:bg-gray-700 rounded-lg gap-2 <?= ($currentPage == 'orders.php') ? 'active' : '' ?>">
                   <i class="fa-solid w-6 flex justify-center fa-basket-shopping"></i>
                    <span class="ft-medium">Orders</span>
                </a>
                 <a href="products.php"
                    class="inline-flex items-center justify-start py-2 px-3 hover:text-gray-400 hover:bg-gray-700 focus:text-gray-400 focus:bg-gray-700 rounded-lg gap-2 <?= ($currentPage == 'products.php') ? 'active' : '' ?>">
                    <i class="fa-solid w-6 flex justify-center fa-boxes-stacked"></i>
                    <span class="ft-medium">Products</span>
                </a>
                <a href="reports.php"
                    class="inline-flex items-center justify-start py-2 px-3 hover:text-gray-400 hover:bg-gray-700 focus:text-gray-400 focus:bg-gray-700 rounded-lg gap-2 <?= ($currentPage == 'reports.php') ? 'active' : '' ?>">
                    <i class="fa-solid w-6 flex justify-center fa-clipboard-list"></i>
                    <span class="ft-medium">Reports</span>
                </a>
                <a href="customers.php"
                    class="inline-flex items-center justify-start py-2 px-3 hover:text-gray-400 hover:bg-gray-700 focus:text-gray-400 focus:bg-gray-700 rounded-lg gap-2 <?= ($currentPage == 'customers.php') ? 'active' : '' ?>">
                   <i class="fa-solid w-6 flex justify-center fa-user-group"></i>
                    <span class="ft-medium">Customers</span>
                </a> -->
                 <a href="cat_list.php"
                    class="inline-flex items-center justify-start py-2 px-3 hover:text-gray-400 hover:bg-gray-700 focus:text-gray-400 focus:bg-gray-700 rounded-lg gap-2 <?= ($currentPage == 'cat_list.php') ? 'active' : '' ?>">
                   <i class="fa-solid fa-layer-group w-6 flex justify-center"></i>
                    <span class="ft-medium">Manage Category</span>
                </a> 
                <a href="blog_list.php"
                    class="inline-flex items-center justify-start py-2 px-3 hover:text-gray-400 hover:bg-gray-700 focus:text-gray-400 focus:bg-gray-700 rounded-lg gap-2 <?= ($currentPage == 'blog_list.php') ? 'active' : '' ?>">
                   <i class="fa-solid fa-blog w-6 flex justify-center"></i>
                    <span class="ft-medium">Manage Blogs</span>
                </a> 
                 <!-- <a href="reports.php"
                    class="inline-flex items-center justify-start py-2 px-3 hover:text-gray-400 hover:bg-gray-700 focus:text-gray-400 focus:bg-gray-700 rounded-lg gap-2">
                    <i class="fa-solid w-6 flex justify-center fa-clipboard-list"></i>
                    <span class="ft-medium">Business Reports</span>
                </a>
                <a href="enquiries.php"
                    class="inline-flex items-center justify-start py-2 px-3 hover:text-gray-400 hover:bg-gray-700 focus:text-gray-400 focus:bg-gray-700 rounded-lg gap-2 <?= ($currentPage == 'enquiries.php') ? 'active' : '' ?>">
                    <i class="fa-solid w-6 flex justify-center fa-envelope-open-text"></i>
                    <span class="ft-medium">Enquiries</span>
                </a> -->
            </nav>
            <div class="p-3 inline-flex items-center justify-start h-20 w-full border-t border-gray-700">
                <a href="settings.php"
                     class="flex text-left w-full items-center justify-start px-2 py-3 gap-2 hover:text-gray-400 hover:bg-gray-700 focus:text-gray-400 focus:bg-gray-700 rounded-lg">
                   <i class="fa-solid w-6 flex justify-center fa-gears"></i>
                    <span class="ft-medium">Site Configuration</span>

            </a>
            </div>
        </div>
    </aside>




