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
-- Dumping data for table `users_collections`
--

INSERT INTO `users_collections` (`user_id`, `release_id`, `count`, `currency_code`, `price`, `purchased_at`) VALUES
(1, 1, 1, 'EUR', 9.99, null),
(1, 2, 2, 'EUR', 7.99, 'Amazon'),
(1, 3, 1, 'USD', 5.99, 'http://www.amazon.de'),
(2, 1, 1, 'EUR', 9.99, null),
(2, 2, 2, 'EUR', 7.99, 'Amazon'),
(2, 3, 1, 'USD', 5.99, 'http://www.amazon.de'),
(3, 1, 1, 'EUR', 9.99, null),
(3, 2, 2, 'EUR', 7.99, 'Amazon'),
(3, 3, 1, 'USD', 5.99, 'http://www.amazon.de');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;