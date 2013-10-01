-- phpMyAdmin SQL Dump
-- version 4.0.6
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 01, 2013 at 04:45 PM
-- Server version: 10.0.4-MariaDB-1~wheezy-log
-- PHP Version: 5.5.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `movlib`
--

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `access`, `created`, `login`, `private`, `deactivated`, `time_zone_id`, `edits`, `dyn_profile`, `sex`, `system_language_code`, `avatar_name`, `avatar_extension`, `avatar_changed`, `birthday`, `country_id`, `real_name`, `website`, `facebook`, `google_plus`, `twitter`) VALUES
(1, 'Fleshgrinder', 'richard@fussenegger.info', '$2y$10$N/kvo2/A9vAv.8Mkgb4ky.llucBnDaPi5pdW7HPP2OCHV9yDQyjbG', '2013-10-01 16:44:29', '2013-10-01 16:44:29', '2013-10-01 16:44:29', 0, 0, 'Europe/Vienna', 0, '', 0, 'de', 'fleshgrinder', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
