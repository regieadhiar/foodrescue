-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 13, 2026 at 12:57 AM
-- Server version: 12.2.2-MariaDB-log
-- PHP Version: 8.2.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `foodrescue`
--

-- --------------------------------------------------------

--
-- Table structure for table `food_items`
--

CREATE TABLE `food_items` (
  `id` int(11) NOT NULL,
  `merchant_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `image_url` text DEFAULT NULL,
  `original_price` decimal(10,2) NOT NULL,
  `rescue_price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `expiry_time` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `food_items`
--

INSERT INTO `food_items` (`id`, `merchant_id`, `title`, `description`, `image_url`, `original_price`, `rescue_price`, `quantity`, `expiry_time`, `created_at`) VALUES
(1, 1, 'Nasi Goreng', 'Baru Masak', 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=400&q=80', 12000.00, 8000.00, 5, '2026-06-16 01:54:00', '2026-06-15 23:54:00'),
(2, 1, 'Test', 'Enak', '[\"uploads\\/food_6a349466e9eba_0.webp\"]', 8000.00, 5000.00, 16, '2026-06-19 12:59:18', '2026-06-19 00:59:18'),
(3, 1, 'Donat', 'Enak', '[\"uploads\\/food_6a362061dab3e_0.png\"]', 10000.00, 3000.00, 9, '2026-06-20 17:08:49', '2026-06-20 05:08:49'),
(4, 1, 'Donat', 'Donat', '[\"uploads\\/food_6a38e63bb06cd_0.webp\"]', 8000.00, 5000.00, 1, '2026-06-22 09:37:31', '2026-06-22 07:37:31'),
(5, 1, 'Kue', 'Kue', '[\"uploads\\/food_6a38e6882c219_0.png\"]', 5000.00, 1000.00, 1, '2026-06-22 12:38:48', '2026-06-22 07:38:48'),
(6, 2, 'Donat Campur', 'Donat', '[\"uploads\\/food_6a38e6f10213d_0.png\"]', 8000.00, 3000.00, 16, '2026-06-22 15:40:33', '2026-06-22 07:40:33');

-- --------------------------------------------------------

--
-- Table structure for table `merchants`
--

CREATE TABLE `merchants` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `business_name` varchar(100) NOT NULL,
  `address` text NOT NULL,
  `latitude` double NOT NULL,
  `longitude` double NOT NULL,
  `phone` varchar(20) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `merchants`
--

INSERT INTO `merchants` (`id`, `user_id`, `business_name`, `address`, `latitude`, `longitude`, `phone`, `is_active`, `created_at`) VALUES
(1, 2, 'Resto Maju', 'Garut', -7.215627, 107.905118, '082108210821', 1, '2026-06-15 23:51:44'),
(2, 4, 'Warung Nasi', 'Samping Jalan Utama', -7.212037, 107.907383, '082108210821', 1, '2026-06-18 05:38:13');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `food_item_id` int(11) NOT NULL,
  `rescuer_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `payment_method` varchar(20) NOT NULL DEFAULT 'cash',
  `payment_status` varchar(20) NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `food_item_id`, `rescuer_id`, `quantity`, `status`, `created_at`, `payment_method`, `payment_status`) VALUES
(1, 2, 5, 1, 'completed', '2026-06-19 01:00:48', 'cash', 'paid'),
(2, 2, 4, 1, 'completed', '2026-06-19 01:09:20', 'cash', 'paid'),
(3, 3, 2, 1, 'completed', '2026-06-20 05:37:42', 'cash', 'paid'),
(4, 6, 6, 1, 'completed', '2026-06-22 07:42:43', 'cash', 'paid');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'rescuer',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `reset_token` varchar(100) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `remember_token`, `created_at`, `reset_token`, `reset_token_expiry`, `profile_picture`) VALUES
(1, 'admin', 'admin@foodrescue.com', '$2y$10$9zJk8TS7S4bMS1NJXqkw6.zbFi9NVtE7XobvWR.sy3KSjC9nEuaDC', 'admin', '02c6943828c9445c71179cbddb762558482854a894f9a774de0f918ccb5856a6', '2026-06-15 23:39:39', NULL, NULL, 'uploads/profile_1_6a4fa1b1766a1.png'),
(2, 'restomaju', 'restomaju@mail.com', '$2y$10$r8ygewruEYKgR.kRbJTcV.fzeOSlvYvBMpbSIJNttAhkpqto8g7su', 'merchant', NULL, '2026-06-15 23:51:44', '722944cf77cd9b6e9682ab7b374d5eb892ddb3dd3920fc084fe5d55c7d03b243', '2026-06-19 01:04:34', NULL),
(3, 'regie', 'regie@mail.com', '$2y$10$uIRXhuYL9t464oKOnSoPZOitrf/cE.XufRl6JkyDXtr0hMP.X9HKO', 'rescuer', NULL, '2026-06-16 00:00:41', NULL, NULL, NULL),
(4, 'cecepnasgor', 'cecep@mail.com', '$2y$10$LOEaXO3g/1GLxOJMhgSZOOJv6CNCbr5IcqvhnWPNT/mjWPgkvgQSO', 'merchant', NULL, '2026-06-18 05:38:13', 'f036ee7c456fa24a6073bacf1cdc27aa87b63c64ac1afe1da2ed94e3e4dbafe4', '2026-06-18 05:54:08', 'uploads/profile_4_6a4f9bc4aaca2.png'),
(5, 'egy', 'egy@mail.com', '$2y$10$T771MwGKRm1IQm1VtRU3POdSSW8ASSTLW47q/q7gTpw1fXR9jvrq.', 'rescuer', NULL, '2026-06-19 01:00:37', NULL, NULL, NULL),
(6, 'asad', 'asad@mail.com', '$2y$10$.JndSVdRKdUrp40bRvvgw.IutCyZNJsd45osQxbBofBjHQuP0FlCm', 'rescuer', NULL, '2026-06-22 07:42:19', NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `food_items`
--
ALTER TABLE `food_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `merchant_id` (`merchant_id`);

--
-- Indexes for table `merchants`
--
ALTER TABLE `merchants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `food_item_id` (`food_item_id`),
  ADD KEY `rescuer_id` (`rescuer_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `food_items`
--
ALTER TABLE `food_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `merchants`
--
ALTER TABLE `merchants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `food_items`
--
ALTER TABLE `food_items`
  ADD CONSTRAINT `1` FOREIGN KEY (`merchant_id`) REFERENCES `merchants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `merchants`
--
ALTER TABLE `merchants`
  ADD CONSTRAINT `1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `1` FOREIGN KEY (`food_item_id`) REFERENCES `food_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `2` FOREIGN KEY (`rescuer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
