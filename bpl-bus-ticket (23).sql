-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Oct 02, 2025 at 07:57 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.1.17

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bpl-bus-ticket`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `type` varchar(20) NOT NULL DEFAULT 'employee',
  `permissions` text DEFAULT NULL COMMENT 'JSON formatted permissions',
  `last_login_time` datetime DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL,
  `session_token` varchar(255) DEFAULT NULL,
  `name` varchar(110) NOT NULL,
  `mobile` varchar(110) NOT NULL,
  `email` varchar(110) NOT NULL,
  `password` varchar(1100) NOT NULL,
  `password_salt` varchar(110) NOT NULL,
  `status` varchar(2) NOT NULL DEFAULT '1' COMMENT '1= active, 2 deactive',
  `linked_staff_id` int(11) DEFAULT NULL,
  `ip_address` varchar(110) DEFAULT NULL,
  `date_time` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `type`, `permissions`, `last_login_time`, `last_login_ip`, `session_token`, `name`, `mobile`, `email`, `password`, `password_salt`, `status`, `linked_staff_id`, `ip_address`, `date_time`) VALUES
(1, 'main_admin', '{\"all_access\": true}', '2025-09-30 10:07:20', '::1', 'a1d9bd16bf8a0c28836bb9d5d575249513621178906dd9a5ef3872081f1d8d60', 'Dev Sheoran', '8930000210', 'admin@gmail.com', '$2y$10$1izEIiYcwHnHriC5zYX.KOOr2az22mXg/3FnbRwwS33b.aPSr33Z2', '123456', '1', NULL, '::1', '2025-07-23 13:36:33'),
(16, 'employee', '{\"can_book_tickets\":true,\"can_view_bookings\":true,\"can_manage_cancellations\":true,\"can_manage_routes\":true,\"can_delete_routes\":true,\"can_charter_bus\":true,\"can_toggle_popular_route\":true,\"can_view_own_collections\":true}', '2025-09-27 17:53:26', '::1', NULL, 'Akash Sheoran', '9876543213', 'akash@gmail.com', '$2y$10$nYP5f7sgR0G1niAOxyTkTOpxlyJqFbHTZhQFyQiw7b/ybgRy9/YJi', 'bcrypt', '1', 57, NULL, '2025-09-23 09:53:35'),
(17, 'employee', '{\"can_book_tickets\":true,\"can_view_bookings\":true,\"can_delete_bookings\":true,\"can_manage_routes\":true,\"can_delete_routes\":true,\"can_charter_bus\":true,\"can_toggle_popular_route\":true,\"can_view_own_collections\":true,\"can_view_reports\":true}', '2025-09-26 17:46:04', '192.168.1.50', NULL, 'Dev Sheoran', '8930000211', 'dev@gmail.com', '$2y$10$.6v.qRXFTF925k0I0wPlNud6uYVQHC5oMrG5L0TAQlZ84xHQroQfe', 'bcrypt', '1', 55, NULL, '2025-09-23 09:54:39'),
(18, 'employee', '{\"can_book_tickets\":true,\"can_view_bookings\":true,\"can_delete_bookings\":true,\"can_manage_routes\":true,\"can_delete_routes\":true,\"can_charter_bus\":true,\"can_toggle_popular_route\":true,\"can_manage_buses\":true,\"can_edit_buses\":true,\"can_delete_buses\":true,\"can_view_own_collections\":true,\"can_view_reports\":true,\"can_manage_employees\":true,\"can_manage_settings\":true,\"main_admin\":true}', NULL, NULL, NULL, 'Dinesh Sharma', '9876543243', 'dinesh@gmail.com', '$2y$10$GZ6w9LqXnlYshYVgPqi4u.khPhhODH3VFY7vb0MXX0.aTlfk28VXe', 'bcrypt', '1', 59, NULL, '2025-09-23 09:55:32');

-- --------------------------------------------------------

--
-- Table structure for table `admin_activity_log`
--

CREATE TABLE `admin_activity_log` (
  `log_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `admin_name` varchar(255) NOT NULL,
  `activity_type` varchar(50) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `device_type` varchar(50) DEFAULT NULL,
  `os` varchar(100) DEFAULT NULL,
  `browser` varchar(100) DEFAULT NULL,
  `geo_lat` decimal(10,8) DEFAULT NULL,
  `geo_long` decimal(11,8) DEFAULT NULL,
  `captured_image` varchar(255) DEFAULT NULL,
  `log_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_activity_log`
--

