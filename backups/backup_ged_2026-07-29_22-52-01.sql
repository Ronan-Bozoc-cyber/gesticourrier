-- ==========================================================
-- OpenGestiCourrier V1.3 - Export Sauvegarde Base de Données
-- Date: 2026-07-29 22:52:01
-- ==========================================================

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `categories` VALUES("1", "Administration");
INSERT INTO `categories` VALUES("3", "Finances");
INSERT INTO `categories` VALUES("2", "Ressources Humaines");
INSERT INTO `categories` VALUES("4", "Technique");


DROP TABLE IF EXISTS `courriers_arrive`;
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

INSERT INTO `courriers_arrive` VALUES("1", "1", "ARRIVE", "2026-07-01", "email", "12345", "Demande de renseignement", "Administration", NULL, "1", NULL, NULL, NULL, NULL, "1", NULL, "2026");


DROP TABLE IF EXISTS `courriers_depart`;
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
) ENGINE=InnoDB AUTO_INCREMENT=2343 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `courriers_depart` VALUES("1", "1", "DEPART", "2026-07-02", "papier", "67890", "Réponse à la demande", "Administration", NULL, NULL, "1", NULL, NULL, NULL, NULL, "1", "2026");
INSERT INTO `courriers_depart` VALUES("2340", "2", "DEPART", "2026-07-29", "papier", "", "test", "Administration", "/var/www/html/partials/../uploads/2-DEPART-Jean_Dupont-test-2026-07-29-document1.pdf", NULL, "1", NULL, NULL, NULL, NULL, "1", "2026");
INSERT INTO `courriers_depart` VALUES("2341", "3", "DEPART", "2026-07-29", "papier", "", "test", "Administration", "/var/www/html/partials/../uploads/3-DEPART-Jean_Dupont-test-2026-07-29-document1.pdf", NULL, "1", "/var/www/html/partials/../uploads/3-DEPART-Jean_Dupont-test-2026-07-29-document2.pdf", NULL, NULL, NULL, "1", "2026");
INSERT INTO `courriers_depart` VALUES("2342", "4", "DEPART", "2026-07-29", "papier", "", "tes 001", "Administration", "/var/www/html/partials/../uploads/4-DEPART-Jean_Dupont-tes_001-2026-07-29-document1.pdf", "1", "1", "/var/www/html/partials/../uploads/4-DEPART-Jean_Dupont-tes_001-2026-07-29-document2.pdf", "/var/www/html/partials/../uploads/4-DEPART-Jean_Dupont-tes_001-2026-07-29-document3.pdf", "/var/www/html/partials/../uploads/4-DEPART-Jean_Dupont-tes_001-2026-07-29-document4.pdf", NULL, "1", "2026");


DROP TABLE IF EXISTS `expediteurs`;
CREATE TABLE `expediteurs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `adresse` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=2316 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `expediteurs` VALUES("1", "Jean Dupont", "10 rue de Paris");
INSERT INTO `expediteurs` VALUES("2", "Marie Curie", "20 avenue des Sciences");


DROP TABLE IF EXISTS `password_reset`;
CREATE TABLE `password_reset` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=316 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



DROP TABLE IF EXISTS `users`;
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

INSERT INTO `users` VALUES("1", "admin", "$2y$12$vTBNALtejqJIDb/xn1FFjOF.XYzyG6ZgEOAHOm.aJEQVyeGxYlAEa", "admin@example.com", "admin");
INSERT INTO `users` VALUES("2", "user1", "$2y$12$vTBNALtejqJIDb/xn1FFjOF.XYzyG6ZgEOAHOm.aJEQVyeGxYlAEa", "user1@example.com", "user");


SET FOREIGN_KEY_CHECKS=1;
