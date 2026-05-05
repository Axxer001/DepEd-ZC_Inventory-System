/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `acquisition_sources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `acquisition_sources` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `source_type` enum('Internal','External') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `asset_distributions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `asset_distributions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `asset_source_id` bigint unsigned NOT NULL,
  `region` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Region IX',
  `division` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Division of Zamboanga City',
  `office_school_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `school_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'School ID of holder, empty if not a school',
  `office_school_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nature_of_occupancy` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `property_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `acquisition_cost` decimal(15,2) NOT NULL DEFAULT '0.00',
  `acquisition_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `asset_distributions_property_number_unique` (`property_number`),
  KEY `asset_distributions_asset_source_id_foreign` (`asset_source_id`),
  CONSTRAINT `asset_distributions_asset_source_id_foreign` FOREIGN KEY (`asset_source_id`) REFERENCES `asset_sources` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `asset_sources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `asset_sources` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `item_id` bigint unsigned NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `acquisition_source_id` bigint unsigned NOT NULL,
  `mode_of_acquisition` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `source_personnel` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `personnel_position` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `asset_cost` decimal(15,2) NOT NULL DEFAULT '0.00',
  `quantity` int NOT NULL DEFAULT '1',
  `estimated_useful_life` int DEFAULT NULL COMMENT 'Estimated useful life in years (e.g., 2, 3, 5, 10)',
  `acceptance_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `asset_sources_item_id_foreign` (`item_id`),
  KEY `asset_sources_acquisition_source_id_foreign` (`acquisition_source_id`),
  CONSTRAINT `asset_sources_acquisition_source_id_foreign` FOREIGN KEY (`acquisition_source_id`) REFERENCES `acquisition_sources` (`id`) ON DELETE CASCADE,
  CONSTRAINT `asset_sources_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `blocked_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `blocked_accounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `blocked_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `blocked_accounts_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `classification_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `categories_classification_id_foreign` (`classification_id`),
  CONSTRAINT `categories_classification_id_foreign` FOREIGN KEY (`classification_id`) REFERENCES `classifications` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `classifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `classifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `classifications_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `districts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `districts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quadrant_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `districts_quadrant_id_foreign` (`quadrant_id`),
  CONSTRAINT `districts_quadrant_id_foreign` FOREIGN KEY (`quadrant_id`) REFERENCES `quadrants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `items_category_id_foreign` (`category_id`),
  CONSTRAINT `items_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `legislative_districts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `legislative_districts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pending_registrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pending_registrations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pending_registrations_email_unique` (`email`),
  UNIQUE KEY `pending_registrations_token_unique` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `quadrants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `quadrants` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `legislative_district_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `quadrants_legislative_district_id_foreign` (`legislative_district_id`),
  CONSTRAINT `quadrants_legislative_district_id_foreign` FOREIGN KEY (`legislative_district_id`) REFERENCES `legislative_districts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `schools`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `schools` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `district_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `schools_new_school_id_index` (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`avnadmin`@`%`*/ /*!50003 TRIGGER `trg_schools_insert` AFTER INSERT ON `schools` FOR EACH ROW BEGIN
                INSERT INTO system_logs (user, activity, module, action_type, created_at, updated_at)
                VALUES (
                    IFNULL(@app_user, CURRENT_USER()),
                    CONCAT('Added new school: ', NEW.name),
                    'Schools',
                    'Create',
                    NEW.created_at,
                    NEW.created_at
                );
            END */;;
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
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`avnadmin`@`%`*/ /*!50003 TRIGGER `trg_schools_delete` AFTER DELETE ON `schools` FOR EACH ROW BEGIN
                INSERT INTO system_logs (user, activity, module, action_type, created_at, updated_at)
                VALUES (
                    IFNULL(@app_user, CURRENT_USER()),
                    CONCAT('Deleted school: ', OLD.name),
                    'Schools',
                    'Delete',
                    NOW(),
                    NOW()
                );
            END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `system_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activity` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `module` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `action_type` enum('Create','Update','Delete','Others') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `approved` tinyint(1) NOT NULL DEFAULT '0',
  `dark_mode` tinyint(1) NOT NULL DEFAULT '0',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`avnadmin`@`%`*/ /*!50003 TRIGGER `trg_users_insert` AFTER INSERT ON `users` FOR EACH ROW BEGIN
                INSERT INTO system_logs (user, activity, module, action_type, created_at, updated_at)
                VALUES (
                    IFNULL(@app_user, CURRENT_USER()),
                    CONCAT('Created new account: ', NEW.email),
                    'Accounts',
                    'Create',
                    NOW(),
                    NOW()
                );
            END */;;
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
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`avnadmin`@`%`*/ /*!50003 TRIGGER `trg_users_delete` AFTER DELETE ON `users` FOR EACH ROW BEGIN
                INSERT INTO system_logs (user, activity, module, action_type, created_at, updated_at)
                VALUES (
                    IFNULL(@app_user, CURRENT_USER()),
                    CONCAT('Deleted account: ', OLD.email),
                    'Accounts',
                    'Delete',
                    NOW(),
                    NOW()
                );
            END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'0001_01_01_000000_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2026_03_03_000001_add_approved_to_users_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2026_03_03_000002_create_pending_registrations_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2026_03_03_000003_create_legislative_districts_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2026_03_03_000004_create_quadrants_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2026_03_03_000005_create_districts_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2026_03_03_000006_create_schools_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2026_03_03_000007_create_items_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2026_03_03_000008_create_sub_items_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2026_03_05_000001_add_expires_at_to_pending_registrations',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2026_03_05_000002_create_blocked_accounts_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2026_03_03_000001_add_approved_to_users_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15,'2026_03_05_000001_create_pending_registrations_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16,'2026_03_05_000002_create_blocked_accounts_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (17,'2026_03_03_061619_make_password_nullable_on_users_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (18,'2026_03_10_030111_update_schools_table_primary_key',7);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (20,'2026_03_10_065655_create_system_logs_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (21,'2026_03_10_065931_create_schools_triggers',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (22,'2026_03_10_070523_create_users_triggers',10);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (23,'2026_03_12_034646_add_category_id_to_items_table',11);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (24,'2026_03_17_095134_create_ownerships_table',12);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (25,'2026_03_17_095149_update_items_table_drop_columns',13);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (26,'2026_03_17_095231_update_sub_items_table_drop_columns',13);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (27,'2026_03_17_111200_fix_schools_triggers_timestamp',14);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (28,'2026_03_17_143237_update_items_table_add_master_quantity',15);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (29,'2026_03_19_090546_add_quantity_to_sub_items_table',16);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (30,'2026_03_26_000001_add_condition_to_sub_items_table',17);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (31,'2026_03_26_000002_add_condition_to_ownerships_table',18);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (32,'2026_03_26_100001_create_stakeholders_table',19);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (33,'2026_03_26_100002_seed_stakeholders_and_update_ownerships',20);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (34,'2026_03_31_000001_add_distributor_id_to_sub_items_table',21);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (35,'2026_04_07_103648_update_sub_items_for_expanded_inventory',22);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (36,'2026_04_07_112521_add_status_to_stakeholders_table',23);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (37,'2026_04_07_112534_create_asset_transactions_table',24);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (38,'2026_04_07_130350_make_school_id_nullable_in_ownerships_table',25);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (39,'2026_04_07_130350_add_classification_to_stakeholders_table',26);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (40,'2026_04_07_130351_seed_standard_government_sources',27);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (41,'2026_04_14_111509_drop_source_type_from_stakeholders_table',28);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (42,'2026_04_27_214025_add_serialization_to_ownerships_table',29);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (43,'2026_04_28_113849_add_updated_at_to_categories_table',30);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (44,'2026_03_17_092831_create_schools_table',31);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (45,'2026_04_23_100319_add_stock_in_to_asset_transactions_type_enum',31);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (46,'2026_04_30_142126_restructure_asset_database_schema',32);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (47,'2026_04_30_212955_add_dark_mode_to_users_table',33);
