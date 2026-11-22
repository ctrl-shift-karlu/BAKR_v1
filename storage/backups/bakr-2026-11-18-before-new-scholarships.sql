/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.16-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: bakr
-- ------------------------------------------------------
-- Server version	10.11.16-MariaDB

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
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` bigint(20) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES
('bakr-cache-0ade7c2cf97f75d009975f4d720d1fa6c19f4897','i:1;',1794935411),
('bakr-cache-0ade7c2cf97f75d009975f4d720d1fa6c19f4897:timer','i:1794935411;',1794935411),
('bakr-cache-1b6453892473a467d07372d45eb05abc2031647a','i:1;',1794934265),
('bakr-cache-1b6453892473a467d07372d45eb05abc2031647a:timer','i:1794934265;',1794934265),
('bakr-cache-356a192b7913b04c54574d18c28d46e6395428ab','i:1;',1794931093),
('bakr-cache-356a192b7913b04c54574d18c28d46e6395428ab:timer','i:1794931093;',1794931093),
('bakr-cache-5c785c036466adea360111aa28563bfd556b5fba','i:1;',1795005268),
('bakr-cache-5c785c036466adea360111aa28563bfd556b5fba:timer','i:1795005268;',1795005268),
('bakr-cache-902ba3cda1883801594b6e1b452790cc53948fda','i:1;',1794984703),
('bakr-cache-902ba3cda1883801594b6e1b452790cc53948fda:timer','i:1794984703;',1794984703),
('bakr-cache-da4b9237bacccdf19c0760cab7aec4a8359010b0','i:1;',1794935318),
('bakr-cache-da4b9237bacccdf19c0760cab7aec4a8359010b0:timer','i:1794935318;',1794935318),
('bakr-cache-fe5dbbcea5ce7e2988b8c69bcfdfde8904aabc1f','i:3;',1794934539),
('bakr-cache-fe5dbbcea5ce7e2988b8c69bcfdfde8904aabc1f:timer','i:1794934538;',1794934539);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` bigint(20) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `campuses`
--

