-- phpMyAdmin SQL Dump
-- version 4.8.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: 01.04.2025 klo 07:39
-- Palvelimen versio: 10.1.31-MariaDB
-- PHP Version: 7.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `2025_login`
--

-- --------------------------------------------------------

--
-- Rakenne taululle `roles`
--

CREATE TABLE `roles` (
  `r_id` int(11) NOT NULL,
  `r_name` varchar(255) NOT NULL,
  `r_level` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Rakenne taululle `users`
--

CREATE TABLE `users` (
  `u_id` int(11) NOT NULL,
  `u_name` varchar(255) NOT NULL,
  `u_fname` varchar(255) NOT NULL,
  `u_lname` varchar(255) NOT NULL,
  `u_email` varchar(255) NOT NULL,
  `u_password` varchar(255) NOT NULL,
  `u_lastlogin` datetime NOT NULL,
  `u_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `u_isactive` tinyint(1) NOT NULL,
  `u_role_fk` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

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
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `r_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `u_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Rajoitteet vedostauluille
--

--
-- Rajoitteet taululle `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`u_role_fk`) REFERENCES `roles` (`r_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
