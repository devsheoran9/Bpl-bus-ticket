-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 13, 2025 at 08:06 AM
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
(1, 'main_admin', '{\"all_access\": true}', '2025-09-13 09:54:05', '::1', '821436b3abac16301bb5a45c89341cdb6f31793ec7a71dbb09b622ac4ea42589', 'dev', '8930000210', 'admin@gmail.com', '$2y$12$F5HnNj16GzvkVuojDu/9Re/IeDjwwH4.flwKS5hX5FluIrlOlexC6', '123456', '1', '::1', '2025-07-23 13:36:33');

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
(88, 1, 'dev', 'login', '::1', '2025-09-13 04:24:05');

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
(163, 19, 25, 376, 158, '2025-09-13'),
(164, 19, 25, 372, 159, '2025-09-13');

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
(158, 'BPL141633653', 25, 19, NULL, 1, 'Bhiwani,hashi Gate', 'Juiii', NULL, 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-13', 300.00, 'PAID', NULL, 'CONFIRMED', '2025-09-13 05:48:28'),
(159, 'BPL036615668', 25, 19, NULL, 1, 'Bhiwani,hashi Gate', 'Juiii', NULL, 'rohitmechujaatji@gmail.com', '8905288939', '2025-09-13', 100.00, 'PENDING', NULL, 'CONFIRMED', '2025-09-13 06:00:50');

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
(20, 'Bus no 2', 'HR 61 B 2918', '234234234', '23423423423', 'Non-AC Seater', 0, 0, 0, NULL, 'Lorem, ipsum dolor sit amet consectetur adipisicing elit. Ea mollitia quam ipsa suscipit eum est illo, nostrum rerum accusamus vitae esse explicabo culpa libero repudiandae. Eaque vero modi, alias iste veniam laborum recusandae cupiditate corporis. Ea nostrum officia iste maxime, unde laboriosam non ratione itaque ipsam! Similique excepturi consequatur asperiores, accusantium numquam deleniti tempora?', 'Active', '2025-09-13 10:24:44', '2025-09-13 11:13:43');

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
(91, 19, 18);

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
(30, 20, 'bus_20_1757739284_68c4f91444007.jpg', '2025-09-13 04:54:44');

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
(163, 158, 376, 'LP5', 'Rohit', '', 22, 'MALE', 300.00, 'CONFIRMED'),
(164, 159, 372, 'LS1', 'rohit', '', 22, 'MALE', 100.00, 'CONFIRMED');

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
(24, 19, 'Delhi To Pilani', 'Delhi, kasmiri Gate', 'Pilani,Rj', 'Active', 0, '2025-09-13 05:06:23'),
(25, 19, 'Bhiwani to Badhra', 'Bhiwani,hashi Gate', 'Badhra', 'Active', 1, '2025-09-13 05:24:25'),
(26, 20, 'Loharu To Badhra', 'Loharu', 'Badhra', 'Active', 0, '2025-09-13 05:29:16'),
(27, 20, 'Loharu To Dadri', 'Loharu', 'Dadri', 'Active', 1, '2025-09-13 05:31:52');

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
(458, 20, 'US4', 'UPPER', 'SEATER', 210, 570, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-13 10:30:52', '2025-09-13 10:30:54');

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
(33, 159, '31c60129795ba36790b0f3fd71443997', '2025-09-13 06:00:58');

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
(26, 158, NULL, NULL, 'Razorpay', 'pay_RGya2aobcWkyQT', 'order_RGyZnNWuR4QZiw', '921f1c0a3cea9b4974a3279b1d944f8a0dd0b272c8953b51945504648aea1142', 300.00, 'INR', 'CAPTURED', 'online', NULL, NULL, '2025-09-13 05:48:57', '2025-09-13 05:48:57');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `admin_activity_log`
--
ALTER TABLE `admin_activity_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT for table `booked_seats`
--
ALTER TABLE `booked_seats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=165;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=160;

--
-- AUTO_INCREMENT for table `buses`
--
ALTER TABLE `buses`
  MODIFY `bus_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `bus_categories`
--
ALTER TABLE `bus_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `bus_category_map`
--
ALTER TABLE `bus_category_map`
  MODIFY `map_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

--
-- AUTO_INCREMENT for table `bus_images`
--
ALTER TABLE `bus_images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `cancellations`
--
ALTER TABLE `cancellations`
  MODIFY `cancellation_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cash_collections_log`
--
ALTER TABLE `cash_collections_log`
  MODIFY `collection_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- AUTO_INCREMENT for table `passengers`
--
ALTER TABLE `passengers`
  MODIFY `passenger_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=165;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `routes`
--
ALTER TABLE `routes`
  MODIFY `route_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `route_schedules`
--
ALTER TABLE `route_schedules`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT for table `route_stops`
--
ALTER TABLE `route_stops`
  MODIFY `stop_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99;

--
-- AUTO_INCREMENT for table `seats`
--
ALTER TABLE `seats`
  MODIFY `seat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=459;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `staff_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `ticket_access_tokens`
--
ALTER TABLE `ticket_access_tokens`
  MODIFY `token_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

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