DROP TABLE IF EXISTS `campuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `campuses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `campus_name` varchar(255) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `campuses_campus_name_unique` (`campus_name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `campuses`
--

LOCK TABLES `campuses` WRITE;
/*!40000 ALTER TABLE `campuses` DISABLE KEYS */;
INSERT INTO `campuses` VALUES
(1,'Main (Panabo Campus)','Panabo City','2026-11-18 04:20:09','2026-11-18 04:20:09'),
(2,'Carmen Campus','Carmen','2026-11-18 04:20:09','2026-11-18 04:20:09'),
(3,'Samal Campus','Island Garden City of Samal','2026-11-18 04:20:09','2026-11-18 04:20:09'),
(4,'Talaingod Campus','Talaingod','2026-11-18 04:20:09','2026-11-18 04:20:09'),
(5,'Asuncion Campus','Asuncion','2026-11-18 04:20:09','2026-11-18 04:20:09');
/*!40000 ALTER TABLE `campuses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contact_messages`
--

DROP TABLE IF EXISTS `contact_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `contact_messages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `topic` varchar(30) NOT NULL,
  `name` varchar(80) NOT NULL,
  `email` varchar(120) NOT NULL,
  `rating` tinyint(3) unsigned DEFAULT NULL,
  `message` text NOT NULL,
  `share_publicly` tinyint(1) NOT NULL DEFAULT 0,
  `approved_at` timestamp NULL DEFAULT NULL,
  `emailed_at` timestamp NULL DEFAULT NULL,
  `handled_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `contact_messages_topic_share_publicly_approved_at_index` (`topic`,`share_publicly`,`approved_at`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact_messages`
--

LOCK TABLES `contact_messages` WRITE;
/*!40000 ALTER TABLE `contact_messages` DISABLE KEYS */;
INSERT INTO `contact_messages` VALUES
(1,'question','haha','kgabaes@gmail.com',NULL,'gwapoko nohhh?? oejreajdada',0,NULL,'2026-11-17 05:57:32',NULL,'2026-11-17 05:57:29','2026-11-17 05:57:32');
/*!40000 ALTER TABLE `contact_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES
(1,'0001_01_01_000000_create_users_table',1),
(2,'2026_11_05_000001_add_role_and_status_to_users_table',1),
(3,'2026_11_05_000002_create_reference_tables',1),
(4,'2026_11_05_000003_create_student_profiles_table',1),
(5,'2026_11_05_000004_create_student_applications_table',1),
(6,'2026_11_05_083739_create_cache_table',1),
(7,'2026_11_07_000001_create_scholarships_table',1),
(8,'2026_11_07_000002_create_scholarship_applications_table',1),
(9,'2026_11_07_000003_create_scholarship_matches_table',1),
(10,'2026_11_08_000001_create_contact_messages_table',1),
(11,'2026_11_09_000001_drop_legacy_phase3_tables',1),
(12,'2026_11_12_000001_add_provider_key_to_users_table',1),
(13,'2026_11_12_000002_create_provider_operations_tables',1),
(14,'2026_11_13_000001_add_release_type_to_stipend_disbursements',1),
(15,'2026_11_15_000001_add_application_dates_to_scholarships',1),
(16,'2026_11_17_000001_add_roster_fields_to_scholars',2),
(17,'2026_11_17_000002_add_grants_to_scholarships_and_scholars',3),
(18,'2026_11_17_000003_add_scholar_accounts',4),
(19,'2026_11_18_000001_move_scholars_to_scholar_accounts',5);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `programs`
--

DROP TABLE IF EXISTS `programs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `programs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `program_name` varchar(255) NOT NULL,
  `campus_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `programs_campus_id_program_name_unique` (`campus_id`,`program_name`),
  CONSTRAINT `programs_campus_id_foreign` FOREIGN KEY (`campus_id`) REFERENCES `campuses` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=91 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `programs`
--

LOCK TABLES `programs` WRITE;
/*!40000 ALTER TABLE `programs` DISABLE KEYS */;
INSERT INTO `programs` VALUES
(1,'Bachelor of Science in Information Systems',1,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(2,'Bachelor of Science in Information Technology',1,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(3,'Bachelor of Science in Agro-Forestry',1,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(4,'Bachelor of Science in Fisheries and Aquatic Sciences',1,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(5,'Bachelor of Science in Food Technology',1,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(6,'Bachelor of Science in Marine Biology',1,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(7,'Bachelor of Public Administration',1,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(8,'Bachelor of Science in Criminology',1,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(9,'Bachelor of Science in Disaster Resilience and Management',1,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(10,'Bachelor of Science in Entrepreneurship',1,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(11,'Bachelor of Science in Social Work',1,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(12,'Bachelor of Science in Tourism Management',1,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(13,'Bachelor of Arts in Communication',1,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(14,'Bachelor of Secondary Education - English',1,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(15,'Bachelor of Secondary Education - Mathematics',1,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(16,'Bachelor of Secondary Education - Science',1,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(17,'Bachelor of Technology and Livelihood Education',1,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(18,'Bachelor of Physical Education',1,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(19,'Bachelor of Science in Information Systems',2,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(20,'Bachelor of Science in Information Technology',2,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(21,'Bachelor of Science in Agro-Forestry',2,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(22,'Bachelor of Science in Fisheries and Aquatic Sciences',2,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(23,'Bachelor of Science in Food Technology',2,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(24,'Bachelor of Science in Marine Biology',2,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(25,'Bachelor of Public Administration',2,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(26,'Bachelor of Science in Criminology',2,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(27,'Bachelor of Science in Disaster Resilience and Management',2,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(28,'Bachelor of Science in Entrepreneurship',2,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(29,'Bachelor of Science in Social Work',2,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(30,'Bachelor of Science in Tourism Management',2,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(31,'Bachelor of Arts in Communication',2,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(32,'Bachelor of Secondary Education - English',2,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(33,'Bachelor of Secondary Education - Mathematics',2,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(34,'Bachelor of Secondary Education - Science',2,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(35,'Bachelor of Technology and Livelihood Education',2,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(36,'Bachelor of Physical Education',2,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(37,'Bachelor of Science in Information Systems',3,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(38,'Bachelor of Science in Information Technology',3,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(39,'Bachelor of Science in Agro-Forestry',3,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(40,'Bachelor of Science in Fisheries and Aquatic Sciences',3,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(41,'Bachelor of Science in Food Technology',3,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(42,'Bachelor of Science in Marine Biology',3,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(43,'Bachelor of Public Administration',3,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(44,'Bachelor of Science in Criminology',3,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(45,'Bachelor of Science in Disaster Resilience and Management',3,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(46,'Bachelor of Science in Entrepreneurship',3,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(47,'Bachelor of Science in Social Work',3,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(48,'Bachelor of Science in Tourism Management',3,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(49,'Bachelor of Arts in Communication',3,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(50,'Bachelor of Secondary Education - English',3,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(51,'Bachelor of Secondary Education - Mathematics',3,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(52,'Bachelor of Secondary Education - Science',3,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(53,'Bachelor of Technology and Livelihood Education',3,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(54,'Bachelor of Physical Education',3,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(55,'Bachelor of Science in Information Systems',4,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(56,'Bachelor of Science in Information Technology',4,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(57,'Bachelor of Science in Agro-Forestry',4,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(58,'Bachelor of Science in Fisheries and Aquatic Sciences',4,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(59,'Bachelor of Science in Food Technology',4,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(60,'Bachelor of Science in Marine Biology',4,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(61,'Bachelor of Public Administration',4,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(62,'Bachelor of Science in Criminology',4,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(63,'Bachelor of Science in Disaster Resilience and Management',4,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(64,'Bachelor of Science in Entrepreneurship',4,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(65,'Bachelor of Science in Social Work',4,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(66,'Bachelor of Science in Tourism Management',4,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(67,'Bachelor of Arts in Communication',4,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(68,'Bachelor of Secondary Education - English',4,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(69,'Bachelor of Secondary Education - Mathematics',4,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(70,'Bachelor of Secondary Education - Science',4,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(71,'Bachelor of Technology and Livelihood Education',4,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(72,'Bachelor of Physical Education',4,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(73,'Bachelor of Science in Information Systems',5,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(74,'Bachelor of Science in Information Technology',5,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(75,'Bachelor of Science in Agro-Forestry',5,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(76,'Bachelor of Science in Fisheries and Aquatic Sciences',5,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(77,'Bachelor of Science in Food Technology',5,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(78,'Bachelor of Science in Marine Biology',5,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(79,'Bachelor of Public Administration',5,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(80,'Bachelor of Science in Criminology',5,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(81,'Bachelor of Science in Disaster Resilience and Management',5,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(82,'Bachelor of Science in Entrepreneurship',5,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(83,'Bachelor of Science in Social Work',5,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(84,'Bachelor of Science in Tourism Management',5,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(85,'Bachelor of Arts in Communication',5,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(86,'Bachelor of Secondary Education - English',5,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(87,'Bachelor of Secondary Education - Mathematics',5,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(88,'Bachelor of Secondary Education - Science',5,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(89,'Bachelor of Technology and Livelihood Education',5,'2026-11-18 04:20:09','2026-11-18 04:20:09'),
(90,'Bachelor of Physical Education',5,'2026-11-18 04:20:09','2026-11-18 04:20:09');
/*!40000 ALTER TABLE `programs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `provider_applications`
--

DROP TABLE IF EXISTS `provider_applications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `provider_applications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `scholarship_id` bigint(20) unsigned NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'submitted',
  `snapshot` text DEFAULT NULL,
  `consented_at` timestamp NOT NULL,
  `decided_at` timestamp NULL DEFAULT NULL,
  `decided_by` bigint(20) unsigned DEFAULT NULL,
  `decision_note` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `provider_applications_user_id_scholarship_id_unique` (`user_id`,`scholarship_id`),
  KEY `provider_applications_decided_by_foreign` (`decided_by`),
  KEY `provider_applications_scholarship_id_status_index` (`scholarship_id`,`status`),
  CONSTRAINT `provider_applications_decided_by_foreign` FOREIGN KEY (`decided_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `provider_applications_scholarship_id_foreign` FOREIGN KEY (`scholarship_id`) REFERENCES `scholarships` (`id`) ON DELETE CASCADE,
  CONSTRAINT `provider_applications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `provider_applications`
--

LOCK TABLES `provider_applications` WRITE;
/*!40000 ALTER TABLE `provider_applications` DISABLE KEYS */;
/*!40000 ALTER TABLE `provider_applications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `scholar_requirements`
--

DROP TABLE IF EXISTS `scholar_requirements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `scholar_requirements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `scholar_id` bigint(20) unsigned NOT NULL,
  `term` varchar(60) NOT NULL,
  `name` varchar(200) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'missing',
  `file_path` varchar(255) DEFAULT NULL,
  `original_name` varchar(200) DEFAULT NULL,
  `mime` varchar(100) DEFAULT NULL,
  `size` int(10) unsigned DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `review_note` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `scholar_requirements_scholar_id_term_name_unique` (`scholar_id`,`term`,`name`),
  KEY `scholar_requirements_term_status_index` (`term`,`status`),
  CONSTRAINT `scholar_requirements_scholar_id_foreign` FOREIGN KEY (`scholar_id`) REFERENCES `scholars` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `scholar_requirements`
--

LOCK TABLES `scholar_requirements` WRITE;
/*!40000 ALTER TABLE `scholar_requirements` DISABLE KEYS */;
INSERT INTO `scholar_requirements` VALUES
(1,2,'AY 2026–2027 · 1st Semester','Certified true copy of the Certificate of Registration / Enrollment (COR / COE)','missing',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-11-17 21:09:34','2026-11-17 21:09:34'),
(2,2,'AY 2026–2027 · 1st Semester','Certified true copy of grades showing the GWA of the previous semester','submitted','scholar-requirements/2/AXdnerHjcQr4Rjc9vFkBOvxzglLQb3HMSKdS8F9Z.png','Screenshot 2026-11-15 173629.png','image/png',68697,'2026-11-17 22:50:43',NULL,NULL,'2026-11-17 21:09:34','2026-11-17 22:50:43'),
(3,2,'AY 2026–2027 · 1st Semester','HEI certification that you do not receive another national government-funded scholarship (every start of the academic year)','missing',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-11-17 21:09:34','2026-11-17 21:09:34');
/*!40000 ALTER TABLE `scholar_requirements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `scholars`
--

DROP TABLE IF EXISTS `scholars`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `scholars` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `full_name` varchar(150) DEFAULT NULL,
  `email` varchar(190) DEFAULT NULL,
  `scholarship_id` bigint(20) unsigned NOT NULL,
  `provider_application_id` bigint(20) unsigned DEFAULT NULL,
  `student_number` varchar(30) DEFAULT NULL,
  `program` varchar(150) DEFAULT NULL,
  `year_level` varchar(20) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `grant_type` varchar(30) DEFAULT NULL,
  `started_term` varchar(60) DEFAULT NULL,
  `stipend_amount` decimal(10,2) DEFAULT NULL,
  `added_by` bigint(20) unsigned DEFAULT NULL,
  `credentials_sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `scholars_user_id_scholarship_id_unique` (`user_id`,`scholarship_id`),
  KEY `scholars_provider_application_id_foreign` (`provider_application_id`),
  KEY `scholars_scholarship_id_status_index` (`scholarship_id`,`status`),
  KEY `scholars_added_by_foreign` (`added_by`),
  KEY `scholars_scholarship_id_student_number_index` (`scholarship_id`,`student_number`),
  CONSTRAINT `scholars_added_by_foreign` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `scholars_provider_application_id_foreign` FOREIGN KEY (`provider_application_id`) REFERENCES `provider_applications` (`id`) ON DELETE SET NULL,
  CONSTRAINT `scholars_scholarship_id_foreign` FOREIGN KEY (`scholarship_id`) REFERENCES `scholarships` (`id`) ON DELETE CASCADE,
  CONSTRAINT `scholars_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `scholars`
--

LOCK TABLES `scholars` WRITE;
/*!40000 ALTER TABLE `scholars` DISABLE KEYS */;
INSERT INTO `scholars` VALUES
(2,7,'Karl Angelo T. Gabaes','gabaes.karlangelo@dnsc.edu.ph',8,NULL,'2025-00344','Bachelor of Science in Information Technology','2','active','half','AY 2026–2027 · 1st Semester',20000.00,1,'2026-11-17 07:57:19','2026-11-17 07:57:13','2026-11-17 07:57:19'),
(3,8,'Juan Tapulan','gabaeskarlangelo@gmail.com',2,NULL,'2025-00123','Bachelor of Science in Information Systems','3','active',NULL,'AY 2026–2027 · 1st Semester',NULL,4,'2026-11-17 08:50:12','2026-11-17 08:50:06','2026-11-17 08:50:12'),
(4,9,'Raiden Shogun','hakdoggie678@gmail.com',7,NULL,'2025-06969','Bachelor of Science in Social Work','4','active','regular','AY 2026–2027 · 1st Semester',8000.00,2,'2026-11-17 09:07:43','2026-11-17 09:07:38','2026-11-17 09:07:43');
/*!40000 ALTER TABLE `scholars` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `scholarship_applications`
--

DROP TABLE IF EXISTS `scholarship_applications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `scholarship_applications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `version` int(10) unsigned NOT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT 1,
  `status` varchar(20) NOT NULL DEFAULT 'submitted',
  `answers` longtext NOT NULL,
  `ai_summary` text DEFAULT NULL,
  `ai_summary_at` timestamp NULL DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sch_apps_user_version_unique` (`user_id`,`version`),
  KEY `sch_apps_user_current_idx` (`user_id`,`is_current`),
  CONSTRAINT `scholarship_applications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `scholarship_applications`
--

LOCK TABLES `scholarship_applications` WRITE;
/*!40000 ALTER TABLE `scholarship_applications` DISABLE KEYS */;
INSERT INTO `scholarship_applications` VALUES
(2,11,1,1,'submitted','eyJpdiI6InZ6alZGUWZSb2Z5OXhaQkx5aEV1WFE9PSIsInZhbHVlIjoiOFhFVnVwMmhZanluYnZjUTdOTmlwQVN6NGY3a1FwSXM0eE56eTJOYzJ4SXl2bW9JbndydVozZ2lZc25QZDV3T0ozaVFXZDdyUERiYVdvc0w0c0ROT3JYMGVtSXZaREQ5R2VUamxJSklxbmk0TlRZUkN1d3hYSzU5TnFIRXBqeEIzbnNBNWlMQ2l1SlJHem9TTXgzNTFXYVl2SDVHZGUyTjBIcnBicDNhYzI0SE1KM3RwZlZpYUtzczhpNkxsRWtZdlIzOVJiSDlHaVRPWWlhblRtU2k0NUo2d2tKNktxRS96VUdIODZ5NnJnbnBCL2o4Sm1VeGlUUE9yT281TDFSOVJGODZwaXhUbEx1ditwOExWZHhMRXBxblhSaUI0aUdQMWJIb0Z2bHRZSm5QZmpDY01yU2xJSmNVcVhEYlJFNTlIOHpJNXExdk1EWU4rcXArZlVuTTMxVk5nWEx5eDQwTE9LZnlQVDM3ajZwVWU0S3NWYUIvTUFTL0dtdGZ2NnhvU3JWK0E0Y1N6Q0dmaGdualpHSnVHT1FUYTZLYnduTlQxYXlzeW9UK2JZYWxaNG9YUEdKNTlzRDArc1RhN2M3bUFtY29Ka3NsMk9LT2pEZ1Q3MFJjNTFvZXJiSHIwd1lma1V2WndMNXgyMTkxcHA0dGdLZzlRaE9tTUpNY1BJV1dIUlJTTWpnVW4vbWxIN1o0VlgxbFdUSHdTY1V2SU1EczhQck9NTnRpUTNBZGliSUFLL280c0JuNHFnU0NvVURCWWlnUUdkM1FHWkNHSVQyZUN4eHJubGN1UDFDbUY1QmlKSkNLWEtaWVVJR0JUQk12WDZEMERoY1QzclRKRE82d2QvbnBvbDk1TFZzTmxXUi9qcVozUmRJUlY3ckFsaTdZOWlLSlZ1cEtqMFV6ditjaDR1T3paMVlodGxjdWh5WDV2WG0rM04vd0Q1UXl1Q3piMXV0dTYycDZIaCtnRVlVd2N4RVllSmJBbklKV0Rqc29WcDUyOXZZb0VvMDV6cElYVHpLaFMrRURtQUhqcmN5L1I5ZmN5VUVVSEpZZWlDeDZPSUJSNDdKVG9aM1hRQ1VUZ0QzbWlRY29ZblUyQ3JCbSIsIm1hYyI6IjgxZmFhYmJhMGM4NWViMjVhNDA3YzM0NmYzZWE1ZjdiMzhmOWRmOTJjNTIwNGFkN2ZiZTczN2JiNTM1ZmNlOGUiLCJ0YWciOiIifQ==',NULL,NULL,'2026-11-18 04:31:24','2026-11-18 04:31:24','2026-11-18 04:31:24');
/*!40000 ALTER TABLE `scholarship_applications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `scholarship_matches`
--

DROP TABLE IF EXISTS `scholarship_matches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `scholarship_matches` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `scholarship_application_id` bigint(20) unsigned NOT NULL,
  `scholarship_id` bigint(20) unsigned NOT NULL,
  `status` varchar(20) NOT NULL,
  `score` tinyint(3) unsigned NOT NULL,
  `checks` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`checks`)),
  `explanation` text DEFAULT NULL,
  `explanation_source` varchar(30) DEFAULT NULL,
  `explained_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sch_matches_app_sch_unique` (`scholarship_application_id`,`scholarship_id`),
  KEY `scholarship_matches_scholarship_id_foreign` (`scholarship_id`),
  KEY `scholarship_matches_status_index` (`status`),
  CONSTRAINT `scholarship_matches_scholarship_application_id_foreign` FOREIGN KEY (`scholarship_application_id`) REFERENCES `scholarship_applications` (`id`) ON DELETE CASCADE,
  CONSTRAINT `scholarship_matches_scholarship_id_foreign` FOREIGN KEY (`scholarship_id`) REFERENCES `scholarships` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `scholarship_matches`
--

LOCK TABLES `scholarship_matches` WRITE;
/*!40000 ALTER TABLE `scholarship_matches` DISABLE KEYS */;
INSERT INTO `scholarship_matches` VALUES
(12,2,1,'possible',75,'[{\"key\":\"dnsc\",\"label\":\"DNSC student (current or incoming)\",\"result\":\"pass\",\"detail\":\"Your BAKR account is for a DNSC student.\",\"required\":true},{\"key\":\"enrolled\",\"label\":\"Currently enrolled or enrolling\",\"result\":\"pass\",\"detail\":\"Your answer: Incoming first-year (not yet started).\",\"required\":true},{\"key\":\"gwa\",\"label\":\"Meets the academic (GWA) requirement\",\"result\":\"confirm\",\"detail\":\"Set by the DNSC Scholarship and Grants Unit. It is not published in the sources BAKR uses.\",\"required\":true}]',NULL,NULL,NULL,'2026-11-18 04:31:24','2026-11-18 04:31:24'),
(13,2,2,'not_eligible',44,'[{\"key\":\"dnsc\",\"label\":\"DNSC student (current or incoming)\",\"result\":\"pass\",\"detail\":\"Your BAKR account is for a DNSC student.\",\"required\":true},{\"key\":\"enrolled\",\"label\":\"Currently enrolled or enrolling\",\"result\":\"pass\",\"detail\":\"Your answer: Incoming first-year (not yet started).\",\"required\":true},{\"key\":\"talent\",\"label\":\"Has a skill in music, dance, arts, or sports\",\"result\":\"fail\",\"detail\":\"None of the qualifying options were selected.\",\"required\":true},{\"key\":\"awards\",\"label\":\"Has awards or achievements to show\",\"result\":\"unknown\",\"detail\":\"Answer this in your application so BAKR can check it.\",\"required\":false}]',NULL,NULL,NULL,'2026-11-18 04:31:24','2026-11-18 04:31:24'),
(14,2,3,'not_eligible',25,'[{\"key\":\"dnsc\",\"label\":\"DNSC student (current or incoming)\",\"result\":\"pass\",\"detail\":\"Your BAKR account is for a DNSC student.\",\"required\":true},{\"key\":\"enrolled\",\"label\":\"Currently enrolled\",\"result\":\"fail\",\"detail\":\"Your answer: Incoming first-year (not yet started).\",\"required\":true},{\"key\":\"leader\",\"label\":\"Student Supreme Council officer or Student Publication Editorial Board member\",\"result\":\"fail\",\"detail\":\"None of the qualifying options were selected.\",\"required\":true}]',NULL,NULL,NULL,'2026-11-18 04:31:24','2026-11-18 04:31:24'),
(15,2,4,'not_eligible',45,'[{\"key\":\"dnsc\",\"label\":\"DNSC student (current or incoming)\",\"result\":\"pass\",\"detail\":\"Your BAKR account is for a DNSC student.\",\"required\":true},{\"key\":\"enrolled\",\"label\":\"Currently enrolled or enrolling\",\"result\":\"pass\",\"detail\":\"Your answer: Incoming first-year (not yet started).\",\"required\":true},{\"key\":\"willing\",\"label\":\"Willing to work as a student assistant\",\"result\":\"fail\",\"detail\":\"Your answer: No.\",\"required\":true},{\"key\":\"need\",\"label\":\"Shows financial need (family income)\",\"result\":\"confirm\",\"detail\":\"The unit decides what counts as financial need. Bring proof of family income.\",\"required\":true}]',NULL,NULL,NULL,'2026-11-18 04:31:24','2026-11-18 04:31:24'),
(16,2,5,'possible',80,'[{\"key\":\"citizen\",\"label\":\"Filipino citizen\",\"result\":\"pass\",\"detail\":\"Your answer: Filipino citizen.\",\"required\":true},{\"key\":\"first_degree\",\"label\":\"Taking a first undergraduate degree (no previous bachelor\'s degree)\",\"result\":\"pass\",\"detail\":\"Your answer: No.\",\"required\":true},{\"key\":\"enrolled\",\"label\":\"Enrolled at DNSC (the call opens after the enrollment period)\",\"result\":\"pass\",\"detail\":\"Your answer: Incoming first-year (not yet started).\",\"required\":true},{\"key\":\"mrr\",\"label\":\"Within the Maximum Residency Rule (the normal length of the program plus one year)\",\"result\":\"na\",\"detail\":\"\",\"required\":true},{\"key\":\"no_other\",\"label\":\"Not receiving another national government-funded financial assistance (Free Higher Education and one-time grants such as AICS or SMART do not count, but TDP and SMART cannot be received in the same semester)\",\"result\":\"pass\",\"detail\":\"You do not receive a conflicting scholarship.\",\"required\":true},{\"key\":\"indigency\",\"label\":\"Can get a Certificate of Indigency from your barangay\",\"result\":\"confirm\",\"detail\":\"Ask the Punong Barangay where you live. If it cannot be secured, a Certificate of Tax Exemption or a Social Case Study from a registered social worker is accepted.\",\"required\":true},{\"key\":\"retention\",\"label\":\"Meets DNSC admission and retention policies\",\"result\":\"confirm\",\"detail\":\"DNSC and the CHED Regional Office check this when they validate your enrollment.\",\"required\":false},{\"key\":\"slots\",\"label\":\"A TDP call is open at DNSC this semester\",\"result\":\"confirm\",\"detail\":\"The UniFAST Secretariat issues the call after the enrollment period. Ask the DNSC Scholarship and Grants Unit.\",\"required\":false}]',NULL,NULL,NULL,'2026-11-18 04:31:24','2026-11-18 04:31:24'),
(17,2,6,'possible',97,'[{\"key\":\"citizen\",\"label\":\"Filipino citizen\",\"result\":\"pass\",\"detail\":\"Your answer: Filipino citizen.\",\"required\":true},{\"key\":\"enrolled\",\"label\":\"Enrolled at DNSC (non-students cannot apply)\",\"result\":\"pass\",\"detail\":\"Your answer: Incoming first-year (not yet started).\",\"required\":true},{\"key\":\"no_degree\",\"label\":\"No previous undergraduate degree (not in a second undergraduate program)\",\"result\":\"pass\",\"detail\":\"Your answer: No.\",\"required\":true},{\"key\":\"duration\",\"label\":\"Not overstaying in the program (within the normal years plus a 1-year grace)\",\"result\":\"na\",\"detail\":\"\",\"required\":true},{\"key\":\"no_other\",\"label\":\"Not already a TDP, CHED scholarship or other national government aid beneficiary (one-time grants such as AICS or SMART do not count)\",\"result\":\"pass\",\"detail\":\"You do not receive a conflicting scholarship.\",\"required\":true},{\"key\":\"priority\",\"label\":\"In a TES group: Listahanan or 4Ps household (first priority), dependent of a solo parent in the DSWD registry, or Indigenous Peoples household listed by NCIP\",\"result\":\"pass\",\"detail\":\"You selected: I am a child of a solo parent.\",\"required\":true},{\"key\":\"admission\",\"label\":\"Meets DNSC admission and retention policies\",\"result\":\"confirm\",\"detail\":\"DNSC checks this when it validates your enrollment.\",\"required\":false}]',NULL,NULL,NULL,'2026-11-18 04:31:24','2026-11-18 04:31:24'),
(18,2,7,'possible',74,'[{\"key\":\"citizen\",\"label\":\"Natural-born Filipino citizen\",\"result\":\"pass\",\"detail\":\"Your answer: Filipino citizen.\",\"required\":true},{\"key\":\"incoming\",\"label\":\"Graduating from Grade 12, or a Grade 12 graduate not yet enrolled in college\",\"result\":\"pass\",\"detail\":\"Your answer: Incoming first-year (not yet started).\",\"required\":true},{\"key\":\"strand\",\"label\":\"STEM strand graduate, or in the top 5% of a non-STEM graduating class\",\"result\":\"confirm\",\"detail\":\"Your principal certifies this on Form E (E1 for STEM, E2 for non-STEM top 5%; E3 homeschool; E4 ALS).\",\"required\":true},{\"key\":\"course\",\"label\":\"Enrolling in a DOST-SEI priority S&T program\",\"result\":\"pass\",\"detail\":\"Your program (Bachelor of Science in Information Technology) is on the provider\'s list.\",\"required\":true},{\"key\":\"no_units\",\"label\":\"Has not earned any post-secondary (college or vocational) units\",\"result\":\"pass\",\"detail\":\"Your answer: No.\",\"required\":true},{\"key\":\"exam\",\"label\":\"DOST-SEI Scholarship Examination taken or passed (selection is by this exam)\",\"result\":\"fail\",\"detail\":\"Your answer: I have not taken it.\",\"required\":false},{\"key\":\"ra7687\",\"label\":\"RA 7687 track only: family income within DOST\'s set values, and living in your municipality for at least 4 years\",\"result\":\"confirm\",\"detail\":\"The Merit track has no income or residency requirement. For RA 7687, DOST-SEI checks your proof of income and your barangay\'s certificate of residency.\",\"required\":false}]',NULL,NULL,NULL,'2026-11-18 04:31:24','2026-11-18 04:31:24'),
(19,2,8,'possible',93,'[{\"key\":\"citizen\",\"label\":\"Filipino citizen\",\"result\":\"pass\",\"detail\":\"Your answer: Filipino citizen.\",\"required\":true},{\"key\":\"first_year\",\"label\":\"Incoming or current first-year college student\",\"result\":\"pass\",\"detail\":\"Your answer: 1st year.\",\"required\":true},{\"key\":\"gwa\",\"label\":\"Grade 12 GWA of at least 93%\",\"result\":\"pass\",\"detail\":\"You entered 95.5%. The minimum is 93%.\",\"required\":true},{\"key\":\"income\",\"label\":\"Combined parent \\/ guardian gross income of \\u20b1500,000 or less\",\"result\":\"pass\",\"detail\":\"Your income range (Up to \\u20b1250,000) is within the limit.\",\"required\":true},{\"key\":\"no_other\",\"label\":\"Not already getting another national government-funded scholarship or grant, including TES or TDP (one-time grants do not count)\",\"result\":\"pass\",\"detail\":\"You do not receive a conflicting scholarship.\",\"required\":true},{\"key\":\"no_degree\",\"label\":\"Has not finished an undergraduate degree (no second-course takers)\",\"result\":\"pass\",\"detail\":\"Your answer: No.\",\"required\":true},{\"key\":\"no_credited_units\",\"label\":\"Not a transferee or shiftee with credited college units\",\"result\":\"pass\",\"detail\":\"Your answer: No.\",\"required\":true},{\"key\":\"ph_shs\",\"label\":\"Graduated from a senior high school in the Philippines\",\"result\":\"confirm\",\"detail\":\"Your Form 138 from a Philippine senior high school shows this.\",\"required\":true},{\"key\":\"priority\",\"label\":\"Enrolled in a CHED priority program (non-priority programs cannot apply)\",\"result\":\"confirm\",\"detail\":\"The program must be on the CHED priority list and have CHED recognition (a Certificate of Program Compliance for state colleges). Ask CHEDRO XI or the DNSC Scholarship and Grants Unit.\",\"required\":true}]',NULL,NULL,NULL,'2026-11-18 04:31:24','2026-11-18 04:31:24'),
(20,2,9,'possible',94,'[{\"key\":\"citizen\",\"label\":\"Filipino citizen (foreign or dual citizens cannot apply)\",\"result\":\"pass\",\"detail\":\"Your answer: Filipino citizen.\",\"required\":true},{\"key\":\"first_year\",\"label\":\"Incoming first-year college student (not repeating or re-entering first year)\",\"result\":\"pass\",\"detail\":\"Your answer: 1st year.\",\"required\":true},{\"key\":\"merit\",\"label\":\"Top 5 of the graduating class, or a Grade 12 GWA of at least 95%\",\"result\":\"pass\",\"detail\":\"Top 5 of the graduating class: yes.\",\"required\":true},{\"key\":\"no_units\",\"label\":\"No college units earned before (including transferees or shiftees with credited units)\",\"result\":\"pass\",\"detail\":\"Your answer: No.\",\"required\":true},{\"key\":\"no_degree\",\"label\":\"Has not completed an undergraduate degree\",\"result\":\"pass\",\"detail\":\"Your answer: No.\",\"required\":true},{\"key\":\"income\",\"label\":\"Combined parent \\/ guardian gross income of \\u20b12,000,000 or less\",\"result\":\"pass\",\"detail\":\"Your income range (Up to \\u20b1250,000) is within the limit.\",\"required\":true},{\"key\":\"no_other\",\"label\":\"Not already getting a national government-funded scholarship or grant (private, LGU and one-time local grants do not count)\",\"result\":\"pass\",\"detail\":\"You do not receive a conflicting scholarship.\",\"required\":true},{\"key\":\"no_discipline\",\"label\":\"No pending case or record of disciplinary action in school\",\"result\":\"pass\",\"detail\":\"Your answer: No.\",\"required\":true},{\"key\":\"program\",\"label\":\"Enrolling in a CHED priority program\",\"result\":\"confirm\",\"detail\":\"Priority domains: Agriculture and Fisheries; Creative Industries; Healthcare and Allied Programs; Engineering and Technology; Financial Services; Teacher Education; Digital Technology; Science and Mathematics. Ask CHEDRO XI or DNSC whether your program is on the current list.\",\"required\":true},{\"key\":\"equity\",\"label\":\"Special equity group (10 extra ranking points): Indigenous Peoples, PWD, solo parent or dependent, living in poverty, or first-generation student\",\"result\":\"pass\",\"detail\":\"You selected: I am a child of a solo parent.\",\"required\":false}]',NULL,NULL,NULL,'2026-11-18 04:31:24','2026-11-18 04:31:24'),
(21,2,10,'eligible',100,'[{\"key\":\"citizen\",\"label\":\"Filipino citizen\",\"result\":\"pass\",\"detail\":\"Your answer: Filipino citizen.\",\"required\":true},{\"key\":\"first_year\",\"label\":\"Incoming first-year college student\",\"result\":\"pass\",\"detail\":\"Your answer: 1st year.\",\"required\":true},{\"key\":\"gwa\",\"label\":\"Grade 12 grade of at least 90%\",\"result\":\"pass\",\"detail\":\"You entered 95.5%. The minimum is 90%.\",\"required\":true},{\"key\":\"none_other\",\"label\":\"No other scholarship or educational assistance\",\"result\":\"pass\",\"detail\":\"You do not receive a conflicting scholarship.\",\"required\":true},{\"key\":\"course\",\"label\":\"A 3- to 4-year Business, STEM (not medical) or Sustainability program\",\"result\":\"pass\",\"detail\":\"Your program (Bachelor of Science in Information Technology) is on the provider\'s list.\",\"required\":true},{\"key\":\"conduct\",\"label\":\"No disciplinary sanction in senior high school\",\"result\":\"pass\",\"detail\":\"Your answer: No.\",\"required\":true}]',NULL,NULL,NULL,'2026-11-18 04:31:24','2026-11-18 04:31:24'),
(22,2,11,'eligible',100,'[{\"key\":\"citizen\",\"label\":\"Filipino citizen\",\"result\":\"pass\",\"detail\":\"Your answer: Filipino citizen.\",\"required\":true},{\"key\":\"client\",\"label\":\"Cebuana Lhuillier client with a 24K Loyalty Card\",\"result\":\"pass\",\"detail\":\"Your answer: Yes, I am.\",\"required\":true},{\"key\":\"savings\",\"label\":\"Cebuana Lhuillier Microsavings account (on the foundation\'s earlier qualifications page; not in the current FAQ)\",\"result\":\"pass\",\"detail\":\"Your answer: Yes.\",\"required\":false},{\"key\":\"enrolled\",\"label\":\"Enrolled, or enrolling, in a public state university or college (DNSC is a state college); any year level\",\"result\":\"pass\",\"detail\":\"Your answer: Incoming first-year (not yet started).\",\"required\":true},{\"key\":\"course\",\"label\":\"Supported course (Accountancy \\/ Finance, Business, Hospitality, Electrical \\/ Civil \\/ Mechanical \\/ Industrial Engineering, Architecture, Psychology, IT); no other courses are accepted\",\"result\":\"pass\",\"detail\":\"Your program (Bachelor of Science in Information Technology) is on the provider\'s list.\",\"required\":true},{\"key\":\"income\",\"label\":\"Total annual family income of \\u20b1350,000 or less\",\"result\":\"pass\",\"detail\":\"Your income range (Up to \\u20b1250,000) is within the limit.\",\"required\":true},{\"key\":\"gwa\",\"label\":\"GWA of at least 85% from the last semester or school (freshmen: senior high school grades)\",\"result\":\"pass\",\"detail\":\"You entered 95.5%. The minimum is 85%.\",\"required\":true},{\"key\":\"no_fail\",\"label\":\"No failing mark in any subject\",\"result\":\"pass\",\"detail\":\"Your answer: No.\",\"required\":true}]',NULL,NULL,NULL,'2026-11-18 04:31:24','2026-11-18 04:31:24');
/*!40000 ALTER TABLE `scholarship_matches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `scholarship_revisions`
--

DROP TABLE IF EXISTS `scholarship_revisions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `scholarship_revisions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `scholarship_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `before` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`before`)),
  `after` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`after`)),
  `rules_changed` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `scholarship_revisions_scholarship_id_foreign` (`scholarship_id`),
  KEY `scholarship_revisions_user_id_foreign` (`user_id`),
  CONSTRAINT `scholarship_revisions_scholarship_id_foreign` FOREIGN KEY (`scholarship_id`) REFERENCES `scholarships` (`id`) ON DELETE CASCADE,
  CONSTRAINT `scholarship_revisions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `scholarship_revisions`
--

LOCK TABLES `scholarship_revisions` WRITE;
/*!40000 ALTER TABLE `scholarship_revisions` DISABLE KEYS */;
/*!40000 ALTER TABLE `scholarship_revisions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `scholarships`
--

DROP TABLE IF EXISTS `scholarships`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `scholarships` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(60) NOT NULL,
  `name` varchar(255) NOT NULL,
  `sector` varchar(20) NOT NULL,
  `provider` varchar(255) DEFAULT NULL,
  `official_url` varchar(255) DEFAULT NULL,
  `overview` text NOT NULL,
  `benefits` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`benefits`)),
  `grants` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`grants`)),
  `documents` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`documents`)),
  `renewal_requirements` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`renewal_requirements`)),
  `application_route` text DEFAULT NULL,
  `window_note` varchar(255) DEFAULT NULL,
  `application_opens_on` date DEFAULT NULL,
  `application_closes_on` date DEFAULT NULL,
  `results_on` date DEFAULT NULL,
  `source_note` text DEFAULT NULL,
  `rules` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`rules`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  `verified_on` date DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `scholarships_slug_unique` (`slug`),
  KEY `scholarships_updated_by_foreign` (`updated_by`),
  CONSTRAINT `scholarships_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `scholarships`
--

LOCK TABLES `scholarships` WRITE;
/*!40000 ALTER TABLE `scholarships` DISABLE KEYS */;
INSERT INTO `scholarships` VALUES
(1,'dnsc-academic','DNSC Academic Scholarship','internal','DNSC Scholarship and Grants Unit','https://www.facebook.com/DNSCScholarshipAndFinancialGrants','Scholarship assistance for DNSC students who meet the institution\'s academic excellence requirements.','[\"Set by the DNSC Scholarship and Grants Unit\"]',NULL,'[\"Latest Certificate of Registration (COR)\",\"Latest grades \\/ academic record\",\"Student identification \\/ school ID\",\"Other documents required by the DNSC Scholarship and Grants Unit\"]',NULL,'Through the DNSC Scholarship and Grants Unit.',NULL,NULL,NULL,NULL,'Requirements from the BAKR scholarships page. The official Facebook page could not be read by BAKR, so the GWA cut-off is not known.','[{\"key\":\"dnsc\",\"label\":\"DNSC student (current or incoming)\",\"type\":\"eq\",\"fact\":\"dnsc_student\",\"value\":\"yes\",\"required\":true,\"weight\":2,\"src\":\"page\"},{\"key\":\"enrolled\",\"label\":\"Currently enrolled or enrolling\",\"type\":\"in\",\"fact\":\"enrollment_status\",\"value\":[\"currently_enrolled\",\"incoming_first_year\"],\"required\":true,\"weight\":1,\"src\":\"page\"},{\"key\":\"gwa\",\"label\":\"Meets the academic (GWA) requirement\",\"type\":\"confirm\",\"fact\":\"last_gwa_percent\",\"required\":true,\"weight\":3,\"src\":\"page\",\"confirm_note\":\"Set by the DNSC Scholarship and Grants Unit. It is not published in the sources BAKR uses.\",\"min_percent\":null}]',1,1,'2026-11-07',NULL,'2026-11-17 06:57:22','2026-11-17 06:57:22'),
(2,'dnsc-gia-a','DNSC Grant-in-Aid (GIA) – Category A','internal','DNSC Scholarship and Grants Unit','https://www.facebook.com/DNSCScholarshipAndFinancialGrants','Financial assistance for students with exceptional skills in music, dance, arts, and sports.','[\"Set by the DNSC Scholarship and Grants Unit\"]',NULL,'[\"Latest Certificate of Registration (COR)\",\"Latest grades \\/ academic record\",\"Student identification \\/ school ID\",\"Proof of talent, awards, or achievements\",\"Other documents required by the DNSC Scholarship and Grants Unit\"]',NULL,'Through the DNSC Scholarship and Grants Unit.',NULL,NULL,NULL,NULL,'Requirements from the BAKR scholarships page.','[{\"key\":\"dnsc\",\"label\":\"DNSC student (current or incoming)\",\"type\":\"eq\",\"fact\":\"dnsc_student\",\"value\":\"yes\",\"required\":true,\"weight\":2,\"src\":\"page\"},{\"key\":\"enrolled\",\"label\":\"Currently enrolled or enrolling\",\"type\":\"in\",\"fact\":\"enrollment_status\",\"value\":[\"currently_enrolled\",\"incoming_first_year\"],\"required\":true,\"weight\":1,\"src\":\"page\"},{\"key\":\"talent\",\"label\":\"Has a skill in music, dance, arts, or sports\",\"type\":\"contains_any\",\"fact\":\"talent_categories\",\"value\":[\"music\",\"dance\",\"arts\",\"sports\"],\"required\":true,\"weight\":4,\"src\":\"page\"},{\"key\":\"awards\",\"label\":\"Has awards or achievements to show\",\"type\":\"eq\",\"fact\":\"has_awards\",\"value\":\"yes\",\"required\":false,\"weight\":2,\"src\":\"page\"}]',1,2,'2026-11-07',NULL,'2026-11-17 06:57:22','2026-11-17 06:57:22'),
(3,'dnsc-gia-b','DNSC Grant-in-Aid (GIA) – Category B','internal','DNSC Scholarship and Grants Unit','https://www.facebook.com/DNSCScholarshipAndFinancialGrants','Financial assistance intended for qualified Student Supreme Council officers and Student Publication Editorial Board members.','[\"Set by the DNSC Scholarship and Grants Unit\"]',NULL,'[\"Latest Certificate of Registration (COR)\",\"Latest grades \\/ academic record\",\"Student identification \\/ school ID\",\"Proof of student leadership position or appointment\",\"Proof of Student Publication membership, if applicable\",\"Other documents required by the DNSC Scholarship and Grants Unit\"]',NULL,'Through the DNSC Scholarship and Grants Unit.',NULL,NULL,NULL,NULL,'Requirements from the BAKR scholarships page.','[{\"key\":\"dnsc\",\"label\":\"DNSC student (current or incoming)\",\"type\":\"eq\",\"fact\":\"dnsc_student\",\"value\":\"yes\",\"required\":true,\"weight\":2,\"src\":\"page\"},{\"key\":\"enrolled\",\"label\":\"Currently enrolled\",\"type\":\"eq\",\"fact\":\"enrollment_status\",\"value\":\"currently_enrolled\",\"required\":true,\"weight\":1,\"src\":\"page\"},{\"key\":\"leader\",\"label\":\"Student Supreme Council officer or Student Publication Editorial Board member\",\"type\":\"contains_any\",\"fact\":\"leadership_roles\",\"value\":[\"ssc_officer\",\"publication_board\"],\"required\":true,\"weight\":5,\"src\":\"page\"}]',1,3,'2026-11-07',NULL,'2026-11-17 06:57:22','2026-11-17 06:57:22'),
(4,'dnsc-sa-c','DNSC Student Assistant (SA) – Category C','internal','DNSC Scholarship and Grants Unit','https://www.facebook.com/DNSCScholarshipAndFinancialGrants','Assistance for students from financially disadvantaged families who are willing to perform approved institutional support services.','[\"Set by the DNSC Scholarship and Grants Unit\"]',NULL,'[\"Latest Certificate of Registration (COR)\",\"Latest grades \\/ academic record\",\"Student identification \\/ school ID\",\"Proof of family income \\/ financial need\",\"Other documents required by the DNSC Scholarship and Grants Unit\"]',NULL,'Through the DNSC Scholarship and Grants Unit.',NULL,NULL,NULL,NULL,'Requirements from the BAKR scholarships page. No income limit is published, so the unit decides financial need.','[{\"key\":\"dnsc\",\"label\":\"DNSC student (current or incoming)\",\"type\":\"eq\",\"fact\":\"dnsc_student\",\"value\":\"yes\",\"required\":true,\"weight\":2,\"src\":\"page\"},{\"key\":\"enrolled\",\"label\":\"Currently enrolled or enrolling\",\"type\":\"in\",\"fact\":\"enrollment_status\",\"value\":[\"currently_enrolled\",\"incoming_first_year\"],\"required\":true,\"weight\":1,\"src\":\"page\"},{\"key\":\"willing\",\"label\":\"Willing to work as a student assistant\",\"type\":\"eq\",\"fact\":\"willing_student_assistant\",\"value\":\"yes\",\"required\":true,\"weight\":4,\"src\":\"page\"},{\"key\":\"need\",\"label\":\"Shows financial need (family income)\",\"type\":\"confirm\",\"fact\":\"income_max\",\"required\":true,\"weight\":3,\"src\":\"page\",\"confirm_note\":\"The unit decides what counts as financial need. Bring proof of family income.\"}]',1,4,'2026-11-07',NULL,'2026-11-17 06:57:22','2026-11-17 06:57:22'),
(5,'ched-tdp','CHED Tulong Dunong Program (TDP)','government','CHED / UniFAST','https://unifast.gov.ph/','A CHED-UniFAST grant for Filipino students taking their first undergraduate degree in a state, local or private college that is in the CHED Registry and has an agreement (MOA) with CHED-UniFAST. Qualifying does not guarantee a grant: CHED cross-checks all applicants and releases the final list of qualified grantees.','[\"\\u20b17,500 per semester (\\u20b115,000 per academic year), for new and continuing grantees\",\"The amount may be increased if the UniFAST Board approves\",\"Continuing grantees do not re-apply; they keep the grant up to the Maximum Residency Rule\",\"Paid by direct credit to your account, by check, or through DNSC, which must release it within 15 working days of receiving the funds\"]','[{\"key\":\"regular\",\"label\":\"TDP grant\",\"amount\":7500,\"per\":\"semester\"}]','[\"Scanned TDP Application Form\",\"Certificate of Registration \\/ Enrollment (COR \\/ COE), PDF copy\",\"Certificate of Indigency issued by the Punong Barangay where you live (if it cannot be secured: Certificate of Tax Exemption, or a Social Case Study from a registered social worker)\"]','[\"Certificate of Registration \\/ Enrollment (COR \\/ COE), PDF copy, for the current semester (or your name on DNSC\'s certified Enrollment List)\",\"If returning after a one-term Leave of Absence: the LOA you submitted to DNSC (PDF copy)\",\"If you transferred from another school: COR \\/ COE of the previous and the current semester (PDF copies)\",\"If you submit directly to CHEDRO: a school ID or government-issued ID with your specimen signature (PDF copy)\"]','After DNSC\'s enrollment period, the UniFAST Secretariat issues a call. Submit the application and documents through the partner implementer (DNSC) or directly to the CHED Regional Office (CHEDRO). CHEDRO uploads applications to the RC Portal, cross-checks for duplicate grants, and releases the list of qualified applicants to DNSC.','The call opens after DNSC\'s enrollment period each semester. These guidelines apply from the 1st Semester of AY 2026-2027.',NULL,NULL,NULL,'From the Revised TDP Guidelines of 2026 (UniFAST Board Resolution No. 2026-012, effective 1st Semester AY 2026-2027). These guidelines set no income limit; financial need is shown with a Certificate of Indigency.','[{\"key\":\"citizen\",\"label\":\"Filipino citizen\",\"type\":\"eq\",\"fact\":\"citizenship\",\"value\":\"filipino\",\"required\":true,\"weight\":2,\"src\":\"web\"},{\"key\":\"first_degree\",\"label\":\"Taking a first undergraduate degree (no previous bachelor\'s degree)\",\"type\":\"eq\",\"fact\":\"previous_degree\",\"value\":\"no\",\"required\":true,\"weight\":3,\"src\":\"web\"},{\"key\":\"enrolled\",\"label\":\"Enrolled at DNSC (the call opens after the enrollment period)\",\"type\":\"in\",\"fact\":\"enrollment_status\",\"value\":[\"currently_enrolled\",\"incoming_first_year\"],\"required\":true,\"weight\":1,\"src\":\"web\"},{\"key\":\"mrr\",\"label\":\"Within the Maximum Residency Rule (the normal length of the program plus one year)\",\"type\":\"eq\",\"fact\":\"within_years\",\"value\":\"yes\",\"required\":true,\"weight\":2,\"src\":\"web\",\"skip_if\":{\"enrollment_status\":\"incoming_first_year\"}},{\"key\":\"no_other\",\"label\":\"Not receiving another national government-funded financial assistance (Free Higher Education and one-time grants such as AICS or SMART do not count, but TDP and SMART cannot be received in the same semester)\",\"type\":\"contains_none\",\"fact\":\"existing_assistance\",\"value\":[\"tes_tdp_ched\",\"other_government\"],\"required\":true,\"weight\":3,\"src\":\"web\"},{\"key\":\"indigency\",\"label\":\"Can get a Certificate of Indigency from your barangay\",\"type\":\"confirm\",\"fact\":\"income_max\",\"required\":true,\"weight\":4,\"src\":\"web\",\"confirm_note\":\"Ask the Punong Barangay where you live. If it cannot be secured, a Certificate of Tax Exemption or a Social Case Study from a registered social worker is accepted.\"},{\"key\":\"retention\",\"label\":\"Meets DNSC admission and retention policies\",\"type\":\"confirm\",\"fact\":\"dnsc_student\",\"required\":false,\"weight\":1,\"src\":\"web\",\"confirm_note\":\"DNSC and the CHED Regional Office check this when they validate your enrollment.\"},{\"key\":\"slots\",\"label\":\"A TDP call is open at DNSC this semester\",\"type\":\"confirm\",\"fact\":\"dnsc_student\",\"required\":false,\"weight\":1,\"src\":\"web\",\"confirm_note\":\"The UniFAST Secretariat issues the call after the enrollment period. Ask the DNSC Scholarship and Grants Unit.\"}]',1,5,'2026-11-15',NULL,'2026-11-17 06:57:22','2026-11-17 06:57:22'),
(6,'ched-tes','CHED Tertiary Education Subsidy (TES)','government','CHED / UniFAST','https://unifast.gov.ph/tes.html','A CHED-UniFAST subsidy for Filipino students in state, local and private colleges in the CHED Registry with a UniFAST agreement. New slots go first to students from Listahanan (or 4Ps) households, then to dependents of solo parents and Indigenous Peoples, each ranked by household per capita income and subject to available funds.','[\"Listahanan \\/ 4Ps households at state colleges like DNSC: \\u20b110,000 per semester (\\u20b120,000 per academic year)\",\"Dependents of solo parents and Indigenous Peoples (second priority): \\u20b15,000 per semester (\\u20b110,000 per academic year)\",\"Persons with disability (TES-3A): an additional \\u20b15,000 per semester (\\u20b110,000 per academic year)\",\"Graduates of programs with a licensure exam (TES-3B): one-time reimbursement of up to \\u20b18,000\",\"Students at risk of dropping out (SARDO): a one-time \\u20b110,000, subject to DNSC\'s evaluation\",\"Given for the standard length of your program plus one year, if you stay eligible; the amount is not reduced while you remain eligible, subject to available funds\",\"Paid by direct credit to your account, by check, or through DNSC, which must release it within 15 working days of receiving the funds\"]','[{\"key\":\"listahanan\",\"label\":\"Listahanan \\/ 4Ps household\",\"amount\":10000,\"per\":\"semester\"},{\"key\":\"second_priority\",\"label\":\"Solo-parent or IP dependent\",\"amount\":5000,\"per\":\"semester\"}]','[\"Certificate of Registration \\/ Enrollment (COR \\/ COE)\",\"School ID or any government-issued ID with your specimen signature (if you submit directly to CHEDRO)\",\"PWD ID issued by the PDAO of your city or municipality, if applicable\"]','[\"Certificate of Registration \\/ Enrollment (COR \\/ COE), PDF copy, for the current semester (or your name on DNSC\\\\x27s certified Enrollment List)\",\"If returning after a one-term Leave of Absence: the LOA you submitted to DNSC (PDF copy)\",\"If you transferred from another school: COR \\/ COE of the previous and the current semester (PDF copies)\",\"If you submit directly to CHEDRO: a school ID or government-issued ID with your specimen signature (PDF copy)\"]','You do not apply on your own: after the UniFAST call, DNSC submits its list of enrolled student applicants to the UniFAST Portal. CHEDRO and the UniFAST Regional Coordinators validate the documents and issue the Master List of grantees. If DNSC cannot submit your documents, you may submit them directly to CHEDRO XI.','Applications follow the UniFAST call each academic year. These guidelines apply from the 1st Semester of AY 2026-2027.',NULL,NULL,NULL,'From the Revised TES Guidelines of 2026 and unifast.gov.ph. New slots are only for Listahanan / 4Ps households, dependents of solo parents and Indigenous Peoples, in that order, while funds last.','[{\"key\":\"citizen\",\"label\":\"Filipino citizen\",\"type\":\"eq\",\"fact\":\"citizenship\",\"value\":\"filipino\",\"required\":true,\"weight\":2,\"src\":\"web\"},{\"key\":\"enrolled\",\"label\":\"Enrolled at DNSC (non-students cannot apply)\",\"type\":\"in\",\"fact\":\"enrollment_status\",\"value\":[\"currently_enrolled\",\"incoming_first_year\"],\"required\":true,\"weight\":1,\"src\":\"web\"},{\"key\":\"no_degree\",\"label\":\"No previous undergraduate degree (not in a second undergraduate program)\",\"type\":\"eq\",\"fact\":\"previous_degree\",\"value\":\"no\",\"required\":true,\"weight\":3,\"src\":\"web\"},{\"key\":\"duration\",\"label\":\"Not overstaying in the program (within the normal years plus a 1-year grace)\",\"type\":\"eq\",\"fact\":\"within_years\",\"value\":\"yes\",\"required\":true,\"weight\":2,\"src\":\"web\",\"skip_if\":{\"enrollment_status\":\"incoming_first_year\"}},{\"key\":\"no_other\",\"label\":\"Not already a TDP, CHED scholarship or other national government aid beneficiary (one-time grants such as AICS or SMART do not count)\",\"type\":\"contains_none\",\"fact\":\"existing_assistance\",\"value\":[\"tes_tdp_ched\",\"other_government\"],\"required\":true,\"weight\":3,\"src\":\"web\"},{\"key\":\"priority\",\"label\":\"In a TES group: Listahanan or 4Ps household (first priority), dependent of a solo parent in the DSWD registry, or Indigenous Peoples household listed by NCIP\",\"type\":\"contains_any\",\"fact\":\"special_groups\",\"value\":[\"listahanan\",\"four_ps\",\"solo_parent\",\"indigenous_people\"],\"required\":true,\"weight\":5,\"src\":\"web\"},{\"key\":\"admission\",\"label\":\"Meets DNSC admission and retention policies\",\"type\":\"confirm\",\"fact\":\"dnsc_student\",\"required\":false,\"weight\":1,\"src\":\"web\",\"confirm_note\":\"DNSC checks this when it validates your enrollment.\"}]',1,6,'2026-11-15',NULL,'2026-11-17 06:57:22','2026-11-17 06:57:22'),
(7,'dost-sei','DOST-SEI Undergraduate Scholarship','government','DOST – Science Education Institute','https://www.science-scholarships.ph/','A DOST-SEI scholarship for talented Filipino students entering priority Science and Technology programs. Two tracks: the Merit Scholarship (formerly NSDB / NSTA) for students with high aptitude in science and mathematics, and the RA 7687 Scholarship for students whose families\' socio-economic status does not exceed DOST\'s set values. Scholars may study at state colleges like DNSC.','[\"Tuition and other school fees: actual fees up to \\u20b140,000 a year (for scholars in private schools)\",\"Learning materials and \\/ or connectivity allowance: \\u20b110,000 a year\",\"Monthly living allowance: \\u20b18,000 a month\",\"Clothing allowance: \\u20b11,000 (first semester of first year only)\",\"Transportation: one economy-class round trip a year, if you study outside your home province\",\"Group accident and health insurance\",\"Thesis allowance: \\u20b110,000\",\"Research dissemination grant: up to \\u20b150,000 (applied for by the scholar, subject to approval)\",\"Graduation allowance: \\u20b11,000\",\"Mid-year term, if your curriculum requires it: fees up to \\u20b110,000, \\u20b12,000 learning materials, and \\u20b18,000 a month\"]','[{\"key\":\"regular\",\"label\":\"DOST-SEI scholar\",\"amount\":8000,\"per\":\"month\"}]','[\"Form C \\u2013 Certificate of Good Moral Character\",\"Form D \\u2013 Certificate of Good Health\",\"Form E \\u2013 Principal\'s Certification: E1 Grade 12 STEM strand; E2 Grade 12 non-STEM strand in the upper 5% of the class; E3 homeschool; E4 Alternative Learning System\",\"Form F \\u2013 Certificate of Residency\",\"Form G \\u2013 Parent\'s Certification that you have no pending immigration application to another country\",\"Form H \\u2013 DOST-SEI Scholarship Examination \\/ Award\",\"Form I \\u2013 Applicant\'s Certification of No Post-Secondary Units\",\"Form J \\u2013 Signed Declaration of Applicant and Parent \\/ Legal Guardian\",\"Recent passport-size picture (4.5 cm \\u00d7 3.5 cm)\",\"Birth certificate\",\"One proof of income of your parents, guardian or spouse: 2024 Income Tax Return; BIR Form 1701; OFW employment contract; BIR Certificate of Exemption from Filing of ITR; Certificate of Indigency; Certificate of Employment with Compensation; or proof of pension\",\"Notarized Affidavit of Guardianship, if applicable\",\"Marriage certificate, if married\"]',NULL,'Apply online through the DOST-SEI scholarship site (science-scholarships.ph), where the forms are posted, then take the DOST-SEI Scholarship Examination. Qualifiers enroll in a priority S&T program at the University of the Philippines, a state college such as DNSC, or a private school that is a CHED Center of Excellence / Development or has FAAP Level III accreditation.','For Grade 12 students graduating in SY 2025–2026, and earlier Grade 12 graduates not yet enrolled in college. Watch science-scholarships.ph for the call and exam schedule.',NULL,NULL,NULL,'From the DOST-SEI Undergraduate Scholarship brochure and the DOST-SEI priority S&T programs list. Students who applied before and did not qualify, or qualified but did not avail, may apply again.','[{\"key\":\"citizen\",\"label\":\"Natural-born Filipino citizen\",\"type\":\"eq\",\"fact\":\"citizenship\",\"value\":\"filipino\",\"required\":true,\"weight\":2,\"src\":\"web\"},{\"key\":\"incoming\",\"label\":\"Graduating from Grade 12, or a Grade 12 graduate not yet enrolled in college\",\"type\":\"eq\",\"fact\":\"enrollment_status\",\"value\":\"incoming_first_year\",\"required\":true,\"weight\":3,\"src\":\"web\"},{\"key\":\"strand\",\"label\":\"STEM strand graduate, or in the top 5% of a non-STEM graduating class\",\"type\":\"confirm\",\"fact\":\"shs_gwa_percent\",\"required\":true,\"weight\":3,\"src\":\"web\",\"confirm_note\":\"Your principal certifies this on Form E (E1 for STEM, E2 for non-STEM top 5%; E3 homeschool; E4 ALS).\"},{\"key\":\"course\",\"label\":\"Enrolling in a DOST-SEI priority S&T program\",\"type\":\"program_in\",\"fact\":\"program_name\",\"required\":true,\"weight\":5,\"src\":\"web\",\"pass\":[\"Bachelor of Science in Agro-Forestry\",\"Bachelor of Science in Fisheries and Aquatic Sciences\",\"Bachelor of Science in Food Technology\",\"Bachelor of Science in Information Systems\",\"Bachelor of Science in Information Technology\",\"Bachelor of Science in Marine Biology\",\"Bachelor of Secondary Education - Mathematics\",\"Bachelor of Secondary Education - Science\"],\"maybe\":[\"Bachelor of Technology and Livelihood Education\"],\"not\":[\"Bachelor of Arts in Communication\",\"Bachelor of Physical Education\",\"Bachelor of Public Administration\",\"Bachelor of Science in Criminology\",\"Bachelor of Science in Disaster Resilience and Management\",\"Bachelor of Science in Entrepreneurship\",\"Bachelor of Science in Social Work\",\"Bachelor of Science in Tourism Management\",\"Bachelor of Secondary Education - English\"]},{\"key\":\"no_units\",\"label\":\"Has not earned any post-secondary (college or vocational) units\",\"type\":\"eq\",\"fact\":\"previous_units\",\"value\":\"no\",\"required\":true,\"weight\":2,\"src\":\"web\"},{\"key\":\"exam\",\"label\":\"DOST-SEI Scholarship Examination taken or passed (selection is by this exam)\",\"type\":\"in\",\"fact\":\"dost_exam\",\"value\":[\"taken_waiting\",\"passed\"],\"required\":false,\"weight\":3,\"src\":\"web\"},{\"key\":\"ra7687\",\"label\":\"RA 7687 track only: family income within DOST\'s set values, and living in your municipality for at least 4 years\",\"type\":\"confirm\",\"fact\":\"income_max\",\"required\":false,\"weight\":1,\"src\":\"web\",\"confirm_note\":\"The Merit track has no income or residency requirement. For RA 7687, DOST-SEI checks your proof of income and your barangay\'s certificate of residency.\"}]',1,7,'2026-11-15',NULL,'2026-11-17 06:57:22','2026-11-17 06:57:22'),
(8,'ched-cmsp','CHED Merit Scholarship Program (CMSP)','government','CHED','https://ched.gov.ph/','A competitive CHED scholarship for academically talented and deserving incoming or current first-year college students who graduated from a Philippine senior high school with a Grade 12 GWA of at least 93%. CHED Regional Offices evaluate and rank applicants; meeting the requirements does not guarantee a slot.','[\"State colleges like DNSC: tuition and other school fees under Free Higher Education\",\"Full scholarship (Full-SSP): \\u20b140,000 per semester (\\u20b180,000 a year) = stipend \\u20b135,000 (\\u20b17,000 a month \\u00d7 5 months) + book \\/ connectivity allowance \\u20b15,000\",\"Half scholarship (Half-SSP): \\u20b120,000 per semester (\\u20b140,000 a year) = stipend \\u20b117,500 (\\u20b13,500 a month \\u00d7 5 months) + book \\/ connectivity allowance \\u20b12,500\",\"Paid every semester until you finish the degree program, if you keep the scholarship\",\"Paid by CHEDRO to your bank account or by check, or through DNSC if it has an agreement with CHEDRO. For the first payment: certified COR \\/ COE, college ID or government ID with your signature, and a copy of an active Landbank ATM card\"]','[{\"key\":\"full\",\"label\":\"Full scholarship (Full-SSP)\",\"amount\":40000,\"per\":\"semester\"},{\"key\":\"half\",\"label\":\"Half scholarship (Half-SSP)\",\"amount\":20000,\"per\":\"semester\"}]','[\"Accomplished application form (Annex A)\",\"Birth certificate issued by the NSO or PSA\",\"Certified true copy of Form 138 (Learner\'s Progress Report Card \\/ SF9), signed by the registrar of your senior high school\",\"Any one: latest ITR of parents or guardian; BIR Certificate of Tax Exemption \\/ Non-Filer; certified latest contract or proof of income (children of OFWs and seafarers); or Social Case Study Report from the C\\/MSWDO\",\"If applicable: PWD ID or PDAO certification; Solo Parent ID; Senior Citizen ID; DHSUD or C\\/MSWDO certification (underprivileged and homeless family); C\\/MSWDO Social Case Study (Magna Carta for the Poor \\/ first-generation students); NCIP certification (Indigenous Peoples)\",\"Notarized Certificate of Guardianship, if applicable\"]','[\"Certified true copy of the Certificate of Registration \\/ Enrollment (COR \\/ COE)\",\"Certified true copy of grades showing the GWA of the previous semester\",\"HEI certification that you do not receive another national government-funded scholarship (every start of the academic year)\"]','Apply to the CHED Regional Office (CHEDRO XI for Davao) with the Annex A form and documents. CHEDRO evaluates and ranks applicants, tells you the result by SMS or e-mail, and issues a Notice of Award (NOA) to qualified applicants or through DNSC.','CHED announces one call per academic year for incoming and current first-year students. Watch CHED and CHEDRO XI announcements.',NULL,NULL,NULL,'From CHED Memorandum Order No. 13, s. 2025 (Revised Policies and Guidelines for the CMSP). To keep the scholarship: GWA of at least 85% (full) or 80% (half), a regular load, a CHED priority program, finishing on time, and good moral character. You need CHEDRO\\x27s written approval before transferring, shifting, or taking a leave of absence (allowed once, up to one academic year, with no benefits while on leave).','[{\"key\":\"citizen\",\"label\":\"Filipino citizen\",\"type\":\"eq\",\"fact\":\"citizenship\",\"value\":\"filipino\",\"required\":true,\"weight\":2,\"src\":\"web\"},{\"key\":\"first_year\",\"label\":\"Incoming or current first-year college student\",\"type\":\"eq\",\"fact\":\"year_level\",\"value\":1,\"required\":true,\"weight\":3,\"src\":\"web\"},{\"key\":\"gwa\",\"label\":\"Grade 12 GWA of at least 93%\",\"type\":\"gte\",\"fact\":\"shs_gwa_percent\",\"value\":93,\"required\":true,\"weight\":5,\"src\":\"web\"},{\"key\":\"income\",\"label\":\"Combined parent \\/ guardian gross income of \\u20b1500,000 or less\",\"type\":\"income_lte\",\"fact\":\"income\",\"value\":500000,\"required\":true,\"weight\":4,\"src\":\"web\"},{\"key\":\"no_other\",\"label\":\"Not already getting another national government-funded scholarship or grant, including TES or TDP (one-time grants do not count)\",\"type\":\"contains_none\",\"fact\":\"existing_assistance\",\"value\":[\"tes_tdp_ched\",\"other_government\"],\"required\":true,\"weight\":3,\"src\":\"web\"},{\"key\":\"no_degree\",\"label\":\"Has not finished an undergraduate degree (no second-course takers)\",\"type\":\"eq\",\"fact\":\"previous_degree\",\"value\":\"no\",\"required\":true,\"weight\":3,\"src\":\"web\"},{\"key\":\"no_credited_units\",\"label\":\"Not a transferee or shiftee with credited college units\",\"type\":\"eq\",\"fact\":\"previous_units\",\"value\":\"no\",\"required\":true,\"weight\":3,\"src\":\"web\"},{\"key\":\"ph_shs\",\"label\":\"Graduated from a senior high school in the Philippines\",\"type\":\"confirm\",\"fact\":\"shs_gwa_percent\",\"required\":true,\"weight\":1,\"src\":\"web\",\"confirm_note\":\"Your Form 138 from a Philippine senior high school shows this.\"},{\"key\":\"priority\",\"label\":\"Enrolled in a CHED priority program (non-priority programs cannot apply)\",\"type\":\"confirm\",\"fact\":\"program_field\",\"required\":true,\"weight\":3,\"src\":\"web\",\"confirm_note\":\"The program must be on the CHED priority list and have CHED recognition (a Certificate of Program Compliance for state colleges). Ask CHEDRO XI or the DNSC Scholarship and Grants Unit.\"}]',1,8,'2026-11-15',NULL,'2026-11-17 06:57:22','2026-11-17 06:57:22'),
(9,'bpmsp','Bagong Pilipinas Merit Scholarship Program (BPMSP)','government','CHED','https://bpms.ched.gov.ph/','A merit-based CHED scholarship for academically talented Filipino senior high school graduates whose parents or guardians earn ₱2,000,000 a year or less. Higher Education Track: incoming first-year students in the Top 5 of their class or with a GWA of at least 95%, enrolling in a CHED priority program. CHEDRO ranks applicants by GWA (70%) and family income (30%), plus 10 points for special equity groups.','[\"State colleges like DNSC: tuition and other school fees under Free Higher Education\",\"Stipend and allowance: \\u20b145,000 per academic year (\\u20b122,500 per semester)\",\"Academic Excellence Incentive: \\u20b118,000 per term for the top 15 scholars per region\",\"Until you finish the degree program, as long as you pass all subjects every semester\",\"Amounts may be adjusted depending on available funds and Commission En Banc approval\"]','[{\"key\":\"regular\",\"label\":\"BPMSP scholar\",\"amount\":22500,\"per\":\"semester\"}]','[\"Application form, filled in on the official BPMSP online portal (Annex A for applicants from GIDCAs)\",\"PSA birth certificate\",\"Certified true copy of Form 138 \\/ SF9, signed by your senior high school registrar\",\"Proof of income, any one: BIR Certificate of Tax Exemption \\/ Non-Filer; DSWD or C\\/MSWDO certificate for 4Ps beneficiaries; latest ITR (BIR Form 1701) or BIR Form 2316 of both parents or guardians, or of the applicant; or certified latest contract or proof of income (children of OFWs and seafarers)\",\"College admission slip or proof of admission\",\"Parent \\/ Legal Guardian Consent Form (Annex L), if applicable\",\"Certified certificate from your senior high school that you were in the Top 5 of your graduating class, if applicable\",\"Proof of a special equity group, if applicable: PWD ID or PDAO certification; Solo Parent ID (yours or your parent\'s); Senior Citizen ID; Social Case Study for first-generation students; or NCIP certification for Indigenous Peoples\"]','[\"Certified true copy of the Certificate of Registration \\/ Enrollment (COR \\/ COE)\",\"Certified true copy of grades showing the GWA of the previous semester\",\"HEI certification that you do not receive another nationally funded scholarship (every start of the academic year)\"]','CHED and DepEd announce the call for applications. Apply on the official BPMSP online portal; it is coursed through the CHED Regional Office where your college is (CHEDRO XI for DNSC). CHEDRO shortlists and ranks applicants, checks that you are enrolled in a priority program, and issues a Notice of Award. Accept it within 15 days. If you qualify for two national government scholarships, you must choose one.','CHED and DepEd announce one call each academic year on the BPMSP portal. If you apply before you are admitted, you get one month from the start of the semester to submit proof of enrollment.',NULL,NULL,NULL,'From the CHED-TESDA-DepEd Joint Memorandum Circular on the BPMSP (Higher Education Track). Scholars render one year of return service in the Philippines for every year of support, and lose the grant if they fail a subject, drop below a regular load, or shift outside their priority domain. A leave of absence is allowed once, for up to one academic year. Holders of TESDA NC I to IV and working people with no college units may also apply, at any age.','[{\"key\":\"citizen\",\"label\":\"Filipino citizen (foreign or dual citizens cannot apply)\",\"type\":\"eq\",\"fact\":\"citizenship\",\"value\":\"filipino\",\"required\":true,\"weight\":2,\"src\":\"web\"},{\"key\":\"first_year\",\"label\":\"Incoming first-year college student (not repeating or re-entering first year)\",\"type\":\"eq\",\"fact\":\"year_level\",\"value\":1,\"required\":true,\"weight\":3,\"src\":\"web\"},{\"key\":\"merit\",\"label\":\"Top 5 of the graduating class, or a Grade 12 GWA of at least 95%\",\"type\":\"any_of\",\"required\":true,\"weight\":5,\"src\":\"web\",\"rules\":[{\"key\":\"top5\",\"label\":\"Top 5 of the graduating class\",\"type\":\"eq\",\"fact\":\"top_five\",\"value\":\"yes\"},{\"key\":\"gwa95\",\"label\":\"Grade 12 GWA of at least 95%\",\"type\":\"gte\",\"fact\":\"shs_gwa_percent\",\"value\":95}]},{\"key\":\"no_units\",\"label\":\"No college units earned before (including transferees or shiftees with credited units)\",\"type\":\"eq\",\"fact\":\"previous_units\",\"value\":\"no\",\"required\":true,\"weight\":2,\"src\":\"web\"},{\"key\":\"no_degree\",\"label\":\"Has not completed an undergraduate degree\",\"type\":\"eq\",\"fact\":\"previous_degree\",\"value\":\"no\",\"required\":true,\"weight\":2,\"src\":\"web\"},{\"key\":\"income\",\"label\":\"Combined parent \\/ guardian gross income of \\u20b12,000,000 or less\",\"type\":\"income_lte\",\"fact\":\"income\",\"value\":2000000,\"required\":true,\"weight\":3,\"src\":\"web\"},{\"key\":\"no_other\",\"label\":\"Not already getting a national government-funded scholarship or grant (private, LGU and one-time local grants do not count)\",\"type\":\"contains_none\",\"fact\":\"existing_assistance\",\"value\":[\"tes_tdp_ched\",\"other_government\"],\"required\":true,\"weight\":3,\"src\":\"web\"},{\"key\":\"no_discipline\",\"label\":\"No pending case or record of disciplinary action in school\",\"type\":\"eq\",\"fact\":\"disciplinary\",\"value\":\"no\",\"required\":true,\"weight\":2,\"src\":\"web\"},{\"key\":\"program\",\"label\":\"Enrolling in a CHED priority program\",\"type\":\"confirm\",\"fact\":\"program_field\",\"required\":true,\"weight\":3,\"src\":\"web\",\"confirm_note\":\"Priority domains: Agriculture and Fisheries; Creative Industries; Healthcare and Allied Programs; Engineering and Technology; Financial Services; Teacher Education; Digital Technology; Science and Mathematics. Ask CHEDRO XI or DNSC whether your program is on the current list.\"},{\"key\":\"equity\",\"label\":\"Special equity group (10 extra ranking points): Indigenous Peoples, PWD, solo parent or dependent, living in poverty, or first-generation student\",\"type\":\"contains_any\",\"fact\":\"special_groups\",\"value\":[\"indigenous_people\",\"pwd\",\"solo_parent\",\"listahanan\",\"four_ps\",\"first_generation\"],\"required\":false,\"weight\":2,\"src\":\"web\"}]',1,9,'2026-11-15',NULL,'2026-11-17 06:57:22','2026-11-17 06:57:22'),
(10,'grabscholar','GrabScholar College Scholarship','private','Grab Philippines','https://www.grab.com/ph/community/grabscholar-college-scholarship/','Six full-ride, merit-based scholarships for up to four years, for Filipino incoming first-year college students in academic years 2026–2030 who enroll in a 3- to 4-year Business, STEM (except medical courses) or Sustainability program at any university or college in the Philippines. Scholars are also linked to training, mentorship, internships and possible employment at Grab.','[\"100% tuition and miscellaneous fees\",\"School supplies allowance (uniform, textbooks, student ID): \\u20b111,000 a year\",\"Monthly student allowance: \\u20b18,000 a month\",\"Learning and development programs\",\"Internship opportunities, mentorship and possible employment at Grab\",\"For the whole 3- to 4-year program, as long as you meet the retention requirements\"]','[{\"key\":\"regular\",\"label\":\"GrabScholar\",\"amount\":8000,\"per\":\"month\"}]','[\"Birth certificate of the student (if you are the sibling of a Grab \\/ Move It driver or merchant partner, also the partner\'s birth certificate; a spouse of a partner submits a marriage certificate instead)\",\"Marriage certificate, if you are the spouse of a Grab \\/ Move It driver or merchant partner (owner or staff)\",\"Certificate of legal guardianship, if a legal guardian is applying for you\",\"Grade 12 report card\",\"Acceptance letter from your college or university, or any similar document\",\"Certificate of Good Moral Standing from your senior high school\"]',NULL,'Apply through the official GrabScholar application, yourself (adult applicant) or through a parent or legal guardian. Shortlisted applicants take assessment tests and interviews. Only selected applicants are notified by SMS and e-mail, and the list of scholars is posted on the GrabScholar website.','SY 2026 call: shortlist notified July 22; assessment tests and interviews July 25 – August 18, 2026.','2026-06-22','2026-07-17','2026-08-20','From the GrabScholar College Scholarship page for SY 2026–2030. The course list there is \"included but not limited to\", so BAKR only rules out programs that are clearly not Business, STEM or Sustainability.','[{\"key\":\"citizen\",\"label\":\"Filipino citizen\",\"type\":\"eq\",\"fact\":\"citizenship\",\"value\":\"filipino\",\"required\":true,\"weight\":2,\"src\":\"web\"},{\"key\":\"first_year\",\"label\":\"Incoming first-year college student\",\"type\":\"eq\",\"fact\":\"year_level\",\"value\":1,\"required\":true,\"weight\":3,\"src\":\"web\"},{\"key\":\"gwa\",\"label\":\"Grade 12 grade of at least 90%\",\"type\":\"gte\",\"fact\":\"shs_gwa_percent\",\"value\":90,\"required\":true,\"weight\":5,\"src\":\"web\"},{\"key\":\"none_other\",\"label\":\"No other scholarship or educational assistance\",\"type\":\"contains_none\",\"fact\":\"existing_assistance\",\"value\":[\"tes_tdp_ched\",\"other_government\",\"private_foundation\",\"dnsc_internal\"],\"required\":true,\"weight\":3,\"src\":\"web\"},{\"key\":\"course\",\"label\":\"A 3- to 4-year Business, STEM (not medical) or Sustainability program\",\"type\":\"program_in\",\"fact\":\"program_name\",\"required\":true,\"weight\":4,\"src\":\"web\",\"pass\":[\"Bachelor of Science in Information Technology\",\"Bachelor of Science in Information Systems\",\"Bachelor of Science in Entrepreneurship\",\"Bachelor of Science in Agro-Forestry\"],\"maybe\":[\"Bachelor of Science in Fisheries and Aquatic Sciences\",\"Bachelor of Science in Food Technology\",\"Bachelor of Science in Marine Biology\",\"Bachelor of Science in Disaster Resilience and Management\",\"Bachelor of Science in Tourism Management\",\"Bachelor of Public Administration\",\"Bachelor of Science in Social Work\",\"Bachelor of Secondary Education - Mathematics\",\"Bachelor of Secondary Education - Science\",\"Bachelor of Technology and Livelihood Education\"],\"not\":[\"Bachelor of Science in Criminology\",\"Bachelor of Physical Education\",\"Bachelor of Secondary Education - English\",\"Bachelor of Arts in Communication\"]},{\"key\":\"conduct\",\"label\":\"No disciplinary sanction in senior high school\",\"type\":\"eq\",\"fact\":\"disciplinary\",\"value\":\"no\",\"required\":true,\"weight\":2,\"src\":\"web\"}]',1,10,'2026-11-15',NULL,'2026-11-17 06:57:22','2026-11-17 06:57:22'),
(11,'cebuana-clfi','Cebuana Lhuillier Foundation National Scholarship Program','private','Cebuana Lhuillier Foundation','https://cebuanafoundation.org/','College scholarship grants from the Cebuana Lhuillier Foundation (CLFI) for economically challenged Filipino students, 1st to 4th year, in public state universities and colleges, in courses aligned with its Education-to-Employment (E2E) program. Open to all year levels; only one child per household at a time.','[\"Fixed stipend of up to \\u20b150,000 for school-related and living expenses\",\"Monthly learning sessions led by resource persons or subject matter experts, and various trainings\",\"Annual general assembly, outreach programs, leadership boot camp and a Cebuana Lhuillier Museum tour\",\"Recognition of exemplary academic and non-academic achievements\",\"Membership in the Cebuana Lhuillier Scholar Alumni Association\",\"Education-to-Employment (E2E): employment opportunities after graduation; scholars who graduate with Latin honors or a GWA of 90% are exempted from the PJLI pre-employment test\"]',NULL,'[\"Cebuana Lhuillier 24K Loyalty Card\",\"Proof of total family income\",\"Grades showing a GWA of at least 85% with no failing mark: 2nd semester of SY 2025\\u20132026 (freshmen: senior high school grades; if your report card is not yet released, a registrar\'s certification of your Grade 12 GWA)\",\"Certificate of Good Moral Character signed by an official of your last school\",\"Current enrollment or registration form (1st semester of SY 2026\\u20132027)\",\"Your university or college grading system in percentages\"]',NULL,'Fill in the online application form on the Scholarship Form tab of the CLFI website. CLFI does a paper screening with departments of the PJ Group of Companies; successful applicants are interviewed together with their parents or guardians. Results are final and cannot be appealed, but you may apply again in later years.','Open once a year. For SY 2026–2027, official results are announced on or before the last week of October 2026.',NULL,NULL,NULL,'From the Cebuana Lhuillier Foundation scholarship FAQs (Q1–Q12) and its qualifications page. Only the listed courses are accepted (no exceptions), and only one child per household at a time.','[{\"key\":\"citizen\",\"label\":\"Filipino citizen\",\"type\":\"eq\",\"fact\":\"citizenship\",\"value\":\"filipino\",\"required\":true,\"weight\":2,\"src\":\"web\"},{\"key\":\"client\",\"label\":\"Cebuana Lhuillier client with a 24K Loyalty Card\",\"type\":\"maybe_in\",\"fact\":\"cebuana_client\",\"pass\":[\"self\"],\"maybe\":[\"relative\"],\"required\":true,\"weight\":3,\"src\":\"web\"},{\"key\":\"savings\",\"label\":\"Cebuana Lhuillier Microsavings account (on the foundation\'s earlier qualifications page; not in the current FAQ)\",\"type\":\"maybe_in\",\"fact\":\"cebuana_microsavings\",\"pass\":[\"yes\"],\"maybe\":[\"willing_to_open\"],\"required\":false,\"weight\":1,\"src\":\"web\"},{\"key\":\"enrolled\",\"label\":\"Enrolled, or enrolling, in a public state university or college (DNSC is a state college); any year level\",\"type\":\"in\",\"fact\":\"enrollment_status\",\"value\":[\"currently_enrolled\",\"incoming_first_year\"],\"required\":true,\"weight\":1,\"src\":\"web\"},{\"key\":\"course\",\"label\":\"Supported course (Accountancy \\/ Finance, Business, Hospitality, Electrical \\/ Civil \\/ Mechanical \\/ Industrial Engineering, Architecture, Psychology, IT); no other courses are accepted\",\"type\":\"program_in\",\"fact\":\"program_name\",\"required\":true,\"weight\":4,\"src\":\"web\",\"pass\":[\"Bachelor of Science in Information Technology\"],\"maybe\":[\"Bachelor of Science in Information Systems\",\"Bachelor of Science in Entrepreneurship\",\"Bachelor of Science in Tourism Management\"],\"not\":[\"Bachelor of Arts in Communication\",\"Bachelor of Physical Education\",\"Bachelor of Public Administration\",\"Bachelor of Science in Agro-Forestry\",\"Bachelor of Science in Criminology\",\"Bachelor of Science in Disaster Resilience and Management\",\"Bachelor of Science in Fisheries and Aquatic Sciences\",\"Bachelor of Science in Food Technology\",\"Bachelor of Science in Marine Biology\",\"Bachelor of Science in Social Work\",\"Bachelor of Secondary Education - English\",\"Bachelor of Secondary Education - Mathematics\",\"Bachelor of Secondary Education - Science\",\"Bachelor of Technology and Livelihood Education\"]},{\"key\":\"income\",\"label\":\"Total annual family income of \\u20b1350,000 or less\",\"type\":\"income_lte\",\"fact\":\"income\",\"value\":350000,\"required\":true,\"weight\":4,\"src\":\"web\"},{\"key\":\"gwa\",\"label\":\"GWA of at least 85% from the last semester or school (freshmen: senior high school grades)\",\"type\":\"gte\",\"fact\":\"last_gwa_percent\",\"value\":85,\"required\":true,\"weight\":4,\"src\":\"web\"},{\"key\":\"no_fail\",\"label\":\"No failing mark in any subject\",\"type\":\"eq\",\"fact\":\"failing_grade\",\"value\":\"no\",\"required\":true,\"weight\":2,\"src\":\"web\"}]',1,11,'2026-11-15',NULL,'2026-11-17 06:57:22','2026-11-17 06:57:22');
/*!40000 ALTER TABLE `scholarships` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES
('6Udv2Pudvw3GClmC1ky3fMbWLm5w1d6GFo3ZvnZz',10,'127.0.0.1','Symfony','eyJfdG9rZW4iOiIybmd2Qm1lUmE5RlhIVmZMOFVZQ0gxYWQzUHAwZFA3M1RtWHp3ek1lIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdFwvYWRtaW4iLCJyb3V0ZSI6ImFkbWluLm92ZXJ2aWV3In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1795002726),
('7JZqa0oubO3MZdjzDuDvGE44J6NwSQIDdRbUmHR4',7,'127.0.0.1','Symfony','eyJfdG9rZW4iOiJiYXViZkZxeDJleVpMT2daVWZHdHlQOW9pQXhBUnBVUWRGYVBIdWhSIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdFwvc2Nob2xhciIsInJvdXRlIjoic2Nob2xhci5vdmVydmlldyJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1794988653),
('btDF1hESh3N06uUb9nuM6Vy8YMrkoFc3zuqkbM2l',999999,'127.0.0.1','Symfony','eyJfdG9rZW4iOiJuUkczWWdFanVPSmtKbUozbGhCdk1tcm1oOWs1VUNYQjJ2WlR1WVpMIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdFwvYWRtaW4iLCJyb3V0ZSI6ImFkbWluLm92ZXJ2aWV3In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1794989526),
('bvIyAbRgJcZktLiaOOnCWeoPLeYPbpEc7zPao5dX',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','eyJfdG9rZW4iOiI4QUNhVFhsTDhoblpJbTVMNURDdWlpUHY4S3M4S1JBZEUwR1laN0toIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDAiLCJyb3V0ZSI6ImhvbWUifX0=',1795005864),
('oMZbHGr8CrX26uqNA7JY6J3V55HEi0CwME0ScHGP',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-PH) WindowsPowerShell/5.1.26100.9818','eyJfdG9rZW4iOiJNUEhzR1JFT3NKaEJvUWwwSnpReTJvbW9DUzRBeXY1Qld0aXluTUY1IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDAwXC9zdHVkZW50XC9mb3Jnb3QtcGFzc3dvcmQ/ZnJvbT1wcm92aWRlciIsInJvdXRlIjoic3R1ZGVudC5wYXNzd29yZC5yZXF1ZXN0In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1794984373),
('sm7iZyny7k7ZvXUjUqD3Kj71CmD4EE8n4V6TdHIc',1,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-PH) WindowsPowerShell/5.1.26100.9818','eyJfdG9rZW4iOiJoQjBsaW1jUVI0QzNHeGhtY1BZdTl2YmI2MU01TEpYTlhEZ0dRTlVEIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDAwXC9wcm92aWRlclwvc2Nob2xhcnNoaXBzIiwicm91dGUiOiJwcm92aWRlci5zY2hvbGFyc2hpcHMifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6MSwicGFzc3dvcmRfaGFzaF93ZWIiOiIxOGUwMjI0ODQ5OTYyY2U3YjdkNmY0MzliYWFkZTRjMmMxYzQ3MGMzMTIxNTFmNzAxMTUwNWVkMzY5N2RhZDk1In0=',1794984373),
('t9GJ2iWvPsjpPLIhR7IyxcJ6I8jlBIOxy6VYMFop',NULL,'::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-PH) WindowsPowerShell/5.1.26100.9818','eyJfdG9rZW4iOiJTeFNCUTNnamR0NnBrcXVhTEVKa2VMRlI4Z3V6MG1Uc2JJYndaQkpWIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdFwvQkFLUlwvcHVibGljIiwicm91dGUiOiJob21lIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1795004497),
('XWkcMpBwzbfssuuDYw6AxKjU8IuANkEvqq8XiPZx',10,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','eyJfdG9rZW4iOiI2NDBuemhjc2luZjRIZlUyVWpRbmJxMmlHTWZtQWp6WGoxS1VVdkgxIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDBcL2FkbWluXC9hbmFseXRpY3MiLCJyb3V0ZSI6ImFkbWluLmFuYWx5dGljcyJ9LCJ1cmwiOltdLCJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6MTAsInBhc3N3b3JkX2hhc2hfd2ViIjoiNmQ4ZTIyYWZhYmEzZTE1YzBhMTU4NDljN2MyZTQzNTllMmYyMmM3ZTQxZTczMzA1Y2I1OGYyZGM0NjNiNjU3OSJ9',1794989929),
('yOI3T0h7WzWZesyySbGtKTsFpJhOHSfrX9VWwkMp',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-PH) WindowsPowerShell/5.1.26100.9818','eyJfdG9rZW4iOiJ6bEpGRHNSM1R4ZHBTaDVSNFdvWUdRSjVneGJHeEd0RTNwYnZqdDlEIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDAwIiwicm91dGUiOiJob21lIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1795004496);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stipend_disbursements`
--

DROP TABLE IF EXISTS `stipend_disbursements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `stipend_disbursements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `scholar_id` bigint(20) unsigned NOT NULL,
  `term` varchar(60) NOT NULL,
  `release_type` varchar(10) NOT NULL DEFAULT 'semester',
  `period_month` varchar(7) NOT NULL DEFAULT '',
  `amount` decimal(10,2) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `released_at` timestamp NULL DEFAULT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `recorded_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stipend_disbursements_scholar_term_period_unique` (`scholar_id`,`term`,`period_month`),
  KEY `stipend_disbursements_recorded_by_foreign` (`recorded_by`),
  KEY `stipend_disbursements_term_status_index` (`term`,`status`),
  CONSTRAINT `stipend_disbursements_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `stipend_disbursements_scholar_id_foreign` FOREIGN KEY (`scholar_id`) REFERENCES `scholars` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stipend_disbursements`
--

LOCK TABLES `stipend_disbursements` WRITE;
/*!40000 ALTER TABLE `stipend_disbursements` DISABLE KEYS */;
INSERT INTO `stipend_disbursements` VALUES
(1,2,'AY 2026–2027 · 1st Semester','semester','',20000.00,'pending',NULL,NULL,1,'2026-11-17 08:59:42','2026-11-17 08:59:42');
/*!40000 ALTER TABLE `stipend_disbursements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_consents`
--

DROP TABLE IF EXISTS `student_consents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `student_consents` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `consent_type` varchar(50) NOT NULL,
  `accepted_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_consents_user_id_consent_type_unique` (`user_id`,`consent_type`),
  CONSTRAINT `student_consents_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_consents`
--

LOCK TABLES `student_consents` WRITE;
/*!40000 ALTER TABLE `student_consents` DISABLE KEYS */;
INSERT INTO `student_consents` VALUES
(1,11,'data_privacy','2026-11-18 04:31:24','2026-11-18 04:31:24','2026-11-18 04:31:24');
/*!40000 ALTER TABLE `student_consents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'student',
  `provider_key` varchar(30) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `must_change_password` tinyint(1) NOT NULL DEFAULT 0,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_role_index` (`role`),
  KEY `users_provider_key_index` (`provider_key`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(1,'CHED / UniFAST','ched.provider@bakr.test',NULL,'$2y$12$Mexp77XpCYR5BTCK73wQ3uBp7VMvjFO41vid/.DN2M9RakwhR7OY.','provider','ched','active',0,NULL,'2026-11-17 06:20:36','2026-11-17 06:20:36'),
(2,'DOST – Science Education Institute','dost.provider@bakr.test',NULL,'$2y$12$kfb7f6e4KSrhGafkK5dJcuabZJrrlcJPZoMyj8HsDw0rT.iVTYLo2','provider','dost','active',0,NULL,'2026-11-17 06:20:36','2026-11-17 06:20:36'),
(3,'Cebuana Lhuillier Foundation','cebuana.provider@bakr.test',NULL,'$2y$12$gf4PJBEQ8R/Ty1ihgrPXSuau8rqVoRY3dSa3TgOhHXgVwjNKrJtjC','provider','cebuana','active',0,NULL,'2026-11-17 06:20:37','2026-11-17 06:20:37'),
(4,'DNSC Scholarship and Grants Unit','dnsc.provider@bakr.test',NULL,'$2y$12$X0Mrzaw14B61ahJgSsJn6uu2ms1edBH0GIPgAhm5RBKbYV5ypr.cC','provider','dnsc','active',0,NULL,'2026-11-17 06:20:37','2026-11-17 06:20:37'),
(5,'Grab Philippines','grab.provider@bakr.test',NULL,'$2y$12$jJcNfl.3dkbfTBRkz6aFS.rXr0J1K.3daMEB17Ul6ztWOFoJxtpSO','provider','grab','active',0,NULL,'2026-11-17 06:20:37','2026-11-17 06:20:37'),
(7,'Karl Angelo T. Gabaes','gabaes.karlangelo@dnsc.edu.ph',NULL,'$2y$12$/gvZMp/69q5npUG4SOTI4OhvoSalLe9zcoJ4t1mqaH/50/Pxwp1Da','scholar',NULL,'active',0,'n1GwVhiOnOW1iyDb7CuzQX0XpOi9yTTLlT9oxFJab2hnaN775HX8JPGard7S','2026-11-17 07:57:13','2026-11-17 08:04:23'),
(8,'Juan Tapulan','gabaeskarlangelo@gmail.com',NULL,'$2y$12$NOuNBZ1/n4/t3gLZYsPd0.bwwbVyGvhgmroA7gY2Idvv.iOa6kTUO','scholar',NULL,'active',0,'cWq4H8OX1Bj8UiVSLdSBFB2nzqH5TtXorkzhoXeSx0vupHv1EsXV4xDGxxVC','2026-11-17 08:50:06','2026-11-17 08:55:13'),
(9,'Raiden Shogun','hakdoggie678@gmail.com',NULL,'$2y$12$QRTIf/RDBNCoHUKIqus3lOJAiN6UDiWiEHG3fbgmY11BRhRmvUzjK','scholar',NULL,'active',0,'EjLYnXg3fHWOrjw6KMTY9y80ypYP78owvMtr6FFGLybv2zNuJ8OcH3tkZMOl','2026-11-17 09:07:38','2026-11-17 09:09:11'),
(10,'BAKR Administrator','bakr@gmail.com',NULL,'$2y$12$A9XueIz6GlJ.YqUF5YsBg.xaN1rNfXy4nRuGh9wUcYNjAKoi5nOm2','admin',NULL,'active',0,NULL,'2026-11-18 00:17:18','2026-11-18 03:43:46'),
(11,'Karl Angelo Gabaes','badboysacoc444@gmail.com',NULL,'$2y$12$3kECLE2AHZORj6KLBgKKOOw1KK1NBcQDgB8Uxw4tfb.6H7Woialoi','student',NULL,'active',0,NULL,'2026-11-18 04:07:40','2026-11-18 04:07:40');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-18 21:07:52
