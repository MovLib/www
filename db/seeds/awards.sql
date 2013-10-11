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

INSERT INTO `awards` (`award_id`, `name`, `description`, `dyn_names`, `dyn_descriptions`) VALUES
(1, 'Japan Academy Prize', NULL, '\0\0\0\0\0\0sdeja!Japanese Academy Award!日本アカデミー賞', ''),
(2, 'Czech Lion', NULL, '\0\0\0\0\0\0�\0csde!Český lev!Tschechischer Löwe', ''),
(3, 'César Award', NULL, '\0\0\0\0\0\0s\0defr!César!César', ''),
(4, 'Golden Reel Award (Motion Picture Sound Editors)', NULL, '\0\0\0\0\0\0deen!Golden Reel Award (Motion Picture Sound Editors)!Golden Reel Award (Motion Picture Sound Editors)', ''),
(5, 'Oscar', NULL, '\0\0\0\0\0de!Oscar', ''),
(6, 'Saturn Award', NULL, '\0\0\0\0\0de!Saturn Award', ''),
(7, 'Eddie', NULL, '\0\0\0\0\0de!Eddie', ''),
(8, 'ASC Award', NULL, '\0\0\0\0\0de!ASC Award', ''),
(9, 'Plus Camerimage', NULL, '\0\0\0\0\0de!Plus Camerimage', ''),
(10, 'Artios Award', NULL, '\0\0\0\0\0de!Artios Award', ''),
(11, 'CFCA Award', NULL, '\0\0\0\0\0de!CFCA Award', ''),
(12, 'Chlotrudis Award', NULL, '\0\0\0\0\0de!Chlotrudis Award', ''),
(13, 'DFWFCA Award', NULL, '\0\0\0\0\0de!DFWFCA Award', ''),
(14, 'DGA Award', NULL, '\0\0\0\0\0de!DGA Award', ''),
(15, 'Golden Globe', NULL, '\0\0\0\0\0de!Golden Globe', ''),
(16, 'Grammy', NULL, '\0\0\0\0\0de!Grammy', ''),
(17, 'Studio Crystal Heart Award', NULL, '\0\0\0\0\0de!Studio Crystal Heart Award', ''),
(18, 'Hochi Film Award', NULL, '\0\0\0\0\0\0deja!Hochi Film Award!報知映画賞', ''),
(19, 'Humanitas Prize', NULL, '\0\0\0\0\0de!Humanitas-Preis', ''),
(20, 'Kinema Junpo', NULL, '\0\0\0\0\0\0�\0deja!Kinema Junpo!キネマ旬報', ''),
(21, 'Mainichi Eiga Concours', NULL, '\0\0\0\0\0\0sdeja!Mainichi Eiga Concours!毎日映画コンクール', ''),
(22, 'NBR Award', NULL, '\0\0\0\0\0de!NBR Award', ''),
(23, 'PEN Center USA West Literary Awards', NULL, '\0\0\0\0\0de!PEN Center USA West Literary Awards', ''),
(24, 'Screen Actors Guild Award', NULL, '\0\0\0\0\0de!Screen Actors Guild Award', ''),
(25, 'USC Scripter Award', NULL, '\0\0\0\0\0de!USC Scripter Award', ''),
(26, 'WGA Award', NULL, '\0\0\0\0\0de!WGA Award', ''),
(27, 'Holland Animation Film Festival', NULL, '\0\0\0\0\0de!Animationsfilm-Festival Holland', '');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
