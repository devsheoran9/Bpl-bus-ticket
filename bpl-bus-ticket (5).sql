-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 05, 2025 at 09:57 AM
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

INSERT INTO `admin` (`id`, `name`, `mobile`, `email`, `password`, `password_salt`, `status`, `ip_address`, `date_time`) VALUES
(1, 'dev', '8930000210', 'admin@gmail.com', '$2y$12$F5HnNj16GzvkVuojDu/9Re/IeDjwwH4.flwKS5hX5FluIrlOlexC6', '123456', '1', '::1', '2025-07-23 13:36:33');

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
(4, 'Hr 323jcdfjkd4', 'fsdfsd34', 1, '121', 0, 0, 0, NULL, 'fsdfsdfsd', 'Active', '2025-09-03 10:33:07', '2025-09-03 10:33:07'),
(5, 'MAYA Bus Services', 'HR 61 B 2917', 2, '121', 52, 30, 22, NULL, 'kuch nhi h ', 'Active', '2025-09-03 10:36:26', '2025-09-03 10:36:26'),
(6, 'HR63 dhidsfj', '6565467343', 2, '121', 12, 43, 53, NULL, 'fgdsfds', 'Active', '2025-09-03 15:57:16', '2025-09-03 15:57:16'),
(10, 'Hr 323jcdfjkdre', 'HR 61 B 29173', 2, 'Non-AC Seater', 0, 0, 0, NULL, '4432423432', 'Active', '2025-09-04 10:29:01', '2025-09-04 10:47:10');

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
(17, 10, 4),
(18, 10, 2);

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
(1, 'Sharma Travels', 'Rahul Sharma', NULL, NULL, NULL, 'Active', '2025-09-02 15:32:55', '2025-09-02 15:32:55'),
(2, 'Shanti Express', 'Priya Singh', NULL, NULL, NULL, 'Active', '2025-09-02 15:32:55', '2025-09-02 15:32:55'),
(3, 'Royal Roadways', 'Amit Kumar', NULL, NULL, NULL, 'Active', '2025-09-02 15:32:55', '2025-09-02 15:32:55'),
(4, '32', '32', '32@gmail.com', '32', 'fsdfd', 'Active', '2025-09-04 13:10:30', '2025-09-04 13:10:30');

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
(8, 5, 'Delhi To Pilani', 'Delhi, kasmiri Gate', 'Rohtak', 'Active', 0, '2025-09-05 06:24:21'),
(9, 5, 'Delhi To Pilani', 'Delhi, kasmiri Gate', 'Pilani', 'Active', 0, '2025-09-05 06:30:06');

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
(1, 8, 'Mon', '14:00:00'),
(2, 8, 'Tue', '15:00:00'),
(3, 8, 'Thu', '17:00:00'),
(23, 9, 'Mon', '02:00:00'),
(24, 9, 'Wed', '17:00:00'),
(25, 9, 'Sun', '03:00:00');

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
(18, 8, 'Rohtak, Purana Bus Stand', 1, 60, NULL, NULL, NULL, NULL),
(19, 8, 'Rohtak', 2, 60, NULL, NULL, NULL, NULL),
(47, 9, 'Rohtak, Purana Bus Stand', 1, 60, 200.00, 300.00, 100.00, 200.00),
(48, 9, 'Loharu', 2, 120, 400.00, 600.00, 200.00, 400.00),
(49, 9, 'Pilani', 3, 180, 600.00, 900.00, 300.00, 600.00);

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
(81, 5, 'LS1', 'LOWER', 'SEATER', 210, 70, 40, 40, 'VERTICAL', 350.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 12:42:11', '2025-09-03 12:42:36'),
(83, 5, 'LS2', 'LOWER', 'SEATER', 160, 70, 40, 40, 'VERTICAL_DOWN', 350.00, 'FEMALE', 1, 'AVAILABLE', '2025-09-03 12:42:42', '2025-09-03 15:49:48'),
(84, 5, 'LS3', 'LOWER', 'SEATER', 210, 120, 40, 40, 'VERTICAL', 350.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 12:42:49', '2025-09-03 12:42:51'),
(85, 5, 'LS4', 'LOWER', 'SEATER', 110, 70, 40, 40, 'HORIZONTAL_RIGHT', 34000.00, 'MALE', 1, 'AVAILABLE', '2025-09-03 12:42:57', '2025-09-03 15:49:37'),
(86, 5, 'LP1', 'LOWER', 'SLEEPER', 60, 420, 40, 90, 'VERTICAL', 800.00, 'FEMALE', 1, 'AVAILABLE', '2025-09-03 12:43:04', '2025-09-03 15:54:30'),
(87, 5, 'LP2', 'LOWER', 'SLEEPER', 40, 250, 40, 90, 'HORIZONTAL_RIGHT', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 12:43:15', '2025-09-04 11:28:22'),
(88, 5, 'LS5', 'LOWER', 'SEATER', 160, 120, 40, 40, 'VERTICAL', 350.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 12:43:22', '2025-09-03 12:43:24'),
(89, 5, 'LS6', 'LOWER', 'SEATER', 60, 120, 40, 40, 'VERTICAL', 350.00, 'FEMALE', 1, 'AVAILABLE', '2025-09-03 12:43:27', '2025-09-03 15:53:41'),
(90, 5, 'LS7', 'LOWER', 'SEATER', 210, 170, 40, 40, 'VERTICAL', 350.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 12:43:31', '2025-09-03 12:43:33'),
(91, 5, 'LS8', 'LOWER', 'SEATER', 160, 170, 40, 40, 'VERTICAL', 350.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 12:43:36', '2025-09-03 12:43:39'),
(93, 5, 'LP3', 'LOWER', 'SLEEPER', 10, 530, 40, 80, 'VERTICAL', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 12:43:47', '2025-09-03 15:30:31'),
(94, 5, 'LS10', 'LOWER', 'SEATER', 210, 220, 40, 40, 'VERTICAL', 600.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 12:43:53', '2025-09-03 12:52:18'),
(95, 5, 'LS11', 'LOWER', 'SEATER', 160, 220, 40, 40, 'VERTICAL', 350.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 12:43:58', '2025-09-03 12:44:00'),
(96, 5, 'LS12', 'LOWER', 'SEATER', 60, 170, 40, 40, 'VERTICAL', 350.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 12:44:05', '2025-09-03 15:53:50'),
(97, 5, 'LS13', 'LOWER', 'SEATER', 210, 270, 40, 40, 'VERTICAL', 350.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 12:44:09', '2025-09-03 12:44:11'),
(98, 5, 'LS14', 'LOWER', 'SEATER', 110, 170, 40, 40, 'VERTICAL', 350.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 12:44:14', '2025-09-03 15:53:53'),
(99, 5, 'LS15', 'LOWER', 'SEATER', 160, 270, 40, 40, 'VERTICAL', 350.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 12:44:20', '2025-09-03 12:44:21'),
(100, 5, 'LS16', 'LOWER', 'SEATER', 210, 320, 40, 40, 'VERTICAL', 350.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 12:44:28', '2025-09-03 12:44:29'),
(101, 5, 'LS17', 'LOWER', 'SEATER', 160, 320, 40, 40, 'VERTICAL', 350.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 12:44:33', '2025-09-03 12:44:34'),
(102, 5, 'LS18', 'LOWER', 'SEATER', 110, 320, 40, 40, 'VERTICAL', 350.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 12:44:39', '2025-09-03 12:44:41'),
(103, 5, 'LP4', 'LOWER', 'SLEEPER', 60, 330, 40, 80, 'VERTICAL', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 12:44:47', '2025-09-03 15:54:32'),
(104, 5, 'LS19', 'LOWER', 'SEATER', 210, 370, 40, 40, 'VERTICAL', 350.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 12:44:56', '2025-09-03 12:44:57'),
(105, 5, 'LS20', 'LOWER', 'SEATER', 160, 370, 40, 40, 'VERTICAL', 350.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 12:45:02', '2025-09-03 12:45:03'),
(106, 5, 'LS21', 'LOWER', 'SEATER', 110, 370, 40, 40, 'VERTICAL', 350.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 12:45:06', '2025-09-03 12:45:07'),
(107, 5, 'LS22', 'LOWER', 'SEATER', 210, 420, 40, 40, 'VERTICAL', 350.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 12:45:11', '2025-09-03 12:45:13'),
(108, 5, 'LS23', 'LOWER', 'SEATER', 160, 420, 40, 40, 'VERTICAL', 350.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 12:45:16', '2025-09-03 12:45:17'),
(109, 5, 'LS24', 'LOWER', 'SEATER', 110, 420, 40, 40, 'VERTICAL', 350.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 12:45:21', '2025-09-03 12:45:22'),
(110, 5, 'LS25', 'LOWER', 'SEATER', 210, 470, 40, 40, 'VERTICAL', 350.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 12:45:28', '2025-09-03 12:47:36'),
(111, 5, 'LS26', 'LOWER', 'SEATER', 160, 470, 40, 40, 'VERTICAL', 350.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 12:45:35', '2025-09-03 12:45:36'),
(112, 5, 'LS27', 'LOWER', 'SEATER', 110, 470, 40, 40, 'VERTICAL', 350.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 12:45:39', '2025-09-03 12:45:41'),
(115, 5, 'UP1', 'UPPER', 'SLEEPER', 40, 20, 40, 80, 'VERTICAL', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 12:48:39', '2025-09-03 12:48:45'),
(117, 5, 'UP2', 'UPPER', 'SLEEPER', 150, 20, 40, 80, 'VERTICAL', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 12:50:06', '2025-09-03 12:50:11'),
(118, 5, 'UP3', 'UPPER', 'SLEEPER', 200, 20, 40, 80, 'VERTICAL', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 12:50:17', '2025-09-03 12:50:19'),
(119, 5, 'UP4', 'UPPER', 'SLEEPER', 200, 110, 40, 80, 'VERTICAL', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 12:50:26', '2025-09-03 12:50:27'),
(120, 5, 'UP5', 'UPPER', 'SLEEPER', 150, 110, 40, 80, 'VERTICAL', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 12:50:30', '2025-09-03 12:50:31'),
(121, 5, 'UP6', 'UPPER', 'SLEEPER', 40, 110, 40, 80, 'VERTICAL', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 12:50:33', '2025-09-03 12:50:34'),
(122, 5, 'UP7', 'UPPER', 'SLEEPER', 200, 200, 40, 80, 'VERTICAL', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 12:50:37', '2025-09-03 12:50:38'),
(123, 5, 'UP8', 'UPPER', 'SLEEPER', 150, 200, 40, 80, 'VERTICAL', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 12:50:44', '2025-09-03 15:52:27'),
(124, 5, 'UP9', 'UPPER', 'SLEEPER', 40, 200, 40, 80, 'VERTICAL', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 12:50:49', '2025-09-03 12:50:51'),
(125, 5, 'UP10', 'UPPER', 'SLEEPER', 200, 290, 40, 80, 'VERTICAL', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 12:50:55', '2025-09-03 12:50:56'),
(126, 5, 'UP11', 'UPPER', 'SLEEPER', 150, 290, 40, 80, 'VERTICAL', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 12:50:59', '2025-09-03 12:51:00'),
(127, 5, 'UP12', 'UPPER', 'SLEEPER', 40, 290, 40, 80, 'VERTICAL', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 12:51:04', '2025-09-03 12:51:04'),
(128, 5, 'UP13', 'UPPER', 'SLEEPER', 150, 380, 40, 80, 'VERTICAL', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 12:51:09', '2025-09-03 12:51:15'),
(129, 5, 'UP14', 'UPPER', 'SLEEPER', 200, 380, 40, 80, 'VERTICAL', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 12:51:13', '2025-09-03 12:51:16'),
(130, 5, 'UP15', 'UPPER', 'SLEEPER', 40, 380, 40, 80, 'VERTICAL', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 12:51:20', '2025-09-03 12:51:23'),
(131, 5, 'UP16', 'UPPER', 'SLEEPER', 200, 470, 40, 80, 'VERTICAL', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 12:51:28', '2025-09-03 12:51:29'),
(132, 5, 'UP17', 'UPPER', 'SLEEPER', 150, 470, 40, 80, 'VERTICAL', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 12:51:33', '2025-09-03 12:51:33'),
(133, 5, 'UP18', 'UPPER', 'SLEEPER', 40, 470, 40, 80, 'VERTICAL', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 12:51:41', '2025-09-03 12:51:43'),
(134, 5, 'DRIVER', 'LOWER', 'DRIVER', 180, 10, 50, 50, 'VERTICAL', 0.00, 'ANY', 0, 'AVAILABLE', '2025-09-03 13:08:09', '2025-09-03 15:36:19'),
(137, 5, 'LS28', 'LOWER', 'SEATER', 210, 520, 40, 40, 'VERTICAL', 350.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 13:49:40', '2025-09-03 13:50:35'),
(138, 5, 'LS29', 'LOWER', 'SEATER', 160, 520, 40, 40, 'VERTICAL', 350.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 13:50:39', '2025-09-03 13:50:40'),
(139, 5, 'LP6', 'LOWER', 'SLEEPER', 10, 340, 40, 80, 'VERTICAL', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 13:50:43', '2025-09-03 15:30:25'),
(140, 5, 'LS30', 'LOWER', 'SEATER', 120, 570, 40, 40, 'VERTICAL', 350.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 13:56:50', '2025-09-03 15:54:38'),
(141, 5, 'LS31', 'LOWER', 'SEATER', 60, 220, 40, 40, 'VERTICAL', 350.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 13:56:59', '2025-09-03 15:54:22'),
(142, 5, 'LS32', 'LOWER', 'SEATER', 10, 120, 40, 40, 'VERTICAL', 350.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 13:57:05', '2025-09-03 15:53:44'),
(143, 5, 'LS33', 'LOWER', 'SEATER', 80, 520, 40, 40, 'VERTICAL', 350.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 13:57:11', '2025-09-03 15:54:34'),
(144, 5, 'LS34', 'LOWER', 'SEATER', 110, 120, 40, 40, 'VERTICAL', 350.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 15:33:09', '2025-09-03 15:38:21'),
(145, 5, 'LP7', 'LOWER', 'SLEEPER', 10, 170, 40, 80, 'VERTICAL', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 15:33:56', '2025-09-03 15:53:48'),
(146, 5, 'LS35', 'LOWER', 'SEATER', 10, 70, 40, 40, 'VERTICAL', 350.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 15:34:11', '2025-09-03 15:53:38'),
(147, 5, 'LS36', 'LOWER', 'SEATER', 60, 70, 40, 40, 'VERTICAL', 350.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 15:34:34', '2025-09-03 15:34:36'),
(148, 6, 'LS1', 'LOWER', 'SEATER', 226, 60, 40, 40, 'VERTICAL', 350.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 15:57:24', '2025-09-03 15:57:53'),
(149, 6, 'LS2', 'LOWER', 'SEATER', 180, 60, 40, 40, '', 0.00, 'MALE', 1, 'AVAILABLE', '2025-09-03 15:57:27', '2025-09-04 10:01:29'),
(150, 6, 'DRIVER', 'LOWER', 'DRIVER', 220, 0, 50, 50, 'VERTICAL', 0.00, 'ANY', 0, 'AVAILABLE', '2025-09-03 15:57:34', '2025-09-03 16:59:40'),
(151, 6, 'LP1', 'LOWER', 'SLEEPER', 226, 110, 40, 80, '', 800.00, 'MALE', 1, 'AVAILABLE', '2025-09-03 15:58:02', '2025-09-03 16:05:33'),
(152, 6, 'LP2', 'LOWER', 'SLEEPER', 180, 110, 40, 80, '', 800.00, 'FEMALE', 1, 'AVAILABLE', '2025-09-03 15:58:10', '2025-09-03 16:05:28'),
(153, 6, 'LP3', 'LOWER', 'SLEEPER', 226, 200, 40, 80, '', 800.00, 'FEMALE', 1, 'AVAILABLE', '2025-09-03 15:58:15', '2025-09-03 16:05:37'),
(154, 6, 'LP4', 'LOWER', 'SLEEPER', 180, 200, 40, 80, '', 800.00, 'MALE', 1, 'AVAILABLE', '2025-09-03 15:58:19', '2025-09-03 16:58:19'),
(155, 6, 'LP5', 'LOWER', 'SLEEPER', 180, 290, 40, 80, '', 800.00, 'FEMALE', 1, 'AVAILABLE', '2025-09-03 15:58:31', '2025-09-03 16:05:47'),
(156, 6, 'LP6', 'LOWER', 'SLEEPER', 226, 290, 40, 80, '', 800.00, 'MALE', 0, 'AVAILABLE', '2025-09-03 15:58:35', '2025-09-03 16:06:45'),
(157, 6, 'LP7', 'LOWER', 'SLEEPER', 180, 380, 40, 80, 'VERTICAL', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 15:58:42', '2025-09-03 15:58:44'),
(158, 6, 'LP8', 'LOWER', 'SLEEPER', 226, 380, 40, 80, '', 800.00, 'FEMALE', 1, 'AVAILABLE', '2025-09-03 15:58:48', '2025-09-03 16:06:41'),
(159, 6, 'LP9', 'LOWER', 'SLEEPER', 180, 470, 40, 80, 'VERTICAL', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 15:58:55', '2025-09-03 15:58:57'),
(160, 6, 'LP10', 'LOWER', 'SLEEPER', 226, 470, 40, 80, '', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 15:59:00', '2025-09-03 16:06:33'),
(161, 6, 'LP11', 'LOWER', 'SLEEPER', 180, 560, 40, 80, 'VERTICAL', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 15:59:06', '2025-09-03 15:59:07'),
(162, 6, 'LP12', 'LOWER', 'SLEEPER', 226, 560, 40, 80, 'VERTICAL', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 15:59:12', '2025-09-03 15:59:14'),
(163, 6, 'LP13', 'LOWER', 'SLEEPER', 40, 40, 40, 80, 'HORIZONTAL_RIGHT', 1000.00, 'MALE', 1, 'AVAILABLE', '2025-09-03 15:59:26', '2025-09-03 16:34:40'),
(164, 6, 'LP14', 'LOWER', 'SLEEPER', 40, 90, 40, 80, 'HORIZONTAL_RIGHT', 400.00, 'FEMALE', 1, 'AVAILABLE', '2025-09-03 15:59:44', '2025-09-03 16:34:42'),
(165, 6, 'LP15', 'LOWER', 'SLEEPER', 40, 140, 40, 80, 'HORIZONTAL_RIGHT', 800.00, 'MALE', 1, 'AVAILABLE', '2025-09-03 15:59:54', '2025-09-03 16:34:44'),
(166, 6, 'LP16', 'LOWER', 'SLEEPER', 40, 190, 40, 80, 'HORIZONTAL_RIGHT', 600.00, 'FEMALE', 1, 'AVAILABLE', '2025-09-03 16:00:03', '2025-09-03 16:34:34'),
(167, 6, 'LP17', 'LOWER', 'SLEEPER', 40, 240, 40, 80, 'HORIZONTAL_RIGHT', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 16:00:05', '2025-09-03 16:00:22'),
(168, 6, 'LP18', 'LOWER', 'SLEEPER', 40, 290, 40, 80, 'HORIZONTAL_RIGHT', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 16:00:07', '2025-09-03 16:00:30'),
(169, 6, 'LP19', 'LOWER', 'SLEEPER', 70, 360, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 16:00:34', '2025-09-03 17:43:27'),
(170, 6, 'LP20', 'LOWER', 'SLEEPER', 20, 360, 40, 80, 'VERTICAL', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 16:00:37', '2025-09-03 16:00:42'),
(171, 6, 'LP21', 'LOWER', 'SLEEPER', 70, 450, 40, 80, 'VERTICAL', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 16:00:38', '2025-09-03 16:00:49'),
(172, 6, 'LP22', 'LOWER', 'SLEEPER', 20, 450, 40, 80, 'VERTICAL', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 16:00:40', '2025-09-03 16:00:51'),
(173, 6, 'LP23', 'LOWER', 'SLEEPER', 20, 540, 40, 80, 'VERTICAL', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 16:00:55', '2025-09-03 16:00:57'),
(174, 6, 'LP24', 'LOWER', 'SLEEPER', 70, 540, 40, 80, '', 800.00, 'MALE', 1, 'AVAILABLE', '2025-09-03 16:01:00', '2025-09-03 16:07:31'),
(175, 6, 'LS3', 'LOWER', 'SEATER', 20, 626, 40, 40, 'VERTICAL', 350.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 16:01:11', '2025-09-03 16:01:13'),
(176, 6, 'LS4', 'LOWER', 'SEATER', 70, 630, 40, 40, 'VERTICAL', 350.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 16:01:19', '2025-09-04 10:05:34'),
(177, 6, 'UP1', 'UPPER', 'SLEEPER', 30, 20, 40, 80, 'VERTICAL', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 16:01:37', '2025-09-03 16:01:40'),
(178, 6, 'UP2', 'UPPER', 'SLEEPER', 80, 20, 40, 80, 'VERTICAL', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 16:01:44', '2025-09-03 16:01:45'),
(179, 6, 'UP3', 'UPPER', 'SLEEPER', 226, 20, 40, 80, 'VERTICAL', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 16:01:51', '2025-09-03 16:02:29'),
(180, 6, 'UP4', 'UPPER', 'SLEEPER', 180, 20, 40, 80, 'VERTICAL', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 16:01:55', '2025-09-03 16:01:58'),
(181, 6, 'UP5', 'UPPER', 'SLEEPER', 30, 110, 40, 80, 'VERTICAL', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 16:02:02', '2025-09-03 16:02:04'),
(182, 6, 'UP6', 'UPPER', 'SLEEPER', 80, 110, 40, 80, 'VERTICAL', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 16:02:06', '2025-09-03 16:02:07'),
(183, 6, 'UP7', 'UPPER', 'SLEEPER', 180, 110, 40, 80, 'VERTICAL', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 16:02:10', '2025-09-03 16:02:12'),
(184, 6, 'UP8', 'UPPER', 'SLEEPER', 226, 110, 40, 80, 'VERTICAL', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 16:02:15', '2025-09-03 16:02:25'),
(185, 6, 'UP9', 'UPPER', 'SLEEPER', 30, 200, 40, 80, 'VERTICAL', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 16:02:37', '2025-09-03 16:02:38'),
(186, 6, 'UP10', 'UPPER', 'SLEEPER', 80, 200, 40, 80, 'VERTICAL', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 16:02:42', '2025-09-03 16:02:43'),
(187, 6, 'UP11', 'UPPER', 'SLEEPER', 30, 290, 40, 80, 'VERTICAL', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 16:02:53', '2025-09-03 16:02:54'),
(188, 6, 'UP12', 'UPPER', 'SLEEPER', 80, 290, 40, 80, 'VERTICAL', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 16:02:57', '2025-09-03 16:02:58'),
(189, 6, 'UP13', 'UPPER', 'SLEEPER', 30, 380, 40, 80, 'VERTICAL', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 16:03:01', '2025-09-03 16:03:02'),
(190, 6, 'UP14', 'UPPER', 'SLEEPER', 80, 380, 40, 80, 'VERTICAL', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 16:03:06', '2025-09-03 16:03:08'),
(191, 6, 'UP15', 'UPPER', 'SLEEPER', 30, 470, 40, 80, 'VERTICAL', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 16:03:11', '2025-09-03 16:03:12'),
(192, 6, 'UP16', 'UPPER', 'SLEEPER', 80, 470, 40, 80, 'VERTICAL', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 16:03:15', '2025-09-03 16:03:16'),
(193, 6, 'UP17', 'UPPER', 'SLEEPER', 30, 560, 40, 80, 'VERTICAL', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 16:03:19', '2025-09-03 16:03:20'),
(194, 6, 'UP18', 'UPPER', 'SLEEPER', 80, 560, 40, 80, 'VERTICAL', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 16:03:24', '2025-09-03 16:03:26'),
(195, 6, 'UP19', 'UPPER', 'SLEEPER', 180, 200, 40, 80, 'VERTICAL', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 16:03:33', '2025-09-03 16:03:34'),
(196, 6, 'UP20', 'UPPER', 'SLEEPER', 226, 200, 40, 80, 'VERTICAL', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 16:03:38', '2025-09-03 16:03:39'),
(197, 6, 'UP21', 'UPPER', 'SLEEPER', 180, 290, 40, 80, 'VERTICAL', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 16:03:42', '2025-09-03 16:03:54'),
(198, 6, 'UP22', 'UPPER', 'SLEEPER', 180, 380, 40, 80, 'VERTICAL', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 16:03:43', '2025-09-03 16:04:02'),
(199, 6, 'UP23', 'UPPER', 'SLEEPER', 226, 290, 40, 80, 'VERTICAL', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 16:03:46', '2025-09-03 16:03:57'),
(200, 6, 'UP24', 'UPPER', 'SLEEPER', 226, 380, 40, 80, 'VERTICAL', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 16:03:48', '2025-09-03 16:04:04'),
(201, 6, 'UP25', 'UPPER', 'SLEEPER', 180, 470, 40, 80, 'VERTICAL', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 16:03:50', '2025-09-03 16:04:06'),
(202, 6, 'UP26', 'UPPER', 'SLEEPER', 226, 470, 40, 80, 'VERTICAL', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 16:04:10', '2025-09-03 16:04:12'),
(203, 6, 'UP27', 'UPPER', 'SLEEPER', 180, 560, 40, 80, 'VERTICAL', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 16:04:16', '2025-09-03 16:04:17'),
(204, 6, 'UP28', 'UPPER', 'SLEEPER', 226, 560, 40, 80, 'VERTICAL', 800.00, 'ANY', 1, 'AVAILABLE', '2025-09-03 16:04:22', '2025-09-03 16:04:23'),
(211, 6, 'LS7', 'LOWER', 'SEATER', 220, 650, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-04 10:05:01', '2025-09-04 10:05:45'),
(212, 4, 'LS1', 'LOWER', 'SEATER', 30, 10, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-04 10:08:01', '2025-09-04 10:08:01'),
(213, 4, 'LS2', 'LOWER', 'SEATER', 90, 10, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-04 10:08:04', '2025-09-04 10:08:04'),
(214, 4, 'LS3', 'LOWER', 'SEATER', 150, 10, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-04 10:08:11', '2025-09-04 10:08:14'),
(215, 4, 'LS4', 'LOWER', 'SEATER', 210, 10, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-04 10:08:17', '2025-09-04 10:08:18'),
(216, 4, 'LP1', 'LOWER', 'SLEEPER', 60, 60, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-04 10:08:22', '2025-09-04 10:08:23'),
(217, 4, 'LP2', 'LOWER', 'SLEEPER', 120, 60, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-04 10:08:26', '2025-09-04 10:08:27'),
(218, 4, 'LP3', 'LOWER', 'SLEEPER', 180, 60, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-04 10:08:31', '2025-09-04 10:08:32'),
(219, 4, 'LP4', 'LOWER', 'SLEEPER', 40, 150, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-04 10:08:36', '2025-09-04 10:08:37'),
(220, 4, 'LP5', 'LOWER', 'SLEEPER', 200, 130, 40, 80, 'HORIZONTAL_LEFT', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-04 10:08:41', '2025-09-04 10:08:50'),
(221, 10, 'LP1', 'LOWER', 'SLEEPER', -150, 20, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-04 11:25:26', '2025-09-04 11:25:29'),
(222, 10, 'LP2', 'LOWER', 'SLEEPER', 180, 190, 40, 80, 'VERTICAL_UP', 0.00, 'FEMALE', 1, 'AVAILABLE', '2025-09-04 11:25:56', '2025-09-04 12:31:44'),
(223, 10, 'DRIVER', 'LOWER', 'DRIVER', 130, 10, 50, 50, 'VERTICAL_UP', 0.00, 'ANY', 0, 'AVAILABLE', '2025-09-04 11:26:01', '2025-09-04 12:48:51'),
(225, 10, 'LS1', 'LOWER', 'SEATER', 80, 470, 40, 40, 'VERTICAL_DOWN', 0.00, 'MALE', 1, 'AVAILABLE', '2025-09-04 11:26:05', '2025-09-04 15:24:29'),
(227, 10, 'LP3', 'LOWER', 'SLEEPER', 180, 100, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-04 11:57:13', '2025-09-04 12:31:39'),
(228, 10, 'LP4', 'LOWER', 'SLEEPER', 230, 20, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-04 12:00:51', '2025-09-04 12:00:53'),
(229, 10, 'LS2', 'LOWER', 'SEATER', 180, 50, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-04 12:01:01', '2025-09-04 12:31:41'),
(230, 10, 'LP5', 'LOWER', 'SLEEPER', 230, 290, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-04 12:01:06', '2025-09-04 12:33:18'),
(231, 10, 'LP6', 'LOWER', 'SLEEPER', 130, 300, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-04 12:09:04', '2025-09-04 12:33:06'),
(232, 10, 'LS3', 'LOWER', 'SEATER', 180, 420, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-04 12:09:07', '2025-09-04 13:15:29'),
(233, 10, 'LP7', 'LOWER', 'SLEEPER', 230, 110, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-04 12:13:21', '2025-09-04 12:31:36'),
(234, 10, 'LS4', 'LOWER', 'SEATER', 130, 160, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-04 12:13:30', '2025-09-04 12:32:02'),
(235, 10, 'LS5', 'LOWER', 'SEATER', 180, 280, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-04 12:13:33', '2025-09-04 12:32:07'),
(236, 10, 'LP8', 'LOWER', 'SLEEPER', 130, 70, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-04 12:13:36', '2025-09-04 13:14:56'),
(237, 10, 'LS6', 'LOWER', 'SEATER', 30, 160, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-04 12:14:39', '2025-09-04 15:17:33'),
(238, 10, 'LS7', 'LOWER', 'SEATER', 30, 70, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-04 12:16:29', '2025-09-04 12:32:52'),
(239, 10, 'LS8', 'LOWER', 'SEATER', 30, 450, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-04 12:18:09', '2025-09-04 13:15:23'),
(240, 10, 'LP9', 'LOWER', 'SLEEPER', 180, 330, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-04 12:21:54', '2025-09-04 12:33:36'),
(241, 10, 'UP1', 'UPPER', 'SLEEPER', -70, 417, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-04 12:22:14', '2025-09-04 12:23:48'),
(242, 10, 'UP2', 'UPPER', 'SLEEPER', -70, 50, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-04 12:23:43', '2025-09-04 12:23:45'),
(243, 10, 'UP3', 'UPPER', 'SLEEPER', 20, 20, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-04 12:23:55', '2025-09-04 13:15:12'),
(245, 10, 'LP10', 'LOWER', 'SLEEPER', 230, 200, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-04 12:31:24', '2025-09-04 12:31:37'),
(246, 10, 'LS9', 'LOWER', 'SEATER', 130, 440, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-04 12:31:29', '2025-09-04 13:15:33'),
(247, 10, 'LP11', 'LOWER', 'SLEEPER', 30, 500, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-04 12:32:14', '2025-09-04 13:15:24'),
(248, 10, 'LP12', 'LOWER', 'SLEEPER', 130, 210, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-04 12:32:25', '2025-09-04 13:14:58'),
(249, 10, 'LP13', 'LOWER', 'SLEEPER', 80, 520, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-04 12:32:31', '2025-09-04 15:24:36'),
(251, 10, 'LP14', 'LOWER', 'SLEEPER', 130, 490, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-04 12:32:43', '2025-09-04 13:15:37'),
(252, 10, 'LP15', 'LOWER', 'SLEEPER', 30, 360, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-04 12:33:46', '2025-09-04 12:33:55'),
(253, 10, 'LP16', 'LOWER', 'SLEEPER', 30, 590, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-04 12:34:36', '2025-09-04 13:15:27'),
(254, 10, 'LG1', 'LOWER', 'AISLE', 80, 370, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 0, 'AVAILABLE', '2025-09-04 12:35:50', '2025-09-04 13:17:52'),
(255, 10, 'LS11', 'LOWER', 'SEATER', 30, 120, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-04 12:51:27', '2025-09-04 12:51:35'),
(256, 10, 'LP17', 'LOWER', 'SLEEPER', 230, 380, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-04 13:14:51', '2025-09-04 13:15:02'),
(257, 10, 'LP18', 'LOWER', 'SLEEPER', 230, 470, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-04 13:15:48', '2025-09-04 13:15:48'),
(259, 10, 'LG2', 'LOWER', 'AISLE', 180, 480, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 0, 'AVAILABLE', '2025-09-04 13:16:47', '2025-09-04 13:18:14'),
(260, 10, 'LG3', 'LOWER', 'AISLE', 80, 120, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 0, 'AVAILABLE', '2025-09-04 13:16:50', '2025-09-04 13:17:00'),
(261, 10, 'LG4', 'LOWER', 'AISLE', 80, 170, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 0, 'AVAILABLE', '2025-09-04 13:17:02', '2025-09-04 13:17:03'),
(262, 10, 'LG5', 'LOWER', 'AISLE', 80, 220, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 0, 'AVAILABLE', '2025-09-04 13:17:06', '2025-09-04 13:17:07'),
(263, 10, 'LG6', 'LOWER', 'AISLE', 80, 270, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 0, 'AVAILABLE', '2025-09-04 13:17:11', '2025-09-04 13:17:11'),
(264, 10, 'LG7', 'LOWER', 'AISLE', 80, 320, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 0, 'AVAILABLE', '2025-09-04 13:17:50', '2025-09-04 13:17:50'),
(265, 10, 'LG8', 'LOWER', 'AISLE', 80, 420, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 0, 'AVAILABLE', '2025-09-04 13:17:55', '2025-09-04 13:17:57'),
(269, 10, 'LP19', 'LOWER', 'SLEEPER', 30, 260, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-04 15:17:12', '2025-09-04 15:24:31'),
(270, 10, 'LP20', 'LOWER', 'SLEEPER', 80, 40, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-04 15:25:00', '2025-09-04 15:25:03'),
(271, 10, 'LS12', 'LOWER', 'SEATER', 130, 590, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-04 15:25:09', '2025-09-04 15:25:10'),
(272, 10, 'LP21', 'LOWER', 'SLEEPER', 110, 650, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-04 15:25:15', '2025-09-04 15:25:15'),
(273, 10, 'LG9', 'LOWER', 'AISLE', 200, 610, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 0, 'AVAILABLE', '2025-09-04 15:25:22', '2025-09-04 15:25:22'),
(274, 10, 'US1', 'UPPER', 'SEATER', 70, 400, 40, 40, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-04 15:26:02', '2025-09-04 15:26:02'),
(275, 10, 'LP22', 'LOWER', 'SLEEPER', 180, 530, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-04 15:26:43', '2025-09-04 15:26:44'),
(276, 10, 'UP4', 'UPPER', 'SLEEPER', 20, 110, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-04 15:42:36', '2025-09-04 15:42:39'),
(277, 10, 'UP5', 'UPPER', 'SLEEPER', 30, 210, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-04 15:46:20', '2025-09-04 15:46:20'),
(278, 10, 'UP6', 'UPPER', 'SLEEPER', 90, 10, 40, 80, 'VERTICAL_UP', 0.00, 'ANY', 1, 'AVAILABLE', '2025-09-04 15:47:19', '2025-09-04 15:47:19');

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
(22, 3, '11340fe980dac87ab49e82eca5004e37a0d97d384ffc26487bd0eb0bc20c081b', '1', '2025-09-05 07:32:36', '::1');

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
(36, '1', '$2y$12$E3XS77PJ7WM9Ro1lQRY4kOwzTfuW6BoqoS5P.D6H5TGbB4cFbpJoK', '1', '2025-09-05 05:39:07', '::1');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `buses`
--
ALTER TABLE `buses`
  MODIFY `bus_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `bus_categories`
--
ALTER TABLE `bus_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `bus_category_map`
--
ALTER TABLE `bus_category_map`
  MODIFY `map_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `bus_images`
--
ALTER TABLE `bus_images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `operators`
--
ALTER TABLE `operators`
  MODIFY `operator_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `routes`
--
ALTER TABLE `routes`
  MODIFY `route_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `route_schedules`
--
ALTER TABLE `route_schedules`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `route_stops`
--
ALTER TABLE `route_stops`
  MODIFY `stop_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `seats`
--
ALTER TABLE `seats`
  MODIFY `seat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=279;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users_login_token`
--
ALTER TABLE `users_login_token`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `user_login_token`
--
ALTER TABLE `user_login_token`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

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
-- Constraints for table `users_login_token`
--
ALTER TABLE `users_login_token`
  ADD CONSTRAINT `users_login_token_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
