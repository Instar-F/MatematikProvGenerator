-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 24, 2025 at 08:28 AM
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
(18, 'Funktioner och gränsvärden', 3);

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
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exams`
--

INSERT INTO `exams` (`ex_id`, `ex_name`, `created_at`) VALUES
(1, 'Matematik 1 - Exam', '2025-04-24 09:30:00'),
(2, 'Ekonomi - Exam', '2025-04-24 09:35:00'),
(3, 'Finska - Exam', '2025-04-24 09:40:00');

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
(1, 1, 1, 1),
(2, 1, 5, 2),
(3, 2, 2, 1),
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
  `image_url` varchar(255) DEFAULT NULL,
  `total_points` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  `teacher` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`qu_id`, `ca_id`, `qt_id`, `text`, `image_url`, `total_points`, `created_at`, `is_active`, `teacher`) VALUES
(1, 6, 3, 'a^2 + b^2 = c^2', NULL, 2, '2025-04-24 09:28:07', 1, 'Mr. Lindgren'),
(2, 6, 3, '(a + b)^2 = a^2 + 2ab + b^2', NULL, 2, '2025-04-24 09:28:07', 1, 'Dr. Norberg'),
(3, 7, 2, '\\frac{1}{x} + \\frac{1}{y} = \\frac{x+y}{xy}', NULL, 3, '2025-04-24 09:28:07', 1, 'Frida Holm'),
(4, 10, 3, '\\int_0^1 x^2 dx = \\frac{1}{3}', NULL, 3, '2025-04-24 09:28:07', 1, 'Anders Wallin'),
(5, 4, 2, 'How do you calculate the monthly payment of a loan with an interest rate of 5% and a loan term of 30 years?', NULL, 3, '2025-04-24 09:28:07', 1, 'Ms. Ekström'),
(6, 5, 1, 'Calculate the compound interest on a deposit of 1000 SEK at an interest rate of 5% for 3 years.', NULL, 3, '2025-04-24 09:28:07', 1, 'Mr. Lindgren'),
(7, 9, 3, 'Solve the differential equation: \\frac{dy}{dx} = 2x', NULL, 4, '2025-04-24 09:28:07', 1, 'Anders Wallin'),
(8, 11, 3, 'What is the limit of \\frac{1}{x} as x approaches 0?', NULL, 2, '2025-04-24 09:28:07', 1, 'Frida Holm');

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
  ADD PRIMARY KEY (`ex_id`);

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
  ADD KEY `fk_questions_qt_id` (`qt_id`);

--
-- Indexes for table `questiontypes`
--
ALTER TABLE `questiontypes`
  ADD PRIMARY KEY (`qt_id`),
  ADD KEY `fk_questiontypes_ca_id` (`qt_ca_fk`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `ca_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `co_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
  MODIFY `qu_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `questiontypes`
--
ALTER TABLE `questiontypes`
  MODIFY `qt_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `fk_categories_co_id` FOREIGN KEY (`ca_co_fk`) REFERENCES `courses` (`co_id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
  ADD CONSTRAINT `fk_questions_ca_id` FOREIGN KEY (`ca_id`) REFERENCES `categories` (`ca_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_questions_qt_id` FOREIGN KEY (`qt_id`) REFERENCES `questiontypes` (`qt_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
