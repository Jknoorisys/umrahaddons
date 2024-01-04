-- MySQL dump 10.13  Distrib 8.0.32, for Win64 (x86_64)
--
-- Host: localhost    Database: umrahaddons
-- ------------------------------------------------------
-- Server version	8.0.34-0ubuntu0.20.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `cities`
--

DROP TABLE IF EXISTS `cities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cities` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `state_id` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=48357 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `countries`
--

DROP TABLE IF EXISTS `countries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `countries` (
  `id` int NOT NULL AUTO_INCREMENT,
  `shortname` varchar(3) NOT NULL,
  `name` varchar(150) NOT NULL,
  `phonecode` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=247 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `languages`
--

DROP TABLE IF EXISTS `languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `languages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `created_date` varchar(35) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `meals_booking`
--

DROP TABLE IF EXISTS `meals_booking`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `meals_booking` (
  `id` int NOT NULL AUTO_INCREMENT,
  `provider_id` int NOT NULL,
  `meals_id` int NOT NULL,
  `user_id` int NOT NULL,
  `ota_id` int NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `mobile` varchar(25) NOT NULL,
  `no_of_person` int NOT NULL,
  `start_date` varchar(35) NOT NULL,
  `end_date` varchar(35) NOT NULL,
  `meals_type` varchar(35) NOT NULL,
  `meals_service` varchar(35) NOT NULL,
  `city` text NOT NULL,
  `lat` varchar(45) DEFAULT NULL,
  `long` varchar(45) DEFAULT NULL,
  `address` text NOT NULL,
  `cost_per_day_person` int NOT NULL,
  `no_of_days` int NOT NULL,
  `total_cost` varchar(50) NOT NULL,
  `notes` text NOT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `booking_status` varchar(35) NOT NULL DEFAULT 'pending',
  `reject_reason` text NOT NULL,
  `created_date` varchar(25) NOT NULL,
  `booking_status_user` enum('in-progress','confirm','failed','cancel') NOT NULL,
  `booking_status_stripe` enum('open','complete','failed','cancel') NOT NULL,
  `payment_status` enum('pending','confirm','completed','rejected') NOT NULL,
  `admin_commision` varchar(35) NOT NULL,
  `ota_commision` varchar(25) NOT NULL,
  `provider_commision` varchar(55) NOT NULL,
  `total_admin_comm_amount` varchar(35) NOT NULL,
  `remaining_admin_comm_amount` varchar(55) NOT NULL,
  `ota_commision_amount` varchar(25) NOT NULL,
  `provider_amount` varchar(55) NOT NULL,
  `ota_payment_status` enum('pending','unpaid','paid') NOT NULL,
  `provider_payment_status` enum('pending','paid','completed','rejected') NOT NULL,
  `session_id` text NOT NULL,
  `checkout_id` int NOT NULL,
  `remianing_amount_pay` varchar(55) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `version` varchar(255) NOT NULL,
  `class` text NOT NULL,
  `group` varchar(255) NOT NULL,
  `namespace` varchar(255) NOT NULL,
  `time` int NOT NULL,
  `batch` int unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=95 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `platform_code_counters`
--

DROP TABLE IF EXISTS `platform_code_counters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `platform_code_counters` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(20) NOT NULL,
  `prefix` varchar(10) NOT NULL,
  `next_value` varchar(30) NOT NULL DEFAULT '0',
  `code_min_length` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `states`
--

DROP TABLE IF EXISTS `states`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `states` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `country_id` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4122 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_activitie_image`
--

DROP TABLE IF EXISTS `tbl_activitie_image`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_activitie_image` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `activitie_id` bigint NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `activitie_img` varchar(255) NOT NULL,
  `created_date` varchar(50) NOT NULL,
  `updated_date` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_activities`
--

DROP TABLE IF EXISTS `tbl_activities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_activities` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `activitie_title` varchar(255) NOT NULL,
  `provider_id` bigint NOT NULL,
  `city_loaction` varchar(255) NOT NULL,
  `ideal_for` varchar(255) NOT NULL,
  `main_img` varchar(255) NOT NULL,
  `included` varchar(155) NOT NULL,
  `not_included` varchar(155) NOT NULL,
  `pickup_loaction` varchar(155) NOT NULL,
  `drop_loaction` varchar(155) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `status_by_admin` enum('active','inactive') NOT NULL DEFAULT 'active',
  `accommodations` enum('yes','no') NOT NULL DEFAULT 'yes',
  `accommodations_title` varchar(50) NOT NULL,
  `accommodations_detail` varchar(255) NOT NULL,
  `return_policy` varchar(255) NOT NULL,
  `type_of_activitie` enum('b2b','b2c','both') NOT NULL DEFAULT 'both',
  `activitie_amount` tinytext NOT NULL,
  `reason` varchar(255) NOT NULL,
  `language` tinytext NOT NULL,
  `created_date` varchar(50) NOT NULL,
  `updated_date` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_admin`
