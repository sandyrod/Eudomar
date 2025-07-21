-- MySQL dump 10.13  Distrib 8.0.19, for Win64 (x86_64)
--
-- Host: localhost    Database: aguas
-- ------------------------------------------------------
-- Server version	8.0.30

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `bombas`
--

DROP TABLE IF EXISTS `bombas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bombas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `lps` varchar(100) DEFAULT NULL,
  `altura` varchar(100) DEFAULT NULL,
  `tipo` varchar(100) DEFAULT NULL,
  `cuerpo` varchar(100) DEFAULT NULL,
  `diametro` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bombas`
--

LOCK TABLES `bombas` WRITE;
/*!40000 ALTER TABLE `bombas` DISABLE KEYS */;
/*!40000 ALTER TABLE `bombas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `motores`
--

DROP TABLE IF EXISTS `motores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `motores` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hp` varchar(100) DEFAULT NULL,
  `voltaje` varchar(100) DEFAULT NULL,
  `tipo` varchar(100) DEFAULT NULL,
  `cuerpo` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `motores`
--

LOCK TABLES `motores` WRITE;
/*!40000 ALTER TABLE `motores` DISABLE KEYS */;
/*!40000 ALTER TABLE `motores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `municipios`
--

DROP TABLE IF EXISTS `municipios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `municipios` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `municipio` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `municipios`
--

LOCK TABLES `municipios` WRITE;
/*!40000 ALTER TABLE `municipios` DISABLE KEYS */;
INSERT INTO `municipios` VALUES (1,'Arístides Bastidas'),(2,'Bolívar'),(3,'Bruzual'),(4,'Cocorote'),(5,'Independencia'),(6,'José Antonio Páez'),(7,'La Trinidad'),(8,'Manuel Monge'),(9,'Nirgua'),(10,'Peña'),(11,'San Felipe'),(12,'Sucre'),(13,'Urachiche'),(14,'Veroes');
/*!40000 ALTER TABLE `municipios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `posos`
--

DROP TABLE IF EXISTS `posos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `posos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) DEFAULT NULL,
  `municipio` varchar(100) DEFAULT NULL,
  `norte` varchar(100) DEFAULT NULL,
  `este` varchar(100) DEFAULT NULL,
  `sectores` text,
  `habitantes` varchar(100) DEFAULT NULL,
  `lps` varchar(100) DEFAULT NULL,
  `statuso` varchar(100) DEFAULT NULL,
  `statusi` varchar(100) DEFAULT NULL,
  `motor_id` bigint unsigned DEFAULT NULL,
  `bomba_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `posos_motores_fk` (`motor_id`),
  KEY `posos_bombas_fk` (`bomba_id`),
  CONSTRAINT `posos_bombas_fk` FOREIGN KEY (`bomba_id`) REFERENCES `bombas` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `posos_motores_fk` FOREIGN KEY (`motor_id`) REFERENCES `motores` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `posos`
--

LOCK TABLES `posos` WRITE;
/*!40000 ALTER TABLE `posos` DISABLE KEYS */;
/*!40000 ALTER TABLE `posos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user` varchar(100) NOT NULL,
  `password` varchar(100) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `lastname` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `Users_unique` (`user`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'seienshi','$2y$10$dWDeUjHWV6ZFrv/IpmTqGudBFiaG1Ka6Ucyw5VU7dCXyRjuVm9ABq','Sandy Raymond','Mendoza','sandyrod@gmail.com','04127925566','administration'),(2,'christian','$2y$10$V9d1WaU5XjAju2.8j6pU0uOwucdSHf8SBJfNVTKZNSN1vr9W9V5Mq','Christian','Wilson','christianwilson@gmail.com','123456','operations');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'aguas'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-07-21 18:05:50
