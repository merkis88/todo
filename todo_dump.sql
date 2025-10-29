-- MySQL dump 10.13  Distrib 8.0.43, for Linux (x86_64)
--
-- Host: localhost    Database: to_do
-- ------------------------------------------------------
-- Server version	8.0.43-0ubuntu0.22.04.1

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
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (8,'2025_04_18_175314_done_task',2),(9,'2014_10_12_000000_create_users_table',3),(10,'2014_10_12_100000_create_password_reset_tokens_table',3),(11,'2019_08_19_000000_create_failed_jobs_table',3),(12,'2019_12_14_000001_create_personal_access_tokens_table',3),(13,'2025_04_10_132826_create_telegraph_bots_table',3),(14,'2025_04_10_132827_create_telegraph_chats_table',3),(15,'2025_04_16_160436_create_tasks_table',3),(16,'2025_04_17_165348_done_task',3),(17,'2025_05_19_132533_add_telegraph_chat_id_to_tasks_table',4),(19,'2025_07_20_143209_add_awaiting_section_name_to_telegraph_chats_table',6),(20,'2025_04_16_160436_create_tasks_table',4),(21,'2025_04_18_175314_done_task',4),(22,'2025_05_19_132533_add_telegraph_chat_id_to_tasks_table',4),(24,'2025_04_16_160436_create_tasks_table',4),(25,'2025_04_18_175314_done_task',4),(26,'2025_05_19_132533_add_telegraph_chat_id_to_tasks_table',4),(28,'2025_05_20_152254_add_remind_at_to_tasks_table',7),(29,'2025_07_22_062104_create_sections_table',8),(30,'2025_07_27_142328_add_section_id_to_tasks_table',9),(31,'2025_08_09_090854_create_jobs_table',10);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
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
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sections`
--

DROP TABLE IF EXISTS `sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sections` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telegraph_chat_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sections_telegraph_chat_id_index` (`telegraph_chat_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sections`
--

LOCK TABLES `sections` WRITE;
/*!40000 ALTER TABLE `sections` DISABLE KEYS */;
INSERT INTO `sections` VALUES (2,'Учёба',2,'2025-07-24 02:41:55','2025-07-24 02:41:55'),(3,'Качалка',2,'2025-07-24 02:47:55','2025-07-24 02:47:55'),(4,'Комп',2,'2025-07-24 02:48:20','2025-07-24 02:48:20'),(6,'Php',2,'2025-07-27 12:34:49','2025-07-27 12:34:49'),(7,'Js',2,'2025-07-28 07:40:55','2025-07-28 07:40:55'),(8,'Отдых',2,'2025-08-03 10:24:14','2025-08-03 10:24:14'),(9,'Кишбулак',2,'2025-08-03 10:55:47','2025-08-03 10:55:47'),(10,'Работа',1,'2025-08-03 13:05:39','2025-08-03 13:05:39'),(11,'Учеба',1,'2025-08-03 13:05:54','2025-08-03 13:05:54'),(13,'Sosal?',7,'2025-08-14 13:49:34','2025-08-14 13:49:34'),(14,'Что',1,'2025-08-16 05:52:12','2025-08-16 05:52:12'),(16,'Наушники',1,'2025-08-23 10:42:00','2025-08-23 10:42:00'),(17,'Импортированные',1,'2025-08-24 08:16:38','2025-08-24 08:16:38');
/*!40000 ALTER TABLE `sections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tasks`
--

DROP TABLE IF EXISTS `tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tasks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_done` tinyint(1) NOT NULL DEFAULT '0',
  `telegraph_chat_id` bigint unsigned DEFAULT NULL,
  `section_id` bigint unsigned DEFAULT NULL,
  `remind_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tasks_telegraph_chat_id_foreign` (`telegraph_chat_id`),
  KEY `tasks_section_id_foreign` (`section_id`),
  CONSTRAINT `tasks_section_id_foreign` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tasks_telegraph_chat_id_foreign` FOREIGN KEY (`telegraph_chat_id`) REFERENCES `telegraph_chats` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tasks`
--

LOCK TABLES `tasks` WRITE;
/*!40000 ALTER TABLE `tasks` DISABLE KEYS */;
INSERT INTO `tasks` VALUES (1,'Task 1','2025-04-19 03:29:37','2025-04-19 03:30:13',1,NULL,NULL,NULL),(2,'Task 2','2025-04-19 03:29:46','2025-04-19 03:29:46',0,NULL,NULL,NULL),(3,'Получить лям','2025-04-19 03:29:53','2025-04-20 04:37:21',0,NULL,NULL,NULL),(6,'Сенсуалист','2025-04-22 05:51:45','2025-04-22 05:52:35',1,NULL,NULL,NULL),(7,'Закончить подключение GPT','2025-04-24 09:54:42','2025-05-19 10:15:30',1,NULL,NULL,NULL),(8,'Написать курсач','2025-04-28 05:13:01','2025-04-28 05:13:01',0,NULL,NULL,NULL),(9,'Не разъебать дверь','2025-05-19 08:27:25','2025-05-19 10:16:03',1,NULL,NULL,NULL),(10,'Разграничить доступ к боту','2025-05-19 10:14:57','2025-05-19 10:14:57',0,NULL,NULL,NULL),(11,'Вроде есть разграничение','2025-05-19 10:48:23','2025-05-19 10:48:23',0,NULL,NULL,NULL),(12,'Что-то','2025-05-19 10:49:32','2025-05-19 10:49:32',0,NULL,NULL,NULL),(13,'Проверка из тинкера','2025-05-19 11:20:28','2025-05-19 11:20:28',0,NULL,NULL,NULL),(14,'Повторная проверка','2025-05-19 11:28:15','2025-05-19 11:28:15',0,NULL,NULL,NULL),(15,'Первая задача','2025-05-19 11:35:13','2025-08-03 06:48:39',1,1,NULL,NULL),(16,'Насрать под дверь','2025-05-19 13:11:34','2025-05-19 13:12:07',1,2,NULL,NULL),(17,'Вторая задача','2025-05-20 03:59:10','2025-05-20 03:59:10',0,1,NULL,NULL),(18,'Первая задача','2025-05-20 04:17:05','2025-05-20 04:17:05',1,2,NULL,NULL),(21,'Интересно','2025-05-21 09:24:13','2025-05-21 09:24:13',0,1,NULL,NULL),(22,'новый пользователь','2025-05-21 09:34:56','2025-05-21 09:34:56',0,3,NULL,NULL),(24,'Привет из Postman','2025-05-22 13:28:55','2025-08-24 08:18:40',1,1,NULL,'2025-08-24 08:28:40'),(25,'ЗАДАЧА ИЗМЕНЕНА','2025-05-22 13:31:15','2025-05-23 05:56:34',0,1,NULL,NULL),(36,'привет','2025-06-19 02:01:58','2025-06-19 02:01:58',0,4,NULL,NULL),(38,'Выучить Английский','2025-08-03 12:29:58','2025-08-03 12:29:58',0,2,2,NULL),(44,'Выучить лорауэль','2025-08-16 06:03:57','2025-08-16 06:03:57',0,1,11,NULL),(45,'Купить машину','2025-08-16 06:21:27','2025-08-16 06:21:27',0,1,14,NULL),(46,'Сходить на тренировку','2025-08-16 06:22:47','2025-08-16 06:22:47',0,1,14,NULL),(47,'Выучить Python','2025-08-16 06:23:42','2025-08-16 06:23:42',0,1,11,NULL),(48,'Сходить к преподавателю за объяснением задачи','2025-08-16 06:24:14','2025-08-16 06:24:14',0,1,11,NULL),(50,'Купить наушники','2025-08-23 10:42:15','2025-08-23 10:42:15',0,1,16,NULL),(52,'Купить Samsung FreeBuds 3 Pro','2025-08-24 05:31:33','2025-08-24 05:31:33',0,1,16,NULL),(53,'Насрать под дверь','2025-08-24 08:16:38','2025-08-24 08:16:38',1,1,17,NULL),(54,'Первая задача','2025-08-24 08:16:38','2025-08-24 08:16:38',1,1,17,NULL),(55,'Выучить Английский','2025-08-24 08:16:38','2025-08-24 08:16:38',0,1,17,NULL),(56,'Сходить на учебу','2025-10-17 05:38:34','2025-10-17 05:38:34',0,1,11,NULL),(57,'Заходить на учебу','2025-10-17 05:58:03','2025-10-17 05:58:03',0,1,11,NULL);
/*!40000 ALTER TABLE `tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `telegraph_bots`
--

DROP TABLE IF EXISTS `telegraph_bots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `telegraph_bots` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `telegraph_bots_token_unique` (`token`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `telegraph_bots`
--

LOCK TABLES `telegraph_bots` WRITE;
/*!40000 ALTER TABLE `telegraph_bots` DISABLE KEYS */;
INSERT INTO `telegraph_bots` VALUES (1,'7842446740:AAEJYeYBQZhEShnMKpPFqvHmYeLy9V9IBaw','ещtodo','2025-04-19 03:26:25','2025-04-19 03:26:25');
/*!40000 ALTER TABLE `telegraph_bots` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `telegraph_chats`
--

DROP TABLE IF EXISTS `telegraph_chats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `telegraph_chats` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `chat_id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telegraph_bot_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `awaiting_section_name` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `telegraph_chats_chat_id_telegraph_bot_id_unique` (`chat_id`,`telegraph_bot_id`),
  KEY `telegraph_chats_telegraph_bot_id_foreign` (`telegraph_bot_id`),
  CONSTRAINT `telegraph_chats_telegraph_bot_id_foreign` FOREIGN KEY (`telegraph_bot_id`) REFERENCES `telegraph_bots` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `telegraph_chats`
--

LOCK TABLES `telegraph_chats` WRITE;
/*!40000 ALTER TABLE `telegraph_chats` DISABLE KEYS */;
INSERT INTO `telegraph_chats` VALUES (1,'965906413','[private] axuenno888',1,'2025-04-19 03:27:10','2025-04-19 03:27:10',0),(2,'1113492652','[private] PospeIka',1,'2025-04-28 10:19:33','2025-04-28 10:19:33',0),(3,'1265242506','[private] Asmodey_666',1,'2025-05-21 09:34:10','2025-05-21 09:34:10',0),(4,'379927482','[private] whyusopowerful',1,'2025-06-19 02:01:20','2025-06-19 02:01:20',0),(5,'2012319209','[private] ',1,'2025-06-23 04:49:10','2025-06-23 04:49:10',0),(6,'7641660038','[private] axuenno111',1,'2025-08-03 06:51:34','2025-08-03 06:51:34',0),(7,'5390120588','[private] jumanjit_007',1,'2025-08-14 13:47:18','2025-08-14 13:47:18',0),(8,'1152864336','[private] Onevanyaaa',1,'2025-08-14 13:52:43','2025-08-14 13:52:43',0),(9,'952960991','[private] pusywarrior2006',1,'2025-08-14 20:24:26','2025-08-14 20:24:26',0);
/*!40000 ALTER TABLE `telegraph_chats` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
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

-- Dump completed on 2025-10-17 18:06:43
