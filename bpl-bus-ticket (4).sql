-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Sep 15, 2025 at 07:38 AM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

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

DROP TABLE IF EXISTS `admin`;
CREATE TABLE IF NOT EXISTS `admin` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` varchar(20) NOT NULL DEFAULT 'employee',
  `permissions` text COMMENT 'JSON formatted permissions',
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
  `date_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `email` (`email`),
  KEY `id` (`id`),
  KEY `mobile` (`mobile`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `type`, `permissions`, `last_login_time`, `last_login_ip`, `session_token`, `name`, `mobile`, `email`, `password`, `password_salt`, `status`, `ip_address`, `date_time`) VALUES
(1, 'main_admin', '{\"all_access\": true}', '2025-09-13 09:54:05', '::1', '821436b3abac16301bb5a45c89341cdb6f31793ec7a71dbb09b622ac4ea42589', 'dev', '8930000210', 'admin@gmail.com', '$2y$12$F5HnNj16GzvkVuojDu/9Re/IeDjwwH4.flwKS5hX5FluIrlOlexC6', '123456', '1', '::1', '2025-07-23 13:36:33');

-- --------------------------------------------------------

--
-- Table structure for table `admin_activity_log`
--

DROP TABLE IF EXISTS `admin_activity_log`;
CREATE TABLE IF NOT EXISTS `admin_activity_log` (
  `log_id` int NOT NULL AUTO_INCREMENT,
  `admin_id` int NOT NULL,
  `admin_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `activity_type` enum('login','logout') COLLATE utf8mb4_general_ci NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `log_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  KEY `admin_id` (`admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=89 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(88, 1, 'dev', 'login', '::1', '2025-09-13 04:24:05');

-- --------------------------------------------------------

--
-- Table structure for table `booked_seats`
--

DROP TABLE IF EXISTS `booked_seats`;
CREATE TABLE IF NOT EXISTS `booked_seats` (
  `id` int NOT NULL AUTO_INCREMENT,
  `bus_id` int NOT NULL,
  `route_id` int NOT NULL,
  `seat_id` int NOT NULL,
  `booking_id` int NOT NULL,
  `travel_date` date NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `route_bus_seat_date` (`route_id`,`bus_id`,`seat_id`,`travel_date`)
) ENGINE=InnoDB AUTO_INCREMENT=165 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booked_seats`
--

INSERT INTO `booked_seats` (`id`, `bus_id`, `route_id`, `seat_id`, `booking_id`, `travel_date`) VALUES
(163, 19, 25, 376, 158, '2025-09-13'),
(164, 19, 25, 372, 159, '2025-09-13');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

DROP TABLE IF EXISTS `bookings`;
CREATE TABLE IF NOT EXISTS `bookings` (
  `booking_id` int NOT NULL AUTO_INCREMENT,
  `ticket_no` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `route_id` int NOT NULL,
  `bus_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `booked_by_employee_id` int DEFAULT NULL,
  `origin` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `destination` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `contact_name` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `contact_email` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `contact_mobile` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `travel_date` date NOT NULL,
  `total_fare` decimal(10,2) NOT NULL,
  `payment_status` enum('PAID','PENDING','FAILED','REFUNDED') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'PENDING',
  `gateway_order_id` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `booking_status` enum('CONFIRMED','CANCELLED','PENDING') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'CONFIRMED',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`booking_id`),
  UNIQUE KEY `ticket_no` (`ticket_no`),
  KEY `route_id` (`route_id`),
  KEY `bus_id` (`bus_id`)
) ENGINE=InnoDB AUTO_INCREMENT=167 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`booking_id`, `ticket_no`, `route_id`, `bus_id`, `user_id`, `booked_by_employee_id`, `origin`, `destination`, `contact_name`, `contact_email`, `contact_mobile`, `travel_date`, `total_fare`, `payment_status`, `gateway_order_id`, `booking_status`, `created_at`) VALUES
(158, 'BPL141633653', 25, 19, NULL, 1, 'Bhiwani,hashi Gate', 'Juiii', NULL, 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-13', 300.00, 'PAID', NULL, 'CONFIRMED', '2025-09-13 05:48:28'),
(159, 'BPL036615668', 25, 19, NULL, 1, 'Bhiwani,hashi Gate', 'Juiii', NULL, 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-13', 100.00, 'PENDING', NULL, 'CONFIRMED', '2025-09-13 06:00:50'),
(160, 'BPL104357921', 25, 19, 3, NULL, 'Bhiwani,hashi Gate', 'Badhra', 'Sanjay Kumar Sheoran', 'sjsheoran111@gmail.com', '9728833428', '2025-09-13', 200.00, 'PENDING', 'order_RGyvPEquCJR3uV', 'CONFIRMED', '2025-09-13 06:08:54'),
(161, 'BPL837525787', 25, 19, 4, NULL, 'Bhiwani,hashi Gate', 'Badhra', 'JSNJ INFOMEDIA', 'jsnjworkmail@gmail.com', '8989898989', '2025-09-13', 1000.00, 'PAID', 'order_RH0vdLTvU4bSGH', 'CONFIRMED', '2025-09-13 08:06:28'),
(162, 'BPL527400391', 25, 19, 4, NULL, 'Bhiwani,hashi Gate', 'Badhra', 'JSNJ INFOMEDIA', 'jsnjworkmail@gmail.com', '8989898989', '2025-09-13', 1000.00, 'PAID', 'order_RH15OSCbYurIEK', 'CONFIRMED', '2025-09-13 08:15:44'),
(163, 'BPL626854041', 25, 19, 4, NULL, 'Bhiwani,hashi Gate', 'Badhra', 'JSNJ INFOMEDIA', 'jsnjworkmail@gmail.com', '8989898989', '2025-09-13', 600.00, 'PAID', 'order_RH2mul33JpCBBT', 'CONFIRMED', '2025-09-13 09:55:36'),
(164, 'BPL072952098', 26, 20, 4, NULL, 'Loharu', 'Badhra', 'JSNJ INFOMEDIA', 'jsnjworkmail@gmail.com', '8989898989', '2025-09-14', 180.00, 'PAID', 'order_RH2tYbs86iLVjH', 'CONFIRMED', '2025-09-13 10:01:54'),
(165, 'BPL901921187', 25, 19, 3, NULL, 'Bhiwani,hashi Gate', 'Badhra', 'Sanjay Kumar Sheoran', 'sjsheoran111@gmail.com', '9728833428', '2025-09-15', 15600.00, 'PAID', 'order_RH5MK6UBgU1eJx', 'CONFIRMED', '2025-09-13 12:26:31'),
(166, 'BPL041777760', 25, 19, 5, NULL, 'Bhiwani,hashi Gate', 'Badhra', 'Rohit', 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-20', 200.00, 'PENDING', 'order_RHkMBv8LZGPINg', 'CONFIRMED', '2025-09-15 04:32:47');

-- --------------------------------------------------------

--
-- Table structure for table `buses`
--

DROP TABLE IF EXISTS `buses`;
CREATE TABLE IF NOT EXISTS `buses` (
  `bus_id` int NOT NULL AUTO_INCREMENT,
  `bus_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `registration_number` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `engine_no` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `chassis_no` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bus_type` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `total_seats` int NOT NULL DEFAULT '0',
  `seater_seats` int DEFAULT '0',
  `sleeper_seats` int DEFAULT '0',
  `amenities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `description` text COLLATE utf8mb4_general_ci,
  `status` enum('Active','Inactive','Under Maintenance','Retired') COLLATE utf8mb4_general_ci DEFAULT 'Active',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`bus_id`),
  UNIQUE KEY `registration_number` (`registration_number`),
  KEY `idx_bus_status` (`status`)
) ;

--
-- Dumping data for table `buses`
--

INSERT INTO `buses` (`bus_id`, `bus_name`, `registration_number`, `engine_no`, `chassis_no`, `bus_type`, `total_seats`, `seater_seats`, `sleeper_seats`, `amenities`, `description`, `status`, `created_at`, `updated_at`) VALUES
(19, 'Bus no 1', 'HR 61 B 2917', '53453454353453', '4535435435345', 'AC Seater', 0, 0, 0, NULL, 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Quisquam temporibus cupiditate est. Vitae delectus sit animi illo excepturi quidem magnam. Repudiandae dicta asperiores neque corrupti harum pariatur similique. Maiores veritatis sed, amet at consequuntur molestiae ad dolor eveniet culpa! Beatae tempore neque optio voluptate facilis vero quas, nulla asperiores, sit fugiat voluptas incidunt, ipsam in!', 'Active', '2025-09-13 10:12:29', '2025-09-13 11:13:54'),
(20, 'Bus no 2', 'HR 61 B 2918', '234234234', '23423423423', 'Non-AC Seater', 0, 0, 0, NULL, 'Lorem, ipsum dolor sit amet consectetur adipisicing elit. Ea mollitia quam ipsa suscipit eum est illo, nostrum rerum accusamus vitae esse explicabo culpa libero repudiandae. Eaque vero modi, alias iste veniam laborum recusandae cupiditate corporis. Ea nostrum officia iste maxime, unde laboriosam non ratione itaque ipsam! Similique excepturi consequatur asperiores, accusantium numquam deleniti tempora?', 'Active', '2025-09-13 10:24:44', '2025-09-13 11:13:43');

-- --------------------------------------------------------

--
-- Table structure for table `bus_categories`
--

DROP TABLE IF EXISTS `bus_categories`;
CREATE TABLE IF NOT EXISTS `bus_categories` (
  `category_id` int NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('Active','Inactive') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `category_name` (`category_name`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

DROP TABLE IF EXISTS `bus_category_map`;
CREATE TABLE IF NOT EXISTS `bus_category_map` (
  `map_id` int NOT NULL AUTO_INCREMENT,
  `bus_id` int NOT NULL,
  `category_id` int NOT NULL,
  PRIMARY KEY (`map_id`),
  KEY `bus_id` (`bus_id`),
  KEY `category_id` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=92 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(91, 19, 18);

-- --------------------------------------------------------

--
-- Table structure for table `bus_images`
--

DROP TABLE IF EXISTS `bus_images`;
CREATE TABLE IF NOT EXISTS `bus_images` (
  `image_id` int NOT NULL AUTO_INCREMENT,
  `bus_id` int NOT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`image_id`),
  KEY `bus_id` (`bus_id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bus_images`
--

INSERT INTO `bus_images` (`image_id`, `bus_id`, `image_path`, `created_at`) VALUES
(27, 19, 'bus_19_1757738549_68c4f6357a032.jpg', '2025-09-13 04:42:29'),
(28, 19, 'bus_19_1757738549_68c4f6357a277.jpg', '2025-09-13 04:42:29'),
(29, 20, 'bus_20_1757739284_68c4f91443e85.jpg', '2025-09-13 04:54:44'),
(30, 20, 'bus_20_1757739284_68c4f91444007.jpg', '2025-09-13 04:54:44');

-- --------------------------------------------------------

--
-- Table structure for table `cancellations`
--

DROP TABLE IF EXISTS `cancellations`;
CREATE TABLE IF NOT EXISTS `cancellations` (
  `cancellation_id` int NOT NULL AUTO_INCREMENT,
  `booking_id` int NOT NULL,
  `passenger_id` int NOT NULL,
  `amount_refunded` decimal(10,2) NOT NULL,
  `cancellation_reason` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `gateway_refund_id` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('COMPLETED','FAILED','PENDING') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'PENDING',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`cancellation_id`),
  KEY `booking_id` (`booking_id`),
  KEY `passenger_id` (`passenger_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cancellations`
--

INSERT INTO `cancellations` (`cancellation_id`, `booking_id`, `passenger_id`, `amount_refunded`, `cancellation_reason`, `gateway_refund_id`, `status`, `created_at`) VALUES
(1, 160, 166, 200.00, 'httt', NULL, 'COMPLETED', '2025-09-13 06:39:19'),
(2, 166, 214, 200.00, 'Medical Emergency', NULL, 'COMPLETED', '2025-09-15 07:10:29');

-- --------------------------------------------------------

--
-- Table structure for table `cash_collections_log`
--

DROP TABLE IF EXISTS `cash_collections_log`;
CREATE TABLE IF NOT EXISTS `cash_collections_log` (
  `collection_id` int NOT NULL AUTO_INCREMENT,
  `booking_id` int NOT NULL,
  `amount_collected` decimal(10,2) NOT NULL,
  `collected_by_admin_id` int NOT NULL,
  `collected_from_employee_id` int NOT NULL,
  `collection_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`collection_id`),
  UNIQUE KEY `booking_id` (`booking_id`),
  KEY `collected_by_admin_id` (`collected_by_admin_id`),
  KEY `collected_from_employee_id` (`collected_from_employee_id`)
) ENGINE=InnoDB AUTO_INCREMENT=90 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `passengers`
--

DROP TABLE IF EXISTS `passengers`;
CREATE TABLE IF NOT EXISTS `passengers` (
  `passenger_id` int NOT NULL AUTO_INCREMENT,
  `booking_id` int NOT NULL,
  `seat_id` int NOT NULL,
  `seat_code` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `passenger_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `passenger_mobile` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `passenger_age` int DEFAULT NULL,
  `passenger_gender` enum('MALE','FEMALE','OTHER') COLLATE utf8mb4_general_ci NOT NULL,
  `fare` decimal(10,2) NOT NULL,
  `passenger_status` enum('CONFIRMED','CANCELLED') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'CONFIRMED',
  PRIMARY KEY (`passenger_id`),
  KEY `booking_id` (`booking_id`)
) ENGINE=InnoDB AUTO_INCREMENT=215 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `passengers`
--

INSERT INTO `passengers` (`passenger_id`, `booking_id`, `seat_id`, `seat_code`, `passenger_name`, `passenger_mobile`, `passenger_age`, `passenger_gender`, `fare`, `passenger_status`) VALUES
(163, 158, 376, 'LP5', 'Rohit', '', 22, 'MALE', 300.00, 'CONFIRMED'),
(164, 159, 372, 'LS1', 'rohit', '', 22, 'MALE', 100.00, 'CONFIRMED'),
(165, 160, 389, 'LS5', 'asd', '', 33, 'FEMALE', 200.00, 'CONFIRMED'),
(166, 160, 385, 'LS3', 'asdasd', '', 23, 'MALE', 200.00, 'CANCELLED'),
(167, 161, 395, 'UP1', 'Sanjay', '', 26, 'MALE', 500.00, 'CONFIRMED'),
(168, 161, 396, 'UP2', 'Rohit', '', 19, 'MALE', 500.00, 'CONFIRMED'),
(169, 162, 398, 'UP4', 'dfgdf', '', 45, 'MALE', 500.00, 'CONFIRMED'),
(170, 162, 399, 'UP5', 'fgdf', '', 54, 'FEMALE', 500.00, 'CONFIRMED'),
(171, 163, 375, 'LP4', 'sadasd', '', 3, 'MALE', 400.00, 'CONFIRMED'),
(172, 163, 385, 'LS3', 'afdsfd', '', 34, 'MALE', 200.00, 'CONFIRMED'),
(173, 164, 413, 'LP1', 'fdd', '', 4, 'MALE', 90.00, 'CONFIRMED'),
(174, 164, 414, 'LP2', 'dfg', '', 5, 'FEMALE', 90.00, 'CONFIRMED'),
(175, 165, 374, 'LP3', 'rfwe', '', 4, 'FEMALE', 400.00, 'CONFIRMED'),
(176, 165, 381, 'LP10', '342', '', 34, 'FEMALE', 400.00, 'CONFIRMED'),
(177, 165, 382, 'LP11', '324', '', 34, 'FEMALE', 400.00, 'CONFIRMED'),
(178, 165, 371, 'LP2', '23432', '', 43, 'FEMALE', 400.00, 'CONFIRMED'),
(179, 165, 370, 'LP1', '432', '', 43, 'FEMALE', 400.00, 'CONFIRMED'),
(180, 165, 373, 'LS2', '434', '', 3, 'MALE', 200.00, 'CONFIRMED'),
(181, 165, 372, 'LS1', '4323', '', 43, 'MALE', 200.00, 'CONFIRMED'),
(182, 165, 389, 'LS5', '34', '', 43, 'FEMALE', 200.00, 'CONFIRMED'),
(183, 165, 385, 'LS3', '343', '', 34, 'MALE', 200.00, 'CONFIRMED'),
(184, 165, 376, 'LP5', '343', '', 43, 'MALE', 400.00, 'CONFIRMED'),
(185, 165, 375, 'LP4', '434', '', 43, 'MALE', 400.00, 'CONFIRMED'),
(186, 165, 378, 'LP7', '343', '', 43, 'FEMALE', 400.00, 'CONFIRMED'),
(187, 165, 377, 'LP6', '34', '', 34, 'FEMALE', 400.00, 'CONFIRMED'),
(188, 165, 379, 'LP8', '34', '', 43, 'OTHER', 400.00, 'CONFIRMED'),
(189, 165, 380, 'LP9', '3', '', 34, 'MALE', 400.00, 'CONFIRMED'),
(190, 165, 383, 'LP12', '343', '', 43, 'FEMALE', 400.00, 'CONFIRMED'),
(191, 165, 384, 'LP13', '344', '', 4, 'MALE', 400.00, 'CONFIRMED'),
(192, 165, 391, 'LS6', '43', '', 43, 'FEMALE', 200.00, 'CONFIRMED'),
(193, 165, 393, 'LS8', '434', '', 43, 'MALE', 200.00, 'CONFIRMED'),
(194, 165, 392, 'LS7', '34', '', 43, 'MALE', 200.00, 'CONFIRMED'),
(195, 165, 411, 'UP17', '343', '', 43, 'OTHER', 500.00, 'CONFIRMED'),
(196, 165, 412, 'UP18', '343', '', 43, 'MALE', 500.00, 'CONFIRMED'),
(197, 165, 408, 'UP14', '43', '', 43, 'FEMALE', 500.00, 'CONFIRMED'),
(198, 165, 407, 'UP13', '3', '', 34, 'MALE', 500.00, 'CONFIRMED'),
(199, 165, 404, 'UP10', '43', '', 43, 'MALE', 500.00, 'CONFIRMED'),
(200, 165, 405, 'UP11', '34', '', 43, 'MALE', 500.00, 'CONFIRMED'),
(201, 165, 401, 'UP7', '34', '', 43, 'MALE', 500.00, 'CONFIRMED'),
(202, 165, 402, 'UP8', '43', '', 43, 'MALE', 500.00, 'CONFIRMED'),
(203, 165, 399, 'UP5', '43', '', 34, 'MALE', 500.00, 'CONFIRMED'),
(204, 165, 398, 'UP4', '34', '', 43, 'OTHER', 500.00, 'CONFIRMED'),
(205, 165, 395, 'UP1', '43', '', 34, 'FEMALE', 500.00, 'CONFIRMED'),
(206, 165, 396, 'UP2', '34', '', 4, 'FEMALE', 500.00, 'CONFIRMED'),
(207, 165, 397, 'UP3', '34', '', 43, 'MALE', 500.00, 'CONFIRMED'),
(208, 165, 400, 'UP6', '3', '', 43, 'MALE', 500.00, 'CONFIRMED'),
(209, 165, 403, 'UP9', '43', '', 4, 'MALE', 500.00, 'CONFIRMED'),
(210, 165, 406, 'UP12', '43', '', 43, 'MALE', 500.00, 'CONFIRMED'),
(211, 165, 409, 'UP15', '43', '', 43, 'FEMALE', 500.00, 'CONFIRMED'),
(212, 165, 410, 'UP16', '34', '', 4, 'MALE', 500.00, 'CONFIRMED'),
(213, 166, 385, 'LS3', 'Rohit', '', 18, 'MALE', 200.00, 'CONFIRMED'),
(214, 166, 389, 'LS5', 'Rohiti', '', 19, 'FEMALE', 200.00, 'CANCELLED');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `user_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `mobile` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `rating` int NOT NULL COMMENT 'Rating from 1 to 5',
  `review_text` text COLLATE utf8mb4_general_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 = Active/Approved, 0 = Pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `user_id`, `user_name`, `email`, `mobile`, `rating`, `review_text`, `status`, `created_at`) VALUES
(2, 3, 'Sanjay Kumar Sheoran', 'sjsheoran111@gmail.com', '9728833428', 5, 'nnjffn  n klnkl nbkln  np nil  ln n pnsp nsdop f', 1, '2025-09-13 10:35:27');

-- --------------------------------------------------------

--
-- Table structure for table `routes`
--

DROP TABLE IF EXISTS `routes`;
CREATE TABLE IF NOT EXISTS `routes` (
  `route_id` int NOT NULL AUTO_INCREMENT,
  `bus_id` int NOT NULL,
  `route_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `starting_point` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `ending_point` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('Active','Inactive') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Active',
  `is_popular` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`route_id`),
  KEY `bus_id` (`bus_id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `routes`
--

INSERT INTO `routes` (`route_id`, `bus_id`, `route_name`, `starting_point`, `ending_point`, `status`, `is_popular`, `created_at`) VALUES
(24, 19, 'Delhi To Pilani', 'Delhi, kasmiri Gate', 'Pilani,Rj', 'Active', 0, '2025-09-13 05:06:23'),
(25, 19, 'Bhiwani to Badhra', 'Bhiwani,hashi Gate', 'Badhra', 'Active', 1, '2025-09-13 05:24:25'),
(26, 20, 'Loharu To Badhra', 'Loharu', 'Badhra', 'Active', 0, '2025-09-13 05:29:16'),
(27, 20, 'Loharu To Dadri', 'Loharu', 'Dadri', 'Active', 1, '2025-09-13 05:31:52');

-- --------------------------------------------------------

--
-- Table structure for table `route_schedules`
--

DROP TABLE IF EXISTS `route_schedules`;
CREATE TABLE IF NOT EXISTS `route_schedules` (
  `schedule_id` int NOT NULL AUTO_INCREMENT,
  `route_id` int NOT NULL,
  `operating_day` varchar(10) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'e.g., Mon, Tue, Sun',
  `departure_time` time NOT NULL,
  PRIMARY KEY (`schedule_id`),
  KEY `route_id` (`route_id`)
) ENGINE=InnoDB AUTO_INCREMENT=75 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `route_schedules`
--

INSERT INTO `route_schedules` (`schedule_id`, `route_id`, `operating_day`, `departure_time`) VALUES
(58, 24, 'Mon', '12:00:00'),
(59, 24, 'Tue', '12:00:00'),
(60, 24, 'Wed', '12:30:00'),
(61, 25, 'Mon', '14:00:00'),
(62, 25, 'Tue', '14:30:00'),
(63, 25, 'Thu', '15:00:00'),
(64, 25, 'Sat', '15:30:00'),
(65, 26, 'Mon', '14:00:00'),
(66, 26, 'Wed', '15:00:00'),
(67, 26, 'Fri', '16:00:00'),
(68, 26, 'Sun', '17:00:00'),
(73, 27, 'Mon', '21:00:00'),
(74, 27, 'Wed', '21:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `route_staff_assignments`
--

DROP TABLE IF EXISTS `route_staff_assignments`;
CREATE TABLE IF NOT EXISTS `route_staff_assignments` (
  `assignment_id` int DEFAULT NULL,
  `route_id` int NOT NULL COMMENT 'Foreign key to the routes table',
  `staff_id` int NOT NULL COMMENT 'Foreign key to the staff table',
  `role` varchar(100) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'e.g., Driver, Co-Driver, Conductor, Helper'
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
(NULL, 15, 38, 'Helper'),
(NULL, 24, 43, 'Driver'),
(NULL, 24, 44, 'Co-Driver'),
(NULL, 24, 48, 'Conductor'),
(NULL, 24, 46, 'Helper'),
(NULL, 24, 45, 'Helper'),
(NULL, 25, 43, 'Driver'),
(NULL, 25, 39, 'Conductor'),
(NULL, 25, 46, 'Helper'),
(NULL, 25, 45, 'Helper'),
(NULL, 26, 44, 'Driver'),
(NULL, 26, 49, 'Co-Driver'),
(NULL, 26, 48, 'Conductor'),
(NULL, 27, 49, 'Driver'),
(NULL, 27, 39, 'Conductor'),
(NULL, 27, 46, 'Helper'),
(NULL, 27, 45, 'Helper');

-- --------------------------------------------------------

--
-- Table structure for table `route_stops`
--

DROP TABLE IF EXISTS `route_stops`;
CREATE TABLE IF NOT EXISTS `route_stops` (
  `stop_id` int NOT NULL AUTO_INCREMENT,
  `route_id` int NOT NULL,
  `stop_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `stop_order` int NOT NULL,
  `duration_from_start_minutes` int DEFAULT '0',
  `price_seater_lower` decimal(10,2) DEFAULT NULL,
  `price_seater_upper` decimal(10,2) DEFAULT NULL,
  `price_sleeper_lower` decimal(10,2) DEFAULT NULL,
  `price_sleeper_upper` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`stop_id`),
  KEY `route_id` (`route_id`)
) ENGINE=InnoDB AUTO_INCREMENT=99 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `route_stops`
--

INSERT INTO `route_stops` (`stop_id`, `route_id`, `stop_name`, `stop_order`, `duration_from_start_minutes`, `price_seater_lower`, `price_seater_upper`, `price_sleeper_lower`, `price_sleeper_upper`) VALUES
(82, 24, 'Rohtak, Purana Bus Stand', 1, 60, 200.00, 300.00, 400.00, 500.00),
(83, 24, 'Loharu,Haryana', 2, 120, 300.00, 400.00, 500.00, 600.00),
(84, 24, 'Pilani,Rj', 3, 180, 400.00, 500.00, 600.00, 700.00),
(85, 25, 'Juiii', 1, 60, 100.00, 200.00, 300.00, 400.00),
(86, 25, 'Badhra', 2, 120, 200.00, 300.00, 400.00, 500.00),
(87, 26, 'Basirwas', 1, 30, 50.00, 100.00, 150.00, 200.00),
(88, 26, 'Laad', 2, 40, 60.00, 70.00, 80.00, 90.00),
(89, 26, 'Badhra', 3, 50, 70.00, 80.00, 90.00, 100.00),
(96, 27, 'Laad', 1, 20, 10.00, 20.00, 30.00, 40.00),
(97, 27, 'Badhra', 2, 40, 20.00, 30.00, 40.00, 50.00),
(98, 27, 'Dadri', 3, 60, 30.00, 40.00, 50.00, 60.00);

-- --------------------------------------------------------

--
-- Table structure for table `seats`
--

DROP TABLE IF EXISTS `seats`;
CREATE TABLE IF NOT EXISTS `seats` (
  `seat_id` int NOT NULL AUTO_INCREMENT,
  `bus_id` int NOT NULL,
  `seat_code` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `deck` enum('LOWER','UPPER') COLLATE utf8mb4_general_ci NOT NULL,
  `seat_type` enum('SEATER','SLEEPER','DRIVER','AISLE','TOILET','GANGWAY') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'SEATER',
  `x_coordinate` int NOT NULL,
  `y_coordinate` int NOT NULL,
  `width` int NOT NULL DEFAULT '40',
  `height` int NOT NULL DEFAULT '40',
  `orientation` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `base_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `gender_preference` enum('ANY','MALE','FEMALE') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'ANY',
  `is_bookable` tinyint(1) NOT NULL DEFAULT '1',
  `status` enum('AVAILABLE','DAMAGED','BLOCKED') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'AVAILABLE',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`seat_id`),
  UNIQUE KEY `bus_id` (`bus_id`,`seat_code`),
  KEY `idx_seats_bus_id` (`bus_id`),
  KEY `idx_seats_deck` (`deck`),
  KEY `idx_seats_is_bookable` (`is_bookable`)
) ENGINE=InnoDB AUTO_INCREMENT=459 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(458, 20, 'US4', 'UPPER', 'SEATER', 210, 570, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:30:52', '2025-09-13 10:30:54');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

DROP TABLE IF EXISTS `staff`;
CREATE TABLE IF NOT EXISTS `staff` (
  `staff_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `mobile` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `designation` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `driving_licence_no` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `aadhar_no` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `profile_image_path` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `remark` text COLLATE utf8mb4_general_ci,
  `status` enum('Active','Inactive') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`staff_id`),
  UNIQUE KEY `mobile` (`mobile`),
  UNIQUE KEY `driving_licence_no` (`driving_licence_no`),
  UNIQUE KEY `aadhar_no` (`aadhar_no`)
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

DROP TABLE IF EXISTS `ticket_access_tokens`;
CREATE TABLE IF NOT EXISTS `ticket_access_tokens` (
  `token_id` int NOT NULL AUTO_INCREMENT,
  `booking_id` int NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`token_id`),
  UNIQUE KEY `token` (`token`),
  KEY `booking_id` (`booking_id`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ticket_access_tokens`
--

INSERT INTO `ticket_access_tokens` (`token_id`, `booking_id`, `token`, `created_at`) VALUES
(32, 158, 'cbcb3b93b3244efdf0f953f2e3f42229', '2025-09-13 05:53:05'),
(33, 159, '31c60129795ba36790b0f3fd71443997', '2025-09-13 06:00:58'),
(34, 160, '9cfdc12335e84b80993d9b975390e806a3efac7f', '2025-09-13 06:14:37'),
(35, 161, '4105bc09ce43c7acbe82b2c1bcef5aa326334591', '2025-09-13 08:07:40'),
(36, 162, '4f21312f429dae8f2d61e725b02c187a97579f05', '2025-09-13 08:16:14'),
(37, 163, 'c27afce1587768492b76bf0a39a9bf857a179f92', '2025-09-13 09:56:53'),
(38, 164, '874b89aa0068f26ef56b0febd3098be98f5e7077', '2025-09-13 10:02:18'),
(39, 165, 'caa0f858f079b994feead59f08cc15d1b1386245', '2025-09-13 12:26:57'),
(40, 166, 'e9010cf6a8b30e00bdd1fe404d985b5d9bc0b9bd', '2025-09-15 04:33:22');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
CREATE TABLE IF NOT EXISTS `transactions` (
  `transaction_id` int NOT NULL AUTO_INCREMENT,
  `booking_id` int NOT NULL,
  `user_id` int DEFAULT NULL COMMENT 'The user/customer who paid, if logged in',
  `employee_id` int DEFAULT NULL COMMENT 'The employee who processed the booking',
  `payment_gateway` varchar(50) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Razorpay',
  `gateway_payment_id` varchar(255) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'e.g., razorpay_payment_id',
  `gateway_order_id` varchar(255) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'e.g., razorpay_order_id',
  `gateway_signature` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'e.g., razorpay_signature for verification',
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(10) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'INR',
  `payment_status` enum('CREATED','AUTHORIZED','CAPTURED','REFUNDED','FAILED') COLLATE utf8mb4_general_ci NOT NULL,
  `method` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'e.g., card, netbanking, upi',
  `error_code` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `error_description` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`transaction_id`),
  KEY `booking_id` (`booking_id`),
  KEY `gateway_payment_id` (`gateway_payment_id`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `booking_id`, `user_id`, `employee_id`, `payment_gateway`, `gateway_payment_id`, `gateway_order_id`, `gateway_signature`, `amount`, `currency`, `payment_status`, `method`, `error_code`, `error_description`, `created_at`, `updated_at`) VALUES
(26, 158, NULL, NULL, 'Razorpay', 'pay_RGya2aobcWkyQT', 'order_RGyZnNWuR4QZiw', '921f1c0a3cea9b4974a3279b1d944f8a0dd0b272c8953b51945504648aea1142', 300.00, 'INR', 'CAPTURED', 'online', NULL, NULL, '2025-09-13 05:48:57', '2025-09-13 05:48:57'),
(27, 160, 3, NULL, 'Razorpay', 'pay_RGyvausGhTlp8n', 'order_RGyvPEquCJR3uV', '069f7456ab386924ecc5f84406567131625c3a9867615c5bf66e3b30c49e2aab', 400.00, 'INR', 'CAPTURED', 'online', NULL, NULL, '2025-09-13 06:09:20', '2025-09-13 06:09:20'),
(28, 161, 4, NULL, 'Razorpay', 'pay_RH0vo59bpGtgsh', 'order_RH0vdLTvU4bSGH', '45c9b35d18c920f0de10bd9b033b2ee52e035a71aef64ced24b9d1006216c345', 1000.00, 'INR', 'CAPTURED', 'online', NULL, NULL, '2025-09-13 08:06:55', '2025-09-13 08:06:55'),
(29, 162, 4, NULL, 'Razorpay', 'pay_RH15dmD75Izxa4', 'order_RH15OSCbYurIEK', 'adf64c983882627a7c0bfe04aa620bc2ea5e473aea690bf6596a309961aa6e4d', 1000.00, 'INR', 'CAPTURED', 'online', NULL, NULL, '2025-09-13 08:16:13', '2025-09-13 08:16:13'),
(30, 163, 4, NULL, 'Razorpay', 'pay_RH2nyAdSe3tR9X', 'order_RH2mul33JpCBBT', '54ec566938e0f4b90921632374943918e53b77009f16302ce677b6e521576092', 600.00, 'INR', 'CAPTURED', 'online', NULL, NULL, '2025-09-13 09:56:53', '2025-09-13 09:56:53'),
(31, 164, 4, NULL, 'Razorpay', 'pay_RH2tgYSaqu8mDQ', 'order_RH2tYbs86iLVjH', 'ed9ffa954f4780923408ba18a7a090a8e10fee6731577a9f28d76b0b36867cf6', 180.00, 'INR', 'CAPTURED', 'online', NULL, NULL, '2025-09-13 10:02:18', '2025-09-13 10:02:18'),
(32, 165, 3, NULL, 'Razorpay', 'pay_RH5MV1daPVwlhO', 'order_RH5MK6UBgU1eJx', 'fe6a4ef2159126f486331241e68fb1e635111ce966d4e95b0dd612ce749762c4', 15600.00, 'INR', 'CAPTURED', 'online', NULL, NULL, '2025-09-13 12:26:57', '2025-09-13 12:26:57'),
(33, 166, 5, NULL, 'Razorpay', 'pay_RHkMUBjDA83JgC', 'order_RHkMBv8LZGPINg', 'cd6ae8cd937b32c209aec447dd48621f64ff50fe6d2721681a8f5e5074e8f79a', 400.00, 'INR', 'CAPTURED', 'online', NULL, NULL, '2025-09-15 04:33:22', '2025-09-15 04:33:22');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `mobile_no` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ip_address` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1=Active, 2=Deactivated',
  `otp` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `otp_expires_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `mobile_no`, `email`, `ip_address`, `status`, `otp`, `otp_expires_at`, `created_at`) VALUES
(3, 'Sanjay Kumar Sheoran', '$2y$10$z7lBSp5NypMVF1S05ZvNeui70bCZceCut.xYsUwAGecNIoqck5DO6', '9728833428', 'sjsheoran111@gmail.com', '::1', 1, NULL, NULL, '2025-09-11 11:49:54'),
(4, 'JSNJ INFOMEDIA', '$2y$10$Iw8K/JFn4B2KnNUyK/rCruXf3NZS8fH1/vfmEZuAdnCmG.cgMEtb.', '8989898989', 'jsnjworkmail@gmail.com', '::1', 1, NULL, NULL, '2025-09-13 13:36:28'),
(5, 'Rohit', '$2y$10$YO47jehFVG8wwqqBqFCSi.iud.2BwSvF2N9Js2Zv05j7RwtOQu0.6', '8905288939', 'rohitmechujaatji@gmail.com', '::1', 1, NULL, NULL, '2025-09-15 10:02:47');

-- --------------------------------------------------------

--
-- Table structure for table `users_login_token`
--

DROP TABLE IF EXISTS `users_login_token`;
CREATE TABLE IF NOT EXISTS `users_login_token` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `status` varchar(1) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '1' COMMENT '1=active, 2=logout',
  `date_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_address` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users_login_token`
--

INSERT INTO `users_login_token` (`id`, `user_id`, `token`, `status`, `date_time`, `ip_address`) VALUES
(24, 3, 'f65fe605ee9cde585b566d63082016593221748c39dcd7593a73855d654f73d0', '2', '2025-09-13 06:08:33', '::1'),
(25, 4, 'f98f2eed42d9aac22ac629c0ebc6c0376fc0f052704a82a957e5d720ef5e0742', '2', '2025-09-13 08:07:17', '::1'),
(26, 4, 'fed8750c1d61bb7677105238a3c640cab03d94989ce0d068f57ed2e6c850daca', '2', '2025-09-13 08:10:49', '::1'),
(27, 4, '2c5b36ded624bc1406a89c0d70347d874e8bd9039598dc53370430a1e35674eb', '2', '2025-09-13 08:15:27', '::1'),
(28, 4, '96b3d7015f2b86315a266b840121420bc753b2fd13fd82516d01c17efafe27f7', '2', '2025-09-13 09:48:46', '::1'),
(29, 3, '280f06834ad71d6fad6cdb2bfa734e37ae605ddd34ca6ac8248f34de444871f1', '2', '2025-09-13 10:26:06', '::1'),
(30, 3, '57f4ad9f4df27969fe448b93ab53eec79d9f9c013d2c6eab1b73c6588ef25105', '2', '2025-09-15 04:29:12', '::1'),
(31, 5, '385c7919f850852b9a9b5127b9068eb1177871684a99e163780941820fc14cc9', '1', '2025-09-15 06:26:49', '::1'),
(32, 3, 'adc3686f191f0d7e04f4d0f7ecfbd7539b48a6a2eb630090b2d6cc5cd66b97fe', '1', '2025-09-15 07:08:13', '::1');

-- --------------------------------------------------------

--
-- Table structure for table `user_login_token`
--

DROP TABLE IF EXISTS `user_login_token`;
CREATE TABLE IF NOT EXISTS `user_login_token` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` varchar(110) NOT NULL,
  `token` varchar(110) NOT NULL,
  `status` varchar(2) DEFAULT '1' COMMENT '1 =active, 2 logout',
  `date_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_address` varchar(110) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=47 DEFAULT CHARSET=latin1;

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
