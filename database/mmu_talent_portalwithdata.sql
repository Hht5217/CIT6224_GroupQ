-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 22, 2025 at 09:04 AM
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
-- Database: `mmu_talent_portal`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `content`, `created_at`) VALUES
(3, 'Welcome to MMU Talent Showcase Portal', 'We are excited to announce the launch of our new talent showcase platform. This portal is designed to help MMU students showcase their talents and connect with opportunities.', '2025-06-01 04:00:00'),
(4, 'New Feature: Talent Categories', 'We have added new talent categories to better organize and showcase different types of talents. Please update your profile to select the most appropriate category for your talents.', '2025-06-03 00:30:00'),
(5, 'Upcoming Talent Show Event', 'Join us for our annual talent show event on December 15th. Registration is now open for all MMU students. Showcase your talents and win exciting prizes!', '2025-06-09 10:35:24'),
(6, 'Platform Maintenance Notice', 'The portal will be undergoing scheduled maintenance on Saturday, 10:00 PM to Sunday, 2:00 AM. We apologize for any inconvenience caused.', '2025-06-12 05:17:32'),
(7, 'New Partnership Opportunities', 'We are pleased to announce new partnerships with local businesses and organizations. These partnerships will provide more opportunities for our talented students.', '2025-06-19 07:26:44');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `product_id`, `quantity`, `created_at`, `updated_at`) VALUES
(10, 2, 1, 1, '2025-06-21 11:26:25', '2025-06-21 11:26:25'),
(11, 2, 5, 3, '2025-06-21 13:05:34', '2025-06-21 13:05:34');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `talent_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `user_id`, `talent_id`, `comment`, `parent_id`, `created_at`) VALUES
(5, 2, 5, 'give me some feedback', NULL, '2025-06-20 11:19:44'),
(6, 3, 5, 'how to download', 5, '2025-06-20 11:20:18'),
(7, 2, 5, 'reply test', 5, '2025-06-20 11:21:01');

-- --------------------------------------------------------

--
-- Table structure for table `faq`
--

CREATE TABLE `faq` (
  `id` int(11) NOT NULL,
  `question` text NOT NULL,
  `answer` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faq`
--

INSERT INTO `faq` (`id`, `question`, `answer`, `created_at`) VALUES
(2, 'What is MMU Talent Showcase Portal?', 'MMU Talent Showcase Portal is a platform designed to showcase the diverse talents of MMU students. Students can display their talents in various fields including music, art, technology, and writing.', '2025-06-10 04:00:00'),
(3, 'How do I register an account?', 'You can go to the registration page by clicking the \"Register\" button under the \"Account\" dropdown of the top bar, then fill in your personal information and click the \"Register\" button to complete registration.', '2025-06-10 04:00:00'),
(4, 'How do I manage my profile?', 'After logging in, go to your dashboard under \"Account\" dropdown, and find \"My Profile\" from the sidebar where you can update your personal information and update your bio.', '2025-06-10 04:00:00'),
(5, 'How can I showcase my talent?\r\n', 'After logging in, you can go to your dashboard under \"Account\" dropdown, then find \"My Talents\" on the sidebar and then choose to upload talent to let everyone discover your potential through your amazing works.', '2025-06-10 04:00:00'),
(6, 'How can I update my talents?', 'You can view your talent by either visiting them through the catalogue page, or from your dashboard \"My Talents\", then choose the \"Edit Talent\" button and a pop up will appear for you to update information about your talent. You can also choose to replace the file uploaded by clicking the \"Replace File\" button under the File section.', '2025-06-10 04:00:00'),
(7, 'How do I search for specific types of talents?', 'On the \"Talent Catalogue\" page, you can type in the search box to search by specific keywords or select any options from the dropdown to filter by talent category.', '2025-06-10 04:00:00'),
(8, 'How can I contact other user?', 'You can find contact information such as email and phone number by viewing other user\'s profile page. You can also leave comments on their work to initiate communication.\r\n', '2025-06-10 04:00:00'),
(9, 'How can I become an administrator?', 'Administrator accounts are assigned by the system administrator. If you need administrator privileges, please contact the system administrator.', '2025-06-10 04:00:00'),
(10, 'How do I delete my account?', 'Please contact the system administrator to delete your account. Note that deleting your account will also remove all associated profiles and works.', '2025-06-10 04:00:00'),
(12, 'How do I report inappropriate content?', 'If you find any inappropriate content, please contact the administrator immediately. You can email the administrator directly.', '2025-06-10 04:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `talent_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `favorites`
--

INSERT INTO `favorites` (`id`, `user_id`, `talent_id`, `created_at`) VALUES
(15, 2, 7, '2025-06-22 05:50:11');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `reply` text DEFAULT NULL,
  `status` enum('pending','in progress','resolved') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `user_id`, `subject`, `message`, `reply`, `status`, `created_at`, `updated_at`) VALUES