--

DROP TABLE IF EXISTS `tbl_admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_admin` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` text NOT NULL,
  `token` text NOT NULL,
  `profile_pic` text NOT NULL,
  `mobile` varchar(50) NOT NULL,
  `city` varchar(150) NOT NULL COMMENT 'User living city',
  `state` varchar(150) NOT NULL COMMENT 'User living state',
  `country` varchar(150) NOT NULL COMMENT 'User living country',
  `zip_code` varchar(50) NOT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_date` varchar(50) NOT NULL,
  `updated_date` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_admin_accounts`
--

DROP TABLE IF EXISTS `tbl_admin_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_admin_accounts` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `user_role` varchar(11) NOT NULL,
  `account_no` text NOT NULL,
  `account_type` varchar(50) NOT NULL,
  `bank_name` varchar(150) NOT NULL,
  `bank_branch` varchar(150) NOT NULL,
  `amount` bigint NOT NULL,
  `remark` varchar(255) NOT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_by` varchar(50) NOT NULL,
  `created_date` varchar(50) NOT NULL,
  `updated_date` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_admin_transactions`
--

DROP TABLE IF EXISTS `tbl_admin_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_admin_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` int NOT NULL,
  `user_id` int NOT NULL COMMENT 'ota, user, provider ID',
  `user_type` varchar(55) NOT NULL COMMENT 'save user role text',
  `transaction_type` enum('Dr','Cr') NOT NULL DEFAULT 'Dr',
  `service_type` enum('package','sabeel','meals') NOT NULL DEFAULT 'package',
  `service_id` int NOT NULL COMMENT 'package/activities Ids',
  `transaction_reason` varchar(255) NOT NULL,
  `currency_code` varchar(5) NOT NULL DEFAULT 'INR',
  `account_id` int NOT NULL,
  `old_balance` int NOT NULL,
  `transaction_amount` int NOT NULL,
  `current_balance` int NOT NULL,
  `transaction_id` varchar(55) NOT NULL,
  `transaction_status` varchar(55) NOT NULL COMMENT 'success/failed/pending/cancelled',
  `transaction_date` varchar(55) NOT NULL,
  `payment_method` varchar(55) NOT NULL,
  `created_date` varchar(50) NOT NULL,
  `updated_date` varchar(50) NOT NULL,
  `booking_id` int NOT NULL DEFAULT '0',
  `payment_session_id` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=257 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_booking`
--

DROP TABLE IF EXISTS `tbl_booking`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_booking` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `service_type` enum('package','activitie') NOT NULL DEFAULT 'package',
  `service_id` bigint NOT NULL,
  `user_id` bigint NOT NULL,
  `user_role` varchar(20) NOT NULL,
  `from_date` varchar(255) NOT NULL,
  `time` varchar(255) NOT NULL,
  `no_of_pox` bigint NOT NULL,
  `user_pax` bigint NOT NULL,
  `action_by` enum('admin','provider') NOT NULL DEFAULT 'provider',
  `action_by_id` bigint NOT NULL,
  `action` enum('pending','confirm','completed','rejected') NOT NULL DEFAULT 'pending',
  `cars` varchar(20) NOT NULL,
  `rate` varchar(20) NOT NULL,
  `provider_id` bigint NOT NULL,
  `ota_id` bigint NOT NULL,
  `booked_time` varchar(200) NOT NULL,
  `booked_date` varchar(200) NOT NULL,
  `booking_status` varchar(55) NOT NULL DEFAULT 'pending',
  `reject_reason` text NOT NULL,
  `booking_status_user` enum('in-progress','confirm','failed','cancel') NOT NULL DEFAULT 'in-progress',
  `booking_status_stripe` enum('open','complete','failed','cancel') NOT NULL DEFAULT 'open',
  `payment_status` enum('pending','confirm','completed','rejected') NOT NULL DEFAULT 'pending',
  `admin_commision` varchar(10) NOT NULL COMMENT 'admin commision',
  `ota_commision` varchar(10) NOT NULL COMMENT 'admin commision',
  `provider_commision` varchar(10) NOT NULL COMMENT 'admin commision',
  `total_admin_comm_amount` varchar(20) NOT NULL COMMENT 'admin amount ',
  `remaining_admin_comm_amount` varchar(20) NOT NULL COMMENT 'Remaining admin commision amount ',
  `ota_commision_amount` varchar(20) NOT NULL COMMENT 'ota amount',
  `provider_amount` varchar(20) NOT NULL COMMENT 'provider amount',
  `ota_payment_status` enum('pending','paid','completed','rejected') NOT NULL DEFAULT 'pending',
  `provider_payment_status` enum('pending','paid','completed','rejected') NOT NULL DEFAULT 'pending',
  `created_date` varchar(50) NOT NULL,
  `updated_date` varchar(50) NOT NULL,
  `session_id` text,
  `checkout_id` int DEFAULT NULL,
  `guest_fullname` varchar(50) DEFAULT NULL,
  `guest_contact_no` varchar(20) DEFAULT NULL,
  `guest_email` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_booking_payment_record`
