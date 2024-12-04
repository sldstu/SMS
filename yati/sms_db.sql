-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 04, 2024 at 06:49 AM
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
-- Database: `sms_db`
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
  `teacher_id` int(11) DEFAULT NULL,
  `time` time DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `facilitator` varchar(255) DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT 0,
  `image` blob DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `facilitator_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`event_id`, `event_name`, `event_date`, `teacher_id`, `time`, `location`, `facilitator`, `is_deleted`, `image`, `image_path`, `description`, `facilitator_id`) VALUES
(0, 'Celebration', '2024-12-03', 1, '11:53:00', 'Philippines', 'Juan Daw', 0, 0x75706c6f6164732f30633632656135333261656361613466633563333130363839383938336535362e6a7067, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `registrations`
--

CREATE TABLE `registrations` (
  `registration_id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `sport_id` int(11) DEFAULT NULL,
  `status` enum('pending','approved','declined') NOT NULL DEFAULT 'pending',
  `last_name` varchar(100) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `contact_info` varchar(15) NOT NULL,
  `age` int(3) NOT NULL,
  `height` decimal(5,2) NOT NULL,
  `weight` decimal(5,2) NOT NULL,
  `bmi` decimal(5,2) GENERATED ALWAYS AS (`weight` / (`height` / 100 * (`height` / 100))) STORED,
  `medcert` blob DEFAULT NULL,
  `cor_pic` blob DEFAULT NULL,
  `id_pic` blob DEFAULT NULL,
  `sex` enum('Male','Female') NOT NULL,
  `course` enum('CS','IT','ACT') NOT NULL,
  `section` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registrations`
--

INSERT INTO `registrations` (`registration_id`, `student_id`, `sport_id`, `status`, `last_name`, `first_name`, `contact_info`, `age`, `height`, `weight`, `medcert`, `cor_pic`, `id_pic`, `sex`, `course`, `section`) VALUES
(132, 0, 12, 'pending', 'Saludo', 'Carl', '09123456789', 20, 159.00, 45.00, '', '', '', 'Male', 'CS', '2C');

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE `sections` (
  `section_id` int(11) NOT NULL,
  `section_name` varchar(100) NOT NULL,
  `course_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sports`
--

CREATE TABLE `sports` (
  `sport_id` int(11) NOT NULL,
  `sport_name` varchar(100) NOT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `event_name` varchar(255) DEFAULT NULL,
  `event_id` int(11) DEFAULT NULL,
  `sport_date` date DEFAULT NULL,
  `sport_time` time DEFAULT NULL,
  `sport_location` varchar(255) DEFAULT NULL,
  `sport_facilitator` varchar(255) DEFAULT NULL,
  `sport_image` blob DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sports`
--

INSERT INTO `sports` (`sport_id`, `sport_name`, `teacher_id`, `event_name`, `event_id`, `sport_date`, `sport_time`, `sport_location`, `sport_facilitator`, `sport_image`) VALUES
(0, 'Arnis', NULL, NULL, 1, '2024-12-03', '11:54:00', 'Philippines', 'Juan Daw', 0x75706c6f6164732f31613830303732656230313039346666353861306139656565383766353062612e6a7067),
(12, 'Badminton', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14, 'chess', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(16, 'Football', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(21, 'Esports', 15, NULL, 26, '2024-12-16', '21:02:00', 'wmsu', 'carlito', 0x75706c6f6164732f53637265656e73686f7420283137292e706e67),
(23, 'coders', NULL, NULL, 38, '2024-12-24', '21:38:00', 'wmsu', 'nesty', 0x75706c6f6164732f53637265656e73686f7420323032342d30312d3331203133303936202e706e67);

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
(0, 'Carl Saludo', '$2y$10$QPmtjp3IaYpZAU0feIY1vu1b3xz7.G43tuT6STivc2Dsiwj9w6hiS', 'student', 'Carl', 'Saludo', '2024-12-03 23:19:30', '2024-12-03 23:19:30'),
(14, 'nesty hihi', '$2y$10$nocEbR.ohU.qwVaejwGbfe7fLT9NF4w4VBrQN3vNZk4epFu0qPTE6', 'student', 'Nesty', 'Students', '2024-11-05 00:37:02', '2024-11-05 00:37:02'),
(15, 'admin', '$2y$10$oLz1CijvbsKTNAbuTGnEAunCbAO131fX0Olga4I5oU/4Q.GQd9Vd6', 'admin', 'admin', 'admin', '2024-11-05 00:37:02', '2024-11-05 16:14:35'),
(17, 'student', '$2y$10$O8CT9BNQ4aSqrK6v9EsUxOEI1WSLvNHMmBo2.tQzklTAQQ1525uxe', 'student', 'student', 'student', '2024-11-05 00:37:02', '2024-11-05 15:32:14'),
(18, 'nesty315', '$2y$10$DzA/3bmku.yhoopQBIyYNusVf5nUQ/w3BZsGnkGywwbJX0pkTMWXW', 'student', 'Nesty', 'Omongos', '2024-11-05 00:43:25', '2024-11-05 00:43:25'),
(19, 'teacher', '$2y$10$HroWP0HQQh2n5wffRRbQR.EvKoe7ee2/hgbVki5RC/XHlPoXn63Ca', 'teacher', 'teacher', 'teacher', '2024-11-05 00:50:58', '2024-11-05 16:14:00'),
(20, 'marie_wana', '$2y$10$o0ULGe.Lpp9HDJHkK0xeQ.q6SM.nj.1QbFKOpT/8oIopAp4WntYJm', 'student', 'Margie marie', 'Clarionn', '2024-11-29 23:00:42', '2024-11-29 23:00:42'),
(21, 'admin1', '$2y$10$VEw1QxokK3DYD/cyFNwOmeARyMfS2DmUb1JfKa/zO8Z3hr5RBgkqa', 'admin', 'temsmsmsm', 'sjsmsms', '2024-11-30 07:43:33', '2024-11-30 07:43:33');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `registrations`
--
ALTER TABLE `registrations`
  ADD PRIMARY KEY (`registration_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `sport_id` (`sport_id`),
  ADD KEY `idx_student_id` (`student_id`),
  ADD KEY `idx_sport_id` (`sport_id`);

--
-- Indexes for table `sports`
--
ALTER TABLE `sports`
  ADD PRIMARY KEY (`sport_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `registrations`
--
ALTER TABLE `registrations`
  MODIFY `registration_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=133;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `registrations`
--
ALTER TABLE `registrations`
  ADD CONSTRAINT `registrations_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `registrations_ibfk_2` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`sport_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
