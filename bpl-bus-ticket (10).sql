-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 09, 2025 at 07:23 AM
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
(1, 'main_admin', '{\"all_access\": true}', '2025-09-09 10:08:24', '::1', 'bc9668ca087e4ca987f29025e45dc19629d4b630890de2ae4c9a585d5000cf89', 'dev', '8930000210', 'admin@gmail.com', '$2y$12$F5HnNj16GzvkVuojDu/9Re/IeDjwwH4.flwKS5hX5FluIrlOlexC6', '123456', '1', '::1', '2025-07-23 13:36:33'),
(4, 'employee', '{\"can_manage_operators\":true,\"can_manage_buses\":true,\"can_manage_routes\":true}', '2025-09-06 10:04:26', '::1', NULL, 'Rohit', '8905288939', 'rohitmechujaatji@gmail.com', '$2y$10$eNyHEAGHfbiVS0d2sk1oneFu4PviTSyMDKZjOrb9JFo4fKV.x7VIa', '123456', '1', NULL, '2025-09-06 04:34:08');

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
(47, 1, 'dev', 'login', '::1', '2025-09-09 04:38:24');

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
(24, 11, 12, 286, 30, '2025-09-08'),
(25, 11, 12, 296, 30, '2025-09-08'),
(26, 11, 13, 288, 31, '2025-09-08'),
(27, 11, 12, 297, 32, '2025-09-08'),
(28, 11, 13, 286, 33, '2025-09-08'),
(29, 11, 13, 289, 34, '2025-09-08'),
(30, 11, 13, 287, 35, '2025-09-08'),
(31, 11, 13, 296, 36, '2025-09-08'),
(32, 11, 13, 293, 37, '2025-09-08'),
(33, 11, 13, 289, 38, '2025-09-15'),
(34, 11, 13, 288, 39, '2025-09-15'),
(35, 11, 13, 286, 39, '2025-09-15'),
(36, 11, 13, 290, 39, '2025-09-15'),
(37, 11, 13, 294, 39, '2025-09-15'),
(38, 11, 12, 296, 40, '2025-09-15'),
(39, 11, 12, 297, 40, '2025-09-15'),
(40, 11, 12, 298, 40, '2025-09-15'),
(41, 11, 12, 292, 40, '2025-09-15'),
(42, 11, 12, 286, 40, '2025-09-15');

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
  `contact_email` varchar(255) DEFAULT NULL,
  `contact_mobile` varchar(20) DEFAULT NULL,
  `travel_date` date NOT NULL,
  `total_fare` decimal(10,2) NOT NULL,
  `payment_status` enum('PAID','PENDING','FAILED','REFUNDED') NOT NULL DEFAULT 'PENDING',
  `booking_status` enum('CONFIRMED','CANCELLED','PENDING') NOT NULL DEFAULT 'CONFIRMED',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`booking_id`, `ticket_no`, `route_id`, `bus_id`, `user_id`, `booked_by_employee_id`, `origin`, `destination`, `contact_email`, `contact_mobile`, `travel_date`, `total_fare`, `payment_status`, `booking_status`, `created_at`) VALUES