--

DROP TABLE IF EXISTS `tbl_booking_payment_record`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_booking_payment_record` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `service_type` varchar(20) NOT NULL COMMENT 'Package / Activities',
  `sevice_id` int NOT NULL COMMENT 'ota, provider, admin ID',
  `booking_id` int NOT NULL COMMENT 'ota, provider, admin ID',
  `user_id` int NOT NULL COMMENT 'user id',
  `Provider_id` int NOT NULL COMMENT 'provider id',
  `ota_id` int NOT NULL COMMENT 'ota id',
  `package_rate` varchar(20) NOT NULL COMMENT 'Package Amount',
  `admin_commision` varchar(10) NOT NULL COMMENT 'admin commision',
  `ota_commision` varchar(10) NOT NULL COMMENT 'admin commision',
  `provider_commision` varchar(10) NOT NULL COMMENT 'admin commision',
  `admin_amount` varchar(20) NOT NULL COMMENT 'admin amount ',
  `ota_amount` varchar(20) NOT NULL COMMENT 'ota amount',
  `provider_amount` varchar(20) NOT NULL COMMENT 'provider amount',
  `date` varchar(55) NOT NULL,
  `created_date` varchar(50) NOT NULL,
  `updated_date` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_cuision_master`
--

DROP TABLE IF EXISTS `tbl_cuision_master`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_cuision_master` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `created_date` varchar(50) NOT NULL,
  `updated_date` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_duas`
--

DROP TABLE IF EXISTS `tbl_duas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_duas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `user_type` varchar(50) NOT NULL DEFAULT 'admin',
  `title_en` varchar(250) NOT NULL,
  `reference_en` longtext NOT NULL,
  `title_ur` varchar(250) NOT NULL,
  `reference_ur` longtext NOT NULL,
  `image` text NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'umrah',
  `status` tinyint NOT NULL DEFAULT '1',
  `created_at` varchar(50) NOT NULL,
  `updated_at` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_full_package`
--

DROP TABLE IF EXISTS `tbl_full_package`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_full_package` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `provider_id` bigint NOT NULL,
  `name` varchar(255) NOT NULL,
  `duration` varchar(100) NOT NULL,
  `departure_city` varchar(255) NOT NULL,
  `mecca_hotel` varchar(255) NOT NULL,
  `mecca_hotel_distance` varchar(255) NOT NULL,
  `madinah_hotel` varchar(255) NOT NULL,
  `madinah_hotel_distance` varchar(255) NOT NULL,
  `details` text NOT NULL,
  `main_img` varchar(255) NOT NULL,
  `inclusions` text NOT NULL,
  `single_rate_SAR` varchar(255) NOT NULL,
  `single_rate_INR` varchar(255) NOT NULL,
  `double_rate_SAR` varchar(255) NOT NULL,
  `double_rate_INR` varchar(255) NOT NULL,
  `triple_rate_SAR` varchar(255) NOT NULL,
  `triple_rate_INR` varchar(255) NOT NULL,
  `quad_rate_SAR` varchar(255) NOT NULL,
  `quad_rate_INR` varchar(255) NOT NULL,
  `pent_rate_SAR` varchar(255) NOT NULL,
  `pent_rate_INR` varchar(255) NOT NULL,
  `infant_rate_with_bed_SAR` varchar(255) NOT NULL,
  `infant_rate_with_bed_INR` varchar(255) NOT NULL,
  `infant_rate_without_bed_SAR` varchar(255) NOT NULL,
  `infant_rate_without_bed_INR` varchar(255) NOT NULL,
  `status` enum('1','0','2') NOT NULL DEFAULT '1',
  `created_at` varchar(50) NOT NULL,
  `updated_at` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_full_package_dates`
