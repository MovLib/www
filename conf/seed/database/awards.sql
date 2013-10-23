-- phpMyAdmin SQL Dump
-- version 4.0.8
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 11, 2013 at 01:12 PM
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
-- Dumping data for table `awards`
--

INSERT INTO `awards` (`award_id`, `name`, `description`, `dyn_names`, `dyn_descriptions`, `created`) VALUES
(1, 'Japan Academy Prize', NULL, '\0\0\0\0\0\0sdeja!Japanese Academy Award!日本アカデミー賞', '', CURRENT_TIMESTAMP),
(2, 'Czech Lion', NULL, '\0\0\0\0\0\0�\0csde!Český lev!Tschechischer Löwe', '', CURRENT_TIMESTAMP),
(3, 'César Award', NULL, '\0\0\0\0\0\0s\0defr!César!César', '', CURRENT_TIMESTAMP),
(4, 'Golden Reel Award (Motion Picture Sound Editors)', NULL, '\0\0\0\0\0\0deen!Golden Reel Award (Motion Picture Sound Editors)!Golden Reel Award (Motion Picture Sound Editors)', '', CURRENT_TIMESTAMP),
(5, 'Oscar', NULL, '\0\0\0\0\0de!Oscar', '', CURRENT_TIMESTAMP),
(6, 'Saturn Award', NULL, '\0\0\0\0\0de!Saturn Award', '', CURRENT_TIMESTAMP),
(7, 'Eddie', NULL, '\0\0\0\0\0de!Eddie', '', CURRENT_TIMESTAMP),
(8, 'ASC Award', NULL, '\0\0\0\0\0de!ASC Award', '', CURRENT_TIMESTAMP),
(9, 'Plus Camerimage', NULL, '\0\0\0\0\0de!Plus Camerimage', '', CURRENT_TIMESTAMP),
(10, 'Artios Award', NULL, '\0\0\0\0\0de!Artios Award', '', CURRENT_TIMESTAMP),
(11, 'CFCA Award', NULL, '\0\0\0\0\0de!CFCA Award', '', CURRENT_TIMESTAMP),
(12, 'Chlotrudis Award', NULL, '\0\0\0\0\0de!Chlotrudis Award', '', CURRENT_TIMESTAMP),
(13, 'DFWFCA Award', NULL, '\0\0\0\0\0de!DFWFCA Award', '', CURRENT_TIMESTAMP),
(14, 'DGA Award', NULL, '\0\0\0\0\0de!DGA Award', '', CURRENT_TIMESTAMP),
(15, 'Golden Globe', NULL, '\0\0\0\0\0de!Golden Globe', '', CURRENT_TIMESTAMP),
(16, 'Grammy', NULL, '\0\0\0\0\0de!Grammy', '', CURRENT_TIMESTAMP),
(17, 'Studio Crystal Heart Award', NULL, '\0\0\0\0\0de!Studio Crystal Heart Award', '', CURRENT_TIMESTAMP),
(18, 'Hochi Film Award', NULL, '\0\0\0\0\0\0deja!Hochi Film Award!報知映画賞', '', CURRENT_TIMESTAMP),
(19, 'Humanitas Prize', NULL, '\0\0\0\0\0de!Humanitas-Preis', '', CURRENT_TIMESTAMP),
(20, 'Kinema Junpo', NULL, '\0\0\0\0\0\0�\0deja!Kinema Junpo!キネマ旬報', '', CURRENT_TIMESTAMP),
(21, 'Mainichi Eiga Concours', NULL, '\0\0\0\0\0\0sdeja!Mainichi Eiga Concours!毎日映画コンクール', '', CURRENT_TIMESTAMP),
(22, 'NBR Award', NULL, '\0\0\0\0\0de!NBR Award', '', CURRENT_TIMESTAMP),
(23, 'PEN Center USA West Literary Awards', NULL, '\0\0\0\0\0de!PEN Center USA West Literary Awards', '', CURRENT_TIMESTAMP),
(24, 'Screen Actors Guild Award', NULL, '\0\0\0\0\0de!Screen Actors Guild Award', '', CURRENT_TIMESTAMP),
(25, 'USC Scripter Award', NULL, '\0\0\0\0\0de!USC Scripter Award', '', CURRENT_TIMESTAMP),
(26, 'WGA Award', NULL, '\0\0\0\0\0de!WGA Award', '', CURRENT_TIMESTAMP),
(27, 'Holland Animation Film Festival', NULL, '\0\0\0\0\0de!Animationsfilm-Festival Holland', '', CURRENT_TIMESTAMP);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
