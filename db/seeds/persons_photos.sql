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
-- Dumping data for table `persons_photos`
--

INSERT INTO `persons_photos` (`person_id`, `section_id`, `user_id`, `license_id`, `filename`, `width`, `height`, `size`, `ext`, `changed`, `created`, `rating`, `dyn_descriptions`, `hash`, `source`) VALUES
(1, 1, 1, 1, 'Luc-Besson.1.en', 858, 1087, 239919, 'jpg', '2013-09-16 11:37:18', '2013-09-16 11:37:18', 0, '', 'hash\0\0\0\0\0\0\0\0\0\0\0\0', 'http://commons.wikimedia.org/wiki/File:Luc-Besson-Taken.JPG'),
(5, 1, 1, 1, 'Frank-Darabont.1.en', 348, 394, 109183, 'jpg', '2013-09-16 11:37:18', '2013-09-16 11:37:18', 0, '', 'hash\0\0\0\0\0\0\0\0\0\0\0\0', 'http://commons.wikimedia.org/wiki/File:Struzan_darabont.jpg');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
