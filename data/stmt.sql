-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 27, 2026 at 05:48 AM
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
-- Database: `stmt`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `lecture_id` int(11) NOT NULL,
  `status` enum('Present','Absent') NOT NULL DEFAULT 'Present'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `student_id`, `lecture_id`, `status`) VALUES
(1, 1, 2, 'Present'),
(2, 1, 3, 'Present'),
(3, 1, 4, 'Present');

-- --------------------------------------------------------

--
-- Table structure for table `faculty`
--

CREATE TABLE `faculty` (
  `id` int(11) NOT NULL,
  `firstname` varchar(100) DEFAULT NULL,
  `lastname` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `pos` varchar(100) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faculty`
--

INSERT INTO `faculty` (`id`, `firstname`, `lastname`, `email`, `pos`, `password_hash`, `created_at`) VALUES
(1, 'Test', 'Faculty', 'test@college.com', 'AP', '$2y$10$IDx510yFl4DV.dUZD2W6luLdOuH0gZ73w8lPkO6adj3MhOf7fPjCi', '2026-02-13 17:36:39'),
(2, 'pavan', 'malani', 'psmalani@gmail.com', 'tpo', NULL, '2026-03-27 04:33:49'),
(3, 'shardul', 'kulkarni', 'shardulk699@gmail.com', 'HOD', NULL, '2026-03-27 04:44:44');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `lecture_id` int(11) NOT NULL,
  `faculty_id` int(11) NOT NULL,
  `teaching_rating` int(11) DEFAULT NULL,
  `explanation_rating` int(11) DEFAULT NULL,
  `interaction_rating` int(11) DEFAULT NULL,
  `query` text DEFAULT NULL,
  `feedback_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `student_id`, `lecture_id`, `faculty_id`, `teaching_rating`, `explanation_rating`, `interaction_rating`, `query`, `feedback_date`) VALUES
(1, 1, 3, 1, 2, 2, 2, 'doubt', '2026-02-13');

-- --------------------------------------------------------

--
-- Table structure for table `lectures`
--

CREATE TABLE `lectures` (
  `id` int(11) NOT NULL,
  `faculty_id` int(11) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `lecture_no` int(11) NOT NULL,
  `lecture_date` date NOT NULL,
  `code` varchar(6) NOT NULL,
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lectures`
--

INSERT INTO `lectures` (`id`, `faculty_id`, `subject`, `lecture_no`, `lecture_date`, `code`, `expires_at`) VALUES
(1, 1, 'Operating Systems', 1, '2026-02-13', '668171', '2026-02-13 23:21:57'),
(2, 1, 'Operating Systems', 2, '2026-02-13', '482558', '2026-02-13 23:26:05'),
(3, 1, 'Operating Systems', 3, '2026-02-13', '517968', '2026-02-13 23:34:47'),
(4, 1, 'Operating Systems', 4, '2026-03-27', '972000', '2026-03-27 10:14:18');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `department` varchar(100) NOT NULL,
  `semester` varchar(20) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `first_name`, `last_name`, `email`, `department`, `semester`, `password_hash`, `created_at`) VALUES
(1, 'shardul', 'kulkarni', 'shardulk699@gmail.com', 'cs', '5', '$2y$10$QsaggPXdZhAuhR6calMj3OSNZOJGXAVBEUOIMog.1w6w0gjq8CwI.', '2026-02-06 12:20:18'),
(2, 'Rahul', 'Sharma', 'rahul@gmail.com', 'CS', NULL, '$2y$10$766FmWEZ0j4Dse9PQXpfb.NGBmoWwfipofoeRyuNxg99qEnNnkV2a', '2026-02-06 13:12:05'),
(3, 'Anita', 'Patil', 'anita@gmail.com', 'CS', NULL, '$2y$10$8WQWWUdO1iS5mMr26zYJe.YStAC0bH68uRkyUfDEV/MucvBYzASXO', '2026-02-06 13:12:05'),
(4, 'Suresh', 'Deshmukh', 'suresh@gmail.com', 'IT', NULL, '$2y$10$xOos7P5HnEjyNSjIGjeR0ug2D2zOQv56dtLQrfuA6tzVIpI4WeXnS', '2026-02-06 13:12:05'),
(5, 'Pooja', 'Kulkarni', 'pooja@gmail.com', 'CS', NULL, '$2y$10$1CQh.5diFRBN6ZrrqL8Nl.TofpP7FYT0.eyX0fokcctCW5teigpAS', '2026-02-06 13:12:05'),
(6, 'Amit', 'Joshi', 'amit@gmail.com', 'IT', NULL, '$2y$10$eyyPSivVpe2Dhi1NsVRcwuaakjObaEhqjoF6L4lBKl6Gw/xI1x2Sa', '2026-02-06 13:12:05'),
(7, 'Kunal', 'More', 'kunal@gmail.com', 'CS', NULL, '$2y$10$s8L.voxsIZuRvY4rBKmLcO6K.kZJKfVRgZ0NwYLeE8qjRQreqcFyW', '2026-02-06 13:12:05'),
(8, 'Neha', 'Patil', 'neha@gmail.com', 'CS', NULL, '$2y$10$eEzDLRUS.C8C4rGBH.3WAO7pPbi/L7vc2WnckiTLS.XSxrWhgJdOi', '2026-02-06 13:12:05'),
(12, 'kunal', 'sh', 'ks@gmail.com', 'cs', '2', '123456', '0000-00-00 00:00:00'),
(13, 'abh', 'p', 'ksa@gmail.com', 'cs', 'fifth', '$2y$10$br/ozXBkW./TJJxkT2Gdw.8HG4lAzBIu7Kme5J6AgaUA.fiNoSSn.', '2026-02-12 03:39:24'),
(14, 'abhi', 'pal', 'apal@gmail.com', 'cs', 'fifth', '$2y$10$ofRhDok9YSOcotQQejmwqe05tAMpnrkZZgIHYS9dEGF6fJK.8yoGW', '2026-02-12 15:37:53'),
(15, 'soham', 'jadhav', 'soham@gmail.com', 'CS', '6', '$2y$10$Ptmz8ENv6XQeOdiUQ5d7KulAswAYvCgSbFlyhueLS8jhlwOXkNWS.', '2026-02-13 18:21:25'),
(17, 'ojas', 'p', 'ojas@gamil.com', 'cs', '5', '$2y$10$LXi974cSRRyYstFDwVTBg.cz7jXxgTgb.r2SU0iuGPiB1fBO48QIe', '2026-03-27 04:44:04');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_student_lecture` (`student_id`,`lecture_id`),
  ADD KEY `lecture_id` (`lecture_id`);

--
-- Indexes for table `faculty`
--
ALTER TABLE `faculty`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `lecture_id` (`lecture_id`),
  ADD KEY `faculty_id` (`faculty_id`);

--
-- Indexes for table `lectures`
--
ALTER TABLE `lectures`
  ADD PRIMARY KEY (`id`),
  ADD KEY `faculty_id` (`faculty_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `faculty`
--
ALTER TABLE `faculty`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lectures`
--
ALTER TABLE `lectures`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`lecture_id`) REFERENCES `lectures` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `feedback_ibfk_2` FOREIGN KEY (`lecture_id`) REFERENCES `lectures` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `feedback_ibfk_3` FOREIGN KEY (`faculty_id`) REFERENCES `faculty` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `lectures`
--
ALTER TABLE `lectures`
  ADD CONSTRAINT `lectures_ibfk_1` FOREIGN KEY (`faculty_id`) REFERENCES `faculty` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
