-- phpMyAdmin SQL Dump
-- version 4.0.7
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 02, 2013 at 01:18 PM
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
-- Dumping data for table `styles`
--

INSERT INTO `styles` (`style_id`, `name`, `description`, `dyn_names`, `dyn_descriptions`) VALUES
(1, 'Film noir', '', '', ''),
(2, 'Color film noir', '', '', ''),
(3, 'Neo-noir', '', '', ''),
(4, 'Cinema verite', '', '', ''),
(5, 'Direct Cinema', '', '', ''),
(6, 'Documentary mode', '', '', ''),
(7, 'Fly on the wall', '', '', ''),
(8, 'Tex Murphy', '', '', ''),
(9, 'Ghost in the Shell films', '', '', ''),
(10, 'Terminator films', '', '', '');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
