-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 25, 2025 at 08:06 AM
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
-- Database: `matteprovgenerator`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `ca_id` int(11) NOT NULL,
  `ca_name` varchar(255) NOT NULL,
  `ca_co_fk` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`ca_id`, `ca_name`, `ca_co_fk`) VALUES
(10, 'Skatt', 1),
(11, 'Budgetering', 2),
(12, 'Räntor', 2),
(13, 'Geometriska figurer', 1),
(14, 'Trigonometri', 1),
(15, 'Plan geometri', 1),
(16, 'Differentialekvationer', 3),
(17, 'Integraler', 3),
(18, 'Funktioner och gränsvärden', 3),
(19, 'Algebra', 1),
(20, 'Arithmetic', 1),
(21, 'Geometry', 2),
(22, 'Trigonometry', 2),
(23, 'Calculus', 3),
(24, 'Statistics', 3),
(25, 'Linear Algebra', 4),
(26, 'Probability', 4),
(27, 'Differential Equations', 5),
(28, 'Discrete Math', 5),
(29, 'Algebra', 1),
(30, 'Arithmetic', 1),
(31, 'Geometry', 2),
(32, 'Trigonometry', 2),
(33, 'Calculus', 3),
(34, 'Statistics', 3),
(35, 'Linear Algebra', 4),
(36, 'Probability', 4),
(37, 'Differential Equations', 5),
(38, 'Discrete Math', 5);

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `co_id` int(11) NOT NULL,
  `co_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`co_id`, `co_name`, `description`) VALUES
(1, 'Matematik 1', 'Basic mathematics course covering algebra, geometry, and trigonometry.'),
(2, 'Matematik 2', 'Intermediate mathematics course focusing on calculus, integrals, and differential equations.'),
(3, 'Ekonomi', 'A comprehensive course on economics, covering budgeting, taxation, and financial systems.'),
(4, 'Finska', 'Introductory course to Finnish language and culture.'),
(5, 'Finska 2', 'Advanced Finnish language course focusing on grammar and literature.'),
(6, 'Math A', 'Intro to math'),
(7, 'Math B', 'Intermediate math'),
(8, 'Math C', 'Advanced math'),
(9, 'Math D', 'Applied mathematics'),
(10, 'Math E', 'Math for engineers'),
(11, 'Math A', 'Intro to math'),
(12, 'Math B', 'Intermediate math'),
(13, 'Math C', 'Advanced math'),
(14, 'Math D', 'Applied mathematics'),
(15, 'Math E', 'Math for engineers');

-- --------------------------------------------------------

--
-- Table structure for table `exams`
--

CREATE TABLE `exams` (
  `ex_id` int(11) NOT NULL,
  `ex_name` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `ex_createdby_fk` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exams`
--

INSERT INTO `exams` (`ex_id`, `ex_name`, `created_at`, `ex_createdby_fk`) VALUES
(1, 'Matematik 1 - Exam', '2025-04-24 09:30:00', 1),
(2, 'Ekonomi - Exam', '2025-04-24 09:35:00', 1),
(3, 'Finska - Exam', '2025-04-24 09:40:00', 2);

-- --------------------------------------------------------

--
-- Table structure for table `exam_questions`
--

