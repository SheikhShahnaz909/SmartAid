-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Nov 27, 2025 at 11:51 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `smartaid_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `role` varchar(20) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `role`, `action`, `details`, `ip_address`, `created_at`) VALUES
(1, 1, 'donor', 'notified_nearby_need', 'report_id:7; distance_km:0', '::1', '2025-11-26 17:03:49'),
(2, 17, 'reporter', 'submitted_report', 'Report ID: 7; type: food', '::1', '2025-11-26 17:03:51'),
(3, 1, 'donor', 'view_report', 'report_id:7', '::1', '2025-11-26 17:57:22'),
(4, 1, 'donor', 'notified_nearby_need', 'report_id:8; distance_km:0', '::1', '2025-11-26 18:02:09'),
(5, 19, 'reporter', 'submitted_report', 'Report ID: 8; type: hygiene', '::1', '2025-11-26 18:02:09'),
(6, 1, 'donor', 'view_notifications', 'notifications_page_loaded', '::1', '2025-11-26 18:02:47'),
(7, 1, 'donor', 'notification_mark_read', 'notification_id:3', '::1', '2025-11-26 18:02:52'),
(8, 1, 'donor', 'view_notifications', 'notifications_page_loaded', '::1', '2025-11-26 18:02:52'),
(9, 1, 'donor', 'view_report', 'report_id:8', '::1', '2025-11-26 18:02:54'),
(10, 1, 'donor', 'claimed_report', 'report_id:8; donor_id:1', '::1', '2025-11-26 18:04:18'),
(11, 1, 'donor', 'view_report', 'report_id:8', '::1', '2025-11-26 18:04:18'),
(12, 1, 'admin', 'admin_mark_claimed', 'Report ID 8 marked as completed by admin', '::1', '2025-11-26 18:05:05'),
(13, 1, 'admin', 'admin_mark_claimed', 'Report ID 8 marked as completed by admin', '::1', '2025-11-27 02:07:27'),
(14, 21, 'donor', 'view_notifications', 'notifications_page_loaded', '::1', '2025-11-27 02:28:22'),
(15, 21, 'donor', 'view_notifications', 'notifications_page_loaded', '::1', '2025-11-27 02:29:27'),
(16, 1, 'donor', 'notified_nearby_need', 'report_id:9; distance_km:2.47', '::1', '2025-11-27 02:32:00'),
(17, 21, 'donor', 'notified_nearby_need', 'report_id:9; distance_km:2.47', '::1', '2025-11-27 02:32:05'),
(18, 20, 'reporter', 'submitted_report', 'Report ID: 9; type: hygiene', '::1', '2025-11-27 02:32:05'),
(19, 1, 'donor', 'view_notifications', 'notifications_page_loaded', '::1', '2025-11-27 02:33:43'),
(20, 1, 'donor', 'notification_mark_read', 'notification_id:4', '::1', '2025-11-27 02:33:47'),
(21, 1, 'donor', 'view_notifications', 'notifications_page_loaded', '::1', '2025-11-27 02:33:47'),
(22, 1, 'donor', 'view_report', 'report_id:9', '::1', '2025-11-27 02:33:49'),
(23, 1, 'donor', 'view_notifications', 'notifications_page_loaded', '::1', '2025-11-27 02:38:08'),
(24, 1, 'donor', 'view_notifications', 'notifications_page_loaded', '::1', '2025-11-27 02:38:12'),
(25, 1, 'donor', 'view_notifications', 'notifications_page_loaded', '::1', '2025-11-27 02:50:39'),
(26, 1, 'donor', 'view_report', 'report_id:9', '::1', '2025-11-27 02:50:45'),
(27, 1, 'donor', 'view_notifications', 'notifications_page_loaded', '::1', '2025-11-27 02:53:03'),
(28, 20, 'reporter', 'submitted_report', 'Report ID: 10; type: food', '::1', '2025-11-27 03:11:04'),
(29, 1, 'donor', 'view_notifications', 'notifications_page_loaded', '::1', '2025-11-27 03:26:27'),
(30, 1, 'donor', 'view_notifications', 'notifications_page_loaded', '::1', '2025-11-27 03:26:33'),
(31, 22, 'donor', 'view_notifications', 'notifications_page_loaded', '::1', '2025-11-27 04:04:02'),
(32, 22, 'donor', 'view_notifications', 'notifications_page_loaded', '::1', '2025-11-27 04:04:04'),
(33, 1, 'donor', 'view_notifications', 'notifications_page_loaded', '::1', '2025-11-27 04:14:26'),
(34, 20, 'reporter', 'submitted_report', 'Report ID: 11; type: food', '::1', '2025-11-27 05:06:52'),
(35, 20, 'reporter', 'submitted_report', 'Report ID: 12; type: food', '::1', '2025-11-27 06:06:32'),
(36, 20, 'reporter', 'submitted_report', 'Report ID: 13; type: clothing', '::1', '2025-11-27 06:28:54'),
(37, 23, 'donor', 'view_notifications', 'notifications_page_loaded', '::1', '2025-11-27 07:25:43'),
(38, 20, 'reporter', 'submitted_report', 'Report ID: 14; type: food', '::1', '2025-11-27 07:41:47'),
(39, 23, 'donor', 'view_notifications', 'notifications_page_loaded', '::1', '2025-11-27 07:47:23'),
(40, 23, 'donor', 'view_notifications', 'notifications_page_loaded', '::1', '2025-11-27 07:47:26'),
(41, 1, 'donor', 'view_notifications', 'notifications_page_loaded', '::1', '2025-11-27 07:51:57'),
(42, 1, 'donor', 'view_report', 'report_id:9', '::1', '2025-11-27 07:52:20'),
(43, 1, 'donor', 'view_notifications', 'notifications_page_loaded', '::1', '2025-11-27 08:50:08'),
(44, 1, 'donor', 'view_notifications', 'notifications_page_loaded', '::1', '2025-11-27 08:50:17'),
(45, 1, 'donor', 'view_report', 'report_id:9', '::1', '2025-11-27 08:50:21'),
(46, 20, 'reporter', 'submitted_report', 'Report ID: 15; type: food', '::1', '2025-11-27 09:48:22'),
(47, 1, 'donor', 'view_notifications', 'notifications_page_loaded', '::1', '2025-11-27 09:48:48'),
(48, 1, 'donor', 'view_report', 'report_id:9', '::1', '2025-11-27 09:48:52'),
(49, 24, 'donor', 'notified_nearby_need', 'report_id:16; distance_km:0', '::1', '2025-11-27 10:27:26'),
(50, 23, 'donor', 'notified_nearby_need', 'report_id:16; distance_km:0.01', '::1', '2025-11-27 10:27:31'),
(51, 22, 'donor', 'notified_nearby_need', 'report_id:16; distance_km:0.91', '::1', '2025-11-27 10:27:37'),
(52, 1, 'donor', 'notified_nearby_need', 'report_id:16; distance_km:2.72', '::1', '2025-11-27 10:27:43'),
(53, 17, 'reporter', 'submitted_report', 'Report ID: 16; type: food', '::1', '2025-11-27 10:27:43'),
(54, 24, 'donor', 'view_notifications', 'notifications_page_loaded', '::1', '2025-11-27 10:28:25'),
(55, 24, 'donor', 'view_notifications', 'notifications_page_loaded', '::1', '2025-11-27 10:43:06'),
(56, 24, 'donor', 'notification_mark_read', 'notification_id:6', '::1', '2025-11-27 10:43:14'),
(57, 24, 'donor', 'view_notifications', 'notifications_page_loaded', '::1', '2025-11-27 10:43:14'),
(58, 24, 'donor', 'view_report', 'report_id:16', '::1', '2025-11-27 10:43:16'),
(59, 24, 'donor', 'view_notifications', 'notifications_page_loaded', '::1', '2025-11-27 10:45:07');

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `email` varchar(191) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `email`, `password_hash`, `created_at`, `last_login`) VALUES
(1, 'smrtaid@gmail.com', '$2y$10$4UhqE4zM4ET.92/Bgw4ZauvBfXFSlStTrKGHQFdAw5QbN6h7ETlyW', '2025-11-24 14:41:25', '2025-11-27 10:45:56');

-- --------------------------------------------------------

--
-- Table structure for table `donations`
--

CREATE TABLE `donations` (
  `id` int(11) NOT NULL,
  `donor_id` int(11) DEFAULT NULL,
  `donor_email` varchar(255) DEFAULT NULL,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `lat` decimal(10,7) NOT NULL,
  `lng` decimal(10,7) NOT NULL,
  `available_until` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `donations`
--

INSERT INTO `donations` (`id`, `donor_id`, `donor_email`, `name`, `description`, `address`, `lat`, `lng`, `available_until`, `created_at`) VALUES
(1, NULL, NULL, 'Cafe Sunrise - Leftovers', 'Extra sandwiches and pastries available', '12 Market Rd, Example City', 28.7040600, 77.1024930, '2025-11-21 23:51:52', '2025-11-21 14:21:52'),
(2, NULL, NULL, 'Green Kitchen - Surplus Meals', '20 cooked meals needing pickup', '5 Church St, Example City', 28.7050000, 77.1050000, '2025-11-22 01:51:52', '2025-11-21 14:21:52'),
(3, NULL, NULL, 'Bakery Delight - Day leftovers', 'Assorted breads and rolls', '18 Baker St, Example City', 28.7030000, 77.1000000, '2025-11-22 03:51:52', '2025-11-21 14:21:52'),
(4, 13, 'z@gmail.com', 'food', 'rice', '0', 13.0763430, 75.1019940, NULL, '2025-11-25 15:39:01'),
(5, 1, 'sheikhshahnaz909@gmail.com', '5kg rice', 'basmati rice for a family of four', '0', 13.0763430, 75.1019940, NULL, '2025-11-25 16:43:08'),
(6, 1, 'sheikhshahnaz909@gmail.com', '5 veg meals', 'lunch meal for 5 people', '0', 13.0763430, 75.1019940, NULL, '2025-11-25 16:44:01');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` varchar(60) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `related_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `title`, `message`, `is_read`, `related_id`, `created_at`) VALUES
(1, 1, 'nearby_need', 'Nearby need reported', 'New nearby need: 2kg rice, 1 litre oil, 10 food packets, 3 packets noodles', 0, 6, '2025-11-26 16:55:57'),
(2, 1, 'nearby_need', 'Nearby need reported', 'New nearby need: 2kg rice, 1 litre oil, 10 food packets, 3 packets noodles', 1, 7, '2025-11-26 17:03:45'),
(3, 1, 'nearby_need', 'Nearby need reported', 'New nearby need: Women’s Hygiene Kit (Sanitary Pads, Soap, Comb, Wipes, Toothpaste)\r\n\r\nKids Care Kit (Mild Soap, Toothpaste, Soft Brush, Baby Powder)', 1, 8, '2025-11-26 18:02:04'),
(4, 1, 'nearby_need', 'Nearby need reported', 'New nearby need: Sanitary pads (pack of 10), hand sanitizer, soap bars, hygiene kit', 1, 9, '2025-11-27 02:31:54'),
(5, 21, 'nearby_need', 'Nearby need reported', 'New nearby need: Sanitary pads (pack of 10), hand sanitizer, soap bars, hygiene kit', 0, 9, '2025-11-27 02:32:00'),
(6, 24, 'nearby_need', 'Nearby need reported', 'New nearby need: fiiddfjkdslaj', 1, 16, '2025-11-27 10:27:17'),
(7, 23, 'nearby_need', 'Nearby need reported', 'New nearby need: fiiddfjkdslaj', 0, 16, '2025-11-27 10:27:26'),
(8, 22, 'nearby_need', 'Nearby need reported', 'New nearby need: fiiddfjkdslaj', 0, 16, '2025-11-27 10:27:31'),
(9, 1, 'nearby_need', 'Nearby need reported', 'New nearby need: fiiddfjkdslaj', 0, 16, '2025-11-27 10:27:37');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `user_id`, `token_hash`, `expires_at`, `used`, `created_at`) VALUES
(1, 1, '$2y$10$LVkw6ZhgJTZ1BrmjL6M5vuw6kxLzeM0KXnsReX9g4WGz6rJRxunZy', '2025-11-19 17:32:17', 1, '2025-11-19 15:32:17'),
(2, 1, '$2y$10$G6ikotTbNsIrRD9qx4y/LeTZuBQvbJ5VO.2XL5ecRuCqyj9PKQt5e', '2025-11-20 06:40:46', 1, '2025-11-20 04:40:46'),
(3, 1, '$2y$10$cQnu0i2gJXUDMNQHnsA/.OmN0LGDjap8Dog9OjOlEvJSl/NQYoAue', '2025-11-20 08:41:49', 1, '2025-11-20 06:41:49'),
(4, 1, '$2y$10$e.oyC1bK0iedTAkl/i9tNeIfHhqPDeajhbgPoNYus7tvaYnWckMRO', '2025-11-20 08:42:00', 1, '2025-11-20 06:42:00'),
(5, 3, '$2y$10$XHBbVrCxN7AirV7zpt.eJuFgPy32QZWceKWnfynGXA2SAD3KQvjMC', '2025-11-21 16:42:08', 1, '2025-11-21 14:42:08'),
(6, 1, '$2y$10$.AXNHJawdV9/Li..D9AIZe0KPJfAaoxB6AZsVfqnw3hHZCeZmziLK', '2025-11-21 16:51:45', 1, '2025-11-21 14:51:45'),
(7, 1, '$2y$10$LyN54risk.Hn3VEBAvLaTe/DtdOLKS8sHB.5P8BtB.tgGgIWgDmie', '2025-11-21 17:22:11', 1, '2025-11-21 15:22:11'),
(8, 1, '$2y$10$RWP7X2TuaG0CmXawYdz3WuLa0LtT7VGWDFWEUTmmlw2MPlhKZMZLW', '2025-11-22 11:47:33', 1, '2025-11-22 09:47:33'),
(9, 1, '$2y$10$xsxL7.qMMlupS8AjEkysvuHT1rEKI18p1hfQLP3hu7S6EJNOwaO2i', '2025-11-23 09:03:22', 1, '2025-11-23 07:03:22'),
(10, 1, '$2y$10$XIHRYohJDO49TK9F0aM6NOc6rUFgXL.3AupzgDamj.7vvdwCzReCe', '2025-11-23 09:03:27', 1, '2025-11-23 07:03:27'),
(11, 1, '$2y$10$ClVRbU8lQ48a0A4gHHFZr.CjMEgz8jzuDuX32r078DK5n/MhhNeJa', '2025-11-23 09:03:31', 1, '2025-11-23 07:03:31'),
(12, 1, '$2y$10$HVwFn8XrZbal5/3jZM.CgOcv5ss/iKg2hSWt7bFw7M35VGhY4fv4.', '2025-11-23 09:03:36', 1, '2025-11-23 07:03:36'),
(13, 1, '$2y$10$XfrPkXUYADYLaCOqYBZuluvAwghl.5bdoB4VCNWo5cFla9MtyZqOK', '2025-11-23 09:03:42', 1, '2025-11-23 07:03:42'),
(14, 1, '$2y$10$WsZ/wE0OoKJsgSunHGTszeyRrar3OtAWqYGpD3ek8Vu40oFRiWhBq', '2025-11-23 09:03:48', 1, '2025-11-23 07:03:48'),
(15, 1, '$2y$10$dyRYpL.qSVfihPCS.MmASe3JDz4mnLGvBKwi2dbi4cvj8VAFiU4V2', '2025-11-23 09:03:53', 1, '2025-11-23 07:03:53'),
(16, 1, '$2y$10$h2WxZbAgN51E16rgTM/RRu0Id6l077tkHNfjgoWMjTDGpk41MigpO', '2025-11-23 09:03:58', 1, '2025-11-23 07:03:58'),
(17, 1, '$2y$10$vKVpxkxbeg6mX/2RVNrk4e5k7zNKOE739SIS4tMHNKfkK6afhA6cq', '2025-11-23 09:04:07', 1, '2025-11-23 07:04:07'),
(18, 1, '$2y$10$6MyibXIqJ3H99uE0QK9/2OfzgH/4O2hMgZ/WyJtLb2jIDPhP7KH7q', '2025-11-23 09:05:04', 1, '2025-11-23 07:05:04'),
(19, 3, '$2y$10$KKJe73K9QpKGSUNiuvwX4ODpr5iGTBMhV2LsSsSvrYn9evht1LQ3u', '2025-11-23 09:10:11', 1, '2025-11-23 07:10:11'),
(20, 3, '$2y$10$xc7gATd4eyPLEuxJsu2/pOjXbBjkhxoVNa6AHPMEjhM9Nfr1ekGu.', '2025-11-23 09:11:34', 1, '2025-11-23 07:11:34'),
(21, 3, '$2y$10$Eh974KtCbQ85Z2TG4Ur6Hem4axrTtLNWMuiOuT/GLJadc0myLNlr.', '2025-11-23 09:13:06', 1, '2025-11-23 07:13:06'),
(22, 3, '$2y$10$figi7WdmTbwUbAMEWTOE.OKdM13UKpqTHcxy.rBVIPTqvTIyKCwMG', '2025-11-23 09:15:41', 1, '2025-11-23 07:15:41'),
(23, 1, '$2y$10$j9BNX6/JMKj.pdGunvGYPegNjBZDGXXKS2.e7WSfc/9LBMHfRosAa', '2025-11-24 08:51:56', 1, '2025-11-24 06:51:56'),
(24, 1, '$2y$10$pEcDdPvF6HuHZt6AIIvoJOPnagukEiF3pZSjB1zOyyf4R7P6AY//e', '2025-11-24 15:14:14', 1, '2025-11-24 13:14:14'),
(25, 3, '$2y$10$AiwKqPChqlwSy0DEi0Sx0er11GRR3GydZezVwHb4au6ZvWf5Rliym', '2025-11-25 19:33:38', 1, '2025-11-25 17:33:38'),
(26, 3, '$2y$10$uMvlhPxy0Ktj7lH/YijrmO8JwtJoU4IFg7BqXtxNt.zIuE9FJ0iRa', '2025-11-26 04:27:02', 1, '2025-11-26 02:27:02'),
(27, 3, '$2y$10$.a2gkBwzgqXrrNNop9xfpulZtTI.EbxQsdSGuKnwoseDw0Rlb6k9W', '2025-11-26 15:35:24', 1, '2025-11-26 13:35:24'),
(28, 3, '$2y$10$nH.jYdTZMmrAgLE22Bl3vuafsVONKH6q7p3x3X35P3J/AEPX0hnCG', '2025-11-26 15:37:12', 1, '2025-11-26 13:37:12'),
(29, 3, '$2y$10$44lOcNU2wMCuRv36p1MA6.Km9Wp86aCfOubiQRqxGLzBhKgL2FfNK', '2025-11-26 15:48:53', 1, '2025-11-26 13:48:53'),
(30, 3, '$2y$10$l6aiDq8JJ1B8hYVHQhnvs.QdX5kAX49a6ZBSZkMnhh/WhVlTps7eK', '2025-11-26 16:30:05', 1, '2025-11-26 14:30:05'),
(31, 3, '$2y$10$xM5XHDmWYaa9VESgWupHpeuzsc07MZX21CZC2cilN6IDvif8uNQga', '2025-11-26 16:37:17', 1, '2025-11-26 14:37:17'),
(32, 3, '$2y$10$aFyQTfc114hUY5I9b2RxUuVaZdy.fCQzS1PA7Xs81/iaNX3Regp9C', '2025-11-26 16:37:54', 1, '2025-11-26 14:37:54'),
(33, 3, '$2y$10$Wdwv0dBKgyzhBNZfFL5So.urKjmjQoSYOC.dnLD6UVnrrWk.TFfu.', '2025-11-26 16:38:43', 1, '2025-11-26 14:38:43'),
(34, 3, '$2y$10$otubYH03gtEzYLFTlqUKrOtyvSVGIeZRKvaqt9wMzU1uc.8AD0FYK', '2025-11-26 16:43:22', 1, '2025-11-26 14:43:22'),
(35, 3, '$2y$10$BDx0.la9Aw4EjrHmFXPZ7udE1aL9Gex0OPFFnktdL6eH2E4GDqY9a', '2025-11-26 17:05:21', 1, '2025-11-26 15:05:21'),
(36, 3, '$2y$10$hUTPR5KGbcbUxkaXCrhVtem.rD4QTw35FnMzouLTIFVrmxJuJRr0G', '2025-11-26 19:57:50', 1, '2025-11-26 17:57:50');

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `caption` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `image_file` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `user_id`, `caption`, `image_path`, `image_file`, `created_at`, `updated_at`) VALUES
(2, 1, '5 veg meals\r\ni love to donate', NULL, 'uploads/69b361c9990162ba9ab18310.jpg', '2025-11-26 19:26:56', '2025-11-26 19:51:11'),
(3, 22, 'Just donation some items.. grateful for the opputunities', NULL, 'uploads/98fe940f4ff5c551120aa636.jpeg', '2025-11-27 12:03:11', NULL),
(4, 23, 'grateful to donate', NULL, 'uploads/56b4b6b4dfa0d72c1f89e66c.jpeg', '2025-11-27 12:07:57', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `post_comments`
--

CREATE TABLE `post_comments` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `post_comments`
--

INSERT INTO `post_comments` (`id`, `post_id`, `user_id`, `comment`, `created_at`) VALUES
(1, 2, 1, 'Great work!!', '2025-11-26 14:51:53'),
(2, 3, 22, 'amazinggg', '2025-11-27 06:33:45');

-- --------------------------------------------------------

--
-- Table structure for table `post_likes`
--

CREATE TABLE `post_likes` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `post_likes`
--

INSERT INTO `post_likes` (`id`, `post_id`, `user_id`, `created_at`) VALUES
(1, 2, 1, '2025-11-26 14:51:40'),
(2, 3, 22, '2025-11-27 06:33:35'),
(3, 4, 23, '2025-11-27 06:38:06'),
(4, 3, 23, '2025-11-27 07:47:48'),
(5, 2, 23, '2025-11-27 07:47:50');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `report_id` int(11) NOT NULL,
  `reporter_id` int(11) NOT NULL,
  `report_type` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `status` enum('New','InProgress','Resolved','Discarded') DEFAULT 'New',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `accepted_by` int(11) DEFAULT NULL,
  `accepted_at` datetime DEFAULT NULL,
  `lat` decimal(10,7) DEFAULT NULL,
  `lng` decimal(10,7) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`report_id`, `reporter_id`, `report_type`, `description`, `location`, `status`, `created_at`, `accepted_by`, `accepted_at`, `lat`, `lng`) VALUES
(1, 13, 'food', 'food for family of four', 'Moodbidri bus stand', '', '2025-11-25 17:14:31', NULL, NULL, NULL, NULL),
(3, 17, 'clothing', 'cloths for children', 'shirthady', 'New', '2025-11-26 02:50:49', NULL, NULL, NULL, NULL),
(4, 17, 'clothing', 'children winter cloths', 'shirthady', 'New', '2025-11-26 13:04:16', NULL, NULL, NULL, NULL),
(5, 17, 'other', 'blankets and pillows', 'Alangar,Moodbidri', 'New', '2025-11-26 15:23:50', NULL, NULL, NULL, NULL),
(6, 17, 'food', '2kg rice, 1 litre oil, 10 food packets, 3 packets noodles', 'Shirthady', '', '2025-11-26 16:55:57', NULL, NULL, NULL, NULL),
(7, 17, 'food', '2kg rice, 1 litre oil, 10 food packets, 3 packets noodles', 'Shirthady', '', '2025-11-26 17:03:45', NULL, NULL, NULL, NULL),
(8, 19, 'hygiene', 'Women’s Hygiene Kit (Sanitary Pads, Soap, Comb, Wipes, Toothpaste)\r\n\r\nKids Care Kit (Mild Soap, Toothpaste, Soft Brush, Baby Powder)', 'moodbidri', '', '2025-11-26 18:02:04', 1, NULL, 13.0763427, 75.1019942),
(9, 20, 'hygiene', 'Sanitary pads (pack of 10), hand sanitizer, soap bars, hygiene kit', 'Shirthady', 'New', '2025-11-27 02:31:53', NULL, NULL, 13.0872524, 75.0820779),
(10, 20, 'food', 'Kids Care Kit (Mild Soap, Toothpaste, Soft Brush, Baby Powder)', 'Thodar,Mijar', 'New', '2025-11-27 03:11:04', NULL, NULL, 12.2958104, 76.6393805),
(11, 20, 'food', 'food for family of 5', 'Alangar,Moodbidri', 'New', '2025-11-27 05:06:52', NULL, NULL, 12.2958104, 76.6393805),
(12, 20, 'food', 'food for family for 5', 'Thodar,Mijar', 'New', '2025-11-27 06:06:31', NULL, NULL, 12.2958104, 76.6393805),
(13, 20, 'clothing', 'winter cloths', 'Moodbidri bus stand', 'New', '2025-11-27 06:28:54', NULL, NULL, 12.2958104, 76.6393805),
(14, 20, 'food', 'food for family of 4', 'Thodar,Mijar', 'New', '2025-11-27 07:41:47', NULL, NULL, 12.2958104, 76.6393805),
(15, 20, 'food', 'sdhfgbr', 'Moodbidri bus stand', 'New', '2025-11-27 09:48:22', NULL, NULL, 12.9564672, 77.6208384),
(16, 17, 'food', 'fiiddfjkdslaj', 'Thodar,Mijar', 'New', '2025-11-27 10:27:16', NULL, NULL, 13.0443193, 74.9782581);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `role` enum('donor','reporter','admin') NOT NULL,
  `verification_status` enum('none','pending','verified','rejected') DEFAULT 'none',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','blocked') NOT NULL DEFAULT 'active',
  `lat` decimal(10,7) DEFAULT NULL,
  `lng` decimal(10,7) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password_hash`, `phone`, `location`, `role`, `verification_status`, `created_at`, `status`, `lat`, `lng`) VALUES