INSERT INTO `admin_activity_log` (`log_id`, `admin_id`, `admin_name`, `activity_type`, `ip_address`, `user_agent`, `device_type`, `os`, `browser`, `geo_lat`, `geo_long`, `captured_image`, `log_time`) VALUES
(264, 16, 'Akash Sheoran', 'login', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'desktop', 'macOS', 'Chrome 140', 28.51099397, 75.94573076, 'capture_16_1758888132.jpg', '2025-09-26 12:02:12'),
(265, 16, 'Akash Sheoran', 'logout', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'desktop', 'macOS', 'Chrome 140', 28.51099398, 75.94573075, 'capture_16_1758888251.jpg', '2025-09-26 12:04:11'),
(266, 1, 'Dev Sheoran', 'failed_attempt', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'desktop', 'macOS', 'Chrome 140', 28.51099398, 75.94573075, 'capture_1_1758888257.jpg', '2025-09-26 12:04:17'),
(267, 1, 'Dev Sheoran', 'failed_attempt', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'desktop', 'macOS', 'Chrome 140', 28.51099398, 75.94573075, 'capture_1_1758888261.jpg', '2025-09-26 12:04:21'),
(268, 16, 'Akash Sheoran', 'login', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'desktop', 'macOS', 'Chrome 140', 28.51099398, 75.94573075, 'capture_16_1758888269.jpg', '2025-09-26 12:04:29'),
(269, 16, 'Akash Sheoran', 'logout', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'desktop', 'macOS', 'Chrome 140', 28.51099398, 75.94573075, 'capture_16_1758888278.jpg', '2025-09-26 12:04:38'),
(270, 1, 'Dev Sheoran', 'failed_attempt', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'desktop', 'macOS', 'Chrome 140', 28.51099398, 75.94573075, 'capture_1_1758888296.jpg', '2025-09-26 12:04:56'),
(271, 1, 'Dev Sheoran', 'login', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'desktop', 'macOS', 'Chrome 140', 28.51099398, 75.94573075, 'capture_1_1758888301.jpg', '2025-09-26 12:05:01'),
(272, 1, 'Dev Sheoran', 'logout', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'desktop', 'macOS', 'Chrome 140', 28.51099398, 75.94573075, 'capture_1_1758888374.jpg', '2025-09-26 12:06:14'),
(273, 16, 'Akash Sheoran', 'login', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'desktop', 'macOS', 'Chrome 140', 28.51092923, 75.94580064, 'capture_16_1758888390.jpg', '2025-09-26 12:06:30'),
(274, 16, 'Akash Sheoran', 'logout', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'desktop', 'macOS', 'Chrome 140', 28.51092910, 75.94580078, 'capture_16_1758888397.jpg', '2025-09-26 12:06:37'),
(275, 1, 'Dev Sheoran', 'login', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'desktop', 'macOS', 'Chrome 140', 28.51092906, 75.94580082, 'capture_1_1758888421.jpg', '2025-09-26 12:07:01'),
(276, 16, 'Akash Sheoran', 'logout', '192.168.1.50', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Mobile Safari/537.36', 'mobile', 'Android 10', 'Chrome 140', NULL, NULL, NULL, '2025-09-26 12:15:00'),
(277, 16, 'Akash Sheoran', 'login', '192.168.1.50', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Mobile Safari/537.36', 'mobile', 'Android 10', 'Chrome 140', NULL, NULL, 'capture_16_1758888911.jpg', '2025-09-26 12:15:11'),
(278, 16, 'Akash Sheoran', 'logout', '192.168.1.50', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Mobile Safari/537.36', 'mobile', 'Android 10', 'Chrome 140', 28.51109590, 75.94586180, 'capture_16_1758888930.jpg', '2025-09-26 12:15:30'),
(279, 17, 'Dev Sheoran', 'failed_attempt', '192.168.1.50', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Mobile Safari/537.36', 'mobile', 'Android 10', 'Chrome 140', 28.51109590, 75.94586180, 'capture_17_1758888961.jpg', '2025-09-26 12:16:01'),
(280, 17, 'Dev Sheoran', 'login', '192.168.1.50', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Mobile Safari/537.36', 'mobile', 'Android 10', 'Chrome 140', 28.51109590, 75.94586180, 'capture_17_1758888964.jpg', '2025-09-26 12:16:04'),
(281, 16, 'Akash Sheoran', 'login', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'desktop', 'macOS', 'Chrome 140', 28.51099399, 75.94573074, 'capture_16_1758889701.jpg', '2025-09-26 12:28:21'),
(282, 16, 'Akash Sheoran', 'logout', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'desktop', 'macOS', 'Chrome 140', 28.51099398, 75.94573075, 'capture_16_1758889708.jpg', '2025-09-26 12:28:28'),
(283, 1, 'Dev Sheoran', 'failed_attempt', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'desktop', 'macOS', 'Chrome 140', 28.51131978, 75.94573242, 'capture_1_1758946737.jpg', '2025-09-27 04:18:57'),
(284, 1, 'Dev Sheoran', 'login', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'desktop', 'macOS', 'Chrome 140', 28.51178342, 75.94546518, 'capture_1_1758946741.jpg', '2025-09-27 04:19:01'),
(285, 1, 'Dev Sheoran', 'login', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'desktop', 'macOS', 'Chrome 140', 28.51131978, 75.94573242, 'capture_1_1758946817.jpg', '2025-09-27 04:20:17'),
(286, 1, 'Dev Sheoran', 'failed_attempt', '192.168.1.54', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'desktop', 'Windows 10', 'Chrome 140', NULL, NULL, NULL, '2025-09-27 06:36:55'),
(287, 1, 'Dev Sheoran', 'failed_attempt', '192.168.1.54', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'desktop', 'Windows 10', 'Chrome 140', NULL, NULL, NULL, '2025-09-27 06:37:01'),
(288, 1, 'Dev Sheoran', 'login', '192.168.1.54', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'desktop', 'Windows 10', 'Chrome 140', NULL, NULL, NULL, '2025-09-27 06:37:05'),
(289, 1, 'Dev Sheoran', 'failed_attempt', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Mobile Safari/537.36', 'mobile', 'Android 6.0', 'Chrome 140', 28.51093101, 75.94580078, 'capture_1_1758955064.jpg', '2025-09-27 06:37:44'),
(290, 1, 'Dev Sheoran', 'login', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Mobile Safari/537.36', 'mobile', 'Android 6.0', 'Chrome 140', 28.51099486, 75.94573076, 'capture_1_1758955067.jpg', '2025-09-27 06:37:47'),
(291, 1, 'Dev Sheoran', 'login', '192.168.1.54', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'desktop', 'Windows 10', 'Chrome 140', NULL, NULL, NULL, '2025-09-27 06:38:11'),
(292, 1, 'Dev Sheoran', 'login', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Mobile Safari/537.36', 'mobile', 'Android 6.0', 'Chrome 140', 28.51099488, 75.94573073, 'capture_1_1758955259.jpg', '2025-09-27 06:40:59'),
(293, 1, 'Dev Sheoran', 'login', '192.168.1.54', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'desktop', 'Windows 10', 'Chrome 140', NULL, NULL, NULL, '2025-09-27 06:46:30'),
(294, 1, 'Dev Sheoran', 'login', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Mobile Safari/537.36', 'mobile', 'Android 6.0', 'Chrome 140', 28.51099486, 75.94573075, 'capture_1_1758955652.jpg', '2025-09-27 06:47:32'),
(295, 16, 'Akash Sheoran', 'login', '192.168.1.49', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Mobile Safari/537.36', 'mobile', 'Android 10', 'Chrome 140', NULL, NULL, NULL, '2025-09-27 09:53:54'),
(296, 1, 'Dev Sheoran', 'logout', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'desktop', 'macOS', 'Chrome 140', 28.51104927, 75.94567108, NULL, '2025-09-27 12:06:27'),
(297, 1, 'Dev Sheoran', 'failed_attempt', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'desktop', 'macOS', 'Chrome 140', 28.51099486, 75.94573075, 'capture_1_1758974794.jpg', '2025-09-27 12:06:34'),
(298, 1, 'Dev Sheoran', 'login', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'desktop', 'macOS', 'Chrome 140', 28.51099486, 75.94573075, 'capture_1_1758974796.jpg', '2025-09-27 12:06:36'),
(299, 1, 'Dev Sheoran', 'logout', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'desktop', 'macOS', 'Chrome 140', 28.51099486, 75.94573075, NULL, '2025-09-27 12:06:40'),
(300, 1, 'Dev Sheoran', 'failed_attempt', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'desktop', 'macOS', 'Chrome 140', 28.51099486, 75.94573075, 'capture_1_1758974809.jpg', '2025-09-27 12:06:49'),
(301, 1, 'Dev Sheoran', 'login', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'desktop', 'macOS', 'Chrome 140', 28.51099486, 75.94573075, 'capture_1_1758974814.jpg', '2025-09-27 12:06:54'),
(302, 16, 'Akash Sheoran', 'logout', '192.168.1.50', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Mobile Safari/537.36', 'mobile', 'Android 10', 'Chrome 140', NULL, NULL, NULL, '2025-09-27 12:07:09'),
(303, 1, 'Dev Sheoran', 'login', '192.168.1.50', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Mobile Safari/537.36', 'mobile', 'Android 10', 'Chrome 140', NULL, NULL, NULL, '2025-09-27 12:07:24'),
(304, 1, 'Dev Sheoran', 'login', '192.168.1.50', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Mobile Safari/537.36', 'mobile', 'Android 10', 'Chrome 140', NULL, NULL, NULL, '2025-09-27 12:08:23'),
(305, 1, 'Dev Sheoran', 'logout', '192.168.1.50', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Mobile Safari/537.36', 'mobile', 'Android 10', 'Chrome 140', NULL, NULL, NULL, '2025-09-27 12:09:51'),
(306, 16, 'Akash Sheoran', 'login', '192.168.1.50', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Mobile Safari/537.36', 'mobile', 'Android 10', 'Chrome 140', NULL, NULL, NULL, '2025-09-27 12:09:59'),
(307, 16, 'Akash Sheoran', 'logout', '192.168.1.50', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Mobile Safari/537.36', 'mobile', 'Android 10', 'Chrome 140', NULL, NULL, NULL, '2025-09-27 12:10:07'),
(308, 1, 'Dev Sheoran', 'login', '192.168.1.50', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Mobile Safari/537.36', 'mobile', 'Android 10', 'Chrome 140', NULL, NULL, NULL, '2025-09-27 12:10:16'),
(309, 1, 'Dev Sheoran', 'login', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'desktop', 'macOS', 'Chrome 140', 28.51099486, 75.94573075, 'capture_1_1758975059.jpg', '2025-09-27 12:10:59'),
(310, 1, 'Dev Sheoran', 'logout', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'desktop', 'macOS', 'Chrome 140', 28.51099486, 75.94573075, NULL, '2025-09-27 12:11:44'),
(311, 1, 'Dev Sheoran', 'login', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'desktop', 'macOS', 'Chrome 140', 28.51099486, 75.94573075, 'capture_1_1758975121.jpg', '2025-09-27 12:12:01'),
(312, 1, 'Dev Sheoran', 'logout', '192.168.1.50', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Mobile Safari/537.36', 'mobile', 'Android 10', 'Chrome 140', NULL, NULL, NULL, '2025-09-27 12:17:16'),
(313, 1, 'Dev Sheoran', 'logout', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'desktop', 'macOS', 'Chrome 140', 28.51099486, 75.94573075, NULL, '2025-09-27 12:18:03'),
(314, 1, 'Dev Sheoran', 'login', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', 'mobile', 'iOS 18.5', 'Safari', 28.51099486, 75.94573075, NULL, '2025-09-27 12:22:03'),
(315, 1, 'Dev Sheoran', 'logout', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', 'mobile', 'iOS 18.5', 'Safari', 28.51104927, 75.94567108, NULL, '2025-09-27 12:22:09'),
(316, 1, 'Dev Sheoran', 'failed_attempt', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', 'mobile', 'iOS 18.5', 'Safari', 28.51104927, 75.94567108, NULL, '2025-09-27 12:22:27'),
(317, 1, 'Dev Sheoran', 'login', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', 'mobile', 'iOS 18.5', 'Safari', 28.51104927, 75.94567108, NULL, '2025-09-27 12:22:31'),
(318, 1, 'Dev Sheoran', 'logout', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'desktop', 'macOS', 'Chrome 140', 28.51104927, 75.94567108, NULL, '2025-09-27 12:23:15'),
(319, 16, 'Akash Sheoran', 'login', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'desktop', 'macOS', 'Chrome 140', 28.51104927, 75.94567108, NULL, '2025-09-27 12:23:26'),
(320, 16, 'Akash Sheoran', 'logout', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'desktop', 'macOS', 'Chrome 140', 28.51099492, 75.94573069, NULL, '2025-09-27 12:23:30'),
(321, 1, 'Dev Sheoran', 'login', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'desktop', 'macOS', 'Chrome 140', 28.51099484, 75.94573078, NULL, '2025-09-27 12:23:36'),
(322, 1, 'Dev Sheoran', 'failed_attempt', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'desktop', 'macOS', 'Chrome 140', 28.51117246, 75.94577884, NULL, '2025-09-29 04:28:10'),
(323, 1, 'Dev Sheoran', 'login', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'desktop', 'macOS', 'Chrome 140', 28.51117246, 75.94577884, NULL, '2025-09-29 04:28:14'),
(324, 1, 'Dev Sheoran', 'failed_attempt', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'desktop', 'macOS', 'Chrome 140', 28.51099486, 75.94573075, NULL, '2025-09-29 06:39:34'),
(325, 1, 'Dev Sheoran', 'login', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'desktop', 'macOS', 'Chrome 140', 28.51123214, 75.94565044, NULL, '2025-09-29 06:39:39'),
(326, 1, 'Dev Sheoran', 'login', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'desktop', 'macOS', 'Chrome 140', 28.51093103, 75.94580076, NULL, '2025-09-29 07:48:34'),
(327, 1, 'Dev Sheoran', 'login', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'desktop', 'macOS', 'Chrome 140', 28.51123215, 75.94565044, NULL, '2025-09-30 04:27:05'),
(328, 1, 'Dev Sheoran', 'login', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'desktop', 'macOS', 'Chrome 140', 28.51143926, 75.94574777, NULL, '2025-09-30 04:37:20');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL,
  `ticket_no` varchar(20) DEFAULT NULL,
  `route_id` int(11) NOT NULL,
  `bus_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `booked_by_employee_id` int(11) DEFAULT NULL,
  `origin` varchar(255) NOT NULL,
  `destination` varchar(255) NOT NULL,
  `contact_name` varchar(255) DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `contact_mobile` varchar(20) DEFAULT NULL,
  `travel_date` date NOT NULL,
  `total_fare` decimal(10,2) NOT NULL,
  `payment_status` enum('PAID','PENDING','FAILED','REFUNDED') NOT NULL DEFAULT 'PENDING',
  `gateway_order_id` varchar(255) DEFAULT NULL,
  `booking_status` enum('CONFIRMED','CANCELLED','PENDING') NOT NULL DEFAULT 'CONFIRMED',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`booking_id`, `ticket_no`, `route_id`, `bus_id`, `user_id`, `booked_by_employee_id`, `origin`, `destination`, `contact_name`, `contact_email`, `contact_mobile`, `travel_date`, `total_fare`, `payment_status`, `gateway_order_id`, `booking_status`, `created_at`) VALUES
(256, 'BPL858433916', 33, 26, 9, NULL, 'Jaipur,Rajasthan', 'Ludhiana,Punjab', 'Sanjay Kumar Sheoran', 'sjsheoran111@gmail.com', '9728833428', '2025-09-24', 2600.00, 'PAID', 'order_RL1kB4gQhAUQh9', 'CONFIRMED', '2025-09-23 11:30:46'),
(258, 'BPL976503945', 32, 26, 11, NULL, 'Bhiwani,hashi Gate', 'Badhra,Haryana', 'Sanjay Kumar Sheoran', '32@gmail.com', '8905288939', '2025-09-27', 60.00, 'PAID', 'order_RLLF0i17UaHLPt', 'CONFIRMED', '2025-09-24 06:35:09'),
(259, 'BPL409670357', 33, 26, 11, NULL, 'Jaipur,Rajasthan', 'Jammu & Kashmir', 'Sanjay Kumar Sheoran', '32@gmail.com', '8905288939', '2025-09-29', 2800.00, 'PAID', 'order_RLLXGonul4auU4', 'CONFIRMED', '2025-09-24 06:52:25'),
(260, 'BPL251520339', 33, 26, 11, NULL, 'Jaipur,Rajasthan', 'Jammu & Kashmir', 'Sanjay Kumar Sheoran', 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-29', 14400.00, 'PAID', 'order_RLML83x1oCp7OE', 'CONFIRMED', '2025-09-24 07:39:35'),
(261, 'BPL515006489', 33, 26, 11, NULL, 'Jaipur,Rajasthan', 'Noida,Delhi', 'Rohit', 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-29', 3200.00, 'PAID', 'order_RLOcN2pqSH5YvQ', 'CONFIRMED', '2025-09-24 09:53:39'),
(262, 'BPL732970153', 32, 26, 11, NULL, 'Bhiwani,hashi Gate', 'Badhra,Haryana', 'Sanjay Kumar Sheoran', 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-27', 320.00, 'PAID', 'order_RLQ9PYl1dMS710', 'CONFIRMED', '2025-09-24 11:23:19'),
(263, 'BPL552239374', 32, 26, 11, NULL, 'Bhiwani,hashi Gate', 'Badhra,Haryana', 'Sanjay Kumar Sheoran', 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-27', 80.00, 'PAID', 'order_RLQciRpjNtarHC', 'CONFIRMED', '2025-09-24 11:51:00'),
(264, 'BPL367062334', 34, 26, 11, NULL, 'Delhi, kasmiri Gate', 'Pilani,Rajasthan', 'Sanjay Kumar Sheoran', 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-25', 25.00, 'PAID', 'order_RLRFSwt9wr99L9', 'CONFIRMED', '2025-09-24 12:27:45'),
(265, 'BPL390780733', 34, 26, 11, NULL, 'Delhi, kasmiri Gate', 'Pilani,Rajasthan', 'Sanjay Kumar Sheoran', 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-24', 25.00, 'PAID', 'order_RLRHJuaSeJ1cCC', 'CONFIRMED', '2025-09-24 12:29:27'),
(266, 'BPL690564742', 33, 26, 9, NULL, 'Jaipur,Rajasthan', 'Ludhiana,Punjab', 'SANJAY', 'sjsheoran111@gmail.com', '9728833428', '2025-09-30', 5200.00, 'PAID', 'order_RLhwqLSYNWsHje', 'CONFIRMED', '2025-09-25 05:09:41'),
(267, 'BPL568318833', 31, 25, 9, NULL, 'Delhi, kasmiri Gate', 'Pilani,Rajasthan', 'Sanjay Kumar Sheoran', 'sjsheoran111@gmail.com', '9728833428', '2025-09-25', 120.00, 'PAID', 'order_RLj2jlyNh3BcUy', 'CONFIRMED', '2025-09-25 05:52:28'),
(269, 'BPL620455379', 31, 25, 11, NULL, 'Delhi, kasmiri Gate', 'Pilani,Rajasthan', 'Sanjay Kumar Sheoran', 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-26', 720.00, 'PAID', 'order_RLoyuk2qfn6gKD', 'CONFIRMED', '2025-09-25 11:40:42'),
(270, 'BPL598472387', 31, 25, 9, NULL, 'Delhi, kasmiri Gate', 'Pilani,Rajasthan', 'Sanjay Kumar Sheoran', 'sjsheoran111@gmail.com', '9728833428', '2025-09-27', 120.00, 'PAID', 'order_RM9icdYINHhBri', 'CONFIRMED', '2025-09-26 07:58:05'),
(273, 'BPL176028385', 32, 26, 12, NULL, 'Bhiwani,hashi Gate', 'Badhra,Haryana', 'Dharmdev Sheoran', 'devdharm9@gmail.com', '8930000210', '2025-09-27', 200.00, 'PAID', 'order_RMEUDSuLOHFwln', 'CONFIRMED', '2025-09-26 12:37:40'),
(274, 'BPL063263009', 32, 26, NULL, 1, 'Juiii,Haryana', 'Badhra,Haryana', NULL, 'rohit@gmail.com', '8905288939', '2025-09-27', 30.00, 'PAID', NULL, 'CONFIRMED', '2025-09-27 08:16:35'),
(275, 'BPL015275664', 31, 25, 9, NULL, 'Delhi, kasmiri Gate', 'Pilani,Rajasthan', 'Sanjay Kumar Sheoran', 'sjsheoran111@gmail.com', '9728833428', '2025-09-29', 100.00, 'PAID', 'order_RNHjPtTLRIPfuE', 'CONFIRMED', '2025-09-29 04:27:09'),
(276, 'BPL261970633', 31, 25, 13, NULL, 'Delhi, kasmiri Gate', 'Pilani,Rajasthan', 'Sanjay Kumar Sheoran', '32@gmail.com', '9728833428', '2025-09-29', 100.00, 'PAID', 'order_RNJXB6IIVfAwl1', 'CONFIRMED', '2025-09-29 06:13:04'),
(277, 'BPL375254946', 32, 26, 11, NULL, 'Bhiwani,hashi Gate', 'Badhra,Haryana', 'Sanjay Kumar Sheoran', 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-29', 60.00, 'PAID', 'order_RNK0levj8zkjC4', 'CONFIRMED', '2025-09-29 06:40:56'),
(278, 'BPL719619035', 34, 26, 11, NULL, 'Delhi, kasmiri Gate', 'Pilani,Rajasthan', 'Sanjay Kumar Sheoran', 'rohitmechujaatji@gmail.com', '8905288939', '2025-10-01', 25.00, 'PAID', 'order_RNKBGi6s09yatO', 'CONFIRMED', '2025-09-29 06:50:52'),
(279, 'BPL803815686', 32, 26, NULL, 1, 'Bhiwani,hashi Gate', 'Juiii,Haryana', NULL, 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-30', 30.00, 'PAID', NULL, 'CONFIRMED', '2025-09-30 06:50:16');

-- --------------------------------------------------------

--
-- Table structure for table `buses`
--

CREATE TABLE `buses` (
  `bus_id` int(11) NOT NULL,
  `bus_name` varchar(255) NOT NULL,
  `registration_number` varchar(100) NOT NULL,
  `engine_no` varchar(100) DEFAULT NULL,
  `chassis_no` varchar(100) DEFAULT NULL,
  `bus_type` varchar(255) NOT NULL,
  `total_seats` int(11) NOT NULL DEFAULT 0,
  `seater_seats` int(11) DEFAULT 0,
  `sleeper_seats` int(11) DEFAULT 0,
  `amenities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`amenities`)),
  `description` text DEFAULT NULL,
  `status` enum('Active','Inactive','Under Maintenance','Retired') DEFAULT 'Active',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `buses`
--

INSERT INTO `buses` (`bus_id`, `bus_name`, `registration_number`, `engine_no`, `chassis_no`, `bus_type`, `total_seats`, `seater_seats`, `sleeper_seats`, `amenities`, `description`, `status`, `created_at`, `updated_at`) VALUES
(25, 'Bus no 1', 'HR 61 B 2973', '53453454353453', '4535435435345', 'AC Seater', 0, 0, 0, NULL, '', 'Active', '2025-09-23 15:26:41', '2025-09-23 15:26:41'),
(26, 'Bus no 2', 'HR 61 B 2917', '53453454353453', '4535435435345', 'Non-AC Seater', 0, 0, 0, NULL, '', 'Active', '2025-09-23 15:29:12', '2025-09-23 15:29:12');

-- --------------------------------------------------------

--
-- Table structure for table `bus_categories`
--

CREATE TABLE `bus_categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bus_categories`
--

INSERT INTO `bus_categories` (`category_id`, `category_name`, `status`, `created_at`) VALUES
(7, 'Luxury', 'Active', '2025-09-13 04:35:47'),
(8, 'Express', 'Active', '2025-09-13 04:36:02'),
(9, 'Local', 'Active', '2025-09-13 04:36:20'),
(10, 'AC', 'Active', '2025-09-13 04:36:32'),
(11, 'AC Seater', 'Active', '2025-09-13 04:36:47'),
(12, 'Non-AC Seater', 'Active', '2025-09-13 04:37:01'),
(13, 'AC Sleeper', 'Active', '2025-09-13 04:37:15'),
(14, 'Non-AC Sleeper', 'Active', '2025-09-13 04:37:29'),
(15, 'Seater-Sleeper Mix', 'Active', '2025-09-13 04:37:46'),
(16, 'Volvo / Scania:', 'Active', '2025-09-13 04:38:42'),
(17, 'Super Luxury / Platinum Class', 'Active', '2025-09-13 04:38:58'),
(18, 'Semi-Sleeper', 'Active', '2025-09-13 04:39:15'),
(19, 'Non-Stop', 'Active', '2025-09-13 04:39:26'),
(20, 'Limited Stops', 'Active', '2025-09-13 04:39:41'),
(21, 'Wi-Fi Onboard', 'Active', '2025-09-13 04:39:59'),
(22, 'Live Tracking', 'Active', '2025-09-13 04:40:38'),
(23, 'Charging Port', 'Active', '2025-09-13 04:40:54');

-- --------------------------------------------------------

--
-- Table structure for table `bus_category_map`
--

CREATE TABLE `bus_category_map` (
  `map_id` int(11) NOT NULL,
  `bus_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bus_category_map`
--

INSERT INTO `bus_category_map` (`map_id`, `bus_id`, `category_id`) VALUES
(104, 25, 10),
(105, 25, 11),
(106, 25, 23),
(107, 26, 10),
(108, 26, 11),
(109, 26, 13);

-- --------------------------------------------------------

--
-- Table structure for table `bus_images`
--

CREATE TABLE `bus_images` (
  `image_id` int(11) NOT NULL,
  `bus_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bus_images`
--

INSERT INTO `bus_images` (`image_id`, `bus_id`, `image_path`, `created_at`) VALUES
(36, 25, 'bus_25_1758621401_68d26ed9e8864.jpg', '2025-09-23 09:56:41');

-- --------------------------------------------------------

--
-- Table structure for table `cancellations`
--

CREATE TABLE `cancellations` (
  `cancellation_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `passenger_id` int(11) NOT NULL,
  `amount_refunded` decimal(10,2) NOT NULL,
  `cancellation_reason` varchar(255) DEFAULT NULL,
  `gateway_refund_id` varchar(255) DEFAULT NULL,
  `status` enum('COMPLETED','FAILED','PENDING') NOT NULL DEFAULT 'PENDING',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cancellations`
--

INSERT INTO `cancellations` (`cancellation_id`, `booking_id`, `passenger_id`, `amount_refunded`, `cancellation_reason`, `gateway_refund_id`, `status`, `created_at`) VALUES
(37, 258, 309, 60.00, 'Booked by Mistake', '23432432423423432', 'COMPLETED', '2025-09-24 06:36:47'),
(38, 259, 310, 2800.00, 'Medical Emergency', '23432432423423432', 'COMPLETED', '2025-09-24 06:52:53'),
(39, 260, 315, 0.00, 'Invalid bank details provided.', NULL, 'FAILED', '2025-09-24 07:45:09'),
(40, 260, 315, 2800.00, 'Booked by Mistake', '23432432423423432', 'COMPLETED', '2025-09-24 07:47:51'),
(41, 260, 314, 0.00, 'Technical error during processing.', NULL, 'FAILED', '2025-09-24 07:48:26'),
(42, 260, 314, 3000.00, 'Found a better option', '23432432423423432', 'COMPLETED', '2025-09-24 08:20:32'),
(43, 260, 313, 3000.00, 'Change of Plans', '23432432423423432', 'COMPLETED', '2025-09-24 08:22:20'),
(44, 260, 312, 2800.00, 'Booked by Mistake', '23432432423423432', 'COMPLETED', '2025-09-24 08:22:59'),
(45, 260, 311, 0.00, 'Invalid bank details provided.', NULL, 'FAILED', '2025-09-24 09:45:38'),
(46, 278, 366, 0.00, 'Booked by Mistake', NULL, 'PENDING', '2025-09-29 06:52:28');

-- --------------------------------------------------------

--
-- Table structure for table `cash_collections_log`
--

CREATE TABLE `cash_collections_log` (
  `collection_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `amount_collected` decimal(10,2) NOT NULL,
  `collected_by_admin_id` int(11) NOT NULL,
  `collected_from_employee_id` int(11) NOT NULL,
  `collection_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cash_collections_log`
--

INSERT INTO `cash_collections_log` (`collection_id`, `booking_id`, `amount_collected`, `collected_by_admin_id`, `collected_from_employee_id`, `collection_time`) VALUES
(118, 254, 140.00, 1, 16, '2025-09-23 12:11:24'),
(119, 250, 60.00, 1, 1, '2025-09-23 12:28:51'),
(120, 251, 60.00, 1, 1, '2025-09-23 12:28:51'),
(121, 252, 30.00, 1, 1, '2025-09-23 12:28:51'),
(122, 255, 5800.00, 1, 1, '2025-09-23 12:28:51'),
(123, 271, 60.00, 1, 1, '2025-09-27 07:50:14'),
(124, 272, 1800.00, 1, 1, '2025-09-27 07:50:14');

-- --------------------------------------------------------

--
-- Table structure for table `charter_bookings`
--

CREATE TABLE `charter_bookings` (
  `charter_id` int(11) NOT NULL,
  `route_id` int(11) NOT NULL,
  `travel_date` date NOT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `customer_mobile` varchar(20) DEFAULT NULL,
  `booked_by_admin_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `charter_bookings`
--

INSERT INTO `charter_bookings` (`charter_id`, `route_id`, `travel_date`, `customer_name`, `customer_mobile`, `booked_by_admin_id`, `created_at`) VALUES
(9, 32, '2025-09-25', 'Sanjay Sheoran', '8834384223', 1, '2025-09-23 11:01:06'),
(14, 33, '2025-09-24', 'Sanjay', '63636366', 1, '2025-09-24 05:30:54');

-- --------------------------------------------------------

--
-- Table structure for table `charter_inquiries`
--

CREATE TABLE `charter_inquiries` (
  `inquiry_id` int(11) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_mobile` varchar(20) NOT NULL,
  `from_location` varchar(255) NOT NULL,
  `to_location` varchar(255) NOT NULL,
  `journey_date` date NOT NULL,
  `trip_type` enum('One-Way','Round-Trip') NOT NULL DEFAULT 'One-Way',
  `return_date` date DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` enum('Pending','Contacted','Booked','Closed') NOT NULL DEFAULT 'Pending',
  `inquiry_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `charter_inquiries`
--

INSERT INTO `charter_inquiries` (`inquiry_id`, `customer_name`, `customer_mobile`, `from_location`, `to_location`, `journey_date`, `trip_type`, `return_date`, `message`, `status`, `inquiry_date`) VALUES
(1, 'SANJAY', '9876543212', 'rohtak', 'delhi', '2025-09-30', 'One-Way', NULL, '23342', 'Closed', '2025-09-27 06:19:54'),
(2, 'SANJAY', '9876543212', 'rohtak', 'delhi', '2025-09-30', 'One-Way', NULL, '23342', 'Closed', '2025-09-27 06:19:56'),
(3, 'Rohit', '1122334455', 'rohtak', 'delhi', '2025-09-27', 'Round-Trip', '2025-09-28', '', 'Contacted', '2025-09-27 07:16:36');

-- --------------------------------------------------------

--
-- Table structure for table `deleted_bookings`
--

CREATE TABLE `deleted_bookings` (
  `booking_id` int(11) NOT NULL,
  `ticket_no` varchar(20) DEFAULT NULL,
  `route_id` int(11) NOT NULL,
  `bus_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `booked_by_employee_id` int(11) DEFAULT NULL,
  `origin` varchar(255) NOT NULL,
  `destination` varchar(255) NOT NULL,
  `contact_name` varchar(255) DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `contact_mobile` varchar(20) DEFAULT NULL,
  `travel_date` date NOT NULL,
  `total_fare` decimal(10,2) NOT NULL,
  `payment_status` varchar(50) NOT NULL,
  `gateway_order_id` varchar(255) DEFAULT NULL,
  `booking_status` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `deleted_by_employee_id` int(11) NOT NULL,
  `deleted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reason_for_deletion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `deleted_bookings`
--

INSERT INTO `deleted_bookings` (`booking_id`, `ticket_no`, `route_id`, `bus_id`, `user_id`, `booked_by_employee_id`, `origin`, `destination`, `contact_name`, `contact_email`, `contact_mobile`, `travel_date`, `total_fare`, `payment_status`, `gateway_order_id`, `booking_status`, `created_at`, `deleted_by_employee_id`, `deleted_at`, `reason_for_deletion`) VALUES
(268, 'BPL531110007', 31, 25, 11, NULL, 'Delhi, kasmiri Gate', 'Pilani,Rajasthan', 'Sanjay Kumar Sheoran', 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-25', 1980.00, 'PAID', 'order_RLodWmYfk0yDwo', 'CONFIRMED', '2025-09-25 11:20:30', 1, '2025-09-25 11:29:34', 'Deleted by admin.');

-- --------------------------------------------------------

--
-- Table structure for table `deleted_buses`
--

CREATE TABLE `deleted_buses` (
  `bus_id` int(11) NOT NULL,
  `bus_name` varchar(255) NOT NULL,
  `registration_number` varchar(100) NOT NULL,
  `engine_no` varchar(100) DEFAULT NULL,
  `chassis_no` varchar(100) DEFAULT NULL,
  `bus_type` varchar(255) NOT NULL,
  `amenities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_by_employee_id` int(11) NOT NULL,
  `deleted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `deleted_passengers`
--

CREATE TABLE `deleted_passengers` (
  `passenger_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `seat_id` int(11) NOT NULL,
  `seat_code` varchar(50) NOT NULL,
  `passenger_name` varchar(255) NOT NULL,
  `passenger_mobile` varchar(20) DEFAULT NULL,
  `passenger_age` int(3) DEFAULT NULL,
  `passenger_gender` enum('MALE','FEMALE','OTHER') NOT NULL,
  `fare` decimal(10,2) NOT NULL,
  `passenger_status` enum('CONFIRMED','CANCELLED') NOT NULL DEFAULT 'CONFIRMED',
  `deleted_by_employee_id` int(11) NOT NULL,
  `deleted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `deleted_passengers`
--

INSERT INTO `deleted_passengers` (`passenger_id`, `booking_id`, `seat_id`, `seat_code`, `passenger_name`, `passenger_mobile`, `passenger_age`, `passenger_gender`, `fare`, `passenger_status`, `deleted_by_employee_id`, `deleted_at`) VALUES
(329, 268, 515, 'LP3', 'gfdgd', NULL, 22, 'MALE', 100.00, 'CONFIRMED', 1, '2025-09-25 11:29:34'),
(330, 268, 514, 'LP2', 'Sanjay', NULL, 23, 'MALE', 100.00, 'CONFIRMED', 1, '2025-09-25 11:29:34'),
(331, 268, 513, 'LP1', 'dshgdsfhs', NULL, 32, 'FEMALE', 100.00, 'CONFIRMED', 1, '2025-09-25 11:29:34'),
(332, 268, 516, 'LP4', 'Sandeep', NULL, 23, 'FEMALE', 100.00, 'CONFIRMED', 1, '2025-09-25 11:29:34'),
(333, 268, 518, 'LP6', 'fddsfd', NULL, 32, 'MALE', 100.00, 'CONFIRMED', 1, '2025-09-25 11:29:34'),
(334, 268, 517, 'LP5', 'fsd', NULL, 32, 'MALE', 100.00, 'CONFIRMED', 1, '2025-09-25 11:29:34'),
(335, 268, 522, 'LS1', 'sdffsd', NULL, 32, 'MALE', 60.00, 'CONFIRMED', 1, '2025-09-25 11:29:34'),
(336, 268, 523, 'LS2', 'f', NULL, 32, 'MALE', 60.00, 'CONFIRMED', 1, '2025-09-25 11:29:34'),
(337, 268, 525, 'LP9', 'Rss', NULL, 23, 'MALE', 100.00, 'CONFIRMED', 1, '2025-09-25 11:29:34'),
(338, 268, 524, 'LP8', 'fgdghfdhf', NULL, 32, 'MALE', 100.00, 'CONFIRMED', 1, '2025-09-25 11:29:34'),
(339, 268, 521, 'LP7', '343', NULL, 32, 'MALE', 100.00, 'CONFIRMED', 1, '2025-09-25 11:29:34'),
(340, 268, 532, 'UP7', '34', NULL, 32, 'MALE', 120.00, 'CONFIRMED', 1, '2025-09-25 11:29:34'),
(341, 268, 529, 'UP4', 'dfgdf', NULL, 32, 'MALE', 120.00, 'CONFIRMED', 1, '2025-09-25 11:29:34'),
(342, 268, 533, 'UP8', '43', NULL, 43, 'MALE', 120.00, 'CONFIRMED', 1, '2025-09-25 11:29:34'),
(343, 268, 530, 'UP5', 'fgdf', NULL, 32, 'MALE', 120.00, 'CONFIRMED', 1, '2025-09-25 11:29:34'),
(344, 268, 527, 'UP2', 'Rohit', NULL, 23, 'MALE', 120.00, 'CONFIRMED', 1, '2025-09-25 11:29:34'),
(345, 268, 534, 'UP9', '43', NULL, 43, 'FEMALE', 120.00, 'CONFIRMED', 1, '2025-09-25 11:29:34'),
(346, 268, 531, 'UP6', '4324', NULL, 23, 'MALE', 120.00, 'CONFIRMED', 1, '2025-09-25 11:29:34'),
(347, 268, 528, 'UP3', '3243243', NULL, 32, 'MALE', 120.00, 'CONFIRMED', 1, '2025-09-25 11:29:34');

-- --------------------------------------------------------

--
-- Table structure for table `deleted_routes`
--

CREATE TABLE `deleted_routes` (
  `route_id` int(11) NOT NULL,
  `bus_id` int(11) NOT NULL,
  `route_name` varchar(255) NOT NULL,
  `starting_point` varchar(255) NOT NULL,
  `ending_point` varchar(255) NOT NULL,
  `status` varchar(50) NOT NULL,
  `is_popular` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `deleted_by_employee_id` int(11) NOT NULL,
  `deleted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `deleted_route_staff_assignments`
--

CREATE TABLE `deleted_route_staff_assignments` (
  `assignment_id` int(11) NOT NULL,
  `route_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `role` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `deleted_route_staff_assignments`
--

INSERT INTO `deleted_route_staff_assignments` (`assignment_id`, `route_id`, `staff_id`, `role`) VALUES
(51, 28, 44, 'Driver'),
(52, 28, 44, 'Co-Driver'),
(53, 28, 39, 'Conductor'),
(54, 28, 39, 'Co-Conductor'),
(55, 28, 45, 'Helper');

-- --------------------------------------------------------

--
-- Table structure for table `deleted_route_stops`
--

CREATE TABLE `deleted_route_stops` (
  `stop_id` int(11) NOT NULL,
  `route_id` int(11) NOT NULL,
  `stop_name` varchar(255) NOT NULL,
  `stop_order` int(11) NOT NULL,
  `duration_from_start_minutes` int(11) DEFAULT 0,
  `price_seater_lower` decimal(10,2) DEFAULT NULL,
  `price_seater_upper` decimal(10,2) DEFAULT NULL,
  `price_sleeper_lower` decimal(10,2) DEFAULT NULL,
  `price_sleeper_upper` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `deleted_route_stops`
--

INSERT INTO `deleted_route_stops` (`stop_id`, `route_id`, `stop_name`, `stop_order`, `duration_from_start_minutes`, `price_seater_lower`, `price_seater_upper`, `price_sleeper_lower`, `price_sleeper_upper`) VALUES
(112, 28, 'gfdgdfg', 1, 34, 43.00, 434.00, 343.00, 43.00);

-- --------------------------------------------------------

--
-- Table structure for table `passengers`
--

CREATE TABLE `passengers` (
  `passenger_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `seat_id` int(11) NOT NULL,
  `seat_code` varchar(50) NOT NULL,
  `passenger_name` varchar(255) NOT NULL,
  `passenger_mobile` varchar(20) DEFAULT NULL,
  `passenger_age` int(3) DEFAULT NULL,
  `passenger_gender` enum('MALE','FEMALE','OTHER') NOT NULL,
  `fare` decimal(10,2) NOT NULL,
  `passenger_status` enum('CONFIRMED','CANCELLED') NOT NULL DEFAULT 'CONFIRMED'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `passengers`
--

INSERT INTO `passengers` (`passenger_id`, `booking_id`, `seat_id`, `seat_code`, `passenger_name`, `passenger_mobile`, `passenger_age`, `passenger_gender`, `fare`, `passenger_status`) VALUES
(307, 256, 537, 'LP3', 'Rohit', NULL, 22, 'MALE', 2600.00, 'CONFIRMED'),
(311, 260, 536, 'LP2', 'Sanjay', NULL, 26, 'FEMALE', 2800.00, 'CONFIRMED'),
(316, 261, 537, 'LP3', 'Rohit', NULL, 22, 'MALE', 1600.00, 'CONFIRMED'),
(317, 261, 541, 'LP6', 'Sanjay', NULL, 22, 'MALE', 1600.00, 'CONFIRMED'),
(318, 262, 545, 'UP1', 'wfasdfa', NULL, 42, 'MALE', 80.00, 'CONFIRMED'),
(319, 262, 541, 'LP6', 'dgfgds', NULL, 32, 'MALE', 60.00, 'CONFIRMED'),
(320, 262, 537, 'LP3', 'Rohit', NULL, 22, 'FEMALE', 60.00, 'CONFIRMED'),
(321, 262, 558, 'LP10', 'gfewrt', NULL, 52, 'OTHER', 60.00, 'CONFIRMED'),
(322, 262, 560, 'LP11', '324', NULL, 62, 'FEMALE', 60.00, 'CONFIRMED'),
(323, 263, 548, 'UP4', 'dfgdf', NULL, 22, 'MALE', 80.00, 'CONFIRMED'),
(324, 264, 541, 'LP6', 'fddsfd', NULL, 22, 'MALE', 25.00, 'CONFIRMED'),
(325, 265, 536, 'LP2', 'Rohit', NULL, 22, 'FEMALE', 25.00, 'CONFIRMED'),
(326, 266, 536, 'LP2', 'wrwrwe', NULL, 22, 'FEMALE', 2600.00, 'CONFIRMED'),
(327, 266, 537, 'LP3', 'gfdgd', NULL, 23, 'MALE', 2600.00, 'CONFIRMED'),
(328, 267, 526, 'UP1', 'wfasdfa', NULL, 22, 'MALE', 120.00, 'CONFIRMED'),
(348, 269, 514, 'LP2', 'Rohit', NULL, 23, 'OTHER', 100.00, 'CONFIRMED'),
(349, 269, 513, 'LP1', 'dshgdsfhs', NULL, 32, 'MALE', 100.00, 'CONFIRMED'),
(350, 269, 516, 'LP4', 'Sandeep', NULL, 34, 'MALE', 100.00, 'CONFIRMED'),
(351, 269, 517, 'LP5', 'rdr', NULL, 42, 'OTHER', 100.00, 'CONFIRMED'),
(352, 269, 522, 'LS1', 'r', NULL, 42, 'OTHER', 60.00, 'CONFIRMED'),
(353, 269, 523, 'LS2', 'rr', NULL, 42, 'OTHER', 60.00, 'CONFIRMED'),
(354, 269, 525, 'LP9', 'r', NULL, 42, 'FEMALE', 100.00, 'CONFIRMED'),
(355, 269, 524, 'LP8', 'r', NULL, 42, 'MALE', 100.00, 'CONFIRMED'),
(356, 270, 526, 'UP1', 'Rohit', NULL, 22, 'MALE', 120.00, 'CONFIRMED'),
(359, 273, 554, 'LP8', 'Dharmdev Sheoran', NULL, 29, 'MALE', 60.00, 'CONFIRMED'),
(360, 273, 539, 'LP5', 'Rohit mechu', NULL, 22, 'MALE', 60.00, 'CONFIRMED'),
(361, 273, 564, 'UP7', 'Sanjay', NULL, 26, 'MALE', 80.00, 'CONFIRMED'),
(362, 274, 536, 'LP2', 'Dev', '', 23, 'MALE', 30.00, 'CONFIRMED'),
(363, 275, 513, 'LP1', 'dffsdfd', NULL, 22, 'MALE', 100.00, 'CONFIRMED'),
(364, 276, 514, 'LP2', 'dfg', NULL, 22, 'MALE', 100.00, 'CONFIRMED'),
(365, 277, 536, 'LP2', 'dfg', NULL, 22, 'FEMALE', 60.00, 'CONFIRMED'),
(366, 278, 536, 'LP2', 'dfg', NULL, 22, 'FEMALE', 25.00, 'CONFIRMED'),
(367, 279, 541, 'LP6', 'Sanjay', '', 22, 'MALE', 30.00, 'CONFIRMED');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `rating` int(11) NOT NULL COMMENT 'Rating from 1 to 5',
  `review_text` text NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 = Active/Approved, 0 = Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `user_id`, `user_name`, `email`, `mobile`, `rating`, `review_text`, `status`, `created_at`) VALUES
(4, 11, 'Sanjay Kumar Sheoran', 'rohitmechujaatji@gmail.com', '8905288939', 3, 'Sanjay k haal hn', 1, '2025-09-24 09:50:18'),
(5, 11, 'Sanjay Kumar Sheoran', 'rohitmechujaatji@gmail.com', '8905288939', 5, 'HI i am rohit choudhary from kuloth kalan ', 1, '2025-09-24 10:18:18'),
(6, 11, 'Sanjay Kumar Sheoran', 'rohitmechujaatji@gmail.com', '8905288939', 4, 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Dolor veniam, voluptates quod amet officiis aliquid similique, autem corrupti illo ducimus incidunt? Minus, porro? Veniam natus repellendus quidem voluptatem, rem facilis!', 1, '2025-09-24 10:29:34');

-- --------------------------------------------------------

--
-- Table structure for table `routes`
--

CREATE TABLE `routes` (
  `route_id` int(11) NOT NULL,
  `bus_id` int(11) NOT NULL,
  `route_name` varchar(255) NOT NULL,
  `starting_point` varchar(255) NOT NULL,
  `ending_point` varchar(255) NOT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `is_popular` tinyint(1) NOT NULL DEFAULT 0,
  `is_chartered` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=Available, 1=Fully Booked for Charter',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `routes`
--

INSERT INTO `routes` (`route_id`, `bus_id`, `route_name`, `starting_point`, `ending_point`, `status`, `is_popular`, `is_chartered`, `created_at`) VALUES
(31, 25, 'Delhi To Pilani', 'Delhi, kasmiri Gate', 'Pilani,Rajasthan', 'Active', 1, 0, '2025-09-23 10:15:45'),
(32, 26, 'Bhiwani to Badhra', 'Bhiwani,hashi Gate', 'Badhra,Haryana', 'Active', 1, 0, '2025-09-23 10:21:44'),
(33, 26, 'Jaipur to Jammu & kasmir', 'Jaipur,Rajasthan', 'Jammu & Kashmir', 'Active', 1, 0, '2025-09-23 11:11:57'),
(34, 26, 'Delhi To Pilani', 'Delhi, kasmiri Gate', 'Pilani,Rajasthan', 'Active', 1, 0, '2025-09-24 12:21:41');

-- --------------------------------------------------------

--
-- Table structure for table `route_schedules`
--

CREATE TABLE `route_schedules` (
  `schedule_id` int(11) NOT NULL,
  `route_id` int(11) NOT NULL,
  `operating_day` varchar(10) NOT NULL COMMENT 'e.g., Mon, Tue, Sun',
  `departure_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `route_schedules`
--

INSERT INTO `route_schedules` (`schedule_id`, `route_id`, `operating_day`, `departure_time`) VALUES
(98, 32, 'Mon', '14:00:00'),
(99, 32, 'Tue', '16:00:00'),
(100, 32, 'Wed', '18:00:00'),
(101, 32, 'Sat', '20:00:00'),
(110, 34, 'Wed', '18:00:00'),
(111, 34, 'Thu', '20:00:00'),
(112, 31, 'Mon', '12:00:00'),
(113, 31, 'Tue', '13:00:00'),
(114, 31, 'Wed', '14:00:00'),
(115, 31, 'Thu', '15:00:00'),
(116, 31, 'Fri', '10:00:00'),
(117, 31, 'Sat', '17:00:00'),
(118, 31, 'Sun', '18:00:00'),
(119, 33, 'Mon', '13:30:00'),
(120, 33, 'Tue', '14:00:00'),
(121, 33, 'Wed', '17:30:00'),
(122, 33, 'Fri', '11:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `route_staff_assignments`
--

CREATE TABLE `route_staff_assignments` (
  `assignment_id` int(11) NOT NULL,
  `route_id` int(11) NOT NULL COMMENT 'Foreign key to the routes table',
  `staff_id` int(11) NOT NULL COMMENT 'Foreign key to the staff table',
  `role` varchar(100) NOT NULL COMMENT 'e.g., Driver, Co-Driver, Conductor, Helper'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `route_staff_assignments`
--

INSERT INTO `route_staff_assignments` (`assignment_id`, `route_id`, `staff_id`, `role`) VALUES
(68, 32, 55, 'Driver'),
(69, 32, 53, 'Co-Driver'),
(70, 32, 57, 'Conductor'),
(71, 32, 59, 'Helper'),
(87, 34, 60, 'Driver'),
(88, 34, 53, 'Co-Driver'),
(89, 34, 57, 'Conductor'),
(90, 34, 56, 'Helper'),
(91, 31, 55, 'Driver'),
(92, 31, 53, 'Co-Driver'),
(93, 31, 57, 'Conductor'),
(94, 31, 58, 'Co-Conductor'),
(95, 31, 59, 'Helper'),
(96, 31, 56, 'Helper'),
(97, 33, 55, 'Driver'),
(98, 33, 58, 'Conductor'),
(99, 33, 56, 'Helper');

-- --------------------------------------------------------

--
-- Table structure for table `route_stops`
--

CREATE TABLE `route_stops` (
  `stop_id` int(11) NOT NULL,
  `route_id` int(11) NOT NULL,
  `stop_name` varchar(255) NOT NULL,
  `stop_order` int(11) NOT NULL,
  `duration_from_start_minutes` int(11) DEFAULT 0,
  `price_seater_lower` decimal(10,2) DEFAULT NULL,
  `price_seater_upper` decimal(10,2) DEFAULT NULL,
  `price_sleeper_lower` decimal(10,2) DEFAULT NULL,
  `price_sleeper_upper` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `route_stops`
--

INSERT INTO `route_stops` (`stop_id`, `route_id`, `stop_name`, `stop_order`, `duration_from_start_minutes`, `price_seater_lower`, `price_seater_upper`, `price_sleeper_lower`, `price_sleeper_upper`) VALUES
(118, 32, 'Juiii,Haryana', 1, 30, 10.00, 20.00, 30.00, 40.00),
(119, 32, 'Badhra,Haryana', 2, 60, 20.00, 40.00, 60.00, 80.00),
(129, 34, 'Pilani,Rajasthan', 1, 60, 23.00, 24.00, 25.00, 26.00),
(130, 31, 'Rohtak, Purana Bus Stand', 1, 60, 20.00, 40.00, 60.00, 80.00),
(131, 31, 'Bhiwani,Haryana', 2, 120, 40.00, 60.00, 80.00, 100.00),
(132, 31, 'Pilani,Rajasthan', 3, 180, 60.00, 80.00, 100.00, 120.00),
(133, 33, 'Bhiwani,Haryana', 1, 280, 400.00, 600.00, 800.00, 900.00),
(134, 33, 'Chandigarh', 2, 400, 600.00, 800.00, 900.00, 1000.00),
(135, 33, 'Noida,Delhi', 3, 600, 1200.00, 1400.00, 1600.00, 1800.00),
(136, 33, 'Ambala,Haryana', 4, 680, 1400.00, 1600.00, 1800.00, 2000.00),
(137, 33, 'Ludhiana,Punjab', 5, 700, 2200.00, 2400.00, 2600.00, 2800.00),
(138, 33, 'Jammu & Kashmir', 6, 900, 2400.00, 2600.00, 2800.00, 3000.00);

-- --------------------------------------------------------

--
-- Table structure for table `seats`
--

CREATE TABLE `seats` (
  `seat_id` int(11) NOT NULL,
  `bus_id` int(11) NOT NULL,
  `seat_code` varchar(50) NOT NULL,
  `deck` enum('LOWER','UPPER') NOT NULL,
  `seat_type` enum('SEATER','SLEEPER','DRIVER','AISLE','TOILET','GANGWAY') NOT NULL DEFAULT 'SEATER',
  `x_coordinate` int(11) NOT NULL,
  `y_coordinate` int(11) NOT NULL,
  `width` int(11) NOT NULL DEFAULT 40,
  `height` int(11) NOT NULL DEFAULT 40,
  `orientation` varchar(20) NOT NULL,
  `base_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `gender_preference` enum('ANY','MALE','FEMALE') NOT NULL DEFAULT 'ANY',
  `is_bookable` tinyint(1) NOT NULL DEFAULT 1,
  `status` enum('AVAILABLE','DAMAGED','BLOCKED') NOT NULL DEFAULT 'AVAILABLE',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `seats`
--

INSERT INTO `seats` (`seat_id`, `bus_id`, `seat_code`, `deck`, `seat_type`, `x_coordinate`, `y_coordinate`, `width`, `height`, `orientation`, `base_price`, `gender_preference`, `is_bookable`, `status`, `created_at`, `updated_at`) VALUES
(513, 25, 'LP1', 'LOWER', 'SLEEPER', 190, 80, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-23 15:26:46', '2025-09-23 15:26:46'),
(514, 25, 'LP2', 'LOWER', 'SLEEPER', 140, 80, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-23 15:26:50', '2025-09-23 15:26:51'),
(515, 25, 'LP3', 'LOWER', 'SLEEPER', 60, 80, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-23 15:26:55', '2025-09-23 15:26:56'),
(516, 25, 'LP4', 'LOWER', 'SLEEPER', 190, 170, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-23 15:26:59', '2025-09-23 15:26:59'),
(517, 25, 'LP5', 'LOWER', 'SLEEPER', 140, 170, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-23 15:27:03', '2025-09-23 15:27:05'),
(518, 25, 'LP6', 'LOWER', 'SLEEPER', 60, 170, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-23 15:27:07', '2025-09-23 15:27:08'),
(519, 25, 'DRIVER', 'LOWER', 'DRIVER', 170, 20, 50, 50, 'VERTICAL_UP', 0.00, 'ANY', 0, 'AVAILABLE', '2025-09-23 15:27:11', '2025-09-23 15:27:14'),
(520, 25, 'LG1', 'LOWER', 'AISLE', 60, 30, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 0, 'AVAILABLE', '2025-09-23 15:27:18', '2025-09-23 15:27:18'),
(521, 25, 'LP7', 'LOWER', 'SLEEPER', 60, 260, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-23 15:27:24', '2025-09-23 15:27:24'),
(522, 25, 'LS1', 'LOWER', 'SEATER', 140, 260, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-23 15:27:27', '2025-09-23 15:27:32'),
(523, 25, 'LS2', 'LOWER', 'SEATER', 190, 260, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-23 15:27:30', '2025-09-23 15:27:31'),
(524, 25, 'LP8', 'LOWER', 'SLEEPER', 140, 310, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-23 15:27:37', '2025-09-23 15:27:38'),
(525, 25, 'LP9', 'LOWER', 'SLEEPER', 190, 310, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-23 15:27:41', '2025-09-23 15:27:42'),
(526, 25, 'UP1', 'UPPER', 'SLEEPER', 70, 70, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-23 15:27:50', '2025-09-23 15:27:55'),
(527, 25, 'UP2', 'UPPER', 'SLEEPER', 120, 70, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-23 15:27:53', '2025-09-23 15:27:57'),
(528, 25, 'UP3', 'UPPER', 'SLEEPER', 200, 70, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-23 15:28:01', '2025-09-23 15:28:04'),
(529, 25, 'UP4', 'UPPER', 'SLEEPER', 70, 160, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-23 15:28:07', '2025-09-23 15:28:09'),
(530, 25, 'UP5', 'UPPER', 'SLEEPER', 120, 160, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-23 15:28:12', '2025-09-23 15:28:13'),
(531, 25, 'UP6', 'UPPER', 'SLEEPER', 200, 160, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-23 15:28:17', '2025-09-23 15:28:17'),
(532, 25, 'UP7', 'UPPER', 'SLEEPER', 70, 250, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-23 15:28:24', '2025-09-23 15:28:25'),
(533, 25, 'UP8', 'UPPER', 'SLEEPER', 120, 250, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-23 15:28:29', '2025-09-23 15:28:30'),
(534, 25, 'UP9', 'UPPER', 'SLEEPER', 200, 250, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-23 15:28:33', '2025-09-23 15:28:35'),
(535, 26, 'LP1', 'LOWER', 'SLEEPER', 70, 80, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 0, 'AVAILABLE', '2025-09-23 15:29:17', '2025-09-24 10:57:08'),
(536, 26, 'LP2', 'LOWER', 'SLEEPER', 120, 80, 40, 80, 'VERTICAL_UP', 0.00, 'FEMALE', 1, 'AVAILABLE', '2025-09-23 15:29:19', '2025-09-24 10:57:03'),
(537, 26, 'LP3', 'LOWER', 'SLEEPER', 70, 550, 170, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-23 15:29:24', '2025-09-24 10:57:48'),
(538, 26, 'LP4', 'LOWER', 'SLEEPER', 70, 170, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-23 15:29:28', '2025-09-23 15:29:30'),
(539, 26, 'LP5', 'LOWER', 'SLEEPER', 120, 170, 40, 80, 'VERTICAL_UP', 0.00, 'MALE', 1, 'AVAILABLE', '2025-09-23 15:29:33', '2025-09-24 10:56:59'),
(540, 26, 'DRIVER', 'LOWER', 'DRIVER', 170, 20, 50, 50, 'VERTICAL_UP', 0.00, 'ANY', 0, 'AVAILABLE', '2025-09-23 15:29:36', '2025-09-23 15:29:38'),
(541, 26, 'LP6', 'LOWER', 'SLEEPER', 200, 80, 40, 170, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-23 15:29:41', '2025-09-24 10:58:29'),
(542, 26, 'LS1', 'LOWER', 'SEATER', 70, 260, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-23 15:29:44', '2025-09-23 15:29:46'),
(543, 26, 'LS2', 'LOWER', 'SEATER', 120, 260, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-23 15:29:48', '2025-09-23 15:29:49'),
(544, 26, 'LS3', 'LOWER', 'SEATER', 200, 260, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-23 15:29:53', '2025-09-24 11:01:10'),
(545, 26, 'UP1', 'UPPER', 'SLEEPER', 70, 60, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-23 15:29:58', '2025-09-23 15:30:00'),
(546, 26, 'UP2', 'UPPER', 'SLEEPER', 120, 60, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-23 15:30:03', '2025-09-23 15:30:04'),
(547, 26, 'UP3', 'UPPER', 'SLEEPER', 200, 60, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-23 15:30:08', '2025-09-23 15:30:11'),
(548, 26, 'UP4', 'UPPER', 'SLEEPER', 70, 150, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-23 15:30:15', '2025-09-23 15:30:17'),
(549, 26, 'UP5', 'UPPER', 'SLEEPER', 120, 150, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-23 15:30:21', '2025-09-23 15:30:25'),
(550, 26, 'US1', 'UPPER', 'SEATER', 200, 150, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-23 15:30:29', '2025-09-23 15:30:29'),
(551, 26, 'US2', 'UPPER', 'SEATER', 200, 200, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-23 15:30:33', '2025-09-23 15:30:34'),
(552, 26, 'LP7', 'LOWER', 'SLEEPER', 70, 310, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-24 10:53:57', '2025-09-24 10:54:00'),
(553, 26, 'LS4', 'LOWER', 'SEATER', 120, 310, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-24 10:54:03', '2025-09-24 10:54:06'),
(554, 26, 'LP8', 'LOWER', 'SLEEPER', 120, 360, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-24 10:54:08', '2025-09-24 10:54:11'),
(555, 26, 'LS5', 'LOWER', 'SEATER', 200, 310, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-24 10:54:13', '2025-09-24 10:54:14'),
(556, 26, 'LP9', 'LOWER', 'SLEEPER', 200, 360, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-24 10:54:17', '2025-09-24 10:54:19'),
(557, 26, 'LS6', 'LOWER', 'SEATER', 70, 400, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-24 10:54:24', '2025-09-24 10:54:26'),
(558, 26, 'LP10', 'LOWER', 'SLEEPER', 200, 450, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-24 10:54:35', '2025-09-24 10:54:37'),
(560, 26, 'LP11', 'LOWER', 'SLEEPER', 120, 450, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-24 10:54:45', '2025-09-24 10:54:48'),
(562, 26, 'UP6', 'UPPER', 'SLEEPER', 200, 250, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-24 10:55:01', '2025-09-24 10:55:02'),
(563, 26, 'US3', 'UPPER', 'SEATER', 120, 240, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-24 10:55:04', '2025-09-24 10:55:06'),
(564, 26, 'UP7', 'UPPER', 'SLEEPER', 70, 240, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-24 10:55:09', '2025-09-24 10:55:11'),
(565, 26, 'US4', 'UPPER', 'SEATER', 120, 290, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-24 10:55:14', '2025-09-24 10:55:15'),
(566, 26, 'UP8', 'UPPER', 'SLEEPER', 70, 330, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-24 10:55:20', '2025-09-24 10:55:22'),
(567, 26, 'US5', 'UPPER', 'SEATER', 120, 340, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-24 10:55:25', '2025-09-24 10:55:27'),
(568, 26, 'UP9', 'UPPER', 'SLEEPER', 200, 340, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-24 10:55:32', '2025-09-24 10:55:36'),
(569, 26, 'UP10', 'UPPER', 'SLEEPER', 70, 420, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-24 10:55:40', '2025-09-24 10:55:42'),
(570, 26, 'UP11', 'UPPER', 'SLEEPER', 120, 390, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-24 10:55:49', '2025-09-24 10:55:51'),
(571, 26, 'US6', 'UPPER', 'SEATER', 200, 430, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-24 10:56:06', '2025-09-24 10:56:06'),
(572, 26, 'UP12', 'UPPER', 'SLEEPER', 120, 480, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-24 10:56:20', '2025-09-24 10:56:22'),
(573, 26, 'LP12', 'LOWER', 'SLEEPER', 70, 450, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-24 10:58:52', '2025-09-24 10:58:55'),
(574, 26, 'UP13', 'UPPER', 'SLEEPER', 200, 480, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-26 13:26:09', '2025-09-26 13:26:10'),
(575, 26, 'UP14', 'UPPER', 'SLEEPER', 70, 510, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-26 13:26:14', '2025-09-26 13:26:15'),
(576, 26, 'UP15', 'UPPER', 'SLEEPER', 200, 570, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-26 13:26:38', '2025-09-26 13:26:38'),
(577, 26, 'US7', 'UPPER', 'SEATER', 120, 570, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-26 13:26:41', '2025-09-26 13:26:44'),
(578, 26, 'US8', 'UPPER', 'SEATER', 70, 600, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-26 13:26:49', '2025-09-26 13:26:52'),
(579, 25, 'LP10', 'LOWER', 'SLEEPER', 60, 350, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-26 14:56:03', '2025-09-26 14:56:04'),
(580, 25, 'LP11', 'LOWER', 'SLEEPER', 60, 440, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-26 14:56:07', '2025-09-26 14:56:08');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('company_address', 'Loharu Haryana'),
('company_name', 'Bpl Bus Ticket'),
('email_optional_1', ''),
('email_optional_2', ''),
('email_primary', 'rohit@gmail.com'),
('facebook_url', ''),
('instagram_url', ''),
('mobile_optional_1', ''),
('mobile_optional_2', ''),
('mobile_primary', '35434543534'),
('twitter_url', ''),
('whatsapp_optional_1', ''),
('whatsapp_primary', '235423423'),
('youtube_url', '');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `staff_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `designation` varchar(100) NOT NULL,
  `driving_licence_no` varchar(100) DEFAULT NULL,
  `aadhar_no` varchar(20) DEFAULT NULL,
  `profile_image_path` varchar(255) DEFAULT NULL,
  `remark` text DEFAULT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`staff_id`, `name`, `mobile`, `designation`, `driving_licence_no`, `aadhar_no`, `profile_image_path`, `remark`, `status`, `created_at`) VALUES
(53, 'Rohit Choudhary', '8905288939', 'Driver', '123456789012', '12211212212212', 'staff_1758620597_a414f347.jpg', 'my self Rohit Choudhary', 'Active', '2025-09-23 09:43:17'),
(54, 'Sanjay Sheoran', '9728833428', 'Driver', '1231231223323231', '21323213123212372', 'staff_1758620659_ac2689d7.jpg', '', 'Active', '2025-09-23 09:44:19'),
(55, 'Dev Sheoran', '8930000210', 'Driver', '123123122332312', '213232131232121221', '', '212', 'Active', '2025-09-23 09:45:05'),
(56, 'Naveen Sheoran', '9832435223', 'Helper', NULL, '213232131232123332', '', '', 'Active', '2025-09-23 09:45:47'),
(57, 'Akash Sheoran', '9876543213', 'Conductor', NULL, '2132321312321231', 'staff_1758620793_c3d462d1.jpg', '', 'Active', '2025-09-23 09:46:33'),
(58, 'Sandeep Sheoran', '9876543215', 'Conductor', NULL, '2132321312321235', 'staff_1758620846_25d4b561.jpg', '', 'Active', '2025-09-23 09:47:26'),
(59, 'Dinesh Sharma', '9876543243', 'Helper', NULL, '2132321312321212', '', '', 'Active', '2025-09-23 09:48:22'),
(60, 'Rohit', '8975288939', 'Driver', '2626626236636363', '8905288939', 'staff_1758691287_64494cb0.png', 'Hdh', 'Active', '2025-09-24 05:21:27');

-- --------------------------------------------------------

--
-- Table structure for table `ticket_access_tokens`
--

CREATE TABLE `ticket_access_tokens` (
  `token_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ticket_access_tokens`
--

INSERT INTO `ticket_access_tokens` (`token_id`, `booking_id`, `token`, `created_at`) VALUES
(108, 250, '1d50151ccd1eba000942e12ff8b20145', '2025-09-23 10:25:38'),
(109, 251, '2f7e549b8beab8f71c74c121ffad19ef', '2025-09-23 10:29:43'),
(110, 252, '6389500ea41ce710631414b290ea1b1b0b9e8608', '2025-09-23 10:31:32'),
(111, 253, 'efc8da8173eae9d9f3a800de04f5316a2e1a2892', '2025-09-23 10:36:03'),
(112, 254, '138f090553274ebd6ca48b3972d77ddd098992fb', '2025-09-23 10:41:26'),
(113, 255, 'ca8309a1c4c90f869433d8f44b30cb890d03ceb3', '2025-09-23 11:16:49'),
(114, 256, 'c6fd1898b353b111fb0ac6bb7fe2bb0b0caa572a', '2025-09-23 11:30:46'),
(115, 257, 'b769f129565065d46202dddeb1a222e006ab6778', '2025-09-24 04:07:10'),
(116, 258, '3c9887d8e3a382ca9a63b7173618960822d24b51', '2025-09-24 06:35:09'),
(117, 259, '74145e94ea527220107821f89df54c812db3170f', '2025-09-24 06:52:25'),
(118, 260, 'f3f8b84d64b0b4040be27d2a07553d369a9818e6', '2025-09-24 07:39:35'),
(119, 261, 'fe476c077063b61672f355ac11befefb06a53ccf', '2025-09-24 09:53:39'),
(120, 262, 'ef98551d4fd65b86f90a409c76efb358a6056550', '2025-09-24 11:23:19'),
(121, 263, 'cf38b354e384daa02a28b17d28576f9b355fc41d', '2025-09-24 11:51:00'),
(122, 264, 'ff2065ba7dc8634dae1390427c10c6a735775188', '2025-09-24 12:27:45'),
(123, 265, '558dbb66cf2b548e3c59abd65435dcddaf90bb34', '2025-09-24 12:29:27'),
(124, 266, '134bec321fa3a07d7e9fe201afb35b2f79fb3fca', '2025-09-25 05:09:41'),
(125, 267, 'af0cafa49f3bf11e6b6bb1749c7fa6c828f6cf55', '2025-09-25 05:52:28'),
(126, 268, 'bf4da9c106273e7736fc75fa2acdb347e8464143', '2025-09-25 11:20:30'),
(127, 269, 'b55f4de5b97a04a0af8a6372dd8c4e5d528cbd88', '2025-09-25 11:40:42'),
(128, 270, '5acc44adb91bc33430fbc2c9c3d57dbda9b3f533', '2025-09-26 07:58:05'),
(129, 271, '04ff372c1c36ad34c9c0bab742305384e8c15abd', '2025-09-26 09:28:53'),
(130, 272, '7d0d598f5b5c620cf656f5d22eeeddcd62042868', '2025-09-26 09:47:30'),
(131, 273, '75147c4eccea4292770393af220302b4cf160f63', '2025-09-26 12:37:40'),
(132, 274, '3aacfe2c81ba6b7da3dbaf9ec006e6f1374b715b', '2025-09-27 08:16:35'),
(133, 275, '4eb2115ef24e19d47f14c4f86cef754add80e0ba', '2025-09-29 04:27:09'),
(134, 276, '5645b6e7d87ac619c02f8138437ac7b20b428e78', '2025-09-29 06:13:04'),
(135, 277, '26e3f90ae4dd55e3dc6b97103044e4babe7ce2df', '2025-09-29 06:40:56'),
(136, 278, '1aa412cdc087be847d1764ec009189d7573dfab2', '2025-09-29 06:50:52'),
(137, 279, '29324a2bcce957d1ede20123e34de32e456b05a6', '2025-09-30 06:50:16');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL COMMENT 'The user/customer who paid, if logged in',
  `employee_id` int(11) DEFAULT NULL COMMENT 'The employee who processed the booking',
  `payment_gateway` varchar(50) NOT NULL DEFAULT 'Razorpay',
  `gateway_payment_id` varchar(255) NOT NULL COMMENT 'e.g., razorpay_payment_id',
  `gateway_order_id` varchar(255) NOT NULL COMMENT 'e.g., razorpay_order_id',
  `gateway_signature` varchar(255) DEFAULT NULL COMMENT 'e.g., razorpay_signature for verification',
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'INR',
  `payment_status` enum('CREATED','AUTHORIZED','CAPTURED','REFUNDED','FAILED') NOT NULL,
  `method` varchar(50) DEFAULT NULL COMMENT 'e.g., card, netbanking, upi',
  `error_code` varchar(255) DEFAULT NULL,
  `error_description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `booking_id`, `user_id`, `employee_id`, `payment_gateway`, `gateway_payment_id`, `gateway_order_id`, `gateway_signature`, `amount`, `currency`, `payment_status`, `method`, `error_code`, `error_description`, `created_at`, `updated_at`) VALUES
(67, 256, 9, NULL, 'Razorpay', 'pay_RL1kKVb1QpqnX7', 'order_RL1kB4gQhAUQh9', 'f367181c4a438d5c7d4b66688ab87ecb4b2e35c2cc4e37c6bcc75b5f9f3f30ee', 2600.00, 'INR', 'CAPTURED', 'online', NULL, NULL, '2025-09-23 11:30:46', '2025-09-23 11:30:46'),
(69, 258, 11, NULL, 'Razorpay', 'pay_RLLFAnCyt0BWMt', 'order_RLLF0i17UaHLPt', 'af43f71acaf1ece531fce163705c26d426286cc7fe10b478b8b58bb82e8ee358', 60.00, 'INR', 'CAPTURED', 'online', NULL, NULL, '2025-09-24 06:35:09', '2025-09-24 06:35:09'),
(70, 259, 11, NULL, 'Razorpay', 'pay_RLLXP02iM8vHxb', 'order_RLLXGonul4auU4', 'dba4f201cec5bae2666c1f215aed9e3b3e8b934b7ee9eab066b1ac0b404170b1', 2800.00, 'INR', 'CAPTURED', 'online', NULL, NULL, '2025-09-24 06:52:25', '2025-09-24 06:52:25'),
(71, 260, 11, NULL, 'Razorpay', 'pay_RLMLF0kTgb9qNa', 'order_RLML83x1oCp7OE', 'a5ee445cf1565cb9551de09e01fbb0f0b9501523d52ca5138fbd6beffb9ffa2d', 14400.00, 'INR', 'CAPTURED', 'online', NULL, NULL, '2025-09-24 07:39:35', '2025-09-24 07:39:35'),
(72, 261, 11, NULL, 'Razorpay', 'pay_RLOcU5s0CiXDDL', 'order_RLOcN2pqSH5YvQ', '99495127e29f0113139db28c84f78f932c872a95126fb6a55c190d5649fd3c5b', 3200.00, 'INR', 'CAPTURED', 'online', NULL, NULL, '2025-09-24 09:53:39', '2025-09-24 09:53:39'),
(73, 262, 11, NULL, 'Razorpay', 'pay_RLQ9a8m3vj8tx3', 'order_RLQ9PYl1dMS710', 'b361b432ecbe87e3e9a2e5b038b9149a464c394ac214495abd748c6f33c82583', 320.00, 'INR', 'CAPTURED', 'online', NULL, NULL, '2025-09-24 11:23:19', '2025-09-24 11:23:19'),
(74, 263, 11, NULL, 'Razorpay', 'pay_RLQcozFGzAwdt7', 'order_RLQciRpjNtarHC', '038604af5b0267fdb24db8f9421b54dd768039846f17d7d6f33c61f656dc9e97', 80.00, 'INR', 'CAPTURED', 'online', NULL, NULL, '2025-09-24 11:51:00', '2025-09-24 11:51:00'),
(75, 264, 11, NULL, 'Razorpay', 'pay_RLRFdD6CvhHGN7', 'order_RLRFSwt9wr99L9', 'dcca81380d240deeb30162cb6bb01512e5b0cb7f9c51ebe653fd332fea12ca80', 25.00, 'INR', 'CAPTURED', 'online', NULL, NULL, '2025-09-24 12:27:45', '2025-09-24 12:27:45'),
(76, 265, 11, NULL, 'Razorpay', 'pay_RLRHQdLUhgyOtM', 'order_RLRHJuaSeJ1cCC', '86f8b03e7c8a39dc1701954a4068f80dc31da7eb5874ee560c061101ee2f68d2', 25.00, 'INR', 'CAPTURED', 'online', NULL, NULL, '2025-09-24 12:29:27', '2025-09-24 12:29:27'),
(77, 266, 9, NULL, 'Razorpay', 'pay_RLiJzwa6UgynrT', 'order_RLhwqLSYNWsHje', 'b63d3cfbb46c43c23a86570a792cf1d8990e9de59e40c295224a106b60c07a77', 5200.00, 'INR', 'CAPTURED', 'online', NULL, NULL, '2025-09-25 05:09:41', '2025-09-25 05:09:41'),
(78, 267, 9, NULL, 'Razorpay', 'pay_RLj2tC8LZg6CIP', 'order_RLj2jlyNh3BcUy', '6491380d0cf6b8c36b5ebcea953dc6404b400edf3c8944243b4be4a4598dfb80', 120.00, 'INR', 'CAPTURED', 'online', NULL, NULL, '2025-09-25 05:52:28', '2025-09-25 05:52:28'),
(80, 269, 11, NULL, 'Razorpay', 'pay_RLoz3M1lkvoYt6', 'order_RLoyuk2qfn6gKD', '0c6a42e453ae2263c44b893eacc9243eb1a09b87f25b5cd938ce7e2c5ef6dbb4', 720.00, 'INR', 'CAPTURED', 'online', NULL, NULL, '2025-09-25 11:40:42', '2025-09-25 11:40:42'),
(81, 270, 9, NULL, 'Razorpay', 'pay_RM9j0sXlxwe8YC', 'order_RM9icdYINHhBri', 'a0f0e2250596bb079305a2d5cb06b157e129f7a2e1ff5d18545c8246c8874a52', 120.00, 'INR', 'CAPTURED', 'online', NULL, NULL, '2025-09-26 07:58:05', '2025-09-26 07:58:05'),
(82, 273, 12, NULL, 'Razorpay', 'pay_RMEULygTLsMhtS', 'order_RMEUDSuLOHFwln', '7723beaabb2102213c83bb9dc9a932c4496542f8c19637f88900994256965fe1', 200.00, 'INR', 'CAPTURED', 'online', NULL, NULL, '2025-09-26 12:37:40', '2025-09-26 12:37:40'),
(83, 274, NULL, NULL, 'Razorpay', 'pay_RMYZfltO0pLLQv', 'order_RMYZVfgc11e97K', '5a31231df7d3741d4375bc4edc3086ae61e72aef61f8797378268e91475da9be', 30.00, 'INR', 'CAPTURED', 'online', NULL, NULL, '2025-09-27 08:16:35', '2025-09-27 08:16:35'),
(84, 275, 9, NULL, 'Razorpay', 'pay_RNHjZGtmbCVSe3', 'order_RNHjPtTLRIPfuE', 'dd0f18f870ee4e625da1a96b2d6adbab120deb507ef39deb63e8728a8c66e428', 100.00, 'INR', 'CAPTURED', 'online', NULL, NULL, '2025-09-29 04:27:09', '2025-09-29 04:27:09'),
(85, 276, 13, NULL, 'Razorpay', 'pay_RNJXRKeURASSuG', 'order_RNJXB6IIVfAwl1', 'c5566689dce6d73fad4772a64fe1e72a35d795cbdb4d4d1b0b579bc2b2feca57', 100.00, 'INR', 'CAPTURED', 'online', NULL, NULL, '2025-09-29 06:13:04', '2025-09-29 06:13:04'),
(86, 277, 11, NULL, 'Razorpay', 'pay_RNK0sYqXw7DYFU', 'order_RNK0levj8zkjC4', '944cef339a8092dfde8f5d3ec2fbfce2ab56d08ae5e986bac2a196dc75644b6e', 60.00, 'INR', 'CAPTURED', 'online', NULL, NULL, '2025-09-29 06:40:56', '2025-09-29 06:40:56'),
(87, 278, 11, NULL, 'Razorpay', 'pay_RNKBNjAXUY6QYV', 'order_RNKBGi6s09yatO', '39bc71dab50ffa6e044b432e25eac1eef0ae475bd8cd2749fc0cfdf7687bc199', 25.00, 'INR', 'CAPTURED', 'online', NULL, NULL, '2025-09-29 06:50:52', '2025-09-29 06:50:52');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `mobile_no` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `ip_address` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=Active, 2=Deactivated',
  `otp` varchar(255) DEFAULT NULL,
  `otp_expires_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `mobile_no`, `email`, `ip_address`, `status`, `otp`, `otp_expires_at`, `created_at`) VALUES
(3, 'Sanjay Kumar Sheoran', '$2y$10$z7lBSp5NypMVF1S05ZvNeui70bCZceCut.xYsUwAGecNIoqck5DO6', '9728833428', 'rohitmechujaatjqi@gmail.com', '::1', 1, NULL, NULL, '2025-09-11 11:49:54'),
(9, 'Sanjay Kumasr Sheoran', '$2y$10$XZDmMU4pgCwyVyTJw9OGbOD9aiH9SDDBlKJF0E2LpIh2If9YbrZ7G', '1234567890', 'sjsheoran111@gmail.com', '::1', 1, NULL, NULL, '2025-09-16 09:48:25'),
(10, 'DEV', '$2y$10$GTJTyoS7Ka8l7ajrdGHEv.X3nZ.knWyVJBW2XCQiXyGTB7GXQt1qi', '8930000210', 'dharmdev@jsnj.in', '192.168.1.49', 1, NULL, NULL, '2025-09-22 15:12:21'),
(11, 'Sanjay Kumar Sheoran', '$2y$10$Ybbwz76Am5hUSjsGIzc.ZOKRN05naaett9ycAjjrTyrBD9U3wXlK6', '8905288939', 'rohitmechujaatji@gmail.com', '::1', 1, NULL, NULL, '2025-09-24 12:04:44'),
(12, 'Dharmdev Sheoran', '$2y$10$eMGWt6fhBsLB8swl6Qw8Y.LnOXHz0EdIHPjabfwDgyLx26B2fNtku', '8930000210', 'devdharm9@gmail.com', '192.168.1.52', 1, NULL, NULL, '2025-09-26 18:07:16'),
(13, 'Sanjay Kumar Sheoran', '$2y$10$iLN2.FsGzSV24kIftkWmFOyN9WVztylcAsBhE0fR.f3ZRFJHcNdrK', '9728833428', '32@gmail.com', '::1', 1, NULL, NULL, '2025-09-29 11:42:32');

-- --------------------------------------------------------

--
-- Table structure for table `users_login_token`
--

CREATE TABLE `users_login_token` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `status` varchar(1) NOT NULL DEFAULT '1' COMMENT '1=active, 2=logout',
  `date_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users_login_token`
--

INSERT INTO `users_login_token` (`id`, `user_id`, `token`, `status`, `date_time`, `ip_address`) VALUES
(30, 11, '8e8e15612c19c5561ed0b8ea80b64560b13c46c9bf7bbe0a34d715574a772c00', '2', '2025-09-24 06:36:12', '::1'),
(31, 11, 'b7a20e1ebd08d2dd3e5a69f2c2034b9e282b73fe1939c3913fb69c8f6d8ed60a', '2', '2025-09-24 07:44:40', '::1'),
(32, 11, 'f9ed6401a8c631dd0835b878951286dee125ff766bcccecb816ae0d897d4a4de', '2', '2025-09-24 10:06:05', '::1'),
(33, 11, '36973cce9a448ad3b4315a266317f6504ad2593c7259f18477dcdb2697b39643', '2', '2025-09-24 10:06:22', '192.168.1.50'),
(34, 11, 'a4d8d81ebccbf60a4ea8536a46cafe50c82c2a2fb12b187da2bd8718e8a28a63', '2', '2025-09-25 07:36:11', '::1'),
(35, 10, '74ca251732bbdbed84c5bf1c0a4efed19e9bb41377d45d9df519b238ad50df3e', '2', '2025-09-26 12:40:41', '192.168.1.52'),
(36, 12, 'ee9c7db3d95ee0ab5f0ad2720faa62d8f49ed7708c869e69ca27b296693215be', '1', '2025-09-26 12:41:37', '192.168.1.52'),
(37, 12, '0602a7459c8b9a8125c7acee5c8f5f10c7242170c398095b9fa93e788df5abaf', '1', '2025-09-27 05:49:10', '192.168.1.50'),
(38, 11, 'a4be2f2fc89b059cf92b9dbd2af573c98507025c5cc86badd22697b630ce6359', '2', '2025-09-27 05:54:18', '::1'),
(39, 11, '14a6dadc6cee7f93921eb5b065d4a7384e47e8f8fb5520283c107b3f83b8c50c', '2', '2025-09-29 06:36:32', '::1'),
(40, 11, '86636f0af94bb65a32e5580480f96a91c595f9c0462494f37c621d5628b2054b', '2', '2025-09-29 07:40:09', '::1'),
(41, 11, '3821afa984227ba00425901e74c77fb62b7531d6bda9a1b25325da6d212e4d85', '1', '2025-09-29 07:41:23', '::1');

-- --------------------------------------------------------

--
-- Table structure for table `user_login_token`
--

CREATE TABLE `user_login_token` (
  `id` int(11) NOT NULL,
  `user_id` varchar(110) NOT NULL,
  `token` varchar(110) NOT NULL,
  `status` varchar(2) DEFAULT '1' COMMENT '1 =active, 2 logout',
  `date_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(110) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email` (`email`),
  ADD KEY `id` (`id`),
  ADD KEY `mobile` (`mobile`),
  ADD KEY `fk_admin_to_staff` (`linked_staff_id`);

--
-- Indexes for table `admin_activity_log`
--
ALTER TABLE `admin_activity_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD UNIQUE KEY `ticket_no` (`ticket_no`),
  ADD KEY `route_id` (`route_id`),
  ADD KEY `bus_id` (`bus_id`);

--
-- Indexes for table `buses`
--
ALTER TABLE `buses`
  ADD PRIMARY KEY (`bus_id`),
  ADD UNIQUE KEY `registration_number` (`registration_number`),
  ADD KEY `idx_bus_status` (`status`);

--
-- Indexes for table `bus_categories`
--
ALTER TABLE `bus_categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `bus_category_map`
--
ALTER TABLE `bus_category_map`
  ADD PRIMARY KEY (`map_id`),
  ADD KEY `bus_id` (`bus_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `bus_images`
--
ALTER TABLE `bus_images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `bus_id` (`bus_id`);

--
-- Indexes for table `cancellations`
--
ALTER TABLE `cancellations`
  ADD PRIMARY KEY (`cancellation_id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `passenger_id` (`passenger_id`);

--
-- Indexes for table `cash_collections_log`
--
ALTER TABLE `cash_collections_log`
  ADD PRIMARY KEY (`collection_id`),
  ADD UNIQUE KEY `booking_id` (`booking_id`),
  ADD KEY `collected_by_admin_id` (`collected_by_admin_id`),
  ADD KEY `collected_from_employee_id` (`collected_from_employee_id`);

--
-- Indexes for table `charter_bookings`
--
ALTER TABLE `charter_bookings`
  ADD PRIMARY KEY (`charter_id`),
  ADD UNIQUE KEY `route_date_unique` (`route_id`,`travel_date`);

--
-- Indexes for table `charter_inquiries`
--
ALTER TABLE `charter_inquiries`
  ADD PRIMARY KEY (`inquiry_id`);

--
-- Indexes for table `deleted_bookings`
--
ALTER TABLE `deleted_bookings`
  ADD PRIMARY KEY (`booking_id`);

--
-- Indexes for table `deleted_buses`
--
ALTER TABLE `deleted_buses`
  ADD PRIMARY KEY (`bus_id`);

--
-- Indexes for table `deleted_passengers`
--
ALTER TABLE `deleted_passengers`
  ADD PRIMARY KEY (`passenger_id`);

--
-- Indexes for table `deleted_routes`
--
ALTER TABLE `deleted_routes`
  ADD PRIMARY KEY (`route_id`);

--
-- Indexes for table `passengers`
--
ALTER TABLE `passengers`
  ADD PRIMARY KEY (`passenger_id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `routes`
--
ALTER TABLE `routes`
  ADD PRIMARY KEY (`route_id`),
  ADD KEY `bus_id` (`bus_id`);

--
-- Indexes for table `route_schedules`
--
ALTER TABLE `route_schedules`
  ADD PRIMARY KEY (`schedule_id`),
  ADD KEY `route_id` (`route_id`);

--
-- Indexes for table `route_staff_assignments`
--
ALTER TABLE `route_staff_assignments`
  ADD PRIMARY KEY (`assignment_id`);

--
-- Indexes for table `route_stops`
--
ALTER TABLE `route_stops`
  ADD PRIMARY KEY (`stop_id`),
  ADD KEY `route_id` (`route_id`);

--
-- Indexes for table `seats`
--
ALTER TABLE `seats`
  ADD PRIMARY KEY (`seat_id`),
  ADD UNIQUE KEY `bus_id` (`bus_id`,`seat_code`),
  ADD KEY `idx_seats_bus_id` (`bus_id`),
  ADD KEY `idx_seats_deck` (`deck`),
  ADD KEY `idx_seats_is_bookable` (`is_bookable`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`staff_id`),
  ADD UNIQUE KEY `mobile` (`mobile`),
  ADD UNIQUE KEY `driving_licence_no` (`driving_licence_no`),
  ADD UNIQUE KEY `aadhar_no` (`aadhar_no`);

--
-- Indexes for table `ticket_access_tokens`
--
ALTER TABLE `ticket_access_tokens`
  ADD PRIMARY KEY (`token_id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `gateway_payment_id` (`gateway_payment_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users_login_token`
--
ALTER TABLE `users_login_token`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_login_token`
--
ALTER TABLE `user_login_token`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `admin_activity_log`
--
ALTER TABLE `admin_activity_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=329;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=280;

--
-- AUTO_INCREMENT for table `buses`
--
ALTER TABLE `buses`
  MODIFY `bus_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `bus_categories`
--
ALTER TABLE `bus_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `bus_category_map`
--
ALTER TABLE `bus_category_map`
  MODIFY `map_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=110;

--
-- AUTO_INCREMENT for table `bus_images`
--
ALTER TABLE `bus_images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `cancellations`
--
ALTER TABLE `cancellations`
  MODIFY `cancellation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `cash_collections_log`
--
ALTER TABLE `cash_collections_log`
  MODIFY `collection_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=125;

--
-- AUTO_INCREMENT for table `charter_bookings`
--
ALTER TABLE `charter_bookings`
  MODIFY `charter_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `charter_inquiries`
--
ALTER TABLE `charter_inquiries`
  MODIFY `inquiry_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `passengers`
--
ALTER TABLE `passengers`
  MODIFY `passenger_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=368;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `routes`
--
ALTER TABLE `routes`
  MODIFY `route_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `route_schedules`
--
ALTER TABLE `route_schedules`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=123;

--
-- AUTO_INCREMENT for table `route_staff_assignments`
--
ALTER TABLE `route_staff_assignments`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100;

--
-- AUTO_INCREMENT for table `route_stops`
--
ALTER TABLE `route_stops`
  MODIFY `stop_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=139;

--
-- AUTO_INCREMENT for table `seats`
--
ALTER TABLE `seats`
  MODIFY `seat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=581;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `staff_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `ticket_access_tokens`
--
ALTER TABLE `ticket_access_tokens`
  MODIFY `token_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=138;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users_login_token`
--
ALTER TABLE `users_login_token`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `user_login_token`
--
ALTER TABLE `user_login_token`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bus_category_map`
--
ALTER TABLE `bus_category_map`
  ADD CONSTRAINT `bus_category_map_ibfk_1` FOREIGN KEY (`bus_id`) REFERENCES `buses` (`bus_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bus_category_map_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `bus_categories` (`category_id`) ON DELETE CASCADE;

--
-- Constraints for table `bus_images`
--
ALTER TABLE `bus_images`
  ADD CONSTRAINT `fk_bus_images_to_bus` FOREIGN KEY (`bus_id`) REFERENCES `buses` (`bus_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `charter_bookings`
--
ALTER TABLE `charter_bookings`
  ADD CONSTRAINT `fk_charter_to_route` FOREIGN KEY (`route_id`) REFERENCES `routes` (`route_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `passengers`
--
ALTER TABLE `passengers`
  ADD CONSTRAINT `passengers_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `routes`
--
ALTER TABLE `routes`
  ADD CONSTRAINT `routes_ibfk_1` FOREIGN KEY (`bus_id`) REFERENCES `buses` (`bus_id`) ON DELETE CASCADE;

--
-- Constraints for table `route_schedules`
--
ALTER TABLE `route_schedules`
  ADD CONSTRAINT `route_schedules_ibfk_1` FOREIGN KEY (`route_id`) REFERENCES `routes` (`route_id`) ON DELETE CASCADE;

--
-- Constraints for table `route_stops`
--
ALTER TABLE `route_stops`
  ADD CONSTRAINT `route_stops_ibfk_1` FOREIGN KEY (`route_id`) REFERENCES `routes` (`route_id`) ON DELETE CASCADE;

--
-- Constraints for table `seats`
--
ALTER TABLE `seats`
  ADD CONSTRAINT `seats_ibfk_1` FOREIGN KEY (`bus_id`) REFERENCES `buses` (`bus_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `fk_transaction_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE;

--
-- Constraints for table `users_login_token`
--
ALTER TABLE `users_login_token`
  ADD CONSTRAINT `users_login_token_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
