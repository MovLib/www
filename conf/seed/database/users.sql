-- phpMyAdmin SQL Dump
-- version 4.0.8
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 17, 2013 at 12:03 PM
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
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `access`, `created`, `login`, `private`, `deactivated`, `timeZoneId`, `edits`, `profile`, `sex`, `systemLanguageCode`, `imageExtension`, `imageChanged`, `birthday`, `countryId`, `realName`, `website`) VALUES
(1, 'Fleshgrinder', 'richard@fussenegger.info', '$2y$13$LFDTAUaaxs5D6XulZkDU4uKtYgJBuyjDBS2ax7k.oqsASEXstzQDu', '2013-10-02 09:45:26', '2013-10-01 16:44:29', '2013-10-01 16:44:29', 0, 0, 'Europe/Vienna', 0, '\0\0\0\0\0\0#deen!<p>Mein deutscher Profiltext.</p>!<p>My English profile text.</p>', 1, 'de', 'jpg', '2013-10-02 09:45:26', '1985-06-07', 12, 'Richard Fussenegger', 'http://richard.fussenegger.info/'),
(2, 'ftorghele', 'franz@torghele.at', '$2y$13$UZQYCsImiKIDQQu1OPfaTe9pZSsOd5OCgsEPVXgAVm98ygQLN0Mje', '2013-10-17 12:03:14', '2013-10-17 11:55:53', '2013-10-17 11:55:53', 0, 0, 'UTC', 0, '', 0, 'en', 'jpg', '1970-01-01 00:33:33', NULL, NULL, NULL, NULL),
(3, 'Ravenlord', 'markus@deutschl.at', '$2y$13$xtl5jmUnz3F/Tss5qXyzt.fJ1Rppz/d2HGitxd.ig1MUM7gkXQCPC', '2013-10-17 12:03:43', '2013-10-17 11:57:36', '2013-10-17 11:57:36', 0, 0, 'UTC', 0, '', 0, 'en', 'jpg', '2013-10-15 00:00:00', NULL, NULL, NULL, NULL);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
