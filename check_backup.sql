-- MySQL dump 10.13  Distrib 8.0.42, for Linux (x86_64)
--
-- Host: localhost    Database: to_do
-- ------------------------------------------------------
-- Server version	8.0.42-0ubuntu0.22.04.1

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
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (8,'2025_04_18_175314_done_task',2),(9,'2014_10_12_000000_create_users_table',3),(10,'2014_10_12_100000_create_password_reset_tokens_table',3),(11,'2019_08_19_000000_create_failed_jobs_table',3),(12,'2019_12_14_000001_create_personal_access_tokens_table',3),(13,'2025_04_10_132826_create_telegraph_bots_table',3),(14,'2025_04_10_132827_create_telegraph_chats_table',3),(15,'2025_04_16_160436_create_tasks_table',3),(16,'2025_04_17_165348_done_task',3),(17,'2025_05_19_132533_add_telegraph_chat_id_to_tasks_table',4),(18,'2025_05_20_152254_add_remind_at_to_tasks_table',5);
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
  `remind_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tasks_telegraph_chat_id_foreign` (`telegraph_chat_id`),
  CONSTRAINT `tasks_telegraph_chat_id_foreign` FOREIGN KEY (`telegraph_chat_id`) REFERENCES `telegraph_chats` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tasks`
--

LOCK TABLES `tasks` WRITE;
/*!40000 ALTER TABLE `tasks` DISABLE KEYS */;
INSERT INTO `tasks` VALUES (1,'Task 1','2025-04-19 03:29:37','2025-04-19 03:30:13',1,NULL,NULL),(2,'Task 2','2025-04-19 03:29:46','2025-04-19 03:29:46',0,NULL,NULL),(3,'Получить лям','2025-04-19 03:29:53','2025-04-20 04:37:21',0,NULL,NULL),(6,'Сенсуалист','2025-04-22 05:51:45','2025-04-22 05:52:35',1,NULL,NULL),(7,'Закончить подключение GPT','2025-04-24 09:54:42','2025-05-19 10:15:30',1,NULL,NULL),(8,'Написать курсач','2025-04-28 05:13:01','2025-04-28 05:13:01',0,NULL,NULL),(9,'Не разъебать дверь','2025-05-19 08:27:25','2025-05-19 10:16:03',1,NULL,NULL),(10,'Разграничить доступ к боту','2025-05-19 10:14:57','2025-05-19 10:14:57',0,NULL,NULL),(11,'Вроде есть разграничение','2025-05-19 10:48:23','2025-05-19 10:48:23',0,NULL,NULL),(12,'Что-то','2025-05-19 10:49:32','2025-05-19 10:49:32',0,NULL,NULL),(13,'Проверка из тинкера','2025-05-19 11:20:28','2025-05-19 11:20:28',0,NULL,NULL),(14,'Повторная проверка','2025-05-19 11:28:15','2025-05-19 11:28:15',0,NULL,NULL),(15,'Первая задача','2025-05-19 11:35:13','2025-05-20 03:59:35',1,1,NULL),(16,'Насрать под дверь','2025-05-19 13:11:34','2025-05-19 13:12:07',1,2,NULL),(17,'Вторая задача','2025-05-20 03:59:10','2025-05-20 03:59:10',0,1,NULL),(18,'Первая задача','2025-05-20 04:17:05','2025-05-20 04:17:05',1,2,NULL),(19,'Вторая задача','2025-05-20 04:17:05','2025-05-20 04:17:05',0,2,NULL),(20,'Всё работает','2025-05-20 13:24:40','2025-05-20 13:27:26',0,1,NULL),(21,'Интересно','2025-05-21 09:24:13','2025-05-21 09:24:13',0,1,NULL),(22,'новый пользователь','2025-05-21 09:34:56','2025-05-21 09:34:56',0,3,NULL),(23,'Всё норм','2025-05-22 10:01:25','2025-05-22 10:10:46',1,1,NULL),(24,'Привет из Postman','2025-05-22 13:28:55','2025-05-22 13:28:55',0,1,NULL),(25,'ЗАДАЧА ИЗМЕНЕНА','2025-05-22 13:31:15','2025-05-23 05:56:34',0,1,NULL),(26,'ОТВЕТ 204','2025-05-22 13:52:38','2025-05-23 07:03:55',1,1,'2025-05-23 07:04:55'),(28,'Первая задача','2025-05-23 06:13:08','2025-05-23 06:13:08',1,2,NULL),(29,'Вторая задача','2025-05-23 06:13:08','2025-05-23 06:13:08',0,2,NULL),(30,'Всё работает','2025-05-23 06:13:08','2025-05-23 06:13:08',0,2,NULL),(31,'Интересно','2025-05-23 06:13:08','2025-05-23 06:13:08',0,2,NULL),(32,'Всё норм','2025-05-23 06:13:08','2025-05-23 06:13:08',1,2,NULL),(33,'Привет из Postman','2025-05-23 06:13:08','2025-05-23 06:13:08',0,2,NULL),(34,'ЗАДАЧА ИЗМЕНЕНА','2025-05-23 06:13:08','2025-05-23 06:13:08',0,2,NULL),(35,'ОТВЕТ 204','2025-05-23 06:13:08','2025-05-23 07:04:27',1,2,'2025-05-23 07:05:27'),(36,'привет','2025-06-19 02:01:58','2025-06-19 02:01:58',0,4,NULL);
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
  PRIMARY KEY (`id`),
  UNIQUE KEY `telegraph_chats_chat_id_telegraph_bot_id_unique` (`chat_id`,`telegraph_bot_id`),
  KEY `telegraph_chats_telegraph_bot_id_foreign` (`telegraph_bot_id`),
  CONSTRAINT `telegraph_chats_telegraph_bot_id_foreign` FOREIGN KEY (`telegraph_bot_id`) REFERENCES `telegraph_bots` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `telegraph_chats`
--

LOCK TABLES `telegraph_chats` WRITE;
/*!40000 ALTER TABLE `telegraph_chats` DISABLE KEYS */;
INSERT INTO `telegraph_chats` VALUES (1,'965906413','[private] axuenno888',1,'2025-04-19 03:27:10','2025-04-19 03:27:10'),(2,'1113492652','[private] PospeIka',1,'2025-04-28 10:19:33','2025-04-28 10:19:33'),(3,'1265242506','[private] Asmodey_666',1,'2025-05-21 09:34:10','2025-05-21 09:34:10'),(4,'379927482','[private] whyusopowerful',1,'2025-06-19 02:01:20','2025-06-19 02:01:20'),(5,'2012319209','[private] ',1,'2025-06-23 04:49:10','2025-06-23 04:49:10');
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

-- Dump completed on 2025-07-19 10:43:06