(30, 'BPL342244206', 12, 11, NULL, 1, 'Delhi, kasmiri Gate', 'Rohtak, Purana Bus Stand', 'rohit@gmail.com', '5435435435', '2025-09-08', 700.00, 'PENDING', 'CONFIRMED', '2025-09-08 10:27:34'),
(31, 'BPL900784888', 13, 11, NULL, 1, 'Pilani', 'Rohtak, Purana Bus Stand', 'rohit@gmail.com', '5435435435', '2025-09-08', 500.00, 'PENDING', 'CONFIRMED', '2025-09-08 10:36:35'),
(32, 'BPL286079242', 12, 11, NULL, 1, 'Rohtak, Purana Bus Stand', 'Pilani', 'rohit@gmail.com', '5435435435', '2025-09-08', 200.00, 'PENDING', 'CONFIRMED', '2025-09-08 10:41:42'),
(33, 'BPL152189490', 13, 11, NULL, 1, 'Pilani', 'Loharu', '', '', '2025-09-08', 200.00, 'PENDING', 'CONFIRMED', '2025-09-08 10:43:35'),
(34, 'BPL714032562', 13, 11, NULL, 1, 'Loharu', 'Rohtak, Purana Bus Stand', 'rohit@gmail.com', '5435435435', '2025-09-08', 100.00, 'PENDING', 'CONFIRMED', '2025-09-08 10:44:42'),
(35, 'BPL791226165', 13, 11, NULL, 1, 'Pilani', 'Loharu', 'rohit@gmail.com', '5435435435', '2025-09-08', 200.00, 'PENDING', 'CONFIRMED', '2025-09-08 11:06:50'),
(36, 'BPL805400311', 13, 11, NULL, 1, 'Loharu', 'Rohtak, Purana Bus Stand', 'rohit@gmail.com', '5435435435', '2025-09-08', 100.00, 'PENDING', 'CONFIRMED', '2025-09-08 11:29:21'),
(37, 'BPL704393330', 13, 11, NULL, 1, 'Pilani', 'Rohtak, Purana Bus Stand', '', '323232', '2025-09-08', 500.00, 'PENDING', 'CONFIRMED', '2025-09-08 11:54:03'),
(38, 'BPL257691397', 13, 11, NULL, 1, 'Loharu', 'Rohtak, Purana Bus Stand', 'rohit@gmail.com', '8905288939', '2025-09-15', 100.00, 'PENDING', 'CONFIRMED', '2025-09-09 04:40:16'),
(39, 'BPL541604615', 13, 11, NULL, 1, 'Loharu', 'Rohtak, Purana Bus Stand', 'rohit@gmail.com', '5435435435', '2025-09-15', 400.00, 'PENDING', 'CONFIRMED', '2025-09-09 04:43:21'),
(40, 'BPL973597332', 12, 11, NULL, 1, 'Delhi, kasmiri Gate', 'Loharu', 'rohit@gmail.com', '8905288939', '2025-09-15', 2100.00, 'PENDING', 'CONFIRMED', '2025-09-09 05:21:16');

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
(11, 'Shanti Express', 'HR 61 B 29173', 2, 'AC Seater', 0, 0, 0, NULL, 'gfdgf', 'Active', '2025-09-08 15:23:02', '2025-09-09 10:19:57'),
(12, 'HR 19 B 6566', 'HR 19 B 6566', 4, 'Non-AC Seater', 0, 0, 0, NULL, 'd', 'Active', '2025-09-08 15:25:30', '2025-09-08 15:25:30');

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
(5, 'fds', 'Active', '2025-09-03 12:00:38'),
(6, '4534432443242', 'Active', '2025-09-04 04:29:30');

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
(33, 12, 4),
(34, 12, 2),
(35, 12, 3),
(36, 11, 4),
(37, 11, 2),
(38, 11, 1);

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
(12, 12, 'bus_12_1757325330_68bea81293ec2.jpg', '2025-09-08 09:55:30');

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
(1, 'Sharma Travels', 'Rahul Sharma', '4342342@GMAIL.COM', '32342432', '432523', 'Active', '2025-09-02 15:32:55', '2025-09-06 11:58:31'),
(2, 'Shanti Express', 'Priya Singh', '4342342@GMAIL.COM', '32342432', '34534', 'Active', '2025-09-02 15:32:55', '2025-09-06 11:58:19'),
(3, 'Royal Roadways', 'Amit Kumar', '32@gmail.com', '32342432', '', 'Active', '2025-09-02 15:32:55', '2025-09-06 11:57:56'),
(4, '32', '32', '32@gmail.com', '32', 'fsdfd', 'Active', '2025-09-04 13:10:30', '2025-09-04 13:10:30');

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
  `fare` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `passengers`
--

