-- MySQL dump 10.14  Distrib 10.0.5-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: movlib
-- ------------------------------------------------------
-- Server version	10.0.5-MariaDB-1~wheezy-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Fleshgrinder','2013-11-11 21:23:11','1985-06-27',12,'2013-10-01 16:44:29', 'EUR', '\0\0\0\0\0\0Ûdeen¿&lt;p&gt;Mein deutscher Profiltext.&lt;/p&gt;¿&lt;p&gt;My English profile text.&lt;/p&gt;',0,'richard@fussenegger.info','2013-11-11 21:21:51','jpg','$2y$13$LFDTAUaaxs5D6XulZkDU4uKtYgJBuyjDBS2ax7k.oqsASEXstzQDu',0,0,'Richard Fussenegger',0,1,'en','Europe/Vienna','http://richard.fussenegger.info/'),(2,'Ravenlord','2013-11-11 21:21:51',NULL,12,'2013-10-17 11:55:53', 'EUR','',0,'markus@deutschl.at',NULL,NULL,'$2y$13$xtl5jmUnz3F/Tss5qXyzt.fJ1Rppz/d2HGitxd.ig1MUM7gkXQCPC',0,0,'Markus Deutschl',0,1,'en','Europe/Vienna',NULL),(3,'ftorghele','2013-11-11 21:21:51',NULL,12,'2013-10-17 11:57:36', 'EUR','',0,'franz@torghele.at',NULL,NULL,'$2y$13$UZQYCsImiKIDQQu1OPfaTe9pZSsOd5OCgsEPVXgAVm98ygQLN0Mje',0,0,'Franz Torghele',0,1,'en','Europe/Vienna',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2013-11-11 21:25:22
