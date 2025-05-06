-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 06, 2025 at 11:11 AM
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
(1, 'Skatt', 1),
(2, 'Procent', 1),
(3, 'Differential Equations', 5),
(4, 'Discrete Math', 5),
(39, 'Bråk', 1);

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
(5, 'Finska 2', 'Advanced Finnish language course focusing on grammar and literature.');

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
(1, 'Procenttest 1', '2025-05-06 12:08:02', 3),
(2, 'Algebra & Geometri', '2025-05-06 12:08:02', 3),
(3, 'Mixad utmaning', '2025-05-06 12:08:02', 3),
(4, 'Ekvationstest - nivå med qu_id 42', '2025-05-06 12:10:55', 3);

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
(7, 1, 1, 1),
(8, 1, 4, 2),
(9, 1, 7, 3),
(10, 1, 10, 4),
(11, 1, 13, 5),
(12, 1, 16, 6),
(13, 1, 1, 1),
(14, 1, 2, 2),
(15, 1, 3, 3),
(16, 1, 4, 4),
(17, 1, 5, 5),
(18, 1, 6, 6),
(19, 2, 7, 1),
(20, 2, 8, 2),
(21, 2, 9, 3),
(22, 2, 10, 4),
(23, 2, 11, 5),
(24, 2, 12, 6),
(25, 3, 13, 1),
(26, 3, 14, 2),
(27, 3, 15, 3),
(28, 3, 16, 4),
(29, 3, 17, 5),
(30, 3, 18, 6),
(31, 4, 43, 1),
(32, 4, 44, 2),
(33, 4, 45, 3),
(34, 4, 46, 4),
(35, 4, 47, 5),
(36, 4, 48, 6);

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `qu_id` int(11) NOT NULL,
  `ca_id` int(11) NOT NULL,
  `qt_id` int(11) NOT NULL,
  `text` text NOT NULL,
  `answer` varchar(255) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `total_points` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  `teacher_fk` int(11) DEFAULT NULL,
  `co_id` int(11) DEFAULT NULL,
  `difficulty` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`qu_id`, `ca_id`, `qt_id`, `text`, `answer`, `image_url`, `total_points`, `created_at`, `is_active`, `teacher_fk`, `co_id`, `difficulty`) VALUES
