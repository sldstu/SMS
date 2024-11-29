-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 05, 2024 at 01:43 PM
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
-- Database: `sports_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `course_id` int(11) NOT NULL,
  `course_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`course_id`, `course_name`) VALUES
(1, 'BSNs'),
(4, 'BSEs'),
(8, 'BSNsss'),
(10, 'BSAa'),
(12, 'dada'),
(16, 'Economics');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `event_id` int(11) NOT NULL,
  `event_name` varchar(100) NOT NULL,
  `event_date` date NOT NULL,
  `teacher_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`event_id`, `event_name`, `event_date`, `teacher_id`) VALUES
(7, 'dd party', '2024-11-14', NULL),
(10, 'dddddd', '2024-11-15', NULL),
(16, 'birthday', '2024-11-21', NULL),
(18, 'meme', '2024-10-18', NULL),
(19, 'birthday', '2024-11-21', 19);

-- --------------------------------------------------------

--
-- Table structure for table `registrations`
--

CREATE TABLE `registrations` (
  `registration_id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `sport_id` int(11) DEFAULT NULL,
  `status` enum('pending','approved','declined') NOT NULL DEFAULT 'pending',
  `name` varchar(100) NOT NULL,
  `sex` enum('Male','Female') NOT NULL,
  `course` enum('CS','IT','ACT') NOT NULL,
  `section` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registrations`
--

INSERT INTO `registrations` (`registration_id`, `student_id`, `sport_id`, `status`, `name`, `sex`, `course`, `section`) VALUES
(17, 14, 12, 'approved', 'Nesty Omongos', 'Female', 'CS', '2A'),
(18, 17, 12, 'declined', 'Cathy', 'Female', 'IT', '1A');

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE `sections` (
  `section_id` int(11) NOT NULL,
  `section_name` varchar(100) NOT NULL,
  `course_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sections`
--

INSERT INTO `sections` (`section_id`, `section_name`, `course_id`) VALUES
(6, 'BSN', 1),
(13, '2A', 4),
(15, '4A', 1),
(16, '5A', 4),
(23, '1A', 8),
(24, '2A', 10),
(26, '1A', 12),
(28, '1A', 16);

-- --------------------------------------------------------

--
-- Table structure for table `sports`
--

CREATE TABLE `sports` (
  `sport_id` int(11) NOT NULL,
  `sport_name` varchar(100) NOT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `sport_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sports`
--

INSERT INTO `sports` (`sport_id`, `sport_name`, `teacher_id`, `sport_image`) VALUES
(12, 'Badminton', NULL, NULL),
(14, 'chess', NULL, NULL),
(15, 'basketball', NULL, NULL),
(16, 'Football', NULL, NULL),
(18, 'gg', NULL, NULL),
(19, 'dama', 19, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(100) NOT NULL,
  `role` enum('student','teacher','admin') NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `datetime_sign_up` datetime DEFAULT current_timestamp(),
  `datetime_last_online` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `role`, `first_name`, `last_name`, `datetime_sign_up`, `datetime_last_online`) VALUES
(14, 'nesty hihi', '$2y$10$nocEbR.ohU.qwVaejwGbfe7fLT9NF4w4VBrQN3vNZk4epFu0qPTE6', 'student', 'Nesty', 'Students', '2024-11-05 00:37:02', '2024-11-05 00:37:02'),
(15, 'admin', '$2y$10$oLz1CijvbsKTNAbuTGnEAunCbAO131fX0Olga4I5oU/4Q.GQd9Vd6', 'admin', 'admin', 'admin', '2024-11-05 00:37:02', '2024-11-05 16:14:35'),
(17, 'student', '$2y$10$O8CT9BNQ4aSqrK6v9EsUxOEI1WSLvNHMmBo2.tQzklTAQQ1525uxe', 'student', 'student', 'student', '2024-11-05 00:37:02', '2024-11-05 15:32:14'),
(18, 'nesty315', '$2y$10$DzA/3bmku.yhoopQBIyYNusVf5nUQ/w3BZsGnkGywwbJX0pkTMWXW', 'student', 'Nesty', 'Omongos', '2024-11-05 00:43:25', '2024-11-05 00:43:25'),
(19, 'teacher', '$2y$10$HroWP0HQQh2n5wffRRbQR.EvKoe7ee2/hgbVki5RC/XHlPoXn63Ca', 'teacher', 'teacher', 'teacher', '2024-11-05 00:50:58', '2024-11-05 16:14:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`course_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`event_id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indexes for table `registrations`
--
ALTER TABLE `registrations`
  ADD PRIMARY KEY (`registration_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `sport_id` (`sport_id`);

--
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`section_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `sports`
--
ALTER TABLE `sports`
  ADD PRIMARY KEY (`sport_id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `course_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `registrations`
--
ALTER TABLE `registrations`
  MODIFY `registration_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `section_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `sports`
--
ALTER TABLE `sports`
  MODIFY `sport_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `registrations`
--
ALTER TABLE `registrations`
  ADD CONSTRAINT `registrations_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `registrations_ibfk_2` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`sport_id`);

--
-- Constraints for table `sections`
--
ALTER TABLE `sections`
  ADD CONSTRAINT `sections_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE;

--
-- Constraints for table `sports`
--
ALTER TABLE `sports`
  ADD CONSTRAINT `sports_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
