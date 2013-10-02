-- phpMyAdmin SQL Dump
-- version 4.0.7
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 02, 2013 at 08:06 AM
-- Server version: 10.0.4-MariaDB-1~wheezy-log
-- PHP Version: 5.5.4

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
(1, 'Fleshgrinder', 'richard@fussenegger.info', '$2y$13$iK8nY/tafZM9AXcV1bRZvOj3Vf4Sq0L24HKJX/iQa4ii8XcbO0efy', '2013-10-02 08:03:27', '2013-10-01 16:44:29', '2013-10-01 16:44:29', 0, 0, 'Europe/Vienna', 0, '\0\0\0\0\0\0#deen!<p>Mein deutscher Profiltext.</p>!<p>My English profile text.</p>', 1, 'de', 'fleshgrinder', 'jpg', '2013-10-02 07:35:45', '1985-06-07', NULL, 'Richard Fussenegger', 'http://richard.fussenegger.info/', NULL, NULL, NULL);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
