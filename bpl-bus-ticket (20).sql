-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 22, 2025 at 06:40 AM
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
(1, 'main_admin', '{\"all_access\": true}', '2025-09-22 09:51:23', '::1', '0c2cdac68d048c49de43ecea8d42c784e6aa964a10b5faf3b34d478188b045ec', 'Dev Sheoran', '8930000210', 'admin@gmail.com', '$2y$10$1izEIiYcwHnHriC5zYX.KOOr2az22mXg/3FnbRwwS33b.aPSr33Z2', '123456', '1', NULL, '::1', '2025-07-23 13:36:33'),
(7, 'employee', '{\"can_book_tickets\":true,\"can_view_bookings\":true,\"can_delete_bookings\":true,\"can_manage_cancellations\":true,\"can_manage_routes\":true,\"can_delete_routes\":true}', '2025-09-17 10:02:19', '::1', '5b31b77196f4405827281fc2365abdc90627042d448c32e2611a9169a3cba35f', 'Akash Sheoran', '1122334455', 'rohit@gmail.com', '$2y$10$OJRy02iiEeqRCNqxVnUEU.9hO6VnO0FItFlSdz.9niaEDhjDJE.o.', 'bcrypt', '1', 46, NULL, '2025-09-13 07:27:37'),
(8, 'employee', '[]', NULL, NULL, NULL, 'Dev Sheoran', '2222222222', 'test@gmail.com', '$2y$10$GHsIYHdILS1TM4iPgcEUg.bazrcOo0XlJKMNg/wfZ0shS6KGOKBua', 'bcrypt', '1', 44, NULL, '2025-09-13 07:28:19'),
(10, 'employee', '[]', NULL, NULL, NULL, 'Rohit Choudhary', '8905288931', 'jetcargopackersandmovers@gmail.com', '$2y$10$RJc5chOB1I1JmGNqqRys6.9IsCAdpXu4rFPvV2fEVBcsCaJ1lYeua', 'bcrypt', '1', 43, NULL, '2025-09-13 07:29:54'),
(15, 'employee', '{\"can_book_tickets\":true,\"can_view_bookings\":true,\"can_delete_bookings\":true,\"can_manage_routes\":true,\"can_delete_routes\":true}', NULL, NULL, NULL, 'Naveen Sheoran', '1234567890', 'teest@gmail.com', '$2y$10$Cb1UX3z0jUNy.Yt8ldvMqOkWM3PNnRksn6PsiPJCfXku1o5EIX/NO', 'bcrypt', '1', 45, NULL, '2025-09-15 07:06:34');

-- --------------------------------------------------------

--
-- Table structure for table `admin_activity_log`
--