(1, 3, 'Test user feedback', 'This is a feedback', 'Hi hope it helps you', 'resolved', '2025-06-18 04:40:21', '2025-06-21 02:30:30'),
(2, 2, 'Please add new function', 'would really love it', NULL, 'pending', '2025-06-21 03:38:59', '2025-06-21 03:38:59');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `reference_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','processed','rejected','completed','cancelled') DEFAULT 'pending',
  `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `status`, `payment_status`, `created_at`, `updated_at`) VALUES
(1, 2, 1.00, 'rejected', 'refunded', '2025-06-18 06:55:12', '2025-06-22 03:18:22'),
(2, 2, 2.00, 'completed', 'pending', '2025-06-18 07:04:09', '2025-06-21 08:18:47'),
(4, 2, 1.00, 'cancelled', 'refunded', '2025-06-21 08:21:07', '2025-06-21 08:38:52');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`, `created_at`) VALUES
(1, 1, 1, 1, 1.00, '2025-06-18 06:55:12'),
(2, 2, 1, 2, 1.00, '2025-06-18 07:04:09'),
(4, 4, 1, 1, 1.00, '2025-06-21 08:21:07');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive','out of stock') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `user_id`, `title`, `description`, `price`, `category`, `image_url`, `status`, `created_at`, `updated_at`) VALUES
(1, 3, 'ProductSammy', 'This is Sammy\'s product cat pic', 1.00, 'tools_resources', 'uploads/products/6852592e49dbd.png', 'active', '2025-06-18 06:14:06', '2025-06-21 13:21:47'),
(5, 3, 'aa3123', '1231', 1.00, 'workshops_tutorials', 'assets/images/products/printables_stationery.jpg', 'active', '2025-06-21 12:19:55', '2025-06-21 13:11:53'),
(6, 3, 'add 5', 'again', 10.00, 'custom_commissions', 'assets/images/products/custom_commissions.jpg', 'out of stock', '2025-06-21 12:28:12', '2025-06-21 15:44:33');

-- --------------------------------------------------------

--
-- Table structure for table `profiles`
--

CREATE TABLE `profiles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `profiles`
--

INSERT INTO `profiles` (`id`, `user_id`, `profile_picture`, `phone`, `bio`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, NULL, 'System Administrator of TalentHub', '2025-06-17 13:38:15', '2025-06-17 13:38:15'),
(2, 2, 'assets/images/profiles/685604f412f9f.png', '123-1231', 'Im manny yes', '2025-06-17 23:52:07', '2025-06-21 01:23:42'),
(3, 3, NULL, '', '', '2025-06-19 08:51:32', '2025-06-19 08:51:36');

-- --------------------------------------------------------

--
-- Table structure for table `resources`
--