--

DROP TABLE IF EXISTS `tbl_full_package_dates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_full_package_dates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `full_package_id` bigint NOT NULL,
  `city` varchar(255) NOT NULL,
  `departure_date` varchar(255) NOT NULL,
  `arrival_date` varchar(255) NOT NULL,
  `days` bigint NOT NULL,
  `created_at` varchar(50) NOT NULL,
  `updated_at` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_full_package_enquiry`
--

DROP TABLE IF EXISTS `tbl_full_package_enquiry`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_full_package_enquiry` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `ota_id` int NOT NULL,
  `full_package_id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `country_code` varchar(35) NOT NULL,
  `mobile` varchar(35) NOT NULL,
  `no_of_seats` float NOT NULL,
  `date` varchar(100) NOT NULL,
  `booking_status` enum('pending','accepted','rejected') NOT NULL DEFAULT 'pending',
  `reject_reason` varchar(35) NOT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `created_at` varchar(50) NOT NULL,
  `updated_at` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_full_package_image`
--

DROP TABLE IF EXISTS `tbl_full_package_image`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_full_package_image` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `full_package_id` bigint NOT NULL,
  `image` varchar(255) NOT NULL,
  `created_at` varchar(50) NOT NULL,
  `updated_at` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=125 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_full_package_inclusions`
--

DROP TABLE IF EXISTS `tbl_full_package_inclusions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_full_package_inclusions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `created_at` varchar(50) NOT NULL,
  `updated_at` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_guide`
--

DROP TABLE IF EXISTS `tbl_guide`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_guide` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `contact` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `is_verify` enum('yes','no') NOT NULL DEFAULT 'yes',
  `reason` text NOT NULL COMMENT 'if guide will reject then admin give a reason',
  `token` text NOT NULL,
  `language` varchar(100) NOT NULL COMMENT 'language known by guide',
  `profile_pic` text NOT NULL,
  `cover_pic` text NOT NULL,
  `govt_id_doc` text NOT NULL,
  `dob` varchar(20) NOT NULL,
  `nationality` varchar(20) NOT NULL,
  `education` varchar(255) NOT NULL,
  `experience` varchar(255) NOT NULL,
  `home_address` text NOT NULL COMMENT 'address of guide',
  `city` varchar(30) NOT NULL COMMENT 'address city of guide',
  `country` varchar(30) NOT NULL COMMENT 'address country of guide',
  `about_us` text NOT NULL COMMENT 'description about gudie',
  `updated_date` varchar(50) NOT NULL,
  `created_date` varchar(50) NOT NULL,
  `device_type` varchar(55) NOT NULL,
  `device_token` varchar(255) NOT NULL,
  `is_deleted` tinyint NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_guide_doc`
--

DROP TABLE IF EXISTS `tbl_guide_doc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_guide_doc` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `guide_id` bigint NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `guide_doc` varchar(255) NOT NULL,
  `created_date` varchar(50) NOT NULL,
  `updated_date` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_guide_enquiry`
--

DROP TABLE IF EXISTS `tbl_guide_enquiry`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_guide_enquiry` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `guide_id` int NOT NULL,
  `user_id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `mobile` varchar(255) NOT NULL,
  `start_date` varchar(35) NOT NULL,
  `end_date` varchar(35) NOT NULL,
  `no_of_person` int NOT NULL,
  `package_duration` varchar(255) NOT NULL,
  `notes` text NOT NULL,
  `booking_status` varchar(55) NOT NULL DEFAULT 'pending',
  `reject_reason` text NOT NULL,
  `booking_action` varchar(55) NOT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `created_date` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_ideal_master`
--

DROP TABLE IF EXISTS `tbl_ideal_master`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_ideal_master` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_date` varchar(50) NOT NULL,
  `updated_date` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_included_master`
--

