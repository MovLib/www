-- phpMyAdmin SQL Dump
-- version 4.0.7
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 02, 2013 at 12:19 PM
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
-- Dumping data for table `movies_crew`
--

INSERT INTO `movies_crew` (`crew_id`, `movie_id`, `job_id`, `company_id`, `person_id`) VALUES
(1, 1, 1, NULL, 1),
(2, 1, 2, NULL, 2),
(3, 1, 3, NULL, 3),
(4, 1, 4, NULL, 4),
(5, 1, 5, NULL, 5),
(6, 1, 6, NULL, 6),
(7, 1, 7, NULL, 7),
(8, 2, 1, NULL, 1),
(9, 2, 2, NULL, 2),
(10, 2, 3, NULL, 3),
(11, 2, 4, NULL, 4),
(12, 2, 5, NULL, 5),
(13, 2, 6, NULL, 6),
(14, 2, 7, NULL, 7),
(15, 3, 1, NULL, 1),
(16, 3, 2, NULL, 2),
(17, 3, 3, NULL, 3),
(18, 3, 4, NULL, 4),
(19, 3, 5, NULL, 5),
(20, 3, 6, NULL, 6),
(21, 3, 7, NULL, 7);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
