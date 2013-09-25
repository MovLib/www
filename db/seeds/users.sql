-- phpMyAdmin SQL Dump
-- version 4.0.6
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 25, 2013 at 01:36 PM
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

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `created`, `access`, `login`, `private`, `deactivated`, `time_zone_id`, `edits`, `dyn_profile`, `sex`, `system_language_code`, `country_id`, `real_name`, `birthday`, `website`, `facebook`, `google_plus`, `twitter`, `avatar_extension`, `avatar_name`, `avatar_changed`) VALUES
(1, 'Fleshgrinder', 'richard@fussenegger.info', '$2y$10$N/kvo2/A9vAv.8Mkgb4ky.llucBnDaPi5pdW7HPP2OCHV9yDQyjbG', '2013-09-16 11:37:18', '2013-09-16 11:37:18', '2013-09-16 11:37:18', 0, 0, 'Europe/Vienna', 0, '\0\0\0\0\0\0ódeen!Richards deutscher Profiltext.!Richardâ€™s English profile text.', 1, 'en', 12, 'Richard Fussenegger', '1985-06-27', 'http://richard.fussenegger.info/', NULL, NULL, NULL, 'jpg', '3696208974', NULL),
(2, 'Ravenlord', 'mdeutschl.mmt-m2012@fh-salzburg.ac.at', '$2y$13$5HZMIfCO3V7m8raXH/oNiOkYQeRcvQHBjxMYM.FeoD.atUjZLg3Om', '2013-09-25 13:36:32', '2013-09-25 13:36:32', '2013-09-25 13:31:35', 0, 0, 'UTC', 0, '', 0, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