CREATE TABLE `resources` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `talent_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `file_size` int(11) NOT NULL,
  `is_downloadable` tinyint(1) DEFAULT 0,
  `download_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resources`
--

INSERT INTO `resources` (`id`, `user_id`, `talent_id`, `title`, `description`, `file_name`, `file_path`, `file_type`, `file_size`, `is_downloadable`, `download_count`, `created_at`) VALUES
(8, 2, 5, 'talent reupload test', 'test delete and reupload', 'level1.mp4', 'uploads/talents/685682d7329f2.mp4', 'video/mp4', 1633939, 1, 1, '2025-06-20 05:55:37'),
(13, 3, 7, 'nitro wallpaper', 'from pc', 'Nitro_Wallpaper_5000x2813.jpg', 'uploads/talents/6856d60905fdb.jpg', 'image/jpeg', 3604542, 1, 0, '2025-06-21 15:55:53'),
(14, 3, NULL, 'another bg', 'from pc aggain', 'Planet9_Wallpaper_5000x2813.jpg', 'uploads/resources/685718562252a.jpg', 'image/jpeg', 5761442, 1, 0, '2025-06-21 20:38:46'),
(15, 2, 8, 'talent 2', 'check it oout', '2412325930-hd-32effgem.png', 'uploads/talents/6857931ba7c13.png', 'image/png', 36075, 1, 0, '2025-06-22 05:22:35');

-- --------------------------------------------------------

--
-- Table structure for table `statistics`
--

CREATE TABLE `statistics` (
  `id` int(11) NOT NULL,
  `talent_id` int(11) NOT NULL,
  `views` int(11) DEFAULT 0,
  `downloads` int(11) DEFAULT 0,
  `favorites` int(11) DEFAULT 0,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `statistics`
--

INSERT INTO `statistics` (`id`, `talent_id`, `views`, `downloads`, `favorites`, `last_updated`) VALUES
(1, 2, 1, 0, 0, '2025-06-18 00:00:51'),
(2, 2, 1, 0, 0, '2025-06-18 00:01:06'),
(3, 2, 1, 0, 0, '2025-06-18 00:01:06'),
(4, 2, 1, 0, 0, '2025-06-18 00:01:11'),
(5, 2, 1, 0, 0, '2025-06-18 01:40:30'),
(6, 2, 0, 0, 1, '2025-06-18 01:40:32'),
(7, 2, 1, 0, 0, '2025-06-18 01:40:32'),
(8, 2, 1, 0, 0, '2025-06-18 01:42:08'),
(9, 2, 1, 0, 0, '2025-06-18 05:01:44'),
(10, 2, 1, 0, 0, '2025-06-18 07:15:20'),
(11, 2, 0, 0, 1, '2025-06-18 07:15:21'),
(12, 2, 1, 0, 0, '2025-06-18 07:15:21'),
(13, 2, 1, 0, 0, '2025-06-18 07:15:30'),
(14, 2, 1, 0, 0, '2025-06-18 07:15:30'),
(15, 2, 1, 0, 0, '2025-06-18 07:15:36'),
(16, 2, 1, 0, 0, '2025-06-18 07:15:43'),
(17, 2, 1, 0, 0, '2025-06-18 07:15:43'),
(18, 3, 1, 0, 0, '2025-06-18 07:15:54'),
(19, 3, 1, 0, 0, '2025-06-18 07:15:59'),
(20, 3, 1, 0, 0, '2025-06-18 07:15:59'),
(21, 3, 1, 0, 0, '2025-06-18 07:18:26'),
(22, 3, 1, 0, 0, '2025-06-18 07:18:35'),
(23, 3, 1, 0, 0, '2025-06-18 07:18:35'),
(24, 2, 1, 0, 0, '2025-06-18 07:58:47'),
(25, 3, 1, 0, 0, '2025-06-18 07:59:44'),
(26, 3, 1, 0, 0, '2025-06-18 08:09:16'),
(27, 3, 1, 0, 0, '2025-06-18 08:22:36'),
(28, 2, 1, 0, 0, '2025-06-18 08:22:40'),
(29, 3, 1, 0, 0, '2025-06-18 08:29:16'),
(30, 3, 1, 0, 0, '2025-06-18 08:29:27'),
(31, 3, 1, 0, 0, '2025-06-18 08:33:28'),
(32, 3, 1, 0, 0, '2025-06-18 08:36:11'),
(33, 3, 1, 0, 0, '2025-06-18 08:36:27'),
(34, 3, 1, 0, 0, '2025-06-18 08:36:36'),
(35, 2, 1, 0, 0, '2025-06-18 08:36:43'),
(36, 3, 1, 0, 0, '2025-06-18 08:36:55'),
(37, 2, 1, 0, 0, '2025-06-18 08:37:08'),
(38, 2, 1, 0, 0, '2025-06-18 08:37:19'),
(39, 2, 1, 0, 0, '2025-06-18 08:37:19'),
(40, 2, 1, 0, 0, '2025-06-18 08:47:00'),
(41, 2, 1, 0, 0, '2025-06-18 08:47:50'),
(42, 2, 1, 0, 0, '2025-06-18 08:49:41'),
(43, 3, 1, 0, 0, '2025-06-18 08:50:08'),
(44, 2, 1, 0, 0, '2025-06-18 08:50:15'),
(45, 2, 1, 0, 0, '2025-06-18 08:54:05'),
(46, 3, 1, 0, 0, '2025-06-18 08:54:18'),
(47, 2, 1, 0, 0, '2025-06-18 08:54:29'),
(48, 2, 1, 0, 0, '2025-06-18 08:58:36'),
(49, 2, 1, 0, 0, '2025-06-18 09:00:38'),
(50, 2, 1, 0, 0, '2025-06-18 09:01:01'),
(51, 2, 1, 0, 0, '2025-06-18 09:01:24'),
(52, 2, 1, 0, 0, '2025-06-18 09:01:57'),
(53, 2, 1, 0, 0, '2025-06-18 09:02:56'),
(54, 2, 1, 0, 0, '2025-06-18 09:03:16'),
(55, 2, 1, 0, 0, '2025-06-18 09:03:59'),
(56, 2, 1, 0, 0, '2025-06-18 09:05:35'),
(57, 2, 1, 0, 0, '2025-06-18 09:09:12'),
(58, 2, 1, 0, 0, '2025-06-19 07:34:55'),
(59, 2, 1, 0, 0, '2025-06-19 07:35:08'),
(60, 2, 1, 0, 0, '2025-06-19 07:37:24'),
(61, 2, 0, 0, 1, '2025-06-19 07:37:25'),
(62, 2, 1, 0, 0, '2025-06-19 07:37:25'),
(63, 2, 1, 0, 0, '2025-06-19 07:37:32'),
(64, 3, 1, 0, 0, '2025-06-19 07:37:40'),
(65, 3, 0, 0, 1, '2025-06-19 07:37:41'),
(66, 3, 1, 0, 0, '2025-06-19 07:37:41'),
(67, 3, 1, 0, 0, '2025-06-19 07:37:43'),
(68, 3, 1, 0, 0, '2025-06-19 07:40:23'),
(69, 2, 1, 0, 0, '2025-06-19 07:41:13'),
(70, 3, 1, 0, 0, '2025-06-19 07:44:53'),
(71, 3, 1, 0, 0, '2025-06-19 07:48:30'),
(72, 2, 1, 0, 0, '2025-06-19 07:48:32'),
(73, 2, 1, 0, 0, '2025-06-19 08:43:54'),
(74, 3, 1, 0, 0, '2025-06-19 22:14:14');

-- --------------------------------------------------------

--
-- Table structure for table `talents`
--

CREATE TABLE `talents` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `category` varchar(50) NOT NULL,
  `media_path` varchar(255) DEFAULT NULL,
  `view_count` int(11) DEFAULT 0,
  `favorites_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `talents`
