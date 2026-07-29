/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.18-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: courriers_db
-- ------------------------------------------------------
-- Server version	10.11.18-MariaDB-0+deb12u1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Administration'),(2,'Ressources Humaines'),(3,'Finances'),(4,'Technique');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `courriers_arrive`
--

DROP TABLE IF EXISTS `courriers_arrive`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `courriers_arrive` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `num_ordre` int(11) NOT NULL,
  `flux` enum('ARRIVE','DEPART') NOT NULL,
  `date` date DEFAULT current_timestamp(),
  `type_courrier` enum('papier','email','demat') DEFAULT NULL,
  `num_recommande` varchar(100) DEFAULT NULL,
  `sujet_courrier` varchar(255) NOT NULL,
  `categorie_courrier` varchar(100) NOT NULL,
  `document_path` varchar(255) DEFAULT NULL,
  `expediteur_id` int(11) DEFAULT NULL,
  `document_path2` varchar(255) DEFAULT NULL,
  `document_path3` varchar(255) DEFAULT NULL,
  `document_path4` varchar(255) DEFAULT NULL,
  `document_path5` varchar(255) DEFAULT NULL,
  `traite_par` varchar(100) DEFAULT NULL,
  `courrier_depart_id` int(11) DEFAULT NULL,
  `annee` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `num_ordre` (`num_ordre`,`annee`),
  UNIQUE KEY `num_ordre_2` (`num_ordre`,`annee`),
  KEY `expediteur_id` (`expediteur_id`),
  CONSTRAINT `courriers_arrive_ibfk_1` FOREIGN KEY (`expediteur_id`) REFERENCES `expediteurs` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6842 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `courriers_arrive`
--

LOCK TABLES `courriers_arrive` WRITE;
/*!40000 ALTER TABLE `courriers_arrive` DISABLE KEYS */;
INSERT INTO `courriers_arrive` VALUES (1,1,'ARRIVE','2026-07-01','email','12345','Demande de renseignement','Administration',NULL,1,NULL,NULL,NULL,NULL,'1',NULL,2026);
/*!40000 ALTER TABLE `courriers_arrive` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER update_annee_arrive BEFORE INSERT ON courriers_arrive FOR EACH ROW SET NEW.annee = YEAR(NEW.date) 
*/;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER update_annee_arrive_on_update BEFORE UPDATE ON courriers_arrive FOR EACH ROW SET NEW.annee = YEAR(NEW.date) 
*/;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `courriers_depart`
--

DROP TABLE IF EXISTS `courriers_depart`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `courriers_depart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `num_ordre` int(11) NOT NULL,
  `flux` enum('ARRIVE','DEPART') NOT NULL,
  `date` date NOT NULL DEFAULT curdate(),
  `type_courrier` enum('papier','email','demat') DEFAULT NULL,
  `num_recommande` varchar(100) DEFAULT NULL,
  `sujet_courrier` varchar(255) NOT NULL,
  `categorie_courrier` varchar(100) NOT NULL,
  `document_path` varchar(255) DEFAULT NULL,
  `courrier_arrive_id` int(11) DEFAULT NULL,
  `expediteur_id` int(11) DEFAULT NULL,
  `document_path2` varchar(255) DEFAULT NULL,
  `document_path3` varchar(255) DEFAULT NULL,
  `document_path4` varchar(255) DEFAULT NULL,
  `document_path5` varchar(255) DEFAULT NULL,
  `traite_par` varchar(100) DEFAULT NULL,
  `annee` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `num_ordre` (`num_ordre`,`annee`),
  KEY `courrier_arrive_id` (`courrier_arrive_id`),
  KEY `expediteur_id` (`expediteur_id`),
  CONSTRAINT `courriers_depart_ibfk_1` FOREIGN KEY (`courrier_arrive_id`) REFERENCES `courriers_arrive` (`id`),
  CONSTRAINT `courriers_depart_ibfk_2` FOREIGN KEY (`expediteur_id`) REFERENCES `expediteurs` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2340 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `courriers_depart`
--

LOCK TABLES `courriers_depart` WRITE;
/*!40000 ALTER TABLE `courriers_depart` DISABLE KEYS */;
INSERT INTO `courriers_depart` VALUES (1,1,'DEPART','2026-07-02','papier','67890','Réponse à la demande','Administration',NULL,NULL,1,NULL,NULL,NULL,NULL,'1',2026);
/*!40000 ALTER TABLE `courriers_depart` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER update_annee_depart BEFORE INSERT ON courriers_depart FOR EACH ROW SET NEW.annee = YEAR(NEW.date) 
*/;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER update_annee_depart_on_update BEFORE UPDATE ON courriers_depart FOR EACH ROW SET NEW.annee = YEAR(NEW.date) 
*/;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `expediteurs`
--

DROP TABLE IF EXISTS `expediteurs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `expediteurs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `adresse` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=2316 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expediteurs`
--

LOCK TABLES `expediteurs` WRITE;
/*!40000 ALTER TABLE `expediteurs` DISABLE KEYS */;
INSERT INTO `expediteurs` VALUES (1,'Jean Dupont','10 rue de Paris'),(2,'Marie Curie','20 avenue des Sciences');
/*!40000 ALTER TABLE `expediteurs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset`
--

DROP TABLE IF EXISTS `password_reset`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=316 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset`
--

LOCK TABLES `password_reset` WRITE;
/*!40000 ALTER TABLE `password_reset` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'user',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','$2y$10$YBoCv7HhcnVOYIbPJKAyFOGd9k5WBAIcbCN2YnIGnyWI3VUytXC5a','admin@example.com','admin'),(2,'user1','$2y$10$YBoCv7HhcnVOYIbPJKAyFOGd9k5WBAIcbCN2YnIGnyWI3VUytXC5a','user1@example.com','user');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'courriers_db'
--

--
-- Dumping routines for database 'courriers_db'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-29 13:57:33
