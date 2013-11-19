-- phpMyAdmin SQL Dump
-- version 4.0.6
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 16, 2013 at 12:18 PM
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
-- Dumping data for table `master_releases`
--

INSERT INTO `master_releases` (`master_release_id`, `title`, `country_code`, `dyn_notes`, `release_date`, `packaging_id`, `commit`, `created`) VALUES
(1, 'Die Verurteilten', 'DE', '', '2007-11-15', NULL, NULL, CURRENT_TIMESTAMP),
(2, 'Die Verurteilten', 'DE', '', '2003-01-16', NULL, NULL, CURRENT_TIMESTAMP),
(3, 'Die Verurteilten', 'DE', '', '2000-09-28', NULL, NULL, CURRENT_TIMESTAMP);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
