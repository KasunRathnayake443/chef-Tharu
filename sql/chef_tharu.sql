-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 31, 2026 at 07:20 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `chef_tharu`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int NOT NULL,
  `username` varchar(100) NOT NULL,
  `full_name` varchar(150) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `full_name`, `password`, `created_at`) VALUES
(1, 'admin', 'Main Admin', '$2y$10$4UcsnJGh8x35R.eUzMAC3.ew8jy.8bsCB9j5TifSEq/R2X3w0hY/q', '2026-03-30 08:54:13'),
(2, 'kasun', 'Kasun Rathnayake', '$2y$10$GCgym6u3lIUAEx0qvgzoJeDOrMtnE7MiYXeDNn1OLrCuOhjHYL2TK', '2026-03-30 09:35:27');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int NOT NULL,
  `name` varchar(150) NOT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `sort_order`, `created_at`) VALUES
(3, 'Cheff Tharu Special', 1, '2026-03-30 13:57:01'),
(4, 'Vip Night', 2, '2026-03-30 13:57:09'),
(5, 'Vegan Menu', 4, '2026-03-30 13:57:18'),
(6, 'Chicken blast', 0, '2026-03-30 13:57:26');

-- --------------------------------------------------------

--
-- Table structure for table `foods`
--

CREATE TABLE `foods` (
  `id` int NOT NULL,
  `category_id` int NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `image` varchar(255) DEFAULT NULL,
  `available` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `foods`
--

INSERT INTO `foods` (`id`, `category_id`, `name`, `description`, `price`, `image`, `available`, `created_at`) VALUES
(2, 3, 'Devilled Pork', 'Sri Lankan Devil Pork', 1500.00, 'food_1774863540_b2f6fc64.jpg', 1, '2026-03-30 09:39:00'),
(3, 5, 'Vegan Fried Rice', 'Vegan Rice', 1000.00, 'food_1774879120_aed1932d.webp', 1, '2026-03-30 13:58:40'),
(4, 6, 'Chicken curry', 'Chicken curry', 1500.00, 'food_1774879267_ffc4b560.jpg', 1, '2026-03-30 14:01:07'),
(5, 4, 'Vip Blast', 'Full Course menu', 8000.00, 'food_1774879338_56053022.jpg', 1, '2026-03-30 14:02:18');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int NOT NULL,
  `order_ref` varchar(50) NOT NULL,
  `cust_name` varchar(200) NOT NULL,
  `cust_phone` varchar(50) NOT NULL,
  `cust_email` varchar(200) DEFAULT NULL,
  `cust_address` text NOT NULL,
  `cust_notes` text,
  `total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` enum('pending','confirmed','preparing','ready','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_ref`, `cust_name`, `cust_phone`, `cust_email`, `cust_address`, `cust_notes`, `total`, `status`, `created_at`) VALUES
(1, 'ORD-1001', 'Kasun Rathnayake', '+94771234567', 'kasun@example.com', 'No. 25, Negombo Road, Kurunegala', 'Less spicy please', 3500.00, 'pending', '2026-03-30 09:49:44'),
(2, 'ORD-1002', 'Nimal Perera', '+94781234567', 'nimal@gmail.com', 'Colombo 07', '', 2200.00, 'delivered', '2026-03-30 09:49:44'),
(5, 'ORD-1006', 'Kasun Rathnayake', '+94771234567', 'kasun@example.com', 'No. 25, Negombo Road, Kurunegala', 'Less spicy please', 3500.00, 'pending', '2026-03-30 09:56:44'),
(7, 'ORD-20260330-BC0A5E', 'Kasun Rathnayake', '+94718888888', 'kasunrathnayake121@gmail.com', '123\n1 st lane', 'add more spiceeeee', 9000.00, 'pending', '2026-03-30 10:23:06'),
(8, 'ORD-20260330-E1A5D6', 'Kasun Rathnayake', '+94718888888', 'kasunrathnayake121@gmail.com', '123\n1 st lane', 'kk', 3000.00, 'pending', '2026-03-30 10:56:50'),
(9, 'ORD-20260330-ED6300', 'Kasun Rathnayake', '+94718888888', 'kasunrathnayake121@gmail.com', '123\n1 st lane', 'no  notes', 1500.00, 'pending', '2026-03-30 13:55:59');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `food_id` int DEFAULT NULL,
  `item_name` varchar(200) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `qty` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `food_id`, `item_name`, `price`, `qty`) VALUES
(1, 1, NULL, 'Chicken Kottu', 1200.00, 2),
(2, 1, 2, 'Egg Fried Rice', 1100.00, 1),
(4, 1, NULL, 'Chicken Kottu', 1200.00, 2),
(5, 1, 2, 'Egg Fried Rice', 1100.00, 1),
(6, 7, 2, 'Devilled Pork', 1500.00, 3),
(7, 7, NULL, 'Chicken curry', 1500.00, 3),
(8, 8, 2, 'Devilled Pork', 1500.00, 1),
(9, 8, NULL, 'Chicken curry', 1500.00, 1),
(10, 9, 2, 'Devilled Pork', 1500.00, 1);

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
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `foods`
--
ALTER TABLE `foods`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_food_category` (`category_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_ref` (`order_ref`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_order_items_order` (`order_id`),
  ADD KEY `fk_order_items_food` (`food_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `foods`
--
ALTER TABLE `foods`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `foods`
--
ALTER TABLE `foods`
  ADD CONSTRAINT `fk_food_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_items_food` FOREIGN KEY (`food_id`) REFERENCES `foods` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