DROP TABLE IF EXISTS `tbl_included_master`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_included_master` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_date` varchar(50) NOT NULL,
  `updated_date` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_meals`
--

DROP TABLE IF EXISTS `tbl_meals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_meals` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `cuisine_id` varchar(255) NOT NULL,
  `menu_url` text NOT NULL,
  `no_of_person` int NOT NULL,
  `cost_per_meals` varchar(255) NOT NULL,
  `cost_per_day` varchar(255) NOT NULL,
  `meals_type` enum('tiffin','group') NOT NULL,
  `meals_service` enum('pickup','deliver') NOT NULL,
  `pickup_address` text NOT NULL,
  `cities` text NOT NULL,
  `img_url_1` text NOT NULL,
  `img_url_2` text NOT NULL,
  `img_url_3` text NOT NULL,
  `thumbnail_url` text NOT NULL,
  `status` enum('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `provider_id` int NOT NULL,
  `created_date` varchar(50) NOT NULL,
  `updated_date` varchar(50) NOT NULL,
  `provider_lat` varchar(255) NOT NULL,
  `provider_long` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_meals_enquiry`
--

DROP TABLE IF EXISTS `tbl_meals_enquiry`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_meals_enquiry` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `provider_id` int NOT NULL,
  `meals_id` int NOT NULL,
  `user_id` int NOT NULL,
  `ota_id` int NOT NULL,
  `start_date` varchar(35) NOT NULL,
  `end_date` varchar(35) NOT NULL,
  `meals_type` varchar(35) NOT NULL,
  `meals_service` varchar(35) NOT NULL,
  `no_of_person` int NOT NULL,
  `notes` text NOT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `created_date` varchar(50) NOT NULL,
  `booking_status` varchar(50) NOT NULL DEFAULT 'pending',
  `reject_reason` text NOT NULL,
  `booking_action` varchar(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `mobile` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_meals_menus`
--

DROP TABLE IF EXISTS `tbl_meals_menus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_meals_menus` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `meals_id` bigint NOT NULL,
  `menu_url` varchar(255) NOT NULL,
  `created_date` varchar(50) NOT NULL,
  `updated_date` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_not_included_master`
--

DROP TABLE IF EXISTS `tbl_not_included_master`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_not_included_master` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_date` varchar(50) NOT NULL,
  `updated_date` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_ota`
--

DROP TABLE IF EXISTS `tbl_ota`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_ota` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `firstname` varchar(255) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `domain_name` varchar(255) NOT NULL,
  `domain_type` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` text NOT NULL,
  `plain_password` text NOT NULL,
  `mobile` varchar(50) NOT NULL,
  `bank_account` varchar(30) NOT NULL,
  `dob` varchar(20) NOT NULL,
  `ipsc` varchar(20) NOT NULL,
  `gender` enum('Male','Female') NOT NULL DEFAULT 'Male',
  `profile_pic` varchar(150) NOT NULL,
  `city` varchar(150) NOT NULL COMMENT 'User living city',
  `state` varchar(150) NOT NULL COMMENT 'User living state',
  `country` varchar(150) NOT NULL COMMENT 'User living country',
  `zip_code` varchar(50) NOT NULL,
  `token` text NOT NULL,
  `user_role` varchar(55) NOT NULL,
  `commision_percent` varchar(255) NOT NULL,
  `document` varchar(255) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `supporter_no` varchar(20) NOT NULL,
  `supporter_email` varchar(20) NOT NULL,
  `website_link` varchar(50) NOT NULL,
  `facebook_link` varchar(50) NOT NULL,
  `created_by` tinytext NOT NULL,
  `created_date` varchar(50) NOT NULL,
  `updated_date` varchar(50) NOT NULL,
  `bank_name` varchar(255) NOT NULL,
  `branch_name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_ota_provider_account`
--

