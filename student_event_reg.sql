-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 22, 2026 at 01:03 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `student_event_reg`
--

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `event_date` datetime NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `slots` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `thumbnail` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `category`, `description`, `event_date`, `location`, `slots`, `created_at`, `thumbnail`) VALUES
(5, 'PABILISAN MAG', 'MAUBUSAN NG PASENSYA', 'BULAG SI REHAN DI MAKITA TO', '2026-05-22 04:32:00', 'HANAPIN NYO NLNG PATANONG SA KALBO SA HALLWAY', 48, '2026-05-21 18:33:11', '1779388391_WIN_20221207_12_38_39_Pro.jpg'),
(7, 'Basketball', 'SPORTS', 'pwede magsuntokan basta walang tamaan', '2026-05-23 16:00:00', 'Main Court', 19, '2026-05-22 03:24:22', '1779420262_images.jpg'),
(8, 'Volleyball', 'SPORTS', 'volleyball only', '2026-05-23 14:40:00', 'Main Court', 21, '2026-05-22 06:41:31', '1779432091_102569.jpeg'),
(9, 'Badminton Tryout', 'SPORTS', 'Join NOW! For upcoming tryout.', '2026-11-28 14:00:00', 'Main Court', 21, '2026-05-22 09:59:08', '1779443948_BADMINTON.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `registrations`
--

CREATE TABLE `registrations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `registered_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registrations`
--

INSERT INTO `registrations` (`id`, `user_id`, `event_id`, `registered_at`) VALUES
(11, 4, 5, '2026-05-21 18:33:38'),
(12, 6, 5, '2026-05-21 19:00:38'),
(15, 7, 7, '2026-05-22 06:32:15'),
(17, 8, 8, '2026-05-22 06:50:39'),
(18, 9, 8, '2026-05-22 07:05:24'),
(19, 9, 7, '2026-05-22 07:06:17'),
(32, 10, 9, '2026-05-22 10:26:14');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','student') DEFAULT 'student',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_pic` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`, `profile_pic`) VALUES
(4, 'Rehan Alamada', 'user@school.edu', '$2y$10$H/SjkGxqRBd/ng8Z3wqr/u3m/SVtLC8/dfq7HI7Yehhw8NxN3br3S', 'student', '2026-05-21 09:41:10', NULL),
(5, 'Rehan Alamada', 'admin@school.edu', '$2y$10$TwbRLlbh62qNWM0F2Ajy3uMJgpJS5Pm4igRdkDJa9znVQhi70t8vy', 'admin', '2026-05-21 15:45:42', NULL),
(6, 'natoy', 'natoy@akoto.com', '$2y$10$aF8FMM9iddtEfoWwMv4lceYDPuM23YF2gpuhONTCmC4wqd7B9MIF2', 'student', '2026-05-21 16:24:35', NULL),
(7, 'sofia solemne san antonio', 'sofia@edu.ph', '$2y$10$St5lB7EN9qTAWGZ06qQR0OU311ZLm8Bc7.NLo2GWnU4soeFEuvGiC', 'student', '2026-05-22 03:26:14', '1779422029_images (1).jpg'),
(8, 'Angelica Absalon', 'angel@gmail.com', '$2y$10$R/5w3xi1ymn0Tr/wSKUTyeVlr924HyLCHGI89eC91lVNBrjQyjvn2', 'student', '2026-05-22 06:50:08', NULL),
(9, 'Lance Macawiwili', 'lanceta@fliptop.com', '$2y$10$RRkojTIKHi1MqUYbX/M05ehfveUBpFaY2ci0B0GMV5Q9i2iVTfeLa', 'student', '2026-05-22 07:03:49', NULL),
(10, 'Renz Christian M. Malco', 'malco.renz26@gmail.com', '$2y$10$/N/MqB3C39yYIUI3L0Z2gOfXwHxYxMjWfFZT13SI03/ZVAfkIBVx6', 'student', '2026-05-22 08:47:48', '1779445663_images (2).jpg');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `registrations`
--
ALTER TABLE `registrations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `event_id` (`event_id`);

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
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `registrations`
--
ALTER TABLE `registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `registrations`
--
ALTER TABLE `registrations`
  ADD CONSTRAINT `registrations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `registrations_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
