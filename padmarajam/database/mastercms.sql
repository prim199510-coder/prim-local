-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 16, 2025 at 07:10 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `brandpblog`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `contact` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `email`, `contact`) VALUES
(1, 'admin', '$2y$10$HrDOyFv87KpC1R.PWC5TN.IeWy65RGyJusZv7vupc6rzUweQ46q2u', 'admin@testing.com', '');

-- --------------------------------------------------------

--
-- Table structure for table `blogs`
--

CREATE TABLE `blogs` (
  `id` int(11) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `title` varchar(255) NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT 0,
  `cat_id` int(11) NOT NULL,
  `tags` varchar(1024) NOT NULL,
  `meta_title` varchar(255) NOT NULL,
  `meta_desc` text NOT NULL,
  `meta_keywords` text NOT NULL,
  `banner_images` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `description` text NOT NULL,
  `parent_id` int(11) NOT NULL DEFAULT -1,
  `updated_at` datetime DEFAULT NULL,
  `video_links` text DEFAULT NULL,
  `documents` text DEFAULT NULL,
  `mobile_banner_images` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=ascii COLLATE=ascii_general_ci;

--
-- Dumping data for table `blogs`
--

INSERT INTO `blogs` (`id`, `slug`, `content`, `title`, `published`, `cat_id`, `tags`, `meta_title`, `meta_desc`, `meta_keywords`, `banner_images`, `created_at`, `description`, `parent_id`, `updated_at`, `video_links`, `documents`, `mobile_banner_images`) VALUES
(99, 'blog2', '<p>blog2</p>\r\n', 'blog2', 1, 16, 'blog2', 'blog2', 'blog2', 'blog2', '', '2025-10-17 07:06:52', 'blog2', -1, '2025-10-17 10:27:15', NULL, NULL, NULL),
(104, 'blog24', '<p>blog23</p>\r\n', 'blog24', 1, 12, 'blog23', 'blog23', 'blog23', 'blog23', '', '2025-10-17 09:57:44', 'blog23', -1, '2025-10-17 10:00:02', NULL, NULL, NULL),
(116, 'test-54', '<p>test 54..</p>\r\n', 'test 54', 1, 20, 'tag1, tag2, tag3, tag4, tag5, tag6', '', '', '', '', '2025-11-08 19:17:14', 'test 54', -1, NULL, NULL, NULL, NULL),
(188, 'test45', '<p>test45</p>\r\n', 'test45', 1, 20, 'test1, test2, test3, test4', '', '', 'test,test2', '[\"uploads\\/blogs\\/1763130115_7ed2e9c7_login-bg.jpg\",\"uploads\\/blogs\\/1763130115_57d4cbb5_place_holder_img.jpg\"]', '2025-11-10 04:46:40', 'test45', -1, '2025-11-10 04:48:50', '[\"https:\\/\\/www.youtube.com\\/watch?v=A7ftKTouJN8&pp=ygUTZSBjb21tZXJjZSBidXNpbmVzcw%3D%3D\",\"https:\\/\\/www.youtube.com\\/watch?v=Q2nzrHwBuFE&pp=ugUEEgJlbg%3D%3D\"]', '[\"uploads\\/docs\\/1763306764_a8920469_1763304209_c2e011ad______________________________________________________________________.pdf\"]', NULL),
(203, 'test77', '<p>test77 content..</p>\r\n', 'test77', 1, 20, 'test77', 'test77', 'test77', 'test77', '[]', '2025-11-16 21:17:37', 'test77', -1, NULL, '[]', '[]', NULL),
(204, 'blog1', '<p>blog1</p>\r\n', 'blog1', 1, 20, 'blog1', '', '', '', '[]', '2025-11-16 21:33:03', 'blog1', -1, NULL, '[]', '[]', NULL),
(205, 'blog2', '<p>blog2..</p>\r\n', 'blog2', 1, 20, 'blog2', '', '', '', '[]', '2025-11-16 21:33:27', 'blog2', -1, NULL, '[]', '[]', NULL),
(206, 'blog3', '<p>blog3..</p>\r\n', 'blog3', 1, 20, 'blog3', '', '', '', '[]', '2025-11-16 21:33:49', 'blog3', -1, NULL, '[]', '[]', NULL),
(207, 'blog4', '<p>blog4..</p>\r\n', 'blog4', 1, 20, 'blog4', '', '', '', '[]', '2025-11-16 21:34:12', 'blog4', -1, NULL, '[]', '[]', NULL),
(208, 'blog5', '<p>blog5..</p>\r\n', 'blog5', 1, 20, 'blog5', '', '', '', '[]', '2025-11-16 21:34:33', 'blog5', -1, NULL, '[]', '[]', NULL),
(209, 'blog6', '<p>blog6</p>\r\n', 'blog6', 1, 20, 'blog6', '', '', '', '[\"uploads\\/blogs\\/1763313010_8650b841_blog-01.png\",\"uploads\\/blogs\\/1763313010_7cd794ad_blog-02.png\",\"uploads\\/blogs\\/1763313010_1701a229_blog-03.png\"]', '2025-11-16 21:35:02', 'blog6', -1, NULL, '[]', '[\"uploads\\/docs\\/1763313040_56cb2645_1763306764_a8920469_1763304209_c2e011ad______________________________________________________________________.pdf\"]', '[\"uploads\\/blogs\\/mobile\\/1763315627_3b06a557_1763312814_767e2022_blog-small-02.png\",\"uploads\\/blogs\\/mobile\\/1763315627_1c2a0272_1763312814_16642adb_blog-small-03.png\",\"uploads\\/blogs\\/mobile\\/1763315627_3136c6ff_1763312814_bf1ff442_blog-small-01.png\"]');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `meta_title` varchar(255) NOT NULL,
  `meta_desc` varchar(255) NOT NULL,
  `meta_keywords` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `meta_title`, `meta_desc`, `meta_keywords`) VALUES