--

INSERT INTO `talents` (`id`, `user_id`, `title`, `description`, `category`, `media_path`, `view_count`, `favorites_count`, `created_at`) VALUES
(5, 2, 'talent reupload test edited', 'test delete and reupload, edited', 'tech', 'uploads/talents/685682d7329f2.mp4', 10, 0, '2025-06-20 05:55:37'),
(7, 3, 'nitro wallpaper', 'from pc', 'design', 'uploads/talents/6856d60905fdb.jpg', 7, 1, '2025-06-21 15:55:53'),
(8, 2, 'talent 2', 'check it oout', 'design', 'uploads/talents/6857931ba7c13.png', 3, 0, '2025-06-22 05:22:35');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `full_name`, `role`, `created_at`) VALUES
(1, 'admin', 'admin123', 'admin@talenthub.com', 'System Administrator', 'admin', '2025-06-17 13:38:15'),
(2, 'Manny876', 'Manny876', 'manny@hi.com', 'Manuel', 'user', '2025-06-17 23:48:31'),
(3, 'SAMMY123', 'Sammy123', 'sammy@hi.com', 'Samuel', 'user', '2025-06-18 01:44:47'),
(5, 'empty456', 'empty456', 'empty@blank.com', 'EmptyTest', 'user', '2025-06-20 11:10:22');

-- --------------------------------------------------------

--
-- Table structure for table `user_questions`
--

CREATE TABLE `user_questions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `question` text NOT NULL,
  `answer` text DEFAULT NULL,
  `status` enum('pending','answered','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_questions`
--

INSERT INTO `user_questions` (`id`, `user_id`, `question`, `answer`, `status`, `created_at`, `updated_at`) VALUES
(1, 3, 'Test', 'test answer', 'answered', '2025-06-18 04:43:24', '2025-06-18 04:44:39');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `comments_ibfk_3` (`parent_id`),
  ADD KEY `comments_ibfk_2` (`talent_id`);

--
-- Indexes for table `faq`
--
ALTER TABLE `faq`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_favorite` (`user_id`,`talent_id`),
  ADD KEY `favorites_ibfk_2` (`talent_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_type` (`type`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `profiles`
--
ALTER TABLE `profiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `resources`
--
ALTER TABLE `resources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fk_resources_talent` (`talent_id`);

--
-- Indexes for table `statistics`
--
ALTER TABLE `statistics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `talent_id` (`talent_id`);

--
-- Indexes for table `talents`
--
ALTER TABLE `talents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_questions`
--
ALTER TABLE `user_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `faq`
--
ALTER TABLE `faq`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `profiles`
--
ALTER TABLE `profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `resources`
--
ALTER TABLE `resources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `statistics`
--
ALTER TABLE `statistics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT for table `talents`
--
ALTER TABLE `talents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user_questions`
--
ALTER TABLE `user_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`talent_id`) REFERENCES `talents` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`talent_id`) REFERENCES `talents` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `profiles`
--
ALTER TABLE `profiles`
  ADD CONSTRAINT `profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `resources`
--
ALTER TABLE `resources`
  ADD CONSTRAINT `fk_resources_talent` FOREIGN KEY (`talent_id`) REFERENCES `talents` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `resources_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `statistics`
--
ALTER TABLE `statistics`
  ADD CONSTRAINT `statistics_ibfk_1` FOREIGN KEY (`talent_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `talents`
--
ALTER TABLE `talents`
  ADD CONSTRAINT `talents_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_questions`
--
ALTER TABLE `user_questions`
  ADD CONSTRAINT `user_questions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
