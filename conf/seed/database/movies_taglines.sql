-- phpMyAdmin SQL Dump
-- version 4.0.8
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 11, 2013 at 01:30 PM
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
-- Dumping data for table `movies_taglines`
--

INSERT INTO `movies_taglines` (`movie_id`, `language_id`, `tagline`, `dyn_comments`) VALUES
(2, 41, 'Fear can hold you prisoner. Hope can set you free.', '\0\0\0\0\0\0Cdeen!deutsches Kommentar!english comment'),
(3, 41, 'If you want a job done well hire a professional.', ''),
(3, 41, 'A perfect assassin. An innocent girl. They have nothing left to lose except each other. He moves without sound. Kills without emotion. Disappears without trace. Only a 12 year old girl... knows his weakness.', ''),
(3, 41, 'He moves without sound. Kills without emotion. Disappears without trace.', ''),
(3, 41, 'You can&apos;t stop what you can&apos;t see.', ''),
(4, 41, 'Don&apos;t mess with the bunny!', '\0\0\0\0\0\0Cdeen!deutsches Kommentar!english comment');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
