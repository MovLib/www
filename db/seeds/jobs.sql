-- phpMyAdmin SQL Dump
-- version 4.0.7
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 02, 2013 at 12:11 PM
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
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`job_id`, `title`, `description`, `dyn_titles`, `dyn_descriptions`) VALUES
(1, 'Producer', 'A Film Producer creates the conditions for filmmaking. The Producer initiates, coordinates, supervises, and controls matters such as raising funding, hiring key personnel, and arranging for distributors. The producer is involved throughout all phases of the film making process from development to completion of a project. There may be several producers on a film who may take a role in a number of areas, such as development, financing or production. Producers must be able to identify commercial, marketable projects. They need a keen business sense, and an intimate knowledge of all aspects of film production, financing, marketing and distribution. Producers are responsible for the overall quality control of productions.', '', ''),
(2, 'Executive Producer', 'An Executive Producer (EP) is a producer who was not involved in the technical aspects of the filmmaking process in the original definition, but has played a financial or creative role in ensuring that the project goes into production.[1] Today, however, the title has become ambiguous,[2] particularly in feature films. Since the 1980s, it has become increasingly common for the line producer to be given the title of executive producer, while the initiating producer takes the "produced by" credit. On other projects, the reverse happens, with the line producer taking the "produced by" credit. So the two credits have become effectively interchangeable, with no precise definition.', '', ''),
(3, 'Line Producer', 'The Line Producer is the liaison between the Studio or Producer and the Production Manager, responsible for managing the production budget. The title is associated with the idea that he or she is the person who is "on the line" on a day-to-day basis, and responsible for lining up the resources needed.', '', ''),
(4, 'Production Manager', 'The Production Manager supervises the physical aspects of the production (not the creative aspects) including personnel, technology, budget, and scheduling. It is the Production Managers responsibility to make sure the filming stays on schedule and within its budget. The PM also helps manage the day-to-day budget by managing operating costs such as salaries, production costs, and everyday equipment rental costs. The PM often works under the supervision of a Line Producer and directly supervises the Production Coordinator.', '', ''),
(5, 'Unit Manager', 'The Unit Manager fulfills the same role as the production manager but for secondary "unit" shooting. In some functional structures, the Unit Manager subsumes the role of the Transport Coordinator.', '', ''),
(6, 'Production Coordinator', 'The Production Coordinator is the information nexus of the production, responsible for organizing all the logistics from hiring crew, renting equipment, and booking talent. The PC is an integral part of film production.', '', ''),
(7, 'Post-production Supervisor', 'Post-production Supervisors are responsible for the post-production process, during which they maintain clarity of information and good channels of communication between the Producer, Editor, Supervising Sound Editor, the Facilities Companies (such as film labs, CGI studios and Negative Cutters) and the Production Accountant. Although this is not a creative role, it is pivotal in ensuring that the films post-production budget is manageable and achievable, and that all deadlines are met. Because large amounts of money are involved, and most of a films budget is spent during production, the post-production period can often be difficult and challenging.', '', ''),
(8, 'Production Assistant', 'Production Assistants, referred to as PAs, assist in the production office or in various departments with general tasks, such as assisting the First Assistant Director with set operations.', '', ''),
(9, 'Screenwriter', 'The Screenwriter, or Scriptwriter, may pitch a finished script to potential Producers, or may write a script under contract to a Producer. A Writer may be involved, to varied degrees, with creative aspects of production.', '', ''),
(10, 'Script Supervisor', 'Also known as the continuity person, the Script Supervisor keeps track of what parts of the script have been filmed and makes notes of any deviations between what was actually filmed and what appeared in the script. They make notes on every shot, and keep track of props, blocking, and other details to ensure continuity from shot to shot and scene to scene. The Script Supervisors notes are given to the Editor to expedite the editing process. The Script Supervisor works very closely with the Director on set.', '', ''),
(11, 'Stunt Coordinator', 'Where the film requires a stunt, and involves the use of stunt performers, the Stunt Coordinator will arrange the casting and performance of the stunt, working closely with the Director.', '', ''),
(12, 'Casting Director', 'The Casting Director chooses the Actors for the characters of the film. This usually involves inviting potential Actors to read an excerpt from the script for an audition.', '', '');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
