-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 12, 2025 at 12:39 PM
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
  `ip_address` varchar(110) DEFAULT NULL,
  `date_time` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `type`, `permissions`, `last_login_time`, `last_login_ip`, `session_token`, `name`, `mobile`, `email`, `password`, `password_salt`, `status`, `ip_address`, `date_time`) VALUES
(1, 'main_admin', '{\"all_access\": true}', '2025-09-12 10:46:56', '::1', '98869d95f0826841d8fe150d4782fb92d1c052a6e79e3445fde84f441f5354c6', 'dev', '8930000210', 'admin@gmail.com', '$2y$12$F5HnNj16GzvkVuojDu/9Re/IeDjwwH4.flwKS5hX5FluIrlOlexC6', '123456', '1', '::1', '2025-07-23 13:36:33'),
(5, 'employee', '[]', '2025-09-11 15:37:33', '::1', '0afcda5a831a1d8abab63240825fa6fd84fcc5633928342073761170ca46a5d4', 'SANJAY', '9876543212', 'rohit@gmail.com', '$2y$10$ujvyOF4Zy5C1ACdvfFF2i.FKiyr2Bbo6rIhVYiQU.jxnj748sqOUq', 'bcrypt', '1', NULL, '2025-09-09 11:46:29');

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
(87, 1, 'dev', 'login', '::1', '2025-09-12 05:16:56');

-- --------------------------------------------------------

--
-- Table structure for table `booked_seats`
--