(12, 'category', 'category', 'category1', 'category1', 'category1', 'category1'),
(16, 'Manufacture24', 'manufacture24', '', '', '', ''),
(20, 'Quick Commerce', 'quick-commerce', '', '', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `enquiries`
--

CREATE TABLE `enquiries` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `submitted_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enquiries`
--

INSERT INTO `enquiries` (`id`, `name`, `email`, `message`, `submitted_at`) VALUES
(1, 'test33', 'test33@example.com', 'test33', '2025-07-18 16:18:16'),
(2, 'test33', 'test33@example.com', '123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890', '2025-07-18 16:25:04'),
(3, 'test33', 'test33@example.com', 'test 33 message..', '2025-07-18 16:43:31'),
(4, 'sen', 'sen@example.com', 'test msg..', '2025-07-08 19:47:02'),
(5, 'sen', 'sen@example.com', 'test msg..', '2025-07-08 23:13:22'),
(6, 'test', 'test2@example.com', 'test msg', '2025-07-18 08:03:19'),
(7, 'test33', 'test@t.co', 'test33', '2025-07-18 08:04:05');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `contact_no` varchar(50) DEFAULT NULL,
  `whatsapp_no` varchar(50) DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `website_link` varchar(255) DEFAULT NULL,
  `google_map` text DEFAULT NULL,
  `fb_link` varchar(255) DEFAULT NULL,
  `insta_link` varchar(255) DEFAULT NULL,
  `linkedin_link` varchar(255) DEFAULT NULL,
  `x_link` varchar(255) DEFAULT NULL,
  `youtube_link` varchar(255) DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` text DEFAULT NULL,
  `google_analytics` text DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `fb` varchar(255) DEFAULT NULL,
  `instagram` varchar(255) DEFAULT NULL,
  `linkedin` varchar(255) DEFAULT NULL,
  `twitter` varchar(255) DEFAULT NULL,
  `youtube` varchar(255) DEFAULT NULL,
  `x` varchar(255) DEFAULT NULL,
  `favicon` varchar(255) DEFAULT NULL,
  `header_message` text DEFAULT NULL,
  `banner_images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `company_name`, `logo`, `contact_no`, `whatsapp_no`, `contact_email`, `address`, `website_link`, `google_map`, `fb_link`, `insta_link`, `linkedin_link`, `x_link`, `youtube_link`, `meta_title`, `meta_description`, `meta_keywords`, `google_analytics`, `website`, `fb`, `instagram`, `linkedin`, `twitter`, `youtube`, `x`, `favicon`, `header_message`, `banner_images`) VALUES
(1, 'My Company', 'swapped(2).jpg', '+911234567890', '', 'admin@mycompany.com', 'test', NULL, '', NULL, NULL, NULL, NULL, NULL, 'test', 'test', 'test', '', '', 'https://www.facebook.com/lotusinfosys/', 'https://www.instagram.com/lotusinfosys/', 'https://in.linkedin.com/company/lotusinfosys', NULL, 'https://www.youtube.com/user/LotusInfosys', 'https://x.com/Lotusinfosys', 'login-bg.jpg', 'Engae Koomapattiku vangaa...', '[\"68809093ae77e_banner2.avif\",\"68809093ae9d7_venkat_crackers.jpg\",\"68809093aec7f_venkat_diwali.jpeg\"]');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `blogs`
--
ALTER TABLE `blogs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_category_name` (`name`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `enquiries`
--
ALTER TABLE `enquiries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `blogs`
--
ALTER TABLE `blogs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=212;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `enquiries`
--
ALTER TABLE `enquiries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
