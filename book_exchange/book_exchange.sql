-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 25, 2025 at 07:42 AM
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
-- Database: `book_exchange`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `username`, `email`, `password`, `created_at`) VALUES
(1, 'Kanishka', 'kanishkaramesh60@gmail.com', '$2y$10$PQmgGA7vekrXn7SxRxRZjunIGg/69xw230Pw13V6BkK26va.wrx1m', '2025-10-16 14:34:57'),
(38, 'ResourceX', 'resourcex04@gmail.com', '$2y$10$PQmgGA7vekrXn7SxRxRZjunIGg/69xw230Pw13V6BkK26va.wrx1m', '2025-10-25 05:10:21');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL,
  `resource_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `booked_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`booking_id`, `resource_id`, `user_id`, `booked_at`) VALUES
(11, 13, 4, '2025-10-23 15:54:34');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `message_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0,
  `is_deleted` tinyint(1) DEFAULT 0,
  `sender_deleted` tinyint(1) DEFAULT 0,
  `receiver_deleted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`message_id`, `sender_id`, `receiver_id`, `subject`, `message`, `created_at`, `is_read`, `is_deleted`, `sender_deleted`, `receiver_deleted`) VALUES
(20, 1, 2, 'Reply', 'hello', '2025-10-22 14:33:38', 0, 1, 1, 1),
(21, 1, 2, 'Reply', 'haii', '2025-10-22 15:07:12', 0, 0, 1, 1),
(22, 1, 2, 'Reply', 'I wanted to know about your resources uploaded', '2025-10-23 15:08:28', 0, 0, 1, 0),
(23, 4, 1, 'Regarding: Sample 1', 'how to view it', '2025-10-23 15:53:51', 0, 0, 1, 0),
(24, 4, 1, 'Your resource \'Notes\' has been booked!', 'Hello , your resource \'Notes\' was booked by .', '2025-10-23 15:54:34', 0, 0, 0, 0),
(25, 4, 1, 'Your resource \'Notes\' has been booked!', 'Hello Kanishka, your resource titled \'Notes\' was booked by Ramesh.', '2025-10-23 15:54:34', 0, 0, 0, 0),
(26, 2, 1, 'Your resource \'Sample 1\' has been booked!', 'Hello , your resource \'Sample 1\' was booked by .', '2025-10-24 09:45:20', 0, 0, 0, 1),
(27, 2, 1, 'Your resource \'Sample 1\' has been booked!', 'Hello Kanishka, your resource titled \'Sample 1\' was booked by Kanishka R.', '2025-10-24 09:45:20', 0, 0, 0, 1),
(28, 2, 1, 'Your resource \'book\' has been booked!', 'Hello , your resource \'book\' was booked by .', '2025-10-24 14:23:00', 0, 0, 0, 1),
(29, 2, 1, 'Your resource \'book\' has been booked!', 'Hello Kanishka, your resource titled \'book\' was booked by Kanishka R.', '2025-10-24 14:23:00', 0, 0, 0, 1),
(30, 2, 1, 'Your resource \'book\' has been booked!', 'Hello , your resource \'book\' was booked by .', '2025-10-25 04:12:17', 0, 0, 0, 0),
(31, 2, 1, 'Your resource \'book\' has been booked!', 'Hello Kanishka, your resource titled \'book\' was booked by Kanishka R....', '2025-10-25 04:12:17', 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `resource_id` int(11) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `favorites`
--

INSERT INTO `favorites` (`id`, `user_id`, `resource_id`, `added_at`) VALUES
(3, 4, 12, '2025-10-23 16:47:39'),
(10, 2, 12, '2025-10-25 04:11:40');

-- --------------------------------------------------------

--
-- Table structure for table `modification_requests`
--

CREATE TABLE `modification_requests` (
  `id` int(11) NOT NULL,
  `resource_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `request_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `modification_requests`
--

INSERT INTO `modification_requests` (`id`, `resource_id`, `user_id`, `status`, `request_date`) VALUES
(1, 15, 1, '', '2025-10-23 20:14:05');

-- --------------------------------------------------------

--
-- Table structure for table `resources`
--

CREATE TABLE `resources` (
  `resource_id` int(11) NOT NULL,
  `added_by` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resources`
--

INSERT INTO `resources` (`resource_id`, `added_by`, `title`, `description`, `category`, `status`, `image`, `created_at`) VALUES
(2, 1, 'C programming', 'good', 'Textbook', 'approved', NULL, '2025-10-16 15:23:53'),
(12, 1, 'book', 'sample', 'Textbook', 'approved', NULL, '2025-10-22 09:35:19'),
(13, 1, 'Notes', 'Test', 'Notes', '', 'uploads/1761126054_WhatsApp_Image_2025-10-12_at_13.44.02_130a5da1.jpg', '2025-10-22 09:40:54'),
(15, 1, 'Sample 1', 'Test 1', 'Stationery', 'approved', 'uploads/1761229216_WhatsApp_Image_2025-10-12_at_13.44.02_130a5da1.jpg', '2025-10-23 14:20:16');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `otp_code` varchar(10) DEFAULT NULL,
  `otp_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `location`, `phone`, `password`, `reset_token`, `created_at`, `otp_code`, `otp_expiry`) VALUES
(1, 'Kanishka', 'kanishkaramesh60@gmail.com', 'Coimbatore', NULL, '$2y$10$NGrJzN4ySLklANhj8L7Rn.HH27ZzOEal/PfkN29Qe2Veb1zo28ZFO', NULL, '2025-10-16 14:39:51', NULL, NULL),
(2, 'Kanishka R...', 'kanishkar24cy@srishakthi.ac.in', '', '9342900110', '$2y$10$j6dK4IzQwJM4doLp9y2rQuCeNsFdE4eK.bCmwXexHcizJUQCAMkMq', NULL, '2025-10-16 15:04:40', NULL, NULL),
(3, 'Hlo', 'engineeringexplorationbatch6@gmail.com', NULL, NULL, '$2y$10$iP8CviI/4mxzcqrrk41J1e//9VEIy9nNQbG6pfIjPFtCYERfNR6cO', NULL, '2025-10-17 07:33:08', NULL, NULL),
(4, 'Ramesh', 'kanikani2503@gmail.com', NULL, '9342900110', '$2y$10$QQ3lOkIlCphooxjCdhwxQeYSTz4ctHyAyADvrvaRQRncIB4IucVuO', NULL, '2025-10-23 15:51:39', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `idx_resource_id` (`resource_id`),
  ADD KEY `idx_booking_user` (`user_id`),
  ADD KEY `idx_bookings_resource_id` (`resource_id`),
  ADD KEY `idx_bookings_user_id` (`user_id`),
  ADD KEY `idx_bookings_resource` (`resource_id`),
  ADD KEY `idx_bookings_user` (`user_id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `idx_sender` (`sender_id`),
  ADD KEY `idx_receiver` (`receiver_id`),
  ADD KEY `idx_contact_sender` (`sender_id`),
  ADD KEY `idx_contact_receiver` (`receiver_id`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_favorite` (`user_id`,`resource_id`),
  ADD KEY `resource_id` (`resource_id`),
  ADD KEY `idx_favorites_user` (`user_id`);

--
-- Indexes for table `modification_requests`
--
ALTER TABLE `modification_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `resource_id` (`resource_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `resources`
--
ALTER TABLE `resources`
  ADD PRIMARY KEY (`resource_id`),
  ADD KEY `idx_user_id` (`added_by`),
  ADD KEY `idx_resources_user_id` (`added_by`),
  ADD KEY `idx_resources_added_by` (`added_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `modification_requests`
--
ALTER TABLE `modification_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `resources`
--
ALTER TABLE `resources`
  MODIFY `resource_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`resource_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD CONSTRAINT `contact_messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `contact_messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`resource_id`) ON DELETE CASCADE;

--
-- Constraints for table `modification_requests`
--
ALTER TABLE `modification_requests`
  ADD CONSTRAINT `modification_requests_ibfk_1` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`resource_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `modification_requests_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `resources`
--
ALTER TABLE `resources`
  ADD CONSTRAINT `resources_ibfk_1` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
