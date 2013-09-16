-- phpMyAdmin SQL Dump
-- version 4.0.6
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 16, 2013 at 12:21 PM
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
-- Dumping data for table `persons`
--

INSERT INTO `persons` (`person_id`, `name`, `deleted`, `born_name`, `birthdate`, `deathdate`, `country`, `city`, `region`, `sex`, `rank`, `dyn_aliases`, `dyn_biographies`, `dyn_links`, `commit`) VALUES
(1, 'Luc Besson', 0, NULL, '1959-03-18', NULL, 'FR', 'Paris', NULL, 1, NULL, '', '', '', NULL),
(2, 'Jean Reno', 0, 'Juan Moreno y Herrera-Jiménez', '1948-07-30', NULL, 'MA', 'Casablanca', NULL, 1, NULL, '', '', '', NULL),
(3, 'Natalie Portman', 0, NULL, '1981-06-09', NULL, 'IL', 'Jerusalem', NULL, 2, NULL, '', '', '', NULL),
(4, 'Gary Oldman', 0, NULL, '1958-03-21', NULL, 'GB', 'London', NULL, 1, NULL, '', '', '', NULL),
(5, 'Frank Darabont', 0, NULL, '1959-01-28', NULL, 'FR', 'Montbéliard', NULL, 1, NULL, '', '', '', NULL),
(6, 'Tim Robbins', 0, NULL, '1958-10-16', NULL, 'US', 'West Covina, CA', NULL, 1, NULL, '', '', '', NULL),
(7, 'Morgan Freeman', 0, NULL, '1937-06-01', NULL, 'US', 'Memphis, TN', NULL, 1, NULL, '', '', '', NULL);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