DROP TABLE IF EXISTS `tbl_ota_provider_account`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_ota_provider_account` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_role` varchar(20) NOT NULL COMMENT 'Package / Activities',
  `user_id` int NOT NULL COMMENT 'ota, provider ID',
  `total_amount` varchar(20) NOT NULL COMMENT 'Total Amount Till Now',
  `pending_amount` varchar(20) NOT NULL COMMENT 'Pending Amount ',
  `withdrawal_amount` varchar(20) NOT NULL COMMENT 'Withdrawal Amount Till Now',
  `created_date` varchar(50) NOT NULL,
  `updated_date` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_package`
--

DROP TABLE IF EXISTS `tbl_package`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_package` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `package_title` varchar(255) NOT NULL,
  `package_details` text NOT NULL,
  `city_loaction` varchar(255) NOT NULL,
  `ideal_for` varchar(255) NOT NULL,
  `main_img` varchar(255) NOT NULL,
  `included` varchar(155) NOT NULL,
  `not_included` varchar(155) NOT NULL,
  `pickup_loaction` varchar(155) NOT NULL,
  `drop_loaction` varchar(155) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `status_by_admin` enum('active','inactive') NOT NULL DEFAULT 'active',
  `provider_id` bigint NOT NULL,
  `accommodations` enum('yes','no') NOT NULL DEFAULT 'yes',
  `accommodations_title` varchar(50) NOT NULL,
  `accommodations_detail` varchar(255) NOT NULL,
  `return_policy` varchar(255) NOT NULL,
  `type_of_package` enum('b2b','b2c','both') NOT NULL DEFAULT 'both',
  `package_amount` tinytext NOT NULL,
  `package_duration` varchar(35) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `language` tinytext NOT NULL,
  `created_date` varchar(50) NOT NULL,
  `updated_date` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_package_day_mapping`
--

DROP TABLE IF EXISTS `tbl_package_day_mapping`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_package_day_mapping` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `package_id` int NOT NULL COMMENT 'Package ID',
  `movement_id` int NOT NULL COMMENT 'Movement Table ID',
  `time` varchar(50) NOT NULL,
  `description` varchar(255) NOT NULL,
  `day` varchar(20) NOT NULL COMMENT 'Number Of Days',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_date` varchar(50) NOT NULL,
  `updated_date` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_package_duration`
--

