-- phpMyAdmin SQL Dump
-- version 4.0.6
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 16, 2013 at 12:22 PM
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
-- Dumping data for table `sound_formats`
--

INSERT INTO `sound_formats` (`sound_format_id`, `name`, `dyn_names`, `dyn_descriptions`) VALUES
(1, 'Dolby Digital 5.1', '', ''),
(2, 'Dolby Digital 2.0 Stereo', '', ''),
(3, 'DTS-HD Master Audio 5.1', '', ''),
(4, 'DTS 2.0 Stereo', '', ''),
(5, 'Dolby Digital 5.1 EX', '', ''),
(6, 'Stereo', '', '');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
