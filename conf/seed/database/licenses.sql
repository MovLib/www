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

INSERT INTO `licenses` SET
  `dyn_names`         = COLUMN_CREATE('en', 'Copyrighted'),
  `dyn_descriptions`  = '',
  `url`               = 'https://en.wikipedia.org/wiki/Copyright',
  `abbreviation`      = '©',
  `icon_extension`    = 'svg',
  `icon_changed`      = CURRENT_TIMESTAMP
;

INSERT INTO `licenses` SET
  `dyn_names`         = COLUMN_CREATE('en', 'Creative Commons Zero 1.0 Universal'),
  `dyn_descriptions`  = COLUMN_CREATE('en', '&lt;p&gt;The person who associated a work with this deed has dedicated the work to the public domain by waiving all of his or her rights to the work worldwide under copyright law, including all related and neighboring rights, to the extent allowed by law. You can copy, modify, distribute and perform the work, even for commercial purposes, all without asking permission.&lt;/p&gt;'),
  `url`               = 'https://creativecommons.org/publicdomain/zero/1.0/',
  `abbreviation`      = 'CC0 1.0',
  `icon_extension`    = 'svg',
  `icon_changed`      = CURRENT_TIMESTAMP
;

INSERT INTO `licenses` SET
  `dyn_names`         = COLUMN_CREATE('en', 'Creative Commons Attribution 3.0 Unported'),
  `dyn_descriptions`  = COLUMN_CREATE('en', '&lt;p&gt;You are free:&lt;/p&gt;&lt;ul&gt;&lt;li&gt;&lt;b&gt;to share&lt;/b&gt; – to copy, distribute and transmit the work&lt;/li&gt;&lt;li&gt;&lt;b&gt;to remix&lt;/b&gt; – to adapt the work&lt;/li&gt;&lt;/ul&gt;&lt;p&gt;Under the following conditions:&lt;ul&gt;&lt;li&gt;&lt;b&gt;attribution&lt;/b&gt; – You must attribute the work in the manner specified by the author or licensor (but not in any way that suggests that they endorse you or your use of the work).&lt;/li&gt;&lt;/ul&gt;&lt;/p&gt;'),
  `url`               = 'https://creativecommons.org/licenses/by/3.0/',
  `abbreviation`      = 'CC BY 3.0',
  `icon_extension`    = 'svg',
  `icon_changed`      = CURRENT_TIMESTAMP
;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
