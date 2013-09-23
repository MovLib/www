-- phpMyAdmin SQL Dump
-- version 4.0.6
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 23, 2013 at 04:16 PM
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
-- Dumping data for table `movies_images`
--

INSERT INTO `movies_images` (`movie_id`, `section_id`, `user_id`, `license_id`, `country_id`, `filename`, `width`, `height`, `size`, `ext`, `changed`, `created`, `rating`, `dyn_descriptions`, `type`, `hash`, `source`) VALUES
(1, 1, 1, 2, 77, 'roundhay-garden-scene.1.en', 856, 482, 73462, 'jpg', '2013-09-16 11:37:18', '2013-09-16 11:37:18', 0, '', 0, 'hashhashhashhash', 'Screenshot'),
(2, 1, 1, 1, 233, 'the-shawshank-redemption.1.en', 269, 395, 66394, 'jpg', '2013-09-16 11:37:18', '2013-09-16 11:37:18', 0, '', 0, 'hashhashhashhash', '<a href="http://en.wikipedia.org/wiki/File:ShawshankRedemptionMoviePoster.jpg" rel="nofollow" target="_blank">http://en.wikipedia.org/wiki/File:ShawshankRedemptionMoviePoster.jpg</a>'),
(3, 1, 1, 1, 233, 'leon-the-professional.1.en', 936, 1408, 648028, 'jpg', '2013-09-16 11:37:18', '2013-09-16 11:37:18', 0, '', 0, 'hashhashhashhash', '<a href="http://www.movieposterdb.com/poster/3145139b" rel="nofollow" target="_blank">http://www.movieposterdb.com/poster/3145139b</a>');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