(1, 1, 1, 'Beräkna: (3 + 5 	imes 2)', '13', NULL, 6, '2025-05-06 12:05:49', 1, 3, 1, 2),
(2, 1, 1, 'Vad är resultatet av ( (4 + 2) 	imes 3 )?', '18', NULL, 4, '2025-05-06 12:05:49', 1, 3, 1, 1),
(3, 1, 1, 'Förenkla uttrycket: ( (3 + 5) 	imes (2 + 1) )', '24', NULL, 8, '2025-05-06 12:05:49', 1, 3, 1, 3),
(4, 2, 2, 'Omvandla 5 meter till centimeter.', '500', NULL, 4, '2025-05-06 12:05:49', 1, 3, 1, 1),
(5, 2, 2, 'Hur många liter är 3,5 dl?', '0.35', NULL, 6, '2025-05-06 12:05:49', 1, 3, 1, 2),
(6, 2, 2, 'Konvertera 0,75 timmar till minuter.', '45', NULL, 8, '2025-05-06 12:05:49', 1, 3, 1, 3),
(7, 3, 3, 'Avrunda 12,678 till en decimal.', '12,7', NULL, 4, '2025-05-06 12:05:49', 1, 3, 1, 1),
(8, 3, 3, 'Avrunda 349 till närmaste hundratal.', '300', NULL, 6, '2025-05-06 12:05:49', 1, 3, 1, 2),
(9, 3, 3, 'Vad är 1/3 avrundat till två decimaler?', '0.33', NULL, 8, '2025-05-06 12:05:49', 1, 3, 1, 3),
(10, 1, 4, 'Beräkna 25% av 80.', '20', NULL, 4, '2025-05-06 12:05:49', 1, 3, 1, 1),
(11, 1, 4, 'Vad är 60% av 150?', '90', NULL, 6, '2025-05-06 12:05:49', 1, 3, 1, 2),
(12, 1, 4, 'Om priset är 800 kr och du får 15% rabatt, vad är det nya priset?', '680', NULL, 8, '2025-05-06 12:05:49', 1, 3, 1, 3),
(13, 1, 5, 'Beräkna hur många procent 30 är av 200.', '15%', NULL, 4, '2025-05-06 12:05:49', 1, 3, 1, 1),
(14, 1, 5, 'Om något ökar från 50 till 65, hur många procent har det ökat?', '30%', NULL, 6, '2025-05-06 12:05:49', 1, 3, 1, 2),
(15, 1, 5, 'En vara sänks från 1200 kr till 900 kr. Hur många procent är nedsättningen?', '25%', NULL, 8, '2025-05-06 12:05:49', 1, 3, 1, 3),
(16, 3, 6, 'Vilket tal saknas: ( x + 3 = 10 )', '7', NULL, 4, '2025-05-06 12:05:49', 1, 3, 1, 1),
(17, 3, 6, 'Lös ekvationen: ( 2x - 4 = 10 )', '7', NULL, 6, '2025-05-06 12:05:49', 1, 3, 1, 2),
(18, 3, 6, 'Förenkla och lös: ( 3(x - 2) + 2x = 20 )', '4', NULL, 8, '2025-05-06 12:05:49', 1, 3, 1, 3),
(19, 2, 2, 'Omvandla 1.2 kg till gram.', '1200', NULL, 6, '2025-05-06 12:05:49', 1, 3, 1, 2),
(20, 1, 1, 'Beräkna: ( 7 + (6 	imes 5^2 + 3) )', '160', NULL, 8, '2025-05-06 12:05:49', 1, 3, 1, 3),
(21, 3, 6, 'Lös: ( frac{2x + 1}{3} = 5 )', '7', NULL, 8, '2025-05-06 12:05:49', 1, 3, 1, 3),
(22, 1, 1, 'Beräkna: (3 + 5 	imes 2)', '13', NULL, 6, '2025-05-06 12:05:51', 1, 3, 1, 2),
(23, 1, 1, 'Vad är resultatet av ( (4 + 2) 	imes 3 )?', '18', NULL, 4, '2025-05-06 12:05:51', 1, 3, 1, 1),
(24, 1, 1, 'Förenkla uttrycket: ( (3 + 5) 	imes (2 + 1) )', '24', NULL, 8, '2025-05-06 12:05:51', 1, 3, 1, 3),
(25, 2, 2, 'Omvandla 5 meter till centimeter.', '500', NULL, 4, '2025-05-06 12:05:51', 1, 3, 1, 1),
(26, 2, 2, 'Hur många liter är 3,5 dl?', '0.35', NULL, 6, '2025-05-06 12:05:51', 1, 3, 1, 2),
(27, 2, 2, 'Konvertera 0,75 timmar till minuter.', '45', NULL, 8, '2025-05-06 12:05:51', 1, 3, 1, 3),
(28, 3, 3, 'Avrunda 12,678 till en decimal.', '12,7', NULL, 4, '2025-05-06 12:05:51', 1, 3, 1, 1),
(29, 3, 3, 'Avrunda 349 till närmaste hundratal.', '300', NULL, 6, '2025-05-06 12:05:51', 1, 3, 1, 2),
(30, 3, 3, 'Vad är 1/3 avrundat till två decimaler?', '0.33', NULL, 8, '2025-05-06 12:05:51', 1, 3, 1, 3),
(31, 1, 4, 'Beräkna 25% av 80.', '20', NULL, 4, '2025-05-06 12:05:51', 1, 3, 1, 1),
(32, 1, 4, 'Vad är 60% av 150?', '90', NULL, 6, '2025-05-06 12:05:51', 1, 3, 1, 2),
(33, 1, 4, 'Om priset är 800 kr och du får 15% rabatt, vad är det nya priset?', '680', NULL, 8, '2025-05-06 12:05:51', 1, 3, 1, 3),
(34, 1, 5, 'Beräkna hur många procent 30 är av 200.', '15%', NULL, 4, '2025-05-06 12:05:51', 1, 3, 1, 1),
(35, 1, 5, 'Om något ökar från 50 till 65, hur många procent har det ökat?', '30%', NULL, 6, '2025-05-06 12:05:51', 1, 3, 1, 2),
(36, 1, 5, 'En vara sänks från 1200 kr till 900 kr. Hur många procent är nedsättningen?', '25%', NULL, 8, '2025-05-06 12:05:51', 1, 3, 1, 3),
(37, 3, 6, 'Vilket tal saknas: ( x + 3 = 10 )', '7', NULL, 4, '2025-05-06 12:05:51', 1, 3, 1, 1),
(38, 3, 6, 'Lös ekvationen: ( 2x - 4 = 10 )', '7', NULL, 6, '2025-05-06 12:05:51', 1, 3, 1, 2),
(39, 3, 6, 'Förenkla och lös: ( 3(x - 2) + 2x = 20 )', '4', NULL, 8, '2025-05-06 12:05:51', 1, 3, 1, 3),
(40, 2, 2, 'Omvandla 1.2 kg till gram.', '1200', NULL, 6, '2025-05-06 12:05:51', 1, 3, 1, 2),
(41, 1, 1, 'Beräkna: ( 7 + (6 	imes 5^2 + 3) )', '160', NULL, 8, '2025-05-06 12:05:51', 1, 3, 1, 3),
(42, 3, 6, 'Lös: ( frac{2x + 1}{3} = 5 )', '7', NULL, 8, '2025-05-06 12:05:51', 1, 3, 1, 3),
(43, 1, 1, 'Lös ekvationen: $$2x + 5 = 11$$', 'x = 3', NULL, 6, '2025-05-06 12:10:55', 1, 3, 1, 3),
(44, 1, 1, 'Lös ekvationen: $$\\frac{3x}{4} = 6$$', 'x = 8', NULL, 6, '2025-05-06 12:10:55', 1, 3, 1, 3),
(45, 1, 1, 'Lös ut $$x$$: $$5x - 7 = 3x + 9$$', 'x = 8', NULL, 7, '2025-05-06 12:10:55', 1, 3, 1, 4),
(46, 1, 1, 'Vilket värde har $$x$$ om $$4(x - 2) = 8$$?', 'x = 4', NULL, 5, '2025-05-06 12:10:55', 1, 3, 1, 2),
(47, 1, 1, 'Lös ut $$x$$: $$\\frac{x + 3}{2} = 5$$', 'x = 7', NULL, 6, '2025-05-06 12:10:55', 1, 3, 1, 3),
(48, 1, 1, 'Lös: $$2(x + 3) = x + 10$$', 'x = 4', NULL, 6, '2025-05-06 12:10:55', 1, 3, 1, 3);

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
(6, 'Utmaning', 3);

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
(2, 'test1', 'test1@test.com', '$2y$10$xkSeTLeSohvmbJyOuBri.uY87wsbE7yEsr2bZLAylvC6.B0xWv3o6', 1),
(3, 'KingJingaling', 'JinjoTown@gruntilda.com', '$2a$12$22Z6HD.WEmBi7bYPXNdZXeBm2UzKKvd3I5BJdohy1I3durgcsCCBi', 3);

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
  ADD KEY `teacher_fk` (`teacher_fk`),
  ADD KEY `fk_questions_courses` (`co_id`);

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
  MODIFY `ca_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `co_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `exams`
--
ALTER TABLE `exams`
  MODIFY `ex_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `exam_questions`
--
ALTER TABLE `exam_questions`
  MODIFY `eq_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `qu_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `questiontypes`
--
ALTER TABLE `questiontypes`
  MODIFY `qt_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `r_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `u_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
  ADD CONSTRAINT `fk_questions_courses` FOREIGN KEY (`co_id`) REFERENCES `courses` (`co_id`) ON DELETE SET NULL,
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
