-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 06, 2025 at 08:24 AM
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
(3, 'Finska - Exam', '2025-04-24 09:40:00', 2),
(4, 'Matte 123', '2025-04-30 09:35:42', 2),
(5, 'awfdv', '2025-04-30 10:51:25', 3),
(6, 'qefwvs', '2025-04-30 10:51:48', 3),
(7, 'qefcd', '2025-04-30 11:21:34', 3),
(8, 'efdcs', '2025-04-30 11:21:37', 3),
(9, 'Generated Exam - 2025-04-30 12:10:37', '2025-04-30 13:10:37', 1),
(10, 'Generated Exam - 2025-04-30 12:11:42', '2025-04-30 13:11:42', 1),
(11, 'Generated Exam - 2025-04-30 12:36:09', '2025-04-30 13:36:09', 1),
(12, 'Generated Exam - 2025-04-30 12:38:49', '2025-04-30 13:38:49', 1),
(13, 'Generated Exam - 2025-04-30 12:46:44', '2025-04-30 13:46:44', 1),
(14, 'Generated Exam - 2025-04-30 12:48:09', '2025-04-30 13:48:09', 1),
(15, 'Generated Exam - 2025-04-30 12:48:24', '2025-04-30 13:48:24', 1),
(16, 'Generated Exam - 2025-04-30 12:49:46', '2025-04-30 13:49:46', 1),
(17, 'Generated Exam - 2025-04-30 12:49:55', '2025-04-30 13:49:55', 1),
(18, 'Generated Exam - 2025-04-30 12:50:12', '2025-04-30 13:50:12', 1),
(19, 'Generated Exam - 2025-04-30 12:56:18', '2025-04-30 13:56:18', 1),
(20, 'Generated Exam - 2025-04-30 12:58:37', '2025-04-30 13:58:37', 1),
(21, 'Generated Exam - 2025-04-30 12:58:39', '2025-04-30 13:58:39', 1),
(22, 'Generated Exam - 2025-04-30 12:58:48', '2025-04-30 13:58:48', 1),
(23, 'Generated Exam - 2025-04-30 12:59:55', '2025-04-30 13:59:55', 1),
(24, 'Generated Exam - 2025-04-30 13:02:37', '2025-04-30 14:02:37', 1),
(25, 'Generated Exam - 2025-04-30 13:02:50', '2025-04-30 14:02:50', 1),
(26, 'Generated Exam - 2025-04-30 13:10:30', '2025-04-30 14:10:30', 1),
(27, 'Matteeeee', '2025-04-30 14:10:48', 1);

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
(4, 3, 4, 1),
(5, 4, 8, 1),
(6, 5, 70, 1),
(7, 5, 69, 2),
(8, 6, 70, 1),
(9, 6, 69, 2),
(10, 7, 69, 1),
(11, 7, 70, 2),
(12, 8, 69, 1),
(13, 8, 70, 2),
(14, 8, 91, 3);

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
(4, 10, 3, '<p>A very smart question</p>', '<p>With a very smart answer.</p>', NULL, 3, '2025-04-24 09:28:07', 1, 3, 1, 0),
(8, 11, 3, 'What is the limit of \\frac{1}{x} as x approaches 0?', '', NULL, 2, '2025-04-24 09:28:07', 1, 2, 2, 0),
(69, 10, 1, '<p>Hur mycket är 1 euro i cent?</p>', '<p>100 cent</p>', NULL, 1, '2025-04-30 10:45:49', 1, 3, 1, 0),
(70, 11, 6, '<p>Om du ska välja mellan att köpa en dator och telefon.. vilken ska du köpa?</p>', '<p>Telefon, eftersom det behövs mycket mera all dags runt!</p>', NULL, 2, '2025-04-30 10:46:47', 1, 3, 2, 0),
(71, 10, 6, '<p>What is the tax rate for an income above 50000 in the progressive tax system?</p>', '<p>The tax rate is 30% for income above 50000.</p>', NULL, 3, '2025-04-30 10:48:52', 1, 3, 1, 0),
(72, 10, 6, '<p>What is the main purpose of progressive tax?</p>', '<p>To ensure that people with higher incomes pay a larger percentage in taxes.</p>', NULL, 2, '2025-04-30 10:48:52', 1, 3, 1, 0),
(73, 19, 3, '<p>What is the value of x in the equation: 2x + 5 = 15?</p>', 'x = 5', NULL, 2, '2025-04-30 10:48:52', 1, 1, 1, 0),
(74, 19, 7, '<p>Solve for x: 4x - 7 = 21</p>', 'x = 7', NULL, 3, '2025-04-30 10:48:52', 1, 1, 1, 0),
(75, 14, 12, 'What is the sine of 30 degrees?', '0.5', NULL, 1, '2025-04-30 10:48:52', 1, 1, 1, 0),
(76, 14, 10, 'Is tan(90 degrees) undefined?', 'True', NULL, 2, '2025-04-30 10:48:52', 1, 1, 1, 0),
(77, 23, 13, 'What is the derivative of f(x) = x^2?', '2x', NULL, 5, '2025-04-30 10:48:52', 1, 1, 3, 0),
(78, 23, 8, 'Find the limit: lim(x->0) (sin(x)/x)', '1', NULL, 3, '2025-04-30 10:48:52', 1, 1, 3, 0),
(79, 25, 3, 'What is the determinant of the matrix [[1,2], [3,4]]?', '-2', NULL, 4, '2025-04-30 10:48:52', 1, 1, 4, 0),
(80, 25, 7, 'What is the rank of a matrix with no rows?', '0', NULL, 2, '2025-04-30 10:48:52', 1, 1, 4, 0),
(81, 26, 6, 'If you roll a fair six-sided die, what is the probability of rolling a number greater than 4?', '1/3', NULL, 2, '2025-04-30 10:48:52', 1, 3, 4, 0),
(82, 26, 15, 'True or False: The probability of getting heads on a coin flip is 0.5.', 'True', NULL, 1, '2025-04-30 10:48:52', 1, 3, 4, 0),
(83, 13, 12, 'Which of the following is the largest number: 1, 2, 3, or 5?', '5', NULL, 2, '2025-04-30 10:48:52', 1, 3, 1, 0),
(84, 14, 7, 'Which is the correct formula for the area of a triangle?', 'Area = 0.5 * base * height', NULL, 3, '2025-04-30 10:48:52', 1, 3, 1, 0),
(85, 21, 13, 'What is the capital of Sweden?', 'Stockholm', NULL, 1, '2025-04-30 10:48:52', 1, 3, 2, 0),
(86, 22, 13, 'Who invented the telephone?', 'Alexander Graham Bell', NULL, 2, '2025-04-30 10:48:52', 1, 3, 2, 0),
(87, 23, 14, 'Explain the concept of integration in calculus.', 'Integration is the reverse process of differentiation. It is used to find the area under a curve and has wide applications in physics and engineering.', NULL, 10, '2025-04-30 10:48:52', 1, 3, 3, 0),
(88, 14, 14, 'Describe the Pythagorean theorem and provide an example.', 'The Pythagorean theorem states that in a right triangle, the square of the length of the hypotenuse is equal to the sum of the squares of the other two sides. For example, in a triangle with sides 3 and 4, the hypotenuse is 5.', NULL, 8, '2025-04-30 10:48:52', 1, 1, 1, 0),
(89, 21, 15, 'True or False: The earth is flat.', 'False', NULL, 1, '2025-04-30 10:48:52', 1, 3, 2, 0),
(90, 18, 15, 'True or False: 1+1 equals 2.', 'True', NULL, 1, '2025-04-30 10:48:52', 1, 1, 3, 0),
(91, 12, 5, '<p>rdscx</p>', '<p>bvfdscxz</p>', NULL, 2, '2025-04-30 11:04:39', 1, 3, 2, 0);

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
  MODIFY `ex_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `exam_questions`
--
ALTER TABLE `exam_questions`
  MODIFY `eq_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `qu_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
