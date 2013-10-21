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
-- Dumping data for table `awards_categories`
--

INSERT INTO `awards_categories` (`award_id`, `award_category_id`, `name`, `description`, `dyn_names`, `dyn_descriptions`) VALUES
(1, 1, 'Best Foreign Film', NULL, '\0\0\0\0\0de!Bester Ausländischer Film', ''),
(2, 1, 'Best Foreign Language Film', NULL, '\0\0\0\0\0\0�csde!Nejlepsí zahranicní film!Bester Fremdsprachiger Film', ''),
(3, 1, 'Best Actor', NULL, '\0\0\0\0\0\0sdefr!Bester Hauptdarsteller!Meilleur acteur', ''),
(3, 2, 'Best Cinematography', NULL, '\0\0\0\0\0\0Sdefr!Beste Kameraführung!Meilleure photographie', ''),
(3, 3, 'Best Director', NULL, '\0\0\0\0\0\0defr!Bester Regisseur!Meilleur réalisateur', ''),
(3, 4, 'Best Editing', NULL, '\0\0\0\0\0\0�\0defr!Bester Schnitt!Meilleur montage', ''),
(3, 5, 'Best Film', NULL, '\0\0\0\0\0\0�\0defr!Bester Film!Meilleur film', ''),
(3, 6, 'Best Music', NULL, '\0\0\0\0\0\0defr!Beste Filmmusik!Meilleur musique', ''),
(3, 7, 'Best Sound', NULL, '\0\0\0\0\0\0�\0defr!Bester Ton!Meilleur son', ''),
(4, 1, 'Best Sound Editing - Foreign Feature', NULL, '\0\0\0\0\0de!Beste Tonbearbeitung - Ausländischer Film', ''),
(5, 1, 'Best Actor in a Leading Role', NULL, '\0\0\0\0\0de!Bester Hauptdarsteller', ''),
(5, 2, 'Best Cinematography', NULL, '\0\0\0\0\0de!Beste Kameraführung', ''),
(5, 3, 'Best Film Editing', NULL, '\0\0\0\0\0de!Bester Schnitt', ''),
(5, 4, 'Best Music, Original Score', NULL, '\0\0\0\0\0de!Beste Filmmusik', ''),
(5, 5, 'Best Picture', NULL, '\0\0\0\0\0de!Bester Film', ''),
(5, 6, 'Best Sound', NULL, '\0\0\0\0\0de!Bester Ton', ''),
(5, 7, 'Best Writing (Adapted Screenplay)', NULL, '\0\0\0\0\0de!Bestes adaptiertes Drehbuch', ''),
(6, 1, 'Best Action/Adventure/Thriller Film', NULL, '\0\0\0\0\0de!Bester Action/Adventure/Thriller-Film', ''),
(6, 2, 'Best Writin', NULL, '\0\0\0\0\0de!Bestes Drehbuch', ''),
(7, 1, 'Best Edited Feature Film', NULL, '\0\0\0\0\0de!Bester Filmschnitt', ''),
(8, 1, 'Outstanding Achievement in Cinematography in Theatrical Releases', NULL, '', ''),
(9, 1, 'Bronze Frog', NULL, '\0\0\0\0\0de!Bronzener Frosch', ''),
(9, 2, 'Silver Frog', NULL, '\0\0\0\0\0de!Silberner Frosch', ''),
(9, 3, 'Golden Frog', NULL, '\0\0\0\0\0de!Goldener Frosch', ''),
(9, 4, 'Golden Frog (Lifetime Achievement)', NULL, '\0\0\0\0\0de!Goldener Frosch (Lebenswerk)', ''),
(10, 1, 'Best Casting for Feature Film, Drama', NULL, '', ''),
(11, 1, 'Best Picture', NULL, '\0\0\0\0\0de!Bester Film', ''),
(11, 2, 'Best Supporting Actor', NULL, '\0\0\0\0\0de!Bester Nebendarsteller', ''),
(12, 1, 'Best Actor', NULL, '\0\0\0\0\0de!Bester Hauptdarsteller', ''),
(13, 1, 'Best Actor', NULL, '\0\0\0\0\0de!Bester Hauptdarsteller', ''),
(13, 2, 'Best Picture', NULL, '\0\0\0\0\0de!Bester Film', ''),
(14, 1, 'Outstanding Directorial Achievement in Feature Film', NULL, '', ''),
(15, 1, 'Best Actor – Motion Picture Drama', NULL, '\0\0\0\0\0de!Bester Hauptdarsteller - Drama', ''),
(15, 2, 'Best Screenplay - Motion Picture', NULL, '\0\0\0\0\0de!Bestes Filmdrehbuch', ''),
(16, 1, 'Best Instrumental Composition Written for a Motion Picture or for Television', NULL, '', ''),
(18, 1, 'Best International Picture', NULL, '\0\0\0\0\0de!Bester Internationaler Film', ''),
(19, 1, 'Feature Film', NULL, '\0\0\0\0\0de!Film', ''),
(20, 1, 'Best Foreign Language Film', NULL, '\0\0\0\0\0de!Bester Fremdsprachiger Film', ''),
(20, 2, 'Reader&apos;s Choice', NULL, '\0\0\0\0\0de!Leserpreis', ''),
(21, 1, 'Best Foreign Language Film', NULL, '\0\0\0\0\0de!Bester Fremdsprachiger Film', ''),
(22, 1, 'Top Ten Films', NULL, '\0\0\0\0\0de!Top-Ten-Filme', ''),
(23, 1, 'Screenplay', NULL, '\0\0\0\0\0de!Drehbuch', ''),
(24, 1, 'Outstanding Performance by a Male Actor in a Leading Role', NULL, '\0\0\0\0\0de!Bester Hauptdarsteller', ''),
(26, 1, 'Best Adapted Screenplay', NULL, '\0\0\0\0\0de!Bestes adaptiertes Drehbuch', '');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