CREATE TABLE `admin_activity_log` (
  `log_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `admin_name` varchar(255) NOT NULL,
  `activity_type` enum('login','logout') NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `log_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_activity_log`
--

INSERT INTO `admin_activity_log` (`log_id`, `admin_id`, `admin_name`, `activity_type`, `ip_address`, `log_time`) VALUES
(1, 1, 'dev', 'logout', '::1', '2025-09-05 12:10:16'),
(2, 1, 'dev', 'login', '::1', '2025-09-05 12:10:25'),
(3, 1, 'dev', 'login', '::1', '2025-09-05 12:10:29'),
(4, 1, 'dev', 'login', '::1', '2025-09-05 12:10:35'),
(5, 1, 'dev', 'logout', '::1', '2025-09-05 12:14:08'),
(6, 1, 'dev', 'login', '::1', '2025-09-05 12:14:16'),
(7, 1, 'dev', 'logout', '::1', '2025-09-05 12:14:24'),
(8, 3, 'Rohit Mechu', 'login', '::1', '2025-09-05 12:14:38'),
(9, 3, 'Rohit Mechu', 'logout', '::1', '2025-09-05 12:15:14'),
(10, 1, 'dev', 'login', '::1', '2025-09-05 12:15:27'),
(11, 1, 'dev', 'login', '::1', '2025-09-05 12:30:31'),
(12, 1, 'dev', 'login', '::1', '2025-09-05 12:30:42'),
(13, 1, 'dev', 'login', '::1', '2025-09-06 04:12:07'),
(14, 1, 'dev', 'login', '::1', '2025-09-06 04:13:44'),
(15, 1, 'dev', 'login', '::1', '2025-09-06 04:14:01'),
(16, 1, 'dev', 'login', '::1', '2025-09-06 04:14:27'),
(17, 1, 'dev', 'login', '::1', '2025-09-06 04:20:01'),
(18, 1, 'dev', 'login', '::1', '2025-09-06 04:20:13'),
(19, 1, 'dev', 'login', '::1', '2025-09-06 04:21:13'),
(20, 1, 'dev', 'login', '::1', '2025-09-06 04:21:25'),
(21, 1, 'dev', 'logout', '::1', '2025-09-06 04:21:37'),
(22, 1, 'dev', 'login', '::1', '2025-09-06 04:21:55'),
(23, 1, 'dev', 'logout', '::1', '2025-09-06 04:22:33'),
(24, 1, 'dev', 'login', '::1', '2025-09-06 04:22:40'),
(25, 1, 'dev', 'login', '::1', '2025-09-06 04:27:58'),
(26, 1, 'dev', 'logout', '::1', '2025-09-06 04:31:59'),
(27, 3, 'Rohit Mechu', 'login', '::1', '2025-09-06 04:32:18'),
(28, 3, 'Rohit Mechu', 'logout', '::1', '2025-09-06 04:32:47'),
(29, 1, 'dev', 'login', '::1', '2025-09-06 04:32:59'),
(30, 1, 'dev', 'logout', '::1', '2025-09-06 04:34:17'),
(31, 4, 'Rohit', 'login', '::1', '2025-09-06 04:34:26'),
(32, 4, 'Rohit', 'logout', '::1', '2025-09-06 04:34:51'),
(33, 1, 'dev', 'login', '::1', '2025-09-06 04:34:56'),
(34, 1, 'dev', 'login', '::1', '2025-09-06 10:51:08'),
(35, 1, 'dev', 'login', '::1', '2025-09-06 10:52:14'),
(36, 1, 'dev', 'login', '::1', '2025-09-06 11:00:47'),
(37, 1, 'dev', 'login', '::1', '2025-09-08 04:16:46'),
(38, 1, 'dev', 'login', '::1', '2025-09-08 04:27:00'),
(39, 1, 'dev', 'login', '::1', '2025-09-08 04:27:41'),
(40, 1, 'dev', 'login', '::1', '2025-09-08 04:42:29'),
(41, 1, 'dev', 'login', '::1', '2025-09-08 04:58:15'),
(42, 1, 'dev', 'logout', '::1', '2025-09-08 07:24:00'),
(43, 1, 'dev', 'login', '::1', '2025-09-08 07:27:30'),
(44, 1, 'dev', 'login', '::1', '2025-09-08 07:27:55'),
(45, 1, 'dev', 'logout', '::1', '2025-09-08 11:46:24'),
(46, 1, 'dev', 'login', '::1', '2025-09-08 11:48:40'),
(47, 1, 'dev', 'login', '::1', '2025-09-09 04:38:24'),
(48, 5, 'SANJAY', 'login', '::1', '2025-09-09 11:47:42'),
(49, 5, 'SANJAY', 'login', '::1', '2025-09-09 11:53:52'),
(50, 1, 'dev', 'logout', '::1', '2025-09-09 12:17:34'),
(51, 1, 'dev', 'login', '::1', '2025-09-09 12:19:04'),
(52, 5, 'SANJAY', 'logout', '::1', '2025-09-09 12:19:31'),
(53, 5, 'SANJAY', 'login', '::1', '2025-09-09 12:19:45'),
(54, 5, 'SANJAY', 'logout', '::1', '2025-09-09 12:20:47'),
(55, 5, 'SANJAY', 'login', '::1', '2025-09-09 12:21:01'),
(56, 5, 'SANJAY', 'logout', '::1', '2025-09-09 12:25:46'),
(57, 5, 'SANJAY', 'login', '::1', '2025-09-09 12:25:57'),
(58, 5, 'SANJAY', 'logout', '::1', '2025-09-09 12:27:31'),
(59, 5, 'SANJAY', 'login', '::1', '2025-09-09 12:27:44'),
(60, 5, 'SANJAY', 'logout', '::1', '2025-09-09 12:34:04'),
(61, 5, 'SANJAY', 'login', '::1', '2025-09-09 12:34:18'),
(62, 1, 'dev', 'login', '::1', '2025-09-10 04:27:09'),
(63, 5, 'SANJAY', 'login', '::1', '2025-09-10 04:28:21'),
(64, 5, 'SANJAY', 'logout', '::1', '2025-09-10 04:28:27'),
(65, 5, 'SANJAY', 'login', '::1', '2025-09-10 04:42:15'),
(66, 5, 'SANJAY', 'logout', '::1', '2025-09-10 05:33:52'),
(67, 5, 'SANJAY', 'login', '::1', '2025-09-10 05:34:01'),
(68, 1, 'dev', 'login', '::1', '2025-09-10 06:37:54'),
(69, 1, 'dev', 'login', '::1', '2025-09-10 06:43:32'),
(70, 1, 'dev', 'login', '::1', '2025-09-10 07:38:34'),
(71, 1, 'dev', 'login', '::1', '2025-09-10 07:38:54'),
(72, 1, 'dev', 'login', '::1', '2025-09-10 07:39:36'),
(73, 1, 'dev', 'login', '::1', '2025-09-10 07:41:14'),
(74, 1, 'dev', 'login', '::1', '2025-09-10 07:41:42'),
(75, 1, 'dev', 'login', '::1', '2025-09-10 07:44:15'),
(76, 1, 'dev', 'login', '::1', '2025-09-10 07:45:42'),
(77, 1, 'dev', 'login', '::1', '2025-09-10 07:46:46'),
(78, 1, 'dev', 'login', '::1', '2025-09-10 07:55:54'),
(79, 1, 'dev', 'login', '::1', '2025-09-10 07:57:11'),
(80, 1, 'dev', 'login', '::1', '2025-09-10 07:58:14'),
(81, 5, 'SANJAY', 'logout', '::1', '2025-09-10 10:12:16'),
(82, 1, 'dev', 'login', '::1', '2025-09-10 11:28:39'),
(83, 1, 'dev', 'login', '::1', '2025-09-11 04:45:27'),
(84, 1, 'dev', 'login', '::1', '2025-09-11 07:15:35'),
(85, 5, 'SANJAY', 'login', '::1', '2025-09-11 10:07:33'),
(86, 1, 'dev', 'login', '::1', '2025-09-12 04:19:22'),
(87, 1, 'dev', 'login', '::1', '2025-09-12 05:16:56'),
(88, 1, 'dev', 'login', '::1', '2025-09-13 04:24:05'),
(89, 7, 'Akash Sheoran', 'login', '::1', '2025-09-13 07:53:04'),
(90, 7, 'Akash Sheoran', 'logout', '::1', '2025-09-13 07:53:39'),
(91, 7, 'Akash Sheoran', 'login', '::1', '2025-09-13 07:53:47'),
(92, 7, 'Akash Sheoran', 'logout', '::1', '2025-09-13 07:54:05'),
(93, 7, 'Akash Sheoran', 'login', '::1', '2025-09-13 07:54:39'),
(94, 7, 'Akash Sheoran', 'login', '::1', '2025-09-13 08:02:56'),
(95, 7, 'Akash Sheoran', 'logout', '::1', '2025-09-13 08:09:27'),
(96, 7, 'Akash Sheoran', 'login', '::1', '2025-09-13 08:09:38'),
(97, 7, 'Akash Sheoran', 'login', '::1', '2025-09-13 08:10:06'),
(98, 7, 'Akash Sheoran', 'login', '::1', '2025-09-13 08:10:33'),
(99, 7, 'Akash Sheoran', 'logout', '::1', '2025-09-13 12:12:47'),
(100, 7, 'Akash Sheoran', 'login', '::1', '2025-09-13 12:12:55'),
(101, 1, 'dev', 'login', '::1', '2025-09-13 12:14:25'),
(102, 1, 'dev', 'login', '::1', '2025-09-13 12:17:24'),
(103, 1, 'dev', 'login', '::1', '2025-09-13 12:18:13'),
(104, 1, 'dev', 'logout', '::1', '2025-09-13 12:18:15'),
(105, 7, 'Akash Sheoran', 'login', '::1', '2025-09-13 12:18:21'),
(106, 1, 'dev', 'login', '::1', '2025-09-13 12:21:13'),
(107, 1, 'dev', 'login', '::1', '2025-09-15 04:36:00'),
(108, 1, 'dev', 'login', '::1', '2025-09-16 04:11:32'),
(109, 7, 'Akash Sheoran', 'login', '::1', '2025-09-16 12:11:49'),
(110, 7, 'Akash Sheoran', 'logout', '::1', '2025-09-16 12:15:35'),
(111, 7, 'Akash Sheoran', 'login', '::1', '2025-09-16 12:15:41'),
(112, 7, 'Akash Sheoran', 'logout', '::1', '2025-09-16 12:17:52'),
(113, 1, 'dev', 'login', '::1', '2025-09-16 12:18:46'),
(114, 7, 'Akash Sheoran', 'login', '::1', '2025-09-16 12:19:57'),
(115, 7, 'Akash Sheoran', 'logout', '::1', '2025-09-16 12:20:06'),
(116, 7, 'Akash Sheoran', 'login', '::1', '2025-09-16 12:20:38'),
(117, 7, 'Akash Sheoran', 'logout', '::1', '2025-09-16 12:22:17'),
(118, 7, 'Akash Sheoran', 'login', '::1', '2025-09-16 12:22:24'),
(119, 7, 'Akash Sheoran', 'logout', '::1', '2025-09-16 12:22:47'),
(120, 7, 'Akash Sheoran', 'login', '::1', '2025-09-16 12:23:15'),
(121, 7, 'Akash Sheoran', 'logout', '::1', '2025-09-16 12:23:33'),
(122, 7, 'Akash Sheoran', 'login', '::1', '2025-09-16 12:24:01'),
(123, 7, 'Akash Sheoran', 'logout', '::1', '2025-09-16 12:24:18'),
(124, 7, 'Akash Sheoran', 'login', '::1', '2025-09-16 12:24:38'),
(125, 7, 'Akash Sheoran', 'logout', '::1', '2025-09-16 12:27:10'),
(126, 7, 'Akash Sheoran', 'login', '::1', '2025-09-16 12:27:16'),
(127, 1, 'dev', 'login', '::1', '2025-09-17 04:13:16'),
(128, 7, 'Akash Sheoran', 'logout', '::1', '2025-09-17 04:26:00'),
(129, 7, 'Akash Sheoran', 'login', '::1', '2025-09-17 04:26:07'),
(130, 7, 'Akash Sheoran', 'logout', '::1', '2025-09-17 04:26:23'),
(131, 7, 'Akash Sheoran', 'login', '::1', '2025-09-17 04:31:45'),
(132, 7, 'Akash Sheoran', 'logout', '::1', '2025-09-17 04:32:12'),
(133, 7, 'Akash Sheoran', 'login', '::1', '2025-09-17 04:32:19'),
(134, 1, 'dev', 'logout', '::1', '2025-09-17 04:32:41'),
(135, 1, 'dev', 'login', '::1', '2025-09-17 04:32:48'),
(136, 1, 'dev', 'login', '::1', '2025-09-17 04:46:35'),
(137, 1, 'dev', 'login', '::1', '2025-09-17 04:48:06'),
(138, 1, 'dev', 'login', '::1', '2025-09-17 04:53:21'),
(139, 1, 'dev', 'logout', '::1', '2025-09-17 05:08:45'),
(140, 1, 'dev', 'login', '::1', '2025-09-17 05:08:58'),
(141, 1, 'Dev Sheoran', 'login', '::1', '2025-09-18 04:20:39'),
(142, 1, 'Dev Sheoran', 'login', '::1', '2025-09-19 04:15:07'),
(143, 1, 'Dev Sheoran', 'login', '::1', '2025-09-19 05:48:38'),
(144, 1, 'Dev Sheoran', 'login', '::1', '2025-09-22 04:21:23');

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
(208, 'BPL292586460', 25, 19, 3, NULL, 'Bhiwani,hashi Gate', 'Badhra', 'Sanjay Kumar Sheoran', 'sjsheoran111@gmail.com', '9728833428', '2025-09-22', 3000.00, 'PAID', 'order_RIEBCnIu8f4CKj', 'CONFIRMED', '2025-09-16 09:43:16'),
(210, 'BPL358966081', 25, 19, NULL, 1, 'Bhiwani,hashi Gate', 'Juiii', NULL, '', '8905288939', '2025-09-20', 100.00, 'PAID', NULL, 'CONFIRMED', '2025-09-16 09:51:15'),
(211, 'BPL202418354', 25, 19, NULL, 1, 'Juiii', 'Badhra', NULL, 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-16', 100.00, 'PAID', NULL, 'CONFIRMED', '2025-09-16 11:16:08'),
(212, 'BPL115742606', 25, 19, NULL, 7, 'Bhiwani,hashi Gate', 'Juiii', NULL, '', '', '2025-09-16', 100.00, 'PAID', NULL, 'CONFIRMED', '2025-09-16 12:16:08'),
(213, 'BPL381927974', 25, 19, NULL, 1, 'Bhiwani,hashi Gate', 'Badhra', NULL, 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-18', 600.00, 'PAID', NULL, 'CONFIRMED', '2025-09-17 07:20:57'),
(214, 'BPL769494032', 25, 19, 3, NULL, 'Bhiwani,hashi Gate', 'Badhra', 'Sanjay Kumar Sheoran', 'sjsheoran111@gmail.com', '9728833428', '2025-09-22', 1000.00, 'PAID', 'order_RIemvsJhbIZvMD', 'CONFIRMED', '2025-09-17 11:45:01'),
(215, 'BPL950291147', 25, 19, NULL, 1, 'Bhiwani,hashi Gate', 'Juiii', NULL, 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-18', 400.00, 'PAID', NULL, 'CONFIRMED', '2025-09-18 04:27:58'),
(216, 'BPL419474082', 25, 19, NULL, 1, 'Bhiwani,hashi Gate', 'Juiii', NULL, 'rohit@gmail.com', '8905288939', '2025-09-18', 300.00, 'PAID', NULL, 'CONFIRMED', '2025-09-18 04:40:40'),
(217, 'BPL188747523', 25, 19, NULL, 1, 'Bhiwani,hashi Gate', 'Juiii', NULL, 'rohit@gmail.com', '8905288939', '2025-09-18', 300.00, 'PAID', NULL, 'CONFIRMED', '2025-09-18 04:40:52'),
(218, 'BPL921024359', 25, 19, NULL, 1, 'Bhiwani,hashi Gate', 'Badhra', NULL, '', '', '2025-09-18', 200.00, 'PAID', NULL, 'CONFIRMED', '2025-09-18 04:47:46'),
(219, 'BPL013731820', 25, 19, NULL, 1, 'Bhiwani,hashi Gate', 'Badhra', NULL, 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-18', 200.00, 'PAID', NULL, 'CONFIRMED', '2025-09-18 04:48:25'),
(220, 'BPL721909527', 25, 19, NULL, 1, 'Bhiwani,hashi Gate', 'Badhra', NULL, 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-18', 200.00, 'PAID', NULL, 'CONFIRMED', '2025-09-18 04:51:35'),
(221, 'BPL756642102', 25, 19, NULL, 1, 'Bhiwani,hashi Gate', 'Juiii', NULL, 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-18', 100.00, 'PAID', NULL, 'CONFIRMED', '2025-09-18 04:52:01'),
(222, 'BPL005970830', 25, 19, NULL, 1, 'Bhiwani,hashi Gate', 'Juiii', NULL, 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-18', 100.00, 'PAID', NULL, 'CONFIRMED', '2025-09-18 04:54:10'),
(223, 'BPL505522306', 25, 19, NULL, 1, 'Bhiwani,hashi Gate', 'Juiii', NULL, 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-18', 100.00, 'PAID', NULL, 'CONFIRMED', '2025-09-18 05:23:46'),
(224, 'BPL490722615', 25, 19, NULL, 1, 'Bhiwani,hashi Gate', 'Badhra', NULL, 'rohit@gmail.com', '8905288939', '2025-09-18', 400.00, 'PAID', NULL, 'CONFIRMED', '2025-09-18 05:25:57'),
(225, 'BPL410844737', 25, 19, NULL, 1, 'Bhiwani,hashi Gate', 'Badhra', NULL, 'rohit@gmail.com', '8905288939', '2025-09-18', 400.00, 'PAID', NULL, 'CONFIRMED', '2025-09-18 06:08:30'),
(226, 'BPL218980435', 25, 19, NULL, 1, 'Bhiwani,hashi Gate', 'Juiii', NULL, 'rohitmechujaatji@gmail.com', '', '2025-09-18', 300.00, 'PAID', NULL, 'CONFIRMED', '2025-09-18 06:12:52'),
(227, 'BPL905139052', 25, 19, NULL, 1, 'Bhiwani,hashi Gate', 'Badhra', NULL, 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-22', 400.00, 'PAID', NULL, 'CONFIRMED', '2025-09-18 06:50:10'),
(228, 'BPL324081247', 25, 19, NULL, 1, 'Bhiwani,hashi Gate', 'Badhra', NULL, 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-22', 400.00, 'PAID', NULL, 'CONFIRMED', '2025-09-18 06:54:55'),
(229, 'BPL184247024', 25, 19, NULL, 1, 'Bhiwani,hashi Gate', 'Juiii', NULL, 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-18', 300.00, 'PAID', NULL, 'CONFIRMED', '2025-09-18 06:56:26'),
(230, 'BPL298198185', 25, 19, NULL, 1, 'Bhiwani,hashi Gate', 'Badhra', NULL, 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-18', 400.00, 'PAID', NULL, 'CONFIRMED', '2025-09-18 07:00:52'),
(231, 'BPL992489431', 25, 19, NULL, 1, 'Bhiwani,hashi Gate', 'Badhra', NULL, 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-18', 1400.00, 'PAID', NULL, 'CONFIRMED', '2025-09-18 07:16:05'),
(232, 'BPL258243504', 25, 19, NULL, 1, 'Bhiwani,hashi Gate', 'Badhra', NULL, 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-18', 1300.00, 'PAID', NULL, 'CONFIRMED', '2025-09-18 07:19:11'),
(233, 'BPL570205933', 25, 19, NULL, 1, 'Bhiwani,hashi Gate', 'Badhra', NULL, 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-18', 800.00, 'PAID', NULL, 'CONFIRMED', '2025-09-18 09:36:30'),
(234, 'BPL955117470', 25, 19, NULL, 1, 'Bhiwani,hashi Gate', 'Juiii', NULL, '', '', '2025-09-23', 300.00, 'PAID', NULL, 'CONFIRMED', '2025-09-18 10:48:06'),
(235, 'BPL290718061', 27, 20, 9, NULL, 'Loharu', 'Dadri', 'Sanjay Kumar Sheoran', 'sjsheoran111@gmail.com', '8989898989', '2025-09-22', 100.00, 'PAID', 'order_RJ3SDzOGmEiI8P', 'CONFIRMED', '2025-09-18 11:53:14'),
(236, 'BPL439653866', 25, 19, NULL, 1, 'Juiii', 'Badhra', NULL, 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-20', 200.00, 'PAID', NULL, 'CONFIRMED', '2025-09-19 04:55:23');

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
(19, 'Bus no 1', 'HR 61 B 2917', '53453454353453', '4535435435345', 'AC Seater', 0, 0, 0, NULL, 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Quisquam temporibus cupiditate est. Vitae delectus sit animi illo excepturi quidem magnam. Repudiandae dicta asperiores neque corrupti harum pariatur similique. Maiores veritatis sed, amet at consequuntur molestiae ad dolor eveniet culpa! Beatae tempore neque optio voluptate facilis vero quas, nulla asperiores, sit fugiat voluptas incidunt, ipsam in!', 'Active', '2025-09-13 10:12:29', '2025-09-13 11:13:54'),
(20, 'Bus no 2', 'HR 61 B 2918', '234234234', '23423423423', 'Non-AC Seater', 0, 0, 0, NULL, 'Lorem, ipsum dolor sit amet consectetur adipisicing elit. Ea mollitia quam ipsa suscipit eum est illo, nostrum rerum accusamus vitae esse explicabo culpa libero repudiandae. Eaque vero modi, alias iste veniam laborum recusandae cupiditate corporis. Ea nostrum officia iste maxime, unde laboriosam non ratione itaque ipsam! Similique excepturi consequatur asperiores, accusantium numquam deleniti tempora?', 'Active', '2025-09-13 10:24:44', '2025-09-13 11:13:43'),
(22, 'Hr 323jcdfjkdre', 'HR 61 B 29173', '53453454353453', '23423423423', 'AC Sleeper', 0, 0, 0, NULL, 'ssss', 'Active', '2025-09-16 13:29:10', '2025-09-16 13:29:10'),
(23, 'Hr 323jcdfjkdre23', 'HR 61 B 2932', '5345345435345323', '453543543534512', 'AC Seater', 0, 0, 0, NULL, 'Nice', 'Active', '2025-09-17 16:24:18', '2025-09-17 16:24:18');

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
(80, 20, 10),
(81, 20, 11),
(82, 20, 23),
(83, 20, 15),
(84, 20, 17),
(85, 20, 16),
(86, 20, 21),
(87, 19, 11),
(88, 19, 13),
(89, 19, 8),
(90, 19, 12),
(91, 19, 18),
(93, 22, 10),
(94, 22, 11),
(95, 23, 11),
(96, 23, 23),
(97, 23, 22);

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
(27, 19, 'bus_19_1757738549_68c4f6357a032.jpg', '2025-09-13 04:42:29'),
(28, 19, 'bus_19_1757738549_68c4f6357a277.jpg', '2025-09-13 04:42:29'),
(29, 20, 'bus_20_1757739284_68c4f91443e85.jpg', '2025-09-13 04:54:44'),
(30, 20, 'bus_20_1757739284_68c4f91444007.jpg', '2025-09-13 04:54:44'),
(33, 23, 'bus_23_1758106458_68ca935a72574.jpg', '2025-09-17 10:54:18'),
(34, 23, 'bus_23_1758106458_68ca935a732c4.jpg', '2025-09-17 10:54:18');

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
(31, 208, 233, 500.00, '4343', '23432432423423432', 'COMPLETED', '2025-09-17 11:46:40'),
(32, 208, 234, 0.00, 'Invalid bank details provided.', NULL, 'FAILED', '2025-09-17 11:46:40'),
(33, 208, 235, 0.00, 'Cancellation policy violation.', NULL, 'FAILED', '2025-09-17 11:46:40'),
(34, 208, 232, 500.00, 'Change of Plans', '23432432423423432', 'COMPLETED', '2025-09-17 11:59:33'),
(35, 208, 236, 0.00, 'ddfsfds', NULL, 'FAILED', '2025-09-17 11:59:33'),
(36, 208, 237, 0.00, 'Technical error during processing.', NULL, 'FAILED', '2025-09-17 11:59:33');

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
(90, 161, 600.00, 1, 1, '2025-09-13 11:59:30'),
(91, 165, 100.00, 1, 1, '2025-09-13 11:59:30'),
(92, 166, 800.00, 1, 1, '2025-09-13 11:59:30'),
(93, 168, 300.00, 1, 7, '2025-09-13 12:21:35'),
(94, 170, 100.00, 1, 7, '2025-09-13 12:21:35'),
(95, 212, 100.00, 1, 7, '2025-09-17 10:51:22'),
(96, 210, 100.00, 1, 1, '2025-09-19 05:00:35'),
(97, 213, 600.00, 1, 1, '2025-09-19 05:00:35'),
(98, 215, 400.00, 1, 1, '2025-09-19 05:00:35'),
(99, 216, 300.00, 1, 1, '2025-09-19 05:00:35'),
(100, 217, 300.00, 1, 1, '2025-09-19 05:00:35'),
(101, 218, 200.00, 1, 1, '2025-09-19 05:00:35'),
(102, 219, 200.00, 1, 1, '2025-09-19 05:00:35'),
(103, 220, 200.00, 1, 1, '2025-09-19 05:00:35'),
(104, 221, 100.00, 1, 1, '2025-09-19 05:00:35'),
(105, 222, 100.00, 1, 1, '2025-09-19 05:00:35'),
(106, 223, 100.00, 1, 1, '2025-09-19 05:00:35'),
(107, 224, 400.00, 1, 1, '2025-09-19 05:00:35'),
(108, 225, 400.00, 1, 1, '2025-09-19 05:00:35'),
(109, 226, 300.00, 1, 1, '2025-09-19 05:00:35'),
(110, 227, 400.00, 1, 1, '2025-09-19 05:00:35'),
(111, 228, 400.00, 1, 1, '2025-09-19 05:00:35'),
(112, 229, 300.00, 1, 1, '2025-09-19 05:00:35'),
(113, 230, 400.00, 1, 1, '2025-09-19 05:00:35'),
(114, 232, 1300.00, 1, 1, '2025-09-19 05:00:35'),
(115, 233, 800.00, 1, 1, '2025-09-19 05:00:35'),
(116, 234, 300.00, 1, 1, '2025-09-19 05:00:35'),
(117, 236, 200.00, 1, 1, '2025-09-19 05:00:35');

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
(1, 27, '2025-09-23', 'Sanjay', '8834384223', 1, '2025-09-18 10:32:05'),
(4, 27, '2025-09-19', 'Sanjay', '8834384223', 1, '2025-09-18 11:57:01');

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

--
-- Dumping data for table `deleted_buses`
--

INSERT INTO `deleted_buses` (`bus_id`, `bus_name`, `registration_number`, `engine_no`, `chassis_no`, `bus_type`, `amenities`, `description`, `status`, `created_at`, `updated_at`, `deleted_by_employee_id`, `deleted_at`) VALUES
(21, 'Hr 323jcdfjkdre', 'HR 61 B 2917332', '534534543534533', '234234234233', 'AC Seater', NULL, '323', 'Active', '2025-09-16 13:26:49', '2025-09-16 13:26:49', 1, '2025-09-16 07:57:12');

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

--
-- Dumping data for table `deleted_routes`
--

INSERT INTO `deleted_routes` (`route_id`, `bus_id`, `route_name`, `starting_point`, `ending_point`, `status`, `is_popular`, `created_at`, `deleted_by_employee_id`, `deleted_at`) VALUES
(28, 19, 'Delhi To Pilani', 'Loharu', 'gfdgdfg', 'Active', 0, '2025-09-16 10:03:58', 1, '2025-09-16 10:04:32'),
(29, 19, 'Delhi To Pilani', 'jhgghfh', 'jhgghfh', 'Active', 0, '2025-09-19 05:09:57', 1, '2025-09-19 05:19:14');

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
(234, 208, 401, 'UP7', '32', NULL, 32, 'MALE', 500.00, 'CONFIRMED'),
(235, 208, 402, 'UP8', '32', NULL, 32, 'MALE', 500.00, 'CONFIRMED'),
(236, 208, 404, 'UP10', '32', NULL, 32, 'MALE', 500.00, 'CONFIRMED'),
(237, 208, 405, 'UP11', '32', NULL, 32, 'MALE', 500.00, 'CONFIRMED'),
(240, 210, 372, 'LS1', 'rohit', '', 22, 'MALE', 100.00, 'CONFIRMED'),
(241, 211, 374, 'LP3', 'song', '', 55, 'MALE', 100.00, 'CONFIRMED'),
(242, 212, 372, 'LS1', 'ssdsd', '', 23, 'MALE', 100.00, 'CONFIRMED'),
(243, 213, 375, 'LP4', 'Sanjay', '', 22, 'MALE', 400.00, 'CONFIRMED'),
(244, 213, 393, 'LS8', 'rohit', '', 22, 'MALE', 200.00, 'CONFIRMED'),
(245, 214, 400, 'UP6', '4324', NULL, 22, 'MALE', 500.00, 'CONFIRMED'),
(246, 214, 403, 'UP9', '4234', NULL, 22, 'MALE', 500.00, 'CONFIRMED'),
(247, 215, 373, 'LS2', '12', '', 12, 'MALE', 100.00, 'CONFIRMED'),
(248, 215, 374, 'LP3', '12', '', 12, 'MALE', 300.00, 'CONFIRMED'),
(249, 216, 378, 'LP7', '12', '', 12, 'MALE', 300.00, 'CONFIRMED'),
(250, 217, 378, 'LP7', '12', '', 12, 'MALE', 300.00, 'CONFIRMED'),
(251, 218, 372, 'LS1', 'rohit', '', 23, 'MALE', 200.00, 'CONFIRMED'),
(252, 219, 385, 'LS3', 'gf', '', 22, 'MALE', 200.00, 'CONFIRMED'),
(253, 220, 385, 'LS3', 'gf', '', 22, 'MALE', 200.00, 'CONFIRMED'),
(254, 221, 389, 'LS5', '12', '', 12, 'MALE', 100.00, 'CONFIRMED'),
(255, 222, 389, 'LS5', '12', '', 12, 'MALE', 100.00, 'CONFIRMED'),
(256, 223, 389, 'LS5', '12', '', 12, 'MALE', 100.00, 'CONFIRMED'),
(257, 224, 376, 'LP5', 'Rohit', '', 22, 'MALE', 400.00, 'CONFIRMED'),
(258, 225, 376, 'LP5', 'Rohit', '', 22, 'MALE', 400.00, 'CONFIRMED'),
(259, 226, 377, 'LP6', 'wq', '', 21, 'MALE', 300.00, 'CONFIRMED'),
(260, 227, 375, 'LP4', 'Sanjay', '', 22, 'MALE', 400.00, 'CONFIRMED'),
(261, 228, 375, 'LP4', 'Sanjay', '', 22, 'MALE', 400.00, 'CONFIRMED'),
(262, 229, 380, 'LP9', 'fdf', '', 32, 'MALE', 300.00, 'CONFIRMED'),
(263, 230, 381, 'LP10', '12', '', 12, 'MALE', 400.00, 'CONFIRMED'),
(264, 231, 384, 'LP13', 'Rohit', '', 22, 'MALE', 400.00, 'CONFIRMED'),
(265, 231, 395, 'UP1', 'Sanjay', '', 22, 'MALE', 500.00, 'CONFIRMED'),
(266, 231, 396, 'UP2', 'dev', '', 22, 'MALE', 500.00, 'CONFIRMED'),
(267, 232, 382, 'LP11', 'Sanjay', '', 23, 'MALE', 400.00, 'CONFIRMED'),
(268, 232, 371, 'LP2', 'Rohit', '', 22, 'MALE', 400.00, 'CONFIRMED'),
(269, 232, 405, 'UP11', 'dev', '', 24, 'MALE', 500.00, 'CONFIRMED'),
(270, 233, 379, 'LP8', 'rohit', '', 22, 'MALE', 400.00, 'CONFIRMED'),
(271, 233, 383, 'LP12', 'Sanjay', '', 24, 'MALE', 400.00, 'CONFIRMED'),
(272, 234, 371, 'LP2', '21', '', 21, 'MALE', 300.00, 'CONFIRMED'),
(273, 235, 415, 'LP3', 'Aarti', NULL, 22, 'FEMALE', 50.00, 'CONFIRMED'),
(274, 235, 413, 'LP1', 'Sanjay', NULL, 23, 'MALE', 50.00, 'CONFIRMED'),
(275, 236, 382, 'LP11', 'Sanjay', '', 22, 'MALE', 100.00, 'CONFIRMED'),
(276, 236, 371, 'LP2', 'Rohit', '', 53, 'FEMALE', 100.00, 'CONFIRMED');

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
(24, 19, 'Delhi To Pilani', 'Delhi, kasmiri Gate', 'Pilani,Rj', 'Active', 1, 0, '2025-09-13 05:06:23'),
(25, 19, 'Bhiwani to Badhra', 'Bhiwani,hashi Gate', 'Badhra', 'Active', 1, 0, '2025-09-13 05:24:25'),
(26, 20, 'Loharu To Badhra', 'Loharu', 'Badhra', 'Active', 0, 0, '2025-09-13 05:29:16'),
(27, 20, 'Loharu To Dadri', 'Loharu', 'Dadri', 'Active', 1, 0, '2025-09-13 05:31:52');

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
(61, 25, 'Mon', '14:00:00'),
(62, 25, 'Tue', '14:30:00'),
(63, 25, 'Thu', '15:00:00'),
(64, 25, 'Sat', '15:30:00'),
(75, 26, 'Mon', '14:00:00'),
(76, 26, 'Wed', '15:00:00'),
(77, 26, 'Fri', '16:00:00'),
(78, 26, 'Sun', '17:00:00'),
(82, 24, 'Mon', '12:00:00'),
(83, 24, 'Tue', '12:00:00'),
(84, 24, 'Wed', '12:30:00'),
(85, 27, 'Mon', '21:00:00'),
(86, 27, 'Wed', '21:30:00');

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
(1, 23, 20, 'Driver'),
(2, 23, 20, 'Co-Driver'),
(3, 23, 26, 'Conductor'),
(4, 23, 26, 'Co-Conductor'),
(5, 23, 37, 'Helper'),
(6, 23, 38, 'Helper'),
(7, 23, 36, 'Helper'),
(8, 15, 20, 'Driver'),
(9, 15, 20, 'Co-Driver'),
(10, 15, 26, 'Conductor'),
(11, 15, 26, 'Co-Conductor'),
(12, 15, 37, 'Helper'),
(13, 15, 38, 'Helper'),
(19, 25, 43, 'Driver'),
(20, 25, 39, 'Conductor'),
(21, 25, 46, 'Helper'),
(22, 25, 45, 'Helper'),
(30, 26, 44, 'Driver'),
(31, 26, 49, 'Co-Driver'),
(32, 26, 48, 'Conductor'),
(33, 26, 46, 'Helper'),
(34, 26, 45, 'Helper'),
(39, 24, 43, 'Driver'),
(40, 24, 44, 'Co-Driver'),
(41, 24, 48, 'Conductor'),
(42, 24, 45, 'Helper'),
(43, 27, 49, 'Driver'),
(44, 27, 39, 'Conductor'),
(45, 27, 45, 'Helper'),
(51, 28, 44, 'Driver'),
(52, 28, 44, 'Co-Driver'),
(53, 28, 39, 'Conductor'),
(54, 28, 39, 'Co-Conductor'),
(55, 28, 45, 'Helper');

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
(85, 25, 'Juiii', 1, 60, 100.00, 200.00, 300.00, 400.00),
(86, 25, 'Badhra', 2, 120, 200.00, 300.00, 400.00, 500.00),
(99, 26, 'Basirwas', 1, 30, 50.00, 100.00, 150.00, 200.00),
(100, 26, 'Laad', 2, 40, 60.00, 70.00, 80.00, 90.00),
(101, 26, 'Badhra', 3, 50, 70.00, 80.00, 90.00, 100.00),
(105, 24, 'Rohtak, Purana Bus Stand', 1, 60, 200.00, 300.00, 400.00, 500.00),
(106, 24, 'Loharu,Haryana', 2, 120, 300.00, 400.00, 500.00, 600.00),
(107, 24, 'Pilani,Rj', 3, 180, 400.00, 500.00, 600.00, 700.00),
(108, 27, 'Laad', 1, 20, 10.00, 20.00, 30.00, 40.00),
(109, 27, 'Badhra', 2, 40, 20.00, 30.00, 40.00, 50.00),
(110, 27, 'Dadri', 3, 60, 30.00, 40.00, 50.00, 60.00);

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
(370, 19, 'LP1', 'LOWER', 'SLEEPER', 210, 540, 40, 80, 'VERTICAL_UP', 0.00, 'FEMALE', 1, 'AVAILABLE', '2025-09-13 10:12:34', '2025-09-13 10:20:55'),
(371, 19, 'LP2', 'LOWER', 'SLEEPER', 210, 450, 40, 80, 'VERTICAL_UP', 0.00, 'FEMALE', 1, 'AVAILABLE', '2025-09-13 10:13:16', '2025-09-13 10:20:48'),
(372, 19, 'LS1', 'LOWER', 'SEATER', 210, 80, 40, 40, 'VERTICAL_UP', 0.00, 'MALE', 1, 'AVAILABLE', '2025-09-13 10:13:21', '2025-09-13 10:20:28'),
(373, 19, 'LS2', 'LOWER', 'SEATER', 210, 130, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:13:31', '2025-09-13 10:16:34'),
(374, 19, 'LP3', 'LOWER', 'SLEEPER', 210, 180, 40, 80, 'VERTICAL_UP', 0.00, 'FEMALE', 1, 'AVAILABLE', '2025-09-13 10:13:37', '2025-09-13 10:20:13'),
(375, 19, 'LP4', 'LOWER', 'SLEEPER', 100, 170, 40, 80, 'VERTICAL_UP', 0.00, 'MALE', 1, 'AVAILABLE', '2025-09-13 10:13:42', '2025-09-13 10:21:04'),
(376, 19, 'LP5', 'LOWER', 'SLEEPER', 50, 170, 40, 80, 'VERTICAL_UP', 0.00, 'MALE', 1, 'AVAILABLE', '2025-09-13 10:13:47', '2025-09-13 10:20:59'),
(377, 19, 'LP6', 'LOWER', 'SLEEPER', 50, 260, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:13:52', '2025-09-13 10:17:59'),
(378, 19, 'LP7', 'LOWER', 'SLEEPER', 100, 260, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:13:56', '2025-09-13 10:18:00'),
(379, 19, 'LP8', 'LOWER', 'SLEEPER', 50, 350, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:14:02', '2025-09-13 10:17:54'),
(380, 19, 'LP9', 'LOWER', 'SLEEPER', 100, 350, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:14:08', '2025-09-13 10:17:56'),
(381, 19, 'LP10', 'LOWER', 'SLEEPER', 210, 270, 40, 80, 'VERTICAL_UP', 0.00, 'FEMALE', 1, 'AVAILABLE', '2025-09-13 10:14:13', '2025-09-13 10:20:20'),
(382, 19, 'LP11', 'LOWER', 'SLEEPER', 210, 360, 40, 80, 'VERTICAL_UP', 0.00, 'FEMALE', 1, 'AVAILABLE', '2025-09-13 10:14:19', '2025-09-13 10:20:42'),
(383, 19, 'LP12', 'LOWER', 'SLEEPER', 100, 440, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:14:26', '2025-09-13 10:17:53'),
(384, 19, 'LP13', 'LOWER', 'SLEEPER', 50, 440, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:14:29', '2025-09-13 10:17:41'),
(385, 19, 'LS3', 'LOWER', 'SEATER', 50, 120, 40, 40, 'VERTICAL_UP', 0.00, 'MALE', 1, 'AVAILABLE', '2025-09-13 10:14:35', '2025-09-13 10:21:11'),
(386, 19, 'LG1', 'LOWER', 'AISLE', 50, 70, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 0, 'AVAILABLE', '2025-09-13 10:14:43', '2025-09-13 10:18:09'),
(388, 19, 'LS4', 'LOWER', 'SEATER', 50, 20, 40, 40, 'VERTICAL_UP', 0.00, 'MALE', 0, 'AVAILABLE', '2025-09-13 10:15:31', '2025-09-13 10:20:33'),
(389, 19, 'LS5', 'LOWER', 'SEATER', 100, 120, 40, 40, 'VERTICAL_UP', 0.00, 'FEMALE', 1, 'AVAILABLE', '2025-09-13 10:15:44', '2025-09-13 10:21:16'),
(390, 19, 'LG3', 'LOWER', 'AISLE', 50, 530, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 0, 'AVAILABLE', '2025-09-13 10:15:49', '2025-09-13 10:17:44'),
(391, 19, 'LS6', 'LOWER', 'SEATER', 50, 580, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:15:53', '2025-09-13 10:17:45'),
(392, 19, 'LS7', 'LOWER', 'SEATER', 150, 580, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:16:00', '2025-09-13 10:17:50'),
(393, 19, 'LS8', 'LOWER', 'SEATER', 100, 580, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:16:04', '2025-09-13 10:17:48'),
(394, 19, 'DRIVER', 'LOWER', 'DRIVER', 200, 20, 50, 50, 'VERTICAL_UP', 0.00, 'ANY', 0, 'AVAILABLE', '2025-09-13 10:16:42', '2025-09-13 10:17:13'),
(395, 19, 'UP1', 'UPPER', 'SLEEPER', 50, 50, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:18:17', '2025-09-13 10:18:33'),
(396, 19, 'UP2', 'UPPER', 'SLEEPER', 100, 50, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:18:20', '2025-09-13 10:19:25'),
(397, 19, 'UP3', 'UPPER', 'SLEEPER', 210, 50, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:18:29', '2025-09-13 10:19:27'),
(398, 19, 'UP4', 'UPPER', 'SLEEPER', 50, 140, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:18:50', '2025-09-13 10:18:51'),
(399, 19, 'UP5', 'UPPER', 'SLEEPER', 100, 140, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:18:54', '2025-09-13 10:18:55'),
(400, 19, 'UP6', 'UPPER', 'SLEEPER', 210, 140, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:18:58', '2025-09-13 10:18:59'),
(401, 19, 'UP7', 'UPPER', 'SLEEPER', 50, 230, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:19:02', '2025-09-13 10:19:03'),
(402, 19, 'UP8', 'UPPER', 'SLEEPER', 100, 230, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:19:06', '2025-09-13 10:19:08'),
(403, 19, 'UP9', 'UPPER', 'SLEEPER', 210, 230, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:19:11', '2025-09-13 10:19:12'),
(404, 19, 'UP10', 'UPPER', 'SLEEPER', 50, 320, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:19:19', '2025-09-13 10:19:20'),
(405, 19, 'UP11', 'UPPER', 'SLEEPER', 100, 320, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:19:33', '2025-09-13 10:19:34'),
(406, 19, 'UP12', 'UPPER', 'SLEEPER', 210, 320, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:19:37', '2025-09-13 10:19:37'),
(407, 19, 'UP13', 'UPPER', 'SLEEPER', 50, 410, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:19:43', '2025-09-13 10:19:44'),
(408, 19, 'UP14', 'UPPER', 'SLEEPER', 100, 410, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:19:47', '2025-09-13 10:19:47'),
(409, 19, 'UP15', 'UPPER', 'SLEEPER', 210, 410, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:19:50', '2025-09-13 10:19:51'),
(410, 19, 'UP16', 'UPPER', 'SLEEPER', 210, 500, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:19:55', '2025-09-13 10:19:57'),
(411, 19, 'UP17', 'UPPER', 'SLEEPER', 50, 500, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:20:01', '2025-09-13 10:20:02'),
(412, 19, 'UP18', 'UPPER', 'SLEEPER', 100, 500, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:20:06', '2025-09-13 10:20:07'),
(413, 20, 'LP1', 'LOWER', 'SLEEPER', 50, 120, 40, 80, 'VERTICAL_UP', 0.00, 'MALE', 1, 'AVAILABLE', '2025-09-13 10:24:48', '2025-09-13 10:31:30'),
(414, 20, 'LP2', 'LOWER', 'SLEEPER', 100, 120, 40, 80, 'VERTICAL_UP', 0.00, 'FEMALE', 1, 'AVAILABLE', '2025-09-13 10:24:49', '2025-09-13 10:31:34'),
(415, 20, 'LP3', 'LOWER', 'SLEEPER', 200, 70, 40, 80, 'VERTICAL_UP', 0.00, 'FEMALE', 1, 'AVAILABLE', '2025-09-13 10:25:38', '2025-09-13 10:31:38'),
(416, 20, 'LG1', 'LOWER', 'AISLE', 50, 70, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 0, 'AVAILABLE', '2025-09-13 10:25:53', '2025-09-13 10:25:58'),
(418, 20, 'DRIVER', 'LOWER', 'DRIVER', 190, 10, 50, 50, 'VERTICAL_UP', 0.00, 'ANY', 0, 'AVAILABLE', '2025-09-13 10:26:16', '2025-09-13 10:27:53'),
(419, 20, 'LP4', 'LOWER', 'SLEEPER', 50, 210, 40, 80, 'VERTICAL_UP', 0.00, 'FEMALE', 1, 'AVAILABLE', '2025-09-13 10:26:33', '2025-09-13 10:31:47'),
(420, 20, 'LP5', 'LOWER', 'SLEEPER', 100, 210, 40, 80, 'VERTICAL_UP', 0.00, 'MALE', 1, 'AVAILABLE', '2025-09-13 10:26:36', '2025-09-13 10:31:42'),
(421, 20, 'LP6', 'LOWER', 'SLEEPER', 50, 300, 40, 80, 'VERTICAL_UP', 0.00, 'FEMALE', 1, 'AVAILABLE', '2025-09-13 10:26:41', '2025-09-13 10:32:05'),
(422, 20, 'LP7', 'LOWER', 'SLEEPER', 100, 300, 40, 80, 'VERTICAL_UP', 0.00, 'MALE', 1, 'AVAILABLE', '2025-09-13 10:26:45', '2025-09-13 10:32:00'),
(423, 20, 'LP8', 'LOWER', 'SLEEPER', 50, 390, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:26:50', '2025-09-13 10:26:50'),
(424, 20, 'LP9', 'LOWER', 'SLEEPER', 100, 390, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:26:54', '2025-09-13 10:26:54'),
(425, 20, 'LP10', 'LOWER', 'SLEEPER', 50, 480, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:26:59', '2025-09-13 10:27:00'),
(426, 20, 'LP11', 'LOWER', 'SLEEPER', 100, 480, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:27:02', '2025-09-13 10:27:04'),
(427, 20, 'LS1', 'LOWER', 'SEATER', 50, 570, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:27:10', '2025-09-13 10:27:13'),
(428, 20, 'LS2', 'LOWER', 'SEATER', 100, 570, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:27:15', '2025-09-13 10:27:16'),
(429, 20, 'LS3', 'LOWER', 'SEATER', 150, 570, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:27:19', '2025-09-13 10:27:22'),
(430, 20, 'LS4', 'LOWER', 'SEATER', 200, 570, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:27:26', '2025-09-13 10:27:27'),
(431, 20, 'LP12', 'LOWER', 'SLEEPER', 200, 160, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:27:30', '2025-09-13 10:27:57'),
(432, 20, 'LP13', 'LOWER', 'SLEEPER', 200, 250, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:27:34', '2025-09-13 10:27:59'),
(433, 20, 'LP14', 'LOWER', 'SLEEPER', 200, 340, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:27:37', '2025-09-13 10:28:00'),
(434, 20, 'LP15', 'LOWER', 'SLEEPER', 200, 430, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:27:41', '2025-09-13 10:28:02'),
(435, 20, 'LS5', 'LOWER', 'SEATER', 200, 520, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:27:45', '2025-09-13 10:28:04'),
(436, 20, 'LS6', 'LOWER', 'SEATER', 50, 20, 40, 40, 'VERTICAL_UP', 0.00, 'MALE', 0, 'AVAILABLE', '2025-09-13 10:28:44', '2025-09-13 10:28:59'),
(437, 20, 'UP1', 'UPPER', 'SLEEPER', 50, 30, 40, 80, 'VERTICAL_UP', 0.00, 'FEMALE', 1, 'AVAILABLE', '2025-09-13 10:29:10', '2025-09-13 10:31:03'),
(438, 20, 'UP2', 'UPPER', 'SLEEPER', 210, 30, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:29:18', '2025-09-13 10:29:20'),
(439, 20, 'UP3', 'UPPER', 'SLEEPER', 100, 30, 40, 80, 'VERTICAL_UP', 0.00, 'MALE', 1, 'AVAILABLE', '2025-09-13 10:29:23', '2025-09-13 10:31:07'),
(440, 20, 'UP4', 'UPPER', 'SLEEPER', 50, 120, 40, 80, 'VERTICAL_UP', 0.00, 'MALE', 1, 'AVAILABLE', '2025-09-13 10:29:29', '2025-09-13 10:31:11'),
(441, 20, 'UP5', 'UPPER', 'SLEEPER', 100, 120, 40, 80, 'VERTICAL_UP', 0.00, 'FEMALE', 1, 'AVAILABLE', '2025-09-13 10:29:34', '2025-09-13 10:31:15'),
(442, 20, 'UP6', 'UPPER', 'SLEEPER', 210, 120, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:29:38', '2025-09-13 10:29:39'),
(443, 20, 'UP7', 'UPPER', 'SLEEPER', 50, 210, 40, 80, 'VERTICAL_UP', 0.00, 'MALE', 1, 'AVAILABLE', '2025-09-13 10:29:42', '2025-09-13 10:31:20'),
(444, 20, 'UP8', 'UPPER', 'SLEEPER', 100, 210, 40, 80, 'VERTICAL_UP', 0.00, 'FEMALE', 1, 'AVAILABLE', '2025-09-13 10:29:47', '2025-09-13 10:31:25'),
(445, 20, 'UP9', 'UPPER', 'SLEEPER', 210, 210, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:29:51', '2025-09-13 10:29:51'),
(446, 20, 'UP10', 'UPPER', 'SLEEPER', 50, 300, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:29:56', '2025-09-13 10:30:02'),
(447, 20, 'UP11', 'UPPER', 'SLEEPER', 100, 300, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:30:00', '2025-09-13 10:30:01'),
(448, 20, 'UP12', 'UPPER', 'SLEEPER', 210, 300, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:30:06', '2025-09-13 10:30:06'),
(449, 20, 'UP13', 'UPPER', 'SLEEPER', 50, 390, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:30:09', '2025-09-13 10:30:09'),
(450, 20, 'UP14', 'UPPER', 'SLEEPER', 100, 390, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:30:12', '2025-09-13 10:30:14'),
(451, 20, 'UP15', 'UPPER', 'SLEEPER', 210, 390, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:30:18', '2025-09-13 10:30:19'),
(452, 20, 'UP16', 'UPPER', 'SLEEPER', 50, 480, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:30:22', '2025-09-13 10:30:24'),
(453, 20, 'UP17', 'UPPER', 'SLEEPER', 100, 480, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:30:28', '2025-09-13 10:30:29'),
(454, 20, 'UP18', 'UPPER', 'SLEEPER', 210, 480, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:30:33', '2025-09-13 10:30:34'),
(455, 20, 'US1', 'UPPER', 'SEATER', 50, 570, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:30:37', '2025-09-13 10:30:38'),
(456, 20, 'US2', 'UPPER', 'SEATER', 100, 570, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:30:42', '2025-09-13 10:30:43'),
(457, 20, 'US3', 'UPPER', 'SEATER', 160, 570, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:30:46', '2025-09-13 10:30:49'),
(458, 20, 'US4', 'UPPER', 'SEATER', 210, 570, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:30:52', '2025-09-13 10:30:54'),
(463, 22, 'LP1', 'LOWER', 'SLEEPER', 50, 20, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-16 13:29:13', '2025-09-16 13:29:13'),
(464, 22, 'LP2', 'LOWER', 'SLEEPER', 170, 20, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-16 13:29:15', '2025-09-16 13:29:15'),
(465, 22, 'LS1', 'LOWER', 'SEATER', 100, 130, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-16 13:29:16', '2025-09-16 13:29:16'),
(466, 22, 'UP1', 'UPPER', 'SLEEPER', 70, 50, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-16 13:29:18', '2025-09-16 13:29:18'),
(467, 22, 'LP3', 'LOWER', 'SLEEPER', 180, 130, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-16 16:44:29', '2025-09-16 16:44:29'),
(468, 22, 'DRIVER', 'LOWER', 'DRIVER', 120, 320, 50, 50, 'VERTICAL_UP', 0.00, 'ANY', 0, 'AVAILABLE', '2025-09-16 16:44:37', '2025-09-16 16:44:38'),
(469, 23, 'DRIVER', 'LOWER', 'DRIVER', 200, 30, 50, 50, 'VERTICAL_UP', 0.00, 'ANY', 0, 'AVAILABLE', '2025-09-17 16:24:45', '2025-09-17 16:24:48'),
(470, 23, 'LP1', 'LOWER', 'SLEEPER', 200, 100, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-17 16:24:52', '2025-09-17 16:24:56'),
(471, 23, 'LP2', 'LOWER', 'SLEEPER', 110, 100, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-17 16:25:01', '2025-09-17 16:25:03'),
(472, 23, 'LP3', 'LOWER', 'SLEEPER', 60, 100, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-17 16:25:06', '2025-09-17 16:25:08'),
(473, 23, 'LS1', 'LOWER', 'SEATER', 200, 190, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-17 16:25:21', '2025-09-17 16:25:23'),
(474, 23, 'LP4', 'LOWER', 'SLEEPER', 110, 190, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-17 16:25:27', '2025-09-17 16:25:29'),
(475, 23, 'LP5', 'LOWER', 'SLEEPER', 60, 190, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-17 16:25:33', '2025-09-17 16:25:35'),
(476, 23, 'UP1', 'UPPER', 'SLEEPER', 180, 40, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-17 16:25:42', '2025-09-17 16:25:44'),
(477, 23, 'UP2', 'UPPER', 'SLEEPER', 70, 40, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-17 16:25:47', '2025-09-17 16:25:48');

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
('company_address', 'LOharu Haryana'),
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
(39, 'Sanjay Sheoran', '8905288912', 'Conductor', NULL, '2132321312321233', 'staff_1757737621_b23ed4d7.jpg', 'rere', 'Active', '2025-09-13 04:27:01'),
(43, 'Rohit Choudhary', '8905288931', 'Driver', '1231231223322', '2132321312321231', 'staff_1757737706_3b730fcc.jpg', 'dfd', 'Active', '2025-09-13 04:28:26'),
(44, 'Dev Sheoran', '2222222222', 'Driver', '1231231223324', '2132321312321234', 'staff_1757737764_19cfc0ed.jpg', 'vcvcv', 'Active', '2025-09-13 04:29:24'),
(45, 'Naveen Sheoran', '1234567890', 'Helper', NULL, '2132321312321215', 'staff_1757737804_2b420ce9.jpg', 'gfgdf', 'Active', '2025-09-13 04:30:04'),
(46, 'Akash Sheoran', '1122334455', 'Helper', NULL, '2132321312321237', '', 'fdgdfg', 'Active', '2025-09-13 04:30:51'),
(48, 'Amit', '1122334456', 'Conductor', NULL, '2132321312321238', '', 'sdfsd', 'Active', '2025-09-13 04:31:54'),
(49, 'Sandeep', '1234567892', 'Driver', '12312312233211', '2132321312321239', '', 'f', 'Active', '2025-09-13 04:32:55');

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
(32, 158, 'cbcb3b93b3244efdf0f953f2e3f42229', '2025-09-13 05:53:05'),
(33, 159, '31c60129795ba36790b0f3fd71443997', '2025-09-13 06:00:58'),
(34, 160, '3910527ad398c2f5c070e3cf9b4724f7', '2025-09-13 06:15:37'),
(35, 165, 'c327a7c025c0b625888e71f93b11e8dc', '2025-09-13 11:13:05'),
(36, 166, 'be6a2d6dc7f6c80bd39c5f8bca04b620', '2025-09-13 11:14:08'),
(37, 168, 'ce5cdba38c0de7cd89b8a4ebf5a83253', '2025-09-13 11:58:33'),
(38, 169, '19d7f8ba4012b711acea2ccbece21a3b', '2025-09-13 12:01:30'),
(39, 175, '4f850919e30373c167266e2cbc2448381d5c816f', '2025-09-15 08:04:51'),
(40, 176, 'd7327676da42f7d9bedfbf54e6d498dbb7464466', '2025-09-15 08:18:25'),
(41, 177, 'b2167895b507c94a8c6724fff90d57fdd14b8e0c', '2025-09-15 08:19:40'),
(42, 181, '43135e485dc9d098d1562942aab1727e13d04442', '2025-09-15 10:40:43'),
(43, 182, 'eb4ee076ff259b6331d30b78f2da1f52dc5ec748', '2025-09-15 10:44:45'),
(44, 178, '1efe753bac551998514008a77b3fc77ed24d07b0', '2025-09-15 11:07:07'),
(45, 183, '6822a3adb3def81a8019dc1bb1261ef2bf7372ac', '2025-09-15 11:10:31'),
(46, 184, 'bbb5c805512f6a7a09acfa470d4fc75916ef7f26', '2025-09-15 11:11:58'),
(47, 185, '49e8fe6190b350c38b79b5aab2649c2af4feb5d9', '2025-09-15 11:32:21'),
(48, 186, 'a193266d0463aceb9a45544a44684990fc027c58', '2025-09-15 11:52:56'),
(49, 187, '2a4527c5a255cb4f4fb1e9db83638bde9bb1f540', '2025-09-15 11:55:42'),
(50, 188, 'bf6f8aeffb0850ef8e8031b7818a10a898c56b44', '2025-09-16 04:18:53'),
(51, 189, '8ab6d7e6d4191484dbb821213fefe6faa3ad86c6', '2025-09-16 04:21:26'),
(52, 190, 'd51651baaf73cc0b520bfe6231c0b62072c5dcbd', '2025-09-16 04:23:27'),
(53, 191, '8eefd588855c32780aa177e95c0a460d9553e21f', '2025-09-16 04:32:27'),
(54, 192, '26085b009728fb757b45bfc48306d9484a01864f', '2025-09-16 04:35:21'),
(55, 195, '5aaddd987f35437d503f1eac6debb6b429b8a4cf', '2025-09-16 04:40:41'),
(56, 197, 'aaca9aa28c621f5657fd6215078d2038c98cf253', '2025-09-16 05:08:27'),
(57, 196, '630edc8d5919a20214fdf1d2e51a6046', '2025-09-16 05:09:40'),
(58, 198, 'e25545c0eca70c9cc1db4d729b396a28', '2025-09-16 05:23:20'),
(59, 199, 'db27bbc8636d8d4db1c3eb59a55c65443a46b999', '2025-09-16 05:27:42'),
(60, 200, 'b6500c11868abeb74a3c8bacea886ae86bede2c1', '2025-09-16 05:33:37'),
(61, 201, 'bd710c13e81d736dcce9a5b9136578a5e1e47072', '2025-09-16 05:46:22'),
(62, 202, 'e51a78f048f21c645be734df3161c980e982cf4d', '2025-09-16 05:50:36'),
(63, 203, '324e3da85375fce1366208102f8fffd0923884e6', '2025-09-16 05:53:57'),
(64, 204, '93af96dec69aeae2f776ab059b5778557fd13380', '2025-09-16 06:17:47'),
(65, 205, '432647eda581a076ec2431ca0588bba82ee92d55', '2025-09-16 06:35:49'),
(66, 208, '1913804e3626242838d9a6572a35627901fd6949', '2025-09-16 09:43:44'),
(67, 209, '1e70659408dfe0429f08e50e90b68457b020823e', '2025-09-16 09:46:21'),
(68, 210, 'e6cfd1d116c1d1ac958c2a5cc17637e2', '2025-09-16 09:52:19'),
(69, 211, '044458c3a7c746fad4443a9a882d0a95', '2025-09-16 11:16:43'),
(70, 213, '915f08f1860ce91b053eaccb004ca681', '2025-09-17 07:21:22'),
(71, 214, 'b2b957cb0e3a4427bdb1bc53a033a0f0ae29235b', '2025-09-17 11:45:26'),
(72, 215, 'df8c1dcf4f2d5c012e494476a8ed8087', '2025-09-18 04:28:09'),
(73, 216, 'cc1fc10f81d568ad0e1d626ace1d4828f87f4142', '2025-09-18 04:40:40'),
(74, 217, 'cacdf7f0c7b5ea744e6624b29d452e225f784a8b', '2025-09-18 04:40:52'),
(75, 218, '241a4312e8b7d8a1baf29ed2e63b5afb', '2025-09-18 04:47:48'),
(76, 219, '825c57745fc71f82bb5e8381763b9fb1a1a5cf31', '2025-09-18 04:48:25'),
(77, 220, '5da8c71f49f90882476f3ea0346c96676786203d', '2025-09-18 04:51:35'),
(78, 221, 'f79ef19f4be8c632becfed163d5ce488e83e79bd', '2025-09-18 04:52:01'),
(79, 222, '6207ee88d3b678434d69aee9314c539ca2436a59', '2025-09-18 04:54:10'),
(80, 223, '20f6f020f14deefda38960dab2ba1eb2f6bc1c17', '2025-09-18 05:23:46'),
(81, 224, 'b8cb786396d14695085b0f13789f8dd2511d3434', '2025-09-18 05:25:57'),
(82, 225, '4defbe2881e0f68fde52b5b7f21670c4', '2025-09-18 06:08:38'),
(83, 226, '41c07df5465db2307613cf907a8545df', '2025-09-18 06:12:58'),
(84, 228, '14228caea4d26c688fe000e083282d0b', '2025-09-18 06:55:04'),
(85, 229, '408b8dcae7cf63e597ac6eba19690524', '2025-09-18 06:56:48'),
(86, 230, 'bb54a9009916442fcb76ffbc8e1ba044', '2025-09-18 07:00:52'),
(87, 231, '7515d235ace0612df8ab7ed9b8821678', '2025-09-18 07:16:05'),
(88, 232, 'cc5b9249dd4f347ac64f17c1c4fbe13f', '2025-09-18 07:19:11'),
(89, 233, 'd28729532f9e71fc0e1447cf855e6972', '2025-09-18 09:36:30'),
(90, 234, '07a2240934884de589a51f43ae05a8f6', '2025-09-18 10:48:07'),
(91, 235, '0c8ee8e7505a5fdd7dc32b7cea6a940b988ac981', '2025-09-18 11:53:14'),
(92, 236, 'ae6243ef601a27b31efac3e5da20ce7a', '2025-09-19 04:55:23');

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
(54, 208, 3, NULL, 'Razorpay', 'pay_RIEBOoaGLaEe10', 'order_RIEBCnIu8f4CKj', '890763fdc4421c3128a0be090dc7a4133438756c6a0f27f903eb0decb8cd2713', 3000.00, 'INR', 'CAPTURED', 'online', NULL, NULL, '2025-09-16 09:43:44', '2025-09-16 09:43:44'),
(56, 211, NULL, NULL, 'Razorpay', 'pay_RIFl1IG2aX3iZD', 'order_RIFkiPK3CqTVul', '61d5c71ad2daca0e8f61fffc7bd27cd6d431ed77d7ff77c277a4a3f73a2efde3', 100.00, 'INR', 'CAPTURED', 'online', NULL, NULL, '2025-09-16 11:16:08', '2025-09-16 11:16:08'),
(57, 214, 3, NULL, 'Razorpay', 'pay_RIen5ztgLoVmXf', 'order_RIemvsJhbIZvMD', 'ae847af69aeb77ccae103ce64f9b07ac76a508cbef25e5c49bb47990339198f6', 1000.00, 'INR', 'CAPTURED', 'online', NULL, NULL, '2025-09-17 11:45:26', '2025-09-17 11:45:26'),
(58, 231, NULL, NULL, 'Razorpay', 'pay_RIyjgtWrA2vNq3', 'order_RIyjSTR9CSpMZm', '83228a806979505c73b02ca2e4fb0834c71a90707bf6a11bdfea8da82079fc25', 1400.00, 'INR', 'CAPTURED', 'online', NULL, NULL, '2025-09-18 07:16:05', '2025-09-18 07:16:05'),
(59, 235, 9, NULL, 'Razorpay', 'pay_RJ3SRrhWcpykyb', 'order_RJ3SDzOGmEiI8P', 'ff6158620f5356bddaf0c38fdfab0eb30f6036896666c2d345bd7fb352eb5c73', 100.00, 'INR', 'CAPTURED', 'online', NULL, NULL, '2025-09-18 11:53:14', '2025-09-18 11:53:14');

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
(3, 'Sanjay Kumar Sheoran', '$2y$10$z7lBSp5NypMVF1S05ZvNeui70bCZceCut.xYsUwAGecNIoqck5DO6', '9728833428', 'rohitmechujaatji@gmail.com', '::1', 1, NULL, NULL, '2025-09-11 11:49:54'),
(9, 'Sanjay Kumasr Sheoran', '$2y$10$XZDmMU4pgCwyVyTJw9OGbOD9aiH9SDDBlKJF0E2LpIh2If9YbrZ7G', '1234567890', 'sjsheoran111@gmail.com', '::1', 1, NULL, NULL, '2025-09-16 09:48:25');

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
(24, 3, '75131cc6738fbac93111ed874139a55a2d27b6fcd95e0592a933c1e7bbcd489b', '1', '2025-09-15 07:44:55', '::1'),
(25, 3, '626cf9a80aadf40a5edb87ccd107cd597c7fa80389a1fd0d5f6121e6aeb8b32a', '1', '2025-09-16 04:20:22', '::1'),
(26, 3, '8dcf3c5748865e1de2753d38fd4dcd0458336b893f384e4a268afde7b215224e', '1', '2025-09-17 11:45:57', '::1');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `admin_activity_log`
--
ALTER TABLE `admin_activity_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=145;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=237;

--
-- AUTO_INCREMENT for table `buses`
--
ALTER TABLE `buses`
  MODIFY `bus_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `bus_categories`
--
ALTER TABLE `bus_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `bus_category_map`
--
ALTER TABLE `bus_category_map`
  MODIFY `map_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=98;

--
-- AUTO_INCREMENT for table `bus_images`
--
ALTER TABLE `bus_images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `cancellations`
--
ALTER TABLE `cancellations`
  MODIFY `cancellation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `cash_collections_log`
--
ALTER TABLE `cash_collections_log`
  MODIFY `collection_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=118;

--
-- AUTO_INCREMENT for table `charter_bookings`
--
ALTER TABLE `charter_bookings`
  MODIFY `charter_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `passengers`
--
ALTER TABLE `passengers`
  MODIFY `passenger_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=277;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `routes`
--
ALTER TABLE `routes`
  MODIFY `route_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `route_schedules`
--
ALTER TABLE `route_schedules`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT for table `route_staff_assignments`
--
ALTER TABLE `route_staff_assignments`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `route_stops`
--
ALTER TABLE `route_stops`
  MODIFY `stop_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=113;

--
-- AUTO_INCREMENT for table `seats`
--
ALTER TABLE `seats`
  MODIFY `seat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=478;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `staff_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `ticket_access_tokens`
--
ALTER TABLE `ticket_access_tokens`
  MODIFY `token_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users_login_token`
--
ALTER TABLE `users_login_token`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

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