DROP TABLE IF EXISTS `tbl_package_duration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_package_duration` (
  `id` int NOT NULL AUTO_INCREMENT,
  `duration` varchar(255) NOT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `created_date` varchar(35) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_package_enquiry`
--

DROP TABLE IF EXISTS `tbl_package_enquiry`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_package_enquiry` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `provider_id` int NOT NULL,
  `ota_id` int NOT NULL,
  `package_id` int NOT NULL,
  `from_date` varchar(35) NOT NULL,
  `no_of_pax` int NOT NULL,
  `package_amount` varchar(50) NOT NULL,
  `total_amount` varchar(35) NOT NULL,
  `full_name` text NOT NULL,
  `email_address` text NOT NULL,
  `country` varchar(35) NOT NULL,
  `mobile` varchar(35) NOT NULL,
  `booking_status` varchar(35) NOT NULL DEFAULT 'pending',
  `reject_reason` text NOT NULL,
  `action_by` varchar(35) NOT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `created_date` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_package_image`
--

DROP TABLE IF EXISTS `tbl_package_image`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_package_image` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `package_id` bigint NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `package_imgs` varchar(255) NOT NULL,
  `created_date` varchar(50) NOT NULL,
  `updated_date` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=153 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_package_movment`
--

DROP TABLE IF EXISTS `tbl_package_movment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_package_movment` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `package_id` bigint NOT NULL,
  `day` varchar(50) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `language` tinytext NOT NULL,
  `created_date` varchar(50) NOT NULL,
  `updated_date` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_package_vehicle`
--

DROP TABLE IF EXISTS `tbl_package_vehicle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_package_vehicle` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `package_id` bigint NOT NULL,
  `no_of_pox_id` varchar(20) NOT NULL,
  `vehicle_id` bigint NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `rate` tinytext NOT NULL,
  `created_date` varchar(50) NOT NULL,
  `updated_date` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_pax_master`
--

DROP TABLE IF EXISTS `tbl_pax_master`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_pax_master` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `min_pax` bigint NOT NULL,
  `max_pax` bigint NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_date` varchar(50) NOT NULL,
  `updated_date` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_payment_checkout`
--

DROP TABLE IF EXISTS `tbl_payment_checkout`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_payment_checkout` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `session_id` text NOT NULL,
  `object` varchar(20) NOT NULL,
  `amount_total` varchar(10) NOT NULL,
  `customer_stripe_email` varchar(255) NOT NULL,
  `customer_stripe_id` varchar(50) NOT NULL,
  `customer_stripe_name` varchar(50) NOT NULL,
  `currency` varchar(9) NOT NULL,
  `payment_intent` varchar(20) DEFAULT NULL,
  `payment_status` varchar(20) NOT NULL,
  `stripe_status` varchar(30) NOT NULL,
  `url` text NOT NULL,
  `customer_details` text NOT NULL,
  `user_id` int NOT NULL,
  `user_role` varchar(20) NOT NULL,
  `ota_id` int NOT NULL,
  `service_id` int NOT NULL,
  `service_type` varchar(20) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_date` varchar(50) NOT NULL,
  `updated_date` varchar(50) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mobile_no` varchar(15) NOT NULL,
  `guest_fullname` varchar(50) DEFAULT NULL,
  `guest_contact_no` varchar(20) DEFAULT NULL,
  `guest_email` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=116 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_provider`
--

DROP TABLE IF EXISTS `tbl_provider`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_provider` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `firstname` varchar(255) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` text NOT NULL,
  `plain_password` text NOT NULL,
  `mobile` varchar(50) NOT NULL,
  `bank_account` varchar(30) NOT NULL,
  `dob` varchar(20) NOT NULL,
  `ipsc` varchar(50) NOT NULL,
  `gender` enum('Male','Female') NOT NULL DEFAULT 'Male',
  `profile_pic` varchar(150) NOT NULL,
  `city` varchar(150) NOT NULL COMMENT 'User living city',
  `state` varchar(150) NOT NULL COMMENT 'User living state',
  `country` varchar(150) NOT NULL COMMENT 'User living country',
  `zip_code` varchar(50) NOT NULL,
  `token` text NOT NULL,
  `user_role` varchar(55) NOT NULL,
  `commision_percent` varchar(255) NOT NULL,
  `document` varchar(255) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `supporter_no` varchar(20) DEFAULT NULL,
  `created_by` tinytext NOT NULL,
  `created_date` varchar(50) NOT NULL,
  `updated_date` varchar(50) NOT NULL,
  `bank_name` varchar(255) NOT NULL,
  `branch_name` varchar(255) NOT NULL,
  `device_type` varchar(55) NOT NULL,
  `device_token` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_sabeel`
--

DROP TABLE IF EXISTS `tbl_sabeel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_sabeel` (
  `id` int NOT NULL AUTO_INCREMENT,
  `provider_id` int NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `price` varchar(55) NOT NULL,
  `photo` text NOT NULL,
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '0:delete,1:active,2:inactive',
  `created_date` varchar(35) NOT NULL,
  `updated_date` varchar(35) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_sabeel_booking`
--

DROP TABLE IF EXISTS `tbl_sabeel_booking`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_sabeel_booking` (
  `id` int NOT NULL AUTO_INCREMENT,
  `provider_id` int NOT NULL,
  `sabeel_id` int NOT NULL,
  `user_id` int NOT NULL,
  `ota_id` int NOT NULL,
  `full_name` varchar(250) NOT NULL,
  `mobile` varchar(55) NOT NULL,
  `total_price` varchar(50) NOT NULL,
  `quantity` varchar(50) NOT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `booking_status` varchar(35) NOT NULL,
  `reject_reason` text NOT NULL,
  `created_date` varchar(25) NOT NULL,
  `booking_status_user` enum('in-progress','confirm','failed','cancel') NOT NULL,
  `booking_status_stripe` enum('open','complete','failed','cancel') NOT NULL,
  `payment_status` enum('pending','confirm','completed','rejected') NOT NULL,
  `admin_commision` varchar(35) NOT NULL,
  `ota_commision` varchar(25) NOT NULL,
  `provider_commision` varchar(35) NOT NULL,
  `total_admin_comm_amount` varchar(25) NOT NULL,
  `remaining_admin_comm_amount` varchar(35) NOT NULL,
  `ota_commision_amount` varchar(25) NOT NULL,
  `provider_amount` varchar(25) NOT NULL,
  `ota_payment_status` enum('pending','unpaid','paid') NOT NULL,
  `provider_payment_status` enum('pending','paid','completed','rejected') NOT NULL,
  `session_id` text NOT NULL,
  `checkout_id` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_service_commision_mapping`
--

DROP TABLE IF EXISTS `tbl_service_commision_mapping`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_service_commision_mapping` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `service_id` int NOT NULL,
  `service_type` varchar(20) NOT NULL,
  `user_id` int NOT NULL,
  `user_role` varchar(20) NOT NULL,
  `commision_in_percent` bigint NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_date` varchar(50) NOT NULL,
  `updated_date` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=88 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_service_master`
--

DROP TABLE IF EXISTS `tbl_service_master`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_service_master` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_transport_enquiry`
--

DROP TABLE IF EXISTS `tbl_transport_enquiry`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_transport_enquiry` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `ota_id` int NOT NULL,
  `vehicle_type` varchar(35) NOT NULL,
  `from_city` varchar(255) NOT NULL,
  `to_city` varchar(255) NOT NULL,
  `date` varchar(35) NOT NULL,
  `time` varchar(35) NOT NULL,
  `name` text NOT NULL,
  `mobile` varchar(35) NOT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `created_date` varchar(50) NOT NULL,
  `booking_status` varchar(50) NOT NULL DEFAULT 'pending',
  `reject_reason` text NOT NULL,
  `booking_action` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_user`
--

DROP TABLE IF EXISTS `tbl_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `firstname` varchar(255) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `country_code` varchar(45) NOT NULL,
  `mobile` varchar(50) NOT NULL,
  `password` text NOT NULL,
  `plain_password` text NOT NULL,
  `dob` varchar(20) NOT NULL,
  `gender` enum('Male','Female') NOT NULL DEFAULT 'Male',
  `profile_pic` varchar(150) NOT NULL,
  `city` varchar(150) NOT NULL COMMENT 'User living city',
  `state` varchar(150) NOT NULL COMMENT 'User living state',
  `country` varchar(150) NOT NULL COMMENT 'User living country',
  `zip_code` varchar(50) NOT NULL,
  `token` text NOT NULL,
  `user_role` varchar(55) NOT NULL,
  `id_prrof` varchar(255) NOT NULL,
  `document` varchar(255) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_by` tinytext NOT NULL,
  `otp` tinytext NOT NULL,
  `device_type` tinytext NOT NULL,
  `device_token` varchar(255) NOT NULL,
  `created_date` varchar(50) NOT NULL,
  `updated_date` varchar(50) NOT NULL,
  `created_by_id` int NOT NULL,
  `created_by_role` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=98 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_user_transactions`
--

DROP TABLE IF EXISTS `tbl_user_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_user_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `user_id` int NOT NULL COMMENT 'ota, provider, admin ID',
  `user_type` varchar(55) NOT NULL COMMENT 'save user role text',
  `transaction_type` enum('Dr','Cr') NOT NULL DEFAULT 'Dr',
  `transaction_reason` varchar(255) NOT NULL,
  `currency_code` varchar(5) NOT NULL DEFAULT 'INR',
  `transaction_amount` int NOT NULL,
  `transaction_id` varchar(55) NOT NULL,
  `transaction_status` varchar(55) NOT NULL COMMENT 'success/failed/pending/cancelled',
  `transaction_date` varchar(55) NOT NULL,
  `payment_method` varchar(55) NOT NULL,
  `service_type` enum('package','activities') NOT NULL DEFAULT 'package',
  `service_id` int NOT NULL COMMENT 'package/activities Ids',
  `created_date` varchar(50) NOT NULL,
  `updated_date` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=278 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_users_app_version`
--

DROP TABLE IF EXISTS `tbl_users_app_version`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_users_app_version` (
  `id` int NOT NULL AUTO_INCREMENT,
  `app_version_android` varchar(20) NOT NULL,
  `forcefully_update_android` int NOT NULL,
  `update_datetime_android` varchar(35) NOT NULL,
  `app_version_ios` int NOT NULL,
  `forcefully_update_ios` int NOT NULL,
  `update_datetime_ios` varchar(35) NOT NULL,
  `app_name` varchar(35) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_vehicle_master`
--

DROP TABLE IF EXISTS `tbl_vehicle_master`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_vehicle_master` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_date` varchar(50) NOT NULL,
  `updated_date` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_visa`
--

DROP TABLE IF EXISTS `tbl_visa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_visa` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `currency` varchar(50) NOT NULL DEFAULT '₹',
  `price` float NOT NULL DEFAULT '0',
  `duration` varchar(250) NOT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `created_at` varchar(50) NOT NULL,
  `updated_at` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_visa_enquiry`
--

DROP TABLE IF EXISTS `tbl_visa_enquiry`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_visa_enquiry` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `ota_id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `country_code` varchar(35) NOT NULL,
  `mobile` varchar(35) NOT NULL,
  `no_of_persons` float NOT NULL,
  `booking_status` enum('pending','accepted','rejected') NOT NULL DEFAULT 'pending',
  `reject_reason` varchar(35) NOT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `created_at` varchar(50) NOT NULL,
  `updated_at` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `webhook_check`
--

DROP TABLE IF EXISTS `webhook_check`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `webhook_check` (
  `id` int NOT NULL AUTO_INCREMENT,
  `payment_id` text NOT NULL,
  `name` text NOT NULL,
  `amount` int NOT NULL,
  `event_name` varchar(255) NOT NULL,
  `created_date` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2023-10-05  9:13:46