CREATE TABLE `exam_questions` (
  `eq_id` int(11) NOT NULL,
  `ex_id` int(11) NOT NULL,
  `qu_id` int(11) NOT NULL,
  `question_order` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exam_questions`
--

INSERT INTO `exam_questions` (`eq_id`, `ex_id`, `qu_id`, `question_order`) VALUES
(4, 3, 4, 1);

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `qu_id` int(11) NOT NULL,
  `ca_id` int(11) NOT NULL,
  `qt_id` int(11) NOT NULL,
  `text` text NOT NULL,
  `ansver` varchar(255) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `total_points` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  `teacher_fk` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`qu_id`, `ca_id`, `qt_id`, `text`, `ansver`, `image_url`, `total_points`, `created_at`, `is_active`, `teacher_fk`) VALUES
(4, 10, 3, '\\int_0^1 x^2 dx = \\frac{1}{3}', '', NULL, 3, '2025-04-24 09:28:07', 1, 1),
(8, 11, 3, 'What is the limit of \\frac{1}{x} as x approaches 0?', '', NULL, 2, '2025-04-24 09:28:07', 1, 2);

-- --------------------------------------------------------

--
-- Table structure for table `questiontypes`
--

CREATE TABLE `questiontypes` (
  `qt_id` int(11) NOT NULL,
  `qt_name` varchar(255) NOT NULL,
  `qt_ca_fk` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `questiontypes`
--

INSERT INTO `questiontypes` (`qt_id`, `qt_name`, `qt_ca_fk`) VALUES
(1, 'Räknesättens ordningsföljd', 1),
(2, 'Enhetsomvandling', 2),
(3, 'Avrundning', 3),
(4, 'Gr. Proc.', 1),
(5, 'Proc 2', 1),
(6, 'Utmaning', 3),
(7, 'Multiple Choice', 1),
(8, 'Short Answer', 3),
(9, 'Long Answer', 5),
(10, 'True/False', 7),
(11, 'Calculation', 9),
(12, 'Multiple Choice', 1),
(13, 'Short Answer', 3),
(14, 'Long Answer', 5),
(15, 'True/False', 7),
(16, 'Calculation', 9);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `r_id` int(11) NOT NULL,
  `r_name` varchar(255) NOT NULL,
  `r_level` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`r_id`, `r_name`, `r_level`) VALUES
(1, 'teacher', 100),
(2, 'admin', 300),
(3, 'supergigaadmin', 900);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `u_id` int(11) NOT NULL,
  `u_uname` varchar(255) NOT NULL,
  `u_mail` varchar(255) NOT NULL,
  `u_password` varchar(300) NOT NULL,
  `u_role_fk` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`u_id`, `u_uname`, `u_mail`, `u_password`, `u_role_fk`) VALUES
(1, 'Robin55', 'dahlgrenrobin1@gmail.com', '$2y$10$bwiXTNa/7BgqiXXG5J3GS.h8Zu9JnWLJfgbT7ibZ02lMHBISn8MTO', 3),
(2, 'test1', 'test1@test.com', '$2y$10$xkSeTLeSohvmbJyOuBri.uY87wsbE7yEsr2bZLAylvC6.B0xWv3o6', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`ca_id`),
  ADD KEY `fk_categories_co_id` (`ca_co_fk`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`co_id`);

--
-- Indexes for table `exams`
--
ALTER TABLE `exams`
  ADD PRIMARY KEY (`ex_id`),
  ADD KEY `exams_ibfk_1` (`ex_createdby_fk`);

--
-- Indexes for table `exam_questions`
--
ALTER TABLE `exam_questions`
  ADD PRIMARY KEY (`eq_id`),
  ADD KEY `fk_exam_questions_ex_id` (`ex_id`),
  ADD KEY `fk_exam_questions_qu_id` (`qu_id`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`qu_id`),
  ADD KEY `fk_questions_ca_id` (`ca_id`),
  ADD KEY `fk_questions_qt_id` (`qt_id`),
  ADD KEY `teacher_fk` (`teacher_fk`);

--
-- Indexes for table `questiontypes`
--
ALTER TABLE `questiontypes`
  ADD PRIMARY KEY (`qt_id`),
  ADD KEY `fk_questiontypes_ca_id` (`qt_ca_fk`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`r_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`u_id`),
  ADD KEY `u_role_fk` (`u_role_fk`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `ca_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `co_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `exams`
--
ALTER TABLE `exams`
  MODIFY `ex_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `exam_questions`
--
ALTER TABLE `exam_questions`
  MODIFY `eq_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `qu_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `questiontypes`
--
ALTER TABLE `questiontypes`
  MODIFY `qt_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `r_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `u_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `fk_categories_co_id` FOREIGN KEY (`ca_co_fk`) REFERENCES `courses` (`co_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `exams`
--
ALTER TABLE `exams`
  ADD CONSTRAINT `exams_ibfk_1` FOREIGN KEY (`ex_createdby_fk`) REFERENCES `users` (`u_id`);

--
-- Constraints for table `exam_questions`
--
ALTER TABLE `exam_questions`
  ADD CONSTRAINT `fk_exam_questions_ex_id` FOREIGN KEY (`ex_id`) REFERENCES `exams` (`ex_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_exam_questions_exam` FOREIGN KEY (`ex_id`) REFERENCES `exams` (`ex_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_exam_questions_qu_id` FOREIGN KEY (`qu_id`) REFERENCES `questions` (`qu_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_exam_questions_question` FOREIGN KEY (`qu_id`) REFERENCES `questions` (`qu_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`qt_id`) REFERENCES `questiontypes` (`qt_id`),
  ADD CONSTRAINT `questions_ibfk_2` FOREIGN KEY (`ca_id`) REFERENCES `categories` (`ca_id`),
  ADD CONSTRAINT `questions_ibfk_3` FOREIGN KEY (`teacher_fk`) REFERENCES `users` (`u_id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`u_role_fk`) REFERENCES `roles` (`r_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
