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
-- Dumping data for table `releases`
--

INSERT INTO `releases` (`release_id`, `master_release_id`, `is_cut`, `ean`, `length`, `length_credits`, `length_bonus`, `dyn_extras`, `dyn_notes`, `aspect_ratio_id`, `packaging_id`, `type`, `bin_type_data`) VALUES
(1, 1, 0, '4009750255773', '02:12:38', '02:16:44', '01:16:00', '', '', 1, 1, 'DVD', ''),
(2, 2, 1, '4009750216279', NULL, NULL, NULL, '', '\0\0\0\0\0\0cdeen!Der Film wurde aufgrund eines Masteringfehlers unabsichtlich um 2-3 Minuten gekürzt.!The movie has been accidentaly cut by 2-3 minutes due to a mastering mistake.', 3, 2, 'DVD', ''),
(3, 3, 0, '4012909054233', NULL, '02:17:00', NULL, '', '\0\0\0\0\0\0�deen!Abspann beginnt über bewegtem Filmbild.!The credits start over the moving movie image.', 2, 4, 'Video', '');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