(1, 'Sheikh Shahnaz', 'sheikhshahnaz909@gmail.com', '$2y$10$grnFitxCBfe4VA1Lwogvzumf29/n826geF6xSf2DMMUOYdkXistnK', '9900776154', 'PARKALA, HERGA, UDUPI', 'donor', 'none', '2025-11-19 13:06:25', 'active', 13.0219520, 74.9680424),
(3, 'Sheikh Shahnaz', 'sheikhshhnz.09@gmail.com', '$2y$10$RIJE0YiNh7lUhDc9HlIzueLHMP5SUjFhafBclOy.au5GiNMgSpkt2', '0990077615', 'PARKALA, HERGA, UDUPI', 'donor', 'none', '2025-11-19 13:18:12', 'active', NULL, NULL),
(4, 'Sz', 'S@GMAIL.COM', '$2y$10$PNY.UDRaReQkAAPo9SnqRuIIaxZaDmkucKdoYPLxpIkRNo8DF94EO', '1111111111', 'udup;i', 'donor', 'none', '2025-11-19 13:19:00', 'active', NULL, NULL),
(5, 'uzma', 'uzma@gmail.com', '$2y$10$3/kttMq9l8uZejsEvcY22OLsiQ3vjArwP5NjSsFduX6PfGr81FSVq', '1234567890', 'manglore', 'donor', 'none', '2025-11-19 13:31:17', 'active', NULL, NULL),
(6, 'tasmia', 't@gmail.com', '$2y$10$7UCHSm8RT9weKRQ9nJbb0OPf1vAe4GA1tOMvN1/8fyDIzZcwHzzYm', '1212121212', 'dflkjdsfkljad', 'reporter', 'none', '2025-11-19 13:57:38', 'active', NULL, NULL),
(7, 'rafa', 'rafa@gmail.com', '$2y$10$A81UmF/MquXLOMU2tsjd2eLOSy.GJvjQexORWBpHfuqHdLXFaMzQ6', '1234567890', 'KUNDAPURA', 'donor', 'none', '2025-11-19 14:08:03', 'active', NULL, NULL),
(9, 'rahul', 'r@gmail.com', '$2y$10$nrlbkYsi3eXXEt.39x98yOKSj5IlTIfecHfaxsssDKdttTYbebTCu', '1212123456', 'INDIA', 'reporter', 'none', '2025-11-20 05:18:03', 'active', NULL, NULL),
(10, 'srustu', 'sru@gmail.com', '$2y$10$D9Zmkezs6DU6PTCMARcC0ONH9/pK3RJUDYJON/T45HYUt3FwXOPYC', '3232323232', 'india', 'reporter', 'none', '2025-11-20 06:15:10', 'active', NULL, NULL),
(11, 'hamu', 'hamu@gmail.com', '$2y$10$HdDGWGrScbPQeeYmTDcK2upkYQJ9r4/xG.6oVSjI3jUMBxePnAnly', '2323232323', 'In', 'reporter', 'none', '2025-11-20 06:16:44', 'active', NULL, NULL),
(12, 'navya', 'n@gmail.com', '$2y$10$Cs26iIqSK.rC/yiBLcjVA.ZqE1CjvT8lWb./PoLbljxHkulEz/fGu', '9898989898', 'INDIA', 'donor', 'none', '2025-11-21 14:39:26', 'active', NULL, NULL),
(13, 'zara', 'z@gmail.com', '$2y$10$0nvQSUEC82qZEscLMFIXXOK.wd.rUiCejxeutE1dWfPEGTccFDCT6', '6767676767', 'india', 'reporter', 'none', '2025-11-23 05:43:29', 'active', NULL, NULL),
(14, 'Tasmia', 'tasmiakhankulkarni@gmail.com', '$2y$10$0YsorLytdTswhSavw3ecfOc5Bu2/UXTh1hYjLLDpk.EFv81GXasmS', '8971573343', 'Ranibennur', 'reporter', 'none', '2025-11-24 06:55:45', 'active', NULL, NULL),
(15, 'riyaz', 'riyaz@gmail.com', '$2y$10$HFm7/UdvQXQWglWWr9T7iePINwppjU3fl7WH/PC/8k4pEVTpLavmO', '8767809453', 'Udupi', 'donor', 'none', '2025-11-25 16:47:18', 'active', NULL, NULL),
(16, 'Dania', 'd@gmail.com', '$2y$10$jUTK5TpsMfRwLg5OQYIP3OjVoazMfpq91Oi/Yk5p1TVnPmXUzlnuy', '1234567890', 'thodar', 'reporter', 'none', '2025-11-25 16:53:49', 'active', NULL, NULL),
(17, 'veekshitha', 'veekshithabandari27@gmail.com', '$2y$10$rJ88FGFukWtlg44ZwRhoUeq.7E6szydFl5AtX06y89XSQoGfPAZPu', '1234567890', 'Moodabidri', 'reporter', 'none', '2025-11-26 02:30:38', 'active', NULL, NULL),
(19, 'Rafa Rafeeque', 'rafarafee@gmail.com', '$2y$10$UK/0U2JfeEUQ/BzTdeoSVOAuPdKsdHRpx.tsIE99Z.tIMpl2mm6zu', '1212345478', 'kannlur', 'reporter', 'none', '2025-11-26 18:00:52', 'active', NULL, NULL),
(20, 'Uzma Amir', 'uzmaubaid7@gmail.com', '$2y$10$wZzRu73BRMfw3sSBPH3Hwugs7aJo/NK5zzazucQAh6gS33aDFN076', '7338351032', 'Shirthady', 'reporter', 'none', '2025-11-27 02:00:48', 'active', NULL, NULL),
(21, 'Rafa Sukro', 'rafasukri@gmail.com', '$2y$10$s631zl4pCk0DNvpqme0bHe/rjshtn.ipmP3T46nJWCkYyM16HutV6', '7865998436', 'thodar', 'donor', 'none', '2025-11-27 02:02:23', 'active', 13.0763427, 75.1019942),
(22, 'Rafa Rafeeque', 'rafarafeeque086@gmail.com', '$2y$10$wqFpoZZh/wPTjQP.3vZELugHrdgZYVPhGMCV3hvAeDxt.mqC9dMi2', '8976774301', 'Moodabidri', 'donor', 'none', '2025-11-27 03:59:15', 'active', 13.0458463, 74.9700487),
(23, 'Rehmat Fatima', 'shaikhtasmia343@gmail.com', '$2y$10$UWKHephHS4Z7nWxi9CY9GeZnUbwsiQXo8j10jWSSNwCVx90cBqR8G', '6754998713', 'yedepoda', 'donor', 'none', '2025-11-27 06:35:20', 'active', 13.0443935, 74.9782885),
(24, 'Uzma', '23cs-uzma5181@yit.edu.in', '$2y$10$Os7Xe/5TxOkhv8pEo63zTeKIoLmWZ8xDD7Uhr0kZEac4LNtRkdk6C', '8123898994', 'Thodar', 'donor', 'none', '2025-11-27 10:24:55', 'active', 13.0443193, 74.9782581);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `donations`
--
ALTER TABLE `donations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_donations_donor` (`donor_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_isread` (`user_id`,`is_read`),
  ADD KEY `idx_related` (`related_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `post_comments`
--
ALTER TABLE `post_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `post_likes`
--
ALTER TABLE `post_likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `post_id` (`post_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `reporter_id` (`reporter_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `donations`
--
ALTER TABLE `donations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `post_comments`
--
ALTER TABLE `post_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `post_likes`
--
ALTER TABLE `post_likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `donations`
--
ALTER TABLE `donations`
  ADD CONSTRAINT `fk_donations_donor` FOREIGN KEY (`donor_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `post_comments`
--
ALTER TABLE `post_comments`
  ADD CONSTRAINT `post_comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `post_comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `post_likes`
--
ALTER TABLE `post_likes`
  ADD CONSTRAINT `post_likes_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `post_likes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