CREATE TABLE `booked_seats` (
  `id` int(11) NOT NULL,
  `bus_id` int(11) NOT NULL,
  `route_id` int(11) NOT NULL,
  `seat_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `travel_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booked_seats`
--

INSERT INTO `booked_seats` (`id`, `bus_id`, `route_id`, `seat_id`, `booking_id`, `travel_date`) VALUES
(51, 12, 15, 301, 48, '2025-09-10'),
(52, 12, 14, 306, 49, '2025-09-13'),
(53, 12, 14, 307, 49, '2025-09-13'),
(54, 12, 15, 300, 50, '2025-09-10'),
(55, 12, 15, 306, 51, '2025-09-10'),
(56, 12, 15, 307, 53, '2025-09-10'),
(57, 12, 15, 308, 54, '2025-09-10'),
(58, 12, 15, 305, 55, '2025-09-10'),
(59, 12, 15, 310, 56, '2025-09-10'),
(60, 12, 15, 302, 56, '2025-09-10'),
(61, 12, 15, 299, 57, '2025-09-10'),
(62, 12, 15, 304, 57, '2025-09-10'),
(63, 12, 15, 317, 58, '2025-09-10'),
(64, 12, 15, 319, 59, '2025-09-10'),
(65, 12, 15, 300, 60, '2025-09-12'),
(66, 12, 15, 309, 61, '2025-09-10'),
(67, 12, 15, 313, 62, '2025-09-10'),
(68, 12, 15, 299, 63, '2025-09-12'),
(69, 12, 15, 304, 64, '2025-09-12'),
(70, 12, 15, 305, 65, '2025-09-12'),
(71, 12, 15, 301, 66, '2025-09-12'),
(72, 12, 15, 306, 67, '2025-09-12'),
(73, 12, 15, 307, 68, '2025-09-12'),
(74, 12, 15, 320, 69, '2025-09-12'),
(75, 12, 15, 308, 70, '2025-09-12'),
(76, 12, 15, 319, 71, '2025-09-12'),
(77, 12, 15, 310, 72, '2025-09-12'),
(78, 12, 15, 324, 73, '2025-09-12'),
(79, 12, 15, 309, 74, '2025-09-12'),
(80, 12, 15, 321, 75, '2025-09-12'),
(81, 12, 15, 311, 76, '2025-09-12'),
(82, 12, 15, 314, 77, '2025-09-12'),
(83, 12, 15, 316, 78, '2025-09-12'),
(84, 12, 15, 313, 79, '2025-09-12'),
(85, 12, 15, 315, 80, '2025-09-12'),
(86, 12, 15, 334, 81, '2025-09-12'),
(87, 12, 15, 312, 82, '2025-09-12'),
(88, 12, 15, 336, 83, '2025-09-12'),
(89, 12, 15, 323, 84, '2025-09-12'),
(90, 12, 15, 329, 85, '2025-09-12'),
(91, 12, 15, 302, 86, '2025-09-12'),
(92, 12, 15, 318, 87, '2025-09-12'),
(93, 12, 15, 322, 88, '2025-09-12'),
(94, 12, 15, 325, 89, '2025-09-12'),
(95, 12, 15, 317, 90, '2025-09-12'),
(96, 12, 15, 332, 91, '2025-09-12'),
(100, 12, 15, 330, 95, '2025-09-12'),
(101, 12, 15, 326, 96, '2025-09-12'),
(102, 12, 15, 327, 97, '2025-09-12'),
(109, 12, 15, 331, 104, '2025-09-12'),
(110, 12, 15, 333, 105, '2025-09-12'),
(111, 11, 12, 286, 106, '2025-09-15'),
(112, 12, 14, 300, 107, '2025-09-11'),
(113, 12, 15, 351, 108, '2025-09-12'),
(114, 12, 14, 308, 109, '2025-09-11'),
(115, 12, 15, 350, 110, '2025-09-12'),
(116, 11, 12, 294, 111, '2025-09-15'),
(117, 12, 15, 354, 112, '2025-09-12'),
(118, 12, 14, 299, 113, '2025-09-11'),
(120, 12, 14, 305, 115, '2025-09-11'),
(121, 12, 14, 304, 116, '2025-09-11'),
(122, 12, 14, 307, 117, '2025-09-11'),
(123, 12, 14, 320, 118, '2025-09-11'),
(124, 12, 14, 300, 119, '2025-09-12'),
(125, 12, 14, 301, 120, '2025-09-11'),
(126, 12, 14, 306, 121, '2025-09-11'),
(127, 12, 14, 309, 122, '2025-09-11'),
(128, 12, 14, 310, 123, '2025-09-11'),
(129, 12, 14, 311, 124, '2025-09-11'),
(130, 12, 14, 314, 125, '2025-09-11'),
(131, 12, 14, 313, 126, '2025-09-11'),
(132, 12, 14, 334, 127, '2025-09-11'),
(133, 12, 14, 299, 128, '2025-09-12'),
(134, 12, 14, 312, 129, '2025-09-11'),
(135, 12, 14, 336, 130, '2025-09-11'),
(136, 12, 14, 316, 131, '2025-09-11'),
(137, 12, 15, 308, 132, '2025-09-17'),
(138, 12, 15, 307, 133, '2025-09-17'),
(139, 12, 15, 300, 134, '2025-09-17'),
(140, 12, 15, 299, 135, '2025-09-17'),
(141, 12, 15, 305, 136, '2025-09-17'),
(142, 12, 15, 306, 137, '2025-09-17'),
(143, 12, 15, 314, 138, '2025-09-17'),
(144, 12, 14, 305, 139, '2025-09-12'),
(145, 12, 14, 308, 140, '2025-09-12'),
(146, 12, 15, 309, 141, '2025-09-17'),
(147, 12, 15, 310, 142, '2025-09-17'),
(148, 12, 15, 301, 143, '2025-09-17'),
(150, 11, 12, 287, 145, '2025-09-15'),
(152, 11, 12, 291, 147, '2025-09-15'),
(153, 12, 15, 311, 148, '2025-09-17');

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
(48, 'BPL600460533', 15, 12, NULL, 1, 'Bhiwani,hashi Gate', 'juiii', NULL, 'rohit@gmail.com', '8905288939', '2025-09-10', 20.00, 'PAID', NULL, 'CONFIRMED', '2025-09-10 07:22:30'),
(49, 'BPL051458717', 14, 12, NULL, 1, 'Dadri Bus Stand', 'Badhra Bus stand', NULL, 'sanjay@gmail.com', '9747823434', '2025-09-13', 140.00, 'PENDING', NULL, 'CONFIRMED', '2025-09-10 07:27:03'),
(50, 'BPL675347563', 15, 12, NULL, 1, 'Bhiwani,hashi Gate', 'Jeetpura,Bhiwani', NULL, 'rohit@gmail.com', '5435435435', '2025-09-10', 60.00, 'PENDING', NULL, 'CONFIRMED', '2025-09-10 08:10:32'),
(51, 'BPL826289427', 15, 12, NULL, 1, 'Bhiwani,hashi Gate', 'Jeetpura,Bhiwani', NULL, 'rohit@gmail.com', '5435435435', '2025-09-10', 60.00, 'PENDING', NULL, 'PENDING', '2025-09-10 08:11:25'),
(53, 'BPL068555884', 15, 12, NULL, 1, 'Bhiwani,hashi Gate', 'Jeetpura,Bhiwani', NULL, 'rohit@gmail.com', '5435435435', '2025-09-10', 30.00, 'PAID', NULL, 'CONFIRMED', '2025-09-10 09:43:22'),
(54, 'BPL401795140', 15, 12, NULL, 1, 'Bhiwani,hashi Gate', 'Badhra,Haryana', NULL, 'rohit@gmail.com', '5435435435', '2025-09-10', 80.00, 'PENDING', NULL, 'PENDING', '2025-09-10 09:58:19'),
(55, 'BPL052567021', 15, 12, NULL, 1, 'Bhiwani,hashi Gate', 'Jeetpura,Bhiwani', NULL, 'rohit@gmail.com', '5435435435', '2025-09-10', 60.00, 'PENDING', NULL, 'PENDING', '2025-09-10 10:04:28'),
(56, 'BPL612436558', 15, 12, NULL, 1, 'Bhiwani,hashi Gate', 'Jeetpura,Bhiwani', NULL, 'rohit@gmail.com', '5435435435', '2025-09-10', 140.00, 'PENDING', NULL, 'PENDING', '2025-09-10 10:18:14'),
(57, 'BPL894309598', 15, 12, NULL, 1, 'Bhiwani,hashi Gate', 'Jeetpura,Bhiwani', NULL, 'rohit@gmail.com', '5435435435', '2025-09-10', 140.00, 'PENDING', NULL, 'CONFIRMED', '2025-09-10 10:51:36'),
(58, 'BPL210465171', 15, 12, NULL, 1, 'Bhiwani,hashi Gate', 'Jeetpura,Bhiwani', NULL, 'rohit@gmail.com', '5435435435', '2025-09-10', 80.00, 'PENDING', NULL, 'CONFIRMED', '2025-09-10 11:35:27'),
(59, 'BPL583685834', 15, 12, NULL, 1, 'juiii', 'Badhra,Haryana', NULL, '', '', '2025-09-10', 60.00, 'PENDING', NULL, 'CONFIRMED', '2025-09-10 11:40:57'),
(60, 'BPL531264476', 15, 12, NULL, 1, 'Bhiwani,hashi Gate', 'Jeetpura,Bhiwani', NULL, 'rohit@gmail.com', '5435435435', '2025-09-12', 60.00, 'PENDING', NULL, 'CONFIRMED', '2025-09-10 11:58:38'),
(61, 'BPL848524921', 15, 12, NULL, 1, 'Bhiwani,hashi Gate', 'Jeetpura,Bhiwani', NULL, 'rohit@gmail.com', '5435435435', '2025-09-10', 60.00, 'PENDING', NULL, 'PENDING', '2025-09-10 12:29:16'),
(62, 'BPL068750391', 15, 12, NULL, 1, 'Bhiwani,hashi Gate', 'juiii', NULL, 'rohit@gmail.com', '4343244', '2025-09-10', 20.00, 'PENDING', NULL, 'PENDING', '2025-09-10 12:31:48'),
(63, 'BPL837410459', 15, 12, NULL, 1, 'juiii', 'Jeetpura,Bhiwani', NULL, 'rohit@gmail.com', '5435435435', '2025-09-12', 10.00, 'PENDING', NULL, 'PENDING', '2025-09-11 04:46:37'),
(64, 'BPL120859736', 15, 12, NULL, 1, 'Bhiwani,hashi Gate', 'juiii', NULL, '', '', '2025-09-12', 60.00, 'PENDING', NULL, 'PENDING', '2025-09-11 04:55:08'),
(65, 'BPL383358462', 15, 12, NULL, 1, 'Bhiwani,hashi Gate', 'Jeetpura,Bhiwani', NULL, 'rohit@gmail.com', '5435435435', '2025-09-12', 60.00, 'PENDING', NULL, 'PENDING', '2025-09-11 04:55:48'),
(66, 'BPL656359547', 15, 12, NULL, 1, 'Bhiwani,hashi Gate', 'juiii', NULL, 'rohit@gmail.com', '5435435435', '2025-09-12', 20.00, 'PENDING', NULL, 'PENDING', '2025-09-11 04:58:31'),
(67, 'BPL657788185', 15, 12, NULL, 1, 'Bhiwani,hashi Gate', 'Jeetpura,Bhiwani', NULL, 'rohit@gmail.com', '5435435435', '2025-09-12', 60.00, 'PENDING', NULL, 'PENDING', '2025-09-11 05:01:18'),
(68, 'BPL101443255', 15, 12, NULL, 1, 'Bhiwani,hashi Gate', 'Jeetpura,Bhiwani', NULL, 'rohit@gmail.com', '5435435435', '2025-09-12', 30.00, 'PENDING', NULL, 'PENDING', '2025-09-11 05:02:11'),
(69, 'BPL107749746', 15, 12, NULL, 1, 'Bhiwani,hashi Gate', 'Jeetpura,Bhiwani', NULL, 'rohit@gmail.com', '5435435435', '2025-09-12', 80.00, 'PENDING', NULL, 'PENDING', '2025-09-11 05:06:10'),
(70, 'BPL691790413', 15, 12, NULL, 1, 'Bhiwani,hashi Gate', 'Jeetpura,Bhiwani', NULL, '', '', '2025-09-12', 60.00, 'PENDING', NULL, 'PENDING', '2025-09-11 05:08:17'),
(71, 'BPL575391164', 15, 12, NULL, 1, 'juiii', 'Jeetpura,Bhiwani', NULL, 'rohit@gmail.com', '5435435435', '2025-09-12', 20.00, 'PENDING', NULL, 'PENDING', '2025-09-11 05:11:08'),
(72, 'BPL722261040', 15, 12, NULL, 1, 'Bhiwani,hashi Gate', 'juiii', NULL, 'rohit@gmail.com', '5435435435', '2025-09-12', 50.00, 'PENDING', NULL, 'PENDING', '2025-09-11 05:19:00'),
(73, 'BPL615494228', 15, 12, NULL, 1, 'juiii', 'Badhra,Haryana', NULL, 'rohit@gmail.com', '5435435435', '2025-09-12', 60.00, 'PENDING', NULL, 'PENDING', '2025-09-11 05:20:19'),
(74, 'BPL328396651', 15, 12, NULL, 1, 'Bhiwani,hashi Gate', 'Badhra,Haryana', NULL, 'rohit@gmail.com', '5435435435', '2025-09-12', 80.00, 'PENDING', NULL, 'PENDING', '2025-09-11 05:21:15'),
(75, 'BPL533303947', 15, 12, NULL, 1, 'Bhiwani,hashi Gate', 'Jeetpura,Bhiwani', NULL, 'rohit@gmail.com', '5435435435', '2025-09-12', 80.00, 'PENDING', NULL, 'PENDING', '2025-09-11 05:23:40'),
(76, 'BPL747402085', 15, 12, NULL, 1, 'Bhiwani,hashi Gate', 'Jeetpura,Bhiwani', NULL, 'rohit@gmail.com', '5435435435', '2025-09-12', 60.00, 'PENDING', NULL, 'PENDING', '2025-09-11 05:24:33'),
(77, 'BPL908268722', 15, 12, NULL, 1, 'Bhiwani,hashi Gate', 'Badhra,Haryana', NULL, 'rohit@gmail.com', '5435435435', '2025-09-12', 80.00, 'PENDING', NULL, 'PENDING', '2025-09-11 05:35:10'),
(78, 'BPL238856434', 15, 12, NULL, 1, 'Bhiwani,hashi Gate', 'juiii', NULL, 'rohit@gmail.com', '5435435435', '2025-09-12', 50.00, 'PENDING', NULL, 'PENDING', '2025-09-11 05:40:44'),
(79, 'BPL257349627', 15, 12, NULL, 1, 'Bhiwani,hashi Gate', 'juiii', NULL, 'rohit@gmail.com', '5435435435', '2025-09-12', 20.00, 'PENDING', NULL, 'PENDING', '2025-09-11 05:50:23'),
(80, 'BPL845537697', 15, 12, NULL, 1, 'Bhiwani,hashi Gate', 'Badhra,Haryana', NULL, 'rohit@gmail.com', '5435435435', '2025-09-12', 40.00, 'PENDING', NULL, 'PENDING', '2025-09-11 05:53:45'),
(81, 'BPL877555537', 15, 12, NULL, 1, 'Bhiwani,hashi Gate', 'Jeetpura,Bhiwani', NULL, 'rohit@gmail.com', '5435435435', '2025-09-12', 30.00, 'PENDING', NULL, 'PENDING', '2025-09-11 05:56:19'),
(82, 'BPL054967275', 15, 12, NULL, 1, 'Jeetpura,Bhiwani', 'Badhra,Haryana', NULL, 'rohit@gmail.com', '4343244', '2025-09-12', 10.00, 'PENDING', NULL, 'PENDING', '2025-09-11 05:57:47'),
(83, 'BPL823468202', 15, 12, NULL, 1, 'Bhiwani,hashi Gate', 'juiii', NULL, 'rohit@gmail.com', '8905288939', '2025-09-12', 20.00, 'PENDING', NULL, 'PENDING', '2025-09-11 06:00:58'),
(84, 'BPL635747390', 15, 12, NULL, 1, 'Bhiwani,hashi Gate', 'Jeetpura,Bhiwani', NULL, 'rohitmechu', '4343244', '2025-09-12', 80.00, 'PAID', NULL, 'CONFIRMED', '2025-09-11 06:09:36'),
(85, 'BPL016961199', 15, 12, NULL, 1, 'Jeetpura,Bhiwani', 'Badhra,Haryana', NULL, 'rohit@gmail.com', '8905288939', '2025-09-12', 40.00, 'PAID', NULL, 'CONFIRMED', '2025-09-11 06:26:19'),
(86, 'BPL475125214', 15, 12, NULL, 1, 'Bhiwani,hashi Gate', 'Jeetpura,Bhiwani', NULL, 'rohit@gmail.com', '8905288939', '2025-09-12', 80.00, 'PAID', NULL, 'CONFIRMED', '2025-09-11 06:38:28'),
(87, 'BPL852627791', 15, 12, NULL, 1, 'Jeetpura,Bhiwani', 'Badhra,Haryana', NULL, 'rohit@gmail.com', '8905288939', '2025-09-12', 40.00, 'PENDING', NULL, 'CONFIRMED', '2025-09-11 06:40:29'),
(88, 'BPL457405741', 15, 12, NULL, 1, 'Bhiwani,hashi Gate', 'Jeetpura,Bhiwani', NULL, 'rohit@gmail.com', '8905288939', '2025-09-12', 80.00, 'PAID', NULL, 'CONFIRMED', '2025-09-11 06:53:01'),
(89, 'BPL921105378', 15, 12, NULL, 1, 'Bhiwani,hashi Gate', 'juiii', NULL, 'rohit@gmail.com', '8905288939', '2025-09-12', 60.00, 'PAID', NULL, 'CONFIRMED', '2025-09-11 07:27:51'),
(90, 'BPL195945003', 15, 12, NULL, 1, 'juiii', 'Badhra,Haryana', NULL, 'rohit@gmail.com', '8905288939', '2025-09-12', 60.00, 'PENDING', NULL, 'CONFIRMED', '2025-09-11 07:32:40'),
(91, 'BPL598651601', 15, 12, NULL, 1, 'Bhiwani,hashi Gate', 'Jeetpura,Bhiwani', NULL, 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-12', 80.00, 'PENDING', NULL, 'CONFIRMED', '2025-09-11 07:59:02'),
(95, 'BPL482914672', 15, 12, NULL, 1, 'juiii', 'Jeetpura,Bhiwani', NULL, 'rohit@gmail.com', '8905288939', '2025-09-12', 20.00, 'PENDING', NULL, 'CONFIRMED', '2025-09-11 08:00:13'),
(96, 'BPL210195362', 15, 12, NULL, 1, 'Jeetpura,Bhiwani', 'Badhra,Haryana', NULL, 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-12', 40.00, 'PENDING', NULL, 'CONFIRMED', '2025-09-11 08:01:38'),
(97, 'BPL315299072', 15, 12, NULL, 1, 'Bhiwani,hashi Gate', 'juiii', NULL, 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-12', 60.00, 'PENDING', NULL, 'CONFIRMED', '2025-09-11 08:08:34'),
(104, 'BPL197063163', 15, 12, NULL, 1, 'Bhiwani,hashi Gate', 'Jeetpura,Bhiwani', NULL, 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-12', 80.00, 'PENDING', NULL, 'CONFIRMED', '2025-09-11 09:37:35'),
(105, 'BPL610457328', 15, 12, NULL, 1, 'Bhiwani,hashi Gate', 'Jeetpura,Bhiwani', NULL, 'rohit@gmail.com', '8905288939', '2025-09-12', 80.00, 'PENDING', NULL, 'PENDING', '2025-09-11 09:42:47'),
(106, 'BPL998402750', 12, 11, NULL, 1, 'Rohtak, Purana Bus Stand', 'Pilani', NULL, 'rohit@gmail.com', '8905288939', '2025-09-15', 200.00, 'PENDING', NULL, 'CONFIRMED', '2025-09-11 09:43:47'),
(107, 'BPL435013370', 14, 12, NULL, 1, 'Atela', 'Badhra Bus stand', NULL, 'rohit@gmail.com', '8905288939', '2025-09-11', 20.00, 'PAID', NULL, 'CONFIRMED', '2025-09-11 10:31:01'),
(108, 'BPL295352398', 15, 12, NULL, 1, 'Bhiwani,hashi Gate', 'Jeetpura,Bhiwani', NULL, 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-12', 30.00, 'PENDING', NULL, 'CONFIRMED', '2025-09-11 10:49:24'),
(109, 'BPL050922578', 14, 12, NULL, 1, 'Atela', 'Loharu Bus stand', NULL, 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-11', 40.00, 'PENDING', NULL, 'CONFIRMED', '2025-09-11 10:51:21'),
(110, 'BPL805696301', 15, 12, NULL, 1, 'juiii', 'Badhra,Haryana', NULL, 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-12', 30.00, 'PAID', NULL, 'CONFIRMED', '2025-09-11 10:52:40'),
(111, 'BPL265760228', 12, 11, NULL, 1, 'Rohtak, Purana Bus Stand', 'Loharu', NULL, '', '8905288939', '2025-09-15', 100.00, 'PENDING', NULL, 'CONFIRMED', '2025-09-11 10:55:11'),
(112, 'BPL499219376', 15, 12, NULL, 1, 'juiii', 'Jeetpura,Bhiwani', NULL, 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-12', 10.00, 'PENDING', NULL, 'CONFIRMED', '2025-09-11 10:58:44'),
(113, 'BPL106153918', 14, 12, NULL, 1, 'Dadri Bus Stand', 'Atela', NULL, 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-11', 60.00, 'PENDING', NULL, 'CONFIRMED', '2025-09-11 11:15:43'),
(115, 'BPL507207190', 14, 12, NULL, 1, 'Dadri Bus Stand', 'Badhra Bus stand', NULL, 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-11', 80.00, 'PENDING', NULL, 'CONFIRMED', '2025-09-11 11:19:10'),
(116, 'BPL047443865', 14, 12, NULL, 1, 'Dadri Bus Stand', 'Loharu Bus stand', NULL, 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-11', 140.00, 'PAID', NULL, 'CONFIRMED', '2025-09-11 11:20:24'),
(117, 'BPL724862730', 14, 12, NULL, 1, 'Atela', 'Loharu Bus stand', NULL, 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-11', 40.00, 'PAID', NULL, 'CONFIRMED', '2025-09-11 11:22:42'),
(118, 'BPL339707967', 14, 12, NULL, 1, 'Dadri Bus Stand', 'Loharu Bus stand', NULL, 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-11', 140.00, 'PAID', NULL, 'CONFIRMED', '2025-09-11 11:28:04'),
(119, 'BPL883942014', 14, 12, NULL, 1, 'Atela', 'Badhra Bus stand', NULL, 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-12', 20.00, 'PENDING', NULL, 'CONFIRMED', '2025-09-11 11:33:05'),
(120, 'BPL650482215', 14, 12, NULL, 1, 'Dadri Bus Stand', 'Badhra Bus stand', NULL, 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-11', 60.00, 'PENDING', NULL, 'CONFIRMED', '2025-09-11 11:40:04'),
(121, 'BPL566093287', 14, 12, NULL, 1, 'Dadri Bus Stand', 'Badhra Bus stand', NULL, 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-11', 80.00, 'PENDING', NULL, 'CONFIRMED', '2025-09-11 11:47:45'),
(122, 'BPL280261323', 14, 12, NULL, 1, 'Dadri Bus Stand', 'Badhra Bus stand', NULL, 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-11', 80.00, 'PAID', NULL, 'CONFIRMED', '2025-09-11 11:49:03'),
(123, 'BPL888614300', 14, 12, NULL, 1, 'Dadri Bus Stand', 'Atela', NULL, 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-11', 60.00, 'PENDING', NULL, 'CONFIRMED', '2025-09-11 11:50:10'),
(124, 'BPL562834393', 14, 12, NULL, 1, 'Atela', 'Badhra Bus stand', NULL, 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-11', 20.00, 'PENDING', NULL, 'CONFIRMED', '2025-09-11 11:58:03'),
(125, 'BPL692804473', 14, 12, NULL, 1, 'Dadri Bus Stand', 'Badhra Bus stand', NULL, 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-11', 80.00, 'PENDING', NULL, 'CONFIRMED', '2025-09-11 12:06:40'),
(126, 'BPL063402211', 14, 12, NULL, 1, 'Dadri Bus Stand', 'Badhra Bus stand', NULL, 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-11', 60.00, 'PENDING', NULL, 'CONFIRMED', '2025-09-11 12:09:21'),
(127, 'BPL957737881', 14, 12, NULL, 1, 'Dadri Bus Stand', 'Badhra Bus stand', NULL, 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-11', 60.00, 'PENDING', NULL, 'CONFIRMED', '2025-09-11 12:12:16'),
(128, 'BPL963502802', 14, 12, NULL, 1, 'Dadri Bus Stand', 'Badhra Bus stand', NULL, 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-12', 80.00, 'PENDING', NULL, 'CONFIRMED', '2025-09-11 12:14:12'),
(129, 'BPL554667854', 14, 12, NULL, 1, 'Dadri Bus Stand', 'Atela', NULL, 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-11', 40.00, 'PAID', NULL, 'CONFIRMED', '2025-09-11 12:17:09'),
(130, 'BPL854022429', 14, 12, NULL, 1, 'Atela', 'Loharu Bus stand', NULL, 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-11', 40.00, 'PAID', NULL, 'CONFIRMED', '2025-09-11 12:18:20'),
(131, 'BPL108920255', 14, 12, NULL, 1, 'Badhra Bus stand', 'Loharu Bus stand', NULL, 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-11', 20.00, 'PAID', NULL, 'CONFIRMED', '2025-09-11 12:19:38'),
(132, 'BPL901527225', 15, 12, NULL, 1, 'juiii', 'Badhra,Haryana', NULL, 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-17', 30.00, 'PAID', NULL, 'CONFIRMED', '2025-09-12 04:20:19'),
(133, 'BPL016896503', 15, 12, NULL, 1, 'Bhiwani,hashi Gate', 'Jeetpura,Bhiwani', NULL, 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-17', 30.00, 'PENDING', NULL, 'PENDING', '2025-09-12 04:33:58'),
(134, 'BPL321688850', 15, 12, NULL, 1, 'Bhiwani,hashi Gate', 'Jeetpura,Bhiwani', NULL, 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-17', 60.00, 'PENDING', NULL, 'PENDING', '2025-09-12 04:36:17'),
(135, 'BPL673688029', 15, 12, NULL, 1, 'juiii', 'Badhra,Haryana', NULL, 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-17', 30.00, 'PAID', NULL, 'CONFIRMED', '2025-09-12 04:39:38'),
(136, 'BPL245228126', 15, 12, NULL, 1, 'juiii', 'Badhra,Haryana', NULL, 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-17', 30.00, 'PENDING', NULL, 'PENDING', '2025-09-12 04:41:12'),
(137, 'BPL378705160', 15, 12, NULL, 1, 'juiii', 'Badhra,Haryana', NULL, 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-17', 30.00, 'PENDING', NULL, 'PENDING', '2025-09-12 04:42:23'),
(138, 'BPL239047690', 15, 12, NULL, 1, 'juiii', 'Badhra,Haryana', NULL, 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-17', 30.00, 'PAID', NULL, 'CONFIRMED', '2025-09-12 04:44:53'),
(139, 'BPL402387853', 14, 12, NULL, 1, 'Dadri Bus Stand', 'Badhra Bus stand', NULL, 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-12', 80.00, 'PAID', NULL, 'CONFIRMED', '2025-09-12 04:46:11'),
(140, 'BPL053708947', 14, 12, NULL, 1, 'Atela', 'Badhra Bus stand', NULL, 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-12', 20.00, 'PAID', NULL, 'CONFIRMED', '2025-09-12 04:51:39'),
(141, 'BPL365571130', 15, 12, NULL, 1, 'Bhiwani,hashi Gate', 'Jeetpura,Bhiwani', NULL, '', '', '2025-09-17', 60.00, 'PAID', NULL, 'CONFIRMED', '2025-09-12 04:55:20'),
(142, 'BPL170565041', 15, 12, NULL, 1, 'Bhiwani,hashi Gate', 'Jeetpura,Bhiwani', NULL, '', '8905288939', '2025-09-17', 60.00, 'PENDING', NULL, 'CONFIRMED', '2025-09-12 05:19:01'),
(143, 'BPL180229416', 15, 12, NULL, 1, 'Bhiwani,hashi Gate', 'Jeetpura,Bhiwani', NULL, '', '', '2025-09-17', 30.00, 'PENDING', NULL, 'CONFIRMED', '2025-09-12 05:20:35'),
(145, 'BPL980884888', 12, 11, NULL, 1, 'Delhi, kasmiri Gate', 'Loharu', NULL, 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-15', 400.00, 'PENDING', NULL, 'CONFIRMED', '2025-09-12 05:21:18'),
(147, 'BPL110319891', 12, 11, NULL, 1, 'Delhi, kasmiri Gate', 'Loharu', NULL, 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-15', 400.00, 'PAID', NULL, 'CONFIRMED', '2025-09-12 05:22:14'),
(148, 'BPL259220513', 15, 12, NULL, 1, 'Bhiwani,hashi Gate', 'juiii', NULL, 'devdharm09@gmail.com', '', '2025-09-17', 50.00, 'PAID', NULL, 'CONFIRMED', '2025-09-12 07:19:28');

-- --------------------------------------------------------

--
-- Table structure for table `buses`
--

CREATE TABLE `buses` (
  `bus_id` int(11) NOT NULL,
  `bus_name` varchar(255) NOT NULL,
  `registration_number` varchar(100) NOT NULL,
  `operator_id` int(11) DEFAULT NULL,
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

INSERT INTO `buses` (`bus_id`, `bus_name`, `registration_number`, `operator_id`, `bus_type`, `total_seats`, `seater_seats`, `sleeper_seats`, `amenities`, `description`, `status`, `created_at`, `updated_at`) VALUES
(11, 'Bus no 2', 'HR 41 B 3453', 2, 'AC Seater', 0, 0, 0, NULL, 'gfdgf', 'Active', '2025-09-08 15:23:02', '2025-09-10 12:38:23'),
(12, 'Bus no 1', 'HR 19 B 6566', 2, 'Non-AC Seater', 0, 0, 0, NULL, 'd', 'Active', '2025-09-08 15:25:30', '2025-09-10 12:40:34'),
(18, 'Hr 323jcdfjkdre', 'HR 61 B 291713', NULL, 'Non-AC Seater', 0, 0, 0, NULL, 'dfdfdf', 'Active', '2025-09-12 13:14:48', '2025-09-12 13:14:48');

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
(1, 'Luxury', 'Active', '2025-09-03 11:43:00'),
(2, 'Expresss', 'Active', '2025-09-03 11:43:00'),
(3, 'Local', 'Active', '2025-09-03 11:43:00'),
(4, 'AC', 'Active', '2025-09-03 11:43:00'),
(5, 'fds', 'Active', '2025-09-03 12:00:38');

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
(42, 11, 4),
(43, 11, 2),
(44, 11, 1),
(48, 12, 4),
(49, 12, 2),
(50, 12, 3),
(59, 18, 4),
(60, 18, 2);

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
(9, 11, 'bus_11_1757325182_68bea77e43ed6.jpg', '2025-09-08 09:53:02'),
(10, 11, 'bus_11_1757325182_68bea77e44194.jpg', '2025-09-08 09:53:02'),
(11, 12, 'bus_12_1757325330_68bea81293beb.jpg', '2025-09-08 09:55:30'),
(12, 12, 'bus_12_1757325330_68bea81293ec2.jpg', '2025-09-08 09:55:30'),
(25, 18, 'bus_18_1757663088_68c3cf70c82fc.jpg', '2025-09-12 07:44:48'),
(26, 18, 'bus_18_1757663088_68c3cf70c878c.jpg', '2025-09-12 07:44:48');

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
(17, 48, 20.00, 1, 1, '2025-09-10 11:31:08'),
(18, 49, 140.00, 1, 1, '2025-09-10 11:31:08'),
(19, 50, 60.00, 1, 1, '2025-09-10 11:31:08'),
(20, 51, 60.00, 1, 1, '2025-09-10 11:31:08'),
(21, 53, 30.00, 1, 1, '2025-09-10 11:31:08'),
(22, 54, 80.00, 1, 1, '2025-09-10 11:31:08'),
(23, 55, 60.00, 1, 1, '2025-09-10 11:31:08'),
(24, 56, 140.00, 1, 1, '2025-09-10 11:31:08'),
(25, 57, 140.00, 1, 1, '2025-09-10 11:31:08'),
(26, 58, 80.00, 1, 1, '2025-09-10 11:39:23'),
(27, 59, 60.00, 1, 1, '2025-09-10 12:00:01'),
(28, 60, 60.00, 1, 1, '2025-09-10 12:00:01'),
(29, 61, 60.00, 1, 1, '2025-09-11 06:27:28'),
(30, 62, 20.00, 1, 1, '2025-09-11 06:27:28'),
(31, 63, 10.00, 1, 1, '2025-09-11 06:27:28'),
(32, 64, 60.00, 1, 1, '2025-09-11 06:27:28'),
(33, 65, 60.00, 1, 1, '2025-09-11 06:27:28'),
(34, 66, 20.00, 1, 1, '2025-09-11 06:27:28'),
(35, 67, 60.00, 1, 1, '2025-09-11 06:27:28'),
(36, 68, 30.00, 1, 1, '2025-09-11 06:27:28'),
(37, 69, 80.00, 1, 1, '2025-09-11 06:27:28'),
(38, 70, 60.00, 1, 1, '2025-09-11 06:27:28'),
(39, 71, 20.00, 1, 1, '2025-09-11 06:27:28'),
(40, 72, 50.00, 1, 1, '2025-09-11 06:27:28'),
(41, 73, 60.00, 1, 1, '2025-09-11 06:27:28'),
(42, 74, 80.00, 1, 1, '2025-09-11 06:27:28'),
(43, 75, 80.00, 1, 1, '2025-09-11 06:27:28'),
(44, 76, 60.00, 1, 1, '2025-09-11 06:27:28'),
(45, 77, 80.00, 1, 1, '2025-09-11 06:27:28'),
(46, 78, 50.00, 1, 1, '2025-09-11 06:27:28'),
(47, 79, 20.00, 1, 1, '2025-09-11 06:27:28'),
(48, 80, 40.00, 1, 1, '2025-09-11 06:27:28'),
(49, 81, 30.00, 1, 1, '2025-09-11 06:27:28'),
(50, 82, 10.00, 1, 1, '2025-09-11 06:27:28'),
(51, 83, 20.00, 1, 1, '2025-09-11 06:27:28');

-- --------------------------------------------------------

--
-- Table structure for table `operators`
--

CREATE TABLE `operators` (
  `operator_id` int(11) NOT NULL,
  `operator_name` varchar(255) NOT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `contact_phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `operators`
--

INSERT INTO `operators` (`operator_id`, `operator_name`, `contact_person`, `contact_email`, `contact_phone`, `address`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Sharma Travels', 'Rahul Sharma', '32@gmail.com', '32342432', 'refdfsd', 'Active', '2025-09-02 15:32:55', '2025-09-10 10:18:54'),
(2, 'Shanti Express', 'Priya Singh', '4342342@GMAIL.COM', '32342432', '34534', 'Active', '2025-09-02 15:32:55', '2025-09-06 11:58:19'),
(3, 'dev sheoran', 'Amit Kumar', '32@gmail.com', '32342432', 'ramraradsd', 'Active', '2025-09-02 15:32:55', '2025-09-11 16:02:54');

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
  `passenger_mobile` varchar(20) NOT NULL,
  `passenger_age` int(3) DEFAULT NULL,
  `passenger_gender` enum('MALE','FEMALE','OTHER') NOT NULL,
  `fare` decimal(10,2) NOT NULL,
  `passenger_status` enum('CONFIRMED','CANCELLED') NOT NULL DEFAULT 'CONFIRMED'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `passengers`
--

INSERT INTO `passengers` (`passenger_id`, `booking_id`, `seat_id`, `seat_code`, `passenger_name`, `passenger_mobile`, `passenger_age`, `passenger_gender`, `fare`, `passenger_status`) VALUES
(51, 48, 301, 'LS1', 'Rohit Choudhary', '', 24, 'MALE', 20.00, 'CONFIRMED'),
(52, 49, 306, 'LP4', 'Sanjay Sheoran', '', 23, 'MALE', 80.00, 'CONFIRMED'),
(53, 49, 307, 'LS2', 'Dev Sheoran', '', 22, 'MALE', 60.00, 'CONFIRMED'),
(54, 50, 300, 'LP2', '12', '', 12, 'MALE', 60.00, 'CONFIRMED'),
(55, 51, 306, 'LP4', 'Sanjay Sheoran', '', 22, 'MALE', 60.00, 'CONFIRMED'),
(56, 53, 307, 'LS2', 'Dev Sheoran', '', 22, 'MALE', 30.00, 'CONFIRMED'),
(57, 54, 308, 'LP5', 'fdf', '', 33, 'MALE', 80.00, 'CONFIRMED'),
(58, 55, 305, 'LP3', '23', '', 23, 'MALE', 60.00, 'CONFIRMED'),
(59, 56, 310, 'LP7', 'fsdfsd', '', 34, 'MALE', 60.00, 'CONFIRMED'),
(60, 56, 302, 'UP1', 'sdff', '', 34, 'MALE', 80.00, 'CONFIRMED'),
(61, 57, 299, 'LP1', 'rfdfsd', '', 34, 'MALE', 60.00, 'CONFIRMED'),
(62, 57, 304, 'UP2', '34343', '', 34, 'MALE', 80.00, 'CONFIRMED'),
(63, 58, 317, 'UP3', 'rohit', '', 35, 'MALE', 80.00, 'CONFIRMED'),
(64, 59, 319, 'UP5', '23', '', 23, 'MALE', 60.00, 'CONFIRMED'),
(65, 60, 300, 'LP2', '12', '', 23, 'MALE', 60.00, 'CONFIRMED'),
(66, 61, 309, 'LP6', 'fdfs', '', 22, 'MALE', 60.00, 'CONFIRMED'),
(67, 62, 313, 'LS4', 'fdf', '', 33, 'MALE', 20.00, 'CONFIRMED'),
(68, 63, 299, 'LP1', 'rfdfsd', '', 23, 'MALE', 10.00, 'CONFIRMED'),
(69, 64, 304, 'UP2', '34343', '', 23, 'MALE', 60.00, 'CONFIRMED'),
(70, 65, 305, 'LP3', '23', '', 22, 'MALE', 60.00, 'CONFIRMED'),
(71, 66, 301, 'LS1', 'Rohit Choudhary', '', 34, 'MALE', 20.00, 'CONFIRMED'),
(72, 67, 306, 'LP4', 'Sanjay Sheoran', '', 22, 'MALE', 60.00, 'CONFIRMED'),
(73, 68, 307, 'LS2', 'Dev Sheoran', '', 22, 'MALE', 30.00, 'CONFIRMED'),
(74, 69, 320, 'UP6', '23323', '', 32, 'MALE', 80.00, 'CONFIRMED'),
(75, 70, 308, 'LP5', 'fdf', '', 23, 'MALE', 60.00, 'CONFIRMED'),
(76, 71, 319, 'UP5', '23', '', 23, 'MALE', 20.00, 'CONFIRMED'),
(77, 72, 310, 'LP7', '23', '', 22, 'MALE', 50.00, 'CONFIRMED'),
(78, 73, 324, 'UP10', 'rohit', '', 22, 'MALE', 60.00, 'CONFIRMED'),
(79, 74, 309, 'LP6', '23', '', 23, 'MALE', 80.00, 'CONFIRMED'),
(80, 75, 321, 'UP7', 'fdfdf', '', 323, 'MALE', 80.00, 'CONFIRMED'),
(81, 76, 311, 'LP8', '23', '', 23, 'MALE', 60.00, 'CONFIRMED'),
(82, 77, 314, 'LP9', '23', '43', 23, 'MALE', 80.00, 'CONFIRMED'),
(83, 78, 316, 'LP10', 'fdfd', '', 34, 'MALE', 50.00, 'CONFIRMED'),
(84, 79, 313, 'LS4', 'fdsf', '', 34, 'MALE', 20.00, 'CONFIRMED'),
(85, 80, 315, 'LS5', 'fsdfd', '343434', 34, 'MALE', 40.00, 'CONFIRMED'),
(86, 81, 334, 'LS6', 'rer', '', 34, 'MALE', 30.00, 'CONFIRMED'),
(87, 82, 312, 'LS3', '232', '', 23, 'MALE', 10.00, 'CONFIRMED'),
(88, 83, 336, 'LS7', 'fdfdf', '', 32, 'MALE', 20.00, 'CONFIRMED'),
(89, 84, 323, 'UP9', 'ffds34', '', 44, 'MALE', 80.00, 'CONFIRMED'),
(90, 85, 329, 'UP14', 'errer', '', 34, 'MALE', 40.00, 'CONFIRMED'),
(91, 86, 302, 'UP1', 'sdff', '', 23, 'MALE', 80.00, 'CONFIRMED'),
(92, 87, 318, 'UP4', 'ghfgf', '', 45, 'MALE', 40.00, 'CONFIRMED'),
(93, 88, 322, 'UP8', '23233', '', 23, 'MALE', 80.00, 'CONFIRMED'),
(94, 89, 325, 'UP11', 'fddsfd', '', 22, 'MALE', 60.00, 'CONFIRMED'),
(95, 90, 317, 'UP3', 'rohit', '', 12, 'MALE', 60.00, 'CONFIRMED'),
(96, 91, 332, 'UP17', 'rohit', '', 23, 'MALE', 80.00, 'CONFIRMED'),
(100, 95, 330, 'UP15', 'sd', '', 21, 'MALE', 20.00, 'CONFIRMED'),
(101, 96, 326, 'UP12', 'rohit', '', 22, 'MALE', 40.00, 'CONFIRMED'),
(102, 97, 327, 'UP13', 'wer', '', 22, 'MALE', 60.00, 'CONFIRMED'),
(109, 104, 331, 'UP16', 'gf', '', 34, 'MALE', 80.00, 'CONFIRMED'),
(110, 105, 333, 'UP18', '43434', '', 33, 'MALE', 80.00, 'CONFIRMED'),
(111, 106, 286, 'LP1', 'fdfdf', '', 22, 'MALE', 200.00, 'CONFIRMED'),
(112, 107, 300, 'LP2', '12', '', 12, 'MALE', 20.00, 'CONFIRMED'),
(113, 108, 351, 'LS8', 'fdfd', '', 33, 'MALE', 30.00, 'CONFIRMED'),
(114, 109, 308, 'LP5', 'dfs', '', 23, 'MALE', 40.00, 'CONFIRMED'),
(115, 110, 350, 'LP11', 'gfdgf', '', 23, 'MALE', 30.00, 'CONFIRMED'),
(116, 111, 294, 'LP5', '4324234234', '', 34, 'MALE', 100.00, 'CONFIRMED'),
(117, 112, 354, 'LP12', 'rohit', '', 12, 'MALE', 10.00, 'CONFIRMED'),
(118, 113, 299, 'LP1', 'rfdfsd', '', 22, 'MALE', 60.00, 'CONFIRMED'),
(120, 115, 305, 'LP3', 'fdf', '', 22, 'MALE', 80.00, 'CONFIRMED'),
(121, 116, 304, 'UP2', '34343', '', 23, 'MALE', 140.00, 'CONFIRMED'),
(122, 117, 307, 'LS2', 'Dev Sheoran', '', 22, 'MALE', 40.00, 'CONFIRMED'),
(123, 118, 320, 'UP6', '23', '', 23, 'MALE', 140.00, 'CONFIRMED'),
(124, 119, 300, 'LP2', '12', '', 33, 'MALE', 20.00, 'CONFIRMED'),
(125, 120, 301, 'LS1', 'Rohit Choudhary', '', 22, 'MALE', 60.00, 'CONFIRMED'),
(126, 121, 306, 'LP4', 'Sanjay Sheoran', '', 33, 'MALE', 80.00, 'CONFIRMED'),
(127, 122, 309, 'LP6', 'errere', '', 22, 'MALE', 80.00, 'CONFIRMED'),
(128, 123, 310, 'LP7', 'fsdfsd', '', 12, 'MALE', 60.00, 'CONFIRMED'),
(129, 124, 311, 'LP8', 'errer', '', 22, 'MALE', 20.00, 'CONFIRMED'),
(130, 125, 314, 'LP9', 'dfdf', '', 23, 'MALE', 80.00, 'CONFIRMED'),
(131, 126, 313, 'LS4', 'dd', '', 23, 'MALE', 60.00, 'CONFIRMED'),
(132, 127, 334, 'LS6', 'fdf', '', 22, 'MALE', 60.00, 'CONFIRMED'),
(133, 128, 299, 'LP1', 'rfdfsd', '', 23, 'MALE', 80.00, 'CONFIRMED'),
(134, 129, 312, 'LS3', '23', '', 23, 'MALE', 40.00, 'CONFIRMED'),
(135, 130, 336, 'LS7', '121212', '', 12, 'MALE', 40.00, 'CONFIRMED'),
(136, 131, 316, 'LP10', 'fddrf', '', 3, 'MALE', 20.00, 'CONFIRMED'),
(137, 132, 308, 'LP5', 'dfs', '', 22, 'MALE', 30.00, 'CONFIRMED'),
(138, 133, 307, 'LS2', 'Dev Sheoran', '', 22, 'MALE', 30.00, 'CONFIRMED'),
(139, 134, 300, 'LP2', '12', '', 22, 'MALE', 60.00, 'CONFIRMED'),
(140, 135, 299, 'LP1', 'rfdfsd', '', 22, 'MALE', 30.00, 'CONFIRMED'),
(141, 136, 305, 'LP3', '23', '', 22, 'MALE', 30.00, 'CONFIRMED'),
(142, 137, 306, 'LP4', 'Sanjay Sheoran', '', 22, 'MALE', 30.00, 'CONFIRMED'),
(143, 138, 314, 'LP9', 'dfdf', '', 22, 'MALE', 30.00, 'CONFIRMED'),
(144, 139, 305, 'LP3', 'fdf', '', 23, 'MALE', 80.00, 'CONFIRMED'),
(145, 140, 308, 'LP5', 'fdf', '', 23, 'MALE', 20.00, 'CONFIRMED'),
(146, 141, 309, 'LP6', 'fdfd', '', 343, 'MALE', 60.00, 'CONFIRMED'),
(147, 142, 310, 'LP7', 'fsdfsd', '', 12, 'MALE', 60.00, 'CONFIRMED'),
(148, 143, 301, 'LS1', 'Rohit Choudhary', '', 22, 'MALE', 30.00, 'CONFIRMED'),
(150, 145, 287, 'LP2', '543534', '', 22, 'MALE', 400.00, 'CONFIRMED'),
(152, 147, 291, 'LP4', '23', '', 23, 'MALE', 400.00, 'CONFIRMED'),
(153, 148, 311, 'LP8', 'Are Dev Sir', '', 23, 'MALE', 50.00, 'CONFIRMED');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `routes`
--

INSERT INTO `routes` (`route_id`, `bus_id`, `route_name`, `starting_point`, `ending_point`, `status`, `is_popular`, `created_at`) VALUES
(12, 11, 'Delhi To Pilani', 'Delhi, kasmiri Gate', 'Pilani', 'Active', 0, '2025-09-08 09:57:42'),
(13, 11, 'Pilani To Delhi', 'Pilani', 'Rohtak, Purana Bus Stand', 'Active', 1, '2025-09-08 09:59:57'),
(14, 12, 'Dadri,Haryana To Loharu,Haryana', 'Dadri Bus Stand', 'Loharu Bus stand', 'Active', 1, '2025-09-10 07:16:08'),
(15, 12, 'Bhiwani to Badhra', 'Bhiwani,hashi Gate', 'Badhra,Haryana', 'Active', 0, '2025-09-10 07:20:46'),
(23, 12, 'Barwas to Loharu', 'Barwas', 'Loharu', 'Active', 1, '2025-09-12 10:35:31');

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
(33, 12, 'Mon', '00:30:00'),
(34, 13, 'Mon', '00:00:00'),
(35, 14, 'Mon', '09:00:00'),
(36, 14, 'Tue', '01:00:00'),
(37, 14, 'Wed', '02:00:00'),
(38, 14, 'Thu', '16:00:00'),
(39, 14, 'Fri', '06:00:00'),
(40, 14, 'Sat', '08:00:00'),
(41, 14, 'Sun', '02:00:00'),
(53, 23, 'Mon', '00:00:00'),
(54, 15, 'Mon', '00:00:00'),
(55, 15, 'Wed', '02:00:00'),
(56, 15, 'Fri', '03:00:00'),
(57, 15, 'Sun', '04:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `route_staff_assignments`
--

CREATE TABLE `route_staff_assignments` (
  `assignment_id` int(11) DEFAULT NULL,
  `route_id` int(11) NOT NULL COMMENT 'Foreign key to the routes table',
  `staff_id` int(11) NOT NULL COMMENT 'Foreign key to the staff table',
  `role` varchar(100) NOT NULL COMMENT 'e.g., Driver, Co-Driver, Conductor, Helper'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `route_staff_assignments`
--

INSERT INTO `route_staff_assignments` (`assignment_id`, `route_id`, `staff_id`, `role`) VALUES
(NULL, 23, 20, 'Driver'),
(NULL, 23, 20, 'Co-Driver'),
(NULL, 23, 26, 'Conductor'),
(NULL, 23, 26, 'Co-Conductor'),
(NULL, 23, 37, 'Helper'),
(NULL, 23, 38, 'Helper'),
(NULL, 23, 36, 'Helper'),
(NULL, 15, 20, 'Driver'),
(NULL, 15, 20, 'Co-Driver'),
(NULL, 15, 26, 'Conductor'),
(NULL, 15, 26, 'Co-Conductor'),
(NULL, 15, 37, 'Helper'),
(NULL, 15, 38, 'Helper');

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
(60, 12, 'Rohtak, Purana Bus Stand', 1, 60, 100.00, 200.00, 300.00, 400.00),
(61, 12, 'Loharu', 2, 120, 200.00, 300.00, 400.00, 500.00),
(62, 12, 'Pilani', 3, 180, 300.00, 400.00, 500.00, 600.00),
(63, 13, 'Loharu', 1, 60, 400.00, 300.00, 200.00, 100.00),
(64, 13, 'Rohtak, Purana Bus Stand', 2, 120, 500.00, 400.00, 300.00, 200.00),
(65, 14, 'Atela', 1, 50, 40.00, 60.00, 60.00, 80.00),
(66, 14, 'Badhra Bus stand', 2, 80, 60.00, 80.00, 80.00, 120.00),
(67, 14, 'Loharu Bus stand', 3, 100, 80.00, 100.00, 100.00, 140.00),
(77, 23, 'Basirwas', 1, 10, 20.00, 40.00, 40.00, 60.00),
(78, 23, 'Loharu', 2, 20, 40.00, 60.00, 60.00, 80.00),
(79, 15, 'juiii', 1, 40, 20.00, 40.00, 50.00, 60.00),
(80, 15, 'Jeetpura,Bhiwani', 2, 80, 30.00, 60.00, 60.00, 80.00),
(81, 15, 'Badhra,Haryana', 3, 120, 40.00, 70.00, 80.00, 120.00);

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
(286, 11, 'LP1', 'LOWER', 'SLEEPER', 160, 60, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-08 15:23:11', '2025-09-08 15:23:20'),
(287, 11, 'LP2', 'LOWER', 'SLEEPER', 220, 60, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-08 15:23:14', '2025-09-08 15:23:18'),
(288, 11, 'LS1', 'LOWER', 'SEATER', 80, 60, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-08 15:23:28', '2025-09-10 12:38:54'),
(289, 11, 'LS2', 'LOWER', 'SEATER', 80, 170, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-08 15:23:30', '2025-09-10 12:38:51'),
(290, 11, 'LP3', 'LOWER', 'SLEEPER', 160, 150, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-08 15:23:41', '2025-09-10 12:38:40'),
(291, 11, 'LP4', 'LOWER', 'SLEEPER', 220, 150, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-08 15:23:45', '2025-09-10 12:38:37'),
(292, 11, 'LS3', 'LOWER', 'SEATER', 80, 120, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-08 15:23:50', '2025-09-10 12:38:53'),
(293, 11, 'LS4', 'LOWER', 'SEATER', 80, 220, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-08 15:23:54', '2025-09-10 12:38:56'),
(294, 11, 'LP5', 'LOWER', 'SLEEPER', 160, 240, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-08 15:23:58', '2025-09-10 12:38:44'),
(295, 11, 'LP6', 'LOWER', 'SLEEPER', 220, 330, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-08 15:24:02', '2025-09-10 12:38:47'),
(296, 11, 'UP1', 'UPPER', 'SLEEPER', 40, 50, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-08 15:24:10', '2025-09-08 15:24:11'),
(297, 11, 'UP2', 'UPPER', 'SLEEPER', 90, 50, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-08 15:24:14', '2025-09-08 15:24:15'),
(298, 11, 'UP3', 'UPPER', 'SLEEPER', 210, 50, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-08 15:24:18', '2025-09-08 15:24:18'),
(299, 12, 'LP1', 'LOWER', 'SLEEPER', 190, 70, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-08 15:25:34', '2025-09-08 15:25:34'),
(300, 12, 'LP2', 'LOWER', 'SLEEPER', 140, 70, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-08 15:25:36', '2025-09-08 15:25:37'),
(301, 12, 'LS1', 'LOWER', 'SEATER', 50, 70, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-08 15:25:40', '2025-09-10 12:30:25'),
(302, 12, 'UP1', 'UPPER', 'SLEEPER', 150, 60, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-08 15:25:43', '2025-09-10 12:34:56'),
(304, 12, 'UP2', 'UPPER', 'SLEEPER', 70, 60, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-08 15:25:49', '2025-09-10 12:35:06'),
(305, 12, 'LP3', 'LOWER', 'SLEEPER', 190, 160, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-10 12:30:21', '2025-09-10 12:30:22'),
(306, 12, 'LP4', 'LOWER', 'SLEEPER', 50, 120, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-10 12:30:28', '2025-09-11 15:35:39'),
(307, 12, 'LS2', 'LOWER', 'SEATER', 140, 160, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-10 12:30:33', '2025-09-10 12:30:34'),
(308, 12, 'LP5', 'LOWER', 'SLEEPER', 140, 210, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-10 12:30:36', '2025-09-10 12:30:37'),
(309, 12, 'LP6', 'LOWER', 'SLEEPER', 190, 250, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-10 12:30:41', '2025-09-10 12:30:42'),
(310, 12, 'LP7', 'LOWER', 'SLEEPER', 140, 300, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-10 12:30:45', '2025-09-10 12:30:46'),
(311, 12, 'LP8', 'LOWER', 'SLEEPER', 190, 340, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-10 12:30:50', '2025-09-10 12:30:51'),
(312, 12, 'LS3', 'LOWER', 'SEATER', 140, 390, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-10 12:30:54', '2025-09-10 12:30:55'),
(313, 12, 'LS4', 'LOWER', 'SEATER', 50, 210, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-10 12:30:58', '2025-09-10 12:31:00'),
(314, 12, 'LP9', 'LOWER', 'SLEEPER', 50, 260, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-10 12:31:02', '2025-09-10 12:31:04'),
(315, 12, 'LS5', 'LOWER', 'SEATER', 200, 510, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-10 12:31:08', '2025-09-11 15:34:53'),
(316, 12, 'LP10', 'LOWER', 'SLEEPER', 50, 400, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-10 12:31:13', '2025-09-10 12:31:25'),
(317, 12, 'UP3', 'UPPER', 'SLEEPER', 200, 60, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-10 12:34:54', '2025-09-10 12:34:54'),
(318, 12, 'UP4', 'UPPER', 'SLEEPER', 200, 150, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-10 12:35:13', '2025-09-10 12:35:47'),
(319, 12, 'UP5', 'UPPER', 'SLEEPER', 150, 150, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-10 12:35:16', '2025-09-10 12:35:48'),
(320, 12, 'UP6', 'UPPER', 'SLEEPER', 70, 150, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-10 12:35:20', '2025-09-10 12:36:01'),
(321, 12, 'UP7', 'UPPER', 'SLEEPER', 200, 240, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-10 12:35:23', '2025-09-10 12:35:45'),
(322, 12, 'UP8', 'UPPER', 'SLEEPER', 150, 330, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-10 12:35:25', '2025-09-10 12:35:59'),
(323, 12, 'UP9', 'UPPER', 'SLEEPER', 70, 240, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-10 12:35:26', '2025-09-10 12:36:03'),
(324, 12, 'UP10', 'UPPER', 'SLEEPER', 150, 240, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-10 12:35:29', '2025-09-10 12:35:56'),
(325, 12, 'UP11', 'UPPER', 'SLEEPER', 200, 330, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-10 12:35:31', '2025-09-10 12:35:50'),
(326, 12, 'UP12', 'UPPER', 'SLEEPER', 200, 420, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-10 12:35:34', '2025-09-10 12:35:52'),
(327, 12, 'UP13', 'UPPER', 'SLEEPER', 200, 510, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-10 12:35:36', '2025-09-10 12:35:54'),
(328, 12, 'DRIVER', 'LOWER', 'DRIVER', 170, 10, 50, 50, 'VERTICAL_UP', 0.00, 'ANY', 0, 'AVAILABLE', '2025-09-10 12:35:39', '2025-09-10 12:35:40'),
(329, 12, 'UP14', 'UPPER', 'SLEEPER', 70, 330, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-10 12:36:07', '2025-09-10 12:36:08'),
(330, 12, 'UP15', 'UPPER', 'SLEEPER', 150, 420, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-10 12:36:19', '2025-09-10 12:36:21'),
(331, 12, 'UP16', 'UPPER', 'SLEEPER', 150, 510, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-10 12:36:25', '2025-09-10 12:36:26'),
(332, 12, 'UP17', 'UPPER', 'SLEEPER', 70, 420, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-10 12:36:30', '2025-09-10 12:36:31'),
(333, 12, 'UP18', 'UPPER', 'SLEEPER', 70, 510, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-10 12:36:34', '2025-09-10 12:36:35'),
(334, 12, 'LS6', 'LOWER', 'SEATER', 140, 440, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-10 12:36:39', '2025-09-10 12:36:40'),
(336, 12, 'LS7', 'LOWER', 'SEATER', 190, 440, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-10 12:37:00', '2025-09-10 12:37:00'),
(337, 11, 'LP7', 'LOWER', 'SLEEPER', 220, 240, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-10 12:38:36', '2025-09-10 12:38:41'),
(338, 11, 'LP8', 'LOWER', 'SLEEPER', 80, 270, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-10 12:38:59', '2025-09-10 12:39:00'),
(339, 11, 'LP9', 'LOWER', 'SLEEPER', 160, 330, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-10 12:39:03', '2025-09-10 12:39:04'),
(340, 11, 'LP10', 'LOWER', 'SLEEPER', 80, 360, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-10 12:39:07', '2025-09-10 12:39:07'),
(341, 11, 'UP4', 'UPPER', 'SLEEPER', 40, 140, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-10 12:39:12', '2025-09-10 12:39:12'),
(342, 11, 'UP5', 'UPPER', 'SLEEPER', 90, 140, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-10 12:39:15', '2025-09-10 12:39:17'),
(343, 11, 'UP6', 'UPPER', 'SLEEPER', 40, 230, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-10 12:39:20', '2025-09-10 12:39:20'),
(344, 11, 'UP7', 'UPPER', 'SLEEPER', 90, 230, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-10 12:39:24', '2025-09-10 12:39:24'),
(345, 11, 'UP8', 'UPPER', 'SLEEPER', 210, 140, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-10 12:39:27', '2025-09-10 12:39:28'),
(346, 11, 'UP9', 'UPPER', 'SLEEPER', 210, 230, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-10 12:39:33', '2025-09-10 12:39:34'),
(347, 11, 'UP10', 'UPPER', 'SLEEPER', 40, 320, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-10 12:39:37', '2025-09-10 12:39:39'),
(348, 11, 'UP11', 'UPPER', 'SLEEPER', 90, 320, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-10 12:39:41', '2025-09-10 12:39:43'),
(349, 11, 'UP12', 'UPPER', 'SLEEPER', 210, 320, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-10 12:39:47', '2025-09-10 12:39:49'),
(350, 12, 'LP11', 'LOWER', 'SLEEPER', 50, 490, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-11 15:33:48', '2025-09-11 15:33:49'),
(351, 12, 'LS8', 'LOWER', 'SEATER', 150, 500, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-11 15:33:52', '2025-09-11 15:33:52'),
(352, 12, 'LS9', 'LOWER', 'SEATER', 50, 10, 40, 40, 'VERTICAL_DOWN', 0.00, 'MALE', 0, 'AVAILABLE', '2025-09-11 15:34:16', '2025-09-11 15:34:40'),
(353, 12, 'LG1', 'LOWER', 'AISLE', 50, 350, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 0, 'AVAILABLE', '2025-09-11 15:34:50', '2025-09-11 15:34:56'),
(354, 12, 'LP12', 'LOWER', 'SLEEPER', 150, 550, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-11 15:35:16', '2025-09-11 15:35:20'),
(361, 18, 'LP1', 'LOWER', 'SLEEPER', 40, 20, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-12 13:19:15', '2025-09-12 13:19:16'),
(362, 18, 'LP2', 'LOWER', 'SLEEPER', 100, 20, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-12 13:19:19', '2025-09-12 13:19:38'),
(363, 18, 'LP3', 'LOWER', 'SLEEPER', 190, 20, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-12 13:19:27', '2025-09-12 13:19:34'),
(364, 18, 'LP4', 'LOWER', 'SLEEPER', 190, 110, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-12 13:19:31', '2025-09-12 13:19:36'),
(365, 18, 'LP5', 'LOWER', 'SLEEPER', 100, 110, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-12 13:19:41', '2025-09-12 13:19:43'),
(366, 18, 'LP6', 'LOWER', 'SLEEPER', 40, 110, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-12 13:19:47', '2025-09-12 13:19:48'),
(367, 18, 'LP7', 'LOWER', 'SLEEPER', 40, 200, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-12 13:19:52', '2025-09-12 13:19:53'),
(368, 18, 'LP8', 'LOWER', 'SLEEPER', 100, 200, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-12 13:19:56', '2025-09-12 13:19:56'),
(369, 18, 'LP9', 'LOWER', 'SLEEPER', 190, 200, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-12 13:19:59', '2025-09-12 13:20:00');

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
(20, 'Rohit Choudhary', '8905288939', 'Driver', '98765432112', '21323213123212', 'staff_1757658253_014737ed.jpg', 'Heee', 'Active', '2025-09-12 06:24:13'),
(26, 'Sanjay Sheoran', '9876543212', 'Conductor', NULL, '2132321312321232', 'staff_1757658340_93e7dc15.jpg', 'fdfsd', 'Active', '2025-09-12 06:25:40'),
(35, 'Dev sheoran', '9728833427', 'Telecaller', NULL, '213232131232112', 'staff_1757658857_3113cc3f.jpg', 'ewew', 'Active', '2025-09-12 06:34:17'),
(36, 'Naveen Sheoran', '1234567890', 'Helper', NULL, '2132321312321211', 'staff_1757671879_05222bb1.jpg', 'ds', 'Active', '2025-09-12 10:11:19'),
(37, 'Akash Sheoran', '9876543211', 'Helper', NULL, '2132321312321233', 'staff_1757671913_798c6e71.jpg', 'sd', 'Active', '2025-09-12 10:11:53'),
(38, 'Dinesh Sharma', '8905288111', 'Helper', NULL, '213232131231212', '', 'dcc', 'Active', '2025-09-12 10:12:47');

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
(1, 36, 'c5afb44d837cf6e02161a1e1676150da', '2025-09-08 11:41:44'),
(2, 35, '693d72c9d1680259fd8aa9c5612273a7', '2025-09-08 11:46:00'),
(3, 32, 'e67d4707993133ebdf25ae0927bd05bc', '2025-09-08 11:46:11'),
(4, 37, '2cb58331f1f5f5f820cffa9410763b21', '2025-09-08 11:55:10'),
(5, 38, 'd83ca59b7dfa45ecc46a9b3c61a1b12f', '2025-09-09 04:40:18'),
(6, 39, 'e9142ef24a2c31edb3f0c08b1d6f3c4a', '2025-09-09 04:43:22'),
(7, 40, 'bf163f0d17db29e4ce483a2965a5b31b', '2025-09-09 05:21:17'),
(8, 42, '861b095bab772f959599bba63c157022', '2025-09-09 05:25:11'),
(9, 43, '86f0b8ddb9231be4479a75bc0aa1c230', '2025-09-09 05:29:37'),
(10, 44, 'dbe27b6ddc81c05c9a001d1234efb8ae', '2025-09-09 05:50:13'),
(11, 45, 'df9663897b5e50a12ffe025099bb5f3a', '2025-09-09 05:52:02'),
(12, 47, '3c4181e2e8b14834d0f8e823939f42cf', '2025-09-10 05:46:30'),
(13, 48, '75cb0e30550bba71543ce134e0867f48', '2025-09-10 07:22:33'),
(14, 49, '8e35ae1a8c0eca2eb5611c873e9e69bb', '2025-09-10 07:47:15'),
(15, 50, '4507fa2a074fa66fcf3a3ca959b756d6', '2025-09-10 08:10:53'),
(16, 51, '8f0d5cd3512794031bef0603244f1970', '2025-09-10 08:27:00'),
(17, 55, '8e942b4e1bb2ab448e1ca4e14aea1507', '2025-09-10 10:10:35'),
(18, 57, 'e563de8461bc99d22f269d1ee3e1ae2a', '2025-09-10 10:51:39'),
(19, 59, 'e7bc14535a60e91b8d6d062bcc5ff468', '2025-09-10 11:41:09'),
(20, 58, 'e0149f078d7f6f01e0c630b707492281', '2025-09-10 11:43:07'),
(21, 84, '3389ce49c9102dad2353d382ccea11d3', '2025-09-11 06:10:19'),
(22, 88, 'fce6716ef009191fd596fd46751a2909', '2025-09-11 06:53:55'),
(23, 87, 'fb7474f5feab3e556e2ec08b797a129b', '2025-09-11 07:25:20'),
(24, 91, 'ab1de28953546f030a8443573bd23cd1', '2025-09-11 09:55:28'),
(25, 75, 'b76eb104c038273a6827fb123afee873', '2025-09-11 10:10:49'),
(26, 106, '81b4e6932918335b6437e3305e96b7ce', '2025-09-11 10:36:18'),
(27, 128, '74ea84ff303c1a878591a13c868ccfba', '2025-09-11 12:14:29'),
(28, 140, '22438f2977376417d5848a5fcc85f80e', '2025-09-12 04:52:41');

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
(4, 84, NULL, NULL, 'Razorpay', 'pay_RGBrzvHIOy3EVw', 'order_RGBrsiu2DsMv2c', '44d86c7599ef0f8a1aeed4a04c917355e8ae2012f9ced66e9288421828b5db8c', 80.00, 'INR', 'CAPTURED', 'online', NULL, NULL, '2025-09-11 06:09:59', '2025-09-11 06:09:59'),
(5, 85, NULL, NULL, 'Razorpay', 'pay_RGC9iyitbObRB8', 'order_RGC9XbZits2ymD', 'fd20c40137344c8e7497b7be4b0ffb0256de5a1f21fa0879853d02406471aaa4', 40.00, 'INR', 'CAPTURED', 'online', NULL, NULL, '2025-09-11 06:26:45', '2025-09-11 06:26:45'),
(6, 86, NULL, NULL, 'Razorpay', 'pay_RGCN7rwgAx8vX9', 'order_RGCMMxCqIhW1k3', '034d94cddb09e07edd3e5bed2082c299c1deee276803fb9d6853bab30bade336', 80.00, 'INR', 'CAPTURED', 'online', NULL, NULL, '2025-09-11 06:39:26', '2025-09-11 06:39:26'),
(7, 88, NULL, NULL, 'Razorpay', 'pay_RGCcHhFYH2JJKn', 'order_RGCbjmzR4s2EbX', '1dfad323815b2a963e0854c8d77494bbc7ee891d2c1485c1a0e155645a7a29e4', 80.00, 'INR', 'CAPTURED', 'online', NULL, NULL, '2025-09-11 06:53:47', '2025-09-11 06:53:47'),
(8, 89, NULL, NULL, 'Razorpay', 'pay_RGDCh2TWMVATlS', 'order_RGDCXIF5BRNiTB', '55b0df393fb994589cd3c77e59e5c55b5298c0a4768137cb5759c303f80abfe5', 60.00, 'INR', 'CAPTURED', 'online', NULL, NULL, '2025-09-11 07:28:15', '2025-09-11 07:28:15'),
(9, 107, NULL, NULL, 'Razorpay', 'pay_RGGK7K4Y0Bqr1h', 'order_RGGK1aMgqQcevB', '1b611ffd8a79a83fc0eef9a642d05071a6fc75b1ee3bb3de8fec788a8ffebdcd', 20.00, 'INR', 'CAPTURED', 'online', NULL, NULL, '2025-09-11 10:31:22', '2025-09-11 10:31:22'),
(10, 110, NULL, NULL, 'Razorpay', 'pay_RGGh8FUGpgrSj1', 'order_RGGgtjVnsjiTGf', '1e3d2045583af3d81b898b0f6ac160dd735ecc3883dfa9adbe1f2fb226c4d6cb', 30.00, 'INR', 'CAPTURED', 'online', NULL, NULL, '2025-09-11 10:53:09', '2025-09-11 10:53:09'),
(11, 116, NULL, NULL, 'Razorpay', 'pay_RGHAIV1dW8Od4c', 'order_RGHAByorhngrki', '7db75f76e55e51782f6dff6e4b2a047907200b316e684b94091ed9b55ca51387', 140.00, 'INR', 'CAPTURED', 'online', NULL, NULL, '2025-09-11 11:20:47', '2025-09-11 11:20:47'),
(12, 117, NULL, NULL, 'Razorpay', 'pay_RGHChvo8gVjxPk', 'order_RGHCcFhZTPCbFV', 'efe2fa73a00147d95781510bcf55de172fcd82d603587a3d7cb7cdf48365a734', 40.00, 'INR', 'CAPTURED', 'online', NULL, NULL, '2025-09-11 11:23:03', '2025-09-11 11:23:03'),
(13, 118, NULL, NULL, 'Razorpay', 'pay_RGHIODCtoO0iOe', 'order_RGHIHseNgjg6ep', 'd74ec345d0c1754b193930aa686202235a2a640bb6d5daa65f0f7bd46ef3419f', 140.00, 'INR', 'CAPTURED', 'online', NULL, NULL, '2025-09-11 11:28:25', '2025-09-11 11:28:25'),
(14, 122, NULL, NULL, 'Razorpay', 'pay_RGHeWbocp8at3A', 'order_RGHeRRUZzG0ccK', '84a1347155794ce8114cfecb2f5cb24f92a1ef2b456fb18d124ee3df22b40286', 80.00, 'INR', 'CAPTURED', 'online', NULL, NULL, '2025-09-11 11:49:23', '2025-09-11 11:49:23'),
(15, 129, NULL, NULL, 'Razorpay', 'pay_RGI8Ef0jPYeJqh', 'order_RGI886fiq5S8JX', 'd24602c624b9657d31f159ea57a6eca889eb751e4c2145aaab871e99973a9c38', 40.00, 'INR', 'CAPTURED', 'online', NULL, NULL, '2025-09-11 12:17:24', '2025-09-11 12:17:24'),
(16, 130, NULL, NULL, 'Razorpay', 'pay_RGI9UivzHZaBF2', 'order_RGI9NhcRwqOhMd', '824154b80d8b93cff856b2167a475c0f6cb9630e1550f297f9c603be9769fe61', 40.00, 'INR', 'CAPTURED', 'online', NULL, NULL, '2025-09-11 12:18:57', '2025-09-11 12:18:57'),
(17, 131, NULL, NULL, 'Razorpay', 'pay_RGIAtqWPELXANf', 'order_RGIAlQcc7natOz', 'd3de874ff5d7fb24405ab27af28e516ef81063efb67fde43ce55a35b4940264d', 20.00, 'INR', 'CAPTURED', 'online', NULL, NULL, '2025-09-11 12:20:08', '2025-09-11 12:20:08'),
(18, 132, NULL, NULL, 'Razorpay', 'pay_RGYXmII69Lc0ob', 'order_RGYXYeet7OdYPA', '289d7c3a7301d8110cfba36e01311e0ee52f3a98b14f4ac43be4ed5bf73df36e', 30.00, 'INR', 'CAPTURED', 'online', NULL, NULL, '2025-09-12 04:20:46', '2025-09-12 04:20:46'),
(19, 135, NULL, NULL, 'Razorpay', 'pay_RGYs3AHGcTeocQ', 'order_RGYrxkJ6qxz9nD', '35e95261fccd381753f698f885d3a36e7b47a72c0f612c450c57293eb54ced69', 30.00, 'INR', 'CAPTURED', 'online', NULL, NULL, '2025-09-12 04:39:58', '2025-09-12 04:39:58'),
(20, 138, NULL, NULL, 'Razorpay', 'pay_RGYxbFUZ8buki0', 'order_RGYxVdw1DBFJTn', 'c67bced5d9883b9837a212a884c78d810c1c40ea895bdd6728f573f1acbb6fe5', 30.00, 'INR', 'CAPTURED', 'online', NULL, NULL, '2025-09-12 04:45:13', '2025-09-12 04:45:13'),
(21, 139, NULL, NULL, 'Razorpay', 'pay_RGYyzrXFzaVufe', 'order_RGYysiwGnMmZF4', 'df5f963fcc6830ceaaaf1df650b0a26dd7252da4f8fd6c9e7c9108d54a7a37a2', 80.00, 'INR', 'CAPTURED', 'online', NULL, NULL, '2025-09-12 04:46:33', '2025-09-12 04:46:33'),
(22, 140, NULL, NULL, 'Razorpay', 'pay_RGZ4wKNVncFGbH', 'order_RGZ4fLBlEpDLWA', '937ae27d5bcaa47c72e93b76c7bc9c1eff58118bbbacb854fa17e6f8816e4d5c', 20.00, 'INR', 'CAPTURED', 'online', NULL, NULL, '2025-09-12 04:52:10', '2025-09-12 04:52:10'),
(23, 141, NULL, NULL, 'Razorpay', 'pay_RGZ8fKiHr18ITv', 'order_RGZ8Xy7C1i4xRk', 'd37075808aa7ecfcb8b4447fa7ce5b56e848da3a50ad4916f516e5a37783748c', 60.00, 'INR', 'CAPTURED', 'online', NULL, NULL, '2025-09-12 04:55:41', '2025-09-12 04:55:41'),
(24, 147, NULL, NULL, 'Razorpay', 'pay_RGZb6UoDYrL085', 'order_RGZaxahM01eKB3', '070650e89761789bef0044fead5deb4ba2bccf83cfb35cf921065adee7895671', 400.00, 'INR', 'CAPTURED', 'online', NULL, NULL, '2025-09-12 05:22:37', '2025-09-12 05:22:37'),
(25, 148, NULL, NULL, 'Razorpay', 'pay_RGbavP02XCFQhI', 'order_RGbansZznt3snY', '0385bed7dc9a688399446432bd6a37553e3308cbab68807cbfe61fa7066ad862', 50.00, 'INR', 'CAPTURED', 'online', NULL, NULL, '2025-09-12 07:19:50', '2025-09-12 07:19:50');

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
(3, 'Sanjay Kumar Sheoran', '$2y$10$z7lBSp5NypMVF1S05ZvNeui70bCZceCut.xYsUwAGecNIoqck5DO6', '9728833428', 'sjsheoran111@gmail.com', '::1', 1, NULL, NULL, '2025-09-11 11:49:54');

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
(14, 3, 'ff03b340a2fb6d4fdb3e4fce2623e4badd6e0c841fc269efbba1346009cb0ed1', '2', '2025-09-04 11:37:51', '::1'),
(15, 3, 'be68cfe4cdbbc42b12d948d7f77f528e075a1eacd329f82f45c97494ca3dcb5f', '2', '2025-09-04 11:38:26', '::1'),
(16, 3, '81ad60b196d8261e01d20aec24d8cb41393b3ce84f9bbf71cda0298e6e735eab', '2', '2025-09-04 12:14:56', '::1'),
(17, 3, '279db50551746bb3bfefa5f082e7b395e1ac9696fee0cdb78ef47c47c4fef420', '2', '2025-09-05 05:36:11', '::1'),
(18, 3, '98f53678c0e1add121fe22c8bfa88f706b6d4246f7625405ce97fba5a2f1de1c', '2', '2025-09-05 05:46:11', '::1'),
(19, 3, '9105cd12476318f2a3d5935da616fa94f9e1174bae851d17a4960489c6bdbdba', '2', '2025-09-05 06:03:06', '::1'),
(20, 3, 'f5eec004b9a6278db46e55656dbe16e6b92efd67ee8a007c3d35b07d9707f748', '2', '2025-09-05 06:12:51', '::1'),
(21, 3, '612767242f31264633b7975cdf333590416cf0285994871ad6416ba5af08db8a', '2', '2025-09-05 07:03:20', '::1'),
(22, 3, '11340fe980dac87ab49e82eca5004e37a0d97d384ffc26487bd0eb0bc20c081b', '1', '2025-09-05 07:32:36', '::1'),
(23, 3, '865f07c7ef639d0915cf4d53f042952cac52d003c7cc7bfe1954462da405a1aa', '1', '2025-09-05 08:18:51', '::1');

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
-- Dumping data for table `user_login_token`
--

INSERT INTO `user_login_token` (`id`, `user_id`, `token`, `status`, `date_time`, `ip_address`) VALUES
(1, '1', '$2y$12$MadPgmFOebxfE9Mh5z9Md.KWyzLJouOeKQrL/nY8VXI9t.QnY9dd6', '1', '2025-07-23 13:36:33', '::1'),
(2, '1', '$2y$12$pd.6NV4JojUQkjTrT1CvPeWN0v653OsGhAhP5DHgKvsu/HBRENpXK', '1', '2025-07-23 13:36:39', '::1'),
(3, '1', '$2y$12$VEJp.fRrrAJKh2ItC9E2Xe4ruWWqXPiNtXkZD11jw.p2FLn.uYCUO', '1', '2025-07-23 15:04:00', '::1'),
(4, '1', '$2y$12$DSan8YXML9dpF.2qD6ya6e44a9lsBtKpoSRyiwIOUbDsZAlMEKrVO', '1', '2025-07-24 10:48:17', '::1'),
(5, '1', '$2y$12$cwNlX544x5Pzc4thjJYLh.I1c5dIiRAxLxVDwwYHlzWC3v0xCITR.', '1', '2025-07-24 10:48:21', '::1'),
(6, '1', '$2y$12$Xoz4R2UsI/47YkDRhJLVH.Ahc7VQtsh6x4Zxt0mt49bYEcnWDErim', '1', '2025-07-24 11:33:48', '::1'),
(7, '1', '$2y$12$5MLX0V.L3SODoPmIhb1vder1jM1HDs7S74Y6tq2mavvzlx7dFbNk.', '1', '2025-07-24 12:04:27', '::1'),
(8, '1', '$2y$12$cxXO8ecKRdeLdJsaQmQ0YOsToSCPWr.LwWOYZxGrbtGIAxVxDDdwS', '1', '2025-08-01 05:14:03', '::1'),
(9, '1', '$2y$12$DricklYAleWtna0pwlHJEeGkTtu2C2eHOtrSV.wNCJEsS8onSuHUi', '1', '2025-08-02 04:46:21', '::1'),
(10, '1', '$2y$12$NAxp/I2Ptd8CR/lKWAGCMeWhwylVlM2ezosmnrhujr20vXatgHbBK', '1', '2025-08-02 09:52:11', '::1'),
(11, '1', '$2y$12$47qc5Hc9BTilF5XcKRZEeO0x01G.JtCNY1LhdQQ6NfZr7mfn9psD2', '1', '2025-08-04 04:22:18', '::1'),
(12, '1', '$2y$12$hTKVRSiLvT3fJX4DyVtYr.v5uvKbEtys3.gn0EpzuXpUmGGRUyGKm', '1', '2025-08-06 04:47:49', '::1'),
(13, '1', '$2y$12$YKLh5suCHoBEEFHHnn/MH.oBOWC5S3RmLa1L5In86Y2X4QpoSI5.a', '1', '2025-08-07 05:25:27', '::1'),
(14, '1', '$2y$12$I5CvDe7e8jkX75PObmE3jOKYaVUi8XY.1Fs9xCLJNNP/pW3cl/Vp.', '1', '2025-08-08 04:09:44', '::1'),
(15, '1', '$2y$12$3CxwrTNsloUXtrEYX6y/fOrf8SFkR3vdn1Bfh7s/S04TImuDcQa1S', '1', '2025-08-08 04:18:43', '::1'),
(16, '1', '$2y$12$H8pCHBSMevGvFI.TQ5eUX./ilwYnIm/y2n1jDhOl.tctrNC/lOmMK', '1', '2025-08-08 05:14:08', '::1'),
(17, '1', '$2y$12$wCdkESIdUVw9PX9EiS7HUeTxqWJIFHTvXnt8kS4lg/2IsLuFwuprG', '1', '2025-08-08 06:20:07', '::1'),
(18, '1', '$2y$12$BEF79bdpOS46wKttyX5xL.mxxBbVFQZ8X4ho7m4bEB9Ljs3no8EUa', '1', '2025-08-08 06:22:24', '::1'),
(19, '1', '$2y$12$suS87kiCLaX/ygBTYGOCfeqibLK49lAqlYOp68aXClrw7Q6LSbTd.', '1', '2025-08-08 06:33:57', '::1'),
(20, '1', '$2y$12$RaLUzv70moy4Ahf22FaI2ex3MDS8cMzwqJeUHUX6N3JKLapvz4vsG', '1', '2025-08-08 07:30:01', '::1'),
(21, '1', '$2y$12$naYTP7AzDZis61WNL5GYMu5mtjdmzpX6H4YEZuk9k1LV0lXoIcrKC', '1', '2025-08-08 07:34:39', '::1'),
(22, '1', '$2y$12$YF53TImKSePMqi6GU3JwKuVP6ukeo2hEF.TveXl2x3ZCBZ3jFfmqK', '1', '2025-08-11 04:23:24', '::1'),
(23, '1', '$2y$12$w9oHPm3HxoUg3/91nloVjOJqdCfuSFtwc9rdRCFjP.AMSA2WSKetO', '1', '2025-08-11 04:23:59', '::1'),
(24, '1', '$2y$12$cBfouT6qYrps8ULsB.VgXOxY4r4sALbvdJw36ZFd/210UiyPCbo2i', '1', '2025-08-11 11:33:13', '::1'),
(25, '1', '$2y$12$pNcMEAMzqu.AB.99pBw/YO0p/VAkq1OLaWpyhA3Npux/ecbDyifUO', '1', '2025-08-13 04:31:44', '::1'),
(26, '1', '$2y$12$2MXHsrp5heek9PUdeaPvj..f8cZEnUNCQnNzQRMyvA2xJNn/TZiam', '1', '2025-08-13 10:36:52', '::1'),
(27, '1', '$2y$12$1jK7la7gDV.WkwIaH9RKUuOdm1P19UtF/kwtQTIXEHXNSUHbtj/iW', '1', '2025-08-14 04:25:11', '::1'),
(28, '1', '$2y$12$/Q9bkIR35xs37kPbGok4a.t7yN0vwdWJUAbQfxLWpBiIMKb9kPICq', '1', '2025-08-15 04:17:21', '::1'),
(29, '1', '$2y$12$96OnmgPOrsKaX9NT2Kcpe.IeIscU5YIYp5R4MFDxa8nIUxfM7L.Ve', '1', '2025-08-15 11:47:07', '::1'),
(30, '1', '$2y$12$clDwe/RvdDTCzRx8.fCPYec/Ag6Ju04CJqIThRyDNYL0gjxBTwMee', '1', '2025-08-18 04:13:06', '::1'),
(31, '1', '$2y$12$aFwQ24tlDlF/iqIzh9NNRumMRIuDzr.CxW0bHRsAtSBSX2gal54TW', '1', '2025-08-22 04:30:37', '::1'),
(32, '1', '$2y$12$F6GpdXMCa6cn4ZFqhvu2X.Ky7VNkImPLB5PyxV3KnUGyFNiSnjfYa', '1', '2025-09-02 10:59:11', '::1'),
(33, '1', '$2y$12$fKlf3UayjY0fD82XM5PoNORHBpdZQFaiU5MzKWli27cOCfRNWM09q', '1', '2025-09-03 04:30:16', '::1'),
(34, '1', '$2y$12$Z7YGN34i7pyRVLh8h7/PgegTDa1iBVsRVSyyFYczxheO9LuChFZAu', '1', '2025-09-04 04:29:05', '::1'),
(35, '1', '$2y$12$y0lJzCyfsVZ/GOGh36IDl.AS.CTqPxWmMrXYxF3o6oR3EHXe3Mc4m', '1', '2025-09-04 05:55:09', '::1'),
(36, '1', '$2y$12$E3XS77PJ7WM9Ro1lQRY4kOwzTfuW6BoqoS5P.D6H5TGbB4cFbpJoK', '1', '2025-09-05 05:39:07', '::1'),
(37, '1', '$2y$12$KV1tY0.2slxYlzv13hWnOOXJepGP/nTmafn9toSZmTXWmV9NR4D2G', '1', '2025-09-05 10:24:21', '::1'),
(38, '1', '$2y$12$fVor3chfn9YlUwbqcI5/v.y1P5XvcoaNauJ/iL1wnvWH0Qm87w8jW', '1', '2025-09-05 10:24:57', '::1'),
(39, '1', '$2y$12$1d/Gw3hM9uQiglvv9o5CHeghGhsXCG3LD8y5sexgcso7kVWgVw5YG', '1', '2025-09-05 10:36:39', '::1'),
(40, '1', '$2y$12$awH7nql2Xvkud2hQLNxj.evpymxzKnlS2BqEcCLnxoEshAumHP.8C', '1', '2025-09-05 10:43:46', '::1'),
(41, '1', '$2y$12$TxiMGB1C4GDLfFCBzMlBTuiejdoyYUt92hsz506OTGHSfwXNFC2MO', '1', '2025-09-05 12:10:25', '::1'),
(42, '1', '$2y$12$HrFKPaNyaKu3oFf4fZDLO.wOZEUWp2iHZ11T3iF7mri8ZArc8a5n2', '1', '2025-09-05 12:10:29', '::1'),
(43, '1', '$2y$12$oQFqS420RFEhyq9F9oG33.dqXImlkB9s67GYGxslKV3gyCG9My7lq', '1', '2025-09-05 12:10:35', '::1'),
(44, '1', '$2y$12$0IJW2339.UMKHszKn0sL8Om5In9sZXECiVhwbh7.oWohW7F5oSk5K', '1', '2025-09-05 12:14:16', '::1'),
(45, '3', '$2y$12$ry7m/FP.n6FWQ5WMscrrD.d6noCtP/DyJtwQr81JiJ3eZNWXHb3/G', '1', '2025-09-05 12:14:38', '::1'),
(46, '1', '$2y$12$B8i90VkkWtY9v.fhHc5U1.Lyvj7AFK8K.hxvBh70ZeSYxb7o8bLyC', '1', '2025-09-05 12:15:27', '::1');

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
  ADD KEY `mobile` (`mobile`);

--
-- Indexes for table `admin_activity_log`
--
ALTER TABLE `admin_activity_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `booked_seats`
--
ALTER TABLE `booked_seats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `route_bus_seat_date` (`route_id`,`bus_id`,`seat_id`,`travel_date`);

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
  ADD KEY `idx_bus_operator_id` (`operator_id`),
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
-- Indexes for table `operators`
--
ALTER TABLE `operators`
  ADD PRIMARY KEY (`operator_id`);

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
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `admin_activity_log`
--
ALTER TABLE `admin_activity_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT for table `booked_seats`
--
ALTER TABLE `booked_seats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=154;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=149;

--
-- AUTO_INCREMENT for table `buses`
--
ALTER TABLE `buses`
  MODIFY `bus_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `bus_categories`
--
ALTER TABLE `bus_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `bus_category_map`
--
ALTER TABLE `bus_category_map`
  MODIFY `map_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `bus_images`
--
ALTER TABLE `bus_images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `cancellations`
--
ALTER TABLE `cancellations`
  MODIFY `cancellation_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cash_collections_log`
--
ALTER TABLE `cash_collections_log`
  MODIFY `collection_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `operators`
--
ALTER TABLE `operators`
  MODIFY `operator_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `passengers`
--
ALTER TABLE `passengers`
  MODIFY `passenger_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=154;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `routes`
--
ALTER TABLE `routes`
  MODIFY `route_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `route_schedules`
--
ALTER TABLE `route_schedules`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `route_stops`
--
ALTER TABLE `route_stops`
  MODIFY `stop_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT for table `seats`
--
ALTER TABLE `seats`
  MODIFY `seat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=370;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `staff_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `ticket_access_tokens`
--
ALTER TABLE `ticket_access_tokens`
  MODIFY `token_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users_login_token`
--
ALTER TABLE `users_login_token`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `user_login_token`
--
ALTER TABLE `user_login_token`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `buses`
--
ALTER TABLE `buses`
  ADD CONSTRAINT `buses_ibfk_1` FOREIGN KEY (`operator_id`) REFERENCES `operators` (`operator_id`) ON DELETE SET NULL ON UPDATE CASCADE;

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
