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
-- Dumping data for table `licenses`
--

INSERT INTO `licenses` (`license_id`, `name`, `description`, `dyn_names`, `dyn_descriptions`, `url`, `abbr`, `icon_extension`, `icon_hash`, `admin`) VALUES
(1, 'Copyright protected', '', '', '', 'https://en.wikipedia.org/wiki/Copyright', '©', 'svg', NULL, 1),
(2, 'Creative Commons CC0 1.0 Universal Public Domain Dedication', '<p>The person who associated a work with this deed has dedicated the work to the public domain by waiving all of his or her rights to the work worldwide under copyright law, including all related and neighboring rights, to the extent allowed by law. You can copy, modify, distribute and perform the work, even for commercial purposes, all without asking permission.</p>', '', '', 'https://creativecommons.org/publicdomain/zero/1.0/', 'CC0 1.0', 'svg', NULL, 1),
(3, 'Creative Commons Attribution 3.0 Unported', '<p>You are free:</p><ul><li><b>to share</b> – to copy, distribute and transmit the work</li><li><b>to remix</b> – to adapt the work</li></ul><p>Under the following conditions:<ul><li><b>attribution</b> – You must attribute the work in the manner specified by the author or licensor (but not in any way that suggests that they endorse you or your use of the work).</li></ul></p>', '', '', 'https://creativecommons.org/licenses/by/3.0/', 'CC BY 3.0', 'svg', NULL, 1);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
