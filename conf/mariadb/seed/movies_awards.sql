-- phpMyAdmin SQL Dump
-- version 4.0.8
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 11, 2013 at 01:17 PM
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
-- Dumping data for table `movies_awards`
--

INSERT INTO `movies_awards` (`movie_id`, `award_count`, `award_id`, `award_category_id`, `person_id`, `company_id`, `year`, `won`) VALUES
(2, 1, 5, 1, 7, NULL, 1995, 0),
(2, 2, 5, 2, NULL, NULL, 1995, 0),
(2, 3, 5, 3, NULL, NULL, 1995, 0),
(2, 4, 5, 4, NULL, NULL, 1995, 0),
(2, 5, 5, 5, NULL, NULL, 1995, 0),
(2, 6, 5, 6, NULL, NULL, 1995, 0),
(2, 7, 5, 7, 5, NULL, 1995, 0),
(2, 8, 6, 1, NULL, NULL, 1995, 0),
(2, 9, 6, 2, 5, NULL, 1995, 0),
(2, 10, 7, 1, NULL, NULL, 1995, 0),
(2, 11, 8, 1, NULL, NULL, 1995, 1),
(2, 12, 1, 1, NULL, NULL, 1996, 1),
(2, 13, 9, 1, NULL, NULL, 1995, 1),
(2, 14, 9, 3, NULL, NULL, 1995, 0),
(2, 15, 10, 1, NULL, NULL, 1995, 0),
(2, 16, 11, 1, NULL, NULL, 1995, 0),
(2, 17, 11, 2, 7, NULL, 1995, 0),
(2, 18, 12, 1, 7, NULL, 1995, 1),
(2, 19, 12, 1, 6, NULL, 1995, 0),
(2, 20, 13, 1, 7, NULL, 1995, 0),
(2, 21, 13, 2, NULL, NULL, 1995, 0),
(2, 22, 14, 1, 5, NULL, 1995, 0),
(2, 23, 15, 1, 7, NULL, 1995, 0),
(2, 24, 15, 2, 5, NULL, 1995, 0),
(2, 25, 16, 1, NULL, NULL, 1995, 0),
(2, 26, 17, NULL, 5, NULL, 1995, 1),
(2, 27, 18, 1, 5, NULL, 1995, 1),
(2, 28, 19, 1, 5, NULL, 1995, 1),
(2, 29, 20, 1, 5, NULL, 1996, 1),
(2, 30, 20, 2, 5, NULL, 1996, 1),
(2, 31, 21, 1, 5, NULL, 1996, 1),
(2, 32, 22, 1, NULL, NULL, 1994, 1),
(2, 33, 23, 1, 5, NULL, 1995, 1),
(2, 34, 24, 1, 7, NULL, 1995, 0),
(2, 35, 24, 1, 6, NULL, 1995, 0),
(2, 36, 25, 1, 5, NULL, 1995, 1),
(2, 37, 26, 1, 5, NULL, 1995, 0),
(3, 1, 1, 1, NULL, NULL, 1996, 0),
(3, 2, 2, 1, NULL, NULL, 1996, 1),
(3, 3, 3, 1, 2, NULL, 1995, 0),
(3, 4, 3, 2, NULL, NULL, 1995, 0),
(3, 5, 3, 3, 1, NULL, 1995, 0),
(3, 6, 3, 4, NULL, NULL, 1995, 0),
(3, 7, 3, 5, 1, NULL, 1995, 0),
(3, 8, 3, 6, NULL, NULL, 1995, 0),
(3, 9, 3, 7, NULL, NULL, 1995, 0),
(3, 10, 4, 1, NULL, NULL, 1995, 0),
(4, 1, 27, 1, NULL, NULL, 2008, 0),
(4, 2, 27, 2, NULL, NULL, 2008, 0);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