INSERT INTO `passengers` (`passenger_id`, `booking_id`, `seat_id`, `seat_code`, `passenger_name`, `passenger_mobile`, `passenger_age`, `passenger_gender`, `fare`) VALUES
(24, 30, 286, 'LP1', 're', '4343443', 23, 'MALE', 300.00),
(25, 30, 296, 'UP1', 'dfd', '434343243', 43, 'MALE', 400.00),
(26, 31, 288, 'LS1', 'Rohit', '8905288939', 21, 'MALE', 500.00),
(27, 32, 297, 'UP2', 'Rohit', '896786776', 28, 'MALE', 200.00),
(28, 33, 286, 'LP1', 're', '4343443', 23, 'MALE', 200.00),
(29, 34, 289, 'LS2', '4343', 'rfdrer', 3455, 'MALE', 100.00),
(30, 35, 287, 'LP2', '543534', '345345345', 45, 'MALE', 200.00),
(31, 36, 296, 'UP1', 'dfd', '6654645', 65, 'MALE', 100.00),
(32, 37, 293, 'LS4', '32112', '546456456', 23, 'MALE', 500.00),
(33, 38, 289, 'LS2', 'Rohit', '1234567890', 22, 'MALE', 100.00),
(34, 39, 288, 'LS1', 'Rohit', '8905288939', 24, 'MALE', 100.00),
(35, 39, 286, 'LP1', 'sanjay', '436534534543', 23, 'MALE', 100.00),
(36, 39, 290, 'LP3', '2342423', '243423423432', 33, 'MALE', 100.00),
(37, 39, 294, 'LP5', '4324234234', '4234234234', 23, 'MALE', 100.00),
(38, 40, 296, 'UP1', 'dfd', '6654645', 3, 'MALE', 500.00),
(39, 40, 297, 'UP2', '4234', '4234234', 43, 'MALE', 500.00),
(40, 40, 298, 'UP3', '4234', '234234', 43, 'MALE', 500.00),
(41, 40, 292, 'LS3', '432423', '443242', 43, 'MALE', 200.00),
(42, 40, 286, 'LP1', '42342343', '436534534543', 43, 'MALE', 400.00);

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
(13, 11, 'Pilani To Delhi', 'Pilani', 'Rohtak, Purana Bus Stand', 'Active', 0, '2025-09-08 09:59:57');

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
(34, 13, 'Mon', '00:00:00');

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
(64, 13, 'Rohtak, Purana Bus Stand', 2, 120, 500.00, 400.00, 300.00, 200.00);

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
(288, 11, 'LS1', 'LOWER', 'SEATER', 90, 60, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-08 15:23:28', '2025-09-08 15:23:35'),
(289, 11, 'LS2', 'LOWER', 'SEATER', 30, 60, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-08 15:23:30', '2025-09-08 15:23:36'),
(290, 11, 'LP3', 'LOWER', 'SLEEPER', 160, 160, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-08 15:23:41', '2025-09-08 15:23:42'),
(291, 11, 'LP4', 'LOWER', 'SLEEPER', 220, 160, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-08 15:23:45', '2025-09-08 15:23:46'),
(292, 11, 'LS3', 'LOWER', 'SEATER', 90, 120, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-08 15:23:50', '2025-09-08 15:23:51'),
(293, 11, 'LS4', 'LOWER', 'SEATER', 30, 120, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-08 15:23:54', '2025-09-08 15:23:55'),
(294, 11, 'LP5', 'LOWER', 'SLEEPER', 90, 180, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-08 15:23:58', '2025-09-08 15:23:59'),
(295, 11, 'LP6', 'LOWER', 'SLEEPER', 30, 180, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-08 15:24:02', '2025-09-08 15:24:03'),
(296, 11, 'UP1', 'UPPER', 'SLEEPER', 40, 50, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-08 15:24:10', '2025-09-08 15:24:11'),
(297, 11, 'UP2', 'UPPER', 'SLEEPER', 90, 50, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-08 15:24:14', '2025-09-08 15:24:15'),
(298, 11, 'UP3', 'UPPER', 'SLEEPER', 210, 50, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-08 15:24:18', '2025-09-08 15:24:18'),
(299, 12, 'LP1', 'LOWER', 'SLEEPER', 190, 70, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-08 15:25:34', '2025-09-08 15:25:34'),
(300, 12, 'LP2', 'LOWER', 'SLEEPER', 140, 70, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-08 15:25:36', '2025-09-08 15:25:37'),
(301, 12, 'LS1', 'LOWER', 'SEATER', 90, 70, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-08 15:25:40', '2025-09-08 15:25:41'),
(302, 12, 'UP1', 'UPPER', 'SLEEPER', 140, 60, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-08 15:25:43', '2025-09-08 15:25:43'),
(303, 12, 'US1', 'UPPER', 'SEATER', 80, 70, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-08 15:25:46', '2025-09-08 15:25:46'),
(304, 12, 'UP2', 'UPPER', 'SLEEPER', 70, 160, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-08 15:25:49', '2025-09-08 15:25:49');

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
(7, 40, 'bf163f0d17db29e4ce483a2965a5b31b', '2025-09-09 05:21:17');

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
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=Active, 2=Deactivated'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `mobile_no`, `email`, `ip_address`, `status`) VALUES
(3, 'Sanjay Kumar Sheoran', '$2y$10$z7lBSp5NypMVF1S05ZvNeui70bCZceCut.xYsUwAGecNIoqck5DO6', '9728833428', 'sjsheoran111@gmail.com', '::1', 1);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `admin_activity_log`
--
ALTER TABLE `admin_activity_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `booked_seats`
--
ALTER TABLE `booked_seats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `buses`
--
ALTER TABLE `buses`
  MODIFY `bus_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `bus_categories`
--
ALTER TABLE `bus_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `bus_category_map`
--
ALTER TABLE `bus_category_map`
  MODIFY `map_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `bus_images`
--
ALTER TABLE `bus_images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `operators`
--
ALTER TABLE `operators`
  MODIFY `operator_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `passengers`
--
ALTER TABLE `passengers`
  MODIFY `passenger_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `routes`
--
ALTER TABLE `routes`
  MODIFY `route_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `route_schedules`
--
ALTER TABLE `route_schedules`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `route_stops`
--
ALTER TABLE `route_stops`
  MODIFY `stop_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `seats`
--
ALTER TABLE `seats`
  MODIFY `seat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=305;

--
-- AUTO_INCREMENT for table `ticket_access_tokens`
--
ALTER TABLE `ticket_access_tokens`
  MODIFY `token_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
