-- MySQL dump 10.13  Distrib 5.7.17, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: coord
-- ------------------------------------------------------
-- Server version	5.6.38

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `app_accounts`
--

DROP TABLE IF EXISTS `app_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_accounts` (
  `accountID` int(11) NOT NULL AUTO_INCREMENT,
  `planID` int(11) NOT NULL,
  `byID` int(11) DEFAULT NULL,
  `active` tinyint(1) DEFAULT '1',
  `admin` tinyint(1) NOT NULL DEFAULT '0',
  `demo_data_imported` tinyint(1) DEFAULT '0',
  `status` enum('trial','paid') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'trial',
  `company` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `contact` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `organisation_size` int(11) NOT NULL DEFAULT '0',
  `api_key` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `addon_resources` tinyint(1) NOT NULL DEFAULT '0',
  `addon_messages` tinyint(1) NOT NULL DEFAULT '0',
  `addon_equipment` tinyint(1) NOT NULL DEFAULT '0',
  `addon_sms` tinyint(1) NOT NULL DEFAULT '0',
  `addon_bookings_events` tinyint(1) NOT NULL DEFAULT '0',
  `addon_bookings_bookings` tinyint(1) NOT NULL DEFAULT '0',
  `addon_bookings_projects` tinyint(1) NOT NULL DEFAULT '0',
  `addon_safety` tinyint(1) NOT NULL DEFAULT '0',
  `addon_staff_id` tinyint(1) NOT NULL DEFAULT '0',
  `addon_export` tinyint(1) NOT NULL DEFAULT '0',
  `addon_online_booking` tinyint(1) NOT NULL DEFAULT '0',
  `addon_attachments_editing` tinyint(1) NOT NULL DEFAULT '0',
  `addon_reports` tinyint(1) NOT NULL DEFAULT '0',
  `addon_whitelabel` tinyint(1) NOT NULL DEFAULT '0',
  `addon_bookings_timetable_confirmation` tinyint(1) NOT NULL DEFAULT '0',
  `addon_timesheets` tinyint(1) NOT NULL DEFAULT '0',
  `addon_expenses` tinyint(1) NOT NULL DEFAULT '0',
  `addon_offer_accept` tinyint(1) NOT NULL DEFAULT '0',
  `addon_staff_invoices` tinyint(1) NOT NULL DEFAULT '0',
  `addon_availability_cals` tinyint(1) NOT NULL DEFAULT '0',
  `addon_staff_lesson_uploads` tinyint(1) NOT NULL DEFAULT '0',
  `addon_session_evaluations` tinyint(1) NOT NULL DEFAULT '0',
  `addon_staff_performance` tinyint(1) NOT NULL DEFAULT '0',
  `trial_until` date DEFAULT NULL,
  `paid_until` date DEFAULT NULL,
  `added` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`accountID`),
  KEY `fk_accounts_byID` (`byID`),
  KEY `fk_accounts_planID` (`planID`),
  CONSTRAINT `app_accounts_ibfk_1` FOREIGN KEY (`planID`) REFERENCES `app_accounts_plans` (`planID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_accounts_ibfk_2` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=119 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_accounts`
--

LOCK TABLES `app_accounts` WRITE;
/*!40000 ALTER TABLE `app_accounts` DISABLE KEYS */;
INSERT INTO `app_accounts` VALUES (10,7,1,1,1,0,'paid','Admin Account','Demo Admin','info@i-coordinate.co.uk','',0,'CwydSMgD0ZpYrOAJ5L3vHqQasF81WzxU',0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,NULL,NULL,'2015-10-20 16:04:50','2015-10-20 16:04:50'),(23,11,208,1,0,0,'paid','Demo Data','Support Adminstrator','demo@coordinate.cloud','',0,'SnKBXRJhYxsrPzmy2cvwQuZektVWU6p1',1,1,1,1,1,0,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,NULL,NULL,'2016-04-13 10:09:12','2018-04-04 18:07:52');
/*!40000 ALTER TABLE `app_accounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_accounts_plans`
--

DROP TABLE IF EXISTS `app_accounts_plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_accounts_plans` (
  `planID` int(11) NOT NULL AUTO_INCREMENT,
  `byID` int(11) DEFAULT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `bookings_timetable` tinyint(1) NOT NULL DEFAULT '0',
  `bookings_timetable_own` tinyint(1) DEFAULT '1',
  `bookings_bookings` tinyint(1) NOT NULL DEFAULT '0',
  `bookings_events` tinyint(1) NOT NULL DEFAULT '0',
  `bookings_projects` tinyint(1) NOT NULL DEFAULT '0',
  `bookings_exceptions` tinyint(1) NOT NULL DEFAULT '0',
  `customers_schools` tinyint(1) NOT NULL DEFAULT '0',
  `customers_schools_prospects` tinyint(1) NOT NULL DEFAULT '0',
  `customers_orgs` tinyint(1) NOT NULL DEFAULT '0',
  `customers_orgs_prospects` tinyint(1) NOT NULL DEFAULT '0',
  `participants` tinyint(1) NOT NULL DEFAULT '0',
  `staff_management` tinyint(1) NOT NULL DEFAULT '0',
  `settings` tinyint(1) NOT NULL DEFAULT '0',
  `addons_all` tinyint(1) NOT NULL DEFAULT '0',
  `label_customer` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `label_customers` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `label_participant` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `label_participants` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `default_project_types` varchar(200) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Commercial\nFunded',
  `dashboard_bookings` tinyint(1) DEFAULT '1',
  `dashboard_staff` tinyint(1) DEFAULT '1',
  `dashboard_participants` tinyint(1) DEFAULT '1',
  `dashboard_health_safety` tinyint(1) DEFAULT '1',
  `dashboard_equipment` tinyint(1) DEFAULT '1',
  `dashboard_availability` tinyint(1) DEFAULT '1',
  `dashboard_employee_of_month` tinyint(1) DEFAULT '1',
  `dashboard_staff_birthdays` tinyint(1) DEFAULT '1',
  `label_brand` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `label_brands` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `added` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`planID`),
  KEY `fk_accounts_plans_byID` (`byID`),
  CONSTRAINT `app_accounts_plans_ibfk_1` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_accounts_plans`
--

LOCK TABLES `app_accounts_plans` WRITE;
/*!40000 ALTER TABLE `app_accounts_plans` DISABLE KEYS */;
INSERT INTO `app_accounts_plans` VALUES (7,NULL,'No Access',0,1,0,0,0,0,0,0,0,0,0,0,0,0,NULL,NULL,NULL,NULL,'Commercial\nFunded',1,1,1,1,1,1,1,1,NULL,NULL,'2015-10-06 10:34:04','2015-10-06 12:47:22'),(8,NULL,'Coordinate',1,1,1,1,0,1,1,1,1,1,0,1,1,0,NULL,NULL,NULL,NULL,'Commercial\nFunded',1,1,1,1,1,1,1,1,NULL,NULL,'2015-10-06 10:55:21','2016-03-01 11:46:24'),(9,NULL,'Enhanced (without bexc)',1,1,1,1,1,0,1,1,1,1,1,1,1,0,NULL,NULL,NULL,NULL,'Commercial\r\nFunded',1,1,1,1,1,1,1,1,NULL,NULL,'2015-10-06 10:55:28','2017-10-11 09:11:35'),(10,NULL,'Events',1,1,0,1,1,0,0,0,1,0,1,0,0,0,NULL,NULL,NULL,NULL,'Commercial\nFunded',1,1,1,1,1,1,1,1,NULL,NULL,'2015-10-06 10:55:33','2015-10-06 10:56:56'),(11,NULL,'Full Functionality',1,1,1,1,1,1,1,1,1,1,1,1,1,0,NULL,NULL,NULL,NULL,'Commercial\nFunded',1,1,1,1,1,1,1,1,NULL,NULL,'2015-10-06 10:55:38','2017-11-01 15:05:51'),(13,NULL,'Schools',1,0,0,1,1,0,0,0,1,0,1,0,0,0,'Venue','Venues','Pupil','Pupils','Extra Curricular\r\nExcursions\r\nEvents\r\nEnrichment',0,0,1,1,1,0,0,1,'Faculty','Faculties','2015-10-06 10:55:52','2016-09-21 19:03:23'),(15,208,'Cycle Training',1,1,0,0,1,0,1,0,1,0,1,1,1,0,NULL,NULL,NULL,NULL,'Commercial\nFunded',1,1,1,1,1,1,1,1,NULL,NULL,'2016-02-01 11:42:58','2016-02-01 11:45:43'),(16,208,'Lolly pops',1,1,0,0,1,1,0,0,1,0,0,1,1,0,NULL,NULL,NULL,NULL,'Commercial\nFunded',1,1,1,1,1,1,1,1,NULL,NULL,'2016-06-09 11:16:06','2016-06-09 11:16:06'),(17,235,'Basic',1,1,1,0,0,1,1,1,1,1,1,1,1,0,NULL,NULL,NULL,NULL,'Commercial\nFunded',1,1,1,1,1,1,1,1,NULL,NULL,'2016-06-23 14:06:31','2016-06-23 14:06:31'),(18,235,'Standard',1,1,1,0,0,1,1,1,1,1,1,1,1,0,NULL,NULL,NULL,NULL,'Commercial\nFunded',1,1,1,1,1,1,1,1,NULL,NULL,'2016-06-23 14:19:54','2016-06-23 14:19:54'),(19,235,'Enhanced',1,1,1,0,1,1,1,1,1,1,1,1,1,0,NULL,NULL,NULL,NULL,'Commercial\nFunded',1,1,1,1,1,1,1,1,NULL,NULL,'2016-06-23 14:20:31','2016-07-11 14:07:02'),(20,235,'Pro',1,1,1,0,1,1,1,1,1,1,1,1,1,0,NULL,NULL,NULL,NULL,'Commercial\nFunded',1,1,1,1,1,1,1,1,NULL,NULL,'2016-06-23 14:20:52','2017-11-01 15:05:46');
/*!40000 ALTER TABLE `app_accounts_plans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_accounts_settings`
--

DROP TABLE IF EXISTS `app_accounts_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_accounts_settings` (
  `settingID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `key` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `value` text COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`settingID`),
  KEY `fk_accounts_settings_accountID` (`accountID`),
  CONSTRAINT `app_accounts_settings_ibfk_1` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1776 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_accounts_settings`
--

LOCK TABLES `app_accounts_settings` WRITE;
/*!40000 ALTER TABLE `app_accounts_settings` DISABLE KEYS */;
INSERT INTO `app_accounts_settings` VALUES (35,10,'body_colour','white','2015-10-20 16:04:50','2015-10-20 16:04:50'),(36,10,'contrast_colour','light','2015-10-20 16:04:50','2015-10-20 16:04:50'),(166,23,'body_colour','white','2016-04-13 10:09:13','2018-04-04 18:07:52'),(167,23,'contrast_colour','light','2016-04-13 10:09:13','2018-04-04 18:07:52'),(168,23,'label_nostaff_colour','red','2016-04-22 14:39:34','2016-04-26 10:57:52'),(169,23,'employee_of_month','225','2016-04-26 10:58:42','2016-04-26 10:58:42'),(170,23,'items_per_page','25','2016-04-26 10:58:42','2016-04-26 10:58:42'),(171,23,'website','','2016-04-26 10:58:42','2016-04-26 10:58:42'),(172,23,'email','','2016-04-26 10:58:42','2016-04-26 10:58:42'),(173,23,'phone','','2016-04-26 10:58:42','2016-04-26 10:58:42'),(174,23,'address','','2016-04-26 10:58:42','2016-04-26 10:58:42'),(175,23,'mailchimp_key','','2016-04-26 10:58:42','2016-04-26 10:58:42'),(176,23,'gocardless_app_id','','2016-04-26 10:58:42','2016-04-26 10:58:42'),(177,23,'gocardless_app_secret','','2016-04-26 10:58:42','2016-04-26 10:58:42'),(179,23,'gocardless_merchant_id','','2016-04-26 10:58:42','2016-04-26 10:58:42'),(180,23,'gocardless_environment','sandbox','2016-04-26 10:58:42','2016-04-26 10:58:42'),(181,23,'gocardless_success_redirect','','2016-04-26 10:58:42','2016-04-26 10:58:42'),(1530,23,'dashboard_custom_widget_1_title','Twitter','2017-09-28 14:12:14','2017-09-28 14:12:14'),(1531,23,'dashboard_custom_widget_1_html','<a class=\"twitter-timeline\" data-height=\"400\" href=\"https://twitter.com/premrugby?ref_src=twsrc%5Etfw\">Tweets by premrugby</a> <script async src=\"//platform.twitter.com/widgets.js\" charset=\"utf-8\"></script>','2017-09-28 14:12:14','2017-09-28 14:12:14'),(1532,23,'dashboard_custom_widget_2_title','','2017-09-28 14:12:17','2017-09-28 14:12:17'),(1533,23,'dashboard_custom_widget_2_html','','2017-09-28 14:12:17','2017-09-28 14:12:17'),(1534,23,'dashboard_custom_widget_3_title','','2017-09-28 14:12:17','2017-09-28 14:12:17'),(1535,23,'dashboard_custom_widget_3_html','','2017-09-28 14:12:17','2017-09-28 14:12:17');
/*!40000 ALTER TABLE `app_accounts_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_accounts_settings_dashboard`
--

DROP TABLE IF EXISTS `app_accounts_settings_dashboard`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_accounts_settings_dashboard` (
  `settingID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL,
  `byID` int(11) DEFAULT NULL,
  `key` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `value_amber` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `value_red` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `added` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`settingID`),
  KEY `key` (`key`),
  KEY `accountID` (`accountID`),
  KEY `byID` (`byID`),
  CONSTRAINT `fk_accounts_settings_dashboard_accountID` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_accounts_settings_dashboard_byID` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `fk_accounts_settings_dashboard_key` FOREIGN KEY (`key`) REFERENCES `app_settings_dashboard` (`key`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_accounts_settings_dashboard`
--

LOCK TABLES `app_accounts_settings_dashboard` WRITE;
/*!40000 ALTER TABLE `app_accounts_settings_dashboard` DISABLE KEYS */;
/*!40000 ALTER TABLE `app_accounts_settings_dashboard` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_activities`
--

DROP TABLE IF EXISTS `app_activities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_activities` (
  `activityID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL,
  `byID` int(11) DEFAULT NULL,
  `imported` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `added` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`activityID`),
  KEY `accountID` (`accountID`),
  KEY `byID` (`byID`),
  CONSTRAINT `app_activities_ibfk_1` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_activities_ibfk_2` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_activities_ibfk_3` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=999 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_activities`
--

LOCK TABLES `app_activities` WRITE;
/*!40000 ALTER TABLE `app_activities` DISABLE KEYS */;
INSERT INTO `app_activities` VALUES (1,10,NULL,0,'Games','2016-04-28 10:01:18','2016-04-28 10:01:18'),(2,10,NULL,0,'Dance','2016-04-28 10:01:18','2016-04-28 10:01:18'),(3,10,NULL,0,'Gymnastics','2016-04-28 10:01:19','2016-04-28 10:01:19'),(4,10,NULL,0,'Athletics','2016-04-28 10:01:19','2016-04-28 10:01:19'),(5,10,NULL,0,'OAA','2016-04-28 10:01:19','2016-04-28 10:01:19'),(6,10,NULL,0,'Bikeability','2016-04-28 10:01:19','2016-04-28 10:01:19'),(7,10,NULL,0,'Holiday Camps','2016-04-28 10:01:19','2016-04-28 10:01:19'),(50,23,NULL,0,'Games','2016-04-28 10:01:19','2016-04-28 10:01:19'),(51,23,NULL,0,'Dance','2016-04-28 10:01:19','2016-04-28 10:01:19'),(52,23,NULL,0,'Gymnastics','2016-04-28 10:01:19','2016-04-28 10:01:19'),(53,23,NULL,0,'Athletics','2016-04-28 10:01:19','2016-04-28 10:01:19'),(54,23,NULL,0,'OAA','2016-04-28 10:01:19','2016-04-28 10:01:19'),(55,23,NULL,0,'Bikeability','2016-04-28 10:01:19','2016-04-28 10:01:19'),(56,23,NULL,0,'Holiday Camps','2016-04-28 10:01:19','2016-04-28 10:01:19');
/*!40000 ALTER TABLE `app_activities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_availability_cals`
--

DROP TABLE IF EXISTS `app_availability_cals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_availability_cals` (
  `calID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL,
  `byID` int(11) DEFAULT NULL,
  `brandID` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `added` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`calID`),
  KEY `accountID` (`accountID`),
  KEY `byID` (`byID`),
  KEY `brandID` (`brandID`),
  CONSTRAINT `fk_availability_cals_accountID` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_availability_cals_brandID` FOREIGN KEY (`brandID`) REFERENCES `app_brands` (`brandID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `fk_availability_cals_byID` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_availability_cals`
--

LOCK TABLES `app_availability_cals` WRITE;
/*!40000 ALTER TABLE `app_availability_cals` DISABLE KEYS */;
/*!40000 ALTER TABLE `app_availability_cals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_availability_cals_activities`
--

DROP TABLE IF EXISTS `app_availability_cals_activities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_availability_cals_activities` (
  `linkID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL,
  `calID` int(11) NOT NULL,
  `activityID` int(11) NOT NULL,
  `added` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`linkID`),
  KEY `accountID` (`accountID`),
  KEY `calID` (`calID`),
  KEY `activityID` (`activityID`),
  CONSTRAINT `fk_availability_cals_activities_accountID` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_availability_cals_activities_activityID` FOREIGN KEY (`activityID`) REFERENCES `app_activities` (`activityID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_availability_cals_activities_calID` FOREIGN KEY (`calID`) REFERENCES `app_availability_cals` (`calID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_availability_cals_activities`
--

LOCK TABLES `app_availability_cals_activities` WRITE;
/*!40000 ALTER TABLE `app_availability_cals_activities` DISABLE KEYS */;
/*!40000 ALTER TABLE `app_availability_cals_activities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_availability_cals_slots`
--

DROP TABLE IF EXISTS `app_availability_cals_slots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_availability_cals_slots` (
  `slotID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL,
  `calID` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `startTime` time NOT NULL,
  `endTime` time NOT NULL,
  `added` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`slotID`),
  KEY `accountID` (`accountID`),
  KEY `calID` (`calID`),
  CONSTRAINT `fk_availability_cals_slots_accountID` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_availability_cals_slots_calID` FOREIGN KEY (`calID`) REFERENCES `app_availability_cals` (`calID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_availability_cals_slots`
--

LOCK TABLES `app_availability_cals_slots` WRITE;
/*!40000 ALTER TABLE `app_availability_cals_slots` DISABLE KEYS */;
/*!40000 ALTER TABLE `app_availability_cals_slots` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_bookings`
--

DROP TABLE IF EXISTS `app_bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_bookings` (
  `bookingID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `contactID` int(11) DEFAULT NULL,
  `orgID` int(11) DEFAULT NULL,
  `addressID` int(11) DEFAULT NULL,
  `byID` int(11) DEFAULT NULL,
  `project` tinyint(1) DEFAULT '0',
  `project_typeID` int(11) DEFAULT NULL,
  `confirmed` tinyint(1) NOT NULL DEFAULT '0',
  `invoiced` tinyint(1) NOT NULL DEFAULT '0',
  `riskassessed` tinyint(1) NOT NULL DEFAULT '0',
  `cancelled` tinyint(1) NOT NULL DEFAULT '0',
  `public` tinyint(1) NOT NULL DEFAULT '0',
  `limit_participants` tinyint(1) NOT NULL DEFAULT '0',
  `min_age` int(3) DEFAULT '0',
  `online_booking_password` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `disable_online_booking` tinyint(1) NOT NULL DEFAULT '0',
  `renewed` tinyint(1) NOT NULL DEFAULT '0',
  `type` enum('booking','event') COLLATE utf8_unicode_ci DEFAULT 'booking',
  `name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `location` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `charge` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `autodiscount` enum('off','percentage','amount') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'off',
  `autodiscount_amount` decimal(8,2) NOT NULL DEFAULT '10.00',
  `startDate` date DEFAULT NULL,
  `endDate` date DEFAULT NULL,
  `renewalDate` date DEFAULT NULL,
  `renewalMeetingDate` date DEFAULT NULL,
  `bPackage` enum('bronze','silver','gold') COLLATE utf8_unicode_ci DEFAULT NULL,
  `bTheme` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `bPaid` tinyint(1) DEFAULT NULL,
  `bAttendees` text COLLATE utf8_unicode_ci,
  `bNotes` text COLLATE utf8_unicode_ci,
  `bcCoaching` tinyint(1) DEFAULT NULL,
  `bcCard` tinyint(1) DEFAULT NULL,
  `bcCerts` tinyint(1) DEFAULT NULL,
  `bcInvites` tinyint(1) DEFAULT NULL,
  `bcMedals` tinyint(1) DEFAULT NULL,
  `bcCake` tinyint(1) DEFAULT NULL,
  `bcBags` tinyint(1) DEFAULT NULL,
  `bcTrophy` tinyint(1) DEFAULT NULL,
  `bcPhoto` tinyint(1) DEFAULT NULL,
  `bookingconfirmation` text COLLATE utf8_unicode_ci,
  `eventconfirmation` text COLLATE utf8_unicode_ci,
  `website_description` text COLLATE utf8_unicode_ci,
  `thanksemail` tinyint(1) NOT NULL DEFAULT '0',
  `thanksemail_text` text COLLATE utf8_unicode_ci,
  `contract_type` enum('one off','company','external') COLLATE utf8_unicode_ci DEFAULT NULL,
  `renewal_reminder_1` tinyint(1) NOT NULL DEFAULT '0',
  `renewal_reminder_2` tinyint(1) NOT NULL DEFAULT '0',
  `renewal_reminder_3` tinyint(1) NOT NULL DEFAULT '0',
  `renewal_reminder_4` tinyint(1) NOT NULL DEFAULT '0',
  `brandID` int(11) DEFAULT NULL,
  `register_type` enum('children','individuals','numbers','names','bikeability','children_bikeability','individuals_bikeability') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'children',
  `booking_requirement` enum('all','select','remaining') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'all',
  `booking_postcodes` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
  `monitoring1` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `monitoring2` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `monitoring3` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `monitoring4` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `monitoring5` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `vendortxcode` varchar(13) COLLATE utf8_unicode_ci DEFAULT NULL,
  `added` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`bookingID`),
  KEY `fk_bookings_contactID` (`contactID`),
  KEY `fk_bookings_orgID` (`orgID`),
  KEY `fk_bookings_byID` (`byID`),
  KEY `fk_bookings_addressID` (`addressID`),
  KEY `fk_bookings_accountID` (`accountID`),
  KEY `fk_bookings_brandID` (`brandID`),
  KEY `project_typeID` (`project_typeID`),
  CONSTRAINT `app_bookings_ibfk_1` FOREIGN KEY (`contactID`) REFERENCES `app_orgs_contacts` (`contactID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_ibfk_2` FOREIGN KEY (`orgID`) REFERENCES `app_orgs` (`orgID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_ibfk_3` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_ibfk_4` FOREIGN KEY (`addressID`) REFERENCES `app_orgs_addresses` (`addressID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_ibfk_5` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_ibfk_6` FOREIGN KEY (`brandID`) REFERENCES `app_brands` (`brandID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `fk_bookings_project_typeID` FOREIGN KEY (`project_typeID`) REFERENCES `app_project_types` (`typeID`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7404 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_bookings`
--

LOCK TABLES `app_bookings` WRITE;
/*!40000 ALTER TABLE `app_bookings` DISABLE KEYS */;
INSERT INTO `app_bookings` VALUES (3757,23,2206,1862,NULL,224,0,NULL,0,0,0,0,0,0,0,NULL,0,0,'booking',NULL,NULL,NULL,'percentage',10.00,'2017-09-05','2018-07-30',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,'company',0,0,0,0,24,'children','all',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2016-04-15 08:38:24','2017-09-28 13:58:31'),(3758,23,2211,1864,NULL,224,0,NULL,0,0,0,0,0,0,0,NULL,0,0,'booking',NULL,NULL,NULL,'percentage',10.00,'2015-09-07','2016-07-24',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,'company',0,0,0,0,24,'children','all',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2016-04-15 10:01:23','2016-04-15 10:01:23'),(3759,23,2214,1866,NULL,224,0,NULL,0,0,0,0,0,0,0,NULL,0,0,'booking',NULL,NULL,NULL,'percentage',10.00,'2015-09-07','2016-07-24',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,'company',0,0,0,0,24,'children','all',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2016-04-15 10:09:17','2016-04-22 14:54:22'),(3760,23,2209,1863,NULL,224,0,NULL,0,0,0,0,0,0,0,NULL,0,0,'booking',NULL,NULL,NULL,'percentage',10.00,'2015-09-07','2016-07-24',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,'company',0,0,0,0,24,'children','all',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2016-04-15 10:19:07','2016-04-15 10:19:07'),(3761,23,2213,1865,NULL,224,0,NULL,0,0,0,0,0,0,0,NULL,0,0,'booking',NULL,NULL,NULL,'percentage',10.00,'2015-09-07','2016-07-24',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,'company',0,0,0,0,24,'children','all',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2016-04-15 10:37:51','2016-04-15 10:37:51'),(3762,23,2211,1864,NULL,224,0,NULL,0,0,0,0,0,0,0,NULL,0,0,'booking',NULL,NULL,NULL,'percentage',10.00,'2015-09-07','2016-07-24',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,'company',0,0,0,0,24,'children','all',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2016-04-15 10:46:20','2016-04-15 10:46:20'),(3763,23,2206,1862,NULL,224,0,NULL,0,0,0,0,0,0,0,NULL,0,0,'booking',NULL,NULL,NULL,'percentage',10.00,'2015-09-07','2016-07-24',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,'company',0,0,0,0,24,'children','all',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2016-04-15 10:56:33','2016-04-15 12:20:17'),(3765,23,NULL,1864,2327,224,1,12,0,0,0,0,1,0,0,'',0,0,'event','Fun Camp, Green Road Infants School','School Hall',NULL,'percentage',10.00,'2017-01-01','2017-12-31',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'<p>Hi {contact_first},</p>\r\n<p>Thank you for your booking for {event_name} {date_description}. Please find below details of the sessions booked:</p>\r\n<p>{details}</p>\r\n<p>Please check all details throroughly, should you notice any mistakes to your booking or would like to make an amendment please contact a member of the team.</p>','',0,'<p>Hi {contact_first},</p>\r\n<p>We would like to thank you for coming to {event_name}.</p>\r\n<p>Our coaches had fun, but it\'s more important to know your children have as much fun as we do!</p>\r\n<p>If you haven\'t had a chance to leave us feedback and would like to please follow this <a href=\"http://kids.firststep-sports.co.uk/?p=211\">link</a>&nbsp;to complete a quick survey to tell us how we performed. We value what our users have to say and your feedback will help us to improve our services to you.</p>\r\n<p>We hope to see you again next time! You can manage all your bookings in advance and online. Just follow this link and log in to your account {website}</p>',NULL,0,0,0,0,26,'names','all','','Gender','Postcode','','','','','2016-04-18 09:23:16','2017-09-28 14:37:50'),(3766,23,NULL,1863,2326,224,1,NULL,0,0,0,0,1,0,0,NULL,0,0,'event','Fun Camp, Springfield Primary','School Hall',NULL,'percentage',10.00,'2017-01-01','2017-12-31',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'<p>Hi {contact_first},</p>\r\n<p>Thank you for your booking for {event_name} {date_description}. Please find below details of the sessions booked:</p>\r\n<p>{details}</p>\r\n<p>Please check everything and if anything is incorrect, please contact us.</p>','',0,'<p>Hi {contact_first},</p>\r\n<p>We would like to thank you for coming to {event_name}.</p>\r\n<p>Our coaches had fun, but it\'s more important to know your children have as much fun as we do!</p>\r\n<p>If you haven\'t had a chance to leave us feedback and would like to please follow this <a href=\"http://kids.firststep-sports.co.uk/?p=211\">link</a>&nbsp;to complete a quick survey to tell us how we performed. We value what our users have to say and your feedback will help us to improve our services to you.</p>\r\n<p>We hope to see you again next time! You can manage all your bookings in advance and online. Just follow this link and log in to your account {website}</p>',NULL,0,0,0,0,26,'children','all',NULL,'','','',NULL,NULL,'','2016-04-18 09:58:51','2016-04-18 12:34:18'),(3767,23,2215,1867,NULL,224,1,NULL,1,0,0,0,0,0,0,NULL,0,0,'booking','Summer Splash',NULL,NULL,'off',0.00,'2016-06-06','2016-07-31',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'<p>Hi {contact_first},</p>\r\n<p>Thank you for your booking for {event_name} {date_description}. Please find below details of the sessions booked:</p>\r\n<p>{details}</p>\r\n<p>Please check everything and if anything is incorrect, please contact us.</p>','',0,'<p>Hi {contact_first},</p>\r\n<p>We would like to thank you for coming to {event_name}.</p>\r\n<p>Our coaches had fun, but it\'s more important to know your children have as much fun as we do!</p>\r\n<p>If you haven\'t had a chance to leave us feedback and would like to please follow this <a href=\"http://kids.firststep-sports.co.uk/?p=211\">link</a>&nbsp;to complete a quick survey to tell us how we performed. We value what our users have to say and your feedback will help us to improve our services to you.</p>\r\n<p>We hope to see you again next time! You can manage all your bookings in advance and online. Just follow this link and log in to your account {website}</p>','company',0,0,0,0,25,'numbers','',NULL,'','','',NULL,NULL,'','2016-04-18 12:39:44','2016-04-18 12:51:43'),(3768,23,2215,1867,NULL,224,1,NULL,1,0,0,0,0,0,0,NULL,0,0,'booking','Party In The Park',NULL,NULL,'off',0.00,'2016-07-04','2016-07-10',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'<p>Hi {contact_first},</p>\r\n<p>Thank you for your booking for {event_name} {date_description}. Please find below details of the sessions booked:</p>\r\n<p>{details}</p>\r\n<p>Please check everything and if anything is incorrect, please contact us.</p>','',0,'<p>Hi {contact_first},</p>\r\n<p>We would like to thank you for coming to {event_name}.</p>\r\n<p>Our coaches had fun, but it\'s more important to know your children have as much fun as we do!</p>\r\n<p>If you haven\'t had a chance to leave us feedback and would like to please follow this <a href=\"http://kids.firststep-sports.co.uk/?p=211\">link</a>&nbsp;to complete a quick survey to tell us how we performed. We value what our users have to say and your feedback will help us to improve our services to you.</p>\r\n<p>We hope to see you again next time! You can manage all your bookings in advance and online. Just follow this link and log in to your account {website}</p>','company',0,0,0,0,25,'numbers','',NULL,'','','',NULL,NULL,'','2016-04-18 12:47:04','2016-04-18 12:51:44'),(3769,23,2217,1869,NULL,224,1,NULL,0,0,0,0,0,0,0,NULL,0,0,'booking','Westminster Bikeability',NULL,NULL,'off',0.00,'2016-01-01','2016-12-31',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'<p>Hi {contact_first},</p>\r\n<p>Thank you for your booking for {event_name} {date_description}. Please find below details of the sessions booked:</p>\r\n<p>{details}</p>\r\n<p>Please check everything and if anything is incorrect, please contact us.</p>','',0,'<p>Hi {contact_first},</p>\r\n<p>We would like to thank you for coming to {event_name}.</p>\r\n<p>Our coaches had fun, but it\'s more important to know your children have as much fun as we do!</p>\r\n<p>If you haven\'t had a chance to leave us feedback and would like to please follow this <a href=\"http://kids.firststep-sports.co.uk/?p=211\">link</a>&nbsp;to complete a quick survey to tell us how we performed. We value what our users have to say and your feedback will help us to improve our services to you.</p>\r\n<p>We hope to see you again next time! You can manage all your bookings in advance and online. Just follow this link and log in to your account {website}</p>','company',0,0,0,0,27,'numbers','',NULL,'','','',NULL,NULL,'','2016-04-20 13:31:22','2016-04-22 14:30:28'),(3770,23,2213,1865,NULL,224,1,12,0,0,0,0,0,0,0,NULL,0,0,'booking','First Aid Training',NULL,NULL,'off',0.00,'2016-06-08','2016-06-08',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'<p>Hi {contact_first},</p>\r\n<p>Thank you for your booking for {event_name} {date_description}. Please find below details of the sessions booked:</p>\r\n<p>{details}</p>\r\n<p>Please check everything and if anything is incorrect, please contact us.</p>','',0,'<p>Hi {contact_first},</p>\r\n<p>We would like to thank you for coming to {event_name}.</p>\r\n<p>Our coaches had fun, but it\'s more important to know your children have as much fun as we do!</p>\r\n<p>If you haven\'t had a chance to leave us feedback and would like to please follow this <a href=\"http://kids.firststep-sports.co.uk/?p=211\">link</a>&nbsp;to complete a quick survey to tell us how we performed. We value what our users have to say and your feedback will help us to improve our services to you.</p>\r\n<p>We hope to see you again next time! You can manage all your bookings in advance and online. Just follow this link and log in to your account {website}</p>','one off',0,0,0,0,28,'names','all',NULL,'Level 1','Level 2','Level 3',NULL,NULL,'','2016-04-20 13:46:00','2016-05-26 16:20:52'),(3771,23,2218,1870,NULL,224,0,NULL,0,0,0,0,0,0,0,NULL,0,0,'booking',NULL,NULL,NULL,'percentage',10.00,'2015-09-07','2016-07-24',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,'company',0,0,0,0,24,'children','all',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2016-04-22 12:49:55','2016-04-22 12:51:26'),(3772,23,NULL,1869,2332,224,1,12,0,0,0,0,0,0,0,NULL,0,0,'event','Staff Training','Training Room',NULL,'off',0.00,'2016-06-09','2016-06-15',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'<p>Hi {contact_first},</p>\r\n<p>Thank you for your booking for {event_name} {date_description}. Please find below details of the sessions booked:</p>\r\n<p>{details}</p>\r\n<p>Please check everything and if anything is incorrect, please contact us.</p>','',0,'<p>Hi {contact_first},</p>\r\n<p>We would like to thank you for coming to {event_name}.</p>\r\n<p>Our coaches had fun, but it\'s more important to know your children have as much fun as we do!</p>\r\n<p>If you haven\'t had a chance to leave us feedback and would like to please follow this <a href=\"http://kids.firststep-sports.co.uk/?p=211\">link</a>&nbsp;to complete a quick survey to tell us how we performed. We value what our users have to say and your feedback will help us to improve our services to you.</p>\r\n<p>We hope to see you again next time! You can manage all your bookings in advance and online. Just follow this link and log in to your account {website}</p>',NULL,0,0,0,0,28,'children','all',NULL,'','','',NULL,NULL,'','2016-04-22 14:45:02','2016-06-10 10:34:37'),(3773,23,NULL,1863,2326,234,1,NULL,0,0,0,0,0,0,0,NULL,0,0,'event','Kidstakeover - Beverley','Hall',NULL,'percentage',10.00,'2016-01-01','2016-12-31',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'<p>Hi {contact_first},</p>\r\n<p>Thank you for your booking for {event_name} {date_description}. Please find below details of the sessions booked:</p>\r\n<p>{details}</p>\r\n<p>Please check everything and if anything is incorrect, please contact us.</p>','',0,'<p>Hi {contact_first},</p>\r\n<p>We would like to thank you for coming to {event_name}.</p>\r\n<p>Our coaches had fun, but it\'s more important to know your children have as much fun as we do!</p>\r\n<p>If you haven\'t had a chance to leave us feedback and would like to please follow this <a href=\"http://kids.firststep-sports.co.uk/?p=211\">link</a>&nbsp;to complete a quick survey to tell us how we performed. We value what our users have to say and your feedback will help us to improve our services to you.</p>\r\n<p>We hope to see you again next time! You can manage all your bookings in advance and online. Just follow this link and log in to your account {website}</p>',NULL,0,0,0,0,26,'children','all',NULL,'','','',NULL,NULL,'','2016-04-26 13:13:18','2016-04-26 13:13:18'),(3774,23,2206,1862,NULL,235,0,NULL,0,0,0,0,0,0,0,NULL,0,0,'booking',NULL,NULL,NULL,'percentage',10.00,'2016-05-17','2017-05-17',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,'company',0,0,0,0,27,'children','all',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2016-05-17 14:22:33','2016-05-17 14:22:33');
/*!40000 ALTER TABLE `app_bookings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_bookings_attachments`
--

DROP TABLE IF EXISTS `app_bookings_attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_bookings_attachments` (
  `attachmentID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `bookingID` int(11) DEFAULT NULL,
  `byID` int(11) DEFAULT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `path` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `ext` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `size` bigint(100) NOT NULL,
  `comment` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sendwithconfirmation` tinyint(1) NOT NULL DEFAULT '0',
  `sendwiththanks` tinyint(1) NOT NULL DEFAULT '0',
  `added` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`attachmentID`),
  KEY `fk_bookings_attachments_bookingID` (`bookingID`),
  KEY `fk_bookings_attachments_byID` (`byID`),
  KEY `fk_bookings_attachments_accountID` (`accountID`),
  CONSTRAINT `app_bookings_attachments_ibfk_2` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_attachments_ibfk_3` FOREIGN KEY (`bookingID`) REFERENCES `app_bookings` (`bookingID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_attachments_ibfk_4` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_bookings_attachments`
--

LOCK TABLES `app_bookings_attachments` WRITE;
/*!40000 ALTER TABLE `app_bookings_attachments` DISABLE KEYS */;
/*!40000 ALTER TABLE `app_bookings_attachments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_bookings_attendance_names`
--

DROP TABLE IF EXISTS `app_bookings_attendance_names`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_bookings_attendance_names` (
  `participantID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL,
  `bookingID` int(11) NOT NULL,
  `blockID` int(11) NOT NULL,
  `byID` int(11) DEFAULT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `monitoring1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `monitoring2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `monitoring3` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `monitoring4` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `monitoring5` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `bikeability_level` int(1) DEFAULT NULL,
  `added` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`participantID`),
  KEY `accountID` (`accountID`),
  KEY `bookingID` (`bookingID`),
  KEY `blockID` (`blockID`),
  KEY `byID` (`byID`),
  CONSTRAINT `app_bookings_attendance_names_ibfk_1` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_attendance_names_ibfk_2` FOREIGN KEY (`bookingID`) REFERENCES `app_bookings` (`bookingID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_attendance_names_ibfk_3` FOREIGN KEY (`blockID`) REFERENCES `app_bookings_blocks` (`blockID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_attendance_names_ibfk_4` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8717 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_bookings_attendance_names`
--

LOCK TABLES `app_bookings_attendance_names` WRITE;
/*!40000 ALTER TABLE `app_bookings_attendance_names` DISABLE KEYS */;
INSERT INTO `app_bookings_attendance_names` VALUES (7,23,3770,49595,235,'Test','pass','pass','fail',NULL,NULL,NULL,'2016-05-26 16:29:52','2016-05-26 16:29:52'),(3809,23,3765,49585,208,'Joe Bloggs','M','HU1 1UU',NULL,NULL,NULL,NULL,'2017-09-28 14:39:36','2017-09-28 14:39:36');
/*!40000 ALTER TABLE `app_bookings_attendance_names` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_bookings_attendance_names_sessions`
--

DROP TABLE IF EXISTS `app_bookings_attendance_names_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_bookings_attendance_names_sessions` (
  `attendanceID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL,
  `participantID` int(11) NOT NULL,
  `bookingID` int(11) NOT NULL,
  `blockID` int(11) NOT NULL,
  `lessonID` int(11) NOT NULL,
  `byID` int(11) DEFAULT NULL,
  `date` date NOT NULL,
  `bikeability_level` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `added` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`attendanceID`),
  KEY `accountID` (`accountID`),
  KEY `participantID` (`participantID`),
  KEY `bookingID` (`bookingID`),
  KEY `blockID` (`blockID`),
  KEY `lessonID` (`lessonID`),
  KEY `byID` (`byID`),
  CONSTRAINT `app_bookings_attendance_names_sessions_ibfk_1` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_attendance_names_sessions_ibfk_2` FOREIGN KEY (`participantID`) REFERENCES `app_bookings_attendance_names` (`participantID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_attendance_names_sessions_ibfk_3` FOREIGN KEY (`bookingID`) REFERENCES `app_bookings` (`bookingID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_attendance_names_sessions_ibfk_4` FOREIGN KEY (`blockID`) REFERENCES `app_bookings_blocks` (`blockID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_attendance_names_sessions_ibfk_5` FOREIGN KEY (`lessonID`) REFERENCES `app_bookings_lessons` (`lessonID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_attendance_names_sessions_ibfk_6` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15670 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_bookings_attendance_names_sessions`
--

LOCK TABLES `app_bookings_attendance_names_sessions` WRITE;
/*!40000 ALTER TABLE `app_bookings_attendance_names_sessions` DISABLE KEYS */;
INSERT INTO `app_bookings_attendance_names_sessions` VALUES (3,23,7,3770,49595,15841,235,'2016-06-08',NULL,'2016-05-26 16:29:52','2016-05-26 16:29:52'),(6977,23,3809,3765,49585,15780,208,'2017-02-13',NULL,'2017-09-28 14:39:36','2017-09-28 14:39:36'),(6978,23,3809,3765,49585,15781,208,'2017-02-13',NULL,'2017-09-28 14:39:36','2017-09-28 14:39:36');
/*!40000 ALTER TABLE `app_bookings_attendance_names_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_bookings_attendance_numbers`
--

DROP TABLE IF EXISTS `app_bookings_attendance_numbers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_bookings_attendance_numbers` (
  `attendanceID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `bookingID` int(11) NOT NULL,
  `blockID` int(11) NOT NULL,
  `lessonID` int(11) NOT NULL,
  `date` date NOT NULL,
  `attended` int(11) NOT NULL,
  `byID` int(11) DEFAULT NULL,
  `added` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`attendanceID`),
  KEY `fk_bookings_attendance_numbers_bookingID` (`bookingID`),
  KEY `fk_bookings_attendance_numbers_blockID` (`blockID`),
  KEY `fk_bookings_attendance_numbers_byID` (`byID`),
  KEY `fk_bookings_attendance_numbers_lessonID` (`lessonID`),
  KEY `fk_bookings_attendance_numbers_accountID` (`accountID`),
  CONSTRAINT `app_bookings_attendance_numbers_ibfk_1` FOREIGN KEY (`blockID`) REFERENCES `app_bookings_blocks` (`blockID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_attendance_numbers_ibfk_2` FOREIGN KEY (`bookingID`) REFERENCES `app_bookings` (`bookingID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_attendance_numbers_ibfk_3` FOREIGN KEY (`lessonID`) REFERENCES `app_bookings_lessons` (`lessonID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_attendance_numbers_ibfk_4` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_attendance_numbers_ibfk_5` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=868 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_bookings_attendance_numbers`
--

LOCK TABLES `app_bookings_attendance_numbers` WRITE;
/*!40000 ALTER TABLE `app_bookings_attendance_numbers` DISABLE KEYS */;
INSERT INTO `app_bookings_attendance_numbers` VALUES (425,23,3769,49594,15843,'2016-06-06',50,224,'2016-04-22 14:30:50','2016-04-22 14:30:50'),(426,23,3769,49594,15845,'2016-06-07',0,224,'2016-04-22 14:30:50','2016-04-22 14:30:50'),(427,23,3769,49594,15844,'2016-06-06',50,224,'2016-04-22 14:30:50','2016-04-22 14:30:50'),(428,23,3769,49594,15846,'2016-06-07',0,224,'2016-04-22 14:30:50','2016-04-22 14:30:50');
/*!40000 ALTER TABLE `app_bookings_attendance_numbers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_bookings_blocks`
--

DROP TABLE IF EXISTS `app_bookings_blocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_bookings_blocks` (
  `blockID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `bookingID` int(11) NOT NULL,
  `orgID` int(11) DEFAULT NULL,
  `addressID` int(11) DEFAULT NULL,
  `byID` int(11) DEFAULT NULL,
  `org_bookable` tinyint(1) NOT NULL DEFAULT '0',
  `website_description` text COLLATE utf8_unicode_ci,
  `provisional` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `startDate` date NOT NULL,
  `endDate` date NOT NULL,
  `target_profit` decimal(8,2) NOT NULL DEFAULT '0.00',
  `target_weekly` int(11) NOT NULL DEFAULT '0',
  `target_total` int(11) NOT NULL DEFAULT '0',
  `target_unique` int(11) NOT NULL DEFAULT '0',
  `target_retention` int(11) NOT NULL DEFAULT '0',
  `target_retention_weeks` int(11) NOT NULL DEFAULT '0',
  `target_costs` decimal(8,2) NOT NULL,
  `targets_missed` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `numbers_path` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `misc_income` decimal(8,2) NOT NULL DEFAULT '0.00',
  `staffing_notes` text COLLATE utf8_unicode_ci,
  `min_age` int(3) DEFAULT NULL,
  `thanksemail_sent` tinyint(1) NOT NULL DEFAULT '0',
  `added` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`blockID`),
  KEY `fk_bookings_blocks_bookingID` (`bookingID`),
  KEY `fk_bookings_blocks_byID` (`byID`),
  KEY `fk_bookings_blocks_addressID` (`addressID`),
  KEY `fk_bookings_blocks_accountID` (`accountID`),
  KEY `fk_bookings_blocks_orgID` (`orgID`),
  CONSTRAINT `app_bookings_blocks_ibfk_1` FOREIGN KEY (`bookingID`) REFERENCES `app_bookings` (`bookingID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_blocks_ibfk_2` FOREIGN KEY (`addressID`) REFERENCES `app_orgs_addresses` (`addressID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_blocks_ibfk_3` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_blocks_ibfk_4` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_bookings_blocks_orgID` FOREIGN KEY (`orgID`) REFERENCES `app_orgs` (`orgID`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=57718 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_bookings_blocks`
--

LOCK TABLES `app_bookings_blocks` WRITE;
/*!40000 ALTER TABLE `app_bookings_blocks` DISABLE KEYS */;
INSERT INTO `app_bookings_blocks` VALUES (49559,23,3757,NULL,2325,224,0,NULL,0,'Autumn','2017-09-05','2017-12-24',0.00,0,0,0,0,0,0.00,NULL,NULL,0.00,'',NULL,0,'2016-04-15 08:39:02','2017-09-28 13:58:51'),(49560,23,3757,NULL,2325,224,0,NULL,0,'Spring','2017-01-02','2017-04-09',0.00,0,0,0,0,0,0.00,NULL,NULL,0.00,'',NULL,0,'2016-04-15 09:53:17','2016-04-15 09:54:12'),(49561,23,3757,NULL,2325,224,0,NULL,0,'Summer','2017-04-24','2017-07-30',0.00,0,0,0,0,0,0.00,NULL,NULL,0.00,'',NULL,0,'2016-04-15 09:53:21','2016-04-15 09:54:38'),(49562,23,3758,NULL,2327,224,0,NULL,0,'Autumn','2015-09-07','2015-12-27',0.00,0,0,0,0,0,0.00,NULL,NULL,0.00,'',NULL,0,'2016-04-15 10:02:25','2016-04-15 10:02:25'),(49563,23,3758,NULL,2327,224,0,NULL,0,'Spring','2016-01-04','2016-03-27',0.00,0,0,0,0,0,0.00,NULL,NULL,0.00,'',NULL,0,'2016-04-15 10:06:16','2016-04-15 10:06:55'),(49564,23,3758,NULL,2327,224,0,NULL,0,'Summer','2016-04-11','2016-07-31',0.00,0,0,0,0,0,0.00,NULL,NULL,0.00,'',NULL,0,'2016-04-15 10:06:19','2016-04-15 10:07:44'),(49565,23,3759,NULL,2329,224,0,NULL,0,'Autumn','2015-09-07','2015-12-27',0.00,0,0,0,0,0,0.00,NULL,NULL,0.00,'',NULL,0,'2016-04-15 10:10:09','2016-04-15 10:10:09'),(49568,23,3759,NULL,2329,224,0,NULL,0,'Spring','2016-01-04','2016-03-27',0.00,0,0,0,0,0,0.00,NULL,NULL,0.00,'',NULL,0,'2016-04-15 10:14:55','2016-04-15 10:16:46'),(49569,23,3759,NULL,2329,224,0,NULL,0,'Summer','2016-04-11','2016-07-31',0.00,0,0,0,0,0,0.00,NULL,NULL,0.00,'',NULL,0,'2016-04-15 10:14:58','2016-04-15 10:17:22'),(49570,23,3760,NULL,2326,224,0,NULL,0,'Autumn','2015-09-07','2015-12-20',0.00,0,0,0,0,0,0.00,NULL,NULL,0.00,'',NULL,0,'2016-04-15 10:20:03','2016-04-15 10:20:03'),(49571,23,3760,NULL,2326,224,0,NULL,0,'Spring','2016-01-04','2016-12-25',0.00,0,0,0,0,0,0.00,NULL,NULL,0.00,'',NULL,0,'2016-04-15 10:28:06','2016-04-15 10:28:37'),(49572,23,3760,NULL,2326,224,0,NULL,0,'Summer','2016-04-11','2016-07-24',0.00,0,0,0,0,0,0.00,NULL,NULL,0.00,'',NULL,0,'2016-04-15 10:28:09','2016-04-15 10:29:21'),(49573,23,3761,NULL,2328,224,0,NULL,0,'Autumn','2015-09-07','2015-12-20',0.00,0,0,0,0,0,0.00,NULL,NULL,0.00,'',NULL,0,'2016-04-15 10:38:47','2016-04-15 10:38:47'),(49574,23,3761,NULL,2328,224,0,NULL,0,'Spring','2016-01-04','2016-03-27',0.00,0,0,0,0,0,0.00,NULL,NULL,0.00,'',NULL,0,'2016-04-15 10:43:22','2016-04-15 10:44:06'),(49575,23,3761,NULL,2328,224,0,NULL,0,'Summer','2016-04-11','2016-07-24',0.00,0,0,0,0,0,0.00,NULL,NULL,0.00,'',NULL,0,'2016-04-15 10:43:30','2016-04-15 10:44:41'),(49576,23,3762,NULL,2327,224,0,NULL,0,'Autumn','2015-09-07','2015-12-27',0.00,0,0,0,0,0,0.00,NULL,NULL,0.00,'',NULL,0,'2016-04-15 10:46:55','2016-04-15 10:46:55'),(49577,23,3762,NULL,2327,224,0,NULL,0,'Spring','2016-01-04','2016-03-27',0.00,0,0,0,0,0,0.00,NULL,NULL,0.00,'',NULL,0,'2016-04-15 10:48:34','2016-04-15 10:49:20'),(49578,23,3762,NULL,2327,224,0,NULL,0,'Summer','2016-04-11','2016-07-31',0.00,0,0,0,0,0,0.00,NULL,NULL,0.00,'',NULL,0,'2016-04-15 10:48:37','2016-04-15 10:51:52'),(49579,23,3763,NULL,2325,224,0,NULL,0,'Autumn','2015-09-07','2016-07-24',0.00,0,0,0,0,0,0.00,NULL,NULL,0.00,'',NULL,0,'2016-04-15 12:05:49','2016-04-15 12:05:49'),(49580,23,3763,NULL,2325,224,0,NULL,0,'Spring','2016-01-04','2016-03-27',0.00,0,0,0,0,0,0.00,NULL,NULL,0.00,'',NULL,0,'2016-04-15 12:16:42','2016-04-15 12:19:02'),(49581,23,3763,NULL,2325,224,0,NULL,0,'Summer','2016-04-11','2016-07-24',0.00,0,0,0,0,0,0.00,NULL,NULL,0.00,'',NULL,0,'2016-04-15 12:16:45','2016-04-15 12:19:41'),(49585,23,3765,NULL,NULL,224,0,NULL,0,'Fun Camp, Green Road Infants School, February Half Term','2017-02-13','2017-02-19',0.00,0,0,0,0,0,0.00,'Session Participants',NULL,0.00,'',NULL,0,'2016-04-18 09:41:46','2016-04-18 12:54:00'),(49586,23,3765,NULL,NULL,224,0,NULL,0,'Fun Camp, Green Road Infants School, Easter Half Term Week 1','2017-03-27','2017-04-02',0.00,0,0,0,0,0,0.00,'Lesson Participants',NULL,0.00,'',NULL,0,'2016-04-18 09:47:53','2016-04-18 12:54:53'),(49587,23,3765,NULL,NULL,224,0,NULL,0,'Fun Camp, Green Road Infants School, Easter Half Term Week 2','2017-04-10','2017-04-16',0.00,0,0,0,0,0,0.00,'Lesson Participants',NULL,0.00,'',NULL,0,'2016-04-18 09:53:54','2016-04-18 12:54:22'),(49588,23,3766,NULL,NULL,224,0,NULL,0,'Fun Camp, Springfield Primary - February Half Term','2017-02-13','2017-02-17',0.00,0,0,0,0,0,0.00,'Lesson Participants',NULL,0.00,'',NULL,0,'2016-04-18 09:59:50','2016-04-18 12:31:24'),(49589,23,3766,NULL,NULL,224,0,NULL,0,'Fun Camp, Springfield Primary - Easter Half Term Week 1','2017-04-10','2017-04-14',0.00,0,0,0,0,0,0.00,'Lesson Participants',NULL,0.00,'',NULL,0,'2016-04-18 10:03:50','2016-04-18 12:33:10'),(49590,23,3766,NULL,NULL,224,0,NULL,0,'Fun Camp, Springfield Primary - Easter Half Term Week 2','2017-04-17','2017-04-21',0.00,0,0,0,0,0,0.00,'Lesson Participants',NULL,0.00,'',NULL,0,'2016-04-18 10:05:43','2016-04-18 12:35:56'),(49591,23,3767,NULL,2330,224,0,NULL,0,'Summer Splash','2016-06-06','2016-07-31',0.00,0,0,0,0,0,0.00,'Lesson Participants','I6Q9PC5RWpnhU4fxezTVbXYvLZcolA2j',0.00,'There will be water activities so participants will need change of clothes.',NULL,0,'2016-04-18 12:41:24','2016-04-18 12:41:24'),(49592,23,3768,NULL,2330,224,0,NULL,0,'Party In The Park','2016-07-04','2016-07-10',0.00,0,0,0,0,0,0.00,'Lesson Participants',NULL,0.00,'Children will be using paint so a change of clothes is needed.',NULL,0,'2016-04-18 12:48:00','2016-04-18 12:48:00'),(49593,23,3769,NULL,2332,224,0,NULL,0,'Springfield Primary Level 1 &amp; 2 Bikeability','2016-04-25','2016-05-01',0.00,0,0,0,0,0,0.00,NULL,'i7o8pMVPctOBErsSCqfWa0X92ywQ365H',0.00,'',NULL,0,'2016-04-20 13:34:03','2016-04-20 13:34:03'),(49594,23,3769,NULL,2333,224,0,NULL,0,'North Primary School Level 1 &amp; 2 Bikeability','2016-05-09','2016-06-12',0.00,0,0,0,0,0,0.00,NULL,'1lKZCnrxmVTMOQjYogpIse6G7tdRD5EN',0.00,'',NULL,0,'2016-04-20 13:43:12','2016-05-17 14:27:40'),(49595,23,3770,NULL,2328,224,0,NULL,0,'First Aid Annual Refresher Training','2016-06-08','2016-06-08',0.00,0,0,0,0,0,0.00,NULL,NULL,0.00,'',NULL,0,'2016-04-20 13:46:37','2016-04-20 13:46:37'),(49596,23,3771,NULL,2334,224,0,NULL,0,'Summer','2016-04-11','2016-07-31',0.00,0,0,0,0,0,0.00,NULL,NULL,0.00,'',NULL,0,'2016-04-22 12:52:14','2016-04-22 12:52:14'),(49597,23,3772,NULL,NULL,224,0,NULL,0,'Staff Training','2016-06-09','2016-06-10',0.00,0,0,0,0,0,0.00,NULL,NULL,0.00,'',NULL,0,'2016-04-22 14:45:28','2016-04-22 14:45:28'),(49598,23,3774,NULL,2325,235,0,NULL,0,'Summer','2016-05-17','2016-08-31',0.00,0,0,0,0,0,0.00,NULL,NULL,0.00,'',NULL,0,'2016-05-17 14:23:22','2016-05-17 14:23:22'),(55727,23,3765,NULL,NULL,208,0,NULL,0,'Fun Camp, Green Road Infants School, Half Term','2017-11-13','2017-11-19',0.00,0,0,0,0,0,0.00,'Session Participants',NULL,0.00,'',NULL,0,'2017-11-10 09:25:39','2017-11-10 09:26:29');
/*!40000 ALTER TABLE `app_bookings_blocks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_bookings_costs`
--

DROP TABLE IF EXISTS `app_bookings_costs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_bookings_costs` (
  `costID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `blockID` int(11) NOT NULL,
  `byID` int(11) DEFAULT NULL,
  `date` date NOT NULL,
  `amount` decimal(8,2) NOT NULL,
  `note` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `category` enum('Venue Hire','Marketing','Prizes','Supplies','Misc.') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Misc.',
  `added` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`costID`),
  KEY `fk_bookings_costs_byID` (`byID`),
  KEY `fk_bookings_costs_blockID` (`blockID`),
  KEY `fk_bookings_costs_accountID` (`accountID`),
  CONSTRAINT `app_bookings_costs_ibfk_2` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_costs_ibfk_3` FOREIGN KEY (`blockID`) REFERENCES `app_bookings_blocks` (`blockID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_costs_ibfk_4` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=105 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_bookings_costs`
--

LOCK TABLES `app_bookings_costs` WRITE;
/*!40000 ALTER TABLE `app_bookings_costs` DISABLE KEYS */;
/*!40000 ALTER TABLE `app_bookings_costs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_bookings_individuals`
--

DROP TABLE IF EXISTS `app_bookings_individuals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_bookings_individuals` (
  `recordID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `bookingID` int(11) DEFAULT NULL,
  `familyID` int(11) DEFAULT NULL,
  `contactID` int(11) DEFAULT NULL,
  `childID` int(11) DEFAULT NULL,
  `byID` int(11) DEFAULT NULL,
  `voucherID` int(11) DEFAULT NULL,
  `voucherID_global` int(11) DEFAULT NULL,
  `type` enum('children','individuals') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'children',
  `childcarevoucher` tinyint(1) NOT NULL DEFAULT '0',
  `childcarevoucher_providerID` int(11) DEFAULT NULL,
  `childcarevoucher_provider` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `childcarevoucher_ref` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `paid` tinyint(1) NOT NULL DEFAULT '0',
  `subtotal` decimal(10,2) NOT NULL DEFAULT '0.00',
  `discount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `balance` decimal(10,2) NOT NULL DEFAULT '0.00',
  `monday` tinyint(1) DEFAULT NULL,
  `tuesday` tinyint(1) DEFAULT NULL,
  `wednesday` tinyint(1) DEFAULT NULL,
  `thursday` tinyint(1) DEFAULT NULL,
  `friday` tinyint(1) DEFAULT NULL,
  `saturday` tinyint(1) DEFAULT NULL,
  `sunday` tinyint(1) DEFAULT NULL,
  `monPaid` tinyint(1) DEFAULT NULL,
  `monAmount` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tuePaid` tinyint(1) DEFAULT NULL,
  `tueAmount` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `wedPaid` tinyint(1) DEFAULT NULL,
  `wedAmount` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `thuPaid` tinyint(1) DEFAULT NULL,
  `thuAmount` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `friPaid` tinyint(1) DEFAULT NULL,
  `friAmount` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `satPaid` tinyint(1) DEFAULT NULL,
  `satAmount` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sunPaid` tinyint(1) DEFAULT NULL,
  `sunAmount` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `paymentnotes` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `source` enum('Twitter','Facebook','Website','Email','SMS','Flyer','Newspaper','Poster','Referral','Existing Customer','Other') COLLATE utf8_unicode_ci NOT NULL,
  `source_other` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `payment_reminder_before` tinyint(1) NOT NULL DEFAULT '0',
  `payment_reminder_after` tinyint(1) NOT NULL DEFAULT '0',
  `added` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`recordID`),
  KEY `fk_bookings_individuals_childID` (`childID`),
  KEY `fk_bookings_individuals_byID` (`byID`),
  KEY `fk_bookings_individuals_bookingID` (`bookingID`),
  KEY `fk_bookings_individuals_contactID` (`contactID`),
  KEY `fk_bookings_individuals_familyID` (`familyID`),
  KEY `fk_bookings_individuals_voucherID` (`voucherID`),
  KEY `fk_bookings_individuals_voucherID_global` (`voucherID_global`),
  KEY `fk_bookings_individuals_childcarevoucher_providerID` (`childcarevoucher_providerID`),
  KEY `fk_bookings_individuals_accountID` (`accountID`),
  CONSTRAINT `app_bookings_individuals_ibfk_1` FOREIGN KEY (`familyID`) REFERENCES `app_family` (`familyID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_individuals_ibfk_10` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_individuals_ibfk_2` FOREIGN KEY (`contactID`) REFERENCES `app_family_contacts` (`contactID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_individuals_ibfk_3` FOREIGN KEY (`childID`) REFERENCES `app_family_children` (`childID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_individuals_ibfk_5` FOREIGN KEY (`bookingID`) REFERENCES `app_bookings` (`bookingID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_individuals_ibfk_6` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_individuals_ibfk_7` FOREIGN KEY (`voucherID`) REFERENCES `app_bookings_vouchers` (`voucherID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_individuals_ibfk_8` FOREIGN KEY (`voucherID_global`) REFERENCES `app_vouchers` (`voucherID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_individuals_ibfk_9` FOREIGN KEY (`childcarevoucher_providerID`) REFERENCES `app_settings_childcarevoucherproviders` (`providerID`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18595 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_bookings_individuals`
--

LOCK TABLES `app_bookings_individuals` WRITE;
/*!40000 ALTER TABLE `app_bookings_individuals` DISABLE KEYS */;
INSERT INTO `app_bookings_individuals` VALUES (12079,23,3765,4273,4285,NULL,224,NULL,NULL,'children',0,NULL,NULL,NULL,0,336.00,15.00,321.00,321.00,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Existing Customer','',1,1,'2016-04-22 12:59:41','2016-04-22 12:59:42'),(12080,23,3765,4271,4283,NULL,224,NULL,NULL,'children',0,NULL,NULL,NULL,0,84.00,0.00,84.00,0.00,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Website','',0,0,'2016-04-22 13:00:28','2016-04-22 13:00:28'),(12081,23,3770,4267,4279,NULL,224,NULL,NULL,'individuals',0,NULL,NULL,NULL,0,100.00,0.00,100.00,0.00,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Facebook','',0,0,'2016-04-22 13:20:26','2016-04-22 13:20:26'),(12082,23,3770,4270,4282,NULL,224,NULL,NULL,'individuals',0,NULL,NULL,NULL,0,100.00,0.00,100.00,100.00,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Email','',1,1,'2016-04-22 13:20:43','2016-04-22 13:20:43'),(12083,23,3765,4270,4282,NULL,224,NULL,NULL,'children',0,NULL,NULL,NULL,0,140.00,0.00,140.00,140.00,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Twitter','',1,1,'2016-04-22 14:57:04','2016-04-22 14:57:05'),(12084,23,3766,4270,4282,NULL,224,NULL,NULL,'children',0,NULL,NULL,NULL,0,40.00,0.00,40.00,40.00,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Email','',1,1,'2016-04-22 14:57:45','2016-04-22 14:57:45'),(12085,23,3766,4271,4283,NULL,224,NULL,NULL,'children',0,NULL,NULL,NULL,0,84.00,0.00,84.00,0.00,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Facebook','',0,0,'2016-04-22 14:58:35','2016-04-22 14:58:35'),(12086,23,3770,4266,4278,NULL,224,NULL,NULL,'individuals',0,NULL,NULL,NULL,0,100.00,0.00,100.00,100.00,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Flyer','',1,1,'2016-04-22 14:59:13','2016-04-22 14:59:13');
/*!40000 ALTER TABLE `app_bookings_individuals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_bookings_individuals_bikeability`
--

DROP TABLE IF EXISTS `app_bookings_individuals_bikeability`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_bookings_individuals_bikeability` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL,
  `recordID` int(11) NOT NULL,
  `bookingID` int(11) NOT NULL,
  `contactID` int(11) DEFAULT NULL,
  `childID` int(11) DEFAULT NULL,
  `bikeability_level` int(1) DEFAULT NULL,
  `byID` int(11) DEFAULT NULL,
  `added` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `accountID` (`accountID`),
  KEY `recordID` (`recordID`),
  KEY `bookingID` (`bookingID`),
  KEY `contactID` (`contactID`),
  KEY `childID` (`childID`),
  KEY `byID` (`byID`),
  CONSTRAINT `fk_bookings_individuals_bikeability_accountID` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_bookings_individuals_bikeability_bookingID` FOREIGN KEY (`bookingID`) REFERENCES `app_bookings` (`bookingID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_bookings_individuals_bikeability_byID` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `fk_bookings_individuals_bikeability_childID` FOREIGN KEY (`childID`) REFERENCES `app_family_children` (`childID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_bookings_individuals_bikeability_contactID` FOREIGN KEY (`contactID`) REFERENCES `app_family_contacts` (`contactID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_bookings_individuals_bikeability_recordID` FOREIGN KEY (`recordID`) REFERENCES `app_bookings_individuals` (`recordID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=601 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_bookings_individuals_bikeability`
--

LOCK TABLES `app_bookings_individuals_bikeability` WRITE;
/*!40000 ALTER TABLE `app_bookings_individuals_bikeability` DISABLE KEYS */;
/*!40000 ALTER TABLE `app_bookings_individuals_bikeability` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_bookings_individuals_monitoring`
--

DROP TABLE IF EXISTS `app_bookings_individuals_monitoring`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_bookings_individuals_monitoring` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `recordID` int(11) NOT NULL,
  `bookingID` int(11) NOT NULL,
  `contactID` int(11) DEFAULT NULL,
  `childID` int(11) DEFAULT NULL,
  `monitoring1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `monitoring2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `monitoring3` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `monitoring4` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `monitoring5` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `byID` int(11) DEFAULT NULL,
  `added` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_bookings_individuals_monitoring_childID` (`childID`),
  KEY `fk_bookings_individuals_monitoring_recordID` (`recordID`),
  KEY `fk_bookings_individuals_monitoring_bookingID` (`bookingID`),
  KEY `fk_bookings_individuals_monitoring_contactID` (`contactID`),
  KEY `fk_bookings_individuals_monitoring_byID` (`byID`),
  KEY `fk_bookings_individuals_monitoring_accountID` (`accountID`),
  CONSTRAINT `app_bookings_individuals_monitoring_ibfk_1` FOREIGN KEY (`recordID`) REFERENCES `app_bookings_individuals` (`recordID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_individuals_monitoring_ibfk_2` FOREIGN KEY (`contactID`) REFERENCES `app_family_contacts` (`contactID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_individuals_monitoring_ibfk_3` FOREIGN KEY (`childID`) REFERENCES `app_family_children` (`childID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_individuals_monitoring_ibfk_4` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_individuals_monitoring_ibfk_5` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_bookings_individuals_monitoring`
--

LOCK TABLES `app_bookings_individuals_monitoring` WRITE;
/*!40000 ALTER TABLE `app_bookings_individuals_monitoring` DISABLE KEYS */;
/*!40000 ALTER TABLE `app_bookings_individuals_monitoring` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_bookings_individuals_sessions`
--

DROP TABLE IF EXISTS `app_bookings_individuals_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_bookings_individuals_sessions` (
  `sessionID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `recordID` int(11) NOT NULL,
  `bookingID` int(11) NOT NULL,
  `lessonID` int(11) NOT NULL,
  `childID` int(11) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `price` decimal(8,2) NOT NULL DEFAULT '0.00',
  `discount` decimal(8,2) NOT NULL DEFAULT '0.00',
  `total` decimal(8,2) NOT NULL DEFAULT '0.00',
  `attended` tinyint(1) NOT NULL DEFAULT '0',
  `bikeability_level` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `byID` int(11) DEFAULT NULL,
  `added` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`sessionID`),
  KEY `fk_bookings_individuals_sessions_childID` (`childID`),
  KEY `fk_bookings_individuals_sessions_bookingID` (`bookingID`),
  KEY `fk_bookings_individuals_sessions_byID` (`byID`),
  KEY `fk_bookings_individuals_sessions_lessonID` (`lessonID`),
  KEY `fk_bookings_individuals_sessions_recordID` (`recordID`),
  KEY `fk_bookings_individuals_sessions_accountID` (`accountID`),
  CONSTRAINT `app_bookings_individuals_sessions_ibfk_1` FOREIGN KEY (`childID`) REFERENCES `app_family_children` (`childID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_individuals_sessions_ibfk_2` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_individuals_sessions_ibfk_3` FOREIGN KEY (`bookingID`) REFERENCES `app_bookings` (`bookingID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_individuals_sessions_ibfk_5` FOREIGN KEY (`lessonID`) REFERENCES `app_bookings_lessons` (`lessonID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_individuals_sessions_ibfk_6` FOREIGN KEY (`recordID`) REFERENCES `app_bookings_individuals` (`recordID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_individuals_sessions_ibfk_7` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=109293 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_bookings_individuals_sessions`
--

LOCK TABLES `app_bookings_individuals_sessions` WRITE;
/*!40000 ALTER TABLE `app_bookings_individuals_sessions` DISABLE KEYS */;
INSERT INTO `app_bookings_individuals_sessions` VALUES (62137,23,12079,3765,15780,6001,NULL,4.00,0.00,4.00,0,NULL,224,'2016-04-22 12:59:41','2016-04-22 12:59:41'),(62138,23,12079,3765,15782,6001,NULL,4.00,0.00,4.00,0,NULL,224,'2016-04-22 12:59:41','2016-04-22 12:59:41'),(62139,23,12079,3765,15784,6001,NULL,4.00,0.00,4.00,0,NULL,224,'2016-04-22 12:59:41','2016-04-22 12:59:41'),(62140,23,12079,3765,15786,6001,NULL,4.00,0.00,4.00,0,NULL,224,'2016-04-22 12:59:41','2016-04-22 12:59:41'),(62141,23,12079,3765,15788,6001,NULL,4.00,0.00,4.00,0,NULL,224,'2016-04-22 12:59:41','2016-04-22 12:59:41'),(62142,23,12079,3765,15781,6001,NULL,10.00,0.00,10.00,0,NULL,224,'2016-04-22 12:59:41','2016-04-22 12:59:41'),(62143,23,12079,3765,15783,6001,NULL,10.00,0.00,10.00,0,NULL,224,'2016-04-22 12:59:41','2016-04-22 12:59:41'),(62144,23,12079,3765,15785,6001,NULL,10.00,0.00,10.00,0,NULL,224,'2016-04-22 12:59:41','2016-04-22 12:59:41'),(62145,23,12079,3765,15787,6001,NULL,10.00,0.00,10.00,0,NULL,224,'2016-04-22 12:59:41','2016-04-22 12:59:41'),(62146,23,12079,3765,15789,6001,NULL,10.00,0.00,10.00,0,NULL,224,'2016-04-22 12:59:41','2016-04-22 12:59:41'),(62147,23,12079,3765,15794,6001,NULL,4.00,0.00,4.00,0,NULL,224,'2016-04-22 12:59:41','2016-04-22 12:59:41'),(62148,23,12079,3765,15795,6001,NULL,10.00,0.00,10.00,0,NULL,224,'2016-04-22 12:59:41','2016-04-22 12:59:41'),(62149,23,12079,3765,15804,6001,NULL,4.00,0.00,4.00,0,NULL,224,'2016-04-22 12:59:41','2016-04-22 12:59:41'),(62150,23,12079,3765,15808,6001,NULL,4.00,0.00,4.00,0,NULL,224,'2016-04-22 12:59:41','2016-04-22 12:59:41'),(62151,23,12079,3765,15805,6001,NULL,10.00,0.00,10.00,0,NULL,224,'2016-04-22 12:59:41','2016-04-22 12:59:41'),(62152,23,12079,3765,15809,6001,NULL,10.00,0.00,10.00,0,NULL,224,'2016-04-22 12:59:41','2016-04-22 12:59:41'),(62153,23,12079,3765,15780,6000,NULL,4.00,0.00,4.00,0,NULL,224,'2016-04-22 12:59:41','2016-04-22 12:59:41'),(62154,23,12079,3765,15782,6000,NULL,4.00,0.00,4.00,0,NULL,224,'2016-04-22 12:59:41','2016-04-22 12:59:41'),(62155,23,12079,3765,15784,6000,NULL,4.00,0.00,4.00,0,NULL,224,'2016-04-22 12:59:41','2016-04-22 12:59:41'),(62156,23,12079,3765,15786,6000,NULL,4.00,0.00,4.00,0,NULL,224,'2016-04-22 12:59:41','2016-04-22 12:59:41'),(62157,23,12079,3765,15788,6000,NULL,4.00,0.00,4.00,0,NULL,224,'2016-04-22 12:59:41','2016-04-22 12:59:41'),(62158,23,12079,3765,15781,6000,NULL,10.00,0.00,10.00,0,NULL,224,'2016-04-22 12:59:41','2016-04-22 12:59:41'),(62159,23,12079,3765,15783,6000,NULL,10.00,0.00,10.00,0,NULL,224,'2016-04-22 12:59:41','2016-04-22 12:59:41'),(62160,23,12079,3765,15785,6000,NULL,10.00,0.00,10.00,0,NULL,224,'2016-04-22 12:59:41','2016-04-22 12:59:41'),(62161,23,12079,3765,15787,6000,NULL,10.00,0.00,10.00,0,NULL,224,'2016-04-22 12:59:41','2016-04-22 12:59:41'),(62162,23,12079,3765,15789,6000,NULL,10.00,0.00,10.00,0,NULL,224,'2016-04-22 12:59:41','2016-04-22 12:59:41'),(62163,23,12079,3765,15794,6000,NULL,4.00,0.00,4.00,0,NULL,224,'2016-04-22 12:59:41','2016-04-22 12:59:41'),(62164,23,12079,3765,15795,6000,NULL,10.00,0.00,10.00,0,NULL,224,'2016-04-22 12:59:41','2016-04-22 12:59:41'),(62165,23,12079,3765,15804,6000,NULL,4.00,0.00,4.00,0,NULL,224,'2016-04-22 12:59:41','2016-04-22 12:59:41'),(62166,23,12079,3765,15808,6000,NULL,4.00,0.00,4.00,0,NULL,224,'2016-04-22 12:59:41','2016-04-22 12:59:41'),(62167,23,12079,3765,15805,6000,NULL,10.00,0.00,10.00,0,NULL,224,'2016-04-22 12:59:41','2016-04-22 12:59:41'),(62168,23,12079,3765,15809,6000,NULL,10.00,0.00,10.00,0,NULL,224,'2016-04-22 12:59:41','2016-04-22 12:59:41'),(62169,23,12079,3765,15780,5999,NULL,4.00,0.00,4.00,1,NULL,224,'2016-04-22 12:59:41','2017-09-28 14:35:17'),(62170,23,12079,3765,15782,5999,NULL,4.00,0.00,4.00,0,NULL,224,'2016-04-22 12:59:41','2016-04-22 12:59:41'),(62171,23,12079,3765,15784,5999,NULL,4.00,0.00,4.00,0,NULL,224,'2016-04-22 12:59:41','2016-04-22 12:59:41'),(62172,23,12079,3765,15786,5999,NULL,4.00,0.00,4.00,0,NULL,224,'2016-04-22 12:59:41','2016-04-22 12:59:41'),(62173,23,12079,3765,15788,5999,NULL,4.00,0.00,4.00,0,NULL,224,'2016-04-22 12:59:41','2016-04-22 12:59:41'),(62174,23,12079,3765,15781,5999,NULL,10.00,0.00,10.00,1,NULL,224,'2016-04-22 12:59:41','2017-09-28 14:35:19'),(62175,23,12079,3765,15783,5999,NULL,10.00,0.00,10.00,0,NULL,224,'2016-04-22 12:59:41','2016-04-22 12:59:41'),(62176,23,12079,3765,15785,5999,NULL,10.00,0.00,10.00,0,NULL,224,'2016-04-22 12:59:41','2016-04-22 12:59:41'),(62177,23,12079,3765,15787,5999,NULL,10.00,0.00,10.00,0,NULL,224,'2016-04-22 12:59:41','2016-04-22 12:59:41'),(62178,23,12079,3765,15789,5999,NULL,10.00,0.00,10.00,0,NULL,224,'2016-04-22 12:59:41','2016-04-22 12:59:41'),(62179,23,12079,3765,15794,5999,NULL,4.00,0.00,4.00,0,NULL,224,'2016-04-22 12:59:41','2016-04-22 12:59:41'),(62180,23,12079,3765,15795,5999,NULL,10.00,0.00,10.00,0,NULL,224,'2016-04-22 12:59:41','2016-04-22 12:59:41'),(62181,23,12079,3765,15804,5999,NULL,4.00,0.00,4.00,0,NULL,224,'2016-04-22 12:59:41','2016-04-22 12:59:41'),(62182,23,12079,3765,15808,5999,NULL,4.00,0.00,4.00,0,NULL,224,'2016-04-22 12:59:41','2016-04-22 12:59:41'),(62183,23,12079,3765,15805,5999,NULL,10.00,0.00,10.00,0,NULL,224,'2016-04-22 12:59:41','2016-04-22 12:59:41'),(62184,23,12079,3765,15809,5999,NULL,10.00,0.00,10.00,0,NULL,224,'2016-04-22 12:59:41','2016-04-22 12:59:41'),(62185,23,12080,3765,15780,5992,NULL,4.00,0.00,4.00,0,NULL,224,'2016-04-22 13:00:28','2016-04-22 13:00:28'),(62186,23,12080,3765,15781,5992,NULL,10.00,0.00,10.00,0,NULL,224,'2016-04-22 13:00:28','2016-04-22 13:00:28'),(62187,23,12080,3765,15790,5992,NULL,4.00,0.00,4.00,0,NULL,224,'2016-04-22 13:00:28','2016-04-22 13:00:28'),(62188,23,12080,3765,15791,5992,NULL,10.00,0.00,10.00,0,NULL,224,'2016-04-22 13:00:28','2016-04-22 13:00:28'),(62189,23,12080,3765,15800,5992,NULL,4.00,0.00,4.00,0,NULL,224,'2016-04-22 13:00:28','2016-04-22 13:00:28'),(62190,23,12080,3765,15801,5992,NULL,10.00,0.00,10.00,0,NULL,224,'2016-04-22 13:00:28','2016-04-22 13:00:28'),(62191,23,12080,3765,15780,5993,NULL,4.00,0.00,4.00,0,NULL,224,'2016-04-22 13:00:28','2016-04-22 13:00:28'),(62192,23,12080,3765,15781,5993,NULL,10.00,0.00,10.00,0,NULL,224,'2016-04-22 13:00:28','2016-04-22 13:00:28'),(62193,23,12080,3765,15790,5993,NULL,4.00,0.00,4.00,0,NULL,224,'2016-04-22 13:00:28','2016-04-22 13:00:28'),(62194,23,12080,3765,15791,5993,NULL,10.00,0.00,10.00,0,NULL,224,'2016-04-22 13:00:28','2016-04-22 13:00:28'),(62195,23,12080,3765,15800,5993,NULL,4.00,0.00,4.00,0,NULL,224,'2016-04-22 13:00:28','2016-04-22 13:00:28'),(62196,23,12080,3765,15801,5993,NULL,10.00,0.00,10.00,0,NULL,224,'2016-04-22 13:00:28','2016-04-22 13:00:28'),(62197,23,12081,3770,15841,NULL,'2016-06-08',100.00,0.00,100.00,0,NULL,224,'2016-04-22 13:20:26','2016-04-22 13:20:26'),(62198,23,12081,3770,15842,NULL,'2016-06-08',0.00,0.00,0.00,0,NULL,224,'2016-04-22 13:20:26','2016-04-22 13:20:26'),(62199,23,12082,3770,15841,NULL,'2016-06-08',100.00,0.00,100.00,0,NULL,224,'2016-04-22 13:20:43','2016-04-22 13:20:43'),(62200,23,12082,3770,15842,NULL,'2016-06-08',0.00,0.00,0.00,0,NULL,224,'2016-04-22 13:20:43','2016-04-22 13:20:43'),(62201,23,12083,3765,15780,5991,NULL,4.00,0.00,4.00,1,NULL,224,'2016-04-22 14:57:04','2017-09-28 14:35:16'),(62202,23,12083,3765,15782,5991,NULL,4.00,0.00,4.00,0,NULL,224,'2016-04-22 14:57:04','2016-04-22 14:57:04'),(62203,23,12083,3765,15781,5991,NULL,10.00,0.00,10.00,1,NULL,224,'2016-04-22 14:57:04','2017-09-28 14:35:18'),(62204,23,12083,3765,15783,5991,NULL,10.00,0.00,10.00,0,NULL,224,'2016-04-22 14:57:04','2016-04-22 14:57:04'),(62205,23,12083,3765,15787,5991,NULL,10.00,0.00,10.00,0,NULL,224,'2016-04-22 14:57:04','2016-04-22 14:57:04'),(62206,23,12083,3765,15789,5991,NULL,10.00,0.00,10.00,0,NULL,224,'2016-04-22 14:57:04','2016-04-22 14:57:04'),(62207,23,12083,3765,15790,5991,NULL,4.00,0.00,4.00,0,NULL,224,'2016-04-22 14:57:04','2016-04-22 14:57:04'),(62208,23,12083,3765,15791,5991,NULL,10.00,0.00,10.00,0,NULL,224,'2016-04-22 14:57:04','2016-04-26 12:36:12'),(62209,23,12083,3765,15793,5991,NULL,10.00,0.00,10.00,0,NULL,224,'2016-04-22 14:57:04','2016-04-22 14:57:04'),(62210,23,12083,3765,15795,5991,NULL,10.00,0.00,10.00,0,NULL,224,'2016-04-22 14:57:04','2016-04-22 14:57:04'),(62211,23,12083,3765,15797,5991,NULL,10.00,0.00,10.00,0,NULL,224,'2016-04-22 14:57:04','2016-04-22 14:57:04'),(62212,23,12083,3765,15800,5991,NULL,4.00,0.00,4.00,0,NULL,224,'2016-04-22 14:57:04','2016-04-22 14:57:04'),(62213,23,12083,3765,15804,5991,NULL,4.00,0.00,4.00,0,NULL,224,'2016-04-22 14:57:04','2016-04-22 14:57:04'),(62214,23,12083,3765,15801,5991,NULL,10.00,0.00,10.00,0,NULL,224,'2016-04-22 14:57:04','2016-04-22 14:57:04'),(62215,23,12083,3765,15803,5991,NULL,10.00,0.00,10.00,0,NULL,224,'2016-04-22 14:57:04','2016-04-22 14:57:04'),(62216,23,12083,3765,15805,5991,NULL,10.00,0.00,10.00,0,NULL,224,'2016-04-22 14:57:04','2016-04-22 14:57:04'),(62217,23,12083,3765,15807,5991,NULL,10.00,0.00,10.00,0,NULL,224,'2016-04-22 14:57:04','2016-04-22 14:57:04'),(62218,23,12084,3766,15825,5991,NULL,10.00,0.00,10.00,0,NULL,224,'2016-04-22 14:57:45','2016-04-22 14:57:45'),(62219,23,12084,3766,15831,5991,NULL,10.00,0.00,10.00,0,NULL,224,'2016-04-22 14:57:45','2016-04-22 14:57:45'),(62220,23,12084,3766,15827,5991,NULL,10.00,0.00,10.00,0,NULL,224,'2016-04-22 14:57:45','2016-04-22 14:57:45'),(62221,23,12084,3766,15829,5991,NULL,10.00,0.00,10.00,0,NULL,224,'2016-04-22 14:57:45','2016-04-22 14:57:45'),(62222,23,12085,3766,15812,5992,NULL,4.00,0.00,4.00,0,NULL,224,'2016-04-22 14:58:35','2016-04-22 14:58:35'),(62223,23,12085,3766,15811,5992,NULL,10.00,0.00,10.00,0,NULL,224,'2016-04-22 14:58:35','2016-04-22 14:58:35'),(62224,23,12085,3766,15820,5992,NULL,4.00,0.00,4.00,0,NULL,224,'2016-04-22 14:58:35','2016-04-22 14:58:35'),(62225,23,12085,3766,15819,5992,NULL,10.00,0.00,10.00,0,NULL,224,'2016-04-22 14:58:35','2016-04-22 14:58:35'),(62226,23,12085,3766,15828,5992,NULL,4.00,0.00,4.00,0,NULL,224,'2016-04-22 14:58:35','2016-04-22 14:58:35'),(62227,23,12085,3766,15827,5992,NULL,10.00,0.00,10.00,0,NULL,224,'2016-04-22 14:58:35','2016-04-22 14:58:35'),(62228,23,12085,3766,15812,5993,NULL,4.00,0.00,4.00,0,NULL,224,'2016-04-22 14:58:35','2016-04-22 14:58:35'),(62229,23,12085,3766,15811,5993,NULL,10.00,0.00,10.00,0,NULL,224,'2016-04-22 14:58:35','2016-04-22 14:58:35'),(62230,23,12085,3766,15820,5993,NULL,4.00,0.00,4.00,0,NULL,224,'2016-04-22 14:58:35','2016-04-22 14:58:35'),(62231,23,12085,3766,15819,5993,NULL,10.00,0.00,10.00,0,NULL,224,'2016-04-22 14:58:35','2016-04-22 14:58:35'),(62232,23,12085,3766,15828,5993,NULL,4.00,0.00,4.00,0,NULL,224,'2016-04-22 14:58:35','2016-04-22 14:58:35'),(62233,23,12085,3766,15827,5993,NULL,10.00,0.00,10.00,0,NULL,224,'2016-04-22 14:58:35','2016-04-22 14:58:35'),(62234,23,12086,3770,15841,NULL,'2016-06-08',100.00,0.00,100.00,0,NULL,224,'2016-04-22 14:59:13','2016-04-22 14:59:13'),(62235,23,12086,3770,15842,NULL,'2016-06-08',0.00,0.00,0.00,0,NULL,224,'2016-04-22 14:59:13','2016-04-22 14:59:13');
/*!40000 ALTER TABLE `app_bookings_individuals_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_bookings_invoices`
--

DROP TABLE IF EXISTS `app_bookings_invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_bookings_invoices` (
  `invoiceID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `bookingID` int(11) NOT NULL,
  `byID` int(11) DEFAULT NULL,
  `invoiceNumber` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `invoiceDate` date NOT NULL,
  `type` enum('booking','blocks','contract pricing','other','participants per block','particpants per session') COLLATE utf8_unicode_ci DEFAULT NULL,
  `desc` text COLLATE utf8_unicode_ci,
  `amount` decimal(8,2) NOT NULL,
  `note` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `is_invoiced` tinyint(1) NOT NULL DEFAULT '0',
  `added` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`invoiceID`),
  KEY `fk_bookings_invoices_bookingID` (`bookingID`),
  KEY `fk_bookings_invoices_byID` (`byID`),
  KEY `fk_bookings_invoices_accountID` (`accountID`),
  CONSTRAINT `app_bookings_invoices_ibfk_1` FOREIGN KEY (`bookingID`) REFERENCES `app_bookings` (`bookingID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_invoices_ibfk_2` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_invoices_ibfk_3` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=970 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_bookings_invoices`
--

LOCK TABLES `app_bookings_invoices` WRITE;
/*!40000 ALTER TABLE `app_bookings_invoices` DISABLE KEYS */;
INSERT INTO `app_bookings_invoices` VALUES (236,23,3758,224,'1234','2016-01-04','blocks','&lt;label&gt;Calculation&lt;/label&gt;&lt;table class=&quot;table table-striped table-bordered&quot;&gt;&lt;thead&gt;&lt;tr&gt;&lt;th&gt;Item&lt;/th&gt;&lt;th&gt;Sub Total&lt;/th&gt;&lt;/tr&gt;&lt;/thead&gt;&lt;tbody&gt;&lt;tr&gt;&lt;td&gt;90 x PPA @ £60.00&lt;/td&gt;&lt;td&gt;£5,400.00&lt;/td&gt;&lt;/tr&gt;&lt;tr&gt;&lt;td&gt;Total&lt;/td&gt;&lt;td&gt;£5,400.00&lt;/td&gt;&lt;/tr&gt;&lt;/tbody&gt;&lt;/table&gt;',5400.00,'Paid',1,'2016-04-22 13:15:27','2016-04-22 13:15:29'),(237,23,3758,224,'1235','2016-04-11','blocks','&lt;label&gt;Calculation&lt;/label&gt;&lt;table class=&quot;table table-striped table-bordered&quot;&gt;&lt;thead&gt;&lt;tr&gt;&lt;th&gt;Item&lt;/th&gt;&lt;th&gt;Sub Total&lt;/th&gt;&lt;/tr&gt;&lt;/thead&gt;&lt;tbody&gt;&lt;tr&gt;&lt;td&gt;72 x PPA @ £60.00&lt;/td&gt;&lt;td&gt;£4,320.00&lt;/td&gt;&lt;/tr&gt;&lt;tr&gt;&lt;td&gt;Total&lt;/td&gt;&lt;td&gt;£4,320.00&lt;/td&gt;&lt;/tr&gt;&lt;/tbody&gt;&lt;/table&gt;',4320.00,'',1,'2016-04-22 13:15:53','2016-04-22 14:51:12'),(238,23,3758,224,'4257','2016-09-05','blocks','&lt;label&gt;Calculation&lt;/label&gt;&lt;table class=&quot;table table-striped table-bordered&quot;&gt;&lt;thead&gt;&lt;tr&gt;&lt;th&gt;Item&lt;/th&gt;&lt;th&gt;Sub Total&lt;/th&gt;&lt;/tr&gt;&lt;/thead&gt;&lt;tbody&gt;&lt;tr&gt;&lt;td&gt;96 x PPA @ £60.00&lt;/td&gt;&lt;td&gt;£5,760.00&lt;/td&gt;&lt;/tr&gt;&lt;tr&gt;&lt;td&gt;Total&lt;/td&gt;&lt;td&gt;£5,760.00&lt;/td&gt;&lt;/tr&gt;&lt;/tbody&gt;&lt;/table&gt;',5760.00,'',1,'2016-04-22 13:16:29','2016-04-22 14:51:14'),(239,23,3769,224,'4104','2016-04-11','blocks','&lt;label&gt;Calculation&lt;/label&gt;&lt;table class=&quot;table table-striped table-bordered&quot;&gt;&lt;thead&gt;&lt;tr&gt;&lt;th&gt;Item&lt;/th&gt;&lt;th&gt;Sub Total&lt;/th&gt;&lt;/tr&gt;&lt;/thead&gt;&lt;tbody&gt;&lt;tr&gt;&lt;td&gt;4 x Bikeability @ £50.00&lt;/td&gt;&lt;td&gt;£200.00&lt;/td&gt;&lt;/tr&gt;&lt;tr&gt;&lt;td&gt;Total&lt;/td&gt;&lt;td&gt;£200.00&lt;/td&gt;&lt;/tr&gt;&lt;/tbody&gt;&lt;/table&gt;',200.00,'',1,'2016-04-22 14:31:23','2016-04-22 14:41:24'),(240,23,3769,224,'3930','2016-04-11','blocks','&lt;label&gt;Calculation&lt;/label&gt;&lt;table class=&quot;table table-striped table-bordered&quot;&gt;&lt;thead&gt;&lt;tr&gt;&lt;th&gt;Item&lt;/th&gt;&lt;th&gt;Sub Total&lt;/th&gt;&lt;/tr&gt;&lt;/thead&gt;&lt;tbody&gt;&lt;tr&gt;&lt;td&gt;4 x Bikeability @ £50.00&lt;/td&gt;&lt;td&gt;£200.00&lt;/td&gt;&lt;/tr&gt;&lt;tr&gt;&lt;td&gt;Total&lt;/td&gt;&lt;td&gt;£200.00&lt;/td&gt;&lt;/tr&gt;&lt;/tbody&gt;&lt;/table&gt;',200.00,'',0,'2016-04-22 14:53:02','2016-04-22 14:53:02'),(241,23,3762,224,'4257','2016-04-04','blocks','&lt;label&gt;Calculation&lt;/label&gt;&lt;table class=&quot;table table-striped table-bordered&quot;&gt;&lt;thead&gt;&lt;tr&gt;&lt;th&gt;Item&lt;/th&gt;&lt;th&gt;Sub Total&lt;/th&gt;&lt;/tr&gt;&lt;/thead&gt;&lt;tbody&gt;&lt;tr&gt;&lt;td&gt;88 x Extra Curricular @ £60.00&lt;/td&gt;&lt;td&gt;£5,280.00&lt;/td&gt;&lt;/tr&gt;&lt;tr&gt;&lt;td&gt;Total&lt;/td&gt;&lt;td&gt;£5,280.00&lt;/td&gt;&lt;/tr&gt;&lt;/tbody&gt;&lt;/table&gt;',5280.00,'',0,'2016-04-22 14:53:51','2016-04-22 14:53:51'),(242,23,3759,224,'4269','2016-01-04','blocks','&lt;label&gt;Calculation&lt;/label&gt;&lt;table class=&quot;table table-striped table-bordered&quot;&gt;&lt;thead&gt;&lt;tr&gt;&lt;th&gt;Item&lt;/th&gt;&lt;th&gt;Sub Total&lt;/th&gt;&lt;/tr&gt;&lt;/thead&gt;&lt;tbody&gt;&lt;tr&gt;&lt;td&gt;44 x Extra Curricular @ £60.00&lt;/td&gt;&lt;td&gt;£2,640.00&lt;/td&gt;&lt;/tr&gt;&lt;tr&gt;&lt;td&gt;44 x PPA @ £60.00&lt;/td&gt;&lt;td&gt;£2,640.00&lt;/td&gt;&lt;/tr&gt;&lt;tr&gt;&lt;td&gt;Total&lt;/td&gt;&lt;td&gt;£5,280.00&lt;/td&gt;&lt;/tr&gt;&lt;/tbody&gt;&lt;/table&gt;',5280.00,'',0,'2016-04-22 14:54:48','2016-04-22 14:54:48');
/*!40000 ALTER TABLE `app_bookings_invoices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_bookings_invoices_blocks`
--

DROP TABLE IF EXISTS `app_bookings_invoices_blocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_bookings_invoices_blocks` (
  `linkID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `invoiceID` int(11) NOT NULL,
  `blockID` int(11) NOT NULL,
  PRIMARY KEY (`linkID`),
  KEY `fk_bookings_invoices_blocks_invoiceID` (`invoiceID`),
  KEY `fk_bookings_invoices_blocks_blockID` (`blockID`),
  KEY `fk_bookings_invoices_blocks_accountID` (`accountID`),
  CONSTRAINT `app_bookings_invoices_blocks_ibfk_1` FOREIGN KEY (`invoiceID`) REFERENCES `app_bookings_invoices` (`invoiceID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_invoices_blocks_ibfk_2` FOREIGN KEY (`blockID`) REFERENCES `app_bookings_blocks` (`blockID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_invoices_blocks_ibfk_3` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1585 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_bookings_invoices_blocks`
--

LOCK TABLES `app_bookings_invoices_blocks` WRITE;
/*!40000 ALTER TABLE `app_bookings_invoices_blocks` DISABLE KEYS */;
INSERT INTO `app_bookings_invoices_blocks` VALUES (437,23,236,49562),(438,23,237,49563),(440,23,238,49564),(441,23,239,49593),(442,23,240,49594),(443,23,241,49576),(444,23,241,49577),(445,23,241,49578),(446,23,242,49565),(447,23,242,49568),(448,23,242,49569);
/*!40000 ALTER TABLE `app_bookings_invoices_blocks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_bookings_lessons`
--

DROP TABLE IF EXISTS `app_bookings_lessons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_bookings_lessons` (
  `lessonID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `bookingID` int(11) DEFAULT NULL,
  `blockID` int(11) NOT NULL,
  `addressID` int(11) DEFAULT NULL,
  `byID` int(11) DEFAULT NULL,
  `location` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `day` enum('monday','tuesday','wednesday','thursday','friday','saturday','sunday') COLLATE utf8_unicode_ci NOT NULL,
  `group` enum('f1','f2','yr1','yr2','yr3','yr4','yr5','yr6','yr7','yr8','yr9','yr10','yr11','yr12','yr13','yr1+2','yr3+4','yr5+6','ks1','ks2','ks3','ks4','ks1+2','ks3+4','other') COLLATE utf8_unicode_ci DEFAULT NULL,
  `group_other` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `activityID` int(11) DEFAULT NULL,
  `activity_other` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `activity_desc` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `typeID` int(11) DEFAULT NULL,
  `type_other` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `plan` enum('unit1','unit2','unit3','unit4','unit5','unit6','unit7','unit8','unit9','unit10','unit11','unit12','other') COLLATE utf8_unicode_ci NOT NULL,
  `plan_other` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `charge` enum('default','prepaid','free','other') COLLATE utf8_unicode_ci NOT NULL,
  `charge_other` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `class_size` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `startDate` date DEFAULT NULL,
  `endDate` date DEFAULT NULL,
  `startTime` time DEFAULT NULL,
  `endTime` time DEFAULT NULL,
  `req_ppa_playground` tinyint(1) DEFAULT '0',
  `req_ppa_classroom` tinyint(1) DEFAULT '0',
  `req_ppa_meet` tinyint(1) DEFAULT '0',
  `req_ppa_reg` tinyint(1) DEFAULT '0',
  `req_ppa_changed_before` tinyint(1) DEFAULT '0',
  `req_ppa_changed_after` tinyint(1) DEFAULT '0',
  `req_ppa_dismissed` tinyint(1) DEFAULT '0',
  `req_ppa_assist` tinyint(1) DEFAULT '0',
  `req_extra_perf` tinyint(1) DEFAULT '0',
  `req_extra_cert` tinyint(1) DEFAULT '0',
  `req_extra_reg` tinyint(1) DEFAULT '0',
  `req_extra_money` tinyint(1) DEFAULT '0',
  `req_extra_children` tinyint(1) DEFAULT '0',
  `target_participants` int(11) NOT NULL DEFAULT '0',
  `staff_required_assistant` int(3) NOT NULL DEFAULT '0',
  `booking_cutoff` int(3) DEFAULT NULL,
  `min_age` int(3) DEFAULT NULL,
  `staff_required_lead` int(3) NOT NULL DEFAULT '0',
  `staff_required_head` int(3) NOT NULL DEFAULT '0',
  `offer_accept_status` enum('off','offering','assigned','exhausted','expired') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'off',
  `offer_accept_groupID` int(11) DEFAULT NULL,
  `offer_accept_reason` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `added` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`lessonID`),
  KEY `fk_bookings_lessons_bookingID` (`bookingID`),
  KEY `fk_bookings_lessons_byID` (`byID`),
  KEY `fk_bookings_lessons_addressID` (`addressID`),
  KEY `fk_bookings_lessons_blockID` (`blockID`),
  KEY `fk_bookings_lessons_accountID` (`accountID`),
  KEY `activityID` (`activityID`),
  KEY `typeID` (`typeID`),
  KEY `fk_bookings_lessons_offer_accept_groupID` (`offer_accept_groupID`),
  CONSTRAINT `app_bookings_lessons_ibfk_2` FOREIGN KEY (`bookingID`) REFERENCES `app_bookings` (`bookingID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_lessons_ibfk_3` FOREIGN KEY (`addressID`) REFERENCES `app_orgs_addresses` (`addressID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_lessons_ibfk_4` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_lessons_ibfk_5` FOREIGN KEY (`blockID`) REFERENCES `app_bookings_blocks` (`blockID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_lessons_ibfk_6` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_bookings_lessons_activityID` FOREIGN KEY (`activityID`) REFERENCES `app_activities` (`activityID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `fk_bookings_lessons_offer_accept_groupID` FOREIGN KEY (`offer_accept_groupID`) REFERENCES `app_offer_accept_groups` (`groupID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_bookings_lessons_typeID` FOREIGN KEY (`typeID`) REFERENCES `app_lesson_types` (`typeID`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=54501 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_bookings_lessons`
--

LOCK TABLES `app_bookings_lessons` WRITE;
/*!40000 ALTER TABLE `app_bookings_lessons` DISABLE KEYS */;
INSERT INTO `app_bookings_lessons` VALUES (15666,23,3757,49559,2325,224,'Hall','monday','yr5','',50,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'13:15:00','14:20:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 08:40:39','2016-04-15 08:40:39'),(15667,23,3757,49559,2325,224,'Hall','monday','yr4','',50,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'14:20:00','15:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 08:41:04','2016-04-15 08:41:40'),(15668,23,3757,49559,2325,224,'Hall','tuesday','yr4','',50,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'13:15:00','14:20:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 09:03:25','2016-04-15 09:03:25'),(15669,23,3757,49559,2325,224,'Hall','tuesday','yr4','',50,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'14:20:00','15:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 09:04:01','2016-04-15 09:04:01'),(15670,23,3757,49559,2325,224,'Hall','wednesday','yr6','',50,'','',99,'','unit1',NULL,'default','','27',NULL,NULL,NULL,'13:15:00','14:20:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 09:04:38','2016-04-15 09:04:38'),(15671,23,3757,49559,2325,224,'Hall','wednesday','yr3','',51,'','',99,'','unit1',NULL,'default','','25',NULL,NULL,NULL,'14:20:00','15:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 09:06:53','2016-04-15 09:06:53'),(15672,23,3757,49559,2325,224,'Hall','thursday','yr6','',50,'','',99,'','unit1',NULL,'default','','27',NULL,NULL,NULL,'13:15:00','14:20:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 09:07:36','2016-04-15 09:07:36'),(15673,23,3757,49559,2325,224,'Hall','thursday','yr6','',50,'','',99,'','unit1',NULL,'default','','27',NULL,NULL,NULL,'14:20:00','15:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 09:10:24','2016-04-15 09:10:24'),(15674,23,3757,49559,2325,224,'Hall','friday','yr5','',50,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'09:50:00','10:55:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 09:13:21','2016-04-15 09:13:21'),(15675,23,3757,49559,2325,224,'Hall','friday','yr5','',50,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'11:10:00','12:15:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 09:13:48','2016-04-15 09:13:48'),(15676,23,3757,49560,2325,224,'Hall','monday','yr5','',50,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'13:15:00','14:20:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 09:53:17','2016-04-15 09:53:17'),(15677,23,3757,49560,2325,224,'Hall','monday','yr4','',50,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'14:20:00','15:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 09:53:17','2016-04-15 09:53:17'),(15678,23,3757,49560,2325,224,'Hall','tuesday','yr4','',50,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'13:15:00','14:20:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 09:53:17','2016-04-15 09:53:17'),(15679,23,3757,49560,2325,224,'Hall','tuesday','yr4','',50,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'14:20:00','15:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 09:53:17','2016-04-15 09:53:17'),(15680,23,3757,49560,2325,224,'Hall','wednesday','yr6','',50,'','',99,'','unit1',NULL,'default','','27',NULL,NULL,NULL,'13:15:00','14:20:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 09:53:17','2016-04-15 09:53:17'),(15681,23,3757,49560,2325,224,'Hall','wednesday','yr3','',51,'','',99,'','unit1',NULL,'default','','25',NULL,NULL,NULL,'14:20:00','15:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 09:53:17','2016-04-15 09:53:17'),(15682,23,3757,49560,2325,224,'Hall','thursday','yr6','',50,'','',99,'','unit1',NULL,'default','','27',NULL,NULL,NULL,'13:15:00','14:20:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 09:53:17','2016-04-15 09:53:17'),(15683,23,3757,49560,2325,224,'Hall','thursday','yr6','',50,'','',99,'','unit1',NULL,'default','','27',NULL,NULL,NULL,'14:20:00','15:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 09:53:17','2016-04-15 09:53:17'),(15684,23,3757,49560,2325,224,'Hall','friday','yr5','',50,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'09:50:00','10:55:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 09:53:17','2016-04-15 09:53:17'),(15685,23,3757,49560,2325,224,'Hall','friday','yr5','',50,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'11:10:00','12:15:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 09:53:17','2016-04-15 09:53:17'),(15686,23,3757,49561,2325,224,'Hall','monday','yr5','',50,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'13:15:00','14:20:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 09:53:21','2016-04-15 09:53:21'),(15687,23,3757,49561,2325,224,'Hall','monday','yr4','',50,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'14:20:00','15:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 09:53:21','2016-04-15 09:53:21'),(15688,23,3757,49561,2325,224,'Hall','tuesday','yr4','',50,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'13:15:00','14:20:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 09:53:21','2016-04-15 09:53:21'),(15689,23,3757,49561,2325,224,'Hall','tuesday','yr4','',50,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'14:20:00','15:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 09:53:21','2016-04-15 09:53:21'),(15690,23,3757,49561,2325,224,'Hall','wednesday','yr6','',50,'','',99,'','unit1',NULL,'default','','27',NULL,NULL,NULL,'13:15:00','14:20:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 09:53:21','2016-04-15 09:53:21'),(15691,23,3757,49561,2325,224,'Hall','wednesday','yr3','',51,'','',99,'','unit1',NULL,'default','','25',NULL,NULL,NULL,'14:20:00','15:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 09:53:21','2016-04-15 09:53:21'),(15692,23,3757,49561,2325,224,'Hall','thursday','yr6','',50,'','',99,'','unit1',NULL,'default','','27',NULL,NULL,NULL,'13:15:00','14:20:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 09:53:21','2016-04-15 09:53:21'),(15693,23,3757,49561,2325,224,'Hall','thursday','yr6','',50,'','',99,'','unit1',NULL,'default','','27',NULL,NULL,NULL,'14:20:00','15:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 09:53:22','2016-04-15 09:53:22'),(15694,23,3757,49561,2325,224,'Hall','friday','yr5','',50,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'09:50:00','10:55:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 09:53:22','2016-04-15 09:53:22'),(15695,23,3757,49561,2325,224,'Hall','friday','yr5','',50,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'11:10:00','12:15:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 09:53:22','2016-04-15 09:53:22'),(15696,23,3758,49562,2327,224,'Hall','tuesday','yr1','',52,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'13:00:00','14:00:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:03:23','2016-04-15 10:03:23'),(15697,23,3758,49562,2327,224,'Hall','tuesday','yr1','',52,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'14:15:00','15:15:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:03:55','2016-04-15 10:03:55'),(15698,23,3758,49562,2327,224,'Hall','wednesday','yr2','',52,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'13:00:00','14:00:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:04:34','2016-04-15 10:04:34'),(15699,23,3758,49562,2327,224,'Hall','wednesday','yr2','',52,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'14:15:00','15:15:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:05:04','2016-04-15 10:05:04'),(15700,23,3758,49562,2327,224,'Hall','thursday','yr4','',52,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'13:00:00','14:00:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:05:26','2016-04-15 10:05:26'),(15701,23,3758,49562,2327,224,'Hall','thursday','yr4','',52,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'14:15:00','15:15:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:06:00','2016-04-15 10:06:00'),(15702,23,3758,49563,2327,224,'Hall','tuesday','yr1','',52,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'13:00:00','14:00:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:06:16','2016-04-15 10:06:16'),(15703,23,3758,49563,2327,224,'Hall','tuesday','yr1','',52,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'14:15:00','15:15:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:06:16','2016-04-15 10:06:16'),(15704,23,3758,49563,2327,224,'Hall','wednesday','yr2','',52,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'13:00:00','14:00:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:06:16','2016-04-15 10:06:16'),(15705,23,3758,49563,2327,224,'Hall','wednesday','yr2','',52,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'14:15:00','15:15:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:06:16','2016-04-15 10:06:16'),(15706,23,3758,49563,2327,224,'Hall','thursday','yr4','',52,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'13:00:00','14:00:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:06:16','2016-04-15 10:06:16'),(15707,23,3758,49563,2327,224,'Hall','thursday','yr4','',52,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'14:15:00','15:15:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:06:16','2016-04-15 10:06:16'),(15708,23,3758,49564,2327,224,'Hall','tuesday','yr1','',52,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'13:00:00','14:00:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:06:19','2016-04-15 10:06:19'),(15709,23,3758,49564,2327,224,'Hall','tuesday','yr1','',52,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'14:15:00','15:15:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:06:19','2016-04-15 10:06:19'),(15710,23,3758,49564,2327,224,'Hall','wednesday','yr2','',52,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'13:00:00','14:00:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:06:19','2016-04-15 10:06:19'),(15711,23,3758,49564,2327,224,'Hall','wednesday','yr2','',52,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'14:15:00','15:15:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:06:19','2016-04-15 10:06:19'),(15712,23,3758,49564,2327,224,'Hall','thursday','yr4','',52,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'13:00:00','14:00:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:06:19','2016-04-15 10:06:19'),(15713,23,3758,49564,2327,224,'Hall','thursday','yr4','',52,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'14:15:00','15:15:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:06:19','2016-04-15 10:06:19'),(15714,23,3759,49565,2329,224,'Outdoors','monday','other','Class 1',50,'','',101,'','unit1',NULL,'default','','25',NULL,NULL,NULL,'12:30:00','13:15:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:13:40','2016-04-15 10:13:40'),(15715,23,3759,49565,2329,224,'Outdoors/ Hall','monday','other','Class 1',50,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'14:25:00','15:25:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:14:26','2016-04-15 10:14:26'),(15716,23,3759,49568,2329,224,'Outdoors','monday','other','Class 1',50,'','',101,'','unit1',NULL,'default','','25',NULL,NULL,NULL,'12:30:00','13:15:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:14:55','2016-04-15 10:14:55'),(15717,23,3759,49568,2329,224,'Outdoors/ Hall','monday','other','Class 1',50,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'14:25:00','15:25:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:14:55','2016-04-15 10:14:55'),(15718,23,3759,49569,2329,224,'Outdoors','monday','other','Class 1',50,'','',101,'','unit1',NULL,'default','','25',NULL,NULL,NULL,'12:30:00','13:15:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:14:58','2016-04-15 10:14:58'),(15719,23,3759,49569,2329,224,'Outdoors/ Hall','monday','other','Class 1',50,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'14:25:00','15:25:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:14:58','2016-04-15 10:14:58'),(15721,23,3760,49570,2326,224,'Hall','wednesday','other','Reception',51,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'13:15:00','14:15:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:21:39','2016-04-15 10:21:39'),(15722,23,3760,49570,2326,224,'Hall','wednesday','other','Reception',51,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'14:15:00','15:20:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:22:12','2016-04-15 10:22:12'),(15724,23,3760,49570,2326,224,'Hall','thursday','yr2','',51,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'13:15:00','14:15:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:24:37','2016-04-15 10:24:37'),(15725,23,3760,49570,2326,224,'Hall','thursday','yr2','',51,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'14:15:00','15:20:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:25:05','2016-04-15 10:25:05'),(15727,23,3760,49570,2326,224,'Hall','friday','yr1','',51,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'13:15:00','14:15:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:26:39','2016-04-15 10:26:39'),(15728,23,3760,49570,2326,224,'Hall','friday','yr1','',51,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'14:15:00','15:20:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:27:06','2016-04-15 10:27:06'),(15730,23,3760,49571,2326,224,'Hall','wednesday','other','Reception',51,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'13:15:00','14:15:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:28:06','2016-04-15 10:28:06'),(15731,23,3760,49571,2326,224,'Hall','wednesday','other','Reception',51,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'14:15:00','15:20:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:28:06','2016-04-15 10:28:06'),(15733,23,3760,49571,2326,224,'Hall','thursday','yr2','',51,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'13:15:00','14:15:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:28:06','2016-04-15 10:28:06'),(15734,23,3760,49571,2326,224,'Hall','thursday','yr2','',51,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'14:15:00','15:20:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:28:06','2016-04-15 10:28:06'),(15736,23,3760,49571,2326,224,'Hall','friday','yr1','',51,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'13:15:00','14:15:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:28:06','2016-04-15 10:28:06'),(15737,23,3760,49571,2326,224,'Hall','friday','yr1','',51,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'14:15:00','15:20:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:28:06','2016-04-15 10:28:06'),(15739,23,3760,49572,2326,224,'Hall','wednesday','other','Reception',51,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'13:15:00','14:15:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:28:09','2016-04-15 10:28:09'),(15740,23,3760,49572,2326,224,'Hall','wednesday','other','Reception',51,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'14:15:00','15:20:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:28:09','2016-04-15 10:28:09'),(15742,23,3760,49572,2326,224,'Hall','thursday','yr2','',51,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'13:15:00','14:15:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:28:09','2016-04-15 10:28:09'),(15743,23,3760,49572,2326,224,'Hall','thursday','yr2','',51,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'14:15:00','15:20:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:28:09','2016-04-15 10:28:09'),(15745,23,3760,49572,2326,224,'Hall','friday','yr1','',51,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'13:15:00','14:15:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:28:09','2016-04-15 10:28:09'),(15746,23,3760,49572,2326,224,'Hall','friday','yr1','',51,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'14:15:00','15:20:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:28:09','2016-04-15 10:28:09'),(15747,23,3761,49573,2328,224,'Hall','tuesday','yr6','',50,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'13:15:00','14:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:39:54','2016-04-15 10:39:54'),(15748,23,3761,49573,2328,224,'Hall','tuesday','yr6','',50,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'14:45:00','15:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:40:32','2016-04-15 10:40:32'),(15749,23,3761,49573,2328,224,'Playground','wednesday','yr4','',50,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'13:15:00','14:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:41:18','2016-04-15 10:41:18'),(15750,23,3761,49573,2328,224,'Hall','wednesday','yr4','',50,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'14:45:00','15:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:41:49','2016-04-15 10:41:49'),(15751,23,3761,49573,2328,224,'Playground','thursday','yr2','',50,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'13:15:00','14:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:42:37','2016-04-15 10:42:37'),(15752,23,3761,49573,2328,224,'Playground','thursday','yr2','',50,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'14:45:00','15:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:43:12','2016-04-15 10:43:12'),(15753,23,3761,49574,2328,224,'Hall','tuesday','yr6','',50,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'13:15:00','14:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:43:22','2016-04-15 10:43:22'),(15754,23,3761,49574,2328,224,'Hall','tuesday','yr6','',50,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'14:45:00','15:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:43:22','2016-04-15 10:43:22'),(15755,23,3761,49574,2328,224,'Playground','wednesday','yr4','',50,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'13:15:00','14:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:43:22','2016-04-15 10:43:22'),(15756,23,3761,49574,2328,224,'Hall','wednesday','yr4','',50,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'14:45:00','15:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:43:22','2016-04-15 10:43:22'),(15757,23,3761,49574,2328,224,'Playground','thursday','yr2','',50,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'13:15:00','14:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:43:22','2016-04-15 10:43:22'),(15758,23,3761,49574,2328,224,'Playground','thursday','yr2','',50,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'14:45:00','15:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:43:22','2016-04-15 10:43:22'),(15759,23,3761,49575,2328,224,'Hall','tuesday','yr6','',50,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'13:15:00','14:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:43:30','2016-04-15 10:43:30'),(15760,23,3761,49575,2328,224,'Hall','tuesday','yr6','',50,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'14:45:00','15:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:43:30','2016-04-15 10:43:30'),(15761,23,3761,49575,2328,224,'Playground','wednesday','yr4','',50,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'13:15:00','14:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:43:30','2016-04-15 10:43:30'),(15762,23,3761,49575,2328,224,'Hall','wednesday','yr4','',50,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'14:45:00','15:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:43:30','2016-04-15 10:43:30'),(15763,23,3761,49575,2328,224,'Playground','thursday','yr2','',50,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'13:15:00','14:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:43:30','2016-04-15 10:43:30'),(15764,23,3761,49575,2328,224,'Playground','thursday','yr2','',50,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'14:45:00','15:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:43:30','2016-04-15 10:43:30'),(15765,23,3762,49576,2327,224,'SPA','monday','ks1','',50,'','',101,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'15:15:00','16:15:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:47:55','2016-04-15 10:47:55'),(15766,23,3762,49576,2327,224,'SPA','wednesday','ks2','',50,'','',101,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'15:15:00','16:15:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:48:27','2016-04-15 10:48:27'),(15767,23,3762,49577,2327,224,'SPA','monday','ks1','',50,'','',101,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'15:15:00','16:15:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:48:34','2016-04-15 10:48:34'),(15768,23,3762,49577,2327,224,'SPA','wednesday','ks2','',50,'','',101,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'15:15:00','16:15:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:48:34','2016-04-15 10:48:34'),(15769,23,3762,49578,2327,224,'SPA','monday','ks1','',50,'','',101,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'15:15:00','16:15:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:48:37','2016-04-15 10:48:37'),(15770,23,3762,49578,2327,224,'SPA','wednesday','ks2','',50,'','',101,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'15:15:00','16:15:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 10:48:37','2016-04-15 10:48:37'),(15771,23,3763,49579,2325,224,'Hall','thursday','ks2','',50,'','',101,'','unit1',NULL,'default','','25',NULL,NULL,NULL,'08:00:00','08:45:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 12:08:27','2016-04-15 12:08:27'),(15772,23,3763,49579,2325,224,'Hall','thursday','ks2','',51,'','',101,'','unit1',NULL,'default','','25',NULL,NULL,NULL,'15:00:00','16:00:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 12:14:55','2016-04-15 12:14:55'),(15773,23,3763,49579,2325,224,'Hall','friday','ks2','',50,'','',101,'','unit1',NULL,'default','','25',NULL,NULL,NULL,'08:00:00','08:45:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 12:16:18','2016-04-15 12:16:18'),(15774,23,3763,49580,2325,224,'Hall','thursday','ks2','',50,'','',101,'','unit1',NULL,'default','','25',NULL,NULL,NULL,'08:00:00','08:45:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 12:16:42','2016-04-15 12:16:42'),(15775,23,3763,49580,2325,224,'Hall','thursday','ks2','',51,'','',101,'','unit1',NULL,'default','','25',NULL,NULL,NULL,'15:00:00','16:00:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 12:16:42','2016-04-15 12:16:42'),(15776,23,3763,49580,2325,224,'Hall','friday','ks2','',50,'','',101,'','unit1',NULL,'default','','25',NULL,NULL,NULL,'08:00:00','08:45:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 12:16:42','2016-04-15 12:16:42'),(15777,23,3763,49581,2325,224,'Hall','thursday','ks2','',50,'','',101,'','unit1',NULL,'default','','25',NULL,NULL,NULL,'08:00:00','08:45:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 12:16:45','2016-04-15 12:16:45'),(15778,23,3763,49581,2325,224,'Hall','thursday','ks2','',51,'','',101,'','unit1',NULL,'default','','25',NULL,NULL,NULL,'15:00:00','16:00:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 12:16:45','2016-04-15 12:16:45'),(15779,23,3763,49581,2325,224,'Hall','friday','ks2','',50,'','',101,'','unit1',NULL,'default','','25',NULL,NULL,NULL,'08:00:00','08:45:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-15 12:16:45','2016-04-15 12:16:45'),(15780,23,3765,49585,NULL,224,'Hall','monday','other','5-12 Years',56,'','',104,'','unit1',NULL,'default',NULL,'Approx 20',4.00,NULL,NULL,'08:00:00','10:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,20,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-18 09:43:49','2016-04-18 09:44:02'),(15781,23,3765,49585,NULL,224,'Hall','monday','other','5-12 Years',56,'','',103,'','unit1',NULL,'default',NULL,'Approx 30',10.00,NULL,NULL,'10:30:00','15:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,30,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-18 09:45:38','2016-04-18 09:45:49'),(15782,23,3765,49585,NULL,224,'Hall','tuesday','other','5-12 Years',56,'','',104,'','unit1',NULL,'default',NULL,'Approx 20',4.00,NULL,NULL,'08:00:00','10:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,20,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-18 09:45:59','2016-04-18 09:46:07'),(15783,23,3765,49585,NULL,224,'Hall','tuesday','other','5-12 Years',56,'','',103,'','unit1',NULL,'default',NULL,'Approx 30',10.00,NULL,NULL,'10:30:00','15:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,30,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-18 09:45:59','2016-04-18 09:46:13'),(15784,23,3765,49585,NULL,224,'Hall','wednesday','other','5-12 Years',56,'','',104,'','unit1',NULL,'default',NULL,'Approx 20',4.00,NULL,NULL,'08:00:00','10:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,20,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-18 09:46:24','2016-04-18 09:46:31'),(15785,23,3765,49585,NULL,224,'Hall','wednesday','other','5-12 Years',56,'','',103,'','unit1',NULL,'default',NULL,'Approx 30',10.00,NULL,NULL,'10:30:00','15:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,30,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-18 09:46:24','2016-04-18 09:46:38'),(15786,23,3765,49585,NULL,224,'Hall','thursday','other','5-12 Years',56,'','',104,'','unit1',NULL,'default',NULL,'Approx 20',4.00,NULL,NULL,'08:00:00','10:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,20,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-18 09:46:25','2016-04-18 09:46:46'),(15787,23,3765,49585,NULL,224,'Hall','thursday','other','5-12 Years',56,'','',103,'','unit1',NULL,'default',NULL,'Approx 30',10.00,NULL,NULL,'10:30:00','15:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,30,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-18 09:46:25','2016-04-18 09:46:56'),(15788,23,3765,49585,NULL,224,'Hall','friday','other','5-12 Years',56,'','',104,'','unit1',NULL,'default',NULL,'Approx 20',4.00,NULL,NULL,'08:00:00','10:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,20,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-18 09:47:07','2016-04-18 09:47:16'),(15789,23,3765,49585,NULL,224,'Hall','friday','other','5-12 Years',56,'','',103,'','unit1',NULL,'default',NULL,'Approx 30',10.00,NULL,NULL,'10:30:00','15:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,30,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-18 09:47:07','2016-04-18 09:47:23'),(15790,23,3765,49586,NULL,224,'Hall','monday','other','5-12 Years',56,'','',104,'','unit1',NULL,'default',NULL,'Approx 20',4.00,NULL,NULL,'08:00:00','10:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,20,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-18 09:47:53','2016-04-18 09:47:53'),(15791,23,3765,49586,NULL,224,'Hall','monday','other','5-12 Years',56,'','',103,'','unit1',NULL,'default',NULL,'Approx 30',10.00,NULL,NULL,'10:30:00','15:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,30,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-18 09:47:53','2016-04-18 09:47:53'),(15792,23,3765,49586,NULL,224,'Hall','tuesday','other','5-12 Years',56,'','',104,'','unit1',NULL,'default',NULL,'Approx 20',4.00,NULL,NULL,'08:00:00','10:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,20,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-18 09:47:53','2016-04-18 09:47:53'),(15793,23,3765,49586,NULL,224,'Hall','tuesday','other','5-12 Years',56,'','',103,'','unit1',NULL,'default',NULL,'Approx 30',10.00,NULL,NULL,'10:30:00','15:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,30,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-18 09:47:53','2016-04-18 09:47:53'),(15794,23,3765,49586,NULL,224,'Hall','wednesday','other','5-12 Years',56,'','',104,'','unit1',NULL,'default',NULL,'Approx 20',4.00,NULL,NULL,'08:00:00','10:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,20,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-18 09:47:53','2016-04-18 09:47:53'),(15795,23,3765,49586,NULL,224,'Hall','wednesday','other','5-12 Years',56,'','',103,'','unit1',NULL,'default',NULL,'Approx 30',10.00,NULL,NULL,'10:30:00','15:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,30,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-18 09:47:53','2016-04-18 09:47:53'),(15796,23,3765,49586,NULL,224,'Hall','thursday','other','5-12 Years',56,'','',104,'','unit1',NULL,'default',NULL,'Approx 20',4.00,NULL,NULL,'08:00:00','10:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,20,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-18 09:47:53','2016-04-18 09:47:53'),(15797,23,3765,49586,NULL,224,'Hall','thursday','other','5-12 Years',56,'','',103,'','unit1',NULL,'default',NULL,'Approx 30',10.00,NULL,NULL,'10:30:00','15:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,30,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-18 09:47:53','2016-04-18 09:47:53'),(15798,23,3765,49586,NULL,224,'Hall','friday','other','5-12 Years',56,'','',104,'','unit1',NULL,'default',NULL,'Approx 20',4.00,NULL,NULL,'08:00:00','10:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,20,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-18 09:47:53','2016-04-18 09:47:53'),(15799,23,3765,49586,NULL,224,'Hall','friday','other','5-12 Years',56,'','',103,'','unit1',NULL,'default',NULL,'Approx 30',10.00,NULL,NULL,'10:30:00','15:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,30,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-18 09:47:54','2016-04-18 09:47:54'),(15800,23,3765,49587,NULL,224,'Hall','monday','other','5-12 Years',56,'','',104,'','unit1',NULL,'default',NULL,'Approx 20',4.00,NULL,NULL,'08:00:00','10:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,20,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-18 09:53:54','2016-04-18 09:53:54'),(15801,23,3765,49587,NULL,224,'Hall','monday','other','5-12 Years',56,'','',103,'','unit1',NULL,'default',NULL,'Approx 30',10.00,NULL,NULL,'10:30:00','15:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,30,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-18 09:53:54','2016-04-18 09:53:54'),(15802,23,3765,49587,NULL,224,'Hall','tuesday','other','5-12 Years',56,'','',104,'','unit1',NULL,'default',NULL,'Approx 20',4.00,NULL,NULL,'08:00:00','10:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,20,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-18 09:53:54','2016-04-18 09:53:54'),(15803,23,3765,49587,NULL,224,'Hall','tuesday','other','5-12 Years',56,'','',103,'','unit1',NULL,'default',NULL,'Approx 30',10.00,NULL,NULL,'10:30:00','15:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,30,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-18 09:53:54','2016-04-18 09:53:54'),(15804,23,3765,49587,NULL,224,'Hall','wednesday','other','5-12 Years',56,'','',104,'','unit1',NULL,'default',NULL,'Approx 20',4.00,NULL,NULL,'08:00:00','10:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,20,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-18 09:53:54','2016-04-18 09:53:54'),(15805,23,3765,49587,NULL,224,'Hall','wednesday','other','5-12 Years',56,'','',103,'','unit1',NULL,'default',NULL,'Approx 30',10.00,NULL,NULL,'10:30:00','15:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,30,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-18 09:53:54','2016-04-18 09:53:54'),(15806,23,3765,49587,NULL,224,'Hall','thursday','other','5-12 Years',56,'','',104,'','unit1',NULL,'default',NULL,'Approx 20',4.00,NULL,NULL,'08:00:00','10:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,20,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-18 09:53:54','2016-04-18 09:53:54'),(15807,23,3765,49587,NULL,224,'Hall','thursday','other','5-12 Years',56,'','',103,'','unit1',NULL,'default',NULL,'Approx 30',10.00,NULL,NULL,'10:30:00','15:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,30,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-18 09:53:54','2016-04-18 09:53:54'),(15808,23,3765,49587,NULL,224,'Hall','friday','other','5-12 Years',56,'','',104,'','unit1',NULL,'default',NULL,'Approx 20',4.00,NULL,NULL,'08:00:00','10:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,20,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-18 09:53:54','2016-04-18 09:53:54'),(15809,23,3765,49587,NULL,224,'Hall','friday','other','5-12 Years',56,'','',103,'','unit1',NULL,'default',NULL,'Approx 30',10.00,NULL,NULL,'10:30:00','15:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,30,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-18 09:53:54','2016-04-18 09:53:54'),(15810,23,3766,49588,NULL,224,'School Hall','monday','other','5-12 Years',56,'','',104,'','unit1',NULL,'default',NULL,'Approx 20',4.00,NULL,NULL,'08:00:00','10:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,20,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-18 10:00:58','2016-04-18 10:01:08'),(15811,23,3766,49588,NULL,224,'School Hall','tuesday','other','5-12 Years',56,'','',103,'','unit1',NULL,'default',NULL,'Approx 30',10.00,NULL,NULL,'10:30:00','15:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,30,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-18 10:01:54','2016-04-18 10:02:38'),(15812,23,3766,49588,NULL,224,'School Hall','tuesday','other','5-12 Years',56,'','',104,'','unit1',NULL,'default',NULL,'Approx 20',4.00,NULL,NULL,'08:00:00','10:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,20,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-18 10:02:25','2016-04-18 10:02:32'),(15813,23,3766,49588,NULL,224,'School Hall','wednesday','other','5-12 Years',56,'','',103,'','unit1',NULL,'default',NULL,'Approx 30',10.00,NULL,NULL,'10:30:00','15:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,30,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-18 10:02:25','2016-04-18 10:03:01'),(15814,23,3766,49588,NULL,224,'School Hall','wednesday','other','5-12 Years',56,'','',104,'','unit1',NULL,'default',NULL,'Approx 20',4.00,NULL,NULL,'08:00:00','10:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,20,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-18 10:02:46','2016-04-18 10:02:53'),(15815,23,3766,49588,NULL,224,'School Hall','monday','other','5-12 Years',56,'','',103,'','unit1',NULL,'default',NULL,'Approx 30',10.00,NULL,NULL,'10:30:00','15:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,30,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-18 10:02:46','2016-04-18 10:02:46'),(15816,23,3766,49588,NULL,224,'School Hall','friday','other','5-12 Years',56,'','',104,'','unit1',NULL,'default',NULL,'Approx 20',4.00,NULL,NULL,'08:00:00','10:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,20,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-18 10:02:46','2016-04-18 10:03:11'),(15817,23,3766,49588,NULL,224,'School Hall','friday','other','5-12 Years',56,'','',103,'','unit1',NULL,'default',NULL,'Approx 30',10.00,NULL,NULL,'10:30:00','15:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,30,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-18 10:02:46','2016-04-18 10:03:22'),(15818,23,3766,49589,NULL,224,'School Hall','monday','other','5-12 Years',56,'','',104,'','unit1',NULL,'default',NULL,'Approx 20',4.00,NULL,NULL,'08:00:00','10:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,20,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-18 10:03:50','2016-04-18 10:03:50'),(15819,23,3766,49589,NULL,224,'School Hall','tuesday','other','5-12 Years',56,'','',103,'','unit1',NULL,'default',NULL,'Approx 30',10.00,NULL,NULL,'10:30:00','15:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,30,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-18 10:03:50','2016-04-18 10:03:50'),(15820,23,3766,49589,NULL,224,'School Hall','tuesday','other','5-12 Years',56,'','',104,'','unit1',NULL,'default',NULL,'Approx 20',4.00,NULL,NULL,'08:00:00','10:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,20,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-18 10:03:50','2016-04-18 10:03:50'),(15821,23,3766,49589,NULL,224,'School Hall','wednesday','other','5-12 Years',56,'','',103,'','unit1',NULL,'default',NULL,'Approx 30',10.00,NULL,NULL,'10:30:00','15:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,30,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-18 10:03:50','2016-04-18 10:03:50'),(15822,23,3766,49589,NULL,224,'School Hall','wednesday','other','5-12 Years',56,'','',104,'','unit1',NULL,'default',NULL,'Approx 20',4.00,NULL,NULL,'08:00:00','10:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,20,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-18 10:03:50','2016-04-18 10:03:50'),(15823,23,3766,49589,NULL,224,'School Hall','monday','other','5-12 Years',56,'','',103,'','unit1',NULL,'default',NULL,'Approx 30',10.00,NULL,NULL,'10:30:00','15:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,30,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-18 10:03:50','2016-04-18 10:03:50'),(15824,23,3766,49589,NULL,224,'School Hall','friday','other','5-12 Years',56,'','',104,'','unit1',NULL,'default',NULL,'Approx 20',4.00,NULL,NULL,'08:00:00','10:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,20,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-18 10:03:50','2016-04-18 10:03:50'),(15825,23,3766,49589,NULL,224,'School Hall','friday','other','5-12 Years',56,'','',103,'','unit1',NULL,'default',NULL,'Approx 30',10.00,NULL,NULL,'10:30:00','15:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,30,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-18 10:03:50','2016-04-18 10:03:50'),(15826,23,3766,49590,NULL,224,'School Hall','monday','other','5-12 Years',56,'','',104,'','unit1',NULL,'default',NULL,'Approx 20',4.00,NULL,NULL,'08:00:00','10:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,20,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-18 10:05:43','2016-04-18 10:05:43'),(15827,23,3766,49590,NULL,224,'School Hall','tuesday','other','5-12 Years',56,'','',103,'','unit1',NULL,'default',NULL,'Approx 30',10.00,NULL,NULL,'10:30:00','15:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,30,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-18 10:05:43','2016-04-18 10:05:43'),(15828,23,3766,49590,NULL,224,'School Hall','tuesday','other','5-12 Years',56,'','',104,'','unit1',NULL,'default',NULL,'Approx 20',4.00,NULL,NULL,'08:00:00','10:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,20,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-18 10:05:43','2016-04-18 10:05:43'),(15829,23,3766,49590,NULL,224,'School Hall','wednesday','other','5-12 Years',56,'','',103,'','unit1',NULL,'default',NULL,'Approx 30',10.00,NULL,NULL,'10:30:00','15:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,30,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-18 10:05:43','2016-04-18 10:05:43'),(15830,23,3766,49590,NULL,224,'School Hall','wednesday','other','5-12 Years',56,'','',104,'','unit1',NULL,'default',NULL,'Approx 20',4.00,NULL,NULL,'08:00:00','10:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,20,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-18 10:05:43','2016-04-18 10:05:43'),(15831,23,3766,49590,NULL,224,'School Hall','monday','other','5-12 Years',56,'','',103,'','unit1',NULL,'default',NULL,'Approx 30',10.00,NULL,NULL,'10:30:00','15:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,30,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-18 10:05:43','2016-04-18 10:05:43'),(15832,23,3766,49590,NULL,224,'School Hall','friday','other','5-12 Years',56,'','',104,'','unit1',NULL,'default',NULL,'Approx 20',4.00,NULL,NULL,'08:00:00','10:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,20,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-18 10:05:43','2016-04-18 10:05:43'),(15833,23,3766,49590,NULL,224,'School Hall','friday','other','5-12 Years',56,'','',103,'','unit1',NULL,'default',NULL,'Approx 30',10.00,NULL,NULL,'10:30:00','15:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,30,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-18 10:05:43','2016-04-18 10:05:43'),(15834,23,3767,49591,2330,224,'Outside','monday','other','5-12 Years',NULL,'Summer Splash','',107,'','unit1',NULL,'default','','Approx 30',NULL,NULL,NULL,'08:00:00','15:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,30,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-18 12:43:45','2016-04-18 12:43:45'),(15835,23,3768,49592,2330,224,'Outside','tuesday','other','5-12 Years',NULL,'Party In the Park','',107,'','unit1',NULL,'default','','Approx 30',NULL,NULL,NULL,'09:00:00','15:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,30,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-18 12:49:27','2016-04-18 12:49:27'),(15836,23,3768,49592,2330,224,'Outside','wednesday','other','5-12 Years',NULL,'Party In The Park','',107,'','unit1',NULL,'default','','Approx 30',NULL,NULL,NULL,'09:00:00','15:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,30,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-18 12:50:47','2016-04-18 12:50:47'),(15837,23,3769,49593,2332,224,'Playground','monday','other','Year 5 14 Learners',55,'','Level 1 Bikeability',108,'','unit1',NULL,'default','','14',NULL,NULL,NULL,'09:00:00','12:00:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,3,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-20 13:35:05','2016-04-20 13:35:29'),(15838,23,3769,49593,2332,224,'Roads around School','monday','other','Year 5 14 Learners',55,'','Level 2 Bikeability',108,'','unit1',NULL,'default','','14',NULL,NULL,NULL,'13:00:00','15:00:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-20 13:36:04','2016-04-20 13:36:17'),(15839,23,3769,49593,2332,224,'Roads around School','tuesday','other','Year 5 14 Learners',55,'','Level 2 Bikeability',108,'','unit1',NULL,'default','','14',NULL,NULL,NULL,'09:00:00','12:00:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-20 13:37:51','2016-04-20 13:38:04'),(15840,23,3769,49593,2332,224,'Roads around School','tuesday','other','Year 5 14 Learners',55,'','Level 2 Bikeability',108,'','unit1',NULL,'default','','14',NULL,NULL,NULL,'13:00:00','15:00:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-20 13:38:40','2016-04-20 13:38:54'),(15841,23,3770,49595,2328,224,'','wednesday','other','Care Workers',NULL,'Annual FA Refresher Training','',110,'','unit1',NULL,'default','','',100.00,NULL,NULL,'09:30:00','13:00:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-20 13:47:48','2016-04-22 13:19:49'),(15842,23,3770,49595,2328,224,'','wednesday','other','Care Workers',NULL,'Annual FA Refresher Training','',110,'','unit1',NULL,'default','','',NULL,NULL,NULL,'13:30:00','16:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-20 13:48:22','2016-04-20 13:48:22'),(15843,23,3769,49594,2333,224,'Playground','monday','other','Year 5 - 13 Learners',55,'','Level 1 Bikeability',108,'','unit1',NULL,'default','','13',NULL,NULL,NULL,'09:00:00','12:00:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-20 13:49:11','2016-04-20 13:49:21'),(15844,23,3769,49594,2333,224,'Roads around School','monday','other','Year 5 - 13 Learners',55,'','Level 2 Bikeability',108,'','unit1',NULL,'default','','13',NULL,NULL,NULL,'13:00:00','15:00:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-20 13:49:58','2016-04-20 13:50:05'),(15845,23,3769,49594,2333,224,'Roads around School','tuesday','other','Year 5 - 13 Learners',55,'','Level 2 Bikeability',108,'','unit1',NULL,'default','','13',NULL,NULL,NULL,'09:00:00','12:00:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-20 13:51:03','2016-04-20 13:51:10'),(15846,23,3769,49594,2333,224,'Roads around School','tuesday','other','Year 5 - 13 Learners',55,'','Level 2 Bikeability',108,'','unit1',NULL,'default','','13',NULL,NULL,NULL,'13:00:00','15:00:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-20 13:51:41','2016-04-20 13:51:55'),(15847,23,3771,49596,2334,224,'Playground/Field','monday','ks1','5-12 Years',50,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'13:30:00','14:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-22 12:53:20','2016-04-22 12:53:20'),(15848,23,3771,49596,2334,224,'Playground/Field','tuesday','ks1','5-12 Years',50,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'13:30:00','14:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-22 12:53:35','2016-04-22 12:53:40'),(15849,23,3771,49596,2334,224,'Playground/Field','wednesday','ks1','5-12 Years',50,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'13:30:00','14:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-22 12:53:47','2016-04-22 12:53:52'),(15850,23,3771,49596,2334,224,'Playground/Field','thursday','ks1','5-12 Years',50,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'13:30:00','14:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-22 12:53:47','2016-04-22 12:53:57'),(15851,23,3771,49596,2334,224,'Playground/Field','friday','ks1','5-12 Years',50,'','',99,'','unit1',NULL,'default','','30',NULL,NULL,NULL,'13:30:00','14:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-22 12:54:05','2016-04-22 12:54:11'),(15852,23,3772,49597,NULL,224,'Training Room','thursday','other','Staff',NULL,'Staff Training','',106,'','unit1',NULL,'default',NULL,'N/A',NULL,NULL,NULL,'10:00:00','11:00:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-22 14:46:10','2016-04-22 14:46:10'),(15853,23,3772,49597,NULL,224,'Training Room','friday','other','Staff',NULL,'Staff Training','',106,'','unit1',NULL,'default',NULL,'N/A',NULL,NULL,NULL,'10:00:00','11:00:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-04-22 14:46:43','2016-04-22 14:46:43'),(15854,23,3774,49598,2325,235,'Hull','friday','yr5+6','',55,NULL,'',108,NULL,'unit1',NULL,'default','','25',NULL,NULL,NULL,'09:00:00','12:00:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-05-17 14:24:45','2016-05-17 14:24:45'),(15855,23,3774,49598,2325,235,'Hull','thursday','yr5+6','',55,NULL,'',108,NULL,'unit1',NULL,'default','','25',NULL,NULL,NULL,'13:00:00','15:00:00',0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,NULL,NULL,0,0,'off',NULL,NULL,'2016-05-17 14:26:10','2016-05-17 14:26:10'),(45201,23,3765,55727,NULL,208,'Hall','monday','other','5-12 Years',56,'','',104,'','unit1',NULL,'default',NULL,'Approx 20',4.00,NULL,NULL,'08:00:00','10:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,20,1,NULL,NULL,0,0,'off',NULL,NULL,'2017-11-10 09:25:39','2017-11-10 09:25:39'),(45202,23,3765,55727,NULL,208,'Hall','monday','other','5-12 Years',56,'','',103,'','unit1',NULL,'default',NULL,'Approx 30',10.00,NULL,NULL,'10:30:00','15:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,30,1,NULL,NULL,0,0,'off',NULL,NULL,'2017-11-10 09:25:39','2017-11-10 09:25:39'),(45203,23,3765,55727,NULL,208,'Hall','tuesday','other','5-12 Years',56,'','',104,'','unit1',NULL,'default',NULL,'Approx 20',4.00,NULL,NULL,'08:00:00','10:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,20,1,NULL,NULL,0,0,'off',NULL,NULL,'2017-11-10 09:25:39','2017-11-10 09:25:39'),(45204,23,3765,55727,NULL,208,'Hall','tuesday','other','5-12 Years',56,'','',103,'','unit1',NULL,'default',NULL,'Approx 30',10.00,NULL,NULL,'10:30:00','15:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,30,1,NULL,NULL,0,0,'off',NULL,NULL,'2017-11-10 09:25:39','2017-11-10 09:25:39'),(45205,23,3765,55727,NULL,208,'Hall','wednesday','other','5-12 Years',56,'','',104,'','unit1',NULL,'default',NULL,'Approx 20',4.00,NULL,NULL,'08:00:00','10:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,20,1,NULL,NULL,0,0,'off',NULL,NULL,'2017-11-10 09:25:39','2017-11-10 09:25:39'),(45206,23,3765,55727,NULL,208,'Hall','wednesday','other','5-12 Years',56,'','',103,'','unit1',NULL,'default',NULL,'Approx 30',10.00,NULL,NULL,'10:30:00','15:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,30,1,NULL,NULL,0,0,'off',NULL,NULL,'2017-11-10 09:25:39','2017-11-10 09:25:39'),(45207,23,3765,55727,NULL,208,'Hall','thursday','other','5-12 Years',56,'','',104,'','unit1',NULL,'default',NULL,'Approx 20',4.00,NULL,NULL,'08:00:00','10:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,20,1,NULL,NULL,0,0,'off',NULL,NULL,'2017-11-10 09:25:39','2017-11-10 09:25:39'),(45208,23,3765,55727,NULL,208,'Hall','thursday','other','5-12 Years',56,'','',103,'','unit1',NULL,'default',NULL,'Approx 30',10.00,NULL,NULL,'10:30:00','15:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,30,1,NULL,NULL,0,0,'off',NULL,NULL,'2017-11-10 09:25:39','2017-11-10 09:25:39'),(45209,23,3765,55727,NULL,208,'Hall','friday','other','5-12 Years',56,'','',104,'','unit1',NULL,'default',NULL,'Approx 20',4.00,NULL,NULL,'08:00:00','10:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,20,1,NULL,NULL,0,0,'off',NULL,NULL,'2017-11-10 09:25:39','2017-11-10 09:25:39'),(45210,23,3765,55727,NULL,208,'Hall','friday','other','5-12 Years',56,'','',103,'','unit1',NULL,'default',NULL,'Approx 30',10.00,NULL,NULL,'10:30:00','15:30:00',0,0,0,0,0,0,0,0,0,0,0,0,0,30,1,NULL,NULL,0,0,'off',NULL,NULL,'2017-11-10 09:25:39','2017-11-10 09:25:39');
/*!40000 ALTER TABLE `app_bookings_lessons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_bookings_lessons_attachments`
--

DROP TABLE IF EXISTS `app_bookings_lessons_attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_bookings_lessons_attachments` (
  `attachmentID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `bookingID` int(11) DEFAULT NULL,
  `lessonID` int(11) NOT NULL,
  `byID` int(11) DEFAULT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `path` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `ext` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `size` bigint(100) NOT NULL,
  `comment` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `added` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`attachmentID`),
  KEY `fk_bookings_lessons_attachments_bookingID` (`bookingID`),
  KEY `fk_bookings_lessons_attachments_byID` (`byID`),
  KEY `fk_bookings_lessons_attachments_lessonID` (`lessonID`),
  KEY `fk_bookings_lessons_attachments_accountID` (`accountID`),
  CONSTRAINT `app_bookings_lessons_attachments_ibfk_4` FOREIGN KEY (`bookingID`) REFERENCES `app_bookings` (`bookingID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_lessons_attachments_ibfk_5` FOREIGN KEY (`lessonID`) REFERENCES `app_bookings_lessons` (`lessonID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_lessons_attachments_ibfk_6` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_lessons_attachments_ibfk_7` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=206 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_bookings_lessons_attachments`
--

LOCK TABLES `app_bookings_lessons_attachments` WRITE;
/*!40000 ALTER TABLE `app_bookings_lessons_attachments` DISABLE KEYS */;
/*!40000 ALTER TABLE `app_bookings_lessons_attachments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_bookings_lessons_exceptions`
--

DROP TABLE IF EXISTS `app_bookings_lessons_exceptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_bookings_lessons_exceptions` (
  `exceptionID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `bookingID` int(11) DEFAULT NULL,
  `lessonID` int(11) DEFAULT NULL,
  `byID` int(11) DEFAULT NULL,
  `fromID` int(11) DEFAULT NULL,
  `staffID` int(11) DEFAULT NULL,
  `type` enum('cancellation','staffchange') COLLATE utf8_unicode_ci NOT NULL,
  `date` date DEFAULT NULL,
  `reason_select` enum('authorised absence','unauthorised absence','sick','timetable conflict','other') COLLATE utf8_unicode_ci DEFAULT NULL,
  `reason` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `assign_to` enum('staff','company','customer','') COLLATE utf8_unicode_ci DEFAULT NULL,
  `added` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`exceptionID`),
  KEY `fk_bookings_lessons_exceptions_bookingID` (`bookingID`),
  KEY `fk_bookings_lessons_exceptions_byID` (`byID`),
  KEY `fk_bookings_lessons_exceptions_lessonID` (`lessonID`),
  KEY `fk_bookings_lessons_exceptions_fromID` (`fromID`),
  KEY `fk_bookings_lessons_exceptions_staffID` (`staffID`),
  KEY `fk_bookings_lessons_exceptions_accountID` (`accountID`),
  CONSTRAINT `app_bookings_lessons_exceptions_ibfk_2` FOREIGN KEY (`bookingID`) REFERENCES `app_bookings` (`bookingID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_lessons_exceptions_ibfk_3` FOREIGN KEY (`lessonID`) REFERENCES `app_bookings_lessons` (`lessonID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_lessons_exceptions_ibfk_4` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_lessons_exceptions_ibfk_5` FOREIGN KEY (`fromID`) REFERENCES `app_staff` (`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_lessons_exceptions_ibfk_6` FOREIGN KEY (`staffID`) REFERENCES `app_staff` (`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_lessons_exceptions_ibfk_7` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14933 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_bookings_lessons_exceptions`
--

LOCK TABLES `app_bookings_lessons_exceptions` WRITE;
/*!40000 ALTER TABLE `app_bookings_lessons_exceptions` DISABLE KEYS */;
INSERT INTO `app_bookings_lessons_exceptions` VALUES (11021,23,3767,15834,224,NULL,NULL,'cancellation','2016-07-04','other','Bank Holiday','customer','2016-04-18 12:45:37','2016-06-21 16:46:49'),(11022,23,3768,15836,224,NULL,NULL,'cancellation','2016-07-06','other','Park not avilable','customer','2016-04-18 12:51:33','2016-04-18 12:51:33'),(11023,23,3758,15696,224,NULL,NULL,'cancellation','2015-09-15','other','No Sessions','customer','2016-04-22 12:36:48','2016-04-22 12:36:48'),(11024,23,3758,15697,224,NULL,NULL,'cancellation','2015-09-15','other','No Sessions','customer','2016-04-22 12:36:48','2016-04-22 12:36:48'),(11025,23,3758,15698,224,NULL,NULL,'cancellation','2015-09-16','other','No Sessions','customer','2016-04-22 12:36:48','2016-04-22 12:36:48'),(11026,23,3758,15699,224,NULL,NULL,'cancellation','2015-09-16','other','No Sessions','customer','2016-04-22 12:36:48','2016-04-22 12:36:48'),(11027,23,3758,15700,224,NULL,NULL,'cancellation','2015-09-17','other','No Sessions','customer','2016-04-22 12:36:48','2016-04-22 12:36:48'),(11028,23,3758,15701,224,NULL,NULL,'cancellation','2015-09-17','other','No Sessions','customer','2016-04-22 12:36:48','2016-04-22 12:36:48');
/*!40000 ALTER TABLE `app_bookings_lessons_exceptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_bookings_lessons_notes`
--

DROP TABLE IF EXISTS `app_bookings_lessons_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_bookings_lessons_notes` (
  `noteID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `bookingID` int(11) DEFAULT NULL,
  `lessonID` int(11) NOT NULL,
  `byID` int(11) DEFAULT NULL,
  `type` enum('note','evaluation') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'note',
  `summary` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `content` text COLLATE utf8_unicode_ci,
  `date` date DEFAULT NULL,
  `status` enum('unsubmitted','submitted','approved','rejected') COLLATE utf8_unicode_ci DEFAULT NULL,
  `approverID` int(11) DEFAULT NULL,
  `rejection_reason` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `approved` datetime DEFAULT NULL,
  `rejected` datetime DEFAULT NULL,
  `added` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`noteID`),
  KEY `fk_bookings_lessons_notes_bookingID` (`bookingID`),
  KEY `fk_bookings_lessons_notes_byID` (`byID`),
  KEY `fk_bookings_lessons_notes_lessonID` (`lessonID`),
  KEY `fk_bookings_lessons_notes_accountID` (`accountID`),
  KEY `approverID` (`approverID`),
  CONSTRAINT `app_bookings_lessons_notes_ibfk_4` FOREIGN KEY (`bookingID`) REFERENCES `app_bookings` (`bookingID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_lessons_notes_ibfk_5` FOREIGN KEY (`lessonID`) REFERENCES `app_bookings_lessons` (`lessonID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_lessons_notes_ibfk_6` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_lessons_notes_ibfk_7` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_bookings_lessons_notes_approverID` FOREIGN KEY (`approverID`) REFERENCES `app_staff` (`staffID`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_bookings_lessons_notes`
--

LOCK TABLES `app_bookings_lessons_notes` WRITE;
/*!40000 ALTER TABLE `app_bookings_lessons_notes` DISABLE KEYS */;
/*!40000 ALTER TABLE `app_bookings_lessons_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_bookings_lessons_orgs_attachments`
--

DROP TABLE IF EXISTS `app_bookings_lessons_orgs_attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_bookings_lessons_orgs_attachments` (
  `actualID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `bookingID` int(11) NOT NULL,
  `lessonID` int(11) NOT NULL,
  `attachmentID` int(11) NOT NULL,
  `byID` int(11) DEFAULT NULL,
  `added` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`actualID`),
  KEY `fk_bookings_lessons_orgs_attachments_attachmentID` (`attachmentID`),
  KEY `fk_bookings_lessons_orgs_attachments_lessonID` (`lessonID`),
  KEY `fk_bookings_lessons_orgs_attachments_byID` (`byID`),
  KEY `fk_bookings_lessons_orgs_attachments_bookingID` (`bookingID`),
  KEY `fk_bookings_lessons_orgs_attachments_accountID` (`accountID`),
  CONSTRAINT `app_bookings_lessons_orgs_attachments_ibfk_1` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_lessons_orgs_attachments_ibfk_4` FOREIGN KEY (`bookingID`) REFERENCES `app_bookings` (`bookingID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_lessons_orgs_attachments_ibfk_5` FOREIGN KEY (`lessonID`) REFERENCES `app_bookings_lessons` (`lessonID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_lessons_orgs_attachments_ibfk_6` FOREIGN KEY (`attachmentID`) REFERENCES `app_orgs_attachments` (`attachmentID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_lessons_orgs_attachments_ibfk_7` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_bookings_lessons_orgs_attachments`
--

LOCK TABLES `app_bookings_lessons_orgs_attachments` WRITE;
/*!40000 ALTER TABLE `app_bookings_lessons_orgs_attachments` DISABLE KEYS */;
/*!40000 ALTER TABLE `app_bookings_lessons_orgs_attachments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_bookings_lessons_resources_attachments`
--

DROP TABLE IF EXISTS `app_bookings_lessons_resources_attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_bookings_lessons_resources_attachments` (
  `actualID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `bookingID` int(11) NOT NULL,
  `lessonID` int(11) NOT NULL,
  `attachmentID` int(11) NOT NULL,
  `byID` int(11) DEFAULT NULL,
  `added` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`actualID`),
  KEY `fk_bookings_lessons_resources_attachments_attachmentID` (`attachmentID`),
  KEY `fk_bookings_lessons_resources_attachments_lessonID` (`lessonID`),
  KEY `fk_bookings_lessons_resources_attachments_byID` (`byID`),
  KEY `fk_bookings_lessons_resources_attachments_bookingID` (`bookingID`),
  KEY `fk_bookings_lessons_resources_attachments_accountID` (`accountID`),
  CONSTRAINT `app_bookings_lessons_resources_attachments_ibfk_1` FOREIGN KEY (`bookingID`) REFERENCES `app_bookings` (`bookingID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_lessons_resources_attachments_ibfk_2` FOREIGN KEY (`lessonID`) REFERENCES `app_bookings_lessons` (`lessonID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_lessons_resources_attachments_ibfk_3` FOREIGN KEY (`attachmentID`) REFERENCES `app_files` (`attachmentID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_lessons_resources_attachments_ibfk_4` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_lessons_resources_attachments_ibfk_5` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=45437 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_bookings_lessons_resources_attachments`
--

LOCK TABLES `app_bookings_lessons_resources_attachments` WRITE;
/*!40000 ALTER TABLE `app_bookings_lessons_resources_attachments` DISABLE KEYS */;
INSERT INTO `app_bookings_lessons_resources_attachments` VALUES (42869,23,3758,15708,3229,224,'2016-04-22 14:42:11','2016-04-22 14:42:11'),(42870,23,3758,15709,3229,224,'2016-04-22 14:42:11','2016-04-22 14:42:11'),(42871,23,3758,15710,3229,224,'2016-04-22 14:42:11','2016-04-22 14:42:11'),(42872,23,3758,15711,3229,224,'2016-04-22 14:42:12','2016-04-22 14:42:12'),(42873,23,3758,15712,3229,224,'2016-04-22 14:42:12','2016-04-22 14:42:12'),(42874,23,3758,15713,3229,224,'2016-04-22 14:42:12','2016-04-22 14:42:12'),(42875,23,3758,15702,3229,224,'2016-04-22 14:42:30','2016-04-22 14:42:30'),(42876,23,3758,15703,3229,224,'2016-04-22 14:42:30','2016-04-22 14:42:30'),(42877,23,3758,15704,3229,224,'2016-04-22 14:42:30','2016-04-22 14:42:30'),(42878,23,3758,15705,3229,224,'2016-04-22 14:42:30','2016-04-22 14:42:30'),(42879,23,3758,15706,3229,224,'2016-04-22 14:42:30','2016-04-22 14:42:30'),(42880,23,3758,15707,3229,224,'2016-04-22 14:42:30','2016-04-22 14:42:30'),(42989,23,3761,15759,3228,235,'2016-05-26 16:14:15','2016-05-26 16:14:15'),(44424,23,3757,15666,3228,208,'2017-09-28 14:14:45','2017-09-28 14:14:45'),(44425,23,3757,15667,3228,208,'2017-09-28 14:14:45','2017-09-28 14:14:45'),(44426,23,3757,15668,3228,208,'2017-09-28 14:14:45','2017-09-28 14:14:45'),(44427,23,3757,15669,3228,208,'2017-09-28 14:14:45','2017-09-28 14:14:45'),(44428,23,3757,15670,3228,208,'2017-09-28 14:14:45','2017-09-28 14:14:45'),(44429,23,3757,15671,3228,208,'2017-09-28 14:14:45','2017-09-28 14:14:45'),(44430,23,3757,15672,3228,208,'2017-09-28 14:14:45','2017-09-28 14:14:45'),(44431,23,3757,15673,3228,208,'2017-09-28 14:14:45','2017-09-28 14:14:45'),(44432,23,3757,15674,3228,208,'2017-09-28 14:14:45','2017-09-28 14:14:45'),(44433,23,3757,15675,3228,208,'2017-09-28 14:14:45','2017-09-28 14:14:45');
/*!40000 ALTER TABLE `app_bookings_lessons_resources_attachments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_bookings_lessons_staff`
--

DROP TABLE IF EXISTS `app_bookings_lessons_staff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_bookings_lessons_staff` (
  `recordID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `bookingID` int(11) NOT NULL,
  `lessonID` int(11) DEFAULT NULL,
  `staffID` int(11) DEFAULT NULL,
  `type` enum('head','assistant','participant','observer','lead') COLLATE utf8_unicode_ci DEFAULT NULL,
  `byID` int(11) DEFAULT NULL,
  `startDate` date NOT NULL,
  `endDate` date NOT NULL,
  `startTime` time DEFAULT NULL,
  `endTime` time DEFAULT NULL,
  `comment` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `added` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`recordID`),
  KEY `fk_bookings_lessons_staff_lessonID` (`lessonID`),
  KEY `fk_bookings_lessons_staff_byID` (`byID`),
  KEY `fk_bookings_lessons_staff_staffID` (`staffID`),
  KEY `fk_bookings_lessons_staff_bookingID` (`bookingID`),
  KEY `fk_bookings_lessons_staff_accountID` (`accountID`),
  CONSTRAINT `app_bookings_lessons_staff_ibfk_4` FOREIGN KEY (`bookingID`) REFERENCES `app_bookings` (`bookingID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_lessons_staff_ibfk_5` FOREIGN KEY (`lessonID`) REFERENCES `app_bookings_lessons` (`lessonID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_lessons_staff_ibfk_6` FOREIGN KEY (`staffID`) REFERENCES `app_staff` (`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_lessons_staff_ibfk_7` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_lessons_staff_ibfk_8` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=85359 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_bookings_lessons_staff`
--

LOCK TABLES `app_bookings_lessons_staff` WRITE;
/*!40000 ALTER TABLE `app_bookings_lessons_staff` DISABLE KEYS */;
INSERT INTO `app_bookings_lessons_staff` VALUES (31587,23,3765,15780,225,'head',224,'2017-02-13','2017-02-19','08:00:00','10:30:00','','2016-04-22 12:39:55','2016-04-22 12:39:55'),(31588,23,3765,15781,225,'head',224,'2017-02-13','2017-02-19','10:30:00','15:30:00','','2016-04-22 12:39:55','2016-04-22 12:39:55'),(31589,23,3765,15782,225,'head',224,'2017-02-13','2017-02-19','08:00:00','10:30:00','','2016-04-22 12:39:55','2016-04-22 12:39:55'),(31590,23,3765,15783,225,'head',224,'2017-02-13','2017-02-19','10:30:00','15:30:00','','2016-04-22 12:39:55','2016-04-22 12:39:55'),(31591,23,3765,15784,225,'head',224,'2017-02-13','2017-02-19','08:00:00','10:30:00','','2016-04-22 12:39:55','2016-04-22 12:39:55'),(31592,23,3765,15785,225,'head',224,'2017-02-13','2017-02-19','10:30:00','15:30:00','','2016-04-22 12:39:55','2016-04-22 12:39:55'),(31593,23,3765,15786,225,'head',224,'2017-02-13','2017-02-19','08:00:00','10:30:00','','2016-04-22 12:39:55','2016-04-22 12:39:55'),(31594,23,3765,15787,225,'head',224,'2017-02-13','2017-02-19','10:30:00','15:30:00','','2016-04-22 12:39:55','2016-04-22 12:39:55'),(31595,23,3765,15788,225,'head',224,'2017-02-13','2017-02-19','08:00:00','10:30:00','','2016-04-22 12:39:55','2016-04-22 12:39:55'),(31596,23,3765,15789,225,'head',224,'2017-02-13','2017-02-19','10:30:00','15:30:00','','2016-04-22 12:39:55','2016-04-22 12:39:55'),(31597,23,3765,15790,230,'assistant',224,'2017-03-27','2017-04-02','08:00:00','10:30:00','','2016-04-22 12:41:07','2016-04-22 12:41:07'),(31598,23,3765,15791,230,'assistant',224,'2017-03-27','2017-04-02','10:30:00','15:30:00','','2016-04-22 12:41:07','2016-04-22 12:41:07'),(31599,23,3765,15792,230,'assistant',224,'2017-03-27','2017-04-02','08:00:00','10:30:00','','2016-04-22 12:41:07','2016-04-22 12:41:07'),(31600,23,3765,15793,230,'assistant',224,'2017-03-27','2017-04-02','10:30:00','15:30:00','','2016-04-22 12:41:07','2016-04-22 12:41:07'),(31601,23,3765,15794,230,'assistant',224,'2017-03-27','2017-04-02','08:00:00','10:30:00','','2016-04-22 12:41:07','2016-04-22 12:41:07'),(31602,23,3765,15795,230,'assistant',224,'2017-03-27','2017-04-02','10:30:00','15:30:00','','2016-04-22 12:41:07','2016-04-22 12:41:07'),(31603,23,3765,15796,230,'assistant',224,'2017-03-27','2017-04-02','08:00:00','10:30:00','','2016-04-22 12:41:07','2016-04-22 12:41:07'),(31604,23,3765,15797,230,'assistant',224,'2017-03-27','2017-04-02','10:30:00','15:30:00','','2016-04-22 12:41:07','2016-04-22 12:41:07'),(31605,23,3765,15798,230,'assistant',224,'2017-03-27','2017-04-02','08:00:00','10:30:00','','2016-04-22 12:41:07','2016-04-22 12:41:07'),(31606,23,3765,15799,230,'assistant',224,'2017-03-27','2017-04-02','10:30:00','15:30:00','','2016-04-22 12:41:07','2016-04-22 12:41:07'),(31607,23,3765,15800,233,'head',224,'2017-04-10','2017-04-16','08:00:00','10:30:00','','2016-04-22 12:41:32','2016-04-22 12:41:32'),(31608,23,3765,15801,233,'head',224,'2017-04-10','2017-04-16','10:30:00','15:30:00','','2016-04-22 12:41:32','2016-04-22 12:41:32'),(31609,23,3765,15802,233,'head',224,'2017-04-10','2017-04-16','08:00:00','10:30:00','','2016-04-22 12:41:32','2016-04-22 12:41:32'),(31610,23,3765,15803,233,'head',224,'2017-04-10','2017-04-16','10:30:00','15:30:00','','2016-04-22 12:41:32','2016-04-22 12:41:32'),(31611,23,3765,15804,233,'head',224,'2017-04-10','2017-04-16','08:00:00','10:30:00','','2016-04-22 12:41:32','2016-04-22 12:41:32'),(31612,23,3765,15805,233,'head',224,'2017-04-10','2017-04-16','10:30:00','15:30:00','','2016-04-22 12:41:32','2016-04-22 12:41:32'),(31613,23,3765,15806,233,'head',224,'2017-04-10','2017-04-16','08:00:00','10:30:00','','2016-04-22 12:41:32','2016-04-22 12:41:32'),(31614,23,3765,15807,233,'head',224,'2017-04-10','2017-04-16','10:30:00','15:30:00','','2016-04-22 12:41:32','2016-04-22 12:41:32'),(31615,23,3765,15808,233,'head',224,'2017-04-10','2017-04-16','08:00:00','10:30:00','','2016-04-22 12:41:32','2016-04-22 12:41:32'),(31616,23,3765,15809,233,'head',224,'2017-04-10','2017-04-16','10:30:00','15:30:00','','2016-04-22 12:41:32','2016-04-22 12:41:32'),(31627,23,3758,15696,225,'head',224,'2015-09-07','2015-12-27','13:00:00','14:00:00','','2016-04-22 12:44:07','2016-04-22 12:44:07'),(31628,23,3758,15697,225,'head',224,'2015-09-07','2015-12-27','14:15:00','15:15:00','','2016-04-22 12:44:07','2016-04-22 12:44:07'),(31629,23,3758,15698,225,'head',224,'2015-09-07','2015-12-27','13:00:00','14:00:00','','2016-04-22 12:44:07','2016-04-22 12:44:07'),(31630,23,3758,15699,225,'head',224,'2015-09-07','2015-12-27','14:15:00','15:15:00','','2016-04-22 12:44:07','2016-04-22 12:44:07'),(31631,23,3758,15700,225,'head',224,'2015-09-07','2015-12-27','13:00:00','14:00:00','','2016-04-22 12:44:07','2016-04-22 12:44:07'),(31632,23,3758,15701,225,'head',224,'2015-09-07','2015-12-27','14:15:00','15:15:00','','2016-04-22 12:44:07','2016-04-22 12:44:07'),(31633,23,3758,15702,225,'head',224,'2016-01-04','2016-03-27','13:00:00','14:00:00','','2016-04-22 12:44:25','2016-04-22 12:44:25'),(31634,23,3758,15703,225,'head',224,'2016-01-04','2016-03-27','14:15:00','15:15:00','','2016-04-22 12:44:25','2016-04-22 12:44:25'),(31635,23,3758,15704,225,'head',224,'2016-01-04','2016-03-27','13:00:00','14:00:00','','2016-04-22 12:44:25','2016-04-22 12:44:25'),(31636,23,3758,15705,225,'head',224,'2016-01-04','2016-03-27','14:15:00','15:15:00','','2016-04-22 12:44:25','2016-04-22 12:44:25'),(31637,23,3758,15706,225,'head',224,'2016-01-04','2016-03-27','13:00:00','14:00:00','','2016-04-22 12:44:25','2016-04-22 12:44:25'),(31638,23,3758,15707,225,'head',224,'2016-01-04','2016-03-27','14:15:00','15:15:00','','2016-04-22 12:44:25','2016-04-22 12:44:25'),(31639,23,3758,15708,225,'head',224,'2016-04-11','2016-07-31','13:00:00','14:00:00','','2016-04-22 12:45:00','2016-04-22 12:45:00'),(31640,23,3758,15709,225,'head',224,'2016-04-11','2016-07-31','14:15:00','15:15:00','','2016-04-22 12:45:00','2016-04-22 12:45:00'),(31641,23,3758,15710,225,'head',224,'2016-04-11','2016-07-31','13:00:00','14:00:00','','2016-04-22 12:45:00','2016-04-22 12:45:00'),(31642,23,3758,15711,225,'head',224,'2016-04-11','2016-07-31','14:15:00','15:15:00','','2016-04-22 12:45:00','2016-04-22 12:45:00'),(31643,23,3758,15712,225,'head',224,'2016-04-11','2016-07-31','13:00:00','14:00:00','','2016-04-22 12:45:00','2016-04-22 12:45:00'),(31644,23,3758,15713,225,'head',224,'2016-04-11','2016-07-31','14:15:00','15:15:00','','2016-04-22 12:45:00','2016-04-22 12:45:00'),(31645,23,3762,15765,230,'head',224,'2015-09-07','2015-12-27','15:15:00','16:15:00','','2016-04-22 12:45:44','2016-04-22 12:45:44'),(31646,23,3762,15766,230,'head',224,'2015-09-07','2015-12-27','15:15:00','16:15:00','','2016-04-22 12:45:44','2016-04-22 12:45:44'),(31647,23,3762,15767,230,'head',224,'2016-01-04','2016-03-27','15:15:00','16:15:00','','2016-04-22 12:46:19','2016-04-22 12:46:19'),(31648,23,3762,15768,230,'head',224,'2016-01-04','2016-03-27','15:15:00','16:15:00','','2016-04-22 12:46:19','2016-04-22 12:46:19'),(31649,23,3762,15769,230,'head',224,'2016-04-11','2016-07-31','15:15:00','16:15:00','','2016-04-22 12:46:37','2016-04-22 12:46:37'),(31650,23,3762,15770,230,'head',224,'2016-04-11','2016-07-31','15:15:00','16:15:00','','2016-04-22 12:46:37','2016-04-22 12:46:37'),(31651,23,3763,15771,231,'head',224,'2015-09-07','2016-07-24','08:00:00','08:45:00','','2016-04-22 12:47:30','2016-04-22 12:47:30'),(31652,23,3763,15772,228,'head',224,'2015-09-07','2016-07-24','15:00:00','16:00:00','','2016-04-22 12:47:30','2016-05-17 14:37:25'),(31653,23,3763,15773,227,'head',224,'2015-09-07','2016-07-24','08:00:00','08:45:00','','2016-04-22 12:47:30','2016-05-17 14:36:57'),(31654,23,3763,15774,229,'head',224,'2016-01-04','2016-03-27','08:00:00','08:45:00','','2016-04-22 12:49:43','2016-04-22 12:49:43'),(31655,23,3763,15775,229,'head',224,'2016-01-04','2016-03-27','15:00:00','16:00:00','','2016-04-22 12:49:43','2016-04-22 12:49:43'),(31656,23,3763,15776,229,'head',224,'2016-01-04','2016-03-27','08:00:00','08:45:00','','2016-04-22 12:49:43','2016-04-22 12:49:43'),(31657,23,3763,15777,229,'head',224,'2016-04-11','2016-07-24','08:00:00','08:45:00','','2016-04-22 12:50:33','2016-04-22 12:50:33'),(31658,23,3763,15778,229,'head',224,'2016-04-11','2016-07-24','15:00:00','16:00:00','','2016-04-22 12:50:33','2016-04-22 12:50:33'),(31659,23,3763,15779,229,'head',224,'2016-04-11','2016-07-24','08:00:00','08:45:00','','2016-04-22 12:50:33','2016-04-22 12:50:33'),(31660,23,3769,15837,231,'head',224,'2016-04-25','2016-05-01','09:00:00','12:00:00','','2016-04-22 12:52:32','2016-04-22 12:52:32'),(31661,23,3769,15838,231,'head',224,'2016-04-25','2016-05-01','13:00:00','15:00:00','','2016-04-22 12:52:32','2016-04-22 12:52:32'),(31662,23,3769,15839,231,'head',224,'2016-04-25','2016-05-01','09:00:00','12:00:00','','2016-04-22 12:52:32','2016-04-22 12:52:32'),(31663,23,3769,15840,231,'head',224,'2016-04-25','2016-05-01','13:00:00','15:00:00','','2016-04-22 12:52:32','2016-04-22 12:52:32'),(31664,23,3769,15843,229,'head',224,'2016-06-06','2016-06-12','09:00:00','12:00:00','','2016-04-22 12:53:22','2016-04-22 12:53:22'),(31665,23,3769,15844,229,'head',224,'2016-06-06','2016-06-12','13:00:00','15:00:00','','2016-04-22 12:53:22','2016-04-22 12:53:22'),(31667,23,3769,15846,229,'head',224,'2016-06-06','2016-06-12','13:00:00','15:00:00','','2016-04-22 12:53:22','2016-04-22 12:53:22'),(31668,23,3767,15834,225,'head',224,'2016-06-06','2016-07-31','08:00:00','15:30:00','','2016-04-22 12:54:04','2016-04-22 12:54:04'),(31669,23,3768,15835,233,'head',224,'2016-07-04','2016-07-10','09:00:00','15:30:00','','2016-04-22 12:54:40','2016-04-22 12:54:40'),(31670,23,3768,15836,233,'head',224,'2016-07-04','2016-07-10','09:00:00','15:30:00','','2016-04-22 12:54:40','2016-04-22 12:54:40'),(31671,23,3766,15810,229,'head',224,'2017-02-13','2017-02-17','08:00:00','10:30:00','','2016-04-22 12:57:31','2016-04-22 12:57:31'),(31672,23,3766,15815,229,'head',224,'2017-02-13','2017-02-17','10:30:00','15:30:00','','2016-04-22 12:57:31','2016-04-22 12:57:31'),(31673,23,3766,15812,229,'head',224,'2017-02-13','2017-02-17','08:00:00','10:30:00','','2016-04-22 12:57:31','2016-04-22 12:57:31'),(31674,23,3766,15811,229,'head',224,'2017-02-13','2017-02-17','10:30:00','15:30:00','','2016-04-22 12:57:31','2016-04-22 12:57:31'),(31675,23,3766,15814,229,'head',224,'2017-02-13','2017-02-17','08:00:00','10:30:00','','2016-04-22 12:57:31','2016-04-22 12:57:31'),(31676,23,3766,15813,229,'head',224,'2017-02-13','2017-02-17','10:30:00','15:30:00','','2016-04-22 12:57:31','2016-04-22 12:57:31'),(31677,23,3766,15816,229,'head',224,'2017-02-13','2017-02-17','08:00:00','10:30:00','','2016-04-22 12:57:31','2016-04-22 12:57:31'),(31678,23,3766,15817,229,'head',224,'2017-02-13','2017-02-17','10:30:00','15:30:00','','2016-04-22 12:57:31','2016-04-22 12:57:31'),(31679,23,3766,15818,229,'head',224,'2017-04-10','2017-04-14','08:00:00','10:30:00','','2016-04-22 12:57:55','2016-04-22 12:57:55'),(31680,23,3766,15823,229,'head',224,'2017-04-10','2017-04-14','10:30:00','15:30:00','','2016-04-22 12:57:55','2016-04-22 12:57:55'),(31681,23,3766,15820,229,'head',224,'2017-04-10','2017-04-14','08:00:00','10:30:00','','2016-04-22 12:57:55','2016-04-22 12:57:55'),(31682,23,3766,15819,229,'head',224,'2017-04-10','2017-04-14','10:30:00','15:30:00','','2016-04-22 12:57:55','2016-04-22 12:57:55'),(31683,23,3766,15822,229,'head',224,'2017-04-10','2017-04-14','08:00:00','10:30:00','','2016-04-22 12:57:55','2016-04-22 12:57:55'),(31684,23,3766,15821,229,'head',224,'2017-04-10','2017-04-14','10:30:00','15:30:00','','2016-04-22 12:57:55','2016-04-22 12:57:55'),(31685,23,3766,15824,229,'head',224,'2017-04-10','2017-04-14','08:00:00','10:30:00','','2016-04-22 12:57:55','2016-04-22 12:57:55'),(31686,23,3766,15825,229,'head',224,'2017-04-10','2017-04-14','10:30:00','15:30:00','','2016-04-22 12:57:55','2016-04-22 12:57:55'),(31687,23,3766,15826,229,'head',224,'2017-04-17','2017-04-21','08:00:00','10:30:00','','2016-04-22 12:58:13','2016-04-22 12:58:13'),(31688,23,3766,15831,229,'head',224,'2017-04-17','2017-04-21','10:30:00','15:30:00','','2016-04-22 12:58:13','2016-04-22 12:58:13'),(31689,23,3766,15828,229,'head',224,'2017-04-17','2017-04-21','08:00:00','10:30:00','','2016-04-22 12:58:13','2016-04-22 12:58:13'),(31690,23,3766,15827,229,'head',224,'2017-04-17','2017-04-21','10:30:00','15:30:00','','2016-04-22 12:58:13','2016-04-22 12:58:13'),(31691,23,3766,15830,229,'head',224,'2017-04-17','2017-04-21','08:00:00','10:30:00','','2016-04-22 12:58:13','2016-04-22 12:58:13'),(31692,23,3766,15829,229,'head',224,'2017-04-17','2017-04-21','10:30:00','15:30:00','','2016-04-22 12:58:13','2016-04-22 12:58:13'),(31693,23,3766,15832,229,'head',224,'2017-04-17','2017-04-21','08:00:00','10:30:00','','2016-04-22 12:58:13','2016-04-22 12:58:13'),(31694,23,3766,15833,229,'head',224,'2017-04-17','2017-04-21','10:30:00','15:30:00','','2016-04-22 12:58:13','2016-04-22 12:58:13'),(31695,23,3768,15835,230,'head',224,'2016-07-04','2016-07-10','09:00:00','15:30:00','','2016-04-22 13:01:13','2016-04-22 13:01:13'),(31696,23,3768,15836,230,'head',224,'2016-07-04','2016-07-10','09:00:00','15:30:00','','2016-04-22 13:01:13','2016-04-22 13:01:13'),(31697,23,3760,15721,232,'head',224,'2015-09-07','2015-12-20','13:15:00','14:15:00','','2016-04-22 13:02:18','2016-04-22 13:02:18'),(31698,23,3760,15722,232,'head',224,'2015-09-07','2015-12-20','14:15:00','15:20:00','','2016-04-22 13:02:18','2016-04-22 13:02:18'),(31699,23,3760,15724,232,'head',224,'2015-09-07','2015-12-20','13:15:00','14:15:00','','2016-04-22 13:02:18','2016-04-22 13:02:18'),(31700,23,3760,15725,232,'head',224,'2015-09-07','2015-12-20','14:15:00','15:20:00','','2016-04-22 13:02:18','2016-04-22 13:02:18'),(31701,23,3760,15727,232,'head',224,'2015-09-07','2015-12-20','13:15:00','14:15:00','','2016-04-22 13:02:18','2016-04-22 13:02:18'),(31702,23,3760,15728,232,'head',224,'2015-09-07','2015-12-20','14:15:00','15:20:00','','2016-04-22 13:02:18','2016-04-22 13:02:18'),(31703,23,3760,15730,232,'head',224,'2016-01-04','2016-12-25','13:15:00','14:15:00','','2016-04-22 13:02:46','2016-04-22 13:02:46'),(31704,23,3760,15731,232,'head',224,'2016-01-04','2016-12-25','14:15:00','15:20:00','','2016-04-22 13:02:46','2016-04-22 13:02:46'),(31705,23,3760,15733,232,'head',224,'2016-01-04','2016-12-25','13:15:00','14:15:00','','2016-04-22 13:02:46','2016-04-22 13:02:46'),(31706,23,3760,15734,232,'head',224,'2016-01-04','2016-12-25','14:15:00','15:20:00','','2016-04-22 13:02:46','2016-04-22 13:02:46'),(31707,23,3760,15736,232,'head',224,'2016-01-04','2016-12-25','13:15:00','14:15:00','','2016-04-22 13:02:46','2016-04-22 13:02:46'),(31708,23,3760,15737,232,'head',224,'2016-01-04','2016-12-25','14:15:00','15:20:00','','2016-04-22 13:02:46','2016-04-22 13:02:46'),(31709,23,3760,15739,227,'head',224,'2016-04-11','2016-07-24','13:15:00','14:15:00','','2016-04-22 13:03:35','2016-04-22 13:03:35'),(31710,23,3760,15740,227,'head',224,'2016-04-11','2016-07-24','14:15:00','15:20:00','','2016-04-22 13:03:36','2016-04-22 13:03:36'),(31711,23,3760,15742,227,'head',224,'2016-04-11','2016-07-24','13:15:00','14:15:00','','2016-04-22 13:03:36','2016-04-22 13:03:36'),(31712,23,3760,15743,227,'head',224,'2016-04-11','2016-07-24','14:15:00','15:20:00','','2016-04-22 13:03:36','2016-04-22 13:03:36'),(31713,23,3760,15745,227,'head',224,'2016-04-11','2016-07-24','13:15:00','14:15:00','','2016-04-22 13:03:36','2016-04-22 13:03:36'),(31714,23,3760,15746,227,'head',224,'2016-04-11','2016-07-24','14:15:00','15:20:00','','2016-04-22 13:03:36','2016-04-22 13:03:36'),(31715,23,3768,15835,226,'assistant',224,'2016-07-04','2016-07-10','09:00:00','15:30:00','','2016-04-22 13:11:07','2016-04-22 13:11:07'),(31716,23,3768,15836,226,'assistant',224,'2016-07-04','2016-07-10','09:00:00','15:30:00','','2016-04-22 13:11:07','2016-04-22 13:11:07'),(31717,23,3770,15841,232,'lead',224,'2016-06-08','2016-06-08','09:30:00','13:00:00','','2016-04-22 14:06:29','2016-04-22 14:06:29'),(31718,23,3770,15842,232,'lead',224,'2016-06-08','2016-06-08','13:30:00','16:30:00','','2016-04-22 14:06:29','2016-04-22 14:06:29'),(31719,23,3767,15834,231,'assistant',224,'2016-06-06','2016-07-31','08:00:00','15:30:00','','2016-04-22 14:07:25','2016-04-22 14:07:25'),(31720,23,3772,15852,225,'participant',224,'2016-06-09','2016-06-10','10:00:00','11:00:00','','2016-04-22 14:47:00','2016-04-22 14:47:00'),(31721,23,3772,15853,225,'participant',224,'2016-06-09','2016-06-10','10:00:00','11:00:00','','2016-04-22 14:47:00','2016-04-22 14:47:00'),(31722,23,3772,15852,231,'participant',224,'2016-06-09','2016-06-10','10:00:00','11:00:00','','2016-04-22 14:47:13','2016-04-22 14:47:13'),(31723,23,3772,15853,231,'participant',224,'2016-06-09','2016-06-10','10:00:00','11:00:00','','2016-04-22 14:47:13','2016-04-22 14:47:13'),(31724,23,3772,15852,226,'participant',224,'2016-06-09','2016-06-10','10:00:00','11:00:00','','2016-04-22 14:47:34','2016-04-22 14:47:34'),(31725,23,3772,15853,226,'participant',224,'2016-06-09','2016-06-10','10:00:00','11:00:00','','2016-04-22 14:47:34','2016-04-22 14:47:34'),(31726,23,3772,15852,227,'participant',224,'2016-06-09','2016-06-10','10:00:00','11:00:00','','2016-04-22 14:47:53','2016-04-22 14:47:53'),(31727,23,3772,15853,227,'participant',224,'2016-06-09','2016-06-10','10:00:00','11:00:00','','2016-04-22 14:47:53','2016-04-22 14:47:53'),(31728,23,3772,15852,228,'participant',224,'2016-06-09','2016-06-10','10:00:00','11:00:00','','2016-04-22 14:48:09','2016-04-22 14:48:09'),(31729,23,3772,15853,228,'participant',224,'2016-06-09','2016-06-10','10:00:00','11:00:00','','2016-04-22 14:48:09','2016-04-22 14:48:09'),(31730,23,3772,15852,230,'participant',224,'2016-06-09','2016-06-10','10:00:00','11:00:00','','2016-04-22 14:48:28','2016-04-22 14:48:28'),(31731,23,3772,15853,230,'participant',224,'2016-06-09','2016-06-10','10:00:00','11:00:00','','2016-04-22 14:48:28','2016-04-22 14:48:28'),(31732,23,3772,15852,232,'participant',224,'2016-06-09','2016-06-10','10:00:00','11:00:00','','2016-04-22 14:48:45','2016-04-22 14:48:45'),(31733,23,3772,15853,232,'participant',224,'2016-06-09','2016-06-10','10:00:00','11:00:00','','2016-04-22 14:48:45','2016-04-22 14:48:45'),(31734,23,3765,15790,225,'head',234,'2017-03-27','2017-04-02','08:00:00','10:30:00','','2016-04-26 12:29:02','2016-04-26 12:29:02'),(31735,23,3765,15791,225,'head',234,'2017-03-27','2017-04-02','10:30:00','15:30:00','','2016-04-26 12:29:02','2016-04-26 12:29:02'),(31736,23,3765,15792,225,'head',234,'2017-03-27','2017-04-02','08:00:00','10:30:00','','2016-04-26 12:29:02','2016-04-26 12:29:02'),(31737,23,3765,15793,225,'head',234,'2017-03-27','2017-04-02','10:30:00','15:30:00','','2016-04-26 12:29:02','2016-04-26 12:29:02'),(31738,23,3765,15794,225,'head',234,'2017-03-27','2017-04-02','08:00:00','10:30:00','','2016-04-26 12:29:02','2016-04-26 12:29:02'),(31739,23,3765,15795,225,'head',234,'2017-03-27','2017-04-02','10:30:00','15:30:00','','2016-04-26 12:29:02','2016-04-26 12:29:02'),(31740,23,3765,15796,225,'head',234,'2017-03-27','2017-04-02','08:00:00','10:30:00','','2016-04-26 12:29:02','2016-04-26 12:29:02'),(31741,23,3765,15797,225,'head',234,'2017-03-27','2017-04-02','10:30:00','15:30:00','','2016-04-26 12:29:02','2016-04-26 12:29:02'),(31742,23,3765,15798,225,'head',234,'2017-03-27','2017-04-02','08:00:00','10:30:00','','2016-04-26 12:29:02','2016-04-26 12:29:02'),(31743,23,3765,15799,225,'head',234,'2017-03-27','2017-04-02','10:30:00','15:30:00','','2016-04-26 12:29:02','2016-04-26 12:29:02'),(31744,23,3774,15855,226,'lead',235,'2016-05-17','2016-08-31','13:00:00','15:00:00','','2016-05-17 14:28:54','2016-05-17 14:28:54'),(31745,23,3774,15854,233,'lead',235,'2016-05-17','2016-08-31','09:00:00','12:00:00','','2016-05-17 14:29:29','2016-05-17 14:29:29'),(31746,23,3769,15843,227,'assistant',235,'2016-05-09','2016-06-12','09:00:00','12:00:00','','2016-05-17 14:31:42','2016-05-17 14:31:42'),(31747,23,3769,15844,232,'assistant',235,'2016-05-09','2016-06-12','13:00:00','15:00:00','','2016-05-17 14:32:34','2016-05-17 14:32:34'),(31748,23,3769,15846,231,'assistant',235,'2016-05-09','2016-06-12','13:00:00','15:00:00','','2016-05-17 14:33:46','2016-05-17 14:35:09'),(31749,23,3759,15718,233,'lead',235,'2016-04-11','2016-07-31','12:30:00','13:15:00','','2016-05-17 14:38:04','2016-05-17 14:38:04'),(31750,23,3771,15847,233,'lead',235,'2016-04-11','2016-07-31','13:30:00','14:30:00','','2016-05-17 14:38:33','2016-05-17 14:38:33'),(31751,23,3759,15719,228,'lead',235,'2016-04-11','2016-07-31','14:25:00','15:25:00','','2016-05-17 14:39:02','2016-05-17 14:39:02'),(31752,23,3761,15759,228,'head',235,'2016-04-11','2016-07-24','13:15:00','14:30:00','','2016-05-20 13:28:16','2016-05-20 13:28:16'),(31753,23,3771,15848,227,'head',235,'2016-04-11','2016-07-31','13:30:00','14:30:00','','2016-05-20 15:07:24','2016-05-20 15:07:24'),(31754,23,3771,15851,228,'head',235,'2016-04-11','2016-07-31','13:30:00','14:30:00','','2016-05-20 15:16:55','2016-05-20 15:16:55'),(31755,23,3769,15845,225,'head',235,'2016-05-09','2016-06-12','09:00:00','12:00:00','','2016-05-20 15:48:53','2016-05-20 15:48:53'),(31756,23,3761,15763,233,'head',235,'2016-04-11','2016-07-24','13:15:00','14:30:00','','2016-05-20 16:22:09','2016-05-20 16:22:09'),(31757,23,3761,15762,228,'head',235,'2016-04-11','2016-07-24','14:45:00','15:30:00','','2016-05-20 16:44:46','2016-05-20 16:44:46'),(35438,23,3758,15708,234,'observer',235,'2016-04-11','2016-07-31','13:00:00','14:00:00','','2016-06-06 10:18:55','2016-06-06 10:18:55'),(35439,23,3760,15736,233,'observer',235,'2016-01-04','2016-12-25','13:15:00','14:15:00','','2016-06-10 10:21:48','2016-06-10 10:21:48'),(38906,23,3757,15668,225,'lead',235,'2016-09-05','2016-12-18','13:15:00','14:20:00','','2016-11-11 15:26:31','2016-11-11 15:26:31'),(63031,23,3757,15666,225,'head',208,'2017-09-05','2017-12-24','13:15:00','14:20:00','','2017-09-28 14:14:22','2017-09-28 14:14:22'),(63032,23,3757,15667,225,'head',208,'2017-09-05','2017-12-24','14:20:00','15:30:00','','2017-09-28 14:14:22','2017-09-28 14:14:22'),(63033,23,3757,15668,225,'head',208,'2017-09-05','2017-12-24','13:15:00','14:20:00','','2017-09-28 14:14:22','2017-09-28 14:14:22'),(63034,23,3757,15669,225,'head',208,'2017-09-05','2017-12-24','14:20:00','15:30:00','','2017-09-28 14:14:22','2017-09-28 14:14:22'),(63035,23,3757,15670,225,'head',208,'2017-09-05','2017-12-24','13:15:00','14:20:00','','2017-09-28 14:14:22','2017-09-28 14:14:22'),(63036,23,3757,15671,225,'head',208,'2017-09-05','2017-12-24','14:20:00','15:30:00','','2017-09-28 14:14:22','2017-09-28 14:14:22'),(63037,23,3757,15672,225,'head',208,'2017-09-05','2017-12-24','13:15:00','14:20:00','','2017-09-28 14:14:22','2017-09-28 14:14:22'),(63038,23,3757,15673,225,'head',208,'2017-09-05','2017-12-24','14:20:00','15:30:00','','2017-09-28 14:14:22','2017-09-28 14:14:22'),(63039,23,3757,15674,225,'head',208,'2017-09-05','2017-12-24','09:50:00','10:55:00','','2017-09-28 14:14:22','2017-09-28 14:14:22'),(63040,23,3757,15675,225,'head',208,'2017-09-05','2017-12-24','11:10:00','12:15:00','','2017-09-28 14:14:22','2017-09-28 14:14:22');
/*!40000 ALTER TABLE `app_bookings_lessons_staff` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_bookings_lessons_vouchers`
--

DROP TABLE IF EXISTS `app_bookings_lessons_vouchers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_bookings_lessons_vouchers` (
  `linkID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `lessonID` int(11) NOT NULL,
  `voucherID` int(11) NOT NULL,
  `byID` int(11) DEFAULT NULL,
  `added` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`linkID`),
  KEY `fk_bookings_lessons_vouchers_byID` (`byID`),
  KEY `fk_bookings_lessons_vouchers_lessonID` (`lessonID`),
  KEY `fk_bookings_lessons_vouchers_voucherID` (`voucherID`),
  KEY `fk_bookings_lessons_vouchers_accountID` (`accountID`),
  CONSTRAINT `app_bookings_lessons_vouchers_ibfk_2` FOREIGN KEY (`lessonID`) REFERENCES `app_bookings_lessons` (`lessonID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_lessons_vouchers_ibfk_3` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_lessons_vouchers_ibfk_4` FOREIGN KEY (`voucherID`) REFERENCES `app_bookings_vouchers` (`voucherID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_lessons_vouchers_ibfk_5` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_bookings_lessons_vouchers`
--

LOCK TABLES `app_bookings_lessons_vouchers` WRITE;
/*!40000 ALTER TABLE `app_bookings_lessons_vouchers` DISABLE KEYS */;
/*!40000 ALTER TABLE `app_bookings_lessons_vouchers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_bookings_orgs_attachments`
--

DROP TABLE IF EXISTS `app_bookings_orgs_attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_bookings_orgs_attachments` (
  `actualID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `bookingID` int(11) NOT NULL,
  `attachmentID` int(11) NOT NULL,
  `byID` int(11) DEFAULT NULL,
  `added` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`actualID`),
  KEY `fk_bookings_orgs_attachments_attachmentID` (`attachmentID`),
  KEY `fk_bookings_orgs_attachments_bookingID` (`bookingID`),
  KEY `fk_bookings_orgs_attachments_byID` (`byID`),
  KEY `fk_bookings_orgs_attachments_accountID` (`accountID`),
  CONSTRAINT `app_bookings_orgs_attachments_ibfk_1` FOREIGN KEY (`bookingID`) REFERENCES `app_bookings` (`bookingID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_orgs_attachments_ibfk_2` FOREIGN KEY (`attachmentID`) REFERENCES `app_orgs_attachments` (`attachmentID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_orgs_attachments_ibfk_3` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE CASCADE ON UPDATE SET NULL,
  CONSTRAINT `app_bookings_orgs_attachments_ibfk_4` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_bookings_orgs_attachments`
--

LOCK TABLES `app_bookings_orgs_attachments` WRITE;
/*!40000 ALTER TABLE `app_bookings_orgs_attachments` DISABLE KEYS */;
/*!40000 ALTER TABLE `app_bookings_orgs_attachments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_bookings_pricing`
--

DROP TABLE IF EXISTS `app_bookings_pricing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_bookings_pricing` (
  `linkID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL,
  `bookingID` int(11) NOT NULL,
  `typeID` int(11) NOT NULL,
  `amount` decimal(8,2) NOT NULL,
  `contract` tinyint(1) NOT NULL DEFAULT '0',
  `added` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`linkID`),
  KEY `accountID` (`accountID`),
  KEY `bookingID` (`bookingID`),
  KEY `typeID` (`typeID`),
  CONSTRAINT `app_bookings_pricing_ibfk_1` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_pricing_ibfk_2` FOREIGN KEY (`bookingID`) REFERENCES `app_bookings` (`bookingID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_bookings_pricing_typeID` FOREIGN KEY (`typeID`) REFERENCES `app_lesson_types` (`typeID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2123 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_bookings_pricing`
--

LOCK TABLES `app_bookings_pricing` WRITE;
/*!40000 ALTER TABLE `app_bookings_pricing` DISABLE KEYS */;
INSERT INTO `app_bookings_pricing` VALUES (1,23,3757,99,60.00,0,'2016-04-28 10:01:28','2017-09-28 13:58:31'),(2,23,3757,101,60.00,0,'2016-04-28 10:01:28','2017-09-28 13:58:31'),(3,23,3758,99,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(4,23,3758,101,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(5,23,3759,99,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(6,23,3759,100,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(7,23,3759,101,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(8,23,3760,99,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(9,23,3760,101,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(10,23,3761,99,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(11,23,3761,100,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(12,23,3761,101,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(13,23,3762,99,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(14,23,3762,101,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(15,23,3763,99,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(16,23,3763,101,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(17,23,3769,108,50.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(18,23,3774,101,60.00,0,'2016-05-17 14:22:33','2016-05-17 14:22:33'),(19,23,3774,99,20.00,0,'2016-05-17 14:22:33','2016-05-17 14:22:33');
/*!40000 ALTER TABLE `app_bookings_pricing` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_bookings_tags`
--

DROP TABLE IF EXISTS `app_bookings_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_bookings_tags` (
  `linkID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL,
  `bookingID` int(11) NOT NULL,
  `tagID` int(11) NOT NULL,
  `added` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`linkID`),
  KEY `accountID` (`accountID`),
  KEY `tagID` (`tagID`),
  KEY `bookingID` (`bookingID`),
  CONSTRAINT `app_bookings_tags_ibfk_1` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_tags_ibfk_2` FOREIGN KEY (`bookingID`) REFERENCES `app_bookings` (`bookingID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_bookings_tags_tagID` FOREIGN KEY (`tagID`) REFERENCES `app_settings_tags` (`tagID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_bookings_tags`
--

LOCK TABLES `app_bookings_tags` WRITE;
/*!40000 ALTER TABLE `app_bookings_tags` DISABLE KEYS */;
/*!40000 ALTER TABLE `app_bookings_tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_bookings_vouchers`
--

DROP TABLE IF EXISTS `app_bookings_vouchers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_bookings_vouchers` (
  `voucherID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `bookingID` int(11) DEFAULT NULL,
  `byID` int(11) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `code` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `discount_type` enum('percentage','amount') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'percentage',
  `discount` decimal(6,2) NOT NULL,
  `comment` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `added` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`voucherID`),
  KEY `fk_bookings_vouchers_bookingID` (`bookingID`),
  KEY `fk_bookings_vouchers_byID` (`byID`),
  KEY `fk_bookings_vouchers_accountID` (`accountID`),
  CONSTRAINT `app_bookings_vouchers_ibfk_1` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_vouchers_ibfk_2` FOREIGN KEY (`bookingID`) REFERENCES `app_bookings` (`bookingID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_bookings_vouchers_ibfk_3` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_bookings_vouchers`
--

LOCK TABLES `app_bookings_vouchers` WRITE;
/*!40000 ALTER TABLE `app_bookings_vouchers` DISABLE KEYS */;
/*!40000 ALTER TABLE `app_bookings_vouchers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_brands`
--

DROP TABLE IF EXISTS `app_brands`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_brands` (
  `brandID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL,
  `byID` int(11) DEFAULT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `colour` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `logo_path` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `logo_type` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `logo_ext` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `logo_size` bigint(100) DEFAULT NULL,
  `website` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mailchimp_id` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `staff_performance_exclude_session_evaluations` tinyint(1) NOT NULL DEFAULT '0',
  `staff_performance_exclude_pupil_assessments` tinyint(1) NOT NULL DEFAULT '0',
  `hide_online` tinyint(1) NOT NULL DEFAULT '0',
  `added` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`brandID`),
  KEY `fk_brands_byID` (`byID`),
  KEY `fk_brands_accountID` (`accountID`),
  CONSTRAINT `app_brands_ibfk_1` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=614 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_brands`
--

LOCK TABLES `app_brands` WRITE;
/*!40000 ALTER TABLE `app_brands` DISABLE KEYS */;
INSERT INTO `app_brands` VALUES (24,23,224,'Physical Education','purple',NULL,NULL,NULL,NULL,NULL,NULL,0,0,0,'2016-04-15 08:37:28','2016-04-15 10:31:43'),(25,23,224,'Sports Development','pink',NULL,NULL,NULL,NULL,NULL,NULL,0,0,0,'2016-04-15 10:31:25','2016-04-15 10:31:25'),(26,23,224,'Camps','light-blue',NULL,NULL,NULL,NULL,NULL,NULL,0,0,0,'2016-04-15 10:32:32','2016-04-15 10:32:32'),(27,23,224,'Cycle','blue',NULL,NULL,NULL,NULL,NULL,NULL,0,0,0,'2016-04-20 13:30:50','2016-04-22 14:04:39'),(28,23,224,'Training','green',NULL,NULL,NULL,NULL,NULL,NULL,0,0,0,'2016-04-20 13:45:03','2016-04-22 14:04:47');
/*!40000 ALTER TABLE `app_brands` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_equipment`
--

DROP TABLE IF EXISTS `app_equipment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_equipment` (
  `equipmentID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `byID` int(11) DEFAULT NULL,
  `imported` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `location` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `notes` text COLLATE utf8_unicode_ci,
  `added` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`equipmentID`),
  KEY `fk_equipment_byID` (`byID`),
  KEY `fk_equipment_accountID` (`accountID`),
  CONSTRAINT `app_equipment_ibfk_1` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `app_equipment_ibfk_2` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1465 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_equipment`
--

LOCK TABLES `app_equipment` WRITE;
/*!40000 ALTER TABLE `app_equipment` DISABLE KEYS */;
INSERT INTO `app_equipment` VALUES (255,23,NULL,0,'Footballs','Kit Room',150,'Stored in bags of 5.','2016-04-14 15:33:14','2016-04-14 15:33:14'),(256,23,NULL,0,'Pom-Poms','Kit Room',50,'Stored in the black small container.','2016-04-14 15:33:59','2016-04-14 15:33:59'),(257,23,NULL,0,'Tennis Balls','Kit Room',180,'Stored in the yellow container.','2016-04-14 15:34:34','2016-04-14 15:34:34'),(258,23,NULL,0,'Beanbags','Kit Room',120,'','2016-04-14 15:35:24','2016-04-14 15:35:24'),(259,23,NULL,0,'Cones','Kit Room',200,'Stored in blue container.','2016-04-14 15:36:04','2016-04-14 15:36:04'),(260,23,NULL,0,'Dodgeballs','Kit Room',160,'','2016-04-14 15:36:27','2016-04-14 15:36:27'),(261,23,NULL,0,'First Aid Kits','Kit Room',7,'Stored in the red container.','2016-04-14 15:37:10','2016-04-14 15:37:10'),(262,23,NULL,0,'Hockey Sticks','Kit Room',95,'Stored in the Blue bags.','2016-04-14 15:37:55','2016-04-14 15:37:55'),(263,23,NULL,0,'Shuttlecocks','Kit Room',120,'','2016-04-14 15:38:37','2016-04-14 15:38:37'),(264,23,NULL,0,'Hoops','Kit Room',40,'10 red, 10 blue, 10 green, 10 yellow','2016-04-14 15:39:36','2016-04-14 15:39:36'),(265,23,NULL,0,'Badminton Net','Outside Container',1,'','2016-04-18 10:45:02','2016-04-18 10:45:02');
/*!40000 ALTER TABLE `app_equipment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_equipment_bookings`
--

DROP TABLE IF EXISTS `app_equipment_bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_equipment_bookings` (
  `bookingID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `equipmentID` int(11) DEFAULT NULL,
  `type` enum('staff','org','contact','child') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'staff',
  `staffID` int(11) DEFAULT NULL,
  `orgID` int(11) DEFAULT NULL,
  `contactID` int(11) DEFAULT NULL,
  `childID` int(11) DEFAULT NULL,
  `byID` int(11) DEFAULT NULL,
  `dateOut` datetime DEFAULT NULL,
  `dateIn` datetime DEFAULT NULL,
  `quantity` int(45) DEFAULT NULL,
  `status` tinyint(4) NOT NULL,
  `added` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`bookingID`),
  KEY `fk_equipment_bookings_equipmentID` (`equipmentID`),
  KEY `fk_equipment_bookings_byID` (`byID`),
  KEY `fk_equipment_bookings_staffID` (`staffID`),
  KEY `fk_equipment_bookings_accountID` (`accountID`),
  KEY `fk_equipment_bookings_orgID` (`orgID`),
  KEY `fk_equipment_bookings_contactID` (`contactID`),
  KEY `fk_equipment_bookings_childID` (`childID`),
  CONSTRAINT `app_equipment_bookings_ibfk_1` FOREIGN KEY (`equipmentID`) REFERENCES `app_equipment` (`equipmentID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_equipment_bookings_ibfk_2` FOREIGN KEY (`staffID`) REFERENCES `app_staff` (`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_equipment_bookings_ibfk_3` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `app_equipment_bookings_ibfk_4` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_equipment_bookings_childID` FOREIGN KEY (`childID`) REFERENCES `app_family_children` (`childID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `fk_equipment_bookings_contactID` FOREIGN KEY (`contactID`) REFERENCES `app_family_contacts` (`contactID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `fk_equipment_bookings_orgID` FOREIGN KEY (`orgID`) REFERENCES `app_orgs` (`orgID`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1844 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_equipment_bookings`
--

LOCK TABLES `app_equipment_bookings` WRITE;
/*!40000 ALTER TABLE `app_equipment_bookings` DISABLE KEYS */;
INSERT INTO `app_equipment_bookings` VALUES (865,23,258,'staff',225,NULL,NULL,NULL,NULL,'2016-04-22 13:04:59','2016-04-22 13:05:31',5,0,'2016-04-22 13:04:59','2016-04-22 13:04:59'),(866,23,260,'staff',225,NULL,NULL,NULL,NULL,'2016-04-22 13:05:27','2016-07-31 14:00:00',30,1,'2016-04-22 13:05:27','2016-04-22 13:05:27'),(867,23,261,'staff',231,NULL,NULL,NULL,NULL,'2016-04-22 13:05:48','2016-07-31 14:00:00',1,1,'2016-04-22 13:05:48','2016-04-22 13:05:48'),(868,23,265,'staff',229,NULL,NULL,NULL,NULL,'2016-04-22 13:06:16','2016-06-10 11:37:14',1,0,'2016-04-22 13:06:16','2016-04-22 13:06:16'),(961,23,265,'staff',225,NULL,NULL,NULL,NULL,'2016-06-10 11:38:09','2016-06-10 11:39:14',1,0,'2016-06-10 11:38:09','2016-06-10 11:38:09'),(962,23,265,'staff',225,NULL,NULL,NULL,NULL,'2016-06-10 11:40:16','2016-06-10 11:45:01',1,0,'2016-06-10 11:40:16','2016-06-10 11:40:16'),(963,23,265,'staff',225,NULL,NULL,NULL,NULL,'2016-06-10 11:48:51','2016-06-13 14:00:00',1,1,'2016-06-10 11:48:51','2016-06-10 11:48:51');
/*!40000 ALTER TABLE `app_equipment_bookings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_family`
--

DROP TABLE IF EXISTS `app_family`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_family` (
  `familyID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `byID` int(11) DEFAULT NULL,
  `imported` tinyint(1) NOT NULL DEFAULT '0',
  `added` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`familyID`),
  KEY `fk_family_byID` (`byID`),
  KEY `fk_family_accountID` (`accountID`),
  CONSTRAINT `app_family_ibfk_1` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `app_family_ibfk_2` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11700 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_family`
--

LOCK TABLES `app_family` WRITE;
/*!40000 ALTER TABLE `app_family` DISABLE KEYS */;
INSERT INTO `app_family` VALUES (4265,23,224,0,'2016-04-14 15:11:19','2016-04-14 15:11:19'),(4266,23,224,0,'2016-04-14 15:16:02','2016-04-14 15:16:02'),(4267,23,224,0,'2016-04-14 15:19:07','2016-04-14 15:19:07'),(4268,23,224,0,'2016-04-14 15:21:41','2016-04-14 15:21:41'),(4269,23,224,0,'2016-04-14 15:25:10','2016-04-14 15:25:10'),(4270,23,224,0,'2016-04-14 15:27:43','2016-04-14 15:27:43'),(4271,23,224,0,'2016-04-14 15:30:25','2016-04-14 15:30:25'),(4272,23,224,0,'2016-04-15 09:31:52','2016-04-15 09:31:52'),(4273,23,224,0,'2016-04-15 09:36:25','2016-04-15 09:36:25'),(4274,23,224,0,'2016-04-15 09:51:01','2016-04-15 09:51:01');
/*!40000 ALTER TABLE `app_family` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_family_children`
--

DROP TABLE IF EXISTS `app_family_children`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_family_children` (
  `childID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `familyID` int(11) DEFAULT NULL,
  `orgID` int(11) DEFAULT NULL,
  `byID` int(11) DEFAULT NULL,
  `imported` tinyint(1) NOT NULL DEFAULT '0',
  `photoConsent` tinyint(4) DEFAULT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `first_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `last_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `dob` date DEFAULT NULL,
  `last_ecard_year` year(4) DEFAULT NULL,
  `medical` text COLLATE utf8_unicode_ci,
  `added` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`childID`),
  KEY `fk_family_children_familyID` (`familyID`),
  KEY `fk_family_children_orgID` (`orgID`),
  KEY `fk_family_children_byID` (`byID`),
  KEY `fk_family_children_accountID` (`accountID`),
  CONSTRAINT `app_family_children_ibfk_1` FOREIGN KEY (`familyID`) REFERENCES `app_family` (`familyID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_family_children_ibfk_2` FOREIGN KEY (`orgID`) REFERENCES `app_orgs` (`orgID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `app_family_children_ibfk_3` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `app_family_children_ibfk_4` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12331 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_family_children`
--

LOCK TABLES `app_family_children` WRITE;
/*!40000 ALTER TABLE `app_family_children` DISABLE KEYS */;
INSERT INTO `app_family_children` VALUES (5991,23,4270,1862,224,0,1,NULL,'Rebecca','Carrick','2008-04-08',NULL,'Allergic to nuts','2016-04-14 15:27:43','2016-04-14 15:27:43'),(5992,23,4271,1862,224,0,1,NULL,'Ava','Brown','2007-02-14',NULL,'N/A','2016-04-14 15:30:25','2016-04-14 15:30:25'),(5993,23,4271,1862,224,0,1,NULL,'Imogen','Brown','2003-01-08',NULL,'Asthma','2016-04-14 15:31:10','2016-04-14 15:31:23'),(5994,23,4272,1866,224,0,1,NULL,'Ben','Rex','2008-04-17',NULL,'','2016-04-15 09:31:52','2016-04-15 09:31:52'),(5995,23,4272,1866,224,0,1,NULL,'Leo','Rex','2009-12-16',NULL,'','2016-04-15 09:32:30','2016-04-15 09:32:30'),(5996,23,4272,1866,224,0,1,NULL,'Dawn','Rex','2006-04-21',NULL,'','2016-04-15 09:32:50','2016-04-15 09:32:50'),(5997,23,4272,1866,224,0,1,NULL,'Amelia','Rex','2008-04-17',NULL,'','2016-04-15 09:33:15','2016-04-15 09:33:15'),(5998,23,4272,1866,224,0,1,NULL,'Henry','Rex','2009-04-16',NULL,'','2016-04-15 09:33:42','2016-04-15 09:33:42'),(5999,23,4273,1865,224,0,1,NULL,'Freddie','Russell','2008-08-20',NULL,'','2016-04-15 09:36:25','2016-04-15 09:36:25'),(6000,23,4273,1865,224,0,1,NULL,'Erin','Russell','2009-11-12',NULL,'','2016-04-15 09:37:10','2016-04-15 09:37:10'),(6001,23,4273,1865,224,0,1,NULL,'Declan','Russell','2007-04-10',NULL,'','2016-04-15 09:38:22','2016-04-15 09:38:22'),(6002,23,4274,1864,224,0,1,NULL,'Ashley','Sloan','2008-07-10',NULL,'','2016-04-15 09:51:01','2016-04-15 09:51:01'),(6003,23,4274,1864,224,0,1,NULL,'Amelia','Sloan','2009-07-23',NULL,'','2016-04-15 09:51:26','2016-04-15 09:51:26');
/*!40000 ALTER TABLE `app_family_children` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_family_children_tags`
--

DROP TABLE IF EXISTS `app_family_children_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_family_children_tags` (
  `linkID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL,
  `childID` int(11) NOT NULL,
  `tagID` int(11) NOT NULL,
  `added` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`linkID`),
  KEY `accountID` (`accountID`),
  KEY `childID` (`childID`),
  KEY `tagID` (`tagID`),
  CONSTRAINT `app_family_children_tags_ibfk_1` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_family_children_tags_ibfk_2` FOREIGN KEY (`childID`) REFERENCES `app_family_children` (`childID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_family_children_tags_tagID` FOREIGN KEY (`tagID`) REFERENCES `app_settings_tags` (`tagID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_family_children_tags`
--

LOCK TABLES `app_family_children_tags` WRITE;
/*!40000 ALTER TABLE `app_family_children_tags` DISABLE KEYS */;
/*!40000 ALTER TABLE `app_family_children_tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_family_contacts`
--

DROP TABLE IF EXISTS `app_family_contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_family_contacts` (
  `contactID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `familyID` int(11) DEFAULT NULL,
  `byID` int(11) DEFAULT NULL,
  `imported` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `title` enum('mr','mrs','miss','ms','dr') COLLATE utf8_unicode_ci DEFAULT NULL,
  `first_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `last_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `address1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address3` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `town` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `county` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `postcode` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mobile` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `workPhone` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `gender` enum('male','female','other') COLLATE utf8_unicode_ci DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `medical` text COLLATE utf8_unicode_ci,
  `password` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `relationship` enum('parent','grandparent','guardian','parents friend','other','individual') COLLATE utf8_unicode_ci DEFAULT NULL,
  `gc_redirect_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `gc_customer_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `gc_mandate_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `main` tinyint(1) NOT NULL DEFAULT '0',
  `mc_synced` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `blacklisted` tinyint(1) NOT NULL DEFAULT '0',
  `added` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`contactID`),
  KEY `fk_family_contacts_familyID` (`familyID`),
  KEY `fk_family_contacts_byID` (`byID`),
  KEY `fk_family_contacts_accountID` (`accountID`),
  CONSTRAINT `app_family_contacts_ibfk_1` FOREIGN KEY (`familyID`) REFERENCES `app_family` (`familyID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_family_contacts_ibfk_2` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `app_family_contacts_ibfk_3` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11729 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_family_contacts`
--

LOCK TABLES `app_family_contacts` WRITE;
/*!40000 ALTER TABLE `app_family_contacts` DISABLE KEYS */;
INSERT INTO `app_family_contacts` VALUES (4277,23,4265,224,0,NULL,'miss','Emma','Flowers','4 Tranby Ave HU13 0PX','','','Hessle','East Riding of Yorkshire','HU13 0PX','01482 465212','07455986214','','e.flowers10@live.co.uk','female','1990-07-10','Allergic to nuts','$2y$10$8Y3SHvpvrpdlJCEK.IY5Au.8Ep1E/0qT0ijRsgeCodsoy2pku7T.e','individual',NULL,NULL,NULL,1,'2016-04-14 15:11:19',0,'2016-04-14 15:11:19','2016-04-14 15:11:19'),(4278,23,4266,224,0,NULL,'mr','Ibraham','Sharif','18 Pickering Road','','','Hull','Kingston Upon Hull','HU4 6TA','01482 3652149','07459623587','','ibraham.21@hotmail.com','male','1969-04-02','N/A','$2y$10$Ofb1wlAdTo07qimKDuvd4Oj5jkloqKSbIDKvL7D1laEhM8UyTzoQu','individual',NULL,NULL,NULL,1,'2016-04-14 15:16:02',0,'2016-04-14 15:16:02','2016-04-14 15:16:02'),(4279,23,4267,224,0,NULL,'mrs','Abigail','Witherwick','Hessle Road','','','Hull','Kingston Upon Hull','HU3 2AA','01482 569842','07459652365','','Abi_w87@live.com','female','1980-12-23','Asthma','$2y$10$pjERqGL7uKzrE96S.xC6VOhryuf8gXT/yaPzmOo/qlIaMvrLGGSMm','individual',NULL,NULL,NULL,1,'2016-04-14 15:19:07',0,'2016-04-14 15:19:07','2016-04-14 15:19:07'),(4280,23,4268,224,0,NULL,'mr','John','Stathers','Anlaby Avenue','','','Hull','East Yorkshire','HU4 6AU','01482 569898','07458965235','','stathers.12@hotmail.com','male','1991-01-11','N/A','$2y$10$2bX7pEUroRUwTgNzjxsPSO7SHvq8c.M0Paq5xARKJFrXvjE5ZoXDS','individual',NULL,NULL,NULL,1,'2016-04-14 15:21:41',0,'2016-04-14 15:21:41','2016-04-14 15:21:41'),(4281,23,4269,224,0,NULL,'ms','Joan','Milner','43 Burniston road','','','Hull','East Yorkshire','HU7 8JK','01482 265984','07955632145','','joan_m@karoo.com','female','1975-04-12','N/A','$2y$10$2FQBdM9RKFM1fWV7GVH7neKghr0fhnFjDGoyO4y54g3sgH3AIsZKy','individual',NULL,NULL,NULL,1,'2016-04-14 15:25:11',0,'2016-04-14 15:25:10','2016-04-14 15:25:10'),(4282,23,4270,224,0,NULL,'mr','Alan','Carrick','4 Thornbald Road','','','Hull','East Yorkshire','HU16 7HB','01482 569235','07855632588','','A.carrick22@hotmail.co.uk','male',NULL,'','$2y$10$.3IJ649ZLF1k5s0ipWq2MOc.GreFhwzTv.dkrO/E.t0Kr375M6v0O','',NULL,NULL,NULL,1,'2016-04-14 15:27:44',0,'2016-04-14 15:27:43','2016-04-14 15:27:43'),(4283,23,4271,224,0,NULL,'mrs','Jo','Brown','Inmans Road','','','Hedon','East Yorkshire','HU12 8NL','01482 563244','07455896523','','brown.jo@outlook.co.uk',NULL,NULL,'','$2y$10$nhYaAjqp6pQlOpB1tXZoReu6bMORFoEhcLiJxWmbz57RAVulJxesq','',NULL,NULL,NULL,1,'2016-04-14 15:30:25',0,'2016-04-14 15:30:25','2016-04-14 15:30:25'),(4284,23,4272,224,0,NULL,'mr','Andrew','Rex','23 North Road','','','Hull','East Yorkshire','HU4 6AS','01482 456325','07422563259','','rex@hotmail.co.uk',NULL,NULL,'','$2y$10$.LDb.D.vO7q0EK5DmMX7JeseXrfSfEFYwZQkICcvsnedLNi6K0OM.','',NULL,NULL,NULL,1,'2016-04-15 09:31:52',0,'2016-04-15 09:31:52','2016-04-15 09:31:52'),(4285,23,4273,224,0,NULL,NULL,'Ellie','Russell','31 Marble Road','','','Hull','East Yorkshire','HU13 6AU','01482 639936','07233255412','','E.russell@hotmail.co.uk',NULL,NULL,'','$2y$10$B6BWDZwun8/fhan3SpoPl.IjZpVweWjNvy7cXwXuXt/fyAbeqyQMm','',NULL,NULL,NULL,1,'2016-04-15 09:36:25',0,'2016-04-15 09:36:25','2016-04-15 09:36:25'),(4286,23,4274,224,0,NULL,NULL,'Jane','Sloan','41 Chestnut Avenue','','','Hull','East Yorkshire','HU4 6AS','01482 354777','07855235411','','janesloan@live.com',NULL,NULL,'','$2y$10$8jqok9KWx5huenHq3syFueF9RBN.kd8xvOijeILsRY/CrKR5xjDj.','',NULL,NULL,NULL,1,'2016-04-15 09:51:02',0,'2016-04-15 09:51:01','2016-04-15 09:51:01');
/*!40000 ALTER TABLE `app_family_contacts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_family_contacts_newsletters`
--

DROP TABLE IF EXISTS `app_family_contacts_newsletters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_family_contacts_newsletters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `contactID` int(11) DEFAULT NULL,
  `brandID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_family_contacts_newsletters_attachmentID` (`contactID`),
  KEY `fk_family_contacts_newsletters_accountID` (`accountID`),
  KEY `fk_family_contacts_newsletters_brandID` (`brandID`),
  CONSTRAINT `app_family_contacts_newsletters_ibfk_1` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_family_contacts_newsletters_ibfk_2` FOREIGN KEY (`contactID`) REFERENCES `app_family_contacts` (`contactID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_family_contacts_newsletters_ibfk_3` FOREIGN KEY (`brandID`) REFERENCES `app_brands` (`brandID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_family_contacts_newsletters`
--

LOCK TABLES `app_family_contacts_newsletters` WRITE;
/*!40000 ALTER TABLE `app_family_contacts_newsletters` DISABLE KEYS */;
/*!40000 ALTER TABLE `app_family_contacts_newsletters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_family_contacts_tags`
--

DROP TABLE IF EXISTS `app_family_contacts_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_family_contacts_tags` (
  `linkID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL,
  `contactID` int(11) NOT NULL,
  `tagID` int(11) NOT NULL,
  `added` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`linkID`),
  KEY `accountID` (`accountID`),
  KEY `contactID` (`contactID`),
  KEY `tagID` (`tagID`),
  CONSTRAINT `app_family_contacts_tags_ibfk_1` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_family_contacts_tags_ibfk_2` FOREIGN KEY (`contactID`) REFERENCES `app_family_contacts` (`contactID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_family_contacts_tags_tagID` FOREIGN KEY (`tagID`) REFERENCES `app_settings_tags` (`tagID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_family_contacts_tags`
--

LOCK TABLES `app_family_contacts_tags` WRITE;
/*!40000 ALTER TABLE `app_family_contacts_tags` DISABLE KEYS */;
/*!40000 ALTER TABLE `app_family_contacts_tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_family_notes`
--

DROP TABLE IF EXISTS `app_family_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_family_notes` (
  `noteID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `familyID` int(11) DEFAULT NULL,
  `byID` int(11) DEFAULT NULL,
  `summary` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `content` text COLLATE utf8_unicode_ci,
  `added` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`noteID`),
  KEY `fk_family_notes_familyID` (`familyID`),
  KEY `fk_family_notes_byID` (`byID`),
  KEY `fk_family_notes_accountID` (`accountID`),
  CONSTRAINT `app_family_notes_ibfk_3` FOREIGN KEY (`familyID`) REFERENCES `app_family` (`familyID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_family_notes_ibfk_4` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `app_family_notes_ibfk_5` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_family_notes`
--

LOCK TABLES `app_family_notes` WRITE;
/*!40000 ALTER TABLE `app_family_notes` DISABLE KEYS */;
/*!40000 ALTER TABLE `app_family_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_family_notifications`
--

DROP TABLE IF EXISTS `app_family_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_family_notifications` (
  `notificationID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `familyID` int(11) NOT NULL,
  `contactID` int(11) DEFAULT NULL,
  `byID` int(11) DEFAULT NULL,
  `type` enum('email','SMS') COLLATE utf8_unicode_ci NOT NULL,
  `destination` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `subject` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `contentText` text COLLATE utf8_unicode_ci NOT NULL,
  `contentHTML` text COLLATE utf8_unicode_ci,
  `status` enum('pending','sent','delivered','undelivered','invalid','unknown') COLLATE utf8_unicode_ci NOT NULL,
  `added` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`notificationID`),
  KEY `fk_family_notifications_familyID` (`familyID`),
  KEY `fk_family_notifications_contactID` (`contactID`),
  KEY `fk_family_notifications_byID` (`byID`),
  KEY `fk_family_notifications_accountID` (`accountID`),
  CONSTRAINT `app_family_notifications_ibfk_1` FOREIGN KEY (`familyID`) REFERENCES `app_family` (`familyID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_family_notifications_ibfk_2` FOREIGN KEY (`contactID`) REFERENCES `app_family_contacts` (`contactID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `app_family_notifications_ibfk_3` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `app_family_notifications_ibfk_4` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20364 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_family_notifications`
--

LOCK TABLES `app_family_notifications` WRITE;
/*!40000 ALTER TABLE `app_family_notifications` DISABLE KEYS */;
INSERT INTO `app_family_notifications` VALUES (10481,23,4273,4285,224,'email','E.russell@hotmail.co.uk','Booking Confirmation for Fun Camp, Green Road Infants School','Hi Ellie, \n\nThank you for your booking for Fun Camp, Green Road Infants School\nbetween 01/01/2017 and 31/12/2017. Please find below details of the\nsessions booked: \n\nDECLAN RUSSELL\n\nFun Camp, Green Road Infants School, February Half Term (13/02/2017 to\n19/02/2017) \n\n 		DAY\n 		START\n 		END\n 		ACTIVITY\n 		TYPE\n\n 		Monday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Monday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\n 		Tuesday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Tuesday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\n 		Wednesday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Wednesday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\n 		Thursday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Thursday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\n 		Friday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Friday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\nFun Camp, Green Road Infants School, Easter Half Term Week 1\n(27/03/2017 to 02/04/2017) \n\n 		DAY\n 		START\n 		END\n 		ACTIVITY\n 		TYPE\n\n 		Wednesday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Wednesday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\nFun Camp, Green Road Infants School, Easter Half Term Week 2\n(10/04/2017 to 16/04/2017) \n\n 		DAY\n 		START\n 		END\n 		ACTIVITY\n 		TYPE\n\n 		Wednesday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Wednesday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\n 		Friday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Friday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\nERIN RUSSELL\n\nFun Camp, Green Road Infants School, February Half Term (13/02/2017 to\n19/02/2017) \n\n 		DAY\n 		START\n 		END\n 		ACTIVITY\n 		TYPE\n\n 		Monday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Monday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\n 		Tuesday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Tuesday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\n 		Wednesday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Wednesday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\n 		Thursday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Thursday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\n 		Friday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Friday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\nFun Camp, Green Road Infants School, Easter Half Term Week 1\n(27/03/2017 to 02/04/2017) \n\n 		DAY\n 		START\n 		END\n 		ACTIVITY\n 		TYPE\n\n 		Wednesday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Wednesday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\nFun Camp, Green Road Infants School, Easter Half Term Week 2\n(10/04/2017 to 16/04/2017) \n\n 		DAY\n 		START\n 		END\n 		ACTIVITY\n 		TYPE\n\n 		Wednesday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Wednesday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\n 		Friday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Friday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\nFREDDIE RUSSELL\n\nFun Camp, Green Road Infants School, February Half Term (13/02/2017 to\n19/02/2017) \n\n 		DAY\n 		START\n 		END\n 		ACTIVITY\n 		TYPE\n\n 		Monday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Monday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\n 		Tuesday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Tuesday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\n 		Wednesday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Wednesday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\n 		Thursday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Thursday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\n 		Friday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Friday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\nFun Camp, Green Road Infants School, Easter Half Term Week 1\n(27/03/2017 to 02/04/2017) \n\n 		DAY\n 		START\n 		END\n 		ACTIVITY\n 		TYPE\n\n 		Wednesday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Wednesday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\nFun Camp, Green Road Infants School, Easter Half Term Week 2\n(10/04/2017 to 16/04/2017) \n\n 		DAY\n 		START\n 		END\n 		ACTIVITY\n 		TYPE\n\n 		Wednesday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Wednesday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\n 		Friday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Friday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\nSub Total: £336.00\n\nDiscount: £15.00\n\nTotal: £321.00\n\nOutstanding: £321.00 \n\nPlease check all details throroughly, should you notice any mistakes\nto your booking or would like to make an amendment please contact a\nmember of the team.','<p>Hi Ellie,</p>\r\n<p>Thank you for your booking for Fun Camp, Green Road Infants School  between 01/01/2017 and 31/12/2017. Please find below details of the sessions booked:</p>\r\n<p><strong>Declan Russell</strong></p><p>Fun Camp, Green Road Infants School, February Half Term (13/02/2017 to 19/02/2017)</p>\n							<table width=\"100%\" border=\"1\">\n								<tr>\n									<th scope=\"col\">Day</th>\n									<th scope=\"col\">Start</th>\n									<th scope=\"col\">End</th>\n									<th scope=\"col\">Activity</th>\n									<th scope=\"col\">Type</th>\n								</tr><tr>\n							<td>Monday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Monday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr><tr>\n							<td>Tuesday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Tuesday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr><tr>\n							<td>Wednesday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Wednesday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr><tr>\n							<td>Thursday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Thursday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr><tr>\n							<td>Friday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Friday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr></table><p>Fun Camp, Green Road Infants School, Easter Half Term Week 1 (27/03/2017 to 02/04/2017)</p>\n							<table width=\"100%\" border=\"1\">\n								<tr>\n									<th scope=\"col\">Day</th>\n									<th scope=\"col\">Start</th>\n									<th scope=\"col\">End</th>\n									<th scope=\"col\">Activity</th>\n									<th scope=\"col\">Type</th>\n								</tr><tr>\n							<td>Wednesday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Wednesday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr></table><p>Fun Camp, Green Road Infants School, Easter Half Term Week 2 (10/04/2017 to 16/04/2017)</p>\n							<table width=\"100%\" border=\"1\">\n								<tr>\n									<th scope=\"col\">Day</th>\n									<th scope=\"col\">Start</th>\n									<th scope=\"col\">End</th>\n									<th scope=\"col\">Activity</th>\n									<th scope=\"col\">Type</th>\n								</tr><tr>\n							<td>Wednesday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Wednesday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr><tr>\n							<td>Friday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Friday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr></table><p><strong>Erin Russell</strong></p><p>Fun Camp, Green Road Infants School, February Half Term (13/02/2017 to 19/02/2017)</p>\n							<table width=\"100%\" border=\"1\">\n								<tr>\n									<th scope=\"col\">Day</th>\n									<th scope=\"col\">Start</th>\n									<th scope=\"col\">End</th>\n									<th scope=\"col\">Activity</th>\n									<th scope=\"col\">Type</th>\n								</tr><tr>\n							<td>Monday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Monday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr><tr>\n							<td>Tuesday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Tuesday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr><tr>\n							<td>Wednesday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Wednesday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr><tr>\n							<td>Thursday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Thursday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr><tr>\n							<td>Friday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Friday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr></table><p>Fun Camp, Green Road Infants School, Easter Half Term Week 1 (27/03/2017 to 02/04/2017)</p>\n							<table width=\"100%\" border=\"1\">\n								<tr>\n									<th scope=\"col\">Day</th>\n									<th scope=\"col\">Start</th>\n									<th scope=\"col\">End</th>\n									<th scope=\"col\">Activity</th>\n									<th scope=\"col\">Type</th>\n								</tr><tr>\n							<td>Wednesday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Wednesday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr></table><p>Fun Camp, Green Road Infants School, Easter Half Term Week 2 (10/04/2017 to 16/04/2017)</p>\n							<table width=\"100%\" border=\"1\">\n								<tr>\n									<th scope=\"col\">Day</th>\n									<th scope=\"col\">Start</th>\n									<th scope=\"col\">End</th>\n									<th scope=\"col\">Activity</th>\n									<th scope=\"col\">Type</th>\n								</tr><tr>\n							<td>Wednesday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Wednesday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr><tr>\n							<td>Friday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Friday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr></table><p><strong>Freddie Russell</strong></p><p>Fun Camp, Green Road Infants School, February Half Term (13/02/2017 to 19/02/2017)</p>\n							<table width=\"100%\" border=\"1\">\n								<tr>\n									<th scope=\"col\">Day</th>\n									<th scope=\"col\">Start</th>\n									<th scope=\"col\">End</th>\n									<th scope=\"col\">Activity</th>\n									<th scope=\"col\">Type</th>\n								</tr><tr>\n							<td>Monday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Monday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr><tr>\n							<td>Tuesday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Tuesday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr><tr>\n							<td>Wednesday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Wednesday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr><tr>\n							<td>Thursday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Thursday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr><tr>\n							<td>Friday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Friday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr></table><p>Fun Camp, Green Road Infants School, Easter Half Term Week 1 (27/03/2017 to 02/04/2017)</p>\n							<table width=\"100%\" border=\"1\">\n								<tr>\n									<th scope=\"col\">Day</th>\n									<th scope=\"col\">Start</th>\n									<th scope=\"col\">End</th>\n									<th scope=\"col\">Activity</th>\n									<th scope=\"col\">Type</th>\n								</tr><tr>\n							<td>Wednesday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Wednesday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr></table><p>Fun Camp, Green Road Infants School, Easter Half Term Week 2 (10/04/2017 to 16/04/2017)</p>\n							<table width=\"100%\" border=\"1\">\n								<tr>\n									<th scope=\"col\">Day</th>\n									<th scope=\"col\">Start</th>\n									<th scope=\"col\">End</th>\n									<th scope=\"col\">Activity</th>\n									<th scope=\"col\">Type</th>\n								</tr><tr>\n							<td>Wednesday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Wednesday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr><tr>\n							<td>Friday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Friday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr></table><p>Sub Total: £336.00</p><p>Discount: £15.00</p><p>Total: £321.00</p><p>Outstanding: £321.00</p>\r\n<p>Please check all details throroughly, should you notice any mistakes to your booking or would like to make an amendment please contact a member of the team.</p>','sent','2016-04-22 12:59:42','2016-04-22 12:59:42'),(10482,23,4271,4283,224,'email','brown.jo@outlook.co.uk','Booking Confirmation for Fun Camp, Green Road Infants School','Hi Jo, \n\nThank you for your booking for Fun Camp, Green Road Infants School\nbetween 01/01/2017 and 31/12/2017. Please find below details of the\nsessions booked: \n\nAVA BROWN\n\nFun Camp, Green Road Infants School, February Half Term (13/02/2017 to\n19/02/2017) \n\n 		DAY\n 		START\n 		END\n 		ACTIVITY\n 		TYPE\n\n 		Monday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Monday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\nFun Camp, Green Road Infants School, Easter Half Term Week 1\n(27/03/2017 to 02/04/2017) \n\n 		DAY\n 		START\n 		END\n 		ACTIVITY\n 		TYPE\n\n 		Monday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Monday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\nFun Camp, Green Road Infants School, Easter Half Term Week 2\n(10/04/2017 to 16/04/2017) \n\n 		DAY\n 		START\n 		END\n 		ACTIVITY\n 		TYPE\n\n 		Monday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Monday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\nIMOGEN BROWN\n\nFun Camp, Green Road Infants School, February Half Term (13/02/2017 to\n19/02/2017) \n\n 		DAY\n 		START\n 		END\n 		ACTIVITY\n 		TYPE\n\n 		Monday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Monday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\nFun Camp, Green Road Infants School, Easter Half Term Week 1\n(27/03/2017 to 02/04/2017) \n\n 		DAY\n 		START\n 		END\n 		ACTIVITY\n 		TYPE\n\n 		Monday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Monday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\nFun Camp, Green Road Infants School, Easter Half Term Week 2\n(10/04/2017 to 16/04/2017) \n\n 		DAY\n 		START\n 		END\n 		ACTIVITY\n 		TYPE\n\n 		Monday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Monday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\nTotal: £84.00\n\nPayments:\n £84.00 (22/04/2016 - Cash) \n\nPlease check all details throroughly, should you notice any mistakes\nto your booking or would like to make an amendment please contact a\nmember of the team.','<p>Hi Jo,</p>\r\n<p>Thank you for your booking for Fun Camp, Green Road Infants School  between 01/01/2017 and 31/12/2017. Please find below details of the sessions booked:</p>\r\n<p><strong>Ava Brown</strong></p><p>Fun Camp, Green Road Infants School, February Half Term (13/02/2017 to 19/02/2017)</p>\n							<table width=\"100%\" border=\"1\">\n								<tr>\n									<th scope=\"col\">Day</th>\n									<th scope=\"col\">Start</th>\n									<th scope=\"col\">End</th>\n									<th scope=\"col\">Activity</th>\n									<th scope=\"col\">Type</th>\n								</tr><tr>\n							<td>Monday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Monday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr></table><p>Fun Camp, Green Road Infants School, Easter Half Term Week 1 (27/03/2017 to 02/04/2017)</p>\n							<table width=\"100%\" border=\"1\">\n								<tr>\n									<th scope=\"col\">Day</th>\n									<th scope=\"col\">Start</th>\n									<th scope=\"col\">End</th>\n									<th scope=\"col\">Activity</th>\n									<th scope=\"col\">Type</th>\n								</tr><tr>\n							<td>Monday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Monday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr></table><p>Fun Camp, Green Road Infants School, Easter Half Term Week 2 (10/04/2017 to 16/04/2017)</p>\n							<table width=\"100%\" border=\"1\">\n								<tr>\n									<th scope=\"col\">Day</th>\n									<th scope=\"col\">Start</th>\n									<th scope=\"col\">End</th>\n									<th scope=\"col\">Activity</th>\n									<th scope=\"col\">Type</th>\n								</tr><tr>\n							<td>Monday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Monday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr></table><p><strong>Imogen Brown</strong></p><p>Fun Camp, Green Road Infants School, February Half Term (13/02/2017 to 19/02/2017)</p>\n							<table width=\"100%\" border=\"1\">\n								<tr>\n									<th scope=\"col\">Day</th>\n									<th scope=\"col\">Start</th>\n									<th scope=\"col\">End</th>\n									<th scope=\"col\">Activity</th>\n									<th scope=\"col\">Type</th>\n								</tr><tr>\n							<td>Monday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Monday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr></table><p>Fun Camp, Green Road Infants School, Easter Half Term Week 1 (27/03/2017 to 02/04/2017)</p>\n							<table width=\"100%\" border=\"1\">\n								<tr>\n									<th scope=\"col\">Day</th>\n									<th scope=\"col\">Start</th>\n									<th scope=\"col\">End</th>\n									<th scope=\"col\">Activity</th>\n									<th scope=\"col\">Type</th>\n								</tr><tr>\n							<td>Monday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Monday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr></table><p>Fun Camp, Green Road Infants School, Easter Half Term Week 2 (10/04/2017 to 16/04/2017)</p>\n							<table width=\"100%\" border=\"1\">\n								<tr>\n									<th scope=\"col\">Day</th>\n									<th scope=\"col\">Start</th>\n									<th scope=\"col\">End</th>\n									<th scope=\"col\">Activity</th>\n									<th scope=\"col\">Type</th>\n								</tr><tr>\n							<td>Monday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Monday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr></table><p>Total: £84.00</p><p>Payments:<br />\n£84.00 (22/04/2016 - Cash)</p>\r\n<p>Please check all details throroughly, should you notice any mistakes to your booking or would like to make an amendment please contact a member of the team.</p>','sent','2016-04-22 13:00:29','2016-04-22 13:00:29'),(10483,23,4267,4279,224,'email','Abi_w87@live.com','Booking Confirmation for First Aid Training','Hi Abigail, \n\nThank you for your booking for First Aid Training between 08/06/2016\nand 08/06/2016. Please find below details of the sessions booked: \n\nABIGAIL WITHERWICK\n\nFirst Aid Annual Refresher Training (08/06/2016) \n\n 		DAY\n 		START\n 		END\n 		ACTIVITY\n 		TYPE\n\n 		08/06/2016 (Wednesday)\n 		09:30\n 		13:00\n 		Annual FA Refresher Training\n 		Training\n\n 		08/06/2016 (Wednesday)\n 		13:30\n 		16:30\n 		Annual FA Refresher Training\n 		Training\n\nTotal: £100.00\n\nPayments:\n £100.00 (22/04/2016 - Cheque) \n\nPlease check everything and if anything is incorrect, please contact\nus.','<p>Hi Abigail,</p>\r\n<p>Thank you for your booking for First Aid Training  between 08/06/2016 and 08/06/2016. Please find below details of the sessions booked:</p>\r\n<p><strong>Abigail Witherwick</strong></p><p>First Aid Annual Refresher Training (08/06/2016)</p>\n							<table width=\"100%\" border=\"1\">\n								<tr>\n									<th scope=\"col\">Day</th>\n									<th scope=\"col\">Start</th>\n									<th scope=\"col\">End</th>\n									<th scope=\"col\">Activity</th>\n									<th scope=\"col\">Type</th>\n								</tr><tr>\n							<td>08/06/2016 (Wednesday)</td>\n							<td>09:30</td>\n							<td>13:00</td>\n							<td>Annual FA Refresher Training</td>\n							<td>Training</td>\n						</tr><tr>\n							<td>08/06/2016 (Wednesday)</td>\n							<td>13:30</td>\n							<td>16:30</td>\n							<td>Annual FA Refresher Training</td>\n							<td>Training</td>\n						</tr></table><p>Total: £100.00</p><p>Payments:<br />\n£100.00 (22/04/2016 - Cheque)</p>\r\n<p>Please check everything and if anything is incorrect, please contact us.</p>','sent','2016-04-22 13:20:27','2016-04-22 13:20:27'),(10484,23,4270,4282,224,'email','A.carrick22@hotmail.co.uk','Booking Confirmation for First Aid Training','Hi Alan, \n\nThank you for your booking for First Aid Training between 08/06/2016\nand 08/06/2016. Please find below details of the sessions booked: \n\nALAN CARRICK\n\nFirst Aid Annual Refresher Training (08/06/2016) \n\n 		DAY\n 		START\n 		END\n 		ACTIVITY\n 		TYPE\n\n 		08/06/2016 (Wednesday)\n 		09:30\n 		13:00\n 		Annual FA Refresher Training\n 		Training\n\n 		08/06/2016 (Wednesday)\n 		13:30\n 		16:30\n 		Annual FA Refresher Training\n 		Training\n\nTotal: £100.00\n\nOutstanding: £100.00 \n\nPlease check everything and if anything is incorrect, please contact\nus.','<p>Hi Alan,</p>\r\n<p>Thank you for your booking for First Aid Training  between 08/06/2016 and 08/06/2016. Please find below details of the sessions booked:</p>\r\n<p><strong>Alan Carrick</strong></p><p>First Aid Annual Refresher Training (08/06/2016)</p>\n							<table width=\"100%\" border=\"1\">\n								<tr>\n									<th scope=\"col\">Day</th>\n									<th scope=\"col\">Start</th>\n									<th scope=\"col\">End</th>\n									<th scope=\"col\">Activity</th>\n									<th scope=\"col\">Type</th>\n								</tr><tr>\n							<td>08/06/2016 (Wednesday)</td>\n							<td>09:30</td>\n							<td>13:00</td>\n							<td>Annual FA Refresher Training</td>\n							<td>Training</td>\n						</tr><tr>\n							<td>08/06/2016 (Wednesday)</td>\n							<td>13:30</td>\n							<td>16:30</td>\n							<td>Annual FA Refresher Training</td>\n							<td>Training</td>\n						</tr></table><p>Total: £100.00</p><p>Outstanding: £100.00</p>\r\n<p>Please check everything and if anything is incorrect, please contact us.</p>','sent','2016-04-22 13:20:43','2016-04-22 13:20:43'),(10485,23,4270,4282,224,'email','A.carrick22@hotmail.co.uk','Booking Confirmation for Fun Camp, Green Road Infants School','Hi Alan, \n\nThank you for your booking for Fun Camp, Green Road Infants School\nbetween 01/01/2017 and 31/12/2017. Please find below details of the\nsessions booked: \n\nREBECCA CARRICK\n\nFun Camp, Green Road Infants School, February Half Term (13/02/2017 to\n19/02/2017) \n\n 		DAY\n 		START\n 		END\n 		ACTIVITY\n 		TYPE\n\n 		Monday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Monday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\n 		Tuesday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Tuesday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\n 		Thursday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\n 		Friday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\nFun Camp, Green Road Infants School, Easter Half Term Week 1\n(27/03/2017 to 02/04/2017) \n\n 		DAY\n 		START\n 		END\n 		ACTIVITY\n 		TYPE\n\n 		Monday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Monday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\n 		Tuesday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\n 		Wednesday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\n 		Thursday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\nFun Camp, Green Road Infants School, Easter Half Term Week 2\n(10/04/2017 to 16/04/2017) \n\n 		DAY\n 		START\n 		END\n 		ACTIVITY\n 		TYPE\n\n 		Monday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Monday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\n 		Tuesday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\n 		Wednesday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Wednesday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\n 		Thursday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\nTotal: £140.00\n\nOutstanding: £140.00 \n\nPlease check all details throroughly, should you notice any mistakes\nto your booking or would like to make an amendment please contact a\nmember of the team.','<p>Hi Alan,</p>\r\n<p>Thank you for your booking for Fun Camp, Green Road Infants School  between 01/01/2017 and 31/12/2017. Please find below details of the sessions booked:</p>\r\n<p><strong>Rebecca Carrick</strong></p><p>Fun Camp, Green Road Infants School, February Half Term (13/02/2017 to 19/02/2017)</p>\n							<table width=\"100%\" border=\"1\">\n								<tr>\n									<th scope=\"col\">Day</th>\n									<th scope=\"col\">Start</th>\n									<th scope=\"col\">End</th>\n									<th scope=\"col\">Activity</th>\n									<th scope=\"col\">Type</th>\n								</tr><tr>\n							<td>Monday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Monday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr><tr>\n							<td>Tuesday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Tuesday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr><tr>\n							<td>Thursday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr><tr>\n							<td>Friday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr></table><p>Fun Camp, Green Road Infants School, Easter Half Term Week 1 (27/03/2017 to 02/04/2017)</p>\n							<table width=\"100%\" border=\"1\">\n								<tr>\n									<th scope=\"col\">Day</th>\n									<th scope=\"col\">Start</th>\n									<th scope=\"col\">End</th>\n									<th scope=\"col\">Activity</th>\n									<th scope=\"col\">Type</th>\n								</tr><tr>\n							<td>Monday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Monday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr><tr>\n							<td>Tuesday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr><tr>\n							<td>Wednesday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr><tr>\n							<td>Thursday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr></table><p>Fun Camp, Green Road Infants School, Easter Half Term Week 2 (10/04/2017 to 16/04/2017)</p>\n							<table width=\"100%\" border=\"1\">\n								<tr>\n									<th scope=\"col\">Day</th>\n									<th scope=\"col\">Start</th>\n									<th scope=\"col\">End</th>\n									<th scope=\"col\">Activity</th>\n									<th scope=\"col\">Type</th>\n								</tr><tr>\n							<td>Monday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Monday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr><tr>\n							<td>Tuesday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr><tr>\n							<td>Wednesday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Wednesday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr><tr>\n							<td>Thursday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr></table><p>Total: £140.00</p><p>Outstanding: £140.00</p>\r\n<p>Please check all details throroughly, should you notice any mistakes to your booking or would like to make an amendment please contact a member of the team.</p>','sent','2016-04-22 14:57:05','2016-04-22 14:57:05'),(10486,23,4270,4282,224,'email','A.carrick22@hotmail.co.uk','Booking Confirmation for Fun Camp, Springfield Primary','Hi Alan, \n\nThank you for your booking for Fun Camp, Springfield Primary between\n01/01/2017 and 31/12/2017. Please find below details of the sessions\nbooked: \n\nREBECCA CARRICK\n\nFun Camp, Springfield Primary - Easter Half Term Week 1 (10/04/2017 to\n14/04/2017) \n\n 		DAY\n 		START\n 		END\n 		ACTIVITY\n 		TYPE\n\n 		Friday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\nFun Camp, Springfield Primary - Easter Half Term Week 2 (17/04/2017 to\n21/04/2017) \n\n 		DAY\n 		START\n 		END\n 		ACTIVITY\n 		TYPE\n\n 		Monday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\n 		Tuesday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\n 		Wednesday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\nTotal: £40.00\n\nOutstanding: £40.00 \n\nPlease check everything and if anything is incorrect, please contact\nus.','<p>Hi Alan,</p>\r\n<p>Thank you for your booking for Fun Camp, Springfield Primary  between 01/01/2017 and 31/12/2017. Please find below details of the sessions booked:</p>\r\n<p><strong>Rebecca Carrick</strong></p><p>Fun Camp, Springfield Primary - Easter Half Term Week 1 (10/04/2017 to 14/04/2017)</p>\n							<table width=\"100%\" border=\"1\">\n								<tr>\n									<th scope=\"col\">Day</th>\n									<th scope=\"col\">Start</th>\n									<th scope=\"col\">End</th>\n									<th scope=\"col\">Activity</th>\n									<th scope=\"col\">Type</th>\n								</tr><tr>\n							<td>Friday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr></table><p>Fun Camp, Springfield Primary - Easter Half Term Week 2 (17/04/2017 to 21/04/2017)</p>\n							<table width=\"100%\" border=\"1\">\n								<tr>\n									<th scope=\"col\">Day</th>\n									<th scope=\"col\">Start</th>\n									<th scope=\"col\">End</th>\n									<th scope=\"col\">Activity</th>\n									<th scope=\"col\">Type</th>\n								</tr><tr>\n							<td>Monday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr><tr>\n							<td>Tuesday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr><tr>\n							<td>Wednesday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr></table><p>Total: £40.00</p><p>Outstanding: £40.00</p>\r\n<p>Please check everything and if anything is incorrect, please contact us.</p>','sent','2016-04-22 14:57:45','2016-04-22 14:57:45'),(10487,23,4271,4283,224,'email','brown.jo@outlook.co.uk','Booking Confirmation for Fun Camp, Springfield Primary','Hi Jo, \n\nThank you for your booking for Fun Camp, Springfield Primary between\n01/01/2017 and 31/12/2017. Please find below details of the sessions\nbooked: \n\nAVA BROWN\n\nFun Camp, Springfield Primary - February Half Term (13/02/2017 to\n17/02/2017) \n\n 		DAY\n 		START\n 		END\n 		ACTIVITY\n 		TYPE\n\n 		Tuesday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Tuesday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\nFun Camp, Springfield Primary - Easter Half Term Week 1 (10/04/2017 to\n14/04/2017) \n\n 		DAY\n 		START\n 		END\n 		ACTIVITY\n 		TYPE\n\n 		Tuesday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Tuesday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\nFun Camp, Springfield Primary - Easter Half Term Week 2 (17/04/2017 to\n21/04/2017) \n\n 		DAY\n 		START\n 		END\n 		ACTIVITY\n 		TYPE\n\n 		Tuesday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Tuesday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\nIMOGEN BROWN\n\nFun Camp, Springfield Primary - February Half Term (13/02/2017 to\n17/02/2017) \n\n 		DAY\n 		START\n 		END\n 		ACTIVITY\n 		TYPE\n\n 		Tuesday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Tuesday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\nFun Camp, Springfield Primary - Easter Half Term Week 1 (10/04/2017 to\n14/04/2017) \n\n 		DAY\n 		START\n 		END\n 		ACTIVITY\n 		TYPE\n\n 		Tuesday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Tuesday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\nFun Camp, Springfield Primary - Easter Half Term Week 2 (17/04/2017 to\n21/04/2017) \n\n 		DAY\n 		START\n 		END\n 		ACTIVITY\n 		TYPE\n\n 		Tuesday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Tuesday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\nTotal: £84.00\n\nPayments:\n £84.00 (22/04/2016 - Online) \n\nPlease check everything and if anything is incorrect, please contact\nus.','<p>Hi Jo,</p>\r\n<p>Thank you for your booking for Fun Camp, Springfield Primary  between 01/01/2017 and 31/12/2017. Please find below details of the sessions booked:</p>\r\n<p><strong>Ava Brown</strong></p><p>Fun Camp, Springfield Primary - February Half Term (13/02/2017 to 17/02/2017)</p>\n							<table width=\"100%\" border=\"1\">\n								<tr>\n									<th scope=\"col\">Day</th>\n									<th scope=\"col\">Start</th>\n									<th scope=\"col\">End</th>\n									<th scope=\"col\">Activity</th>\n									<th scope=\"col\">Type</th>\n								</tr><tr>\n							<td>Tuesday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Tuesday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr></table><p>Fun Camp, Springfield Primary - Easter Half Term Week 1 (10/04/2017 to 14/04/2017)</p>\n							<table width=\"100%\" border=\"1\">\n								<tr>\n									<th scope=\"col\">Day</th>\n									<th scope=\"col\">Start</th>\n									<th scope=\"col\">End</th>\n									<th scope=\"col\">Activity</th>\n									<th scope=\"col\">Type</th>\n								</tr><tr>\n							<td>Tuesday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Tuesday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr></table><p>Fun Camp, Springfield Primary - Easter Half Term Week 2 (17/04/2017 to 21/04/2017)</p>\n							<table width=\"100%\" border=\"1\">\n								<tr>\n									<th scope=\"col\">Day</th>\n									<th scope=\"col\">Start</th>\n									<th scope=\"col\">End</th>\n									<th scope=\"col\">Activity</th>\n									<th scope=\"col\">Type</th>\n								</tr><tr>\n							<td>Tuesday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Tuesday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr></table><p><strong>Imogen Brown</strong></p><p>Fun Camp, Springfield Primary - February Half Term (13/02/2017 to 17/02/2017)</p>\n							<table width=\"100%\" border=\"1\">\n								<tr>\n									<th scope=\"col\">Day</th>\n									<th scope=\"col\">Start</th>\n									<th scope=\"col\">End</th>\n									<th scope=\"col\">Activity</th>\n									<th scope=\"col\">Type</th>\n								</tr><tr>\n							<td>Tuesday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Tuesday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr></table><p>Fun Camp, Springfield Primary - Easter Half Term Week 1 (10/04/2017 to 14/04/2017)</p>\n							<table width=\"100%\" border=\"1\">\n								<tr>\n									<th scope=\"col\">Day</th>\n									<th scope=\"col\">Start</th>\n									<th scope=\"col\">End</th>\n									<th scope=\"col\">Activity</th>\n									<th scope=\"col\">Type</th>\n								</tr><tr>\n							<td>Tuesday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Tuesday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr></table><p>Fun Camp, Springfield Primary - Easter Half Term Week 2 (17/04/2017 to 21/04/2017)</p>\n							<table width=\"100%\" border=\"1\">\n								<tr>\n									<th scope=\"col\">Day</th>\n									<th scope=\"col\">Start</th>\n									<th scope=\"col\">End</th>\n									<th scope=\"col\">Activity</th>\n									<th scope=\"col\">Type</th>\n								</tr><tr>\n							<td>Tuesday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Tuesday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr></table><p>Total: £84.00</p><p>Payments:<br />\n£84.00 (22/04/2016 - Online)</p>\r\n<p>Please check everything and if anything is incorrect, please contact us.</p>','sent','2016-04-22 14:58:35','2016-04-22 14:58:35'),(10488,23,4266,4278,224,'email','ibraham.21@hotmail.com','Booking Confirmation for First Aid Training','Hi Ibraham, \n\nThank you for your booking for First Aid Training between 08/06/2016\nand 08/06/2016. Please find below details of the sessions booked: \n\nIBRAHAM SHARIF\n\nFirst Aid Annual Refresher Training (08/06/2016) \n\n 		DAY\n 		START\n 		END\n 		ACTIVITY\n 		TYPE\n\n 		08/06/2016 (Wednesday)\n 		09:30\n 		13:00\n 		Annual FA Refresher Training\n 		Training\n\n 		08/06/2016 (Wednesday)\n 		13:30\n 		16:30\n 		Annual FA Refresher Training\n 		Training\n\nTotal: £100.00\n\nOutstanding: £100.00 \n\nPlease check everything and if anything is incorrect, please contact\nus.','<p>Hi Ibraham,</p>\r\n<p>Thank you for your booking for First Aid Training  between 08/06/2016 and 08/06/2016. Please find below details of the sessions booked:</p>\r\n<p><strong>Ibraham Sharif</strong></p><p>First Aid Annual Refresher Training (08/06/2016)</p>\n							<table width=\"100%\" border=\"1\">\n								<tr>\n									<th scope=\"col\">Day</th>\n									<th scope=\"col\">Start</th>\n									<th scope=\"col\">End</th>\n									<th scope=\"col\">Activity</th>\n									<th scope=\"col\">Type</th>\n								</tr><tr>\n							<td>08/06/2016 (Wednesday)</td>\n							<td>09:30</td>\n							<td>13:00</td>\n							<td>Annual FA Refresher Training</td>\n							<td>Training</td>\n						</tr><tr>\n							<td>08/06/2016 (Wednesday)</td>\n							<td>13:30</td>\n							<td>16:30</td>\n							<td>Annual FA Refresher Training</td>\n							<td>Training</td>\n						</tr></table><p>Total: £100.00</p><p>Outstanding: £100.00</p>\r\n<p>Please check everything and if anything is incorrect, please contact us.</p>','sent','2016-04-22 14:59:14','2016-04-22 14:59:14'),(10657,23,4270,4282,NULL,'email','A.carrick22@hotmail.co.uk','First Aid Training Starts Soon','Hi Alan, \n\nIt\'s less than a week left before the First Aid Training starting on\n08/06/2016, please see below a reminder of your booking and account: \n\nFirst Aid Annual Refresher Training (08/06/2016) \n\n 		DAY\n 		START\n 		END\n 		ACTIVITY\n 		TYPE\n\n 		08/06/2016 (Wednesday)\n 		09:30\n 		13:00\n 		Annual FA Refresher Training\n 		Training\n\n 		08/06/2016 (Wednesday)\n 		13:30\n 		16:30\n 		Annual FA Refresher Training\n 		Training\n\nOutstanding: £100.00 \n\nIf your account is outstanding and you will be making a payment by\ncard or childcare vouchers please process your payment before the end\nof the week to ensure your child\'s place is secure. \n\nWe look forward to seeing you there.','<p>Hi Alan,</p>\r\n<p>It\'s less than a week left before the First Aid Training starting on 08/06/2016, please see below a reminder of your booking and account:</p>\r\n<p><strong> </strong></p><p>First Aid Annual Refresher Training (08/06/2016)</p>\n							<table width=\"100%\" border=\"1\">\n								<tr>\n									<th scope=\"col\">Day</th>\n									<th scope=\"col\">Start</th>\n									<th scope=\"col\">End</th>\n									<th scope=\"col\">Activity</th>\n									<th scope=\"col\">Type</th>\n								</tr><tr>\n							<td>08/06/2016 (Wednesday)</td>\n							<td>09:30</td>\n							<td>13:00</td>\n							<td>Annual FA Refresher Training</td>\n							<td>Training</td>\n						</tr><tr>\n							<td>08/06/2016 (Wednesday)</td>\n							<td>13:30</td>\n							<td>16:30</td>\n							<td>Annual FA Refresher Training</td>\n							<td>Training</td>\n						</tr></table>\r\n<p>Outstanding: &pound;100.00</p>\r\n<p>If your account is outstanding and you will be making a payment by card or childcare vouchers please process your payment before the end of the week to ensure your child\'s place is secure.</p>\r\n<p>We look forward to seeing you there.</p>','sent','2016-06-01 10:00:23','2016-06-01 10:00:23'),(10658,23,4266,4278,NULL,'email','ibraham.21@hotmail.com','First Aid Training Starts Soon','Hi Ibraham, \n\nIt\'s less than a week left before the First Aid Training starting on\n08/06/2016, please see below a reminder of your booking and account: \n\nFirst Aid Annual Refresher Training (08/06/2016) \n\n 		DAY\n 		START\n 		END\n 		ACTIVITY\n 		TYPE\n\n 		08/06/2016 (Wednesday)\n 		09:30\n 		13:00\n 		Annual FA Refresher Training\n 		Training\n\n 		08/06/2016 (Wednesday)\n 		13:30\n 		16:30\n 		Annual FA Refresher Training\n 		Training\n\nOutstanding: £100.00 \n\nIf your account is outstanding and you will be making a payment by\ncard or childcare vouchers please process your payment before the end\nof the week to ensure your child\'s place is secure. \n\nWe look forward to seeing you there.','<p>Hi Ibraham,</p>\r\n<p>It\'s less than a week left before the First Aid Training starting on 08/06/2016, please see below a reminder of your booking and account:</p>\r\n<p><strong> </strong></p><p>First Aid Annual Refresher Training (08/06/2016)</p>\n							<table width=\"100%\" border=\"1\">\n								<tr>\n									<th scope=\"col\">Day</th>\n									<th scope=\"col\">Start</th>\n									<th scope=\"col\">End</th>\n									<th scope=\"col\">Activity</th>\n									<th scope=\"col\">Type</th>\n								</tr><tr>\n							<td>08/06/2016 (Wednesday)</td>\n							<td>09:30</td>\n							<td>13:00</td>\n							<td>Annual FA Refresher Training</td>\n							<td>Training</td>\n						</tr><tr>\n							<td>08/06/2016 (Wednesday)</td>\n							<td>13:30</td>\n							<td>16:30</td>\n							<td>Annual FA Refresher Training</td>\n							<td>Training</td>\n						</tr></table>\r\n<p>Outstanding: &pound;100.00</p>\r\n<p>If your account is outstanding and you will be making a payment by card or childcare vouchers please process your payment before the end of the week to ensure your child\'s place is secure.</p>\r\n<p>We look forward to seeing you there.</p>','sent','2016-06-01 10:00:23','2016-06-01 10:00:23'),(10727,23,4270,4282,NULL,'email','A.carrick22@hotmail.co.uk','Payment Overdue','Hi Alan, \n\nOops you\'re account has an outstanding balance. If you have made a\nrecent payment please contact us so we can rectify your account.','<p>Hi Alan,</p>\r\n<p>Oops you\'re account has an outstanding balance. If you have made a recent payment please contact us so we can rectify your account.</p>','sent','2016-06-15 10:00:24','2016-06-15 10:00:24'),(10728,23,4270,4282,NULL,'SMS','447855632588',NULL,'Hi Alan, You have £100.00 outstanding for First Aid Training. Please call us to pay on 01482218753',NULL,'undelivered','2016-06-15 10:00:24','2016-06-15 10:02:02'),(10729,23,4266,4278,NULL,'email','ibraham.21@hotmail.com','Payment Overdue','Hi Ibraham, \n\nOops you\'re account has an outstanding balance. If you have made a\nrecent payment please contact us so we can rectify your account.','<p>Hi Ibraham,</p>\r\n<p>Oops you\'re account has an outstanding balance. If you have made a recent payment please contact us so we can rectify your account.</p>','sent','2016-06-15 10:00:24','2016-06-15 10:00:24'),(10730,23,4266,4278,NULL,'SMS','447459623587',NULL,'Hi Ibraham, You have £100.00 outstanding for First Aid Training. Please call us to pay on 01482218753',NULL,'undelivered','2016-06-15 10:00:24','2016-06-15 10:13:12'),(11257,23,4273,4285,NULL,'email','E.russell@hotmail.co.uk','Fun Camp, Green Road Infants School Starts Soon','Hi Ellie, \n\nIt\'s less than a week left before the Fun Camp, Green Road Infants\nSchool starting on 01/01/2017, please see below a reminder of your\nbooking and account: \n\nDECLAN RUSSELL\n\nFun Camp, Green Road Infants School, February Half Term (13/02/2017 to\n19/02/2017) \n\n 		DAY\n 		START\n 		END\n 		ACTIVITY\n 		TYPE\n\n 		Monday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Monday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\n 		Tuesday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Tuesday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\n 		Wednesday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Wednesday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\n 		Thursday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Thursday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\n 		Friday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Friday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\nFun Camp, Green Road Infants School, Easter Half Term Week 1\n(27/03/2017 to 02/04/2017) \n\n 		DAY\n 		START\n 		END\n 		ACTIVITY\n 		TYPE\n\n 		Wednesday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Wednesday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\nFun Camp, Green Road Infants School, Easter Half Term Week 2\n(10/04/2017 to 16/04/2017) \n\n 		DAY\n 		START\n 		END\n 		ACTIVITY\n 		TYPE\n\n 		Wednesday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Wednesday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\n 		Friday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Friday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\nERIN RUSSELL\n\nFun Camp, Green Road Infants School, February Half Term (13/02/2017 to\n19/02/2017) \n\n 		DAY\n 		START\n 		END\n 		ACTIVITY\n 		TYPE\n\n 		Monday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Monday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\n 		Tuesday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Tuesday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\n 		Wednesday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Wednesday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\n 		Thursday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Thursday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\n 		Friday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Friday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\nFun Camp, Green Road Infants School, Easter Half Term Week 1\n(27/03/2017 to 02/04/2017) \n\n 		DAY\n 		START\n 		END\n 		ACTIVITY\n 		TYPE\n\n 		Wednesday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Wednesday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\nFun Camp, Green Road Infants School, Easter Half Term Week 2\n(10/04/2017 to 16/04/2017) \n\n 		DAY\n 		START\n 		END\n 		ACTIVITY\n 		TYPE\n\n 		Wednesday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Wednesday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\n 		Friday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Friday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\nFREDDIE RUSSELL\n\nFun Camp, Green Road Infants School, February Half Term (13/02/2017 to\n19/02/2017) \n\n 		DAY\n 		START\n 		END\n 		ACTIVITY\n 		TYPE\n\n 		Monday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Monday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\n 		Tuesday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Tuesday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\n 		Wednesday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Wednesday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\n 		Thursday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Thursday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\n 		Friday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Friday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\nFun Camp, Green Road Infants School, Easter Half Term Week 1\n(27/03/2017 to 02/04/2017) \n\n 		DAY\n 		START\n 		END\n 		ACTIVITY\n 		TYPE\n\n 		Wednesday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Wednesday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\nFun Camp, Green Road Infants School, Easter Half Term Week 2\n(10/04/2017 to 16/04/2017) \n\n 		DAY\n 		START\n 		END\n 		ACTIVITY\n 		TYPE\n\n 		Wednesday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Wednesday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\n 		Friday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Friday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\nOutstanding: £321.00 \n\nIf your account is outstanding and you will be making a payment by\ncard or childcare vouchers please process your payment before the end\nof the week to ensure your child\'s place is secure. \n\nWe look forward to seeing you there.','<p>Hi Ellie,</p>\r\n<p>It\'s less than a week left before the Fun Camp, Green Road Infants School starting on 01/01/2017, please see below a reminder of your booking and account:</p>\r\n<p><strong>Declan Russell</strong></p><p>Fun Camp, Green Road Infants School, February Half Term (13/02/2017 to 19/02/2017)</p>\n							<table width=\"100%\" border=\"1\">\n								<tr>\n									<th scope=\"col\">Day</th>\n									<th scope=\"col\">Start</th>\n									<th scope=\"col\">End</th>\n									<th scope=\"col\">Activity</th>\n									<th scope=\"col\">Type</th>\n								</tr><tr>\n							<td>Monday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Monday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr><tr>\n							<td>Tuesday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Tuesday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr><tr>\n							<td>Wednesday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Wednesday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr><tr>\n							<td>Thursday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Thursday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr><tr>\n							<td>Friday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Friday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr></table><p>Fun Camp, Green Road Infants School, Easter Half Term Week 1 (27/03/2017 to 02/04/2017)</p>\n							<table width=\"100%\" border=\"1\">\n								<tr>\n									<th scope=\"col\">Day</th>\n									<th scope=\"col\">Start</th>\n									<th scope=\"col\">End</th>\n									<th scope=\"col\">Activity</th>\n									<th scope=\"col\">Type</th>\n								</tr><tr>\n							<td>Wednesday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Wednesday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr></table><p>Fun Camp, Green Road Infants School, Easter Half Term Week 2 (10/04/2017 to 16/04/2017)</p>\n							<table width=\"100%\" border=\"1\">\n								<tr>\n									<th scope=\"col\">Day</th>\n									<th scope=\"col\">Start</th>\n									<th scope=\"col\">End</th>\n									<th scope=\"col\">Activity</th>\n									<th scope=\"col\">Type</th>\n								</tr><tr>\n							<td>Wednesday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Wednesday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr><tr>\n							<td>Friday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Friday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr></table><p><strong>Erin Russell</strong></p><p>Fun Camp, Green Road Infants School, February Half Term (13/02/2017 to 19/02/2017)</p>\n							<table width=\"100%\" border=\"1\">\n								<tr>\n									<th scope=\"col\">Day</th>\n									<th scope=\"col\">Start</th>\n									<th scope=\"col\">End</th>\n									<th scope=\"col\">Activity</th>\n									<th scope=\"col\">Type</th>\n								</tr><tr>\n							<td>Monday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Monday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr><tr>\n							<td>Tuesday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Tuesday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr><tr>\n							<td>Wednesday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Wednesday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr><tr>\n							<td>Thursday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Thursday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr><tr>\n							<td>Friday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Friday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr></table><p>Fun Camp, Green Road Infants School, Easter Half Term Week 1 (27/03/2017 to 02/04/2017)</p>\n							<table width=\"100%\" border=\"1\">\n								<tr>\n									<th scope=\"col\">Day</th>\n									<th scope=\"col\">Start</th>\n									<th scope=\"col\">End</th>\n									<th scope=\"col\">Activity</th>\n									<th scope=\"col\">Type</th>\n								</tr><tr>\n							<td>Wednesday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Wednesday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr></table><p>Fun Camp, Green Road Infants School, Easter Half Term Week 2 (10/04/2017 to 16/04/2017)</p>\n							<table width=\"100%\" border=\"1\">\n								<tr>\n									<th scope=\"col\">Day</th>\n									<th scope=\"col\">Start</th>\n									<th scope=\"col\">End</th>\n									<th scope=\"col\">Activity</th>\n									<th scope=\"col\">Type</th>\n								</tr><tr>\n							<td>Wednesday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Wednesday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr><tr>\n							<td>Friday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Friday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr></table><p><strong>Freddie Russell</strong></p><p>Fun Camp, Green Road Infants School, February Half Term (13/02/2017 to 19/02/2017)</p>\n							<table width=\"100%\" border=\"1\">\n								<tr>\n									<th scope=\"col\">Day</th>\n									<th scope=\"col\">Start</th>\n									<th scope=\"col\">End</th>\n									<th scope=\"col\">Activity</th>\n									<th scope=\"col\">Type</th>\n								</tr><tr>\n							<td>Monday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Monday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr><tr>\n							<td>Tuesday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Tuesday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr><tr>\n							<td>Wednesday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Wednesday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr><tr>\n							<td>Thursday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Thursday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr><tr>\n							<td>Friday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Friday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr></table><p>Fun Camp, Green Road Infants School, Easter Half Term Week 1 (27/03/2017 to 02/04/2017)</p>\n							<table width=\"100%\" border=\"1\">\n								<tr>\n									<th scope=\"col\">Day</th>\n									<th scope=\"col\">Start</th>\n									<th scope=\"col\">End</th>\n									<th scope=\"col\">Activity</th>\n									<th scope=\"col\">Type</th>\n								</tr><tr>\n							<td>Wednesday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Wednesday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr></table><p>Fun Camp, Green Road Infants School, Easter Half Term Week 2 (10/04/2017 to 16/04/2017)</p>\n							<table width=\"100%\" border=\"1\">\n								<tr>\n									<th scope=\"col\">Day</th>\n									<th scope=\"col\">Start</th>\n									<th scope=\"col\">End</th>\n									<th scope=\"col\">Activity</th>\n									<th scope=\"col\">Type</th>\n								</tr><tr>\n							<td>Wednesday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Wednesday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr><tr>\n							<td>Friday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Friday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr></table>\r\n<p>Outstanding: &pound;321.00</p>\r\n<p>If your account is outstanding and you will be making a payment by card or childcare vouchers please process your payment before the end of the week to ensure your child\'s place is secure.</p>\r\n<p>We look forward to seeing you there.</p>','sent','2016-12-25 09:00:21','2016-12-25 09:00:21'),(11258,23,4270,4282,NULL,'email','A.carrick22@hotmail.co.uk','Fun Camp, Green Road Infants School Starts Soon','Hi Alan, \n\nIt\'s less than a week left before the Fun Camp, Green Road Infants\nSchool starting on 01/01/2017, please see below a reminder of your\nbooking and account: \n\nREBECCA CARRICK\n\nFun Camp, Green Road Infants School, February Half Term (13/02/2017 to\n19/02/2017) \n\n 		DAY\n 		START\n 		END\n 		ACTIVITY\n 		TYPE\n\n 		Monday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Monday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\n 		Tuesday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Tuesday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\n 		Thursday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\n 		Friday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\nFun Camp, Green Road Infants School, Easter Half Term Week 1\n(27/03/2017 to 02/04/2017) \n\n 		DAY\n 		START\n 		END\n 		ACTIVITY\n 		TYPE\n\n 		Monday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Monday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\n 		Tuesday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\n 		Wednesday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\n 		Thursday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\nFun Camp, Green Road Infants School, Easter Half Term Week 2\n(10/04/2017 to 16/04/2017) \n\n 		DAY\n 		START\n 		END\n 		ACTIVITY\n 		TYPE\n\n 		Monday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Monday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\n 		Tuesday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\n 		Wednesday\n 		08:00\n 		10:30\n 		Holiday Camps\n 		EDO\n\n 		Wednesday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\n 		Thursday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\nOutstanding: £140.00 \n\nIf your account is outstanding and you will be making a payment by\ncard or childcare vouchers please process your payment before the end\nof the week to ensure your child\'s place is secure. \n\nWe look forward to seeing you there.','<p>Hi Alan,</p>\r\n<p>It\'s less than a week left before the Fun Camp, Green Road Infants School starting on 01/01/2017, please see below a reminder of your booking and account:</p>\r\n<p><strong>Rebecca Carrick</strong></p><p>Fun Camp, Green Road Infants School, February Half Term (13/02/2017 to 19/02/2017)</p>\n							<table width=\"100%\" border=\"1\">\n								<tr>\n									<th scope=\"col\">Day</th>\n									<th scope=\"col\">Start</th>\n									<th scope=\"col\">End</th>\n									<th scope=\"col\">Activity</th>\n									<th scope=\"col\">Type</th>\n								</tr><tr>\n							<td>Monday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Monday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr><tr>\n							<td>Tuesday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Tuesday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr><tr>\n							<td>Thursday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr><tr>\n							<td>Friday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr></table><p>Fun Camp, Green Road Infants School, Easter Half Term Week 1 (27/03/2017 to 02/04/2017)</p>\n							<table width=\"100%\" border=\"1\">\n								<tr>\n									<th scope=\"col\">Day</th>\n									<th scope=\"col\">Start</th>\n									<th scope=\"col\">End</th>\n									<th scope=\"col\">Activity</th>\n									<th scope=\"col\">Type</th>\n								</tr><tr>\n							<td>Monday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Monday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr><tr>\n							<td>Tuesday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr><tr>\n							<td>Wednesday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr><tr>\n							<td>Thursday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr></table><p>Fun Camp, Green Road Infants School, Easter Half Term Week 2 (10/04/2017 to 16/04/2017)</p>\n							<table width=\"100%\" border=\"1\">\n								<tr>\n									<th scope=\"col\">Day</th>\n									<th scope=\"col\">Start</th>\n									<th scope=\"col\">End</th>\n									<th scope=\"col\">Activity</th>\n									<th scope=\"col\">Type</th>\n								</tr><tr>\n							<td>Monday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Monday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr><tr>\n							<td>Tuesday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr><tr>\n							<td>Wednesday</td>\n							<td>08:00</td>\n							<td>10:30</td>\n							<td>Holiday Camps</td>\n							<td>EDO</td>\n						</tr><tr>\n							<td>Wednesday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr><tr>\n							<td>Thursday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr></table>\r\n<p>Outstanding: &pound;140.00</p>\r\n<p>If your account is outstanding and you will be making a payment by card or childcare vouchers please process your payment before the end of the week to ensure your child\'s place is secure.</p>\r\n<p>We look forward to seeing you there.</p>','sent','2016-12-25 09:00:21','2016-12-25 09:00:21'),(11259,23,4270,4282,NULL,'email','A.carrick22@hotmail.co.uk','Fun Camp, Springfield Primary Starts Soon','Hi Alan, \n\nIt\'s less than a week left before the Fun Camp, Springfield Primary\nstarting on 01/01/2017, please see below a reminder of your booking\nand account: \n\nREBECCA CARRICK\n\nFun Camp, Springfield Primary - Easter Half Term Week 1 (10/04/2017 to\n14/04/2017) \n\n 		DAY\n 		START\n 		END\n 		ACTIVITY\n 		TYPE\n\n 		Friday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\nFun Camp, Springfield Primary - Easter Half Term Week 2 (17/04/2017 to\n21/04/2017) \n\n 		DAY\n 		START\n 		END\n 		ACTIVITY\n 		TYPE\n\n 		Monday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\n 		Tuesday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\n 		Wednesday\n 		10:30\n 		15:30\n 		Holiday Camps\n 		Holiday Camp\n\nOutstanding: £40.00 \n\nIf your account is outstanding and you will be making a payment by\ncard or childcare vouchers please process your payment before the end\nof the week to ensure your child\'s place is secure. \n\nWe look forward to seeing you there.','<p>Hi Alan,</p>\r\n<p>It\'s less than a week left before the Fun Camp, Springfield Primary starting on 01/01/2017, please see below a reminder of your booking and account:</p>\r\n<p><strong>Rebecca Carrick</strong></p><p>Fun Camp, Springfield Primary - Easter Half Term Week 1 (10/04/2017 to 14/04/2017)</p>\n							<table width=\"100%\" border=\"1\">\n								<tr>\n									<th scope=\"col\">Day</th>\n									<th scope=\"col\">Start</th>\n									<th scope=\"col\">End</th>\n									<th scope=\"col\">Activity</th>\n									<th scope=\"col\">Type</th>\n								</tr><tr>\n							<td>Friday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr></table><p>Fun Camp, Springfield Primary - Easter Half Term Week 2 (17/04/2017 to 21/04/2017)</p>\n							<table width=\"100%\" border=\"1\">\n								<tr>\n									<th scope=\"col\">Day</th>\n									<th scope=\"col\">Start</th>\n									<th scope=\"col\">End</th>\n									<th scope=\"col\">Activity</th>\n									<th scope=\"col\">Type</th>\n								</tr><tr>\n							<td>Monday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr><tr>\n							<td>Tuesday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr><tr>\n							<td>Wednesday</td>\n							<td>10:30</td>\n							<td>15:30</td>\n							<td>Holiday Camps</td>\n							<td>Holiday Camp</td>\n						</tr></table>\r\n<p>Outstanding: &pound;40.00</p>\r\n<p>If your account is outstanding and you will be making a payment by card or childcare vouchers please process your payment before the end of the week to ensure your child\'s place is secure.</p>\r\n<p>We look forward to seeing you there.</p>','sent','2016-12-25 09:00:21','2016-12-25 09:00:21'),(17284,23,4273,4285,NULL,'email','E.russell@hotmail.co.uk','Payment Overdue','Hi Ellie, \n\nOops you\'re account has an outstanding balance. If you have made a\nrecent payment please contact us so we can rectify your account.','<p>Hi Ellie,</p>\r\n<p>Oops you\'re account has an outstanding balance. If you have made a recent payment please contact us so we can rectify your account.</p>','sent','2018-01-07 09:00:21','2018-01-07 09:00:21'),(17285,23,4273,4285,NULL,'SMS','447233255412',NULL,'Hi Ellie, You have £321.00 outstanding for Fun Camp, Green Road Infants School. Please call us to pay on 0000000000',NULL,'undelivered','2018-01-07 09:00:21','2018-01-07 09:42:22'),(17286,23,4270,4282,NULL,'email','A.carrick22@hotmail.co.uk','Payment Overdue','Hi Alan, \n\nOops you\'re account has an outstanding balance. If you have made a\nrecent payment please contact us so we can rectify your account.','<p>Hi Alan,</p>\r\n<p>Oops you\'re account has an outstanding balance. If you have made a recent payment please contact us so we can rectify your account.</p>','sent','2018-01-07 09:00:21','2018-01-07 09:00:21'),(17287,23,4270,4282,NULL,'SMS','447855632588',NULL,'Hi Alan, You have £140.00 outstanding for Fun Camp, Green Road Infants School. Please call us to pay on 0000000000',NULL,'undelivered','2018-01-07 09:00:21','2018-01-07 09:42:00'),(17288,23,4270,4282,NULL,'email','A.carrick22@hotmail.co.uk','Payment Overdue','Hi Alan, \n\nOops you\'re account has an outstanding balance. If you have made a\nrecent payment please contact us so we can rectify your account.','<p>Hi Alan,</p>\r\n<p>Oops you\'re account has an outstanding balance. If you have made a recent payment please contact us so we can rectify your account.</p>','sent','2018-01-07 09:00:21','2018-01-07 09:00:21'),(17289,23,4270,4282,NULL,'SMS','447855632588',NULL,'Hi Alan, You have £40.00 outstanding for Fun Camp, Springfield Primary. Please call us to pay on 0000000000',NULL,'undelivered','2018-01-07 09:00:21','2018-01-07 09:42:21');
/*!40000 ALTER TABLE `app_family_notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_family_notifications_attachments`
--

DROP TABLE IF EXISTS `app_family_notifications_attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_family_notifications_attachments` (
  `linkID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `notificationID` int(11) NOT NULL,
  `attachmentID` int(11) DEFAULT NULL,
  PRIMARY KEY (`linkID`),
  KEY `fk_family_notifications_attachments_notificationID` (`notificationID`),
  KEY `fk_family_notifications_attachments_attachmentID` (`attachmentID`),
  KEY `fk_family_notifications_attachments_accountID` (`accountID`),
  CONSTRAINT `app_family_notifications_attachments_ibfk_1` FOREIGN KEY (`notificationID`) REFERENCES `app_family_notifications` (`notificationID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_family_notifications_attachments_ibfk_2` FOREIGN KEY (`attachmentID`) REFERENCES `app_bookings_attachments` (`attachmentID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_family_notifications_attachments_ibfk_3` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_family_notifications_attachments`
--

LOCK TABLES `app_family_notifications_attachments` WRITE;
/*!40000 ALTER TABLE `app_family_notifications_attachments` DISABLE KEYS */;
/*!40000 ALTER TABLE `app_family_notifications_attachments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_family_payments`
--

DROP TABLE IF EXISTS `app_family_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_family_payments` (
  `paymentID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `familyID` int(11) DEFAULT NULL,
  `contactID` int(11) NOT NULL,
  `recordID` int(11) NOT NULL,
  `byID` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `fee` decimal(10,2) DEFAULT NULL,
  `method` enum('card','cash','cheque','online','other','childcare voucher','direct debit') COLLATE utf8_unicode_ci NOT NULL,
  `transaction_ref` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `note` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `locked` tinyint(1) NOT NULL DEFAULT '0',
  `added` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`paymentID`),
  KEY `fk_family_payments_familyID` (`familyID`),
  KEY `fk_family_payments_byID` (`byID`),
  KEY `fk_family_payments_recordID` (`recordID`),
  KEY `fk_family_payments_contactID` (`contactID`),
  KEY `fk_family_payments_accountID` (`accountID`),
  CONSTRAINT `app_family_payments_ibfk_1` FOREIGN KEY (`familyID`) REFERENCES `app_family` (`familyID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_ibfk_2` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_ibfk_3` FOREIGN KEY (`recordID`) REFERENCES `app_bookings_individuals` (`recordID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_ibfk_4` FOREIGN KEY (`contactID`) REFERENCES `app_family_contacts` (`contactID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_ibfk_5` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8611 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_family_payments`
--

LOCK TABLES `app_family_payments` WRITE;
/*!40000 ALTER TABLE `app_family_payments` DISABLE KEYS */;
INSERT INTO `app_family_payments` VALUES (4874,23,4271,4283,12080,224,84.00,NULL,'cash','','Paid',0,'2016-04-22 13:00:28','2016-04-22 13:00:28'),(4875,23,4267,4279,12081,224,100.00,NULL,'cheque','','Paid',0,'2016-04-22 13:20:26','2016-04-22 13:20:26'),(4876,23,4271,4283,12085,224,84.00,NULL,'online','','Paid',0,'2016-04-22 14:58:35','2016-04-22 14:58:35');
/*!40000 ALTER TABLE `app_family_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_family_payments_plans`
--

DROP TABLE IF EXISTS `app_family_payments_plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_family_payments_plans` (
  `planID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL,
  `familyID` int(11) NOT NULL,
  `contactID` int(11) NOT NULL,
  `recordID` int(11) NOT NULL,
  `byID` int(11) DEFAULT NULL,
  `amount` decimal(8,2) NOT NULL,
  `interval_count` int(11) NOT NULL,
  `interval_length` int(11) NOT NULL,
  `interval_unit` enum('day','week','month') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'month',
  `status` enum('inactive','active','cancelled','completed') COLLATE utf8_unicode_ci DEFAULT 'inactive',
  `gc_subscription_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `gc_code` varchar(6) COLLATE utf8_unicode_ci DEFAULT NULL,
  `note` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `added` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  `authorised` datetime DEFAULT NULL,
  PRIMARY KEY (`planID`),
  UNIQUE KEY `gc_code` (`gc_code`),
  KEY `accountID` (`accountID`),
  KEY `familyID` (`familyID`),
  KEY `contactID` (`contactID`),
  KEY `recordID` (`recordID`),
  KEY `byID` (`byID`),
  CONSTRAINT `app_family_payments_plans_ibfk_1` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_10` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_11` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_12` FOREIGN KEY (`familyID`) REFERENCES `app_family` (`familyID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_13` FOREIGN KEY (`contactID`) REFERENCES `app_family_contacts` (`contactID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_14` FOREIGN KEY (`recordID`) REFERENCES `app_bookings_individuals` (`recordID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_15` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_16` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_17` FOREIGN KEY (`familyID`) REFERENCES `app_family` (`familyID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_18` FOREIGN KEY (`contactID`) REFERENCES `app_family_contacts` (`contactID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_19` FOREIGN KEY (`recordID`) REFERENCES `app_bookings_individuals` (`recordID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_2` FOREIGN KEY (`familyID`) REFERENCES `app_family` (`familyID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_20` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_21` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_22` FOREIGN KEY (`familyID`) REFERENCES `app_family` (`familyID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_23` FOREIGN KEY (`contactID`) REFERENCES `app_family_contacts` (`contactID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_24` FOREIGN KEY (`recordID`) REFERENCES `app_bookings_individuals` (`recordID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_25` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_26` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_27` FOREIGN KEY (`familyID`) REFERENCES `app_family` (`familyID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_28` FOREIGN KEY (`contactID`) REFERENCES `app_family_contacts` (`contactID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_29` FOREIGN KEY (`recordID`) REFERENCES `app_bookings_individuals` (`recordID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_3` FOREIGN KEY (`contactID`) REFERENCES `app_family_contacts` (`contactID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_30` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_31` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_32` FOREIGN KEY (`familyID`) REFERENCES `app_family` (`familyID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_33` FOREIGN KEY (`contactID`) REFERENCES `app_family_contacts` (`contactID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_34` FOREIGN KEY (`recordID`) REFERENCES `app_bookings_individuals` (`recordID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_35` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_36` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_37` FOREIGN KEY (`familyID`) REFERENCES `app_family` (`familyID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_38` FOREIGN KEY (`contactID`) REFERENCES `app_family_contacts` (`contactID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_39` FOREIGN KEY (`recordID`) REFERENCES `app_bookings_individuals` (`recordID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_4` FOREIGN KEY (`recordID`) REFERENCES `app_bookings_individuals` (`recordID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_40` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_41` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_42` FOREIGN KEY (`familyID`) REFERENCES `app_family` (`familyID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_43` FOREIGN KEY (`contactID`) REFERENCES `app_family_contacts` (`contactID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_44` FOREIGN KEY (`recordID`) REFERENCES `app_bookings_individuals` (`recordID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_45` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_46` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_47` FOREIGN KEY (`familyID`) REFERENCES `app_family` (`familyID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_48` FOREIGN KEY (`contactID`) REFERENCES `app_family_contacts` (`contactID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_49` FOREIGN KEY (`recordID`) REFERENCES `app_bookings_individuals` (`recordID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_5` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_50` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_51` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_52` FOREIGN KEY (`familyID`) REFERENCES `app_family` (`familyID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_53` FOREIGN KEY (`contactID`) REFERENCES `app_family_contacts` (`contactID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_54` FOREIGN KEY (`recordID`) REFERENCES `app_bookings_individuals` (`recordID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_55` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_56` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_57` FOREIGN KEY (`familyID`) REFERENCES `app_family` (`familyID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_58` FOREIGN KEY (`contactID`) REFERENCES `app_family_contacts` (`contactID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_59` FOREIGN KEY (`recordID`) REFERENCES `app_bookings_individuals` (`recordID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_6` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_60` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_7` FOREIGN KEY (`familyID`) REFERENCES `app_family` (`familyID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_8` FOREIGN KEY (`contactID`) REFERENCES `app_family_contacts` (`contactID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_family_payments_plans_ibfk_9` FOREIGN KEY (`recordID`) REFERENCES `app_bookings_individuals` (`recordID`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_family_payments_plans`
--

LOCK TABLES `app_family_payments_plans` WRITE;
/*!40000 ALTER TABLE `app_family_payments_plans` DISABLE KEYS */;
/*!40000 ALTER TABLE `app_family_payments_plans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_files`
--

DROP TABLE IF EXISTS `app_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_files` (
  `attachmentID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `staffID` int(11) DEFAULT NULL,
  `byID` int(11) DEFAULT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `category` enum('misc','plans','school','camp','policies','staff','office') COLLATE utf8_unicode_ci NOT NULL,
  `path` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `ext` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `size` bigint(100) NOT NULL,
  `comment` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `added` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`attachmentID`),
  KEY `fk_files_accountID` (`accountID`),
  KEY `fk_files_staffID` (`staffID`),
  KEY `fk_files_byID` (`byID`),
  CONSTRAINT `app_files_ibfk_1` FOREIGN KEY (`staffID`) REFERENCES `app_staff` (`staffID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_files_ibfk_2` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `app_files_ibfk_3` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4802 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_files`
--

LOCK TABLES `app_files` WRITE;
/*!40000 ALTER TABLE `app_files` DISABLE KEYS */;
INSERT INTO `app_files` VALUES (3220,23,NULL,224,'School Induction Template.docx','school','mZfsdeLGzvkTHUyC1o6KRwjtJ42WSPx3','application/vnd.openxmlformats-officedocument.wordprocessingml.document','docx',11377,NULL,'2016-04-22 14:22:03','2016-04-22 14:22:03'),(3221,23,NULL,224,'Company Handbook.docx','policies','ow1QvJWRnChuTbDLZt9am5xfXjINU8p7','application/vnd.openxmlformats-officedocument.wordprocessingml.document','docx',11377,NULL,'2016-04-22 14:22:20','2016-04-22 14:22:20'),(3222,23,NULL,224,'Equal Oppotunites Policy.docx','policies','8AqnU2mVCGhBj6FZpOWQycx1e9iNY34E','application/vnd.openxmlformats-officedocument.wordprocessingml.document','docx',11346,NULL,'2016-04-22 14:22:32','2016-04-22 14:22:32'),(3223,23,NULL,224,'E & D Policy.docx','policies','dXeqJil7KIupLvPg69FmwCnZSA01GW3x','application/vnd.openxmlformats-officedocument.wordprocessingml.document','docx',11387,NULL,'2016-04-22 14:22:44','2016-04-22 14:22:44'),(3224,23,NULL,224,'Data Protection Policy.docx','policies','1Ed9pORGraFxl4nzeB75MXfQT2hciIKq','application/vnd.openxmlformats-officedocument.wordprocessingml.document','docx',11336,NULL,'2016-04-22 14:22:56','2016-04-22 14:22:56'),(3225,23,NULL,224,'Safeguarding Policy.docx','policies','zN9uKO34woxC8gq5nm7AVSiIXBsyFjDd','application/vnd.openxmlformats-officedocument.wordprocessingml.document','docx',11377,NULL,'2016-04-22 14:23:13','2017-09-28 14:47:10'),(3226,23,NULL,224,'Appraisal Template.docx','office','vizft2sek3lZhymorQXRd1YGwHVNnDK4','application/vnd.openxmlformats-officedocument.wordprocessingml.document','docx',11377,NULL,'2016-04-22 14:23:58','2016-04-22 14:24:25'),(3227,23,NULL,224,'Contract Template.docx','office','2OEGTrZUkR1AKepljFb9xQzu5mCHnVso','application/vnd.openxmlformats-officedocument.wordprocessingml.document','docx',11377,NULL,'2016-04-22 14:24:06','2016-04-22 14:24:06'),(3228,23,NULL,224,'Rugby plan 1-6.docx','plans','zTa80DSUtZ1f3HXA5p9osqEPwrBMkVd6','application/vnd.openxmlformats-officedocument.wordprocessingml.document','docx',11366,NULL,'2016-04-22 14:24:39','2016-04-22 14:24:39'),(3229,23,NULL,224,'Gymnastics plan 1-6.docx','plans','5RHEuSTyUKGmWZ34nFlt7zQ9jPpsvhNI','application/vnd.openxmlformats-officedocument.wordprocessingml.document','docx',11377,NULL,'2016-04-22 14:24:48','2016-04-22 14:24:48'),(3230,23,NULL,224,'Risk Assessment.docx','school','KECbiY5sg1387VJcweZjFt6x4MAIDLrX','application/vnd.openxmlformats-officedocument.wordprocessingml.document','docx',11366,NULL,'2016-04-22 14:24:58','2016-04-22 14:24:58'),(3231,23,NULL,224,'Camp Risk Assessment.docx','camp','fB9YZMkKtDHgUSvjblV81yNOprWns7XP','application/vnd.openxmlformats-officedocument.wordprocessingml.document','docx',11366,NULL,'2016-04-22 14:25:21','2016-04-22 14:25:21'),(3232,23,NULL,224,'Holiday Request Template.docx','staff','DjtTbLN4pcEFWZdvh87CaqBsilSmzwxP','application/vnd.openxmlformats-officedocument.wordprocessingml.document','docx',11356,NULL,'2016-04-22 14:26:31','2016-04-22 14:26:31');
/*!40000 ALTER TABLE `app_files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_files_brands`
--

DROP TABLE IF EXISTS `app_files_brands`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_files_brands` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `attachmentID` int(11) DEFAULT NULL,
  `brandID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_files_brands_attachmentID` (`attachmentID`),
  KEY `fk_files_brands_brandID` (`brandID`),
  KEY `fk_files_brands_accountID` (`accountID`),
  CONSTRAINT `app_files_brands_ibfk_1` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_files_brands_ibfk_2` FOREIGN KEY (`attachmentID`) REFERENCES `app_files` (`attachmentID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_files_brands_ibfk_3` FOREIGN KEY (`brandID`) REFERENCES `app_brands` (`brandID`)
) ENGINE=InnoDB AUTO_INCREMENT=75 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_files_brands`
--

LOCK TABLES `app_files_brands` WRITE;
/*!40000 ALTER TABLE `app_files_brands` DISABLE KEYS */;
/*!40000 ALTER TABLE `app_files_brands` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_lesson_types`
--

DROP TABLE IF EXISTS `app_lesson_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_lesson_types` (
  `typeID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL,
  `byID` int(11) DEFAULT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `show_dashboard` tinyint(1) NOT NULL DEFAULT '0',
  `exclude_autodiscount` tinyint(1) NOT NULL DEFAULT '0',
  `show_label_register` tinyint(1) NOT NULL DEFAULT '0',
  `birthday_tab` tinyint(1) NOT NULL DEFAULT '0',
  `session_evaluations` tinyint(1) NOT NULL DEFAULT '0',
  `extra_time_head` int(3) NOT NULL DEFAULT '0',
  `extra_time_lead` int(3) NOT NULL DEFAULT '0',
  `extra_time_assistant` int(3) NOT NULL DEFAULT '0',
  `added` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`typeID`),
  KEY `accountID` (`accountID`),
  KEY `byID` (`byID`),
  CONSTRAINT `app_lesson_types_ibfk_1` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_lesson_types_ibfk_2` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1587 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_lesson_types`
--

LOCK TABLES `app_lesson_types` WRITE;
/*!40000 ALTER TABLE `app_lesson_types` DISABLE KEYS */;
INSERT INTO `app_lesson_types` VALUES (1,10,NULL,'PPA',0,0,0,0,0,0,0,0,'2016-04-28 10:01:26','2016-04-28 10:01:26'),(2,10,NULL,'PE Development',0,0,0,0,0,0,0,0,'2016-04-28 10:01:26','2016-04-28 10:01:26'),(3,10,NULL,'Extra Curricular',0,0,0,0,0,0,0,0,'2016-04-28 10:01:26','2016-04-28 10:01:26'),(4,10,NULL,'Academy',0,0,0,0,0,0,0,0,'2016-04-28 10:01:26','2016-04-28 10:01:26'),(5,10,NULL,'Holiday Camp',0,0,0,0,0,0,0,0,'2016-04-28 10:01:26','2016-04-28 10:01:26'),(6,10,NULL,'EDO',0,1,1,0,0,0,0,0,'2016-04-28 10:01:26','2016-04-28 10:01:26'),(7,10,NULL,'LPU',0,1,1,0,0,0,0,0,'2016-04-28 10:01:26','2016-04-28 10:01:26'),(8,10,NULL,'Staff Event',1,0,0,0,0,0,0,0,'2016-04-28 10:01:26','2016-04-28 10:01:26'),(9,10,NULL,'Project',0,0,0,0,0,0,0,0,'2016-04-28 10:01:26','2016-04-28 10:01:26'),(10,10,NULL,'Bikeability',0,0,0,0,0,0,0,0,'2016-04-28 10:01:26','2016-04-28 10:01:26'),(11,10,NULL,'One Off',0,0,0,0,0,0,0,0,'2016-04-28 10:01:26','2016-04-28 10:01:26'),(12,10,NULL,'Training',0,0,0,0,0,0,0,0,'2016-04-28 10:01:26','2016-04-28 10:01:26'),(13,10,NULL,'Enrichment',0,0,0,0,0,0,0,0,'2016-04-28 10:01:26','2016-04-28 10:01:26'),(14,10,NULL,'Birthday',0,0,0,1,0,0,0,0,'2016-04-28 10:01:26','2016-04-28 10:01:26'),(99,23,NULL,'PPA',0,0,0,0,0,0,0,0,'2016-04-28 10:01:27','2016-04-28 10:01:27'),(100,23,NULL,'PE Development',0,0,0,0,0,0,0,0,'2016-04-28 10:01:27','2016-04-28 10:01:27'),(101,23,NULL,'Extra Curricular',0,0,0,0,0,0,0,0,'2016-04-28 10:01:27','2016-04-28 10:01:27'),(102,23,NULL,'Academy',0,0,0,0,0,0,0,0,'2016-04-28 10:01:27','2016-04-28 10:01:27'),(103,23,NULL,'Holiday Camp',0,0,0,0,0,0,0,0,'2016-04-28 10:01:27','2016-04-28 10:01:27'),(104,23,NULL,'EDO',0,1,1,0,0,0,0,0,'2016-04-28 10:01:27','2016-04-28 10:01:27'),(105,23,NULL,'LPU',0,1,1,0,0,0,0,0,'2016-04-28 10:01:27','2016-04-28 10:01:27'),(106,23,NULL,'Staff Event',1,0,0,0,0,0,0,0,'2016-04-28 10:01:27','2016-04-28 10:01:27'),(107,23,NULL,'Project',0,0,0,0,0,0,0,0,'2016-04-28 10:01:27','2016-04-28 10:01:27'),(108,23,NULL,'Bikeability',0,0,0,0,0,0,0,0,'2016-04-28 10:01:27','2016-04-28 10:01:27'),(109,23,NULL,'One Off',0,0,0,0,0,0,0,0,'2016-04-28 10:01:27','2016-04-28 10:01:27'),(110,23,NULL,'Training',0,0,0,0,0,0,0,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(111,23,NULL,'Enrichment',0,0,0,0,0,0,0,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(112,23,NULL,'Birthday',0,0,0,1,0,0,0,0,'2016-04-28 10:01:28','2016-04-28 10:01:28');
/*!40000 ALTER TABLE `app_lesson_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_mandatory_quals`
--

DROP TABLE IF EXISTS `app_mandatory_quals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_mandatory_quals` (
  `qualID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL,
  `byID` int(11) DEFAULT NULL,
  `imported` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `added` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`qualID`),
  KEY `accountID` (`accountID`),
  KEY `byID` (`byID`),
  CONSTRAINT `fk_mandatory_quals_accountID` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_mandatory_quals_byID` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=201 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_mandatory_quals`
--

LOCK TABLES `app_mandatory_quals` WRITE;
/*!40000 ALTER TABLE `app_mandatory_quals` DISABLE KEYS */;
INSERT INTO `app_mandatory_quals` VALUES (1,10,NULL,0,'Level 1 Coaching','2017-03-31 12:01:19','2017-03-31 12:01:19'),(2,10,NULL,0,'Level 2 Coaching','2017-03-31 12:01:19','2017-03-31 12:01:19'),(9,23,NULL,0,'Level 1 Coaching','2017-03-31 12:01:19','2017-03-31 12:01:19'),(10,23,NULL,0,'Level 2 Coaching','2017-03-31 12:01:19','2017-03-31 12:01:19');
/*!40000 ALTER TABLE `app_mandatory_quals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_messages`
--

DROP TABLE IF EXISTS `app_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_messages` (
  `messageID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `byID` int(11) DEFAULT NULL,
  `forID` int(11) NOT NULL,
  `folder` enum('inbox','sent') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'inbox',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `replyTo` int(11) DEFAULT NULL,
  `subject` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `message` text COLLATE utf8_unicode_ci NOT NULL,
  `added` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`messageID`),
  KEY `fk_messages_byID` (`byID`),
  KEY `fk_messages_forID` (`forID`),
  KEY `fk_messages_accountID` (`accountID`),
  CONSTRAINT `app_messages_ibfk_1` FOREIGN KEY (`forID`) REFERENCES `app_staff` (`staffID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_messages_ibfk_2` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `app_messages_ibfk_3` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16279 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_messages`
--

LOCK TABLES `app_messages` WRITE;
/*!40000 ALTER TABLE `app_messages` DISABLE KEYS */;
INSERT INTO `app_messages` VALUES (8726,23,234,234,'inbox',0,NULL,'badminton net','<p>Hi Molly</p>\r\n<p>Can you order a new badminton net&nbsp;</p>','2016-04-27 10:54:22','2016-04-27 10:54:22'),(8727,23,234,234,'sent',1,NULL,'badminton net','<p>Hi Molly</p>\r\n<p>Can you order a new badminton net&nbsp;</p>','2016-04-27 10:54:22','2016-04-27 10:54:22'),(8729,23,234,225,'sent',1,NULL,'Read your documents','<p>Hi Ben,</p>\r\n<p>You have some outstanding documents that require your attention.</p>\r\n<p>&nbsp;</p>\r\n<p>Please read them before your next coaching session.</p>\r\n<p>&nbsp;</p>\r\n<p>Molly.&nbsp;</p>','2016-04-27 11:19:58','2016-04-27 11:19:58'),(8730,23,234,225,'inbox',1,NULL,'Unread Safety Documents','<p>Hi Ben</p>\r\n<p>I can see you have some unread safety documents.</p>\r\n<p>Can you make sure you have read these and marked that you have on the system before your next coaching session please?</p>\r\n<p>Many thanks,</p>\r\n<p>Molly</p>','2016-04-27 16:05:35','2016-04-27 16:05:35'),(8731,23,234,225,'sent',1,NULL,'Unread Safety Documents','<p>Hi Ben</p>\r\n<p>I can see you have some unread safety documents.</p>\r\n<p>Can you make sure you have read these and marked that you have on the system before your next coaching session please?</p>\r\n<p>Many thanks,</p>\r\n<p>Molly</p>','2016-04-27 16:05:35','2016-04-27 16:05:35'),(8732,23,234,225,'inbox',1,NULL,'Change of Location','<p>Hi Ben</p>\r\n<p>Your 4pm session today has changed from the Sports Hall to the gym. I have updated your schedule.</p>\r\n<p>Please confirm receipt of this message.</p>\r\n<p>Many thanks,</p>\r\n<p>Molly</p>','2016-05-03 13:10:30','2016-05-03 13:10:30'),(8733,23,234,225,'sent',1,NULL,'Change of Location','<p>Hi Ben</p>\r\n<p>Your 4pm session today has changed from the Sports Hall to the gym. I have updated your schedule.</p>\r\n<p>Please confirm receipt of this message.</p>\r\n<p>Many thanks,</p>\r\n<p>Molly</p>','2016-05-03 13:10:30','2016-05-03 13:10:30'),(8735,23,235,225,'sent',1,NULL,'Change of Location','<div class=\"form-group\">Hi Ben</div>\r\n<div class=\"form-group\">\r\n<p>Your 4pm session today has changed from the Sports Hall to the gym. I have updated your schedule.</p>\r\n<p>Please confirm receipt of this message.</p>\r\n<p>Many thanks,</p>\r\n<p>Molly</p>\r\n<p>&nbsp;</p>\r\n</div>','2016-05-17 15:20:11','2016-05-17 15:20:11'),(8943,23,225,234,'inbox',0,NULL,'Test','<p>Test</p>','2016-06-07 12:15:26','2016-06-07 12:15:26'),(8944,23,225,234,'sent',1,NULL,'Test','<p>Test</p>','2016-06-07 12:15:26','2016-06-07 12:15:26'),(8945,23,225,234,'inbox',0,NULL,'Test','<p>Test</p>','2016-06-07 12:17:41','2016-06-07 12:17:41'),(8946,23,225,234,'sent',1,NULL,'Test','<p>Test</p>','2016-06-07 12:17:41','2016-06-07 12:17:41'),(8947,23,225,224,'inbox',0,NULL,'Test','<p>Test</p>','2016-06-07 12:17:41','2016-06-07 12:17:41'),(8948,23,225,224,'sent',1,NULL,'Test','<p>Test</p>','2016-06-07 12:17:41','2016-06-07 12:17:41'),(8949,23,235,234,'inbox',0,NULL,'Problem at school','<p>Hi Molly</p>','2016-06-07 12:19:19','2016-06-07 12:19:19'),(8950,23,235,234,'sent',1,NULL,'Problem at school','<p>Hi Molly</p>','2016-06-07 12:19:19','2016-06-07 12:19:19'),(8951,23,235,225,'inbox',1,NULL,'Change of Location','<div class=\"form-group\">Hi Ben</div>\r\n<div class=\"form-group\">\r\n<p>Your 4pm session today has changed from the Sports Hall to the gym. I have updated your schedule.</p>\r\n<p>Please confirm receipt of this message.</p>\r\n<p>Many thanks,</p>\r\n<p>Molly</p>\r\n</div>','2016-06-10 11:30:28','2016-06-10 11:30:28'),(8952,23,235,225,'sent',1,NULL,'Change of Location','<div class=\"form-group\">Hi Ben</div>\r\n<div class=\"form-group\">\r\n<p>Your 4pm session today has changed from the Sports Hall to the gym. I have updated your schedule.</p>\r\n<p>Please confirm receipt of this message.</p>\r\n<p>Many thanks,</p>\r\n<p>Molly</p>\r\n</div>','2016-06-10 11:30:28','2016-06-10 11:30:28');
/*!40000 ALTER TABLE `app_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_messages_attachments`
--

DROP TABLE IF EXISTS `app_messages_attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_messages_attachments` (
  `attachmentID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `messageID` int(11) DEFAULT NULL,
  `byID` int(11) DEFAULT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `path` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `ext` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `size` bigint(20) NOT NULL,
  `added` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`attachmentID`),
  KEY `fk_orgs_attachments_messageID` (`messageID`),
  KEY `fk_orgs_attachments_byID` (`byID`),
  KEY `fk_messages_attachments_accountID` (`accountID`),
  CONSTRAINT `app_messages_attachments_ibfk_1` FOREIGN KEY (`messageID`) REFERENCES `app_messages` (`messageID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_messages_attachments_ibfk_2` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `app_messages_attachments_ibfk_3` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1325 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_messages_attachments`
--

LOCK TABLES `app_messages_attachments` WRITE;
/*!40000 ALTER TABLE `app_messages_attachments` DISABLE KEYS */;
/*!40000 ALTER TABLE `app_messages_attachments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_migrations`
--

DROP TABLE IF EXISTS `app_migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_migrations` (
  `version` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_migrations`
--

LOCK TABLES `app_migrations` WRITE;
/*!40000 ALTER TABLE `app_migrations` DISABLE KEYS */;
INSERT INTO `app_migrations` VALUES (20180404131000);
/*!40000 ALTER TABLE `app_migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_offer_accept`
--

DROP TABLE IF EXISTS `app_offer_accept`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_offer_accept` (
  `offerID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL,
  `lessonID` int(11) NOT NULL,
  `staffID` int(11) NOT NULL,
  `type` enum('head','lead','assistant') COLLATE utf8_unicode_ci NOT NULL,
  `status` enum('offered','accepted','declined','expired') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'offered',
  `reason` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `added` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`offerID`),
  KEY `accountID` (`accountID`),
  KEY `lessonID` (`lessonID`),
  KEY `staffID` (`staffID`),
  CONSTRAINT `fk_offer_accept_accountID` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_offer_accept_lessonID` FOREIGN KEY (`lessonID`) REFERENCES `app_bookings_lessons` (`lessonID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_offer_accept_staffID` FOREIGN KEY (`staffID`) REFERENCES `app_staff` (`staffID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_offer_accept`
--

LOCK TABLES `app_offer_accept` WRITE;
/*!40000 ALTER TABLE `app_offer_accept` DISABLE KEYS */;
/*!40000 ALTER TABLE `app_offer_accept` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_offer_accept_groups`
--

DROP TABLE IF EXISTS `app_offer_accept_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_offer_accept_groups` (
  `groupID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL,
  `byID` int(11) DEFAULT NULL,
  `added` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`groupID`),
  KEY `accountID` (`accountID`),
  KEY `byID` (`byID`),
  CONSTRAINT `fk_offer_accept_groups_accountID` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_offer_accept_groups_byID` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_offer_accept_groups`
--

LOCK TABLES `app_offer_accept_groups` WRITE;
/*!40000 ALTER TABLE `app_offer_accept_groups` DISABLE KEYS */;
/*!40000 ALTER TABLE `app_offer_accept_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_orgs`
--

DROP TABLE IF EXISTS `app_orgs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_orgs` (
  `orgID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `byID` int(11) DEFAULT NULL,
  `imported` tinyint(1) NOT NULL DEFAULT '0',
  `prospect` tinyint(1) DEFAULT NULL,
  `partnership` tinyint(1) DEFAULT NULL,
  `clusterID` int(11) DEFAULT NULL,
  `regionID` int(11) DEFAULT NULL,
  `areaID` int(11) DEFAULT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `invoiceFrequency` enum('weekly','monthly','half termly','termly','annually') COLLATE utf8_unicode_ci DEFAULT NULL,
  `newsletter` tinyint(1) NOT NULL DEFAULT '0',
  `type` enum('school','organisation') COLLATE utf8_unicode_ci DEFAULT NULL,
  `schoolType` enum('primary','infant','junior','secondary','other','college','special') COLLATE utf8_unicode_ci DEFAULT NULL,
  `isPrivate` tinyint(4) NOT NULL DEFAULT '0',
  `email` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `website` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `rate` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `staffing_notes` text COLLATE utf8_unicode_ci,
  `added` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`orgID`),
  KEY `fk_orgs_byID` (`byID`),
  KEY `fk_orgs_clusterID` (`clusterID`),
  KEY `fk_orgs_regionID` (`regionID`),
  KEY `fk_orgs_areaID` (`areaID`),
  KEY `fk_orgs_accountID` (`accountID`),
  CONSTRAINT `app_orgs_ibfk_1` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `app_orgs_ibfk_2` FOREIGN KEY (`clusterID`) REFERENCES `app_orgs_clusters` (`clusterID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_orgs_ibfk_3` FOREIGN KEY (`regionID`) REFERENCES `app_settings_regions` (`regionID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `app_orgs_ibfk_4` FOREIGN KEY (`areaID`) REFERENCES `app_settings_areas` (`areaID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `app_orgs_ibfk_5` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5028 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_orgs`
--

LOCK TABLES `app_orgs` WRITE;
/*!40000 ALTER TABLE `app_orgs` DISABLE KEYS */;
INSERT INTO `app_orgs` VALUES (1862,23,224,0,0,NULL,NULL,6,11,'Highland Park Primary','weekly',0,'school','primary',0,'admin@highland.park.co.uk','www.highlandparkprimary.com','',NULL,'2016-04-14 13:16:24','2016-04-22 13:12:40'),(1863,23,224,0,1,NULL,NULL,6,11,'Springfield Primary','annually',0,'school','primary',0,'admin@springfieldprimary.co.uk','www.springfieldprimary.com','60.00',NULL,'2016-04-15 08:09:44','2016-04-22 13:13:48'),(1864,23,224,0,1,NULL,NULL,6,11,'Green Road Infant School','annually',0,'school','infant',0,'admin@greenroad.co.uk','www.greenroadinfantschool.com','60.00',NULL,'2016-04-15 08:16:32','2016-04-22 13:12:09'),(1865,23,224,0,1,NULL,NULL,NULL,NULL,'Yellow Hill Primary School','weekly',0,'school','primary',0,'admin@yellowhillprimary.co.uk','www.yellowhillprimary.co.uk','60.00',NULL,'2016-04-15 08:25:14','2016-04-22 13:14:49'),(1866,23,224,0,1,NULL,NULL,6,11,'North Primary School','annually',0,'school','primary',0,'admin@north.co.uk','www.northprimaryschool.co.uk','60.00',NULL,'2016-04-15 09:27:42','2016-04-22 13:13:23'),(1867,23,224,0,1,NULL,NULL,NULL,NULL,'Kids Charity UK',NULL,0,'organisation',NULL,0,'kidscharityuk@hotmail.co.uk','www.kidscharityuk.com','',NULL,'2016-04-18 12:11:47','2016-04-18 12:11:47'),(1868,23,224,0,1,NULL,NULL,NULL,NULL,'Raise Awareness',NULL,0,'organisation',NULL,0,'info@raiseawareness.com','www.raiseawareness.com','',NULL,'2016-04-18 12:57:49','2016-04-18 12:57:49'),(1869,23,224,0,1,NULL,NULL,NULL,NULL,'Westminter City Council',NULL,0,'organisation',NULL,0,'admin@hullcity.com','','',NULL,'2016-04-20 13:28:49','2016-04-22 12:55:39'),(1870,23,224,0,1,NULL,NULL,6,11,'Warner Park Primary Schoool','termly',0,'school','primary',0,'admin@warnerpark.co.uk','www.Warner Park Primary Schoool.co.uk','',NULL,'2016-04-22 12:47:57','2016-04-22 13:14:31');
/*!40000 ALTER TABLE `app_orgs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_orgs_addresses`
--

DROP TABLE IF EXISTS `app_orgs_addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_orgs_addresses` (
  `addressID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `orgID` int(11) NOT NULL,
  `byID` int(11) DEFAULT NULL,
  `imported` tinyint(1) NOT NULL DEFAULT '0',
  `type` enum('main','delivery','billing','other') COLLATE utf8_unicode_ci DEFAULT NULL,
  `address1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address3` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `town` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `county` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `postcode` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `added` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`addressID`),
  KEY `fk_orgs_address_orgID` (`orgID`),
  KEY `fk_orgs_address_byID` (`byID`),
  KEY `fk_orgs_addresses_accountID` (`accountID`),
  CONSTRAINT `app_orgs_addresses_ibfk_1` FOREIGN KEY (`orgID`) REFERENCES `app_orgs` (`orgID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_orgs_addresses_ibfk_2` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `app_orgs_addresses_ibfk_3` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6105 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_orgs_addresses`
--

LOCK TABLES `app_orgs_addresses` WRITE;
/*!40000 ALTER TABLE `app_orgs_addresses` DISABLE KEYS */;
INSERT INTO `app_orgs_addresses` VALUES (2325,23,1862,224,0,'main','Highland Avenue','','','Hull','East Yorkshire','HU9 5HE','01482 976153','2016-04-14 13:16:24','2016-04-15 08:35:16'),(2326,23,1863,224,0,'main','Springfield Avenue','','','Brough','East Yorkshire','HU1 3RQ','01482 562248','2016-04-15 08:09:44','2016-04-15 09:23:30'),(2327,23,1864,224,0,'main','Green Road','','','Leeds','West Yorkshire','HU3 6HU','01482 321123','2016-04-15 08:16:32','2016-04-15 08:32:16'),(2328,23,1865,224,0,'main','Garton On The Wolds','','','London','East Yorkshire','SW1A 1AA','01482 789987','2016-04-15 08:25:14','2016-04-15 08:25:14'),(2329,23,1866,224,0,'main','North Road','','','Hull','East Yorkshire','HU1 4DP','01482 456456','2016-04-15 09:27:42','2016-04-15 09:27:42'),(2330,23,1867,224,0,'main','21 Sample Avenue','','','Hull','East Yorkshire','HU4 6AQ','01482 566556','2016-04-18 12:11:47','2016-04-18 12:11:47'),(2331,23,1868,224,0,'main','53 Sample Road','','','Hull','East Yorkshire','HU5 5JY','01482 222222','2016-04-18 12:57:49','2016-04-18 12:57:49'),(2332,23,1869,224,0,'main','Buckingham Primary','Buckingham Street','','Hull','East Riding of Yorkshire','HU8 8UG','+441482841907','2016-04-20 13:28:49','2016-04-20 13:28:49'),(2333,23,1869,224,0,'delivery','North Road','','','Hull','East Riding of Yorkshire','HU1 4DP','01482 456456','2016-04-20 13:41:59','2016-04-20 13:42:11'),(2334,23,1870,224,0,'main','Brantingham Park','','','Brantingham','North Humberside','HU15 1HX','01482 852852','2016-04-22 12:47:57','2016-04-22 12:47:57');
/*!40000 ALTER TABLE `app_orgs_addresses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_orgs_attachments`
--

DROP TABLE IF EXISTS `app_orgs_attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_orgs_attachments` (
  `attachmentID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `orgID` int(11) DEFAULT NULL,
  `addressID` int(11) DEFAULT NULL,
  `byID` int(11) DEFAULT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `path` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `ext` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `size` bigint(20) NOT NULL,
  `comment` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `coachaccess` tinyint(1) DEFAULT NULL,
  `sendwithconfirmation` tinyint(1) NOT NULL DEFAULT '0',
  `added` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`attachmentID`),
  KEY `fk_orgs_attachments_orgID` (`orgID`),
  KEY `fk_orgs_attachments_byID` (`byID`),
  KEY `fk_orgs_attachments_addressID` (`addressID`),
  KEY `fk_orgs_attachments_accountID` (`accountID`),
  CONSTRAINT `app_orgs_attachments_ibfk_1` FOREIGN KEY (`orgID`) REFERENCES `app_orgs` (`orgID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_orgs_attachments_ibfk_2` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `app_orgs_attachments_ibfk_3` FOREIGN KEY (`addressID`) REFERENCES `app_orgs_addresses` (`addressID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_orgs_attachments_ibfk_4` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7219 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_orgs_attachments`
--

LOCK TABLES `app_orgs_attachments` WRITE;
/*!40000 ALTER TABLE `app_orgs_attachments` DISABLE KEYS */;
/*!40000 ALTER TABLE `app_orgs_attachments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_orgs_clusters`
--

DROP TABLE IF EXISTS `app_orgs_clusters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_orgs_clusters` (
  `clusterID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `orgID` int(11) NOT NULL,
  `byID` int(11) DEFAULT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `added` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`clusterID`),
  KEY `fk_orgs_clusters_byID` (`byID`),
  KEY `fk_orgs_clusters_orgID` (`orgID`),
  KEY `fk_orgs_clusters_accountID` (`accountID`),
  CONSTRAINT `app_orgs_clusters_ibfk_1` FOREIGN KEY (`orgID`) REFERENCES `app_orgs` (`orgID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_orgs_clusters_ibfk_2` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `app_orgs_clusters_ibfk_3` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_orgs_clusters`
--

LOCK TABLES `app_orgs_clusters` WRITE;
/*!40000 ALTER TABLE `app_orgs_clusters` DISABLE KEYS */;
/*!40000 ALTER TABLE `app_orgs_clusters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_orgs_contacts`
--

DROP TABLE IF EXISTS `app_orgs_contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_orgs_contacts` (
  `contactID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `orgID` int(11) DEFAULT NULL,
  `byID` int(11) DEFAULT NULL,
  `imported` tinyint(1) NOT NULL DEFAULT '0',
  `isMain` tinyint(1) DEFAULT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `position` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tel` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mobile` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mc_synced` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `added` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`contactID`),
  KEY `fk_orgs_contacts_orgID` (`orgID`),
  KEY `fk_orgs_contacts_byID` (`byID`),
  KEY `fk_orgs_contacts_accountID` (`accountID`),
  CONSTRAINT `app_orgs_contacts_ibfk_1` FOREIGN KEY (`orgID`) REFERENCES `app_orgs` (`orgID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_orgs_contacts_ibfk_2` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `app_orgs_contacts_ibfk_3` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5883 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_orgs_contacts`
--

LOCK TABLES `app_orgs_contacts` WRITE;
/*!40000 ALTER TABLE `app_orgs_contacts` DISABLE KEYS */;
INSERT INTO `app_orgs_contacts` VALUES (2206,23,1862,224,0,1,'Fred Mcphee','Head Teacher','01482 976153','','Head@highland.park.co.uk',NULL,'2016-04-14 13:19:02','2016-04-14 13:19:02','2016-04-14 13:19:02'),(2207,23,1862,224,0,NULL,'Patt Tartan','Business Manager','01482 976153','','Tartan@highland.park.co.uk',NULL,'2016-04-14 13:20:04','2016-04-14 13:20:04','2016-04-14 13:20:04'),(2208,23,1863,224,0,NULL,'Max Palmer','Head Teacher','01482 562248','','Head@springfield.co.uk',NULL,'2016-04-15 08:11:37','2016-04-15 08:11:37','2016-04-15 08:11:37'),(2209,23,1863,224,0,1,'Autumn Moorhouse','Business Manager','01482 562248','','admin@springfield.co.uk',NULL,'2016-04-15 08:12:38','2016-04-15 08:12:38','2016-04-15 08:12:38'),(2210,23,1864,224,0,NULL,'Linda Hall','Head Teacher','01482 321123','','Head@greenroad.co.uk',NULL,'2016-04-15 08:17:35','2016-04-15 08:17:35','2016-04-15 08:17:35'),(2211,23,1864,224,0,1,'Jack Oram','Business Manager','01482321123','','admin@greenroad.co.uk',NULL,'2016-04-15 08:18:16','2016-04-15 08:18:16','2016-04-15 08:18:16'),(2212,23,1865,224,0,NULL,'John King','Head Teacher','01482789987','','head@yellowhill.co.uk',NULL,'2016-04-15 08:27:03','2016-04-15 08:27:03','2016-04-15 08:27:03'),(2213,23,1865,224,0,1,'Amy Wood','Admin','01482 789987','','admin@yellowhillprimary.co.uk',NULL,'2016-04-15 08:27:35','2016-04-15 08:27:35','2016-04-15 08:27:35'),(2214,23,1866,224,0,1,'Emma Harris','Head Teacher','01482 456456','','head@north.co.uk',NULL,'2016-04-15 09:28:30','2016-04-15 09:28:30','2016-04-15 09:28:30'),(2215,23,1867,224,0,1,'Charles George','Unit Leader','01482 566556','','info@kidscharityuk.co.uk',NULL,'2016-04-18 12:16:17','2016-04-18 12:16:17','2016-04-18 12:16:35'),(2216,23,1868,224,0,NULL,'Laura June','Unit Leader','01482 222222','','info@raiseawareness.com',NULL,'2016-04-18 12:59:48','2016-04-18 12:59:48','2016-04-18 12:59:48'),(2217,23,1869,224,0,1,'Lauren','Yates','+441482841907','+441482841907','admin@hullcity.co.uk',NULL,'2016-04-20 13:31:22','2016-04-20 13:31:22','2016-04-20 13:31:22'),(2218,23,1870,224,0,NULL,'Natalie Smales','Head Teacher','01482 852852','','head@warnerpark.co.uk',NULL,'2016-04-22 12:48:38','2016-04-22 12:48:38','2016-04-22 12:48:38');
/*!40000 ALTER TABLE `app_orgs_contacts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_orgs_contacts_newsletters`
--

DROP TABLE IF EXISTS `app_orgs_contacts_newsletters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_orgs_contacts_newsletters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `contactID` int(11) DEFAULT NULL,
  `brandID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_orgs_contacts_newsletters_attachmentID` (`contactID`),
  KEY `fk_orgs_contacts_newsletters_accountID` (`accountID`),
  KEY `fk_orgs_contacts_newsletters_brandID` (`brandID`),
  CONSTRAINT `app_orgs_contacts_newsletters_ibfk_1` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_orgs_contacts_newsletters_ibfk_2` FOREIGN KEY (`contactID`) REFERENCES `app_orgs_contacts` (`contactID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_orgs_contacts_newsletters_ibfk_3` FOREIGN KEY (`brandID`) REFERENCES `app_brands` (`brandID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_orgs_contacts_newsletters`
--

LOCK TABLES `app_orgs_contacts_newsletters` WRITE;
/*!40000 ALTER TABLE `app_orgs_contacts_newsletters` DISABLE KEYS */;
/*!40000 ALTER TABLE `app_orgs_contacts_newsletters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_orgs_notes`
--

DROP TABLE IF EXISTS `app_orgs_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_orgs_notes` (
  `noteID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `orgID` int(11) DEFAULT NULL,
  `byID` int(11) DEFAULT NULL,
  `summary` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `content` text COLLATE utf8_unicode_ci,
  `added` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`noteID`),
  KEY `fk_orgs_note_orgID` (`orgID`),
  KEY `fk_orgs_note_byID` (`byID`),
  KEY `fk_orgs_notes_accountID` (`accountID`),
  CONSTRAINT `app_orgs_notes_ibfk_1` FOREIGN KEY (`orgID`) REFERENCES `app_orgs` (`orgID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_orgs_notes_ibfk_2` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `app_orgs_notes_ibfk_3` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_orgs_notes`
--

LOCK TABLES `app_orgs_notes` WRITE;
/*!40000 ALTER TABLE `app_orgs_notes` DISABLE KEYS */;
/*!40000 ALTER TABLE `app_orgs_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_orgs_notifications`
--

DROP TABLE IF EXISTS `app_orgs_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_orgs_notifications` (
  `notificationID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `orgID` int(11) NOT NULL,
  `contactID` int(11) DEFAULT NULL,
  `byID` int(11) DEFAULT NULL,
  `type` enum('email','SMS') COLLATE utf8_unicode_ci NOT NULL,
  `destination` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `subject` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `contentText` text COLLATE utf8_unicode_ci NOT NULL,
  `contentHTML` text COLLATE utf8_unicode_ci,
  `status` enum('pending','sent','delivered','undelivered','invalid','unknown') COLLATE utf8_unicode_ci NOT NULL,
  `added` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`notificationID`),
  KEY `fk_org_notifications_byID` (`byID`),
  KEY `fk_org_notifications_contactID` (`contactID`),
  KEY `fk_org_notifications_orgID` (`orgID`),
  KEY `fk_orgs_notes_notifications_accountID` (`accountID`),
  CONSTRAINT `app_orgs_notifications_ibfk_3` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `app_orgs_notifications_ibfk_4` FOREIGN KEY (`orgID`) REFERENCES `app_orgs` (`orgID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_orgs_notifications_ibfk_5` FOREIGN KEY (`contactID`) REFERENCES `app_orgs_contacts` (`contactID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `app_orgs_notifications_ibfk_6` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1386 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_orgs_notifications`
--

LOCK TABLES `app_orgs_notifications` WRITE;
/*!40000 ALTER TABLE `app_orgs_notifications` DISABLE KEYS */;
INSERT INTO `app_orgs_notifications` VALUES (1297,23,1862,2206,208,'email','Head@highland.park.co.uk','Booking Confirmation','Dear Fred Mcphee, \n\nThank you for booking with Physical Education for Highland Park\nPrimary between 05/09/2017 and 24/12/2017. \n\nPlease see below a detailed summary of the lessons you have booked and\nany information you will need should you have a query or would like to\nmake any amendments to your booking. \n\nAUTUMN (05/09/2017 TO 24/12/2017) \n\n		DAY\n		START\n		END\n		GROUP\n		ACTIVITY\n\n 		Monday\n 		13:15\n 		14:20\n 		Year 5\n 		Games\n\n 		Monday\n 		14:20\n 		15:30\n 		Year 4\n 		Games\n\n 		Tuesday\n 		13:15\n 		14:20\n 		Year 4\n 		Games\n\n 		Tuesday\n 		14:20\n 		15:30\n 		Year 4\n 		Games\n\n 		Wednesday\n 		13:15\n 		14:20\n 		Year 6\n 		Games\n\n 		Wednesday\n 		14:20\n 		15:30\n 		Year 3\n 		Dance\n\n 		Thursday\n 		13:15\n 		14:20\n 		Year 6\n 		Games\n\n 		Thursday\n 		14:20\n 		15:30\n 		Year 6\n 		Games\n\n 		Friday\n 		09:50\n 		10:55\n 		Year 5\n 		Games\n\n 		Friday\n 		11:10\n 		12:15\n 		Year 5\n 		Games\n\nPlease check all the above details match your booking requirements and\ninform us as soon as possible should you notice any discrepancies or\nare required to make changes. \n\nPlease do not hesitate to contact us if you have any queries. \n\nWe look forward to continuing working closely with you.','<p>Dear Fred Mcphee,</p>\r\n<p>Thank you for booking with Physical Education for Highland Park Primary between 05/09/2017 and 24/12/2017.</p>\r\n<p>Please see below a detailed summary of the lessons you have booked and any information you will need should you have a query or would like to make any amendments to your booking.</p>\r\n<p><strong>Autumn (05/09/2017 to 24/12/2017)</strong></p>\r\n<table style=\"width: 100%;\" border=\"1\">\r\n<tbody>\r\n<tr><th scope=\"col\">Day</th><th scope=\"col\">Start</th><th scope=\"col\">End</th><th scope=\"col\">Group</th><th scope=\"col\">Activity</th></tr>\r\n<tr>\r\n<td>Monday</td>\r\n<td>13:15</td>\r\n<td>14:20</td>\r\n<td>Year 5</td>\r\n<td>Games</td>\r\n</tr>\r\n<tr>\r\n<td>Monday</td>\r\n<td>14:20</td>\r\n<td>15:30</td>\r\n<td>Year 4</td>\r\n<td>Games</td>\r\n</tr>\r\n<tr>\r\n<td>Tuesday</td>\r\n<td>13:15</td>\r\n<td>14:20</td>\r\n<td>Year 4</td>\r\n<td>Games</td>\r\n</tr>\r\n<tr>\r\n<td>Tuesday</td>\r\n<td>14:20</td>\r\n<td>15:30</td>\r\n<td>Year 4</td>\r\n<td>Games</td>\r\n</tr>\r\n<tr>\r\n<td>Wednesday</td>\r\n<td>13:15</td>\r\n<td>14:20</td>\r\n<td>Year 6</td>\r\n<td>Games</td>\r\n</tr>\r\n<tr>\r\n<td>Wednesday</td>\r\n<td>14:20</td>\r\n<td>15:30</td>\r\n<td>Year 3</td>\r\n<td>Dance</td>\r\n</tr>\r\n<tr>\r\n<td>Thursday</td>\r\n<td>13:15</td>\r\n<td>14:20</td>\r\n<td>Year 6</td>\r\n<td>Games</td>\r\n</tr>\r\n<tr>\r\n<td>Thursday</td>\r\n<td>14:20</td>\r\n<td>15:30</td>\r\n<td>Year 6</td>\r\n<td>Games</td>\r\n</tr>\r\n<tr>\r\n<td>Friday</td>\r\n<td>09:50</td>\r\n<td>10:55</td>\r\n<td>Year 5</td>\r\n<td>Games</td>\r\n</tr>\r\n<tr>\r\n<td>Friday</td>\r\n<td>11:10</td>\r\n<td>12:15</td>\r\n<td>Year 5</td>\r\n<td>Games</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<p>Please check all the above details match your booking requirements and inform us as soon as possible should you notice any discrepancies or are required to make changes.</p>\r\n<p>Please do not hesitate to contact us if you have any queries.</p>\r\n<p>We look forward to continuing working closely with you.</p>','sent','2017-09-28 14:15:30','2017-09-28 14:15:30'),(1298,23,1862,2206,208,'email','Head@highland.park.co.uk','DBS Details for Autumn','Dear Fred Mcphee, \n\nAs per your request, we are pleased to supply you with confirmation if\nthe DBS details you have requested for Autumn: \n\n		STAFF NAME\n		DBS NO.\n		ISSUE DATE\n		EXPIRY DATE\n\n 		Ben Smith\n 		001234567891\n 		Unknown\n 		14/04/2019\n\nIf you have any further queries please do not hesitate to contact us.','<p>Dear Fred Mcphee,</p>\r\n<p>As per your request, we are pleased to supply you with confirmation if the DBS details you have requested for Autumn:</p>\r\n<table style=\"width: 100%;\" border=\"1\">\r\n<tbody>\r\n<tr><th scope=\"col\">Staff Name</th><th scope=\"col\">DBS No.</th><th scope=\"col\">Issue Date</th><th scope=\"col\">Expiry Date</th></tr>\r\n<tr>\r\n<td>Ben Smith</td>\r\n<td>001234567891</td>\r\n<td>Unknown</td>\r\n<td>14/04/2019</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<p>If you have any further queries please do not hesitate to contact us.</p>','sent','2017-09-28 14:15:48','2017-09-28 14:15:48');
/*!40000 ALTER TABLE `app_orgs_notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_orgs_notifications_attachments_customers`
--

DROP TABLE IF EXISTS `app_orgs_notifications_attachments_customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_orgs_notifications_attachments_customers` (
  `linkID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `notificationID` int(11) NOT NULL,
  `attachmentID` int(11) DEFAULT NULL,
  PRIMARY KEY (`linkID`),
  KEY `fk_orgs_notifications_attachments_customers_notificationID` (`notificationID`),
  KEY `fk_orgs_notifications_attachments_customers_attachmentID` (`attachmentID`),
  KEY `orgs_notifications_attachments_customers_accountID` (`accountID`),
  CONSTRAINT `app_orgs_notifications_attachments_customers_ibfk_1` FOREIGN KEY (`notificationID`) REFERENCES `app_orgs_notifications` (`notificationID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_orgs_notifications_attachments_customers_ibfk_2` FOREIGN KEY (`attachmentID`) REFERENCES `app_orgs_attachments` (`attachmentID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_orgs_notifications_attachments_customers_ibfk_3` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_orgs_notifications_attachments_customers`
--

LOCK TABLES `app_orgs_notifications_attachments_customers` WRITE;
/*!40000 ALTER TABLE `app_orgs_notifications_attachments_customers` DISABLE KEYS */;
/*!40000 ALTER TABLE `app_orgs_notifications_attachments_customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_orgs_notifications_attachments_resources`
--

DROP TABLE IF EXISTS `app_orgs_notifications_attachments_resources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_orgs_notifications_attachments_resources` (
  `linkID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `notificationID` int(11) NOT NULL,
  `attachmentID` int(11) DEFAULT NULL,
  PRIMARY KEY (`linkID`),
  KEY `fk_orgs_notifications_attachments_resources_notificationID` (`notificationID`),
  KEY `fk_orgs_notifications_attachments_resources_attachmentID` (`attachmentID`),
  KEY `fk_orgs_notifications_attachments_resources_accountID` (`accountID`),
  CONSTRAINT `app_orgs_notifications_attachments_resources_ibfk_1` FOREIGN KEY (`notificationID`) REFERENCES `app_orgs_notifications` (`notificationID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_orgs_notifications_attachments_resources_ibfk_2` FOREIGN KEY (`attachmentID`) REFERENCES `app_files` (`attachmentID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_orgs_notifications_attachments_resources_ibfk_3` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_orgs_notifications_attachments_resources`
--

LOCK TABLES `app_orgs_notifications_attachments_resources` WRITE;
/*!40000 ALTER TABLE `app_orgs_notifications_attachments_resources` DISABLE KEYS */;
/*!40000 ALTER TABLE `app_orgs_notifications_attachments_resources` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_orgs_pricing`
--

DROP TABLE IF EXISTS `app_orgs_pricing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_orgs_pricing` (
  `linkID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL,
  `orgID` int(11) NOT NULL,
  `typeID` int(11) NOT NULL,
  `brandID` int(11) NOT NULL,
  `amount` decimal(8,2) NOT NULL,
  `contract` tinyint(1) NOT NULL DEFAULT '0',
  `added` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`linkID`),
  KEY `accountID` (`accountID`),
  KEY `orgID` (`orgID`),
  KEY `typeID` (`typeID`),
  KEY `brandID` (`brandID`),
  CONSTRAINT `app_orgs_pricing_ibfk_1` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_orgs_pricing_ibfk_2` FOREIGN KEY (`orgID`) REFERENCES `app_orgs` (`orgID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_orgs_pricing_brandID` FOREIGN KEY (`brandID`) REFERENCES `app_brands` (`brandID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `fk_orgs_pricing_typeID` FOREIGN KEY (`typeID`) REFERENCES `app_lesson_types` (`typeID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8215 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_orgs_pricing`
--

LOCK TABLES `app_orgs_pricing` WRITE;
/*!40000 ALTER TABLE `app_orgs_pricing` DISABLE KEYS */;
INSERT INTO `app_orgs_pricing` VALUES (17,23,1862,99,24,20.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(18,23,1862,99,25,20.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(19,23,1862,99,26,20.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(20,23,1862,99,27,20.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(21,23,1862,99,28,20.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(22,23,1862,101,24,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(23,23,1862,101,25,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(24,23,1862,101,26,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(25,23,1862,101,27,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(26,23,1862,101,28,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(27,23,1863,99,24,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(28,23,1863,99,25,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(29,23,1863,99,26,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(30,23,1863,99,27,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(31,23,1863,99,28,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(32,23,1863,101,24,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(33,23,1863,101,25,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(34,23,1863,101,26,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(35,23,1863,101,27,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(36,23,1863,101,28,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(37,23,1864,99,24,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(38,23,1864,99,25,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(39,23,1864,99,26,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(40,23,1864,99,27,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(41,23,1864,99,28,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(42,23,1864,101,24,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(43,23,1864,101,25,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(44,23,1864,101,26,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(45,23,1864,101,27,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(46,23,1864,101,28,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(47,23,1865,99,24,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(48,23,1865,99,25,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(49,23,1865,99,26,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(50,23,1865,99,27,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(51,23,1865,99,28,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(52,23,1865,100,24,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(53,23,1865,100,25,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(54,23,1865,100,26,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(55,23,1865,100,27,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(56,23,1865,100,28,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(57,23,1865,101,24,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(58,23,1865,101,25,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(59,23,1865,101,26,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(60,23,1865,101,27,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(61,23,1865,101,28,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(62,23,1866,99,24,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(63,23,1866,99,25,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(64,23,1866,99,26,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(65,23,1866,99,27,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(66,23,1866,99,28,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(67,23,1866,100,24,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(68,23,1866,100,25,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(69,23,1866,100,26,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(70,23,1866,100,27,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(71,23,1866,100,28,60.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(72,23,1866,101,24,60.00,1,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(73,23,1866,101,25,60.00,1,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(74,23,1866,101,26,60.00,1,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(75,23,1866,101,27,60.00,1,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(76,23,1866,101,28,60.00,1,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(77,23,1866,111,24,20000.00,1,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(78,23,1866,111,25,20000.00,1,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(79,23,1866,111,26,20000.00,1,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(80,23,1866,111,27,20000.00,1,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(81,23,1866,111,28,20000.00,1,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(82,23,1870,99,24,90.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(83,23,1870,99,25,90.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(84,23,1870,99,26,90.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(85,23,1870,99,27,90.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(86,23,1870,99,28,90.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(87,23,1870,100,24,9000.00,1,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(88,23,1870,100,25,9000.00,1,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(89,23,1870,100,26,9000.00,1,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(90,23,1870,100,27,9000.00,1,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(91,23,1870,100,28,9000.00,1,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(92,23,1870,101,24,20.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(93,23,1870,101,25,20.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(94,23,1870,101,26,20.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(95,23,1870,101,27,20.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28'),(96,23,1870,101,28,20.00,0,'2016-04-28 10:01:28','2016-04-28 10:01:28');
/*!40000 ALTER TABLE `app_orgs_pricing` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_orgs_safety`
--

DROP TABLE IF EXISTS `app_orgs_safety`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_orgs_safety` (
  `docID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `orgID` int(11) NOT NULL,
  `addressID` int(11) NOT NULL,
  `byID` int(11) DEFAULT NULL,
  `type` enum('risk assessment','school induction','camp induction') COLLATE utf8_unicode_ci NOT NULL,
  `renewed` tinyint(1) NOT NULL DEFAULT '0',
  `date` date NOT NULL,
  `expiry` date NOT NULL,
  `details` text COLLATE utf8_unicode_ci NOT NULL,
  `added` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`docID`),
  KEY `fk_orgs_safety_orgID` (`orgID`),
  KEY `fk_orgs_safety_byID` (`byID`),
  KEY `fk_orgs_safety_addressID` (`addressID`),
  KEY `fk_orgs_safety_accountID` (`accountID`),
  CONSTRAINT `app_orgs_safety_ibfk_2` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `app_orgs_safety_ibfk_3` FOREIGN KEY (`addressID`) REFERENCES `app_orgs_addresses` (`addressID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_orgs_safety_ibfk_4` FOREIGN KEY (`orgID`) REFERENCES `app_orgs` (`orgID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_orgs_safety_ibfk_5` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1985 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_orgs_safety`
--

LOCK TABLES `app_orgs_safety` WRITE;
/*!40000 ALTER TABLE `app_orgs_safety` DISABLE KEYS */;
INSERT INTO `app_orgs_safety` VALUES (512,23,1864,2327,225,'risk assessment',0,'2016-10-15','2016-10-15','a:3:{s:8:\"location\";s:18:\"Green Road Primary\";s:3:\"who\";s:22:\"Coach and Participants\";s:5:\"final\";s:52:\"Risk Assessment was completed with a member of staff\";}','2016-04-15 13:05:44','2016-06-03 16:37:18'),(513,23,1864,2327,226,'school induction',0,'2015-04-15','2016-10-15','a:20:{s:8:\"location\";s:18:\"Green Road Primary\";s:16:\"fire_alarm_tests\";s:67:\"Once half termly. \r\nTeacher will have register and coach to assist.\";s:20:\"fire_assembly_points\";s:122:\"Main gates/fence\r\nLine children up in class order, the teacher will be present at all times during a drill/fire emergency.\";s:14:\"fire_procedure\";s:101:\"If in the classroom exit through classroom nearest door, \r\nIf in the hall exit through main entrance.\";s:28:\"accident_reporting_procedure\";s:156:\"Contact main office if the incident is serious and you are unable to treat. \r\nLunchtime supervisors overlook accidents during lunch and have their own book.\";s:13:\"accident_book\";s:12:\"Main office.\";s:16:\"accident_contact\";s:0:\"\";s:17:\"behaviour_rewards\";s:149:\"Stickers for good behaviour/work.\r\nGolden tickets that go into the golden bo can be given but only for pupils that are outstanding/large improvement.\";s:19:\"behaviour_procedure\";s:145:\"Sunshine and cloud.\r\nEverybody starts on a sunshine and if pupils are told, spoken to 3 times for bad behaviour then they will go onto the cloud.\";s:21:\"behaviour_sen_medical\";s:0:\"\";s:17:\"further_dos_donts\";s:288:\"No shouting in the corridors or in the hall.\r\nClass teacher will bring children to the hall therefore coach to wait for them and set up prior. \r\nChildren to walk down the corridors quietly.\r\nIf wet play coach rotate from class and play circle games, \r\n1-5 1-3 12:30\r\n4-5 - 1 o&#039;clock.\";s:20:\"further_helpful_info\";s:877:\"Children will arrive to the lesson with their indoor shoes and will take them off once in the hall. \r\nAfter school club, coach will have a member of staff present at all times. Coach must meet children in the ICT suite ready for 3 o&#039;clock. \r\nCoach to take children back to ICT suite to change and to let pupils out of the main door when changed. \r\nRegister for after school club at the main office.\r\n\r\nCoach must be at the school for the first lesson at 08:50am to ensure they are ready to take the children to get changed. Please ensure you are at the school for this time as the school have requested this be actioned. For the Afterschool please ensure if the school ask you stay with the children till 16:15 that this is done, any additional time waiting with the children can be added to your timesheet for the additional 15 minutes should the school request you stay.\";s:17:\"further_behaviour\";s:40:\"Generally overall the behaviour is good.\";s:15:\"further_carpark\";s:31:\"open 7am-9am\r\nReopens at 9:30am\";s:9:\"equipment\";a:10:{i:0;s:10:\"Gymnastics\";i:1;s:8:\"Football\";i:2;s:9:\"CD Player\";i:3;s:9:\"Softballs\";i:4;s:9:\"Athletics\";i:5;s:5:\"Hoops\";i:6;s:8:\"Beanbags\";i:7;s:6:\"Quoits\";i:8;s:12:\"Tennis Balls\";i:9;s:6:\"Hockey\";}s:17:\"equipment_details\";a:17:{s:10:\"gymnastics\";s:0:\"\";s:8:\"football\";s:0:\"\";s:5:\"rugby\";s:0:\"\";s:10:\"basketball\";s:0:\"\";s:7:\"netball\";s:0:\"\";s:8:\"cdplayer\";s:0:\"\";s:6:\"tennis\";s:0:\"\";s:8:\"rounders\";s:0:\"\";s:7:\"cricket\";s:0:\"\";s:9:\"softballs\";s:0:\"\";s:5:\"cones\";s:0:\"\";s:9:\"athletics\";s:0:\"\";s:5:\"hoops\";s:0:\"\";s:8:\"beanbags\";s:0:\"\";s:6:\"quoits\";s:0:\"\";s:11:\"tennisballs\";s:0:\"\";s:6:\"hockey\";s:0:\"\";}s:20:\"equipment_additional\";s:83:\"Limited equipment so any specific equipment such as pom poms bring from the office.\";s:16:\"further_comments\";s:443:\"Class 3 has a child that has speech and language difficulties so coach to be aware.\r\n\r\nBilly in class 1 has epilepsy the staff are all fully aware and trained to give assistance. Should the coach have any concerns at all contact a member of staff immediately.\r\n\r\nClass 4 have some children with challenging behavior so coach to be aware of this. \r\n\r\nAfter school club finish at 4 and the children need to be dressed and ready to leave at 4.15.\";s:12:\"venue_images\";a:1:{i:0;s:36:\"l1a86tdvHD4gX2ThKBbxnRUYVS0ApE3o.jpg\";}s:10:\"map_images\";a:1:{i:0;s:37:\"l1a86tdvHD4gX2ThKBbxnRUYVS0ApE3o1.jpg\";}}','2016-04-15 13:25:03','2016-04-27 11:15:57'),(514,23,1862,2325,226,'school induction',1,'2015-10-15','2016-10-15','a:20:{s:8:\"location\";s:23:\"Highland Avenue Primary\";s:16:\"fire_alarm_tests\";s:67:\"Once half termly. \r\nTeacher will have register and coach to assist.\";s:20:\"fire_assembly_points\";s:122:\"Main gates/fence\r\nLine children up in class order, the teacher will be present at all times during a drill/fire emergency.\";s:14:\"fire_procedure\";s:101:\"If in the classroom exit through classroom nearest door, \r\nIf in the hall exit through main entrance.\";s:28:\"accident_reporting_procedure\";s:156:\"Contact main office if the incident is serious and you are unable to treat. \r\nLunchtime supervisors overlook accidents during lunch and have their own book.\";s:13:\"accident_book\";s:12:\"Main office.\";s:16:\"accident_contact\";s:0:\"\";s:17:\"behaviour_rewards\";s:150:\"Stickers for good behavior/work.\r\nGolden tickets that go into the golden book can be given but only for pupils that are outstanding/large improvement.\";s:19:\"behaviour_procedure\";s:144:\"Sunshine and cloud.\r\nEverybody starts on a sunshine and if pupils are told, spoken to 3 times for bad behavior then they will go onto the cloud.\";s:21:\"behaviour_sen_medical\";s:0:\"\";s:17:\"further_dos_donts\";s:288:\"No shouting in the corridors or in the hall.\r\nClass teacher will bring children to the hall therefore coach to wait for them and set up prior. \r\nChildren to walk down the corridors quietly.\r\nIf wet play coach rotate from class and play circle games, \r\n1-5 1-3 12:30\r\n4-5 - 1 o&#039;clock.\";s:20:\"further_helpful_info\";s:877:\"Children will arrive to the lesson with their indoor shoes and will take them off once in the hall. \r\nAfter school club, coach will have a member of staff present at all times. Coach must meet children in the ICT suite ready for 3 o&#039;clock. \r\nCoach to take children back to ICT suite to change and to let pupils out of the main door when changed. \r\nRegister for after school club at the main office.\r\n\r\nCoach must be at the school for the first lesson at 08:50am to ensure they are ready to take the children to get changed. Please ensure you are at the school for this time as the school have requested this be actioned. For the Afterschool please ensure if the school ask you stay with the children till 16:15 that this is done, any additional time waiting with the children can be added to your timesheet for the additional 15 minutes should the school request you stay.\";s:17:\"further_behaviour\";s:39:\"Generally overall the behavior is good.\";s:15:\"further_carpark\";s:31:\"Open 7am-9am\r\nReopens at 9:30am\";s:9:\"equipment\";a:17:{i:0;s:10:\"Gymnastics\";i:1;s:8:\"Football\";i:2;s:5:\"Rugby\";i:3;s:10:\"Basketball\";i:4;s:7:\"Netball\";i:5;s:9:\"CD Player\";i:6;s:6:\"Tennis\";i:7;s:8:\"Rounders\";i:8;s:7:\"Cricket\";i:9;s:9:\"Softballs\";i:10;s:5:\"Cones\";i:11;s:9:\"Athletics\";i:12;s:5:\"Hoops\";i:13;s:8:\"Beanbags\";i:14;s:6:\"Quoits\";i:15;s:12:\"Tennis Balls\";i:16;s:6:\"Hockey\";}s:17:\"equipment_details\";a:17:{s:10:\"gymnastics\";s:0:\"\";s:8:\"football\";s:0:\"\";s:5:\"rugby\";s:0:\"\";s:10:\"basketball\";s:0:\"\";s:7:\"netball\";s:0:\"\";s:8:\"cdplayer\";s:0:\"\";s:6:\"tennis\";s:0:\"\";s:8:\"rounders\";s:0:\"\";s:7:\"cricket\";s:0:\"\";s:9:\"softballs\";s:0:\"\";s:5:\"cones\";s:0:\"\";s:9:\"athletics\";s:0:\"\";s:5:\"hoops\";s:0:\"\";s:8:\"beanbags\";s:0:\"\";s:6:\"quoits\";s:0:\"\";s:11:\"tennisballs\";s:0:\"\";s:6:\"hockey\";s:0:\"\";}s:20:\"equipment_additional\";s:83:\"Limited equipment so any specific equipment such as pom poms bring from the office.\";s:16:\"further_comments\";s:444:\"Class 3 has a child that has speech and language difficulties so coach to be aware.\r\n\r\nBilly in class 1 has epilepsy the staff are all fully aware and trained to give assistance. Should the coach have any concerns at all contact a member of staff immediately.\r\n\r\nClass 4 have some children with challenging behaviour so coach to be aware of this. \r\n\r\nAfter school club finish at 4 and the children need to be dressed and ready to leave at 4.15.\";s:12:\"venue_images\";N;s:10:\"map_images\";N;}','2016-04-15 13:29:18','2017-09-28 14:16:24'),(515,23,1862,2325,229,'risk assessment',1,'2017-09-28','2019-04-28','a:3:{s:8:\"location\";s:0:\"\";s:3:\"who\";s:22:\"Coach and Participants\";s:5:\"final\";s:52:\"Risk Assessment was completed with a member of staff\";}','2016-04-15 13:30:24','2017-09-28 14:19:04'),(516,23,1866,2329,225,'risk assessment',0,'2016-01-12','2017-04-12','a:3:{s:8:\"location\";s:0:\"\";s:3:\"who\";s:22:\"Coach and Participants\";s:5:\"final\";s:52:\"Risk Assessment was completed with a member of staff\";}','2016-04-15 13:41:09','2016-04-15 13:41:09'),(517,23,1866,2329,230,'school induction',0,'2016-01-20','2017-04-27','a:20:{s:8:\"location\";s:0:\"\";s:16:\"fire_alarm_tests\";s:67:\"Once half termly. \r\nTeacher will have register and coach to assist.\";s:20:\"fire_assembly_points\";s:122:\"Main gates/fence\r\nLine children up in class order, the teacher will be present at all times during a drill/fire emergency.\";s:14:\"fire_procedure\";s:101:\"If in the classroom exit through classroom nearest door, \r\nIf in the hall exit through main entrance.\";s:28:\"accident_reporting_procedure\";s:156:\"Contact main office if the incident is serious and you are unable to treat. \r\nLunchtime supervisors overlook accidents during lunch and have their own book.\";s:13:\"accident_book\";s:12:\"Main office.\";s:16:\"accident_contact\";s:0:\"\";s:17:\"behaviour_rewards\";s:150:\"Stickers for good behavior/work.\r\nGolden tickets that go into the golden book can be given but only for pupils that are outstanding/large improvement.\";s:19:\"behaviour_procedure\";s:144:\"Sunshine and cloud.\r\nEverybody starts on a sunshine and if pupils are told, spoken to 3 times for bad behavior then they will go onto the cloud.\";s:21:\"behaviour_sen_medical\";s:0:\"\";s:17:\"further_dos_donts\";s:288:\"No shouting in the corridors or in the hall.\r\nClass teacher will bring children to the hall therefore coach to wait for them and set up prior. \r\nChildren to walk down the corridors quietly.\r\nIf wet play coach rotate from class and play circle games, \r\n1-5 1-3 12:30\r\n4-5 - 1 o&#039;clock.\";s:20:\"further_helpful_info\";s:880:\"Children will arrive to the lesson with their indoor shoes and will take them off once in the hall. \r\nAfter school club, coach will have a member of staff present at all times. Coach must meet children in the ICT suite ready for 3 o&#039;clock. \r\nCoach to take children back to ICT suite to change and to let pupils out of the main door when changed. \r\nRegister for after school club at the main office.\r\n\r\nCoach must be at the school for the first lesson at 08:50am to ensure they are ready to take the children to get changed. Please ensure you are at the school for this time as the school have requested this be action-ed. For the After school please ensure if the school ask you stay with the children till 16:15 that this is done, any additional time waiting with the children can be added to your time sheet for the additional 15 minutes should the school request you stay.\";s:17:\"further_behaviour\";s:39:\"Generally overall the behavior is good.\";s:15:\"further_carpark\";s:31:\"open 7am-9am\r\nReopens at 9:30am\";s:9:\"equipment\";a:17:{i:0;s:10:\"Gymnastics\";i:1;s:8:\"Football\";i:2;s:5:\"Rugby\";i:3;s:10:\"Basketball\";i:4;s:7:\"Netball\";i:5;s:9:\"CD Player\";i:6;s:6:\"Tennis\";i:7;s:8:\"Rounders\";i:8;s:7:\"Cricket\";i:9;s:9:\"Softballs\";i:10;s:5:\"Cones\";i:11;s:9:\"Athletics\";i:12;s:5:\"Hoops\";i:13;s:8:\"Beanbags\";i:14;s:6:\"Quoits\";i:15;s:12:\"Tennis Balls\";i:16;s:6:\"Hockey\";}s:17:\"equipment_details\";a:17:{s:10:\"gymnastics\";s:0:\"\";s:8:\"football\";s:0:\"\";s:5:\"rugby\";s:0:\"\";s:10:\"basketball\";s:0:\"\";s:7:\"netball\";s:0:\"\";s:8:\"cdplayer\";s:0:\"\";s:6:\"tennis\";s:0:\"\";s:8:\"rounders\";s:0:\"\";s:7:\"cricket\";s:0:\"\";s:9:\"softballs\";s:0:\"\";s:5:\"cones\";s:0:\"\";s:9:\"athletics\";s:0:\"\";s:5:\"hoops\";s:0:\"\";s:8:\"beanbags\";s:0:\"\";s:6:\"quoits\";s:0:\"\";s:11:\"tennisballs\";s:0:\"\";s:6:\"hockey\";s:0:\"\";}s:20:\"equipment_additional\";s:83:\"Limited equipment so any specific equipment such as pom poms bring from the office.\";s:16:\"further_comments\";s:443:\"Class 3 has a child that has speech and language difficulties so coach to be aware.\r\n\r\nBilly in class 1 has epilepsy the staff are all fully aware and trained to give assistance. Should the coach have any concerns at all contact a member of staff immediately.\r\n\r\nClass 4 have some children with challenging behavior so coach to be aware of this. \r\n\r\nAfter school club finish at 4 and the children need to be dressed and ready to leave at 4.15.\";s:12:\"venue_images\";N;s:10:\"map_images\";N;}','2016-04-15 13:48:40','2016-04-15 13:48:40'),(518,23,1863,2326,230,'risk assessment',0,'2016-01-13','2017-04-12','a:3:{s:8:\"location\";s:0:\"\";s:3:\"who\";s:22:\"Coach and Participants\";s:5:\"final\";s:52:\"Risk Assessment was completed with a member of staff\";}','2016-04-15 13:50:00','2016-04-15 13:50:00'),(519,23,1863,2326,228,'school induction',0,'2016-01-06','2017-04-20','a:20:{s:8:\"location\";s:0:\"\";s:16:\"fire_alarm_tests\";s:67:\"Once half termly. \r\nTeacher will have register and coach to assist.\";s:20:\"fire_assembly_points\";s:122:\"Main gates/fence\r\nLine children up in class order, the teacher will be present at all times during a drill/fire emergency.\";s:14:\"fire_procedure\";s:101:\"If in the classroom exit through classroom nearest door, \r\nIf in the hall exit through main entrance.\";s:28:\"accident_reporting_procedure\";s:156:\"Contact main office if the incident is serious and you are unable to treat. \r\nLunchtime supervisors overlook accidents during lunch and have their own book.\";s:13:\"accident_book\";s:12:\"Main office.\";s:16:\"accident_contact\";s:0:\"\";s:17:\"behaviour_rewards\";s:146:\"Stickers for good behavior/work.\r\nGolden tickets that go into the golden  can be given but only for pupils that are outstanding/large improvement.\";s:19:\"behaviour_procedure\";s:144:\"Sunshine and cloud.\r\nEverybody starts on a sunshine and if pupils are told, spoken to 3 times for bad behavior then they will go onto the cloud.\";s:21:\"behaviour_sen_medical\";s:0:\"\";s:17:\"further_dos_donts\";s:288:\"No shouting in the corridors or in the hall.\r\nClass teacher will bring children to the hall therefore coach to wait for them and set up prior. \r\nChildren to walk down the corridors quietly.\r\nIf wet play coach rotate from class and play circle games, \r\n1-5 1-3 12:30\r\n4-5 - 1 o&#039;clock.\";s:20:\"further_helpful_info\";s:879:\"Children will arrive to the lesson with their indoor shoes and will take them off once in the hall. \r\nAfter school club, coach will have a member of staff present at all times. Coach must meet children in the ICT suite ready for 3 o&#039;clock. \r\nCoach to take children back to ICT suite to change and to let pupils out of the main door when changed. \r\nRegister for after school club at the main office.\r\n\r\nCoach must be at the school for the first lesson at 08:50am to ensure they are ready to take the children to get changed. Please ensure you are at the school for this time as the school have requested this be actioned. For the After school please ensure if the school ask you stay with the children till 16:15 that this is done, any additional time waiting with the children can be added to your time sheet for the additional 15 minutes should the school request you stay.\";s:17:\"further_behaviour\";s:39:\"Generally overall the behavior is good.\";s:15:\"further_carpark\";s:31:\"open 7am-9am\r\nReopens at 9:30am\";s:9:\"equipment\";a:17:{i:0;s:10:\"Gymnastics\";i:1;s:8:\"Football\";i:2;s:5:\"Rugby\";i:3;s:10:\"Basketball\";i:4;s:7:\"Netball\";i:5;s:9:\"CD Player\";i:6;s:6:\"Tennis\";i:7;s:8:\"Rounders\";i:8;s:7:\"Cricket\";i:9;s:9:\"Softballs\";i:10;s:5:\"Cones\";i:11;s:9:\"Athletics\";i:12;s:5:\"Hoops\";i:13;s:8:\"Beanbags\";i:14;s:6:\"Quoits\";i:15;s:12:\"Tennis Balls\";i:16;s:6:\"Hockey\";}s:17:\"equipment_details\";a:17:{s:10:\"gymnastics\";s:0:\"\";s:8:\"football\";s:0:\"\";s:5:\"rugby\";s:0:\"\";s:10:\"basketball\";s:0:\"\";s:7:\"netball\";s:0:\"\";s:8:\"cdplayer\";s:0:\"\";s:6:\"tennis\";s:0:\"\";s:8:\"rounders\";s:0:\"\";s:7:\"cricket\";s:0:\"\";s:9:\"softballs\";s:0:\"\";s:5:\"cones\";s:0:\"\";s:9:\"athletics\";s:0:\"\";s:5:\"hoops\";s:0:\"\";s:8:\"beanbags\";s:0:\"\";s:6:\"quoits\";s:0:\"\";s:11:\"tennisballs\";s:0:\"\";s:6:\"hockey\";s:0:\"\";}s:20:\"equipment_additional\";s:83:\"Limited equipment so any specific equipment such as pom poms bring from the office.\";s:16:\"further_comments\";s:443:\"Class 3 has a child that has speech and language difficulties so coach to be aware.\r\n\r\nBilly in class 1 has epilepsy the staff are all fully aware and trained to give assistance. Should the coach have any concerns at all contact a member of staff immediately.\r\n\r\nClass 4 have some children with challenging behavior so coach to be aware of this. \r\n\r\nAfter school club finish at 4 and the children need to be dressed and ready to leave at 4.15.\";s:12:\"venue_images\";N;s:10:\"map_images\";N;}','2016-04-15 13:57:19','2016-04-15 13:57:19'),(520,23,1865,2328,227,'school induction',0,'2016-01-15','2017-04-19','a:20:{s:8:\"location\";s:0:\"\";s:16:\"fire_alarm_tests\";s:67:\"Once half termly. \r\nTeacher will have register and coach to assist.\";s:20:\"fire_assembly_points\";s:122:\"Main gates/fence\r\nLine children up in class order, the teacher will be present at all times during a drill/fire emergency.\";s:14:\"fire_procedure\";s:101:\"If in the classroom exit through classroom nearest door, \r\nIf in the hall exit through main entrance.\";s:28:\"accident_reporting_procedure\";s:156:\"Contact main office if the incident is serious and you are unable to treat. \r\nLunchtime supervisors overlook accidents during lunch and have their own book.\";s:13:\"accident_book\";s:12:\"Main office.\";s:16:\"accident_contact\";s:148:\"Stickers for good behavior/work.\r\nGolden tickets that go into the golden bo can be given but only for pupils that are outstanding/large improvement.\";s:17:\"behaviour_rewards\";s:144:\"Sunshine and cloud.\r\nEverybody starts on a sunshine and if pupils are told, spoken to 3 times for bad behavior then they will go onto the cloud.\";s:19:\"behaviour_procedure\";s:0:\"\";s:21:\"behaviour_sen_medical\";s:0:\"\";s:17:\"further_dos_donts\";s:288:\"No shouting in the corridors or in the hall.\r\nClass teacher will bring children to the hall therefore coach to wait for them and set up prior. \r\nChildren to walk down the corridors quietly.\r\nIf wet play coach rotate from class and play circle games, \r\n1-5 1-3 12:30\r\n4-5 - 1 o&#039;clock.\";s:20:\"further_helpful_info\";s:879:\"Children will arrive to the lesson with their indoor shoes and will take them off once in the hall. \r\nAfter school club, coach will have a member of staff present at all times. Coach must meet children in the ICT suite ready for 3 o&#039;clock. \r\nCoach to take children back to ICT suite to change and to let pupils out of the main door when changed. \r\nRegister for after school club at the main office.\r\n\r\nCoach must be at the school for the first lesson at 08:50am to ensure they are ready to take the children to get changed. Please ensure you are at the school for this time as the school have requested this be actioned. For the After school please ensure if the school ask you stay with the children till 16:15 that this is done, any additional time waiting with the children can be added to your time sheet for the additional 15 minutes should the school request you stay.\";s:17:\"further_behaviour\";s:39:\"Generally overall the behavior is good.\";s:15:\"further_carpark\";s:31:\"open 7am-9am\r\nReopens at 9:30am\";s:9:\"equipment\";a:17:{i:0;s:10:\"Gymnastics\";i:1;s:8:\"Football\";i:2;s:5:\"Rugby\";i:3;s:10:\"Basketball\";i:4;s:7:\"Netball\";i:5;s:9:\"CD Player\";i:6;s:6:\"Tennis\";i:7;s:8:\"Rounders\";i:8;s:7:\"Cricket\";i:9;s:9:\"Softballs\";i:10;s:5:\"Cones\";i:11;s:9:\"Athletics\";i:12;s:5:\"Hoops\";i:13;s:8:\"Beanbags\";i:14;s:6:\"Quoits\";i:15;s:12:\"Tennis Balls\";i:16;s:6:\"Hockey\";}s:17:\"equipment_details\";a:17:{s:10:\"gymnastics\";s:0:\"\";s:8:\"football\";s:0:\"\";s:5:\"rugby\";s:0:\"\";s:10:\"basketball\";s:0:\"\";s:7:\"netball\";s:0:\"\";s:8:\"cdplayer\";s:0:\"\";s:6:\"tennis\";s:0:\"\";s:8:\"rounders\";s:0:\"\";s:7:\"cricket\";s:0:\"\";s:9:\"softballs\";s:0:\"\";s:5:\"cones\";s:0:\"\";s:9:\"athletics\";s:0:\"\";s:5:\"hoops\";s:0:\"\";s:8:\"beanbags\";s:0:\"\";s:6:\"quoits\";s:0:\"\";s:11:\"tennisballs\";s:0:\"\";s:6:\"hockey\";s:0:\"\";}s:20:\"equipment_additional\";s:83:\"Limited equipment so any specific equipment such as pom poms bring from the office.\";s:16:\"further_comments\";s:443:\"Class 3 has a child that has speech and language difficulties so coach to be aware.\r\n\r\nBilly in class 1 has epilepsy the staff are all fully aware and trained to give assistance. Should the coach have any concerns at all contact a member of staff immediately.\r\n\r\nClass 4 have some children with challenging behavior so coach to be aware of this. \r\n\r\nAfter school club finish at 4 and the children need to be dressed and ready to leave at 4.15.\";s:12:\"venue_images\";N;s:10:\"map_images\";N;}','2016-04-15 14:02:46','2016-04-15 14:02:46'),(521,23,1865,2328,228,'risk assessment',0,'2016-01-13','2016-04-19','a:3:{s:8:\"location\";s:0:\"\";s:3:\"who\";s:22:\"Coach and Participants\";s:5:\"final\";s:52:\"Risk Assessment was completed with a member of staff\";}','2016-04-15 14:46:58','2016-05-26 17:16:00'),(522,23,1867,2330,226,'camp induction',0,'2015-04-16','2018-04-25','a:20:{s:8:\"location\";s:11:\"Main Office\";s:14:\"venue_contact1\";s:0:\"\";s:14:\"venue_contact2\";s:0:\"\";s:11:\"open_lockup\";s:257:\"Ensure that all patio doors are locked correctly (including in Training&#039;s Office). \r\nAll rubbish is appropriately cleared and chairs are left tucked under desks. \r\nUpon exiting turn off the lights, turn on the alarm and ensure you have locked the door.\";s:17:\"registration_area\";s:0:\"\";s:14:\"fire_procedure\";s:179:\"In the event of an emergency head out via the main office door, into the court yard or through the Training room. \r\n\r\nThe meeting point is located on the far side of the car park.\";s:14:\"indoor_toilets\";s:0:\"\";s:15:\"outdoor_toilets\";s:0:\"\";s:12:\"indoor_lunch\";s:0:\"\";s:13:\"outdoor_lunch\";s:0:\"\";s:15:\"indoor_activity\";s:0:\"\";s:16:\"outdoor_activity\";s:0:\"\";s:10:\"indoor_not\";s:0:\"\";s:11:\"outdoor_not\";s:0:\"\";s:18:\"accident_procedure\";s:96:\"All incidents need to be reported in the company accident book which Cameron is responsible for.\";s:9:\"equipment\";N;s:17:\"equipment_details\";a:17:{s:10:\"gymnastics\";s:0:\"\";s:8:\"football\";s:0:\"\";s:5:\"rugby\";s:0:\"\";s:10:\"basketball\";s:0:\"\";s:7:\"netball\";s:0:\"\";s:8:\"cdplayer\";s:0:\"\";s:6:\"tennis\";s:0:\"\";s:8:\"rounders\";s:0:\"\";s:7:\"cricket\";s:0:\"\";s:9:\"softballs\";s:0:\"\";s:5:\"cones\";s:0:\"\";s:9:\"athletics\";s:0:\"\";s:5:\"hoops\";s:0:\"\";s:8:\"beanbags\";s:0:\"\";s:6:\"quoits\";s:0:\"\";s:11:\"tennisballs\";s:0:\"\";s:6:\"hockey\";s:0:\"\";}s:20:\"equipment_additional\";s:0:\"\";s:12:\"venue_images\";N;s:10:\"map_images\";N;}','2016-04-18 12:19:35','2016-04-18 12:19:35'),(523,23,1867,2330,226,'risk assessment',0,'2016-02-10','2019-04-17','a:3:{s:8:\"location\";s:11:\"Main Office\";s:3:\"who\";s:9:\"All Staff\";s:5:\"final\";s:2:\"NA\";}','2016-04-18 12:21:30','2016-04-18 12:21:30'),(524,23,1868,2331,226,'camp induction',0,'2016-04-06','2019-04-23','a:20:{s:8:\"location\";s:11:\"Main Office\";s:14:\"venue_contact1\";s:0:\"\";s:14:\"venue_contact2\";s:0:\"\";s:11:\"open_lockup\";s:257:\"Ensure that all patio doors are locked correctly (including in Training&#039;s Office). \r\nAll rubbish is appropriately cleared and chairs are left tucked under desks. \r\nUpon exiting turn off the lights, turn on the alarm and ensure you have locked the door.\";s:17:\"registration_area\";s:0:\"\";s:14:\"fire_procedure\";s:179:\"In the event of an emergency head out via the main office door, into the court yard or through the Training room. \r\n\r\nThe meeting point is located on the far side of the car park.\";s:14:\"indoor_toilets\";s:0:\"\";s:15:\"outdoor_toilets\";s:0:\"\";s:12:\"indoor_lunch\";s:0:\"\";s:13:\"outdoor_lunch\";s:0:\"\";s:15:\"indoor_activity\";s:0:\"\";s:16:\"outdoor_activity\";s:0:\"\";s:10:\"indoor_not\";s:0:\"\";s:11:\"outdoor_not\";s:0:\"\";s:18:\"accident_procedure\";s:96:\"All incidents need to be reported in the company accident book which Cameron is responsible for.\";s:9:\"equipment\";N;s:17:\"equipment_details\";a:17:{s:10:\"gymnastics\";s:0:\"\";s:8:\"football\";s:0:\"\";s:5:\"rugby\";s:0:\"\";s:10:\"basketball\";s:0:\"\";s:7:\"netball\";s:0:\"\";s:8:\"cdplayer\";s:0:\"\";s:6:\"tennis\";s:0:\"\";s:8:\"rounders\";s:0:\"\";s:7:\"cricket\";s:0:\"\";s:9:\"softballs\";s:0:\"\";s:5:\"cones\";s:0:\"\";s:9:\"athletics\";s:0:\"\";s:5:\"hoops\";s:0:\"\";s:8:\"beanbags\";s:0:\"\";s:6:\"quoits\";s:0:\"\";s:11:\"tennisballs\";s:0:\"\";s:6:\"hockey\";s:0:\"\";}s:20:\"equipment_additional\";s:0:\"\";s:12:\"venue_images\";N;s:10:\"map_images\";N;}','2016-04-18 13:02:03','2016-04-18 13:02:03'),(525,23,1868,2331,226,'risk assessment',0,'2016-01-20','2019-04-17','a:3:{s:8:\"location\";s:11:\"Main Office\";s:3:\"who\";s:9:\"All Staff\";s:5:\"final\";s:2:\"NA\";}','2016-04-18 13:03:09','2016-04-18 13:03:09');
/*!40000 ALTER TABLE `app_orgs_safety` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_orgs_safety_hazards`
--

DROP TABLE IF EXISTS `app_orgs_safety_hazards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_orgs_safety_hazards` (
  `hazardID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `orgID` int(11) NOT NULL,
  `docID` int(11) NOT NULL,
  `byID` int(11) DEFAULT NULL,
  `hazard` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `potential_effect` text COLLATE utf8_unicode_ci,
  `likelihood` int(1) NOT NULL,
  `severity` int(1) NOT NULL,
  `risk` int(2) NOT NULL,
  `control_measures` text COLLATE utf8_unicode_ci NOT NULL,
  `residual_risk` int(1) NOT NULL,
  `added` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`hazardID`),
  KEY `fk_orgs_safety_hazards_docID` (`docID`),
  KEY `fk_orgs_safety_hazards_byID` (`byID`),
  KEY `fk_orgs_safety_hazards_orgID` (`orgID`),
  KEY `fk_orgs_safety_hazards_accountID` (`accountID`),
  CONSTRAINT `app_orgs_safety_hazards_ibfk_2` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `app_orgs_safety_hazards_ibfk_3` FOREIGN KEY (`docID`) REFERENCES `app_orgs_safety` (`docID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_orgs_safety_hazards_ibfk_4` FOREIGN KEY (`orgID`) REFERENCES `app_orgs` (`orgID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_orgs_safety_hazards_ibfk_5` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7203 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_orgs_safety_hazards`
--

LOCK TABLES `app_orgs_safety_hazards` WRITE;
/*!40000 ALTER TABLE `app_orgs_safety_hazards` DISABLE KEYS */;
INSERT INTO `app_orgs_safety_hazards` VALUES (3241,23,1864,512,224,'Main Hall - Piano','Children run into and stub toes causing bruising.',2,2,4,'Ensure area is coned off and children are aware.',2,'2016-04-15 13:06:56','2016-04-15 13:06:56'),(3242,23,1864,512,224,'Main Hall - Gym Equipment','Benches - potential hazard if activities are near the equipment.',2,2,4,'Ensure benches are safely pushed away to the side.',2,'2016-04-15 13:09:56','2016-04-15 13:09:56'),(3243,23,1864,512,224,'Main Hall - Boxes','Trip hazard causing bumps and bruising.',2,2,4,'Ensure pushed to the side and cornered off. Make children aware of them.',2,'2016-04-15 13:10:47','2016-04-15 13:10:47'),(3244,23,1864,512,224,'Main Hall - Tables and Chairs','Can cause bruising, cuts if made contact with or if they fall.',2,2,4,'Ensure cornered off and stacked correctly and safely.',2,'2016-04-15 13:12:37','2016-04-15 13:12:37'),(3245,23,1864,512,224,'Main Hall - Floating Lights','If knocked with ball could damage lights or fall in extreme circumstances.',1,3,3,'Warn children of dangers and to avoid causing accidents.\r\nAvoid using balls indoors.',1,'2016-04-15 13:14:49','2016-04-15 13:14:49'),(3246,23,1864,512,224,'Main Hall - Y Frame','Children may fall from bars if climbing causing severe accidents.',2,3,6,'Explain to all children potential dangers and have disciplinary action in place.',3,'2016-04-15 13:16:02','2016-04-15 13:16:02'),(3247,23,1864,512,224,'Playground - Benches','Slip hazard if climbed upon.',2,3,6,'Explain to all children potential dangers and have disciplinary action in place.',3,'2016-04-15 13:16:44','2016-04-15 13:16:44'),(3248,23,1864,512,224,'Playground - Stage area','Slip and trip hazard causing bruising and sprains.',2,3,6,'Warn the children and have disciplinary actions in place.',3,'2016-04-15 13:18:10','2016-04-15 13:18:10'),(3249,23,1862,515,224,'Main Hall - Piano','Children run into and stub toes causing bruising.',2,2,4,'Ensure area is coned off and children are aware.',2,'2016-04-15 13:31:20','2016-04-15 13:31:20'),(3250,23,1862,515,224,'Main Hall - Gym Equipment','Benches - potential hazard if activities are near the equipment.',2,2,4,'Ensure benches are safely pushed away to the side.',2,'2016-04-15 13:32:40','2016-04-15 13:32:40'),(3251,23,1862,515,224,'Main Hall - Boxes','Trip hazard causing bumps and bruising.',2,2,4,'Ensure pushed to the side and cornered off. Make children aware of them.',2,'2016-04-15 13:33:26','2016-04-15 13:33:26'),(3252,23,1862,515,224,'Main Hall - Tables and Chairs','Can cause bruising, cuts if made contact with or if they fall.',2,2,4,'Ensure cornered off and stacked correctly and safely.',2,'2016-04-15 13:34:16','2016-04-15 13:34:16'),(3253,23,1862,515,224,'Playground - Benches','Slip hazard if climbed upon.',2,3,6,'Warn the children and have disciplinary action in place.',3,'2016-04-15 13:38:26','2016-04-15 13:38:26'),(3254,23,1862,515,224,'Playground - Stage area','Slip and trip hazard causing bruising and sprains.',2,3,6,'Warn the children and have disciplinary actions in place.',3,'2016-04-15 13:39:16','2016-04-15 13:39:16'),(3255,23,1866,516,224,'Main Hall - Piano','Children run into and stub toes causing bruising.',2,2,4,'Ensure area is coned off and children are aware.',2,'2016-04-15 13:41:55','2016-04-15 13:41:55'),(3256,23,1866,516,224,'Main Hall - Gym Equipment','Benches - potential hazard if activities are near the equipment.',2,2,4,'Ensure benches are safely pushed away to the side.',2,'2016-04-15 13:42:29','2016-04-15 13:42:29'),(3257,23,1866,516,224,'Main Hall - Tables and Chairs','Can cause bruising, cuts if made contact with or if they fall.',2,2,4,'Ensure cornered off and stacked correctly and safely.',2,'2016-04-15 13:43:41','2016-04-15 13:43:41'),(3258,23,1866,516,224,'Playground - Benches','Slip hazard if climbed upon.',2,3,6,'Warn the children and have disciplinary action in place.',3,'2016-04-15 13:44:17','2016-04-15 13:44:17'),(3259,23,1866,516,224,'Playground - Stage area','Slip and trip hazard causing bruising and sprains.',2,3,6,'Warn the children and have disciplinary actions in place.',3,'2016-04-15 13:44:58','2016-04-15 13:44:58'),(3260,23,1863,518,224,'Main Hall - Piano','Children run into and stub toes causing bruising.',2,2,4,'Ensure area is coned off and children are aware.',2,'2016-04-15 13:50:49','2016-04-15 13:50:49'),(3261,23,1863,518,224,'Main Hall - Gym Equipment','Benches - potential hazard if activities are near the equipment.',2,2,4,'Ensure benches are safely pushed away to the side.',2,'2016-04-15 13:51:32','2016-04-15 13:51:32'),(3262,23,1863,518,224,'Main Hall - Y Frame','Children may fall from bars if climbing causing severe accidents.',2,3,6,'Explain to all children potential dangers and have disciplinary action in place.',3,'2016-04-15 13:52:23','2016-04-15 13:52:23'),(3263,23,1863,518,224,'Playground - Benches','Slip hazard if climbed upon.',2,3,6,'Warn the children and have disciplinary action in place.',3,'2016-04-15 13:53:03','2016-04-15 13:53:03'),(3264,23,1863,518,224,'Playground - Stage area','Slip and trip hazard causing bruising and sprains.',2,3,6,'Warn the children and have disciplinary actions in place.',3,'2016-04-15 13:53:41','2016-04-15 13:53:41'),(3265,23,1865,521,224,'Main Hall - Piano','Children run into and stub toes causing bruising.',2,2,4,'Ensure area is coned off and children are aware.',2,'2016-04-15 14:48:21','2016-04-15 14:48:21'),(3266,23,1865,521,224,'Main Hall - Gym Equipment','Benches - potential hazard if activities are near the equipment.',2,2,4,'Ensure benches are safely pushed away to the side.',2,'2016-04-15 14:49:13','2016-04-15 14:49:13'),(3267,23,1865,521,224,'Main Hall - Boxes','Trip hazard causing bumps and bruising.',2,2,4,'Ensure pushed to the side and cornered off. Make children aware of them.',2,'2016-04-15 14:49:46','2016-04-15 14:49:46'),(3268,23,1865,521,224,'Main Hall - Tables and Chairs','Can cause bruising, cuts if made contact with or if they fall.',2,2,4,'Ensure cornered off and stacked correctly and safely.',2,'2016-04-15 14:50:26','2016-04-15 14:50:26'),(3269,23,1865,521,224,'Playground - Benches','Slip hazard if climbed upon.',2,3,6,'Warn the children and have disciplinary action in place.',3,'2016-04-15 14:51:04','2016-04-15 14:51:04'),(3270,23,1865,521,224,'Playground - Stage area','Slip and trip hazard causing bruising and sprains.',2,3,6,'Warn the children and have disciplinary actions in place.',3,'2016-04-15 14:51:46','2016-04-15 14:51:46'),(3271,23,1867,523,224,'Main Office - Desks','Potential collision or trip hazard resulting in minor injuries such as bruises.',2,2,4,'All staff to be considerate of the corner of desks.',2,'2016-04-18 12:22:16','2016-04-18 12:22:16'),(3272,23,1867,523,224,'Main Office - Chairs','Potential trip, collision or fall hazard resulting in minor bruising or grazes to the head or body.',2,2,4,'Users to ensure the office chairs are used properly. After use chairs are to be tucked under desks tidily.',2,'2016-04-18 12:22:50','2016-04-18 12:22:50'),(3273,23,1867,523,224,'Main Office - Wires','Potential trip hazard resulting in minor injuries to the head or body.',2,2,4,'Ensure all wires suitably stored using cable ties. If power cables are used these should run along the floor as not to cause an additional trip hazard.',2,'2016-04-18 12:23:29','2016-04-18 12:23:29'),(3274,23,1867,523,224,'Main Office - Glass Doors and Walls','Potential collision hazard result in bruising to the head or body.\r\n\r\nPotential to smash if knocked into at force causing cuts to the head or body.',2,5,10,'All staff to be careful when walking around the office. When moving objects around the office staff to do so with care.',5,'2016-04-18 12:24:13','2016-04-18 12:24:13'),(3275,23,1868,525,224,'Main Office - Desks','Potential collision or trip hazard resulting in minor injuries such as bruises.',2,2,4,'All staff to be considerate of the corner of desks.',2,'2016-04-18 13:03:57','2016-04-18 13:03:57'),(3276,23,1868,525,224,'Main Office - Chairs','Potential trip, collision or fall hazard resulting in minor bruising or grazes to the head or body.',2,2,4,'Users to ensure the office chairs are used properly. After use chairs are to be tucked under desks tidily.',2,'2016-04-18 13:04:42','2016-04-18 13:04:42'),(3277,23,1868,525,224,'Main Office - Wires','Potential trip hazard resulting in minor injuries to the head or body.',2,2,4,'Ensure all wires suitably stored using cable ties. If power cables are used these should run along the floor as not to cause an additional trip hazard.',2,'2016-04-18 13:05:28','2016-04-18 13:05:28'),(3278,23,1868,525,224,'Main Office - Glass Doors and Walls','Potential collision hazard result in bruising to the head or body.\r\n\r\nPotential to smash if knocked into at force causing cuts to the head or body.',2,5,10,'All staff to be careful when walking around the office. When moving objects around the office staff to do so with care.',5,'2016-04-18 13:06:34','2016-04-18 13:06:34'),(4153,23,1864,512,235,'test','test',3,3,3,'test',3,'2016-06-03 16:37:04','2016-06-03 16:37:04');
/*!40000 ALTER TABLE `app_orgs_safety_hazards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_orgs_safety_read`
--

DROP TABLE IF EXISTS `app_orgs_safety_read`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_orgs_safety_read` (
  `readID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `docID` int(11) NOT NULL,
  `staffID` int(11) DEFAULT NULL,
  `outdated` tinyint(1) NOT NULL DEFAULT '0',
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`readID`),
  KEY `fk_orgs_safety_read_docID` (`docID`),
  KEY `fk_orgs_safety_read_staffID` (`staffID`),
  KEY `fk_orgs_safety_read_accountID` (`accountID`),
  CONSTRAINT `app_orgs_safety_read_ibfk_1` FOREIGN KEY (`staffID`) REFERENCES `app_staff` (`staffID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_orgs_safety_read_ibfk_2` FOREIGN KEY (`docID`) REFERENCES `app_orgs_safety` (`docID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_orgs_safety_read_ibfk_3` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4710 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_orgs_safety_read`
--

LOCK TABLES `app_orgs_safety_read` WRITE;
/*!40000 ALTER TABLE `app_orgs_safety_read` DISABLE KEYS */;
INSERT INTO `app_orgs_safety_read` VALUES (4322,23,512,225,1,'2016-04-26 12:12:23'),(4323,23,513,225,1,'2016-04-26 12:18:54'),(4382,23,512,225,0,'2016-07-11 11:27:21'),(4639,23,515,225,0,'2017-09-28 14:21:15');
/*!40000 ALTER TABLE `app_orgs_safety_read` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_orgs_tags`
--

DROP TABLE IF EXISTS `app_orgs_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_orgs_tags` (
  `linkID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL,
  `orgID` int(11) NOT NULL,
  `tagID` int(11) NOT NULL,
  `added` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`linkID`),
  KEY `accountID` (`accountID`),
  KEY `orgID` (`orgID`),
  KEY `tagID` (`tagID`),
  CONSTRAINT `app_orgs_tags_ibfk_1` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_orgs_tags_ibfk_2` FOREIGN KEY (`orgID`) REFERENCES `app_orgs` (`orgID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_orgs_tags_tagID` FOREIGN KEY (`tagID`) REFERENCES `app_settings_tags` (`tagID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=137 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_orgs_tags`
--

LOCK TABLES `app_orgs_tags` WRITE;
/*!40000 ALTER TABLE `app_orgs_tags` DISABLE KEYS */;
/*!40000 ALTER TABLE `app_orgs_tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_permission_levels`
--

DROP TABLE IF EXISTS `app_permission_levels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_permission_levels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL,
  `byID` int(11) DEFAULT NULL,
  `department` enum('coaching','office','management','directors','headcoach','fulltimecoach') COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `added` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `accountID` (`accountID`),
  KEY `byID` (`byID`),
  CONSTRAINT `fk_permission_levels_accountID` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_permission_levels_byID` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_permission_levels`
--

LOCK TABLES `app_permission_levels` WRITE;
/*!40000 ALTER TABLE `app_permission_levels` DISABLE KEYS */;
/*!40000 ALTER TABLE `app_permission_levels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_project_types`
--

DROP TABLE IF EXISTS `app_project_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_project_types` (
  `typeID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL,
  `byID` int(11) DEFAULT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `added` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`typeID`),
  KEY `accountID` (`accountID`),
  KEY `byID` (`byID`),
  CONSTRAINT `app_project_types_ibfk_1` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_project_types_ibfk_2` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=456 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_project_types`
--

LOCK TABLES `app_project_types` WRITE;
/*!40000 ALTER TABLE `app_project_types` DISABLE KEYS */;
INSERT INTO `app_project_types` VALUES (1,10,NULL,'Commercial','2016-05-23 13:36:18','2016-05-23 13:36:18'),(2,10,NULL,'Funded','2016-05-23 13:36:18','2016-05-23 13:36:18'),(11,23,NULL,'Commercial','2016-05-23 13:36:18','2016-05-23 13:36:18'),(12,23,NULL,'Funded','2016-05-23 13:36:18','2016-05-23 13:36:18');
/*!40000 ALTER TABLE `app_project_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_sessions`
--

DROP TABLE IF EXISTS `app_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_sessions` (
  `id` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8 NOT NULL,
  `timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `data` blob NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ci_sessions_timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_sessions`
--

LOCK TABLES `app_sessions` WRITE;
/*!40000 ALTER TABLE `app_sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `app_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_settings`
--

DROP TABLE IF EXISTS `app_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_settings` (
  `key` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `type` enum('text','textarea','number','email','email-multiple','wysiwyg','staff','select','image','checkbox','brand','url','tel','html','css') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'text',
  `section` enum('general','styling','global','emailsms','dashboard','integrations') COLLATE utf8_unicode_ci NOT NULL,
  `order` int(5) DEFAULT NULL,
  `options` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` text COLLATE utf8_unicode_ci NOT NULL,
  `instruction` text COLLATE utf8_unicode_ci,
  `max_height` int(11) DEFAULT NULL,
  `max_width` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_settings`
--

LOCK TABLES `app_settings` WRITE;
/*!40000 ALTER TABLE `app_settings` DISABLE KEYS */;
INSERT INTO `app_settings` VALUES ('address','Address','text','general',70,'','','Shown on Coach ID',0,0,'2015-10-04 10:36:52','2017-09-15 14:50:25'),('body_colour','Body Colour','select','styling',10,'white : White\r\nred : Red\r\nblue : Blue\r\norange : Burnt Orange\r\npurple : Purple\r\ngreen : Green\r\nmuted : Muted\r\nfb : Facebook Blue\r\ndark : Dark\r\npink : Muave\r\ngrass-green : Grass Green\r\nbanana : Banana\r\ndark-orange : Dark Orange\r\nbrown : Brown','white','',0,0,'2015-04-14 16:01:02','2017-09-15 14:50:25'),('booking_cutoff','Online Booking Cut Off','number','general',370,'','24','In hours. Applies to online booking only',NULL,NULL,'2017-12-05 18:25:36',NULL),('cc_processor','Credit/Debit Card Processor','select','integrations',120,'stripe : Stripe\r\nsagepay : Sage Pay','','For taking payments online',NULL,NULL,'2017-09-03 19:45:28','2017-09-15 14:50:25'),('childcare_voucher_instruction','Childcare Voucher Instruction','textarea','general',360,'','Please note, your balance will remain outstanding until the childcare vouchers are received/activated (this can take several working days).','Shown on online booking once childcare voucher option selected',NULL,NULL,'2017-09-03 19:45:29','2017-09-15 14:50:25'),('company','Company','text','global',0,'','Coordinate','',0,0,'2014-01-21 23:23:35','2017-09-15 14:50:25'),('company_support_link','Support Link','text','global',0,'','http://coordinate.cloud/','',0,0,'2015-09-08 08:14:48','2017-09-15 14:50:25'),('company_website','Web Site','url','global',0,'','http://coordinate.cloud/','',0,0,'2015-09-08 08:14:48','2017-09-15 14:50:25'),('contrast_colour','Contrast Colour','select','styling',10,'light : Light\r\ndark : Dark\r\ndark-blue : Dark Blue','dark','',0,0,'2015-04-14 16:01:02','2017-09-15 14:50:25'),('dashboard_custom_widget_1_html','Custom Widget 1 HTML','html','dashboard',2,'','','',NULL,NULL,'2017-08-24 18:06:29','2017-09-15 14:50:25'),('dashboard_custom_widget_1_title','Custom Widget 1 Title','text','dashboard',1,'','','',NULL,NULL,'2017-08-24 18:06:29','2017-09-15 14:50:25'),('dashboard_custom_widget_2_html','Custom Widget 1 HTML','html','dashboard',4,'','','',NULL,NULL,'2017-08-24 18:06:29','2017-09-15 14:50:25'),('dashboard_custom_widget_2_title','Custom Widget 1 Title','text','dashboard',3,'','','',NULL,NULL,'2017-08-24 18:06:29','2017-09-15 14:50:25'),('dashboard_custom_widget_3_html','Custom Widget 1 HTML','html','dashboard',6,'','','',NULL,NULL,'2017-08-24 18:06:29','2017-09-15 14:50:25'),('dashboard_custom_widget_3_title','Custom Widget 1 Title','text','dashboard',5,'','','',NULL,NULL,'2017-08-24 18:06:29','2017-09-15 14:50:25'),('email','Email','email','general',50,'','','Shown on Coach ID',0,0,'2015-10-04 10:37:19','2017-09-15 14:50:25'),('email_birthday_email','Birthday Email','wysiwyg','emailsms',152,'','<p>Happy Birthday {first_name}!</p>','Available Tags: {first_name}, {age}',0,0,'2014-08-28 14:30:15','2017-09-15 14:50:25'),('email_birthday_email_brand','Birthday Email Brand','brand','emailsms',153,'','','Determines the logo sent with the birthday email',0,0,'2015-10-03 17:39:41','2017-09-15 14:50:25'),('email_birthday_email_image','Birthday Email Image','image','emailsms',154,'','a:5:{s:4:\"name\";s:28:\"CoordinateFullcolourLogo.jpg\";s:4:\"path\";s:32:\"atnw4SWBJi8xCrsvjpqTLZfNQmIlKhV0\";s:4:\"type\";s:10:\"image/jpeg\";s:4:\"size\";d:98785.279999999999;s:3:\"ext\";s:3:\"jpg\";}','',600,600,'2015-10-03 17:39:41','2017-05-23 14:25:43'),('email_birthday_email_subject','Birthday Email Subject','text','emailsms',151,'','Happy Birthday {first_name}!','Available Tags: {first_name}, {age}',0,0,'2014-08-28 14:30:15','2017-09-15 14:50:25'),('email_customer_booking_confirmation','Customer Booking Confirmation','wysiwyg','emailsms',175,'','<p>Hi {contact_name},</p>\r\n<p>Thank you for your booking from {org_name} for {block_name} {date_description}. Please find below details of the sessions booked:</p>\r\n<p>{details}</p>\r\n<p>Please check all details thoroughly, should you notice any mistakes to your booking or would like to make an amendment please contact a member of the team.</p>','Available tags: {contact_name}, {org_name}, {block_name}, {date_description}, {details}',NULL,NULL,'2017-04-22 11:45:01','2017-09-15 14:50:25'),('email_customer_booking_confirmation_subject','Customer Booking Confirmation Subject','text','emailsms',174,'','Booking confirmation for {block_name}','Available tags: {contact_name}, {org_name}, {block_name}, {date_description}',NULL,NULL,'2017-04-22 11:45:01','2017-09-15 14:50:25'),('email_customer_booking_notification','Customer Booking Notification','wysiwyg','emailsms',173,'','<p>Hello,</p>\r\n<p>{org_name} has just made an online booking for the block: {block_name}.</p>\r\n<p>You can view and edit this block at: {block_link}</p>','Available tags: {org_name}, {block_name}, {block_link}',NULL,NULL,'2017-02-09 17:53:46','2017-09-15 14:50:25'),('email_customer_booking_notification_subject','Customer Booking Notification Subject','text','emailsms',172,'','New customer booking from {org_name}','Available tags: {org_name}, {block_name}, {block_link}',NULL,NULL,'2017-02-09 17:53:46','2017-09-15 14:50:25'),('email_customer_booking_notification_to','Send Customer Booking Notifications To','text','emailsms',170,'','','',NULL,NULL,'2017-02-09 17:53:46','2017-09-15 14:50:25'),('email_customer_password','Customer Contact Welcome Email','wysiwyg','emailsms',241,'','<p>Hi {contact_name},</p>\r\n<p>Here are your login details for your new account for {org_name}:</p>\r\n<p>Email Address: {contact_email}<br>\r\nPassword: {password}</p>','Available tags: {contact_name}, {contact_email}, {org_name}, {password}, {company}',NULL,NULL,'2017-04-21 19:12:40','2017-09-15 14:50:25'),('email_customer_password_subject','Customer Contact Welcome Email Subject','text','emailsms',240,'','Welcome to {company}','Available tags: {contact_name}, {company}',NULL,NULL,'2017-04-21 19:12:40','2017-09-15 14:50:25'),('email_event_confirmation','Event Booking Confirmation for New Events','wysiwyg','emailsms',11,'','<p>Hi {contact_first},</p>\r\n<p>Thank you for your booking on {event_name}.</p>\r\n<p>Location: {location}</p>\r\n<p>{details}</p>\r\n<p>Please check everything and if anything is incorrect, please contact us.</p>','Available tags: {contact_title}, {contact_first}, {contact_last}, {event_name}, {date_description}, {details}, {website}, {location}',0,0,'2014-03-26 21:18:05','2017-09-15 14:50:25'),('email_event_confirmation_bcc','Send a Copy of Event Booking Confirmations to','email-multiple','emailsms',12,'','','',NULL,NULL,'2017-12-22 17:58:40',NULL),('email_event_confirmation_subject','Event Booking Confirmation Subject','text','emailsms',10,'','Booking Confirmation for {event_name}','Available tags: {contact_title}, {contact_first}, {contact_last}, {event_name}, {date_description}',0,0,'2014-08-27 09:42:57','2017-09-15 14:50:25'),('email_event_thanks','Event Thanks','wysiwyg','emailsms',21,'','<p>Hi {contact_first},</p>\r\n<p>We would like to thank you for coming to {event_name}.</p>\r\n<p>Our coaches had fun, but it\'s more important to know your children have as much fun as we do!</p>\r\n<p>If you haven\'t had a chance to leave us feedback and would like to please follow this <a href=\"http://kids.firststep-sports.co.uk/?p=211\">link</a>&nbsp;to complete a quick survey to tell us how we performed. We value what our users have to say and your feedback will help us to improve our services to you.</p>\r\n<p>We hope to see you again next time! You can manage all your bookings in advance and online. Just follow this link and log in to your account {website}</p>','Available tags: {contact_title}, {contact_first}, {contact_last}, {event_name}, {website}',0,0,'2014-03-26 21:18:05','2017-09-15 14:50:25'),('email_event_thanks_subject','Event Thanks Subject','text','emailsms',20,'','Thanks for coming to {event_name}!','Available tags: {contact_title}, {contact_first}, {contact_last}, {event_name}',0,0,'2014-08-27 09:42:57','2017-09-15 14:50:25'),('email_exception_bulk_cancellation','Exception - Bulk Cancellation','wysiwyg','emailsms',101,'','<p>Dear {main_contact},</p>\r\n<p>I am writing to confirm that we have <span data-dobid=\"hdw\">unfortunately</span> had to cancel some of your lessons. Please see the details below for the lesson that this applies:</p>\r\n<p>{details}</p>\r\n<p>If you have any queries please do not hesitate to contact us.</p>','Available Tags: {main_contact}, {details}',0,0,'2014-08-27 22:28:29','2017-09-15 14:50:25'),('email_exception_bulk_cancellation_subject','Exception - Bulk Cancellation Subject','text','emailsms',100,'','Confirmation of Cancellation','Available Tags: {main_contact}',0,0,'2014-08-27 22:28:29','2017-09-15 14:50:25'),('email_exception_bulk_staffchange','Exception - Bulk Staff Change','wysiwyg','emailsms',91,'','<p>Dear {main_contact},</p>\r\n<p>I am writing to confirm that we have had to make a slight change to the staff members who usually deliver some of your lessons. Please see the details below for the lessons that this applies:</p>\r\n<p>{details}</p>\r\n<p>All coaches will have their ID badge with them on the day, however we have detailed below some useful details about the replacement staff:</p>\r\n<p>{dbs_details}</p>\r\n<p>We can confirm no disruption to the delivery of your lessons will occur, however if you have any queries please do not hesitate to contact us.</p>','Available Tags: {main_contact}, {details}, {dbs_details}',0,0,'2014-08-27 22:28:29','2017-09-15 14:50:25'),('email_exception_bulk_staffchange_subject','Exception - Bulk Staff Change Subject','text','emailsms',90,'','Confirmation of Change of Coach','Available Tags: {main_contact}',0,0,'2014-08-27 22:28:29','2017-09-15 14:50:25'),('email_exception_company_cancellation','Exception - Company Cancellation','wysiwyg','emailsms',61,'','<p>Dear {main_contact},</p>\r\n<p>I am writing to confirm that we have <span data-dobid=\"hdw\">unfortunately</span> had to cancel one of your lessons. Please see the details below for the lesson that this applies:</p>\r\n<p>{details}</p>\r\n<p>If you have any queries please do not hesitate to contact us.</p>','Available Tags: {main_contact}, {details}',0,0,'2014-08-27 22:28:29','2017-09-15 14:50:25'),('email_exception_company_cancellation_subject','Exception - Company Cancellation Subject','text','emailsms',60,'','Confirmation of Cancellation','Available Tags: {main_contact}',0,0,'2014-08-27 22:28:29','2017-09-15 14:50:25'),('email_exception_company_staffchange','Exception - Company Staff Change','wysiwyg','emailsms',51,'','<p>Dear {main_contact},</p>\r\n<p>I am writing to confirm that we have had to make a slight change to the staff member who usually deliver some of your lessons. Please see the details below for the lesson that this applies:</p>\r\n<p>{details}</p>\r\n<p>All coaches will have their ID badge with them on the day, however we have detailed below some useful details about the replacement staff:</p>\r\n<p>{dbs_details}</p>\r\n<p>We can confirm no disruption to the delivery of your lessons will occur, however if you have any queries please do not hesitate to contact us.</p>','Available Tags: {main_contact}, {details}, {dbs_details}',0,0,'2014-08-27 22:28:29','2017-09-15 14:50:25'),('email_exception_company_staffchange_subject','Exception - Company Staff Change Subject','','emailsms',50,'','Confirmation of Change of Coach','Available Tags: {main_contact}',0,0,'2014-08-27 22:28:29','2017-09-15 14:50:25'),('email_exception_customer_cancellation','Exception - Customer Cancellation','wysiwyg','emailsms',81,'','<p>Dear {main_contact},</p>\r\n<p>Thank you for informing us of the change to your scheduled lesson. Please find below confirmation of the change you have made.</p>\r\n<p>{details}</p>\r\n<p>Please note we require 2 weeks\' notice for the cancellation of lessons.</p>\r\n<p>If you have any queries please do not hesitate to contact us.</p>','Available Tags: {main_contact}, {details}',0,0,'2014-08-27 22:28:29','2017-09-15 14:50:25'),('email_exception_customer_cancellation_subject','Exception - Customer Cancellation Subject','text','emailsms',80,'','Confirmation of Cancellation','Available Tags: {main_contact}',0,0,'2014-08-27 22:28:29','2017-09-15 14:50:25'),('email_exception_customer_staffchange','Exception - Customer Staff Change','wysiwyg','emailsms',71,'','<p>Dear {main_contact},</p>\r\n<p>Thank you for informing us of the change to your scheduled lesson. Please find below confirmation of the change you have made.</p>\r\n<p>{details}</p>\r\n<p>All coaches will have their ID badge with them on the day, however we have detailed below some useful details about the replacement staff:</p>\r\n<p>{dbs_details}</p>\r\n<p>We can confirm no disruption to the delivery of your lessons will occur, however if you have any queries please do not hesitate to contact us.</p>','Available Tags: {main_contact}, {details}, {dbs_details}',0,0,'2014-08-27 22:28:29','2017-09-15 14:50:25'),('email_exception_customer_staffchange_subject','Exception - Customer Staff Change Subject','text','emailsms',70,'','Confirmation of Change of Coach','Available Tags: {main_contact}',0,0,'2014-08-27 22:28:29','2017-09-15 14:50:25'),('email_footer','Email Footer','wysiwyg','emailsms',135,'','<p>Kind Regards,</p>\r\n<p>{company}</p>\r\n<p><strong>Disclaimer:</strong> This email and any files transmitted with it are confidential and intended solely for the use of the individual or entity to whom they are addressed. Any opinions presented do not necessarily represent those of {company}. If you are not the intended recipient, please notify the author immediately by email and delete all copies of the email on your system. Any unauthorised disclosure of information contained in this communication is strictly prohibited.</p>','Available tags: {company}',0,0,'2014-04-04 21:29:47','2017-09-15 14:50:25'),('email_from','Send Emails From Address','email','emailsms',1,'','support@coordinate.cloud','',0,0,'2015-10-04 09:20:39','2017-09-15 14:50:25'),('email_from_name','Send Emails From Name','text','emailsms',0,'','Coordinate','',0,0,'2015-10-04 09:20:39','2017-09-15 14:50:25'),('email_gocardless_mandate','GoCardless Subscription Link','wysiwyg','emailsms',165,'','<p>Hi {contact_first},</p>\r\n<p>Thank you for requesting to pay by direct debit for {event_name}.</p>\r\n<p>Please click the following link to set up your direct debit securely:</p>\r\n<p><a href=\"{mandate_link}\">Set up my Direct Debit</a></p>','Available tags: {contact_first}, {contact_last}, {event_name}, {mandate_link}',NULL,NULL,'2017-10-24 17:59:56',NULL),('email_gocardless_mandate_subject','GoCardless Mandate Link Subject','text','emailsms',164,'','Complete your payment plan set up','Available tags: {contact_first}, {contact_last}, {event_name}',NULL,NULL,'2017-10-24 17:59:56',NULL),('email_gocardless_payment','GoCardless Payment Confirmation','wysiwyg','emailsms',163,'','<p>Hi {contact_first},</p>\r\n<p>Thank you for your recent payment of {amount} by direct debit towards {event_name}.</p>\r\n<p>It has now been applied to your account and your remaining balance is: {balance}</p>','Available tags: {contact_first}, {contact_last}, {event_name}, {amount}, {balance}',NULL,NULL,'2016-03-09 11:56:18','2017-09-15 14:50:25'),('email_gocardless_payment_subject','GoCardless Payment Confirmation Subject','text','emailsms',162,'','Thank you for your payment','Available tags: {contact_first}, {contact_last}, {event_name}, {amount}, {balance}',NULL,NULL,'2016-03-09 11:56:18','2017-09-15 14:50:25'),('email_gocardless_subscription','GoCardless Subscription Confirmation','wysiwyg','emailsms',161,'','<p>Hi {contact_first},</p>\r\n<p>Thank you for requesting to pay by direct debit for {event_name}.</p>\r\n<p>Your plan will consist of {details}</p>','Available tags: {contact_first}, {contact_last}, {event_name}, {details}',NULL,NULL,'2016-03-09 11:56:18','2017-09-15 14:50:25'),('email_gocardless_subscription_subject','GoCardless Subscription Confirmation Subject','text','emailsms',160,'','Payment plan confirmation','Available tags: {contact_first}, {contact_last}, {event_name}',NULL,NULL,'2016-03-09 11:56:18','2017-09-15 14:50:25'),('email_new_booking','New Booking','wysiwyg','emailsms',31,'','<p>Dear {contact_name},</p>\r\n<p>Thank you for booking with {brand} for {org_name} {date_description}.</p>\r\n<p>Please see below a detailed summary of the lessons you have booked and any information you will need should you have a query or would like to make any amendments to your booking.</p>\r\n<p>{details}</p>\r\n<p>Please check all the above details match your booking requirements and inform us as soon as possible should you notice any discrepancies or are required to make changes.</p>\r\n<p>Please do not hesitate to contact us if you have any queries.</p>\r\n<p>We look forward to continuing working closely with you.</p>','Available tags: {contact_name}, {org_name}, {brand}, {date_description}, {details}',0,0,'2014-01-23 23:22:03','2017-09-15 14:50:25'),('email_new_booking_subject','New Booking Subject','text','emailsms',30,'','Booking Confirmation','Available tags: {org_name}, {date_description}',0,0,'2014-08-27 09:44:00','2017-09-15 14:50:25'),('email_new_participant','Participant Welcome Email','wysiwyg','emailsms',221,'','<p>Hi {contact_first},</p>\r\n<p>Here are your login details for your new account:</p>\r\n<p>Email Address: {contact_email}<br>\r\nPassword: {password}</p>','Available tags: {contact_title}, {contact_first}, {contact_last}, {contact_email}, {password}, {company}',NULL,NULL,'2017-12-05 18:25:37',NULL),('email_new_participant_subject','Participant Welcome Email Subject','text','emailsms',220,'','Welcome to {company}','Available tags: {contact_title}, {contact_first}, {contact_last}, {company}',NULL,NULL,'2017-12-05 18:25:37',NULL),('email_new_staff','Staff Welcome Email','wysiwyg','emailsms',231,'','<p>Hi {staff_first},</p>\r\n<p>Please find your login details for the Coordinate system below:</p>\r\n<p>Email Address: {staff_email}<br>\r\nPassword: {password}</p>\r\n<p>You can login to your account at {login_link} using either your computer, tablet or mobile phone.</p>\r\n<p><strong><u>I need help or something looks wrong in my Dashboard?</u></strong></p>\r\n<p>If you require assistance or have questions about what\'s appearing in your Dashboard, then in the first instance you should contact the Super User(s) or the administrators of the system in your organisation as follows:</p>\r\n<p>{admins}</p>\r\n<p><strong><u>How do I change my password?</u></strong></p>\r\n<p>You can change your password when logged into the portal by clicking on your name at the top right-hand corner of the page and then clicking \"Change Password\".</p>\r\n<p>Thank you for your time and attention.</p>','Available tags: {staff_first}, {staff_last}, {staff_email}, {password}, {company}, {login_link}, {admins}',NULL,NULL,'2017-12-05 18:25:37',NULL),('email_new_staff_subject','Staff Welcome Email Subject','text','emailsms',230,'','Welcome to {company}','Available tags: {staff_first}, {staff_last}, {company}',NULL,NULL,'2017-12-05 18:25:37',NULL),('email_offer_accept_accepted','Offer/Accept - Offer Accepted','wysiwyg','emailsms',204,'','<p>Hello,</p>\r\n<p>One or more session offers have been accepted:</p>\r\n<p>{details}</p>\r\n<p>Please go to the link below to view details:</p>\r\n<p>{link}</p>','Available tags: {details}, {link}',NULL,NULL,'2017-05-31 06:56:14','2017-09-15 14:50:25'),('email_offer_accept_accepted_subject','Offer/Accept - Offer Accepted Subject','text','emailsms',203,'','Sessions Accepted','',NULL,NULL,'2017-05-31 06:56:14','2017-09-15 14:50:25'),('email_offer_accept_declined','Offer/Accept - Offer Declined','wysiwyg','emailsms',206,'','<p>Hello,</p>\r\n<p>One or more session offers have been declined:</p>\r\n<p>{details}</p>\r\n<p>Please go to the link below to view details:</p>\r\n<p>{link}</p>','Available tags: {details}, {link}',NULL,NULL,'2017-05-31 06:56:14','2017-09-15 14:50:25'),('email_offer_accept_declined_subject','Offer/Accept - Offer Declined Subject','text','emailsms',205,'','Sessions Declined','',NULL,NULL,'2017-05-31 06:56:14','2017-09-15 14:50:25'),('email_offer_accept_exhausted','Offer/Accept - Offers Exhausted','wysiwyg','emailsms',208,'','<p>Hello,</p>\r\n<p>The following session has exhausted all available staffing options:</p>\r\n<p>{details}</p>\r\n<p>View Lesson Staff: {link}</p>','Available tags: {details}, {link}',NULL,NULL,'2017-05-31 06:56:14','2017-09-15 14:50:25'),('email_offer_accept_exhausted_subject','Offer/Accept - Offers Exhausted Subject','text','emailsms',207,'','Offer Exhausted','',NULL,NULL,'2017-05-31 06:56:14','2017-09-15 14:50:25'),('email_offer_accept_notifications_to','Offer/Accept - Send Notifications To','email','emailsms',202,'','','Notifications of accepted/rejected session offers will be sent here',NULL,NULL,'2017-05-31 06:56:14','2017-09-15 14:50:25'),('email_offer_accept_offer','Offer/Accept - Offer Session','wysiwyg','emailsms',201,'','<p>Hello {first_name},</p>\r\n<p>You have been invited to teach on one or more sessions. Please go to the link below to accept or decline them:</p>\r\n<p>{link}</p>','Available tags: {first_name}, {link}',NULL,NULL,'2017-05-31 06:56:14','2017-09-15 14:50:25'),('email_offer_accept_offer_subject','Offer/Accept - Offer Session Subject','text','emailsms',200,'','New Sessions Offered','Available tags: {first_name}',NULL,NULL,'2017-05-31 06:56:14','2017-09-15 14:50:25'),('email_participant_reset_password','Participant Reset Password Email','wysiwyg','emailsms',223,'','<p>Hi {contact_first},</p>\r\n<p>As requested, your password has been reset. Your new password is: {password}</p>\r\n<p>Please visit {login_link} to login to your account.</p>','Available tags: {contact_title}, {contact_first}, {contact_last}, {contact_email}, {password}, {company}, {login_link}',NULL,NULL,'2018-01-17 18:30:57',NULL),('email_participant_reset_password_subject','Participant Reset Password Email Subject','text','emailsms',222,'','New Password for {company}','Available tags: {contact_title}, {contact_first}, {contact_last}, {company}',NULL,NULL,'2018-01-17 18:30:57',NULL),('email_payment_reminder_after','Payment Reminder - 1 Week After','wysiwyg','emailsms',121,'','<p>Hi {contact_first},</p>\r\n<p>Oops you\'re account has an outstanding balance. If you have made a recent payment please contact us so we can rectify your account.</p>','Available Tags: {contact_first}, {amount}, {event_name}',0,0,'2014-08-28 14:30:15','2017-09-15 14:50:25'),('email_payment_reminder_after_subject','Payment After - 1 Week After Subject','text','emailsms',120,'','Payment Overdue','Available Tags: {contact_first}, {amount}, {event_name}',0,0,'2014-08-28 14:30:15','2017-09-15 14:50:25'),('email_payment_reminder_before','Payment Reminder - 1 Week Before','wysiwyg','emailsms',111,'','<p>Hi {contact_first},</p>\r\n<p>It\'s less than a week left before the {event_name} starting on {start_date}, please see below a reminder of your booking and account:</p>\r\n<p>{sessions}</p>\r\n<p>Outstanding: &pound;{amount}</p>\r\n<p>If your account is outstanding and you will be making a payment by card or childcare vouchers please process your payment before the end of the week to ensure your child\'s place is secure.</p>\r\n<p>We look forward to seeing {childrens_names} there.</p>','Available Tags: {contact_first}, {amount}, {event_name}, {start_date}, {sessions}',0,0,'2014-08-28 14:30:15','2017-09-15 14:50:25'),('email_payment_reminder_before_subject','Payment Reminder - 1 Week Before Subject','text','emailsms',110,'','{event_name} Starts Soon','Available Tags: {contact_first}, {amount}, {event_name}, {start_date}',0,0,'2014-08-28 14:30:15','2017-09-15 14:50:25'),('email_renewal_reminder','Contract Renewal Reminder','wysiwyg','emailsms',131,'','<p>Dear {main_contact},</p>\r\n<p>We hope the lessons we have been delivering for your school and children have been a success.&nbsp;We would like to remind you that the deadline for contacting us to discuss your requirements for next year is fast approaching.</p>\r\n<p>Your contract for {date_description} is due to renew in {reminder_period} on {renewal_date} and we haven\'t yet set a review meeting to discuss the service we have provided so far.</p>\r\n<p><strong>What do I need to do?</strong></p>\r\n<ol>\r\n<li>If you would like your booking to continue as it is please inform us you will be continuing your lessons. &nbsp;This will secure your current booking slots and prompt the team to arrange a mapping meeting if necessary.</li>\r\n<li>If you would like your booking to continue, but have some changes to make&nbsp;please contactour office manager who will be happy to discuss and confirm these amendments with you.</li>\r\n<li>If for any reason you do not want to continue using our service, or discuss any concerns please put this in writing and send to our Head Office, or contact a member of the team to discuss. This will prevent your contract from automatically renewing as it currently stands. If you let us know you would like to make changes but are unsure of what they are, we can arrange a convenient time to discuss.</li>\r\n</ol>\r\n<p>&nbsp;We look forward to working with your school again.&nbsp;</p>','Available Tags: {main_contact}, {org_name}, {date_description}, {renewal_date}, {reminder_period}',0,0,'2014-08-28 14:30:15','2017-09-15 14:50:25'),('email_renewal_reminder_subject','Contract Renewal Reminder Subject','text','emailsms',130,'','Contract Renewal Reminder','Available Tags: {main_contact}, {org_name}, {date_description}, {renewal_date}, {reminder_period}',0,0,'2014-08-28 14:30:15','2017-09-15 14:50:25'),('email_reset_password','Reset Pasword Email','wysiwyg','global',10,'','<p>Hello {first_name},</p>\r\n<p>You or someone else has requested a password reset.</p>\r\n<p>To reset your password, please click the link below:</p>\r\n<p>{reset_link}</p>\r\n<p>Note: This link is only valid for 15 minutes from when this email was requested. If you didn\'t request this, please ignore this email.</p>','Available tags: {first_name}, {reset_link}',NULL,NULL,'2016-09-21 18:57:41','2017-09-15 14:50:25'),('email_senddbs','Send DBS','wysiwyg','emailsms',41,'','<p>Dear {main_contact},</p>\r\n<p>As per your request, we are pleased to supply you with confirmation if the DBS details you have requested for {block_name}:</p>\r\n<p>{details}</p>\r\n<p>If you have any further queries please do not hesitate to contact us.</p>','Available tags: {main_contact}, {block_name}, {details}',0,0,'2014-08-27 08:11:52','2017-09-15 14:50:25'),('email_senddbs_subject','Send DBS Subject','text','emailsms',40,'','DBS Details for {block_name}','Available tags: {main_contact}, {block_name}',0,0,'2014-08-27 08:11:52','2017-09-15 14:50:25'),('email_staff_cancelled_sessions','Staff Cancelled Session(s) Notification','wysiwyg','emailsms',213,'','<p>Hi {staff_first},</p>\r\n<p>The following session(s) have been <strong>cancelled</strong>:</p>\r\n<p>{details}</p>\r\n<p>Please check your timetable for full details: {timetable_link}</p>','Available tags: {staff_first}, {details}, {timetable_link}',NULL,NULL,'2017-12-05 18:25:37',NULL),('email_staff_cancelled_sessions_subject','Staff Cancelled Session(s) Notification Subject','text','emailsms',212,'','Cancelled Session(s)','Available tags: {staff_first}',NULL,NULL,'2017-12-05 18:25:37',NULL),('email_staff_new_sessions','Staff New Session(s) Notification','wysiwyg','emailsms',211,'','<p>Hi {staff_first},</p>\r\n<p>The following session(s) have been assigned to you:</p>\r\n<p>{details}</p>\r\n<p>Please check your timetable for full details: {timetable_link}</p>','Available tags: {staff_first}, {details}, {timetable_link}',NULL,NULL,'2017-07-02 19:42:14','2017-09-15 14:50:25'),('email_staff_new_sessions_subject','Staff New Session(s) Notification Subject','text','emailsms',210,'','New Session(s)','Available tags: {staff_first}',NULL,NULL,'2017-07-02 19:42:14','2017-09-15 14:50:25'),('employee_of_month','Employee of the Month','staff','general',10,'','','',0,0,'2014-04-15 15:29:36','2017-09-15 14:50:25'),('force_password_change_every_x_months','Force Password Change Every x Months','number','general',120,'','3','Leave blank to disable',NULL,NULL,'2016-09-21 18:57:41','2017-09-15 14:50:25'),('freshdesk_base_url','Freshdesk Base URL','text','global',6,'','','With trailing slash',NULL,NULL,'2018-04-04 17:09:03',NULL),('freshdesk_shared_secret','Freshdesk Shared Secret','text','global',5,'','','See instructions at <a href=\"https://support.freshdesk.com/support/solutions/articles/31166-single-sign-on-remote-authentication-in-freshdesk\" target=\"_blank\">Freshdesk</a>',NULL,NULL,'2018-04-04 17:09:03',NULL),('gocardless_access_token','GoCardless Access Token','text','integrations',112,'','','Complete this field to enable payment plans by Direct Debit. You can create this in your <a href=\"https://manage.gocardless.com/developers/access-tokens/create\" target=\"_blank\">GoCardless Account</a>. <strong> Read-write access is required</strong>.',NULL,NULL,'2016-03-09 11:56:18','2017-09-15 14:50:25'),('gocardless_environment','GoCardless Environment','select','integrations',114,'production : Production\nsandbox : Sandbox','sandbox',NULL,NULL,NULL,'2016-03-09 11:56:18','2017-09-15 14:50:25'),('gocardless_success_redirect','GoCardless Success Redirect','url','integrations',115,'','','Enter the link customers should be redirected to after setting up the direct debit',NULL,NULL,'2016-03-09 11:56:18','2017-09-15 14:50:25'),('gocardless_webhook_secret','GoCardless Webhook Secret','text','integrations',113,'','','You also need to enter the following URL in your <a href=\"https://manage.gocardless.com/developers/webhook-endpoints/create\" target=\"_blank\">Webhook endpoints</a> settings page: <a href=\"{site_url}webhooks/gocardless/{account_id}\" target=\"_blank\">{site_url}webhooks/gocardless/{account_id}</a>. Either choose your own secret, else GoCardless will generate one for you. <strong></strong>This needs to match exactly</strong>.',NULL,NULL,'2017-10-24 17:59:56',NULL),('items_per_page','Items per page','number','general',20,'','25','',0,0,'2014-01-22 16:59:05','2017-09-15 14:50:25'),('label_nostaff_colour','No Staff Label Colour','select','styling',40,'blue : Blue\r\norange : Orange\r\nred : Red\r\ngreen : Green\r\npurple : Purple\r\nblue : Blue\r\npink : Pink\r\nlight-blue : Light Blue\r\ndark-grey : Dark Grey','red','Other label colours can be set in Settings > Brands',0,0,'2015-04-14 16:01:02','2017-09-15 14:50:25'),('logo','Logo','image','styling',0,'','a:5:{s:4:\"name\";s:25:\"logo_login_coordinate.png\";s:4:\"path\";s:32:\"1NyafKpV4kgORIBLZtFG57Dx36TXnlMw\";s:4:\"type\";s:9:\"image/png\";s:4:\"size\";d:5007.3599999999997;s:3:\"ext\";s:3:\"png\";}','Transparent background recommended',100,500,'2015-09-09 10:47:36','2015-10-06 10:54:34'),('mailchimp_key','MailChimp API Key','text','integrations',100,'','','Enter this if you want to sync your user\'s subscriptions with your MailChimp account. <a href=\"http://kb.mailchimp.com/accounts/management/about-api-keys#Find-or-Generate-Your-API-Key\" target=\"_blank\">Find Your API Key</a>. \r\nTo sync unsubscribes back from MailChimp, <a href=\"http://kb.mailchimp.com/integrations/other-integrations/how-to-set-up-webhooks\" target=\"_blank\">Add a Webhook</a> with a URL of <a href=\"{site_url}webhooks/mailchimp\" target=\"_blank\">{site_url}webhooks/mailchimp</a>, type of unsubscribes and send updates when made by a subscriber or admin.',0,0,'2015-10-03 14:48:29','2017-09-15 14:50:25'),('max_invalid_logins','Maximum Invalid Logins','number','global',10,'','5','After x attempts, account will be locked temporarily',NULL,NULL,'2016-09-21 18:57:41','2017-09-15 14:50:25'),('min_age','Minimum Age for Online Booking','number','general',365,'','3','',NULL,NULL,'2017-12-05 18:25:36',NULL),('online_booking_css','Online Booking CSS Overrides','css','styling',50,'','',NULL,NULL,NULL,'2017-09-03 19:45:29','2017-09-15 14:50:25'),('online_booking_footer','Online Booking Footer HTML','html','styling',52,'','',NULL,NULL,NULL,'2018-02-22 17:18:14',NULL),('online_booking_header','Online Booking Header HTML','html','styling',51,'','','Logo will be hidden if this field has content',NULL,NULL,'2018-02-22 17:18:14',NULL),('online_booking_meta','Online Booking Meta HTML','html','styling',53,'','',NULL,NULL,NULL,'2018-02-22 17:18:14',NULL),('phone','Phone','tel','general',60,'','','Shown on Coach ID',0,0,'2015-10-04 10:36:52','2017-09-15 14:50:25'),('provisional_own_timetable','Show Provisional Sessions on Own Timetable','checkbox','general',160,'','','',NULL,NULL,'2017-05-31 06:56:15','2017-09-15 14:50:25'),('register_intro','Registration Introduction','textarea','general',380,'','If you have booked with us before, but not online, you can {retrieve_password_link}. If you already have an account, please login instead.','Shown on online booking. Available Tags: {retrieve_password_link}',NULL,NULL,'2018-01-17 18:30:57',NULL),('require_dob','Require contact date of birth on registration','checkbox','general',351,'','0','',NULL,NULL,'2018-02-22 17:18:13',NULL),('require_full_payment','Require full payment in online booking, unless paying with childcare vouchers','checkbox','integrations',121,'','0','Only applicable to Stripe payments',NULL,NULL,'2017-09-15 18:21:18',NULL),('require_mobile','Require mobile number on registration','checkbox','general',350,'','','',NULL,NULL,'2017-09-03 19:45:28','2017-09-15 14:50:25'),('send_birthday_emails','Send Birthday Emails to Participant Children','checkbox','emailsms',150,'','','Aged 5 to 12 Inclusive',0,0,'2015-10-03 17:39:41','2017-09-15 14:50:25'),('sms_from','Send SMS From Name','text','emailsms',2,'','{company}','Maximum 11 characters. Available tags: {company}',NULL,NULL,'2018-01-17 18:30:57',NULL),('sms_payment_reminder_after','SMS Payment Reminder - 1 Week After','textarea','emailsms',140,'','Hi {contact_first}, You have £{amount} outstanding for {event_name}. Please call us to pay on 0000000000','Available Tags: {contact_first}, {amount}, {event_name}<br />Messages should be no longer than 160 characters. If using tags, this may cause the message to be cut off if the contact has a long name. ',0,0,'2014-08-28 14:30:15','2017-09-15 14:50:25'),('staff_invoice_address','Timesheet Invoices Mailing Address','textarea','general',300,'','','Shown on staff invoices',NULL,NULL,'2017-04-19 19:05:11','2017-09-15 14:50:25'),('staff_invoice_default_buyer','Timesheet Invoices Default Buyer ID','text','general',302,'','','',NULL,NULL,'2017-07-02 19:42:14','2017-09-15 14:50:25'),('staff_invoice_default_subject','Timesheet Invoices Default Subject','text','general',303,'','Invoice for {staff_name}','Available tags: {staff_name}',NULL,NULL,'2017-08-24 18:06:30','2017-09-15 14:50:25'),('staff_invoice_email','Timesheet Invoices Email','wysiwyg','emailsms',183,'','<p>Hello,</p>\r\n<p>{staff_name} has just submitted a new invoice ({invoice_no}) which is attached.</p>','Available tags: {invoice_no}, {staff_name}',NULL,NULL,'2017-04-19 19:05:11','2017-09-15 14:50:25'),('staff_invoice_prefix','Timesheet Invoices Prefix','text','general',301,'','','Invoice numbers will be prefixed with this',NULL,NULL,'2017-07-02 19:42:14','2017-09-15 14:50:25'),('staff_invoice_subject','Timesheet Invoices Subject','text','emailsms',182,'','{staff_name} - Invoice {invoice_no}','Available tags: {invoice_no}, {staff_name}',NULL,NULL,'2017-04-19 19:05:11','2017-09-15 14:50:25'),('staff_invoice_to','Send Timesheet Invoices To','email-multiple','emailsms',181,'','','Staff invoices will be sent to this email address',NULL,NULL,'2017-04-19 19:05:11','2017-09-15 14:50:25'),('stripe_pk','Stripe Publishable Key','text','integrations',130,'','','From your <a href=\"https://dashboard.stripe.com/account/apikeys\" target=\"_blank\">Stripe Dashboard</a>.',NULL,NULL,'2017-09-03 19:45:28','2017-09-15 14:50:25'),('stripe_sk','Stripe Secret Key','text','integrations',131,'','','From your <a href=\"https://dashboard.stripe.com/account/apikeys\" target=\"_blank\">Stripe Dashboard</a>.',NULL,NULL,'2017-09-03 19:45:28','2017-09-15 14:50:25'),('tech_email','Send Errors & Debug Emails To','email','global',0,'','hello@jasongillyon.co.uk','',0,0,'2015-10-04 09:35:03','2017-09-15 14:50:25'),('terms_customer','Customer Booking Terms and Conditions','wysiwyg','general',151,'','','Customers need to read and agree to these to book',NULL,NULL,'2017-03-31 12:01:29','2017-09-15 14:50:25'),('terms_individual','Online Booking Terms and Conditions','wysiwyg','general',150,'','','Participants need to read and agree to these to book',NULL,NULL,'2017-03-31 12:01:29','2017-09-15 14:50:25'),('timesheets_create_day','Create Timesheets On','select','general',298,'1 : Monday\r\n2 : Tuesday\r\n3 : Wednesday\r\n4 : Thursday\r\n5 : Friday\r\n6 : Saturday\r\n7 : Sunday','5','Day to create timesheets for the current week',NULL,NULL,'2018-04-04 17:09:03',NULL),('timesheets_submit_day','Submit Timesheets On','select','general',299,'1 : Monday\r\n2 : Tuesday\r\n3 : Wednesday\r\n4 : Thursday\r\n5 : Friday\r\n6 : Saturday\r\n7 : Sunday','3','Day to auto-submit timesheets for previous week',NULL,NULL,'2018-04-04 17:09:03',NULL),('website','Web Site','url','general',40,'','','Shown on Coach ID and relevant emails if smart tag used',0,0,'2015-10-04 10:17:04','2017-09-15 14:50:25');
/*!40000 ALTER TABLE `app_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_settings_areas`
--

DROP TABLE IF EXISTS `app_settings_areas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_settings_areas` (
  `areaID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `regionID` int(11) NOT NULL,
  `byID` int(11) DEFAULT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `added` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`areaID`),
  KEY `fk_settings_areas_byID` (`byID`),
  KEY `fk_settings_areas_regionID` (`regionID`),
  KEY `fk_settings_areas_accountID` (`accountID`),
  CONSTRAINT `app_settings_areas_ibfk_1` FOREIGN KEY (`regionID`) REFERENCES `app_settings_regions` (`regionID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_settings_areas_ibfk_2` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `app_settings_areas_ibfk_3` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=240 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_settings_areas`
--

LOCK TABLES `app_settings_areas` WRITE;
/*!40000 ALTER TABLE `app_settings_areas` DISABLE KEYS */;
INSERT INTO `app_settings_areas` VALUES (11,23,6,224,'Kingstone Upon Hull','2016-04-22 13:07:29','2016-04-22 13:07:29'),(12,23,7,224,'Leeds','2016-04-22 13:07:38','2016-04-22 13:07:38');
/*!40000 ALTER TABLE `app_settings_areas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_settings_childcarevoucherproviders`
--

DROP TABLE IF EXISTS `app_settings_childcarevoucherproviders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_settings_childcarevoucherproviders` (
  `providerID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `byID` int(11) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `reference` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `comment` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `added` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`providerID`),
  KEY `fk_childcarevoucher_providers_byID` (`byID`),
  KEY `fk_settings_childcarevoucherproviders_accountID` (`accountID`),
  CONSTRAINT `app_settings_childcarevoucherproviders_ibfk_1` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `app_settings_childcarevoucherproviders_ibfk_2` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_settings_childcarevoucherproviders`
--

LOCK TABLES `app_settings_childcarevoucherproviders` WRITE;
/*!40000 ALTER TABLE `app_settings_childcarevoucherproviders` DISABLE KEYS */;
INSERT INTO `app_settings_childcarevoucherproviders` VALUES (24,23,224,1,'Childvouchers','123456789','Camp payments only','2016-04-22 13:08:17','2016-04-22 13:08:41');
/*!40000 ALTER TABLE `app_settings_childcarevoucherproviders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_settings_dashboard`
--

DROP TABLE IF EXISTS `app_settings_dashboard`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_settings_dashboard` (
  `key` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `order` int(5) DEFAULT NULL,
  `section` enum('bookings','staff','participants','safety','equipment') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'bookings',
  `value_amber` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `value_red` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `added` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_settings_dashboard`
--

LOCK TABLES `app_settings_dashboard` WRITE;
/*!40000 ALTER TABLE `app_settings_dashboard` DISABLE KEYS */;
INSERT INTO `app_settings_dashboard` VALUES ('bookings_availability_exceptions','Availability Exception Conflicts',3,'bookings','45 day','2 week','2017-03-31 12:01:18',NULL),('bookings_inactive_staff','Sessions with Inactive Staff',2,'bookings','45 day','2 week','2017-03-31 12:01:18',NULL),('bookings_no_lessons','Bookings with No Sessions',9,'bookings','45 day','2 week','2017-03-31 12:01:18',NULL),('bookings_no_staff','Sessions with No Staff',1,'bookings','45 day','2 week','2017-03-31 12:01:18',NULL),('bookings_provisional_blocks','Provisional Blocks',5,'bookings','45 day','2 week','2017-03-31 12:01:18',NULL),('bookings_renewaldue','Bookings due for Renewal',6,'bookings','1 month','2 week','2017-03-31 12:01:18',NULL),('bookings_unconfirmed','Unconfirmed Bookings',4,'bookings','45 day','2 week','2017-03-31 12:01:18',NULL),('bookings_uninvoiced','Uninvoiced Bookings',7,'bookings','1 month','2 week','2017-03-31 12:01:18',NULL),('bookings_unsent_invoices','Unsent Invoices',8,'bookings','45 day','2 week','2017-03-31 12:01:18',NULL),('equipment_late','Late Equipment',19,'equipment','0 day','2 week','2017-03-31 12:01:18',NULL),('families_outstanding','Bookings with Outstanding Balances',17,'participants','0 day','2 week','2017-03-31 12:01:18',NULL),('safety_docs','Expired or Missing',18,'safety','1 month','2 week','2017-03-31 12:01:18',NULL),('staff_additional_expiring','Expired/Expiring Additional Qualifications',11,'staff','3 month','2 week','2017-03-31 12:01:18',NULL),('staff_availability_exceptions','Upcoming Availability Exceptions (Holiday, etc)',12,'staff','1 month','2 week','2017-03-31 12:01:18',NULL),('staff_birthdays','Staff Birthday',16,'staff','45 day','2 week','2017-03-31 12:01:18',NULL),('staff_driving','Driving Expiring/Missing Declaration',14,'staff','1 month','2 week','2017-03-31 12:01:18',NULL),('staff_mandatory_expiring','Expired/Expiring Mandatory Qualifications',10,'staff','3 month','2 week','2017-03-31 12:01:18',NULL),('staff_probations','Probations Due',13,'staff','1 month','2 week','2017-03-31 12:01:18',NULL),('staff_website_due','Staff Due For Web Site',15,'staff','6 month','1 year','2017-03-31 12:01:18',NULL);
/*!40000 ALTER TABLE `app_settings_dashboard` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_settings_regions`
--

DROP TABLE IF EXISTS `app_settings_regions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_settings_regions` (
  `regionID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `byID` int(11) DEFAULT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `added` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`regionID`),
  KEY `fk_settings_areas_byID` (`byID`),
  KEY `fk_settings_regions_accountID` (`accountID`),
  CONSTRAINT `app_settings_regions_ibfk_1` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `app_settings_regions_ibfk_2` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=224 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_settings_regions`
--

LOCK TABLES `app_settings_regions` WRITE;
/*!40000 ALTER TABLE `app_settings_regions` DISABLE KEYS */;
INSERT INTO `app_settings_regions` VALUES (6,23,224,'East Yorkshire','2016-04-22 13:06:43','2016-04-22 13:06:49'),(7,23,224,'West Yorkshire','2016-04-22 13:07:07','2016-04-22 13:07:07');
/*!40000 ALTER TABLE `app_settings_regions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_settings_tags`
--

DROP TABLE IF EXISTS `app_settings_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_settings_tags` (
  `tagID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL,
  `byID` int(11) DEFAULT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `added` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`tagID`),
  KEY `accountID` (`accountID`),
  KEY `byID` (`byID`),
  CONSTRAINT `app_settings_tags_ibfk_1` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_settings_tags_ibfk_2` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_settings_tags`
--

LOCK TABLES `app_settings_tags` WRITE;
/*!40000 ALTER TABLE `app_settings_tags` DISABLE KEYS */;
/*!40000 ALTER TABLE `app_settings_tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_staff`
--

DROP TABLE IF EXISTS `app_staff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_staff` (
  `staffID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `byID` int(11) DEFAULT NULL,
  `imported` tinyint(1) NOT NULL DEFAULT '0',
  `teamleaderID` int(11) DEFAULT NULL,
  `brandID` int(3) DEFAULT NULL,
  `active` tinyint(1) DEFAULT '1',
  `onsite` tinyint(1) NOT NULL,
  `title` enum('mr','mrs','miss','ms') COLLATE utf8_unicode_ci DEFAULT NULL,
  `first` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `middle` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `surname` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `jobTitle` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `department` enum('coaching','office','management','directors','headcoach','fulltimecoach') COLLATE utf8_unicode_ci DEFAULT NULL,
  `medical` text COLLATE utf8_unicode_ci,
  `nationalInsurance` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `email` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `tshirtSize` enum('xs','s','m','l','xl','xxl') COLLATE utf8_unicode_ci DEFAULT NULL,
  `payments_scale_head` decimal(8,2) NOT NULL,
  `payments_scale_assist` decimal(8,2) NOT NULL,
  `payments_scale_salaried` tinyint(1) NOT NULL DEFAULT '0',
  `payments_scale_salary` decimal(8,2) DEFAULT NULL,
  `payments_bankName` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `payments_sortCode` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `payments_accountNumber` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `payroll_number` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `proofid_passport` tinyint(1) NOT NULL,
  `proofid_passport_date` date DEFAULT NULL,
  `proofid_passport_ref` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `proofid_nicard` tinyint(1) NOT NULL,
  `proofid_nicard_ref` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `proofid_driving` tinyint(1) NOT NULL,
  `proofid_driving_date` date DEFAULT NULL,
  `proofid_driving_ref` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `proofid_birth` tinyint(1) NOT NULL,
  `proofid_birth_date` date DEFAULT NULL,
  `proofid_birth_ref` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `proofid_utility` tinyint(1) NOT NULL,
  `proofid_other` tinyint(1) NOT NULL,
  `proofid_other_specify` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `proof_address` tinyint(1) DEFAULT NULL,
  `proof_nationalinsurance` tinyint(1) DEFAULT NULL,
  `proof_quals` tinyint(1) DEFAULT NULL,
  `proof_permit` tinyint(1) DEFAULT NULL,
  `checklist_idcard` tinyint(1) DEFAULT NULL,
  `checklist_paydates` tinyint(1) DEFAULT NULL,
  `checklist_timesheet` tinyint(1) DEFAULT NULL,
  `checklist_policy` tinyint(1) DEFAULT NULL,
  `checklist_travel` tinyint(1) DEFAULT NULL,
  `checklist_equal` tinyint(1) DEFAULT NULL,
  `checklist_contract` tinyint(1) DEFAULT NULL,
  `checklist_p45` tinyint(1) DEFAULT NULL,
  `checklist_crb` tinyint(1) NOT NULL,
  `checklist_policies` tinyint(1) NOT NULL,
  `checklist_details` tinyint(1) NOT NULL,
  `checklist_tshirt` tinyint(1) NOT NULL,
  `id_personalStatement` text COLLATE utf8_unicode_ci,
  `id_specialism` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `id_favQuote` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `id_sportingHero` varchar(55) COLLATE utf8_unicode_ci DEFAULT NULL,
  `id_photo_name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `id_photo_path` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `id_photo_type` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `id_photo_ext` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `id_photo_size` bigint(100) NOT NULL,
  `equal_ethnic` enum('whiteBritish','whiteIrish','whiteOther','mixedCaribbean','mixedAfrican','mixedOther','asianIndian','asianPakistani','asianBangladeshi','asianOther','asianCaribbean','blackAfrican','chinese','other') COLLATE utf8_unicode_ci DEFAULT NULL,
  `equal_ethnic_other` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `equal_disability` text COLLATE utf8_unicode_ci,
  `equal_source` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `equal_comments` text COLLATE utf8_unicode_ci,
  `qual_first` tinyint(1) NOT NULL,
  `qual_first_issue_date` date DEFAULT NULL,
  `qual_first_expiry_date` date DEFAULT NULL,
  `qual_child` tinyint(1) NOT NULL,
  `qual_child_issue_date` date DEFAULT NULL,
  `qual_child_expiry_date` date DEFAULT NULL,
  `qual_fsscrb` tinyint(1) NOT NULL,
  `qual_fsscrb_issue_date` date DEFAULT NULL,
  `qual_fsscrb_expiry_date` date DEFAULT NULL,
  `qual_fsscrb_ref` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `qual_othercrb` tinyint(1) NOT NULL,
  `qual_othercrb_issue_date` date DEFAULT NULL,
  `qual_othercrb_expiry_date` date DEFAULT NULL,
  `qual_othercrb_ref` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `accept_policies` datetime DEFAULT NULL,
  `target_hours` decimal(6,1) NOT NULL,
  `target_utilisation` int(3) NOT NULL,
  `target_observation_score` int(3) DEFAULT NULL,
  `employment_start_date` date DEFAULT NULL,
  `employment_end_date` date DEFAULT NULL,
  `employment_probation_date` date DEFAULT NULL,
  `employment_probation_complete` tinyint(1) NOT NULL DEFAULT '0',
  `driving_mot` tinyint(1) NOT NULL DEFAULT '0',
  `driving_mot_expiry` date DEFAULT NULL,
  `driving_insurance` tinyint(1) NOT NULL DEFAULT '0',
  `driving_insurance_expiry` date DEFAULT NULL,
  `driving_declaration` tinyint(1) NOT NULL DEFAULT '0',
  `dashboard_config` text COLLATE utf8_unicode_ci,
  `feed_enabled` tinyint(1) DEFAULT '0',
  `feed_key` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `non_delivery` tinyint(1) NOT NULL DEFAULT '0',
  `reset_hash` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `reset_at` datetime DEFAULT NULL,
  `invalid_logins` int(1) DEFAULT '0',
  `locked_until` datetime DEFAULT NULL,
  `last_password_change` datetime DEFAULT NULL,
  `added` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  PRIMARY KEY (`staffID`),
  UNIQUE KEY `email` (`email`),
  KEY `fk_staff_byID` (`byID`),
  KEY `fk_staff_teamleaderID` (`teamleaderID`),
  KEY `fk_staff_accountID` (`accountID`),
  KEY `brandID` (`brandID`),
  CONSTRAINT `app_staff_ibfk_1` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `app_staff_ibfk_2` FOREIGN KEY (`teamleaderID`) REFERENCES `app_staff` (`staffID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `app_staff_ibfk_3` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_staff_brandID` FOREIGN KEY (`brandID`) REFERENCES `app_brands` (`brandID`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2033 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_staff`
--

LOCK TABLES `app_staff` WRITE;
/*!40000 ALTER TABLE `app_staff` DISABLE KEYS */;
INSERT INTO `app_staff` VALUES (208,10,NULL,0,NULL,NULL,1,0,'mr','Demo','.','Admin','.','directors',',','12345678','1990-10-10','demo@coordinate.cloud','$2y$10$3l2dGLiTW9h4ADuxfOx8QO8xLPzI1Qlt4XG8ducmjYx1KXFMxFbfy','s',0.00,0.00,0,NULL,NULL,NULL,NULL,NULL,0,NULL,'',0,'',0,NULL,'',0,NULL,'',0,0,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,0,0,NULL,NULL,NULL,NULL,NULL,'','','',0,'whiteBritish','','','',NULL,0,NULL,NULL,0,NULL,NULL,0,NULL,NULL,'',0,NULL,NULL,'',NULL,0.0,0,NULL,NULL,NULL,NULL,0,0,NULL,0,NULL,0,'[[{\"id\":\"booking_alerts\",\"state\":\"open\"},{\"id\":\"staff_alerts\",\"state\":\"collapsed\"},{\"id\":\"others_tasks\",\"state\":\"collapsed\"}],[{\"id\":\"availability_checker\",\"state\":\"collapsed\"},{\"id\":\"your_tasks\",\"state\":\"collapsed\"}],[{\"id\":\"staff_birthdays\",\"state\":\"open\"},{\"id\":\"upcoming_events_8\",\"state\":\"collapsed\"}]]',0,NULL,0,NULL,NULL,0,NULL,'2017-04-04 18:22:26','2015-10-20 16:04:50','2018-04-04 18:22:26','2018-04-04 18:36:28'),(224,23,NULL,0,NULL,NULL,1,0,NULL,'Support',NULL,'Adminstrator',NULL,'directors','',NULL,NULL,'demo@i-coordinate.co.uk','2d.UyDRx2C.W2',NULL,0.00,0.00,0,NULL,NULL,NULL,NULL,NULL,0,NULL,'',0,'',0,NULL,'',0,NULL,'',0,0,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,0,0,NULL,NULL,NULL,NULL,NULL,'','','',0,NULL,'',NULL,NULL,NULL,0,NULL,NULL,0,NULL,NULL,0,NULL,NULL,'',0,NULL,NULL,'',NULL,0.0,0,NULL,NULL,NULL,NULL,0,0,NULL,0,NULL,0,NULL,0,NULL,0,NULL,NULL,0,NULL,NULL,'2016-04-13 10:09:13','2016-04-13 10:09:13','2017-02-07 14:33:33'),(225,23,224,0,NULL,NULL,1,1,'mr','Ben','Jack','Smith','PE Specialist','fulltimecoach','Asthma','PN000065B','1994-04-06','Jacksmith1994@hotmail.co.uk','$2y$10$PmiMVOEn/DVv0KhXhJNHduI9ZFXh9wQd8jSujkG7aHHQIF0e9JTfS','xl',6.70,6.70,1,10452.00,'Barclays','21-21-00','12345678',NULL,1,'2016-04-01','1234567',1,'PN000065B',0,NULL,'',0,NULL,'',1,0,'',1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,'My passion is sport, I pride myself on delivering an extremely high standard of Physical Education to customers I have the skills, knowledge and the confience to advance within this company.','Rugby','Carpe Diem - Seize The Day','Ben Cohen','2.jpg','jChNHoGtrMQF1A8TpRn5XuScxYUO4K9f','image/jpeg','jpg',5571,'whiteBritish','','','Website',NULL,1,NULL,'2016-06-30',1,NULL,'2019-04-18',1,NULL,'2019-04-14','001234567891',0,NULL,NULL,'',NULL,30.0,80,NULL,'2016-04-01',NULL,'2016-10-01',0,1,'2016-04-01',1,'2016-04-01',1,'[[{\"id\":\"safety_alerts\",\"state\":\"open\"},{\"id\":\"custom_widget_1\",\"state\":\"collapsed\"}],[{\"id\":\"equipment_alerts\",\"state\":\"open\"}],[{\"id\":\"employee_of_month\",\"state\":\"open\"},{\"id\":\"staff_birthdays\",\"state\":\"open\"},{\"id\":\"upcoming_events_106\",\"state\":\"collapsed\"},{\"id\":\"policies\",\"state\":\"collapsed\"},{\"id\":\"your_tasks\",\"state\":\"collapsed\"}]]',0,NULL,0,NULL,NULL,0,NULL,'2016-10-11 09:58:39','2016-04-14 12:58:03','2017-09-28 14:28:15','2016-10-11 09:00:18'),(226,23,224,0,NULL,NULL,1,1,'miss','Jane','Jessica','Long','Team Leader','headcoach','Nut Allergy','PB000064B','1990-04-10','jessica.jlong@hotmail.co.uk','1b5eBYGa9IHV6','s',7.20,0.00,1,13104.00,'Halifax','12-32-40','1234567897',NULL,1,'2017-04-20','451249',0,'',0,NULL,'',0,'2016-04-14','4278585',1,0,'',1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,NULL,NULL,NULL,NULL,NULL,'','','',0,'whiteBritish','','','',NULL,1,NULL,'2017-04-14',1,NULL,'2017-04-14',1,NULL,NULL,'001235647897',0,NULL,NULL,'',NULL,35.0,80,NULL,'2015-10-05',NULL,'2016-04-05',0,0,NULL,0,NULL,0,NULL,0,NULL,0,NULL,NULL,0,NULL,NULL,'2016-04-14 13:42:23','2016-04-22 13:10:21',NULL),(227,23,224,0,NULL,NULL,1,1,'mr','Jordan','Matthew','Wilkinson','Administrator','office','N/A','PB000063B','1996-09-18','j.wilkinson@gmail.com','65b5r5aRMRRS2','l',7.20,0.00,1,13104.00,'Barclays','54-21-00','123456799',NULL,1,'2018-04-19','4563214',0,'',0,NULL,'',0,NULL,'',0,0,'',1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,NULL,NULL,NULL,NULL,NULL,'','','',0,'blackAfrican','','','',NULL,1,NULL,'2018-04-12',1,NULL,'2016-04-12',1,NULL,'2018-04-21','001235647894',0,NULL,NULL,'',NULL,35.0,80,NULL,'2010-04-14',NULL,'2010-10-21',1,0,NULL,0,NULL,0,NULL,0,NULL,0,NULL,NULL,0,NULL,NULL,'2016-04-14 14:07:18','2016-06-10 10:35:17',NULL),(228,23,224,0,NULL,NULL,1,1,'mrs','Lauren','June','Hattersley','Business Development Manager','management','N/A','JP094998B','1985-10-26','ljHattersley@hotmail.co.uk','62m4lkDr.1VsM','l',7.20,0.00,1,13104.00,'Barclays','45-32-87','1234567841',NULL,1,'2018-04-18','4897515',1,'JP094587B',0,NULL,'',0,NULL,'',1,0,'',1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,NULL,NULL,NULL,NULL,NULL,'','','',0,'asianOther','','','Website',NULL,1,NULL,'2018-04-04',1,NULL,'2018-04-06',1,NULL,'2019-04-11','548623157',0,NULL,NULL,'',NULL,35.0,100,NULL,'2014-01-15',NULL,'2015-08-17',1,0,NULL,0,NULL,0,NULL,0,NULL,0,NULL,NULL,0,NULL,NULL,'2016-04-14 14:17:19','2016-04-18 09:07:54',NULL),(229,23,224,0,NULL,NULL,1,1,'mr','Tyler','Heith','Lloyd','Office Manager','management','N/A','NE890154D','2016-04-15','Tyler23@live.co.uk','a3LMzaUcUXKfI','xs',7.20,0.00,1,13104.00,'Barclays','65-85-54','4587451',NULL,1,'2018-06-15','42666479',1,'4548916',0,NULL,'',0,NULL,'',1,0,'',1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,NULL,NULL,NULL,NULL,NULL,'','','',0,'asianIndian','','','Website',NULL,1,NULL,'2018-04-12',1,NULL,'2017-08-17',1,NULL,'2018-04-18','15484451',0,NULL,NULL,'',NULL,35.0,100,NULL,'2010-02-10',NULL,'2010-09-14',1,0,NULL,1,'2018-04-13',1,NULL,0,NULL,0,NULL,NULL,0,NULL,NULL,'2016-04-14 14:37:06','2016-05-27 15:38:11',NULL),(230,23,224,0,226,NULL,1,1,'miss','Rosanna','May','Yates','Casual Coach','coaching','N/A','PB000062B','1996-01-02','Rosanna.may96@hotmail.com','dawLXSQjecjiE','m',8.50,6.70,0,0.00,'HSBC','67-87-98','1234567822',NULL,1,'2019-04-03','46564512',1,'548746541',0,NULL,'',0,NULL,'',1,0,'',1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,NULL,NULL,NULL,NULL,NULL,'','','',0,'mixedCaribbean','','','Website',NULL,1,NULL,'2019-04-04',1,NULL,'2019-04-24',1,NULL,'2018-04-12','4567463',1,NULL,NULL,'',NULL,0.0,0,NULL,'2015-12-04',NULL,'2016-06-15',0,0,NULL,0,NULL,0,NULL,0,NULL,0,NULL,NULL,0,NULL,NULL,'2016-04-14 14:50:51','2016-04-26 12:34:33','2016-04-26 12:40:10'),(231,23,224,0,226,NULL,1,1,'miss','Hannah','Louise','Murray','Sports Coach','coaching','N/A','PB21344SD','1996-04-16','Hannahm21@hotmail.co.uk','7alr80/6WOxbc','s',6.70,6.70,0,0.00,'Barclays','65-45-85','1234567800',NULL,1,'1997-01-06','123456789',1,'YW599012A',1,'2014-09-20','123456789',1,'1990-04-02','12345',1,0,'',1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,'I am passionate about sports and inspiring children to take part in physical activity.','Dance and Gymnastics','The only person you should try to be better than is the person you were yesterday.','David Beckham','1.jpg','bwHQ9f38IasNPMDCe6j0dBy2cVFigYKk','image/jpeg','jpg',5806,'whiteIrish','','','Website',NULL,1,NULL,'2016-04-13',1,NULL,'2017-04-27',1,NULL,'2016-10-26','148821144',0,NULL,NULL,'',NULL,0.0,0,NULL,'2015-01-12',NULL,'2015-10-08',1,1,'2018-01-01',1,'2018-01-01',1,NULL,0,NULL,0,NULL,NULL,0,NULL,NULL,'2016-04-15 14:57:04','2016-04-22 14:03:47',NULL),(232,23,224,0,226,NULL,1,1,'mr','Sean','Paul','David','Sports Coach','coaching','N/A','PB21388SD','1995-04-12','Seanpaul@live.co.uk','f2UtckSc93uhc','m',0.00,0.00,0,0.00,'Halifax','21-12-21','1234560000',NULL,0,NULL,'',0,'',0,NULL,'',0,NULL,'',1,0,'',1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,NULL,NULL,NULL,NULL,NULL,'','','',0,'blackAfrican','','','',NULL,1,NULL,'2018-04-06',1,NULL,'2017-04-24',1,NULL,'2018-04-20','4557845',0,NULL,NULL,'',NULL,0.0,0,NULL,'2016-01-13',NULL,'2016-03-09',1,0,NULL,0,NULL,0,NULL,0,NULL,0,NULL,NULL,0,NULL,NULL,'2016-04-15 15:04:51','2016-04-18 09:14:01',NULL),(233,23,224,0,NULL,NULL,1,1,'mr','Shane','Ashley','Hardy','Sports Coach','coaching','N/A','PB24477SD','1995-11-08','shanehardy.11@hotmail.co.uk','2360qQoCBrzGc','s',0.00,0.00,0,0.00,'HSBC','44-54-44','1234117897',NULL,0,NULL,'',0,'',0,NULL,'',0,NULL,'',0,0,'',1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,NULL,NULL,NULL,NULL,NULL,'','','',0,'mixedAfrican','','','Website',NULL,1,NULL,'2017-04-13',1,NULL,'2018-04-12',1,NULL,'2017-04-05','15487846',0,NULL,NULL,'',NULL,0.0,0,NULL,'2014-04-09',NULL,'2014-10-08',1,0,NULL,0,NULL,0,NULL,0,NULL,0,NULL,NULL,0,NULL,NULL,'2016-04-15 15:53:40','2016-04-18 09:15:00',NULL),(234,23,208,0,NULL,NULL,1,0,'miss','Molly','','Woodruff','Education Manager','directors','n/a','1234567','1990-04-26','molly@i-coordinate.co.uk','$2y$10$Pv2R1.aY2oGTS/1yUip6keLFXld0jfNGm8KjVQ0O9r1h2EF2iqwVO','s',0.00,0.00,0,NULL,NULL,NULL,NULL,NULL,0,NULL,'',0,'',0,NULL,'',0,NULL,'',0,0,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,0,0,NULL,NULL,NULL,NULL,NULL,'','','',0,'whiteBritish','','n/a','',NULL,0,NULL,NULL,0,NULL,NULL,0,NULL,NULL,'',0,NULL,NULL,'',NULL,0.0,0,NULL,NULL,NULL,NULL,0,0,NULL,0,NULL,0,NULL,0,NULL,0,NULL,NULL,0,NULL,'2016-10-11 09:15:48','2016-04-26 09:34:54','2016-10-11 09:15:48','2016-10-19 14:51:21'),(235,10,208,0,NULL,NULL,0,0,'miss','Molly','.','Woodruff','Education Manager','management','.','.','2016-04-04','host@i-coordinate.com','$2y$10$KSZVY5F4LdItVmu1k.NKS.p06btUF2/BHMMl69KshXe42N2j0v3yy','xs',0.00,0.00,0,NULL,NULL,NULL,NULL,NULL,0,NULL,'',0,'',0,NULL,'',0,NULL,'',0,0,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,0,0,NULL,NULL,NULL,NULL,NULL,'','','',0,'whiteBritish','','','',NULL,0,NULL,NULL,0,NULL,NULL,0,NULL,NULL,'',0,NULL,NULL,'',NULL,0.0,0,NULL,NULL,NULL,NULL,0,0,NULL,0,NULL,0,NULL,0,NULL,0,NULL,NULL,0,NULL,'2016-10-20 12:11:09','2016-04-28 12:50:38','2017-09-05 12:14:51','2016-12-07 13:32:03');
/*!40000 ALTER TABLE `app_staff` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_staff_activities`
--

DROP TABLE IF EXISTS `app_staff_activities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_staff_activities` (
  `linkID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL,
  `staffID` int(11) NOT NULL,
  `activityID` int(11) NOT NULL,
  `head` tinyint(1) NOT NULL DEFAULT '0',
  `lead` tinyint(1) NOT NULL DEFAULT '0',
  `assistant` tinyint(1) NOT NULL DEFAULT '0',
  `added` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`linkID`),
  KEY `accountID` (`accountID`),
  KEY `staffID` (`staffID`),
  KEY `activityID` (`activityID`),
  CONSTRAINT `app_staff_activities_ibfk_1` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_staff_activities_ibfk_2` FOREIGN KEY (`staffID`) REFERENCES `app_staff` (`staffID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_staff_activities_activityID` FOREIGN KEY (`activityID`) REFERENCES `app_activities` (`activityID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9602 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_staff_activities`
--

LOCK TABLES `app_staff_activities` WRITE;
/*!40000 ALTER TABLE `app_staff_activities` DISABLE KEYS */;
INSERT INTO `app_staff_activities` VALUES (2,23,225,50,1,0,1,'2016-04-28 10:01:19','2017-09-28 14:28:15'),(3,23,225,51,1,0,1,'2016-04-28 10:01:19','2017-09-28 14:28:15'),(4,23,225,52,1,0,1,'2016-04-28 10:01:19','2017-09-28 14:28:15'),(5,23,225,53,1,0,1,'2016-04-28 10:01:19','2017-09-28 14:28:15'),(6,23,225,54,1,0,1,'2016-04-28 10:01:19','2017-09-28 14:28:15'),(7,23,225,55,1,0,1,'2016-04-28 10:01:19','2017-09-28 14:28:15'),(8,23,225,56,1,0,1,'2016-04-28 10:01:19','2017-09-28 14:28:15'),(9,23,226,50,0,0,1,'2016-04-28 10:01:19','2017-05-31 06:55:54'),(10,23,226,51,0,0,1,'2016-04-28 10:01:19','2017-05-31 06:55:54'),(11,23,226,52,0,0,1,'2016-04-28 10:01:19','2017-05-31 06:55:54'),(12,23,226,53,0,0,1,'2016-04-28 10:01:19','2017-05-31 06:55:54'),(13,23,226,54,0,0,1,'2016-04-28 10:01:19','2017-05-31 06:55:54'),(14,23,226,55,0,0,1,'2016-04-28 10:01:19','2017-05-31 06:55:54'),(15,23,226,56,0,0,1,'2016-04-28 10:01:19','2017-05-31 06:55:54'),(16,23,227,50,0,0,1,'2016-04-28 10:01:19','2017-05-31 06:55:54'),(17,23,227,51,0,0,1,'2016-04-28 10:01:19','2017-05-31 06:55:54'),(18,23,227,52,0,0,1,'2016-04-28 10:01:19','2017-05-31 06:55:54'),(19,23,227,53,0,0,1,'2016-04-28 10:01:19','2017-05-31 06:55:54'),(20,23,227,54,0,0,1,'2016-04-28 10:01:19','2017-05-31 06:55:54'),(21,23,227,55,0,0,1,'2016-04-28 10:01:19','2017-05-31 06:55:54'),(22,23,227,56,0,0,1,'2016-04-28 10:01:19','2017-05-31 06:55:54'),(23,23,228,50,0,0,1,'2016-04-28 10:01:19','2017-05-31 06:55:54'),(24,23,228,51,0,0,1,'2016-04-28 10:01:19','2017-05-31 06:55:54'),(25,23,228,52,0,0,1,'2016-04-28 10:01:20','2017-05-31 06:55:54'),(26,23,228,53,0,0,1,'2016-04-28 10:01:20','2017-05-31 06:55:54'),(27,23,228,54,0,0,1,'2016-04-28 10:01:20','2017-05-31 06:55:54'),(28,23,228,55,0,0,1,'2016-04-28 10:01:20','2017-05-31 06:55:54'),(29,23,228,56,0,0,1,'2016-04-28 10:01:20','2017-05-31 06:55:54'),(30,23,229,50,0,0,1,'2016-04-28 10:01:20','2017-05-31 06:55:54'),(31,23,229,51,0,0,1,'2016-04-28 10:01:20','2017-05-31 06:55:54'),(32,23,229,52,0,0,1,'2016-04-28 10:01:20','2017-05-31 06:55:54'),(33,23,229,53,0,0,1,'2016-04-28 10:01:20','2017-05-31 06:55:54'),(34,23,229,54,0,0,1,'2016-04-28 10:01:20','2017-05-31 06:55:54'),(35,23,229,55,0,0,1,'2016-04-28 10:01:20','2017-05-31 06:55:54'),(36,23,229,56,0,0,1,'2016-04-28 10:01:20','2017-05-31 06:55:54'),(37,23,230,50,0,0,1,'2016-04-28 10:01:20','2017-05-31 06:55:54'),(38,23,230,52,0,0,1,'2016-04-28 10:01:20','2017-05-31 06:55:54'),(39,23,230,53,0,0,1,'2016-04-28 10:01:20','2017-05-31 06:55:54'),(40,23,230,54,0,0,1,'2016-04-28 10:01:20','2017-05-31 06:55:54'),(41,23,230,55,0,0,1,'2016-04-28 10:01:20','2017-05-31 06:55:54'),(42,23,230,56,0,0,1,'2016-04-28 10:01:20','2017-05-31 06:55:54'),(43,23,231,50,0,0,1,'2016-04-28 10:01:20','2017-05-31 06:55:54'),(44,23,231,51,0,0,1,'2016-04-28 10:01:20','2017-05-31 06:55:54'),(45,23,231,52,0,0,1,'2016-04-28 10:01:20','2017-05-31 06:55:54'),(46,23,231,53,0,0,1,'2016-04-28 10:01:20','2017-05-31 06:55:54'),(47,23,231,54,0,0,1,'2016-04-28 10:01:20','2017-05-31 06:55:54'),(48,23,231,55,0,0,1,'2016-04-28 10:01:20','2017-05-31 06:55:54'),(49,23,231,56,0,0,1,'2016-04-28 10:01:20','2017-05-31 06:55:54'),(50,23,232,50,0,0,1,'2016-04-28 10:01:20','2017-05-31 06:55:54'),(51,23,232,51,0,0,1,'2016-04-28 10:01:20','2017-05-31 06:55:54'),(52,23,232,52,0,0,1,'2016-04-28 10:01:20','2017-05-31 06:55:54'),(53,23,232,53,0,0,1,'2016-04-28 10:01:20','2017-05-31 06:55:54'),(54,23,232,54,0,0,1,'2016-04-28 10:01:20','2017-05-31 06:55:54'),(55,23,232,55,0,0,1,'2016-04-28 10:01:20','2017-05-31 06:55:54'),(56,23,232,56,0,0,1,'2016-04-28 10:01:20','2017-05-31 06:55:54'),(57,23,233,50,0,0,1,'2016-04-28 10:01:20','2017-05-31 06:55:54'),(58,23,233,51,0,0,1,'2016-04-28 10:01:20','2017-05-31 06:55:54'),(59,23,233,52,0,0,1,'2016-04-28 10:01:20','2017-05-31 06:55:54'),(60,23,233,53,0,0,1,'2016-04-28 10:01:20','2017-05-31 06:55:54'),(61,23,233,54,0,0,1,'2016-04-28 10:01:20','2017-05-31 06:55:54'),(62,23,233,55,0,0,1,'2016-04-28 10:01:20','2017-05-31 06:55:54'),(63,23,233,56,0,0,1,'2016-04-28 10:01:20','2017-05-31 06:55:54');
/*!40000 ALTER TABLE `app_staff_activities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_staff_addresses`
--

DROP TABLE IF EXISTS `app_staff_addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_staff_addresses` (
  `addressID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `staffID` int(11) DEFAULT NULL,
  `byID` int(11) DEFAULT NULL,
  `imported` tinyint(1) NOT NULL DEFAULT '0',
  `type` enum('main','additional','emergency') COLLATE utf8_unicode_ci DEFAULT NULL,
  `relationship` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address1` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address2` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `town` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `county` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `postcode` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mobile` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `from` date DEFAULT NULL,
  `to` date DEFAULT NULL,
  `added` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`addressID`),
  KEY `fk_addresses_contacts_staffID` (`staffID`),
  KEY `fk_addresses_contacts_byID` (`byID`),
  KEY `fk_staff_addresses_accountID` (`accountID`),
  CONSTRAINT `app_staff_addresses_ibfk_2` FOREIGN KEY (`staffID`) REFERENCES `app_staff` (`staffID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_staff_addresses_ibfk_3` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `app_staff_addresses_ibfk_4` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3609 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='\n';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_staff_addresses`
--

LOCK TABLES `app_staff_addresses` WRITE;
/*!40000 ALTER TABLE `app_staff_addresses` DISABLE KEYS */;
INSERT INTO `app_staff_addresses` VALUES (521,10,208,208,0,'main',NULL,NULL,'N/a','','N/a','N/a','HU1 1UU','','0330 088 4595','2015-01-01',NULL,'2015-12-02 14:44:18','2018-04-04 18:22:26'),(527,10,221,208,0,'main',NULL,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,'2016-02-18 10:46:08','2016-02-18 10:46:08'),(529,10,223,208,0,'main',NULL,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,'2016-04-05 04:16:03','2016-04-05 04:16:03'),(530,10,224,208,0,'main',NULL,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,'2016-04-13 10:09:13','2016-04-13 10:09:13'),(531,23,225,224,0,'main',NULL,NULL,'23 Oak Road','','Hull','East Yorkshire','HU1 1UU','01482 565 656','07894731187','2010-01-01',NULL,'2016-04-14 12:58:03','2016-10-11 09:58:39'),(532,23,225,224,0,'emergency','Parent','Will Smith','23 Oak Road','','Hull','East Yorkshire','HU1 1UU','01482 565 656','',NULL,NULL,'2016-04-14 12:58:03','2016-04-15 09:15:31'),(533,23,226,224,0,'main',NULL,NULL,'31 Ingelton Avenue','','Hull','East Yorkshire','HU1 1UU','01482 563712','07123456789','2010-05-01',NULL,'2016-04-14 13:42:23','2016-04-22 13:10:21'),(534,23,226,224,0,'emergency','Parent','Amelia Long','31 Ingelton Avenue','','Hull','East Yorkshire','HU1 1UU','01482 563712','07944289458',NULL,NULL,'2016-04-14 13:42:23','2016-04-15 09:15:59'),(535,23,227,224,0,'main',NULL,NULL,'Malum Avenue','','Hull','East Yorkshire','HU1 1UU','01482 352253','07944277788','2011-03-01',NULL,'2016-04-14 14:07:18','2016-06-10 10:35:17'),(536,23,227,224,0,'emergency','Parent','Sam Wilkinson','Malum Avenue','','Hull','East Yorkshire','HU1 1UU','01482 352253','07858585741',NULL,NULL,'2016-04-14 14:07:18','2016-04-15 09:16:21'),(537,23,228,224,0,'main',NULL,NULL,'6 Bernadette Avenue','','Hull','East Yorkshire','HU1 1UU','01485 659981','07961443414','2000-02-01',NULL,'2016-04-14 14:17:19','2016-04-15 09:16:36'),(538,23,228,224,0,'emergency','Partner','James Hattersly','6 Bernadette Avenue','','Hull','East Yorkshire','HU1 1UU','01482 452365','07944745425',NULL,NULL,'2016-04-14 14:17:19','2016-04-15 09:16:43'),(539,23,229,224,0,'main',NULL,NULL,'51 Trenton Avenue','','Hull','East Yorkshire','HU1 1UU','01482 369954','07884184998','2010-03-01',NULL,'2016-04-14 14:37:06','2016-04-15 09:17:49'),(540,23,229,224,0,'emergency','Partner','Amy Lloyed','51 Trenton Avenue','','Hull','East Yorkshire','HU1 1UU','01482 369954','07943379425',NULL,NULL,'2016-04-14 14:37:06','2016-04-15 09:17:57'),(541,23,230,224,0,'main',NULL,NULL,'10 First Lane','','Hull','East Yorkshire','HU1 1UU','01482 458794','07955871365','2010-03-01',NULL,'2016-04-14 14:50:51','2016-04-26 12:34:33'),(542,23,230,224,0,'emergency','Parent','Lee Yates','10 First Lane','','Hull','East Yorkshire','HU1 1UU','01482 458794','07639458214',NULL,NULL,'2016-04-14 14:50:51','2016-04-15 09:17:11'),(543,23,231,224,0,'main',NULL,NULL,'4 Ingelmire Road','','Hull','East Yorkshire','HU1 1UU','01482 255552','07985541235','2010-01-01',NULL,'2016-04-15 14:57:04','2016-04-15 14:57:04'),(544,23,231,224,0,'emergency','Parent','Kevin Murray','4 Ingelmire Road','','Hull','East Yorkshire','HU1 1UU','01482 255552','07455896523',NULL,NULL,'2016-04-15 14:57:04','2016-04-15 14:57:04'),(545,23,232,224,0,'main',NULL,NULL,'52 Flounders Avenue','','Hull','East Yorkshire','HU1 1UU','01482 366663','07422563215','2010-04-01',NULL,'2016-04-15 15:04:51','2016-04-15 15:04:51'),(546,23,232,224,0,'emergency','Parent','Paul David','52 Flounders Avenue','','Hull','East Yorkshire','HU1 1UU','01482 366663','07455896623',NULL,NULL,'2016-04-15 15:04:51','2016-04-15 15:04:51'),(547,23,233,224,0,'main',NULL,NULL,'21 Track Lane','','Hull','East Yorkshire','HU1 1UU','01482 222222','07455622356','2010-07-01',NULL,'2016-04-15 15:53:40','2016-04-15 15:53:40'),(548,23,233,224,0,'emergency','Parent','June Hardy','21 Track Lane','','Hull','East Yorkshire','HU1 1UU','01482 222222','07544125236',NULL,NULL,'2016-04-15 15:53:40','2016-04-15 15:53:40'),(549,23,234,208,0,'main',NULL,NULL,'01482 123456','47 Queen Street,','Kingston Upon Hull.','East Yorkshire','HU1 1UU','01482 123456','07654321900','2010-01-01',NULL,'2016-04-26 09:34:54','2016-04-26 09:34:54'),(550,23,234,208,0,'emergency','.','Chris Middleton','The Fruit Market,','47 Queen Street,','Kingston Upon Hull.','.','HU1 1UU','01482 123456','07986543211',NULL,NULL,'2016-04-26 09:34:54','2016-04-26 09:34:54'),(551,10,235,208,0,'main',NULL,NULL,'C4DI','Queen Street','Hull','East Yorkshire','HU1 1UU','0123456789','01234567','1999-01-01',NULL,'2016-04-28 12:50:38','2016-10-20 12:11:09'),(552,10,235,208,0,'emergency','.','Coordinate HQ','.','.','.','.','HU1 1UU','123456789','123456789',NULL,NULL,'2016-04-28 12:50:38','2016-04-28 12:50:38'),(553,10,236,208,0,'main',NULL,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,'2016-05-10 19:13:09','2016-05-10 19:13:09'),(554,10,237,208,0,'main',NULL,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,'2016-05-11 11:05:54','2016-05-11 11:05:54'),(555,10,238,208,0,'main',NULL,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,'2016-05-11 13:30:11','2016-05-11 13:30:11'),(803,10,377,208,0,'main',NULL,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,'2016-05-26 20:42:37','2016-05-26 20:42:37'),(824,10,389,208,0,'main',NULL,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,'2016-05-26 20:48:48','2016-05-26 20:48:48'),(847,10,402,208,0,'main',NULL,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,'2016-05-26 20:59:05','2016-05-26 20:59:05'),(889,10,426,208,0,'main',NULL,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,'2016-05-26 21:07:04','2016-05-26 21:07:04'),(910,10,438,208,0,'main',NULL,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,'2016-05-26 21:13:50','2016-05-26 21:13:50'),(931,10,450,208,0,'main',NULL,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,'2016-05-26 21:22:01','2016-05-26 21:22:01'),(952,10,462,208,0,'main',NULL,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,'2016-05-26 21:25:33','2016-05-26 21:25:33'),(975,10,475,208,0,'main',NULL,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,'2016-05-26 21:42:15','2016-05-26 21:42:15'),(1027,10,504,208,0,'main',NULL,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,'2016-06-01 21:18:05','2016-06-01 21:18:05'),(1092,10,541,208,0,'main',NULL,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,'2016-06-14 13:12:20','2016-06-14 13:12:20'),(1107,10,549,208,0,'main',NULL,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,'2016-06-20 14:09:00','2016-06-20 14:09:00'),(1128,10,561,235,0,'main',NULL,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,'2016-06-23 14:24:28','2016-06-23 14:24:28'),(1149,10,573,235,0,'main',NULL,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,'2016-06-23 14:26:45','2016-06-23 14:26:45'),(1191,10,597,235,0,'main',NULL,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,'2016-07-11 14:00:28','2016-07-11 14:00:28'),(1232,10,620,235,0,'main',NULL,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,'2016-07-11 17:09:15','2016-07-11 17:09:15'),(1253,10,632,208,0,'main',NULL,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,'2016-09-06 16:13:54','2016-09-06 16:13:54'),(1379,10,703,208,0,'main',NULL,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,'2016-11-02 09:19:21','2016-11-02 09:19:21'),(1380,10,704,208,0,'main',NULL,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,'2016-11-02 09:33:43','2016-11-02 09:33:43'),(1422,10,728,208,0,'main',NULL,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,'2016-11-03 09:43:49','2016-11-03 09:43:49'),(1443,10,740,208,0,'main',NULL,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,'2016-11-04 12:49:02','2016-11-04 12:49:02'),(1593,10,826,NULL,0,'main',NULL,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,'2016-11-29 15:53:44','2016-11-29 15:53:44'),(1637,10,852,208,0,'main',NULL,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,'2016-12-08 08:53:53','2016-12-08 08:53:53'),(1723,10,899,208,0,'main',NULL,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,'2016-12-22 10:10:58','2016-12-22 10:10:58'),(2277,10,1309,208,0,'main',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2017-03-08 11:27:33','2017-03-08 11:27:33'),(2349,10,1348,208,0,'main',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2017-05-05 13:37:41','2017-05-05 13:37:41'),(2470,10,1414,208,0,'main',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2017-06-19 09:48:41','2017-06-19 09:48:41'),(2491,10,1426,208,0,'main',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2017-06-19 09:53:05','2017-06-19 09:53:05'),(2518,10,1441,208,0,'main',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2017-07-05 10:06:42','2017-07-05 10:06:42'),(2539,10,1453,208,0,'main',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2017-07-06 01:18:03','2017-07-06 01:18:03'),(2606,10,1491,208,0,'main',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2017-07-21 09:50:55','2017-07-21 09:50:55'),(2631,10,1505,208,0,'main',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2017-08-01 21:57:47','2017-08-01 21:57:47'),(2720,10,1552,208,0,'main',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2017-08-16 09:50:49','2017-08-16 09:50:49'),(2741,10,1564,208,0,'main',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2017-08-16 12:33:50','2017-08-16 12:33:50'),(2809,10,1601,208,0,'main',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2017-08-24 10:19:43','2017-08-24 10:19:43'),(2951,10,1677,208,0,'main',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2017-09-05 13:56:48','2017-09-05 13:56:48'),(2972,10,1689,208,0,'main',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2017-09-06 10:55:34','2017-09-06 10:55:34'),(3019,10,1715,208,0,'main',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2017-09-08 09:27:48','2017-09-08 09:27:48'),(3040,10,1727,208,0,'main',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2017-09-11 11:01:21','2017-09-11 11:01:21'),(3084,10,1752,208,0,'main',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2017-09-14 13:57:16','2017-09-14 13:57:16'),(3105,10,1764,208,0,'main',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2017-09-15 10:53:13','2017-09-15 10:53:13'),(3198,10,1816,208,0,'main',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2017-09-27 13:58:55','2017-09-27 13:58:55'),(3229,10,1833,208,0,'main',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2017-10-13 12:33:04','2017-10-13 12:33:04'),(3256,10,1848,208,0,'main',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2017-10-23 15:22:01','2017-10-23 15:22:01'),(3345,10,1888,208,0,'main',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2017-11-29 11:36:01','2017-11-29 11:36:01'),(3372,10,1903,208,0,'main',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2017-12-14 13:28:34','2017-12-14 13:28:34'),(3427,10,1933,208,0,'main',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2018-01-15 14:02:16','2018-01-15 14:02:16'),(3456,10,1949,208,0,'main',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2018-01-23 09:53:13','2018-01-23 09:53:13'),(3481,10,1963,208,0,'main',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2018-01-29 13:52:56','2018-01-29 13:52:56'),(3553,10,2002,208,0,'main',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2018-02-27 10:55:56','2018-02-27 10:55:56'),(3588,10,2021,208,0,'main',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2018-03-28 10:30:47','2018-03-28 10:30:47');
/*!40000 ALTER TABLE `app_staff_addresses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_staff_attachments`
--

DROP TABLE IF EXISTS `app_staff_attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_staff_attachments` (
  `attachmentID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `staffID` int(11) DEFAULT NULL,
  `byID` int(11) DEFAULT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `path` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `ext` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `size` bigint(100) NOT NULL,
  `comment` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `added` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`attachmentID`),
  KEY `fk_staff_attachments_staffID` (`staffID`),
  KEY `fk_staff_attachments_byID` (`byID`),
  KEY `fk_staff_attachments_accountID` (`accountID`),
  CONSTRAINT `app_staff_attachments_ibfk_2` FOREIGN KEY (`staffID`) REFERENCES `app_staff` (`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_staff_attachments_ibfk_3` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `app_staff_attachments_ibfk_4` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4300 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_staff_attachments`
--

LOCK TABLES `app_staff_attachments` WRITE;
/*!40000 ALTER TABLE `app_staff_attachments` DISABLE KEYS */;
INSERT INTO `app_staff_attachments` VALUES (3300,23,225,224,'Birth Certicicate.docx','V7cWvFnChPzT0EOaBH9D684blZ3GYwQX','application/vnd.openxmlformats-officedocument.wordprocessingml.document','docx',11325,'Identification Documents','2016-04-22 11:36:27','2016-04-22 11:40:46'),(3301,23,225,224,'01_04_2015 Contract of Employment.docx','G2o4BEkOZNXDyiVL0F5svztecHbRwr1a','application/vnd.openxmlformats-officedocument.wordprocessingml.document','docx',11336,'Employment Documents','2016-04-22 11:36:59','2016-04-22 11:36:59'),(3302,23,225,224,'Drivers licence.docx','ER1NwdoD84xZiQ0YVyvCqWbg5rmajuBs','application/vnd.openxmlformats-officedocument.wordprocessingml.document','docx',11223,'Driver Documents','2016-04-22 11:37:28','2016-04-22 11:37:28'),(3303,23,225,224,'04_04_2015 Reference.docx','L24Jy6phgKDPRAtlmTuSeFNXbM9O8ok5','application/vnd.openxmlformats-officedocument.wordprocessingml.document','docx',11346,'Reference Documents','2016-04-22 11:37:58','2016-04-22 11:37:58'),(3304,23,225,224,'Qualifications.docx','lwmDa8T4N9qzuWFGHOpMSAbdnJeP7Yjo','application/vnd.openxmlformats-officedocument.wordprocessingml.document','docx',11336,'Qualification Documents','2016-04-22 11:40:34','2016-04-22 11:40:34'),(3305,23,231,224,'Appraisal.docx','O3C6YsaLpimnIhEocQDxq7WjgM4SkruH','application/vnd.openxmlformats-officedocument.wordprocessingml.document','docx',11356,'Employment Documents','2016-04-22 11:46:57','2016-04-22 11:46:57'),(3306,23,231,224,'12_01_2015 Contract of Employment.docx','GqcVTfb5PQLO7lZiJ0FDxwM8Sekv6dNK','application/vnd.openxmlformats-officedocument.wordprocessingml.document','docx',11336,'Employment Documents','2016-04-22 11:48:22','2016-04-22 11:48:22'),(3308,23,231,224,'Birth Certicicate.docx','cRUlpNQObZK4MhHoExGy8vwkXD9BCzdJ','application/vnd.openxmlformats-officedocument.wordprocessingml.document','docx',11325,'Identification Documents','2016-04-22 11:49:01','2016-04-22 11:49:01'),(3309,23,231,224,'Qualifications.docx','EhefpwAOdcYM8CGaxSDsI34BWtvX2uiP','application/vnd.openxmlformats-officedocument.wordprocessingml.document','docx',11336,'Qualifications','2016-04-22 11:49:35','2016-04-22 11:49:35');
/*!40000 ALTER TABLE `app_staff_attachments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_staff_availability`
--

DROP TABLE IF EXISTS `app_staff_availability`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_staff_availability` (
  `availabilityID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `staffID` int(11) DEFAULT NULL,
  `byID` int(11) DEFAULT NULL,
  `day` enum('monday','tuesday','wednesday','thursday','friday','saturday','sunday') COLLATE utf8_unicode_ci DEFAULT NULL,
  `from` time DEFAULT NULL,
  `to` time DEFAULT NULL,
  `added` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`availabilityID`),
  KEY `fk_staff_availability_staffID` (`staffID`),
  KEY `fk_staff_availability_byID` (`byID`),
  KEY `fk_staff_availability_accountID` (`accountID`),
  CONSTRAINT `app_staff_availability_ibfk_1` FOREIGN KEY (`staffID`) REFERENCES `app_staff` (`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_staff_availability_ibfk_2` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `app_staff_availability_ibfk_3` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17934 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_staff_availability`
--

LOCK TABLES `app_staff_availability` WRITE;
/*!40000 ALTER TABLE `app_staff_availability` DISABLE KEYS */;
INSERT INTO `app_staff_availability` VALUES (8391,23,225,224,'monday','07:00:00','22:00:00','2016-04-14 12:59:04','2016-04-14 12:59:04'),(8392,23,225,224,'tuesday','07:00:00','22:00:00','2016-04-14 12:59:04','2016-04-14 12:59:04'),(8393,23,225,224,'wednesday','07:00:00','22:00:00','2016-04-14 12:59:04','2016-04-14 12:59:04'),(8394,23,225,224,'thursday','07:00:00','22:00:00','2016-04-14 12:59:04','2016-04-14 12:59:04'),(8395,23,225,224,'friday','07:00:00','22:00:00','2016-04-14 12:59:04','2016-04-14 12:59:04'),(8396,23,225,224,'saturday','07:00:00','22:00:00','2016-04-14 12:59:04','2016-04-14 12:59:04'),(8397,23,225,224,'sunday','07:00:00','22:00:00','2016-04-14 12:59:04','2016-04-14 12:59:04'),(8398,23,226,224,'monday','07:00:00','22:00:00','2016-04-14 13:42:59','2016-04-14 13:42:59'),(8399,23,226,224,'tuesday','07:00:00','22:00:00','2016-04-14 13:42:59','2016-04-14 13:42:59'),(8400,23,226,224,'wednesday','07:00:00','22:00:00','2016-04-14 13:42:59','2016-04-14 13:42:59'),(8401,23,226,224,'thursday','07:00:00','22:00:00','2016-04-14 13:42:59','2016-04-14 13:42:59'),(8402,23,226,224,'friday','07:00:00','22:00:00','2016-04-14 13:42:59','2016-04-14 13:42:59'),(8403,23,226,224,'saturday','07:00:00','22:00:00','2016-04-14 13:42:59','2016-04-14 13:42:59'),(8404,23,226,224,'sunday','07:00:00','22:00:00','2016-04-14 13:42:59','2016-04-14 13:42:59'),(8412,23,228,224,'monday','07:00:00','22:00:00','2016-04-14 14:17:50','2016-04-14 14:17:50'),(8413,23,228,224,'tuesday','07:00:00','22:00:00','2016-04-14 14:17:50','2016-04-14 14:17:50'),(8414,23,228,224,'wednesday','07:00:00','22:00:00','2016-04-14 14:17:50','2016-04-14 14:17:50'),(8415,23,228,224,'thursday','07:00:00','22:00:00','2016-04-14 14:17:50','2016-04-14 14:17:50'),(8416,23,228,224,'friday','07:00:00','22:00:00','2016-04-14 14:17:50','2016-04-14 14:17:50'),(8417,23,228,224,'saturday','07:00:00','22:00:00','2016-04-14 14:17:50','2016-04-14 14:17:50'),(8418,23,228,224,'sunday','07:00:00','22:00:00','2016-04-14 14:17:50','2016-04-14 14:17:50'),(8419,23,229,224,'monday','07:00:00','22:00:00','2016-04-14 14:37:13','2016-04-14 14:37:13'),(8420,23,229,224,'tuesday','07:00:00','22:00:00','2016-04-14 14:37:13','2016-04-14 14:37:13'),(8421,23,229,224,'wednesday','07:00:00','22:00:00','2016-04-14 14:37:13','2016-04-14 14:37:13'),(8422,23,229,224,'thursday','07:00:00','22:00:00','2016-04-14 14:37:13','2016-04-14 14:37:13'),(8423,23,229,224,'friday','07:00:00','22:00:00','2016-04-14 14:37:13','2016-04-14 14:37:13'),(8424,23,229,224,'saturday','07:00:00','22:00:00','2016-04-14 14:37:13','2016-04-14 14:37:13'),(8425,23,229,224,'sunday','07:00:00','22:00:00','2016-04-14 14:37:13','2016-04-14 14:37:13'),(8426,23,230,224,'monday','07:00:00','22:00:00','2016-04-14 14:51:09','2016-04-14 14:51:09'),(8427,23,230,224,'tuesday','07:00:00','22:00:00','2016-04-14 14:51:09','2016-04-14 14:51:09'),(8428,23,230,224,'thursday','07:00:00','22:00:00','2016-04-14 14:51:09','2016-04-14 14:51:09'),(8429,23,231,224,'monday','07:00:00','22:00:00','2016-04-15 14:57:09','2016-04-15 14:57:09'),(8430,23,231,224,'tuesday','07:00:00','22:00:00','2016-04-15 14:57:09','2016-04-15 14:57:09'),(8431,23,231,224,'wednesday','07:00:00','22:00:00','2016-04-15 14:57:09','2016-04-15 14:57:09'),(8432,23,231,224,'thursday','07:00:00','22:00:00','2016-04-15 14:57:09','2016-04-15 14:57:09'),(8433,23,231,224,'friday','07:00:00','22:00:00','2016-04-15 14:57:09','2016-04-15 14:57:09'),(8434,23,231,224,'saturday','07:00:00','22:00:00','2016-04-15 14:57:09','2016-04-15 14:57:09'),(8435,23,231,224,'sunday','07:00:00','22:00:00','2016-04-15 14:57:09','2016-04-15 14:57:09'),(8436,23,232,224,'monday','07:00:00','22:00:00','2016-04-15 15:05:35','2016-04-15 15:05:35'),(8437,23,232,224,'tuesday','07:00:00','22:00:00','2016-04-15 15:05:35','2016-04-15 15:05:35'),(8438,23,232,224,'wednesday','07:00:00','22:00:00','2016-04-15 15:05:35','2016-04-15 15:05:35'),(8439,23,232,224,'thursday','07:00:00','22:00:00','2016-04-15 15:05:35','2016-04-15 15:05:35'),(8440,23,232,224,'friday','07:00:00','22:00:00','2016-04-15 15:05:35','2016-04-15 15:05:35'),(8441,23,232,224,'saturday','07:00:00','22:00:00','2016-04-15 15:05:35','2016-04-15 15:05:35'),(8442,23,232,224,'sunday','07:00:00','22:00:00','2016-04-15 15:05:35','2016-04-15 15:05:35'),(8443,23,233,224,'monday','07:00:00','22:00:00','2016-04-15 15:53:48','2016-04-15 15:53:48'),(8444,23,233,224,'tuesday','07:00:00','22:00:00','2016-04-15 15:53:48','2016-04-15 15:53:48'),(8445,23,233,224,'wednesday','07:00:00','22:00:00','2016-04-15 15:53:48','2016-04-15 15:53:48'),(8446,23,233,224,'thursday','07:00:00','22:00:00','2016-04-15 15:53:48','2016-04-15 15:53:48'),(8447,23,233,224,'friday','07:00:00','22:00:00','2016-04-15 15:53:48','2016-04-15 15:53:48'),(8448,23,233,224,'saturday','07:00:00','22:00:00','2016-04-15 15:53:48','2016-04-15 15:53:48'),(8449,23,233,224,'sunday','07:00:00','22:00:00','2016-04-15 15:53:48','2016-04-15 15:53:48'),(8450,23,227,224,'monday','07:00:00','22:00:00','2016-04-22 12:07:00','2016-04-22 12:07:00'),(8451,23,227,224,'tuesday','07:00:00','22:00:00','2016-04-22 12:07:00','2016-04-22 12:07:00'),(8452,23,227,224,'wednesday','07:00:00','22:00:00','2016-04-22 12:07:00','2016-04-22 12:07:00'),(8453,23,227,224,'thursday','07:00:00','22:00:00','2016-04-22 12:07:00','2016-04-22 12:07:00'),(8454,23,227,224,'friday','07:00:00','22:00:00','2016-04-22 12:07:00','2016-04-22 12:07:00'),(8968,23,234,208,'monday','07:00:00','22:00:00','2016-05-26 16:07:58','2016-05-26 16:07:58'),(8969,23,234,208,'tuesday','07:00:00','22:00:00','2016-05-26 16:07:58','2016-05-26 16:07:58'),(8970,23,234,208,'wednesday','07:00:00','22:00:00','2016-05-26 16:07:58','2016-05-26 16:07:58'),(8971,23,234,208,'thursday','07:00:00','22:00:00','2016-05-26 16:07:58','2016-05-26 16:07:58'),(8972,23,234,208,'friday','07:00:00','22:00:00','2016-05-26 16:07:58','2016-05-26 16:07:58'),(8973,23,234,208,'saturday','07:00:00','22:00:00','2016-05-26 16:07:58','2016-05-26 16:07:58'),(8974,23,234,208,'sunday','07:00:00','22:00:00','2016-05-26 16:07:58','2016-05-26 16:07:58');
/*!40000 ALTER TABLE `app_staff_availability` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_staff_availability_exceptions`
--

DROP TABLE IF EXISTS `app_staff_availability_exceptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_staff_availability_exceptions` (
  `exceptionsID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `staffID` int(11) DEFAULT NULL,
  `byID` int(11) DEFAULT NULL,
  `from` datetime DEFAULT NULL,
  `to` datetime DEFAULT NULL,
  `type` enum('authorised','unauthorised','other') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'other',
  `reason` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `added` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`exceptionsID`),
  KEY `fk_staff_availability_exceptions_staffID` (`staffID`),
  KEY `fk_staff_availability_exceptions_byID` (`byID`),
  KEY `fk_staff_availability_exceptions_accountID` (`accountID`),
  CONSTRAINT `app_staff_availability_exceptions_ibfk_1` FOREIGN KEY (`staffID`) REFERENCES `app_staff` (`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_staff_availability_exceptions_ibfk_2` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `app_staff_availability_exceptions_ibfk_3` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3217 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_staff_availability_exceptions`
--

LOCK TABLES `app_staff_availability_exceptions` WRITE;
/*!40000 ALTER TABLE `app_staff_availability_exceptions` DISABLE KEYS */;
INSERT INTO `app_staff_availability_exceptions` VALUES (1138,23,230,224,'2016-07-04 07:00:00','2016-07-10 22:00:00','other','Holiday','2016-04-20 14:24:07','2016-04-20 14:24:07'),(1139,23,225,224,'2016-04-25 09:00:00','2016-04-25 11:30:00','other','Dental Appointment','2016-04-20 14:24:47','2016-04-20 14:24:47'),(1141,23,226,224,'2016-05-02 09:00:00','2016-05-02 12:00:00','other','Hospital Appoitnment','2016-04-20 14:33:29','2016-04-20 14:33:29'),(1142,23,227,224,'2016-09-05 07:00:00','2016-09-11 22:00:00','other','Holiday','2016-04-20 14:34:03','2016-04-20 14:34:03'),(1143,23,228,224,'2016-08-01 07:00:00','2016-08-07 22:00:00','other','Holiday','2016-04-20 14:34:49','2016-04-20 14:34:49'),(1144,23,232,224,'2016-05-04 13:00:00','2016-05-04 14:00:00','other','Dental Appointment','2016-04-20 14:35:38','2016-04-20 14:35:38'),(1145,23,233,224,'2016-10-03 07:00:00','2016-10-09 22:00:00','other','Holiday','2016-04-20 14:36:13','2016-04-20 14:36:13'),(1146,23,229,224,'2016-04-29 07:00:00','2016-04-29 22:00:00','other','Holiday','2016-04-20 14:36:47','2016-04-20 14:36:47'),(1340,23,233,235,'2016-06-06 11:00:00','2016-06-06 22:00:00','other','Sickness','2016-06-06 10:28:58','2016-06-06 10:28:58');
/*!40000 ALTER TABLE `app_staff_availability_exceptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_staff_invoices`
--

DROP TABLE IF EXISTS `app_staff_invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_staff_invoices` (
  `invoiceID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL,
  `staffID` int(11) NOT NULL,
  `sent` tinyint(1) NOT NULL DEFAULT '0',
  `number` int(11) NOT NULL,
  `date` date NOT NULL,
  `subject` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `buyer_id` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `utr` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `bank_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `bank_account` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `bank_sort_code` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `amount` decimal(8,2) NOT NULL,
  `added` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  `sent_date` datetime DEFAULT NULL,
  PRIMARY KEY (`invoiceID`),
  KEY `accountID` (`accountID`),
  KEY `staffID` (`staffID`),
  CONSTRAINT `fk_staff_invoices_accountID` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_staff_invoices_staffID` FOREIGN KEY (`staffID`) REFERENCES `app_staff` (`staffID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=86 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_staff_invoices`
--

LOCK TABLES `app_staff_invoices` WRITE;
/*!40000 ALTER TABLE `app_staff_invoices` DISABLE KEYS */;
INSERT INTO `app_staff_invoices` VALUES (9,23,225,0,1,'2017-08-23','Invoice for Ben Smith','','','Barclays','12345678','21-21-00',6.70,'0000-00-00 00:00:00','2017-08-23 10:43:29',NULL);
/*!40000 ALTER TABLE `app_staff_invoices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_staff_invoices_items`
--

DROP TABLE IF EXISTS `app_staff_invoices_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_staff_invoices_items` (
  `rowID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL,
  `invoiceID` int(11) NOT NULL,
  `timesheetID` int(11) DEFAULT NULL,
  `type` enum('item','expense') COLLATE utf8_unicode_ci NOT NULL,
  `itemID` int(11) DEFAULT NULL,
  `expenseID` int(11) DEFAULT NULL,
  `desc` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `amount` decimal(8,2) NOT NULL,
  `added` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`rowID`),
  KEY `accountID` (`accountID`),
  KEY `invoiceID` (`invoiceID`),
  KEY `itemID` (`itemID`),
  KEY `expenseID` (`expenseID`),
  KEY `timesheetID` (`timesheetID`),
  CONSTRAINT `fk_staff_invoices_items_accountID` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_staff_invoices_items_expenseID` FOREIGN KEY (`expenseID`) REFERENCES `app_timesheets_expenses` (`expenseID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_staff_invoices_items_invoiceID` FOREIGN KEY (`invoiceID`) REFERENCES `app_staff_invoices` (`invoiceID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_staff_invoices_items_itemID` FOREIGN KEY (`itemID`) REFERENCES `app_timesheets_items` (`itemID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_staff_invoices_items_timesheetID` FOREIGN KEY (`timesheetID`) REFERENCES `app_timesheets` (`timesheetID`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2202 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_staff_invoices_items`
--

LOCK TABLES `app_staff_invoices_items` WRITE;
/*!40000 ALTER TABLE `app_staff_invoices_items` DISABLE KEYS */;
INSERT INTO `app_staff_invoices_items` VALUES (287,23,9,5338,'item',39452,NULL,'Timesheet: 26/12/2017 - 07:00-08:00 - Other',6.70,'2017-08-23 10:43:20','2017-08-23 10:43:20');
/*!40000 ALTER TABLE `app_staff_invoices_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_staff_invoices_timesheets`
--

DROP TABLE IF EXISTS `app_staff_invoices_timesheets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_staff_invoices_timesheets` (
  `linkID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL,
  `invoiceID` int(11) NOT NULL,
  `timesheetID` int(11) NOT NULL,
  `added` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`linkID`),
  KEY `accountID` (`accountID`),
  KEY `invoiceID` (`invoiceID`),
  KEY `timesheetID` (`timesheetID`),
  CONSTRAINT `fk_staff_invoices_timesheets_accountID` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_staff_invoices_timesheets_invoiceID` FOREIGN KEY (`invoiceID`) REFERENCES `app_staff_invoices` (`invoiceID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_staff_invoices_timesheets_timesheetID` FOREIGN KEY (`timesheetID`) REFERENCES `app_timesheets` (`timesheetID`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=189 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_staff_invoices_timesheets`
--

LOCK TABLES `app_staff_invoices_timesheets` WRITE;
/*!40000 ALTER TABLE `app_staff_invoices_timesheets` DISABLE KEYS */;
INSERT INTO `app_staff_invoices_timesheets` VALUES (1,23,9,5338,'2017-11-08 07:27:41','2017-11-08 07:27:41');
/*!40000 ALTER TABLE `app_staff_invoices_timesheets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_staff_notes`
--

DROP TABLE IF EXISTS `app_staff_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_staff_notes` (
  `noteID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `staffID` int(11) DEFAULT NULL,
  `byID` int(11) DEFAULT NULL,
  `date` date NOT NULL,
  `type` enum('feedbackpositive','feedbacknegative','observation','induction','appraisal','disciplinary','misc','payroll','pupilassessment','late') COLLATE utf8_unicode_ci NOT NULL,
  `observation_score` int(3) DEFAULT NULL,
  `summary` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `content` text COLLATE utf8_unicode_ci,
  `added` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`noteID`),
  KEY `fk_staff_notes_staffID` (`staffID`),
  KEY `fk_staff_notes_byID` (`byID`),
  KEY `fk_staff_notes_accountID` (`accountID`),
  CONSTRAINT `app_staff_notes_ibfk_1` FOREIGN KEY (`staffID`) REFERENCES `app_staff` (`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_staff_notes_ibfk_2` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `app_staff_notes_ibfk_3` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2321 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_staff_notes`
--

LOCK TABLES `app_staff_notes` WRITE;
/*!40000 ALTER TABLE `app_staff_notes` DISABLE KEYS */;
INSERT INTO `app_staff_notes` VALUES (1220,23,225,224,'2016-04-01','induction',NULL,'Company Induction','Employment checks made, all certificates and identification documents provided.','2016-04-22 11:33:18','2016-04-22 11:33:18'),(1221,23,231,224,'2015-04-01','appraisal',NULL,'Appraisal Meeting','Discussed:\r\nDevelopement Path\r\nImprovements\r\nAttendance\r\nPunctuality\r\nAppraisal document has been loaded to employee profile &amp;#40;attachments&amp;#41;','2016-04-22 11:45:57','2016-04-22 11:45:57'),(1943,23,225,208,'2017-09-28','feedbackpositive',NULL,'Customer Feedback','Compliments','2017-09-28 14:30:25','2017-09-28 14:30:25');
/*!40000 ALTER TABLE `app_staff_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_staff_quals`
--

DROP TABLE IF EXISTS `app_staff_quals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_staff_quals` (
  `qualID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `staffID` int(11) DEFAULT NULL,
  `byID` int(11) DEFAULT NULL,
  `imported` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `level` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `reference` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `added` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`qualID`),
  KEY `fk_staff_qualifications_staffID` (`staffID`),
  KEY `fk_staff_qualifications_byID` (`byID`),
  KEY `fk_staff_quals_accountID` (`accountID`),
  CONSTRAINT `app_staff_quals_ibfk_1` FOREIGN KEY (`staffID`) REFERENCES `app_staff` (`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_staff_quals_ibfk_2` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `app_staff_quals_ibfk_3` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2528 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_staff_quals`
--

LOCK TABLES `app_staff_quals` WRITE;
/*!40000 ALTER TABLE `app_staff_quals` DISABLE KEYS */;
INSERT INTO `app_staff_quals` VALUES (893,23,225,224,0,'Coaching Football','3','500/456/13',NULL,'2025-04-01','2016-04-14 13:02:35','2016-04-14 13:02:35'),(894,23,225,224,0,'Coaching Basketball','1','500/456/77',NULL,'2016-11-11','2016-04-14 13:03:10','2016-04-14 13:03:10'),(895,23,225,224,0,'Coaching Basketball','2','500/456/78',NULL,'2016-11-11','2016-04-14 13:03:32','2016-04-14 13:03:32'),(896,23,226,224,0,'BSc Hons Dance','First','',NULL,'2026-04-30','2016-04-14 13:47:28','2016-04-14 13:47:28'),(897,23,226,224,0,'Coaching Netball','3','',NULL,'2019-04-30','2016-04-14 13:49:12','2016-04-14 13:49:12'),(898,23,228,224,0,'CMI Diploma in First Line Management','3','',NULL,'2026-04-28','2016-04-14 14:19:19','2016-04-14 14:19:19'),(899,23,229,224,0,'Assessor Qualification','3','',NULL,'2026-04-07','2016-04-14 14:37:52','2016-04-14 14:37:52'),(900,23,230,224,0,'Coaching Basketball','2','',NULL,'2023-04-12','2016-04-14 14:54:28','2016-04-22 12:30:56'),(901,23,231,224,0,'Coaching Basketball','1','',NULL,'2026-04-15','2016-04-15 14:59:50','2016-04-22 11:41:39'),(902,23,232,224,0,'Coaching Football','2','',NULL,'2024-04-17','2016-04-15 15:06:23','2016-04-15 15:06:23'),(903,23,233,224,0,'Coaching Netball','3','',NULL,'2025-04-22','2016-04-15 15:54:17','2016-04-15 15:54:17');
/*!40000 ALTER TABLE `app_staff_quals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_staff_quals_mandatory`
--

DROP TABLE IF EXISTS `app_staff_quals_mandatory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_staff_quals_mandatory` (
  `linkID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL,
  `staffID` int(11) NOT NULL,
  `qualID` int(11) NOT NULL,
  `added` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`linkID`),
  KEY `accountID` (`accountID`),
  KEY `staffID` (`staffID`),
  KEY `qualID` (`qualID`),
  CONSTRAINT `fk_staff_quals_mandatory_accountID` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_staff_quals_mandatory_qualID` FOREIGN KEY (`qualID`) REFERENCES `app_mandatory_quals` (`qualID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_staff_quals_mandatory_staffID` FOREIGN KEY (`staffID`) REFERENCES `app_staff` (`staffID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1279 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_staff_quals_mandatory`
--

LOCK TABLES `app_staff_quals_mandatory` WRITE;
/*!40000 ALTER TABLE `app_staff_quals_mandatory` DISABLE KEYS */;
INSERT INTO `app_staff_quals_mandatory` VALUES (52,23,225,9,'2017-03-31 12:01:19','2017-09-28 14:28:15'),(53,23,225,10,'2017-03-31 12:01:19','2017-09-28 14:28:15'),(54,23,226,9,'2017-03-31 12:01:19','2017-03-31 12:01:19'),(55,23,226,10,'2017-03-31 12:01:19','2017-03-31 12:01:19'),(56,23,227,9,'2017-03-31 12:01:19','2017-03-31 12:01:19'),(57,23,227,10,'2017-03-31 12:01:19','2017-03-31 12:01:19'),(58,23,228,9,'2017-03-31 12:01:19','2017-03-31 12:01:19'),(59,23,228,10,'2017-03-31 12:01:19','2017-03-31 12:01:19'),(60,23,229,9,'2017-03-31 12:01:19','2017-03-31 12:01:19'),(61,23,229,10,'2017-03-31 12:01:19','2017-03-31 12:01:19'),(62,23,230,9,'2017-03-31 12:01:19','2017-03-31 12:01:19'),(63,23,230,10,'2017-03-31 12:01:19','2017-03-31 12:01:19'),(64,23,231,9,'2017-03-31 12:01:19','2017-03-31 12:01:19'),(65,23,232,9,'2017-03-31 12:01:19','2017-03-31 12:01:19'),(66,23,232,10,'2017-03-31 12:01:19','2017-03-31 12:01:19'),(67,23,233,9,'2017-03-31 12:01:19','2017-03-31 12:01:19'),(68,23,233,10,'2017-03-31 12:01:19','2017-03-31 12:01:19');
/*!40000 ALTER TABLE `app_staff_quals_mandatory` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_staffing_types`
--

DROP TABLE IF EXISTS `app_staffing_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_staffing_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL,
  `byID` int(11) DEFAULT NULL,
  `type` enum('head','assistant','participant','observer','lead') COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `added` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `accountID` (`accountID`),
  KEY `byID` (`byID`),
  CONSTRAINT `fk_staffing_types_accountID` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_staffing_types_byID` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_staffing_types`
--

LOCK TABLES `app_staffing_types` WRITE;
/*!40000 ALTER TABLE `app_staffing_types` DISABLE KEYS */;
/*!40000 ALTER TABLE `app_staffing_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_tasks`
--

DROP TABLE IF EXISTS `app_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_tasks` (
  `taskID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `staffID` int(11) NOT NULL,
  `byID` int(11) DEFAULT NULL,
  `complete` tinyint(1) NOT NULL DEFAULT '0',
  `task` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `added` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`taskID`),
  KEY `fk_tasks_byID` (`byID`),
  KEY `fk_tasks_staffID` (`staffID`),
  KEY `fk_tasks_accountID` (`accountID`),
  CONSTRAINT `app_tasks_ibfk_1` FOREIGN KEY (`staffID`) REFERENCES `app_staff` (`staffID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_tasks_ibfk_2` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `app_tasks_ibfk_3` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=302 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_tasks`
--

LOCK TABLES `app_tasks` WRITE;
/*!40000 ALTER TABLE `app_tasks` DISABLE KEYS */;
/*!40000 ALTER TABLE `app_tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_timesheets`
--

DROP TABLE IF EXISTS `app_timesheets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_timesheets` (
  `timesheetID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL,
  `staffID` int(11) NOT NULL,
  `date` date NOT NULL,
  `status` enum('unsubmitted','submitted','approved') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'unsubmitted',
  `total_time` time NOT NULL DEFAULT '00:00:00',
  `total_expenses` decimal(8,2) NOT NULL DEFAULT '0.00',
  `submitted` date DEFAULT NULL,
  `submitterID` int(11) DEFAULT NULL,
  `approved` datetime DEFAULT NULL,
  `approverID` int(11) DEFAULT NULL,
  `invoiced` tinyint(1) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`timesheetID`),
  UNIQUE KEY `staffID_2` (`staffID`,`date`),
  UNIQUE KEY `staffID_3` (`staffID`,`date`),
  KEY `accountID` (`accountID`),
  KEY `staffID` (`staffID`),
  KEY `submitterID` (`submitterID`),
  KEY `approverID` (`approverID`),
  CONSTRAINT `app_timesheets_ibfk_1` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_timesheets_ibfk_2` FOREIGN KEY (`staffID`) REFERENCES `app_staff` (`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_timesheets_ibfk_3` FOREIGN KEY (`submitterID`) REFERENCES `app_staff` (`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_timesheets_ibfk_4` FOREIGN KEY (`approverID`) REFERENCES `app_staff` (`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_timesheets_ibfk_5` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_timesheets_ibfk_6` FOREIGN KEY (`staffID`) REFERENCES `app_staff` (`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_timesheets_ibfk_7` FOREIGN KEY (`submitterID`) REFERENCES `app_staff` (`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_timesheets_ibfk_8` FOREIGN KEY (`approverID`) REFERENCES `app_staff` (`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23488 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_timesheets`
--

LOCK TABLES `app_timesheets` WRITE;
/*!40000 ALTER TABLE `app_timesheets` DISABLE KEYS */;
INSERT INTO `app_timesheets` VALUES (14,23,225,'2016-04-11','approved','00:00:00',0.00,NULL,NULL,'2016-04-20 09:00:21',NULL,0,'2016-04-15 09:00:21','2016-04-15 09:00:21'),(15,23,226,'2016-04-11','approved','00:00:00',0.00,NULL,NULL,'2016-04-20 09:00:21',NULL,0,'2016-04-15 09:00:21','2016-04-15 09:00:21'),(17,23,225,'2016-04-18','approved','00:00:00',0.00,NULL,NULL,'2016-04-27 09:00:20',NULL,0,'2016-04-22 09:00:20','2016-04-22 09:00:20'),(18,23,226,'2016-04-18','approved','00:00:00',0.00,NULL,NULL,'2016-04-27 09:00:20',NULL,0,'2016-04-22 09:00:20','2016-04-22 09:00:20'),(19,23,227,'2016-04-18','approved','06:15:00',0.00,NULL,NULL,'2016-04-26 11:41:42',234,0,'2016-04-23 09:00:21','2016-04-26 11:41:42'),(20,23,229,'2016-04-18','approved','02:30:00',0.00,NULL,NULL,'2016-04-27 09:00:20',NULL,0,'2016-04-23 09:00:21','2016-04-23 09:00:21'),(21,23,230,'2016-04-18','approved','02:00:00',0.00,NULL,NULL,'2016-04-27 09:00:20',NULL,0,'2016-04-23 09:00:21','2016-04-23 09:00:21'),(22,23,231,'2016-04-18','approved','02:30:00',0.00,NULL,NULL,'2016-04-27 09:00:20',NULL,0,'2016-04-23 09:00:21','2016-04-23 09:00:21'),(23,23,232,'2016-04-18','approved','06:15:00',0.00,NULL,NULL,'2016-04-27 09:00:20',NULL,0,'2016-04-23 09:00:21','2016-04-23 09:00:21'),(25,23,225,'2016-04-25','approved','06:00:00',0.00,NULL,NULL,'2016-05-04 09:00:21',NULL,0,'2016-04-30 09:00:21','2016-04-30 09:00:21'),(26,23,226,'2016-04-25','approved','00:00:00',0.00,NULL,NULL,'2016-05-04 09:00:21',NULL,0,'2016-04-30 09:00:21','2016-04-30 09:00:21'),(27,23,227,'2016-04-25','approved','06:15:00',0.00,NULL,NULL,'2016-05-04 09:00:21',NULL,0,'2016-04-30 09:00:21','2016-04-30 09:00:21'),(28,23,229,'2016-04-25','approved','02:30:00',0.00,NULL,NULL,'2016-05-04 09:00:21',NULL,0,'2016-04-30 09:00:21','2016-04-30 09:00:21'),(29,23,230,'2016-04-25','approved','02:00:00',0.00,NULL,NULL,'2016-05-04 09:00:21',NULL,0,'2016-04-30 09:00:21','2016-04-30 09:00:21'),(30,23,231,'2016-04-25','approved','12:30:00',0.00,NULL,NULL,'2016-05-04 09:00:21',NULL,0,'2016-04-30 09:00:21','2016-04-30 09:00:21'),(31,23,232,'2016-04-25','approved','06:15:00',0.00,NULL,NULL,'2016-05-04 09:00:22',NULL,0,'2016-04-30 09:00:21','2016-04-30 09:00:21'),(33,23,225,'2016-05-02','approved','06:00:00',0.00,NULL,NULL,'2016-05-11 09:00:20',NULL,0,'2016-05-06 09:00:21','2016-05-06 09:00:21'),(34,23,226,'2016-05-02','approved','00:00:00',0.00,NULL,NULL,'2016-05-11 09:00:20',NULL,0,'2016-05-06 09:00:21','2016-05-06 09:00:21'),(35,23,227,'2016-05-02','approved','06:15:00',0.00,NULL,NULL,'2016-05-11 09:00:20',NULL,0,'2016-05-06 09:00:21','2016-05-06 09:00:21'),(36,23,229,'2016-05-02','approved','02:30:00',0.00,NULL,NULL,'2016-05-11 09:00:20',NULL,0,'2016-05-06 09:00:21','2016-05-06 09:00:21'),(37,23,230,'2016-05-02','approved','02:00:00',0.00,NULL,NULL,'2016-05-11 09:00:20',NULL,0,'2016-05-06 09:00:22','2016-05-06 09:00:22'),(38,23,231,'2016-05-02','approved','02:30:00',0.00,NULL,NULL,'2016-05-11 09:00:20',NULL,0,'2016-05-06 09:00:22','2016-05-06 09:00:22'),(39,23,232,'2016-05-02','approved','06:15:00',0.00,NULL,NULL,'2016-05-11 09:00:20',NULL,0,'2016-05-06 09:00:22','2016-05-06 09:00:22'),(41,23,225,'2016-05-09','approved','06:00:00',0.00,NULL,NULL,'2016-05-18 10:00:20',NULL,0,'2016-05-13 10:00:20','2016-05-13 10:00:20'),(42,23,226,'2016-05-09','approved','00:00:00',0.00,NULL,NULL,'2016-05-18 10:00:20',NULL,0,'2016-05-13 10:00:20','2016-05-13 10:00:20'),(43,23,227,'2016-05-09','approved','06:15:00',0.00,NULL,NULL,'2016-05-18 10:00:20',NULL,0,'2016-05-13 10:00:20','2016-05-13 10:00:20'),(44,23,229,'2016-05-09','approved','02:30:00',0.00,NULL,NULL,'2016-05-18 10:00:20',NULL,0,'2016-05-13 10:00:21','2016-05-13 10:00:21'),(45,23,230,'2016-05-09','approved','02:00:00',0.00,NULL,NULL,'2016-05-18 10:00:20',NULL,0,'2016-05-13 10:00:21','2016-05-13 10:00:21'),(46,23,231,'2016-05-09','approved','02:30:00',0.00,NULL,NULL,'2016-05-18 10:00:20',NULL,0,'2016-05-13 10:00:21','2016-05-13 10:00:21'),(47,23,232,'2016-05-09','approved','06:15:00',0.00,NULL,NULL,'2016-05-18 10:00:20',NULL,0,'2016-05-13 10:00:21','2016-05-13 10:00:21'),(49,23,225,'2016-05-16','approved','06:00:00',0.00,NULL,NULL,'2016-05-25 10:00:21',NULL,0,'2016-05-20 10:00:20','2016-05-20 10:00:20'),(50,23,226,'2016-05-16','approved','02:00:00',0.00,NULL,NULL,'2016-05-25 10:00:21',NULL,0,'2016-05-20 10:00:21','2016-05-20 10:00:21'),(51,23,227,'2016-05-16','approved','10:00:00',0.00,NULL,NULL,'2016-05-25 10:00:21',NULL,0,'2016-05-20 10:00:21','2016-05-20 10:00:21'),(52,23,228,'2016-05-16','approved','02:00:00',0.00,NULL,NULL,'2016-05-25 10:00:21',NULL,0,'2016-05-20 10:00:21','2016-05-20 10:00:21'),(53,23,229,'2016-05-16','approved','02:30:00',0.00,NULL,NULL,'2016-05-25 10:00:21',NULL,0,'2016-05-20 10:00:21','2016-05-20 10:00:21'),(54,23,230,'2016-05-16','approved','02:00:00',0.00,NULL,NULL,'2016-05-25 10:00:21',NULL,0,'2016-05-20 10:00:21','2016-05-20 10:00:21'),(55,23,231,'2016-05-16','approved','02:45:00',0.00,NULL,NULL,'2016-05-25 10:00:21',NULL,0,'2016-05-20 10:00:21','2016-05-20 10:00:21'),(56,23,232,'2016-05-16','approved','08:15:00',0.00,NULL,NULL,'2016-05-25 10:00:21',NULL,0,'2016-05-20 10:00:21','2016-05-20 10:00:21'),(57,23,233,'2016-05-16','approved','04:45:00',0.00,NULL,NULL,'2016-05-25 10:00:21',NULL,0,'2016-05-20 10:00:21','2016-05-20 10:00:21'),(242,23,225,'2016-05-23','approved','09:00:00',0.00,NULL,NULL,'2016-06-01 10:00:27',NULL,0,'2016-05-27 10:00:26','2016-05-27 10:00:26'),(243,23,226,'2016-05-23','approved','02:00:00',0.00,NULL,NULL,'2016-06-01 10:00:27',NULL,0,'2016-05-27 10:00:26','2016-05-27 10:00:26'),(244,23,227,'2016-05-23','approved','11:00:00',0.00,NULL,NULL,'2016-06-01 10:00:27',NULL,0,'2016-05-27 10:00:26','2016-05-27 10:00:26'),(245,23,228,'2016-05-23','approved','05:00:00',0.00,NULL,NULL,'2016-06-01 10:00:27',NULL,0,'2016-05-27 10:00:26','2016-05-27 10:00:26'),(246,23,229,'2016-05-23','approved','02:30:00',0.00,NULL,NULL,'2016-06-01 10:00:27',NULL,0,'2016-05-27 10:00:26','2016-05-27 10:00:26'),(247,23,230,'2016-05-23','approved','02:00:00',0.00,NULL,NULL,'2016-06-01 10:00:27',NULL,0,'2016-05-27 10:00:26','2016-05-27 10:00:26'),(248,23,231,'2016-05-23','approved','02:45:00',0.00,NULL,NULL,'2016-06-01 10:00:27',NULL,0,'2016-05-27 10:00:26','2016-05-27 10:00:26'),(249,23,232,'2016-05-23','approved','08:15:00',0.00,NULL,NULL,'2016-06-01 10:00:27',NULL,0,'2016-05-27 10:00:26','2016-05-27 10:00:26'),(250,23,233,'2016-05-23','approved','06:00:00',0.00,NULL,NULL,'2016-06-01 10:00:27',NULL,0,'2016-05-27 10:00:26','2016-05-27 10:00:26'),(453,23,225,'2016-05-30','approved','09:00:00',0.00,NULL,NULL,'2016-06-08 10:00:32',NULL,0,'2016-06-03 10:00:26','2016-06-03 10:00:26'),(454,23,226,'2016-05-30','approved','02:00:00',0.00,NULL,NULL,'2016-06-08 10:00:32',NULL,0,'2016-06-03 10:00:26','2016-06-03 10:00:26'),(455,23,227,'2016-05-30','approved','11:00:00',0.00,NULL,NULL,'2016-06-08 10:00:32',NULL,0,'2016-06-03 10:00:26','2016-06-03 10:00:26'),(456,23,228,'2016-05-30','approved','05:00:00',0.00,NULL,NULL,'2016-06-08 10:00:32',NULL,0,'2016-06-03 10:00:26','2016-06-03 10:00:26'),(457,23,229,'2016-05-30','approved','02:30:00',0.00,NULL,NULL,'2016-06-08 10:00:32',NULL,0,'2016-06-03 10:00:26','2016-06-03 10:00:26'),(458,23,230,'2016-05-30','approved','02:00:00',0.00,NULL,NULL,'2016-06-08 10:00:32',NULL,0,'2016-06-03 10:00:27','2016-06-03 10:00:27'),(459,23,231,'2016-05-30','approved','02:45:00',0.00,NULL,NULL,'2016-06-08 10:00:32',NULL,0,'2016-06-03 10:00:27','2016-06-03 10:00:27'),(460,23,232,'2016-05-30','approved','08:15:00',0.00,NULL,NULL,'2016-06-08 10:00:32',NULL,0,'2016-06-03 10:00:27','2016-06-03 10:00:27'),(461,23,233,'2016-05-30','approved','06:00:00',0.00,NULL,NULL,'2016-06-08 10:00:32',NULL,0,'2016-06-03 10:00:27','2016-06-03 10:00:27'),(640,23,225,'2016-06-06','approved','18:30:00',0.00,NULL,NULL,'2016-06-15 10:00:29',NULL,0,'2016-06-10 10:00:26','2016-06-10 10:00:26'),(641,23,226,'2016-06-06','approved','04:00:00',0.00,NULL,NULL,'2016-06-15 10:00:29',NULL,0,'2016-06-10 10:00:26','2016-06-10 10:00:26'),(642,23,227,'2016-06-06','approved','13:00:00',0.00,NULL,NULL,'2016-06-15 10:00:29',NULL,0,'2016-06-10 10:00:26','2016-06-10 10:00:26'),(643,23,228,'2016-06-06','approved','07:00:00',0.00,NULL,NULL,'2016-06-15 10:00:29',NULL,0,'2016-06-10 10:00:27','2016-06-10 10:00:27'),(644,23,229,'2016-06-06','approved','09:30:00',0.00,NULL,NULL,'2016-06-15 10:00:29',NULL,0,'2016-06-10 10:00:27','2016-06-10 10:00:27'),(645,23,230,'2016-06-06','approved','04:00:00',0.00,NULL,NULL,'2016-06-15 10:00:29',NULL,0,'2016-06-10 10:00:27','2016-06-10 10:00:27'),(646,23,231,'2016-06-06','approved','12:15:00',0.00,NULL,NULL,'2016-06-15 10:00:29',NULL,0,'2016-06-10 10:00:27','2016-06-10 10:00:27'),(647,23,232,'2016-06-06','approved','16:45:00',0.00,NULL,NULL,'2016-06-15 10:00:29',NULL,0,'2016-06-10 10:00:27','2016-06-10 10:00:27'),(648,23,233,'2016-06-06','approved','06:00:00',0.00,NULL,NULL,'2016-06-15 10:00:29',NULL,0,'2016-06-10 10:00:27','2016-06-10 10:00:27'),(649,23,234,'2016-06-06','approved','01:00:00',0.00,NULL,NULL,'2016-06-15 10:00:29',NULL,0,'2016-06-10 10:00:27','2016-06-10 10:00:27'),(867,23,225,'2016-06-13','approved','13:30:00',0.00,NULL,NULL,'2016-06-22 10:00:32',NULL,0,'2016-06-17 09:07:45','2016-06-17 09:07:45'),(868,23,226,'2016-06-13','approved','02:00:00',0.00,NULL,NULL,'2016-06-22 10:00:32',NULL,0,'2016-06-17 09:07:45','2016-06-17 09:07:45'),(869,23,227,'2016-06-13','approved','08:00:00',0.00,NULL,NULL,'2016-06-22 10:00:32',NULL,0,'2016-06-17 09:07:45','2016-06-17 09:07:45'),(870,23,228,'2016-06-13','approved','05:00:00',0.00,NULL,NULL,'2016-06-22 10:00:32',NULL,0,'2016-06-17 09:07:45','2016-06-17 09:07:45'),(871,23,229,'2016-06-13','approved','02:30:00',0.00,NULL,NULL,'2016-06-22 10:00:32',NULL,0,'2016-06-17 09:07:46','2016-06-17 09:07:46'),(872,23,230,'2016-06-13','approved','02:00:00',0.00,NULL,NULL,'2016-06-22 10:00:32',NULL,0,'2016-06-17 09:07:46','2016-06-17 09:07:46'),(873,23,231,'2016-06-13','approved','08:15:00',0.00,NULL,NULL,'2016-06-22 10:00:32',NULL,0,'2016-06-17 09:07:46','2016-06-17 09:07:46'),(874,23,232,'2016-06-13','approved','06:15:00',0.00,NULL,NULL,'2016-06-22 10:00:32',NULL,0,'2016-06-17 09:07:46','2016-06-17 09:07:46'),(875,23,233,'2016-06-13','approved','07:00:00',0.00,NULL,NULL,'2016-06-22 10:00:32',NULL,0,'2016-06-17 09:07:46','2016-06-17 09:07:46'),(876,23,234,'2016-06-13','approved','01:00:00',0.00,NULL,NULL,'2016-06-22 10:00:33',NULL,0,'2016-06-17 09:07:46','2016-06-17 09:07:46'),(1040,23,225,'2016-06-20','approved','13:30:00',0.00,NULL,NULL,'2016-06-29 10:00:26',NULL,0,'2016-06-24 10:00:27','2016-06-24 10:00:27'),(1041,23,226,'2016-06-20','approved','02:00:00',0.00,NULL,NULL,'2016-06-29 10:00:26',NULL,0,'2016-06-24 10:00:27','2016-06-24 10:00:27'),(1042,23,227,'2016-06-20','approved','08:00:00',0.00,NULL,NULL,'2016-06-29 10:00:26',NULL,0,'2016-06-24 10:00:27','2016-06-24 10:00:27'),(1043,23,228,'2016-06-20','approved','05:00:00',0.00,NULL,NULL,'2016-06-29 10:00:26',NULL,0,'2016-06-24 10:00:27','2016-06-24 10:00:27'),(1044,23,229,'2016-06-20','approved','02:30:00',0.00,NULL,NULL,'2016-06-29 10:00:26',NULL,0,'2016-06-24 10:00:27','2016-06-24 10:00:27'),(1045,23,230,'2016-06-20','approved','02:00:00',0.00,NULL,NULL,'2016-06-29 10:00:26',NULL,0,'2016-06-24 10:00:27','2016-06-24 10:00:27'),(1046,23,231,'2016-06-20','approved','08:15:00',0.00,NULL,NULL,'2016-06-29 10:00:26',NULL,0,'2016-06-24 10:00:27','2016-06-24 10:00:27'),(1047,23,232,'2016-06-20','approved','06:15:00',0.00,NULL,NULL,'2016-06-29 10:00:26',NULL,0,'2016-06-24 10:00:27','2016-06-24 10:00:27'),(1048,23,233,'2016-06-20','approved','07:00:00',0.00,NULL,NULL,'2016-06-29 10:00:26',NULL,0,'2016-06-24 10:00:27','2016-06-24 10:00:27'),(1049,23,234,'2016-06-20','approved','01:00:00',0.00,NULL,NULL,'2016-06-29 10:00:26',NULL,0,'2016-06-24 10:00:27','2016-06-24 10:00:27'),(1243,23,225,'2016-06-27','approved','13:30:00',0.00,NULL,NULL,'2016-07-06 10:00:26',NULL,0,'2016-07-01 10:00:24','2016-07-01 10:00:24'),(1244,23,226,'2016-06-27','approved','02:00:00',0.00,NULL,NULL,'2016-07-06 10:00:26',NULL,0,'2016-07-01 10:00:24','2016-07-01 10:00:24'),(1245,23,227,'2016-06-27','approved','08:00:00',0.00,NULL,NULL,'2016-07-06 10:00:26',NULL,0,'2016-07-01 10:00:24','2016-07-01 10:00:24'),(1246,23,228,'2016-06-27','approved','05:00:00',0.00,NULL,NULL,'2016-07-06 10:00:26',NULL,0,'2016-07-01 10:00:24','2016-07-01 10:00:24'),(1247,23,229,'2016-06-27','approved','02:30:00',0.00,NULL,NULL,'2016-07-06 10:00:26',NULL,0,'2016-07-01 10:00:24','2016-07-01 10:00:24'),(1248,23,230,'2016-06-27','approved','02:00:00',0.00,NULL,NULL,'2016-07-06 10:00:26',NULL,0,'2016-07-01 10:00:24','2016-07-01 10:00:24'),(1249,23,231,'2016-06-27','approved','08:15:00',0.00,NULL,NULL,'2016-07-06 10:00:26',NULL,0,'2016-07-01 10:00:24','2016-07-01 10:00:24'),(1250,23,232,'2016-06-27','approved','06:15:00',0.00,NULL,NULL,'2016-07-06 10:00:26',NULL,0,'2016-07-01 10:00:24','2016-07-01 10:00:24'),(1251,23,233,'2016-06-27','approved','07:00:00',0.00,NULL,NULL,'2016-07-06 10:00:26',NULL,0,'2016-07-01 10:00:24','2016-07-01 10:00:24'),(1252,23,234,'2016-06-27','approved','01:00:00',0.00,NULL,NULL,'2016-07-06 10:00:26',NULL,0,'2016-07-01 10:00:24','2016-07-01 10:00:24'),(1399,23,225,'2016-04-04','approved','00:00:00',0.00,NULL,NULL,'2016-07-06 10:00:29',NULL,0,'2016-07-01 11:19:43','2016-07-01 11:19:43'),(1400,23,226,'2016-04-04','approved','00:00:00',0.00,NULL,NULL,'2016-07-06 10:00:29',NULL,0,'2016-07-01 11:19:43','2016-07-01 11:19:43'),(1401,23,227,'2016-04-04','approved','00:45:00',0.00,NULL,NULL,'2016-07-06 10:00:29',NULL,0,'2016-07-01 11:19:43','2016-07-01 11:19:43'),(1402,23,228,'2016-04-04','approved','01:00:00',0.00,NULL,NULL,'2016-07-06 10:00:29',NULL,0,'2016-07-01 11:19:43','2016-07-01 11:19:43'),(1403,23,231,'2016-04-04','approved','00:45:00',0.00,NULL,NULL,'2016-07-06 10:00:29',NULL,0,'2016-07-01 11:19:43','2016-07-01 11:19:43'),(1404,23,232,'2016-04-04','approved','06:15:00',0.00,NULL,NULL,'2016-07-06 10:00:29',NULL,0,'2016-07-01 11:19:43','2016-07-01 11:19:43'),(1405,23,233,'2016-04-04','approved','01:00:00',0.00,NULL,NULL,'2016-07-06 10:00:29',NULL,0,'2016-07-01 11:19:43','2016-07-01 11:19:43'),(1588,23,225,'2016-07-04','approved','06:00:00',0.00,NULL,NULL,'2016-07-13 10:00:23',NULL,0,'2016-07-06 15:40:13','2016-07-06 15:40:13'),(1589,23,226,'2016-07-04','approved','08:30:00',0.00,NULL,NULL,'2016-07-13 10:00:23',NULL,0,'2016-07-06 15:40:13','2016-07-06 15:40:13'),(1590,23,227,'2016-07-04','approved','08:00:00',0.00,NULL,NULL,'2016-07-13 10:00:23',NULL,0,'2016-07-06 15:40:13','2016-07-06 15:40:13'),(1591,23,228,'2016-07-04','approved','05:00:00',0.00,NULL,NULL,'2016-07-13 10:00:23',NULL,0,'2016-07-06 15:40:13','2016-07-06 15:40:13'),(1592,23,229,'2016-07-04','approved','02:30:00',0.00,NULL,NULL,'2016-07-13 10:00:23',NULL,0,'2016-07-06 15:40:13','2016-07-06 15:40:13'),(1593,23,230,'2016-07-04','approved','08:30:00',0.00,NULL,NULL,'2016-07-13 10:00:23',NULL,0,'2016-07-06 15:40:13','2016-07-06 15:40:13'),(1594,23,231,'2016-07-04','approved','00:45:00',0.00,NULL,NULL,'2016-07-13 10:00:23',NULL,0,'2016-07-06 15:40:13','2016-07-06 15:40:13'),(1595,23,232,'2016-07-04','approved','06:15:00',0.00,NULL,NULL,'2016-07-13 10:00:23',NULL,0,'2016-07-06 15:40:13','2016-07-06 15:40:13'),(1596,23,233,'2016-07-04','approved','13:30:00',0.00,NULL,NULL,'2016-07-13 10:00:23',NULL,0,'2016-07-06 15:40:14','2016-07-06 15:40:14'),(1597,23,234,'2016-07-04','approved','01:00:00',0.00,NULL,NULL,'2016-07-13 10:00:23',NULL,0,'2016-07-06 15:40:14','2016-07-06 15:40:14'),(1837,23,225,'2016-07-11','approved','13:30:00',0.00,NULL,NULL,'2016-07-20 10:00:25',NULL,0,'2016-07-15 10:00:26','2016-07-15 10:00:26'),(1838,23,226,'2016-07-11','approved','02:00:00',0.00,NULL,NULL,'2016-07-20 10:00:25',NULL,0,'2016-07-15 10:00:26','2016-07-15 10:00:26'),(1839,23,227,'2016-07-11','approved','08:00:00',0.00,NULL,NULL,'2016-07-20 10:00:25',NULL,0,'2016-07-15 10:00:26','2016-07-15 10:00:26'),(1840,23,228,'2016-07-11','approved','05:00:00',0.00,NULL,NULL,'2016-07-20 10:00:25',NULL,0,'2016-07-15 10:00:26','2016-07-15 10:00:26'),(1841,23,229,'2016-07-11','approved','02:30:00',0.00,NULL,NULL,'2016-07-20 10:00:25',NULL,0,'2016-07-15 10:00:26','2016-07-15 10:00:26'),(1842,23,230,'2016-07-11','approved','02:00:00',0.00,NULL,NULL,'2016-07-20 10:00:25',NULL,0,'2016-07-15 10:00:26','2016-07-15 10:00:26'),(1843,23,231,'2016-07-11','approved','08:15:00',0.00,NULL,NULL,'2016-07-20 10:00:25',NULL,0,'2016-07-15 10:00:26','2016-07-15 10:00:26'),(1844,23,232,'2016-07-11','approved','06:15:00',0.00,NULL,NULL,'2016-07-20 10:00:25',NULL,0,'2016-07-15 10:00:26','2016-07-15 10:00:26'),(1845,23,233,'2016-07-11','approved','07:00:00',0.00,NULL,NULL,'2016-07-20 10:00:25',NULL,0,'2016-07-15 10:00:26','2016-07-15 10:00:26'),(1846,23,234,'2016-07-11','approved','01:00:00',0.00,NULL,NULL,'2016-07-20 10:00:25',NULL,0,'2016-07-15 10:00:26','2016-07-15 10:00:26'),(2080,23,225,'2016-07-18','approved','13:30:00',0.00,NULL,NULL,'2016-07-27 10:00:26',NULL,0,'2016-07-22 10:00:25','2016-07-22 10:00:25'),(2081,23,226,'2016-07-18','approved','02:00:00',0.00,NULL,NULL,'2016-07-27 10:00:26',NULL,0,'2016-07-22 10:00:25','2016-07-22 10:00:25'),(2082,23,227,'2016-07-18','approved','08:00:00',0.00,NULL,NULL,'2016-07-27 10:00:26',NULL,0,'2016-07-22 10:00:25','2016-07-22 10:00:25'),(2083,23,228,'2016-07-18','approved','05:00:00',0.00,NULL,NULL,'2016-07-27 10:00:26',NULL,0,'2016-07-22 10:00:25','2016-07-22 10:00:25'),(2084,23,229,'2016-07-18','approved','02:30:00',0.00,NULL,NULL,'2016-07-27 10:00:26',NULL,0,'2016-07-22 10:00:25','2016-07-22 10:00:25'),(2085,23,230,'2016-07-18','approved','02:00:00',0.00,NULL,NULL,'2016-07-27 10:00:26',NULL,0,'2016-07-22 10:00:25','2016-07-22 10:00:25'),(2086,23,231,'2016-07-18','approved','08:15:00',0.00,NULL,NULL,'2016-07-27 10:00:26',NULL,0,'2016-07-22 10:00:25','2016-07-22 10:00:25'),(2087,23,232,'2016-07-18','approved','06:15:00',0.00,NULL,NULL,'2016-07-27 10:00:26',NULL,0,'2016-07-22 10:00:25','2016-07-22 10:00:25'),(2088,23,233,'2016-07-18','approved','07:00:00',0.00,NULL,NULL,'2016-07-27 10:00:26',NULL,0,'2016-07-22 10:00:25','2016-07-22 10:00:25'),(2089,23,234,'2016-07-18','approved','01:00:00',0.00,NULL,NULL,'2016-07-27 10:00:26',NULL,0,'2016-07-22 10:00:25','2016-07-22 10:00:25'),(2323,23,225,'2016-07-25','approved','13:30:00',0.00,NULL,NULL,'2016-08-03 10:00:28',NULL,0,'2016-07-29 10:00:25','2016-07-29 10:00:25'),(2324,23,226,'2016-07-25','approved','02:00:00',0.00,NULL,NULL,'2016-08-03 10:00:28',NULL,0,'2016-07-29 10:00:25','2016-07-29 10:00:25'),(2325,23,227,'2016-07-25','approved','01:00:00',0.00,NULL,NULL,'2016-08-03 10:00:28',NULL,0,'2016-07-29 10:00:25','2016-07-29 10:00:25'),(2326,23,228,'2016-07-25','approved','02:00:00',0.00,NULL,NULL,'2016-08-03 10:00:28',NULL,0,'2016-07-29 10:00:25','2016-07-29 10:00:25'),(2327,23,230,'2016-07-25','approved','02:00:00',0.00,NULL,NULL,'2016-08-03 10:00:28',NULL,0,'2016-07-29 10:00:25','2016-07-29 10:00:25'),(2328,23,231,'2016-07-25','approved','07:30:00',0.00,NULL,NULL,'2016-08-03 10:00:28',NULL,0,'2016-07-29 10:00:25','2016-07-29 10:00:25'),(2329,23,232,'2016-07-25','approved','06:15:00',0.00,NULL,NULL,'2016-08-03 10:00:28',NULL,0,'2016-07-29 10:00:25','2016-07-29 10:00:25'),(2330,23,233,'2016-07-25','approved','05:45:00',0.00,NULL,NULL,'2016-08-03 10:00:28',NULL,0,'2016-07-29 10:00:25','2016-07-29 10:00:25'),(2331,23,234,'2016-07-25','approved','01:00:00',0.00,NULL,NULL,'2016-08-03 10:00:28',NULL,0,'2016-07-29 10:00:25','2016-07-29 10:00:25'),(2540,23,225,'2016-08-01','approved','00:00:00',0.00,NULL,NULL,'2016-08-10 10:00:25',NULL,0,'2016-08-05 10:00:28','2016-08-05 10:00:28'),(2541,23,226,'2016-08-01','approved','02:00:00',0.00,NULL,NULL,'2016-08-10 10:00:25',NULL,0,'2016-08-05 10:00:28','2016-08-05 10:00:28'),(2542,23,232,'2016-08-01','approved','06:15:00',0.00,NULL,NULL,'2016-08-10 10:00:25',NULL,0,'2016-08-05 10:00:28','2016-08-05 10:00:28'),(2543,23,233,'2016-08-01','approved','04:00:00',0.00,NULL,NULL,'2016-08-10 10:00:25',NULL,0,'2016-08-05 10:00:28','2016-08-05 10:00:28'),(2691,23,225,'2016-08-08','approved','00:00:00',0.00,NULL,NULL,'2016-08-17 10:00:25',NULL,0,'2016-08-12 10:00:22','2016-08-12 10:00:22'),(2692,23,226,'2016-08-08','approved','02:00:00',0.00,NULL,NULL,'2016-08-17 10:00:25',NULL,0,'2016-08-12 10:00:22','2016-08-12 10:00:22'),(2693,23,232,'2016-08-08','approved','06:15:00',0.00,NULL,NULL,'2016-08-17 10:00:25',NULL,0,'2016-08-12 10:00:22','2016-08-12 10:00:22'),(2694,23,233,'2016-08-08','approved','04:00:00',0.00,NULL,NULL,'2016-08-17 10:00:25',NULL,0,'2016-08-12 10:00:23','2016-08-12 10:00:23'),(2827,23,225,'2016-08-15','approved','00:00:00',0.00,NULL,NULL,'2016-08-24 10:00:22',NULL,0,'2016-08-19 10:00:23','2016-08-19 10:00:23'),(2828,23,226,'2016-08-15','approved','02:00:00',0.00,NULL,NULL,'2016-08-24 10:00:22',NULL,0,'2016-08-19 10:00:23','2016-08-19 10:00:23'),(2829,23,232,'2016-08-15','approved','06:15:00',0.00,NULL,NULL,'2016-08-24 10:00:22',NULL,0,'2016-08-19 10:00:23','2016-08-19 10:00:23'),(2830,23,233,'2016-08-15','approved','04:00:00',0.00,NULL,NULL,'2016-08-24 10:00:22',NULL,0,'2016-08-19 10:00:23','2016-08-19 10:00:23'),(2950,23,225,'2016-08-22','approved','00:00:00',0.00,NULL,NULL,'2016-08-31 10:00:22',NULL,0,'2016-08-26 10:00:23','2016-08-26 10:00:23'),(2951,23,226,'2016-08-22','approved','02:00:00',0.00,NULL,NULL,'2016-08-31 10:00:22',NULL,0,'2016-08-26 10:00:23','2016-08-26 10:00:23'),(2952,23,232,'2016-08-22','approved','06:15:00',0.00,NULL,NULL,'2016-08-31 10:00:22',NULL,0,'2016-08-26 10:00:23','2016-08-26 10:00:23'),(2953,23,233,'2016-08-22','approved','04:00:00',0.00,NULL,NULL,'2016-08-31 10:00:22',NULL,0,'2016-08-26 10:00:23','2016-08-26 10:00:23'),(3068,23,225,'2016-08-29','approved','00:00:00',0.00,NULL,NULL,'2016-09-07 10:00:23',NULL,0,'2016-09-02 10:00:24','2016-09-02 10:00:24'),(3069,23,226,'2016-08-29','approved','00:00:00',0.00,NULL,NULL,'2016-09-07 10:00:23',NULL,0,'2016-09-02 10:00:24','2016-09-02 10:00:24'),(3070,23,232,'2016-08-29','approved','06:15:00',0.00,NULL,NULL,'2016-09-07 10:00:23',NULL,0,'2016-09-02 10:00:24','2016-09-02 10:00:24'),(3071,23,233,'2016-08-29','approved','01:00:00',0.00,NULL,NULL,'2016-09-07 10:00:23',NULL,0,'2016-09-02 10:00:24','2016-09-02 10:00:24'),(3173,23,225,'2016-09-05','approved','00:00:00',0.00,NULL,NULL,'2016-09-14 10:00:22',NULL,0,'2016-09-09 10:00:22','2016-09-09 10:00:22'),(3174,23,226,'2016-09-05','approved','00:00:00',0.00,NULL,NULL,'2016-09-14 10:00:22',NULL,0,'2016-09-09 10:00:22','2016-09-09 10:00:22'),(3175,23,232,'2016-09-05','approved','06:15:00',0.00,NULL,NULL,'2016-09-14 10:00:22',NULL,0,'2016-09-09 10:00:22','2016-09-09 10:00:22'),(3176,23,233,'2016-09-05','approved','01:00:00',0.00,NULL,NULL,'2016-09-14 10:00:23',NULL,0,'2016-09-09 10:00:22','2016-09-09 10:00:22'),(3278,23,225,'2016-09-12','approved','00:00:00',0.00,NULL,NULL,'2016-09-21 10:00:23',NULL,0,'2016-09-16 10:00:22','2016-09-16 10:00:22'),(3279,23,226,'2016-09-12','approved','00:00:00',0.00,NULL,NULL,'2016-09-21 10:00:23',NULL,0,'2016-09-16 10:00:22','2016-09-16 10:00:22'),(3280,23,232,'2016-09-12','approved','06:15:00',0.00,NULL,NULL,'2016-09-21 10:00:23',NULL,0,'2016-09-16 10:00:22','2016-09-16 10:00:22'),(3281,23,233,'2016-09-12','approved','01:00:00',0.00,NULL,NULL,'2016-09-21 10:00:23',NULL,0,'2016-09-16 10:00:22','2016-09-16 10:00:22'),(3379,23,225,'2016-09-19','approved','00:00:00',0.00,NULL,NULL,'2016-09-28 10:00:22',NULL,0,'2016-09-23 10:00:21','2016-09-23 10:00:21'),(3380,23,226,'2016-09-19','approved','00:00:00',0.00,NULL,NULL,'2016-09-28 10:00:22',NULL,0,'2016-09-23 10:00:21','2016-09-23 10:00:21'),(3381,23,232,'2016-09-19','approved','06:15:00',0.00,NULL,NULL,'2016-09-28 10:00:22',NULL,0,'2016-09-23 10:00:21','2016-09-23 10:00:21'),(3382,23,233,'2016-09-19','approved','01:00:00',0.00,NULL,NULL,'2016-09-28 10:00:22',NULL,0,'2016-09-23 10:00:21','2016-09-23 10:00:21'),(3490,23,225,'2016-09-26','approved','00:00:00',0.00,NULL,NULL,'2016-10-05 10:00:21',NULL,0,'2016-09-29 10:50:02','2016-09-29 10:50:02'),(3491,23,226,'2016-09-26','approved','00:00:00',0.00,NULL,NULL,'2016-10-05 10:00:21',NULL,0,'2016-09-29 10:50:02','2016-09-29 10:50:02'),(3492,23,232,'2016-09-26','approved','06:15:00',0.00,NULL,NULL,'2016-10-05 10:00:21',NULL,0,'2016-09-29 10:50:02','2016-09-29 10:50:02'),(3493,23,233,'2016-09-26','approved','01:00:00',0.00,NULL,NULL,'2016-10-05 10:00:21',NULL,0,'2016-09-29 10:50:02','2016-09-29 10:50:02'),(3601,23,225,'2016-10-03','approved','00:00:00',0.00,NULL,NULL,'2016-10-12 10:00:22',NULL,0,'2016-10-07 10:00:22','2016-10-07 10:00:22'),(3602,23,226,'2016-10-03','approved','00:00:00',0.00,NULL,NULL,'2016-10-12 10:00:22',NULL,0,'2016-10-07 10:00:22','2016-10-07 10:00:22'),(3603,23,232,'2016-10-03','approved','06:15:00',0.00,NULL,NULL,'2016-10-12 10:00:22',NULL,0,'2016-10-07 10:00:22','2016-10-07 10:00:22'),(3604,23,233,'2016-10-03','approved','01:00:00',0.00,NULL,NULL,'2016-10-12 10:00:22',NULL,0,'2016-10-07 10:00:22','2016-10-07 10:00:22'),(3712,23,225,'2016-10-10','approved','00:00:00',0.00,NULL,NULL,'2016-10-19 10:00:22',NULL,0,'2016-10-14 10:00:22','2016-10-14 10:00:22'),(3713,23,226,'2016-10-10','approved','00:00:00',0.00,NULL,NULL,'2016-10-19 10:00:22',NULL,0,'2016-10-14 10:00:22','2016-10-14 10:00:22'),(3714,23,232,'2016-10-10','approved','06:15:00',0.00,NULL,NULL,'2016-10-19 10:00:22',NULL,0,'2016-10-14 10:00:22','2016-10-14 10:00:22'),(3715,23,233,'2016-10-10','approved','01:00:00',0.00,NULL,NULL,'2016-10-19 10:00:22',NULL,0,'2016-10-14 10:00:22','2016-10-14 10:00:22'),(3827,23,225,'2016-10-17','approved','00:00:00',0.00,NULL,NULL,'2016-10-26 10:00:44',NULL,0,'2016-10-21 09:45:23','2016-10-21 09:45:23'),(3828,23,226,'2016-10-17','approved','00:00:00',0.00,NULL,NULL,'2016-10-26 10:00:44',NULL,0,'2016-10-21 09:45:23','2016-10-21 09:45:23'),(3829,23,232,'2016-10-17','approved','06:15:00',0.00,NULL,NULL,'2016-10-26 10:00:44',NULL,0,'2016-10-21 09:45:23','2016-10-21 09:45:23'),(3830,23,233,'2016-10-17','approved','01:00:00',0.00,NULL,NULL,'2016-10-26 10:00:44',NULL,0,'2016-10-21 09:45:23','2016-10-21 09:45:23'),(3945,23,225,'2016-10-24','approved','00:00:00',0.00,NULL,NULL,'2016-11-02 09:00:21',NULL,0,'2016-10-28 10:00:21','2016-10-28 10:00:21'),(3946,23,226,'2016-10-24','approved','00:00:00',0.00,NULL,NULL,'2016-11-02 09:00:21',NULL,0,'2016-10-28 10:00:21','2016-10-28 10:00:21'),(3947,23,232,'2016-10-24','approved','06:15:00',0.00,NULL,NULL,'2016-11-02 09:00:21',NULL,0,'2016-10-28 10:00:21','2016-10-28 10:00:21'),(3948,23,233,'2016-10-24','approved','01:00:00',0.00,NULL,NULL,'2016-11-02 09:00:22',NULL,0,'2016-10-28 10:00:21','2016-10-28 10:00:21'),(4062,23,225,'2016-10-31','approved','00:00:00',0.00,NULL,NULL,'2016-11-09 09:00:22',NULL,0,'2016-11-02 10:30:30','2016-11-02 10:30:30'),(4063,23,226,'2016-10-31','approved','00:00:00',0.00,NULL,NULL,'2016-11-09 09:00:22',NULL,0,'2016-11-02 10:30:30','2016-11-02 10:30:30'),(4064,23,232,'2016-10-31','approved','06:15:00',0.00,NULL,NULL,'2016-11-09 09:00:22',NULL,0,'2016-11-02 10:30:30','2016-11-02 10:30:30'),(4065,23,233,'2016-10-31','approved','01:00:00',0.00,NULL,NULL,'2016-11-09 09:00:22',NULL,0,'2016-11-02 10:30:30','2016-11-02 10:30:30'),(4192,23,225,'2016-11-07','approved','00:00:00',0.00,NULL,NULL,'2016-11-16 09:00:21',NULL,0,'2016-11-07 09:05:47','2016-11-07 09:05:47'),(4193,23,226,'2016-11-07','approved','00:00:00',0.00,NULL,NULL,'2016-11-16 09:00:21',NULL,0,'2016-11-07 09:05:47','2016-11-07 09:05:47'),(4194,23,232,'2016-11-07','approved','06:15:00',0.00,NULL,NULL,'2016-11-16 09:00:22',NULL,0,'2016-11-07 09:05:47','2016-11-07 09:05:47'),(4195,23,233,'2016-11-07','approved','01:00:00',0.00,NULL,NULL,'2016-11-16 09:00:22',NULL,0,'2016-11-07 09:05:47','2016-11-07 09:05:47'),(4335,23,225,'2016-11-14','approved','01:05:00',0.00,NULL,NULL,'2016-11-23 09:00:22',NULL,0,'2016-11-18 09:00:25','2016-11-18 09:00:25'),(4336,23,226,'2016-11-14','approved','00:00:00',0.00,NULL,NULL,'2016-11-23 09:00:22',NULL,0,'2016-11-18 09:00:25','2016-11-18 09:00:25'),(4337,23,232,'2016-11-14','approved','06:15:00',0.00,NULL,NULL,'2016-11-23 09:00:22',NULL,0,'2016-11-18 09:00:25','2016-11-18 09:00:25'),(4338,23,233,'2016-11-14','approved','01:00:00',0.00,NULL,NULL,'2016-11-23 09:00:22',NULL,0,'2016-11-18 09:00:25','2016-11-18 09:00:25'),(4482,23,225,'2016-11-21','approved','01:05:00',0.00,NULL,NULL,'2016-11-30 09:00:22',NULL,0,'2016-11-24 14:11:49','2016-11-24 14:11:49'),(4483,23,226,'2016-11-21','approved','00:00:00',0.00,NULL,NULL,'2016-11-30 09:00:22',NULL,0,'2016-11-24 14:11:49','2016-11-24 14:11:49'),(4484,23,232,'2016-11-21','approved','06:15:00',0.00,NULL,NULL,'2016-11-30 09:00:22',NULL,0,'2016-11-24 14:11:49','2016-11-24 14:11:49'),(4485,23,233,'2016-11-21','approved','01:00:00',0.00,NULL,NULL,'2016-11-30 09:00:22',NULL,0,'2016-11-24 14:11:49','2016-11-24 14:11:49'),(4633,23,225,'2016-11-28','approved','01:05:00',0.00,NULL,NULL,'2016-12-07 09:00:24',NULL,0,'2016-11-28 12:41:57','2016-11-28 12:41:57'),(4634,23,226,'2016-11-28','approved','00:00:00',0.00,NULL,NULL,'2016-12-07 09:00:24',NULL,0,'2016-11-28 12:41:57','2016-11-28 12:41:57'),(4635,23,232,'2016-11-28','approved','06:15:00',0.00,NULL,NULL,'2016-12-07 09:00:24',NULL,0,'2016-11-28 12:41:57','2016-11-28 12:41:57'),(4636,23,233,'2016-11-28','approved','01:00:00',0.00,NULL,NULL,'2016-12-07 09:00:24',NULL,0,'2016-11-28 12:41:57','2016-11-28 12:41:57'),(4782,23,225,'2016-12-05','approved','01:05:00',0.00,NULL,NULL,'2016-12-14 09:00:23',NULL,0,'2016-12-06 11:32:27','2016-12-06 11:32:27'),(4783,23,226,'2016-12-05','approved','00:00:00',0.00,NULL,NULL,'2016-12-14 09:00:23',NULL,0,'2016-12-06 11:32:27','2016-12-06 11:32:27'),(4784,23,232,'2016-12-05','approved','06:15:00',0.00,NULL,NULL,'2016-12-14 09:00:23',NULL,0,'2016-12-06 11:32:27','2016-12-06 11:32:27'),(4785,23,233,'2016-12-05','approved','01:00:00',0.00,NULL,NULL,'2016-12-14 09:00:23',NULL,0,'2016-12-06 11:32:27','2016-12-06 11:32:27'),(4918,23,225,'2016-12-12','approved','01:05:00',0.00,NULL,NULL,'2016-12-21 09:00:22',NULL,0,'2016-12-14 13:00:59','2016-12-14 13:00:59'),(4919,23,226,'2016-12-12','approved','00:00:00',0.00,NULL,NULL,'2016-12-21 09:00:22',NULL,0,'2016-12-14 13:00:59','2016-12-14 13:00:59'),(4920,23,232,'2016-12-12','approved','06:15:00',0.00,NULL,NULL,'2016-12-21 09:00:22',NULL,0,'2016-12-14 13:00:59','2016-12-14 13:00:59'),(4921,23,233,'2016-12-12','approved','01:00:00',0.00,NULL,NULL,'2016-12-21 09:00:22',NULL,0,'2016-12-14 13:00:59','2016-12-14 13:00:59'),(5068,23,225,'2016-12-19','approved','00:00:00',0.00,NULL,NULL,'2016-12-28 09:00:22',NULL,0,'2016-12-23 09:00:23','2016-12-23 09:00:23'),(5069,23,226,'2016-12-19','approved','00:00:00',0.00,NULL,NULL,'2016-12-28 09:00:22',NULL,0,'2016-12-23 09:00:23','2016-12-23 09:00:23'),(5070,23,232,'2016-12-19','approved','06:15:00',0.00,NULL,NULL,'2016-12-28 09:00:23',NULL,0,'2016-12-23 09:00:23','2016-12-23 09:00:23'),(5071,23,233,'2016-12-19','approved','01:00:00',0.00,NULL,NULL,'2016-12-28 09:00:23',NULL,0,'2016-12-23 09:00:23','2016-12-23 09:00:23'),(5220,23,225,'2016-12-26','approved','00:00:00',0.00,NULL,NULL,'2017-01-04 09:00:21',NULL,0,'2016-12-30 09:00:21','2016-12-30 09:00:21'),(5221,23,226,'2016-12-26','approved','00:00:00',0.00,NULL,NULL,'2017-01-04 09:00:21',NULL,0,'2016-12-30 09:00:21','2016-12-30 09:00:21'),(5338,23,225,'2017-12-25','approved','01:00:00',0.00,NULL,NULL,'2017-08-23 10:42:55',208,1,'2017-01-01 09:00:46','2017-08-23 10:42:55'),(5339,23,226,'2017-12-25','approved','00:00:00',0.00,NULL,NULL,'2018-01-03 09:00:21',NULL,0,'2017-01-01 09:00:46','2017-01-01 09:00:46'),(5425,23,225,'2017-01-02','approved','00:00:00',0.00,NULL,NULL,'2017-01-11 09:00:22',NULL,0,'2017-01-06 09:00:21','2017-01-06 09:00:21'),(5426,23,226,'2017-01-02','approved','00:00:00',0.00,NULL,NULL,'2017-01-11 09:00:22',NULL,0,'2017-01-06 09:00:21','2017-01-06 09:00:21'),(5522,23,225,'2017-01-09','approved','00:00:00',0.00,NULL,NULL,'2017-01-18 09:00:21',NULL,0,'2017-01-13 09:00:22','2017-01-13 09:00:22'),(5523,23,226,'2017-01-09','approved','00:00:00',0.00,NULL,NULL,'2017-01-18 09:00:21',NULL,0,'2017-01-13 09:00:22','2017-01-13 09:00:22'),(5624,23,225,'2017-01-16','approved','00:00:00',0.00,NULL,NULL,'2017-01-25 09:00:22',NULL,0,'2017-01-18 14:43:54','2017-01-18 14:43:54'),(5625,23,226,'2017-01-16','approved','00:00:00',0.00,NULL,NULL,'2017-01-25 09:00:22',NULL,0,'2017-01-18 14:43:54','2017-01-18 14:43:54'),(5730,23,225,'2017-01-23','approved','00:00:00',0.00,NULL,NULL,'2017-02-01 09:00:22',NULL,0,'2017-01-27 09:00:21','2017-01-27 09:00:21'),(5731,23,226,'2017-01-23','approved','00:00:00',0.00,NULL,NULL,'2017-02-01 09:00:22',NULL,0,'2017-01-27 09:00:21','2017-01-27 09:00:21'),(5838,23,225,'2017-01-30','approved','00:00:00',0.00,NULL,NULL,'2017-02-08 09:00:21',NULL,0,'2017-02-03 09:00:21','2017-02-03 09:00:21'),(5839,23,226,'2017-01-30','approved','00:00:00',0.00,NULL,NULL,'2017-02-08 09:00:21',NULL,0,'2017-02-03 09:00:21','2017-02-03 09:00:21'),(5939,23,225,'2017-02-06','approved','00:00:00',0.00,NULL,NULL,'2017-02-15 09:00:23',NULL,0,'2017-02-10 09:00:21','2017-02-10 09:00:21'),(5940,23,226,'2017-02-06','approved','00:00:00',0.00,NULL,NULL,'2017-02-15 09:00:23',NULL,0,'2017-02-10 09:00:21','2017-02-10 09:00:21'),(6046,23,225,'2017-02-13','approved','37:30:00',0.00,NULL,NULL,'2017-02-22 09:00:22',NULL,0,'2017-02-17 09:00:23','2017-02-17 09:00:23'),(6047,23,226,'2017-02-13','approved','00:00:00',0.00,NULL,NULL,'2017-02-22 09:00:22',NULL,0,'2017-02-17 09:00:23','2017-02-17 09:00:23'),(6048,23,229,'2017-02-13','approved','30:00:00',0.00,NULL,NULL,'2017-02-22 09:00:22',NULL,0,'2017-02-17 09:00:23','2017-02-17 09:00:23'),(6190,23,225,'2017-02-20','approved','00:00:00',0.00,NULL,NULL,'2017-03-01 09:00:22',NULL,0,'2017-02-24 09:00:23','2017-02-24 09:00:23'),(6191,23,226,'2017-02-20','approved','00:00:00',0.00,NULL,NULL,'2017-03-01 09:00:22',NULL,0,'2017-02-24 09:00:23','2017-02-24 09:00:23'),(6348,23,225,'2017-02-27','approved','00:00:00',0.00,NULL,NULL,'2017-03-08 09:00:21',NULL,0,'2017-03-03 09:00:22','2017-03-03 09:00:22'),(6349,23,226,'2017-02-27','approved','00:00:00',0.00,NULL,NULL,'2017-03-08 09:00:21',NULL,0,'2017-03-03 09:00:22','2017-03-03 09:00:22'),(6513,23,225,'2017-03-06','approved','00:00:00',0.00,NULL,NULL,'2017-03-15 09:00:21',NULL,0,'2017-03-07 17:52:54','2017-03-07 17:52:54'),(6514,23,226,'2017-03-06','approved','00:00:00',0.00,NULL,NULL,'2017-03-15 09:00:21',NULL,0,'2017-03-07 17:52:54','2017-03-07 17:52:54'),(6704,23,225,'2017-03-13','approved','00:00:00',0.00,NULL,NULL,'2017-03-22 09:00:21',NULL,0,'2017-03-17 09:00:21','2017-03-17 09:00:21'),(6705,23,226,'2017-03-13','approved','00:00:00',0.00,NULL,NULL,'2017-03-22 09:00:21',NULL,0,'2017-03-17 09:00:21','2017-03-17 09:00:21'),(6894,23,225,'2017-03-20','approved','00:00:00',0.00,NULL,NULL,'2017-03-29 10:00:22',NULL,0,'2017-03-23 15:38:43','2017-03-23 15:38:43'),(6895,23,226,'2017-03-20','approved','00:00:00',0.00,NULL,NULL,'2017-03-29 10:00:22',NULL,0,'2017-03-23 15:38:43','2017-03-23 15:38:43'),(7093,23,225,'2017-03-27','approved','37:30:00',0.00,NULL,NULL,'2017-04-05 10:00:22',NULL,0,'2017-03-31 10:00:23','2017-03-31 10:00:23'),(7094,23,226,'2017-03-27','approved','00:00:00',0.00,NULL,NULL,'2017-04-05 10:00:22',NULL,0,'2017-03-31 10:00:23','2017-03-31 10:00:23'),(7095,23,230,'2017-03-27','approved','37:30:00',0.00,NULL,NULL,'2017-04-05 10:00:22',NULL,0,'2017-03-31 10:00:23','2017-03-31 10:00:23'),(7306,23,225,'2017-04-03','approved','00:00:00',0.00,NULL,NULL,'2017-04-12 10:00:22',NULL,0,'2017-04-07 10:00:24','2017-04-07 10:00:24'),(7307,23,226,'2017-04-03','approved','00:00:00',0.00,NULL,NULL,'2017-04-12 10:00:22',NULL,0,'2017-04-07 10:00:24','2017-04-07 10:00:24'),(7479,23,225,'2017-04-10','approved','00:00:00',0.00,NULL,NULL,'2017-04-19 10:00:21',NULL,0,'2017-04-14 10:00:32','2017-04-14 10:00:32'),(7480,23,226,'2017-04-10','approved','00:00:00',0.00,NULL,NULL,'2017-04-19 10:00:21',NULL,0,'2017-04-14 10:00:32','2017-04-14 10:00:32'),(7481,23,229,'2017-04-10','approved','30:00:00',0.00,NULL,NULL,'2017-04-19 10:00:21',NULL,0,'2017-04-14 10:00:32','2017-04-14 10:00:32'),(7482,23,233,'2017-04-10','approved','37:30:00',0.00,NULL,NULL,'2017-04-19 10:00:21',NULL,0,'2017-04-14 10:00:32','2017-04-14 10:00:32'),(7663,23,225,'2017-04-17','approved','00:00:00',0.00,NULL,NULL,'2017-04-26 10:00:22',NULL,0,'2017-04-21 10:00:22','2017-04-21 10:00:22'),(7664,23,226,'2017-04-17','approved','00:00:00',0.00,NULL,NULL,'2017-04-26 10:00:22',NULL,0,'2017-04-21 10:00:22','2017-04-21 10:00:22'),(7665,23,229,'2017-04-17','approved','30:00:00',0.00,NULL,NULL,'2017-04-26 10:00:22',NULL,0,'2017-04-21 10:00:22','2017-04-21 10:00:22'),(7874,23,225,'2017-04-24','approved','00:00:00',0.00,NULL,NULL,'2017-05-03 10:00:22',NULL,0,'2017-04-28 10:00:22','2017-04-28 10:00:22'),(7875,23,226,'2017-04-24','approved','00:00:00',0.00,NULL,NULL,'2017-05-03 10:00:22',NULL,0,'2017-04-28 10:00:22','2017-04-28 10:00:22'),(8084,23,225,'2017-05-01','approved','00:00:00',0.00,NULL,NULL,'2017-05-10 10:00:22',NULL,0,'2017-05-05 10:00:22','2017-05-05 10:00:22'),(8085,23,226,'2017-05-01','approved','00:00:00',0.00,NULL,NULL,'2017-05-10 10:00:22',NULL,0,'2017-05-05 10:00:22','2017-05-05 10:00:22'),(8271,23,225,'2017-05-08','approved','00:00:00',0.00,NULL,NULL,'2017-05-17 10:00:22',NULL,0,'2017-05-12 10:00:22','2017-05-12 10:00:22'),(8272,23,226,'2017-05-08','approved','00:00:00',0.00,NULL,NULL,'2017-05-17 10:00:22',NULL,0,'2017-05-12 10:00:22','2017-05-12 10:00:22'),(8455,23,225,'2017-05-15','approved','00:00:00',0.00,NULL,NULL,'2017-05-24 10:00:22',NULL,0,'2017-05-19 10:00:22','2017-05-19 10:00:22'),(8456,23,226,'2017-05-15','approved','00:00:00',0.00,NULL,NULL,'2017-05-24 10:00:22',NULL,0,'2017-05-19 10:00:22','2017-05-19 10:00:22'),(8656,23,225,'2017-05-22','approved','00:00:00',0.00,NULL,NULL,'2017-05-31 10:00:21',NULL,0,'2017-05-26 10:00:22','2017-05-26 10:00:22'),(8657,23,226,'2017-05-22','approved','00:00:00',0.00,NULL,NULL,'2017-05-31 10:00:22',NULL,0,'2017-05-26 10:00:22','2017-05-26 10:00:22'),(8868,23,225,'2017-05-29','approved','00:00:00',0.00,NULL,NULL,'2017-06-07 10:00:23',NULL,0,'2017-06-02 10:00:22','2017-06-02 10:00:22'),(8869,23,226,'2017-05-29','approved','00:00:00',0.00,NULL,NULL,'2017-06-07 10:00:23',NULL,0,'2017-06-02 10:00:22','2017-06-02 10:00:22'),(9035,23,225,'2017-06-05','approved','00:00:00',0.00,NULL,NULL,'2017-06-14 10:00:21',NULL,0,'2017-06-09 10:00:22','2017-06-09 10:00:22'),(9036,23,226,'2017-06-05','approved','00:00:00',0.00,NULL,NULL,'2017-06-14 10:00:21',NULL,0,'2017-06-09 10:00:22','2017-06-09 10:00:22'),(9234,23,225,'2017-06-12','approved','00:00:00',0.00,NULL,NULL,'2017-06-21 10:00:21',NULL,0,'2017-06-13 10:47:38','2017-06-13 10:47:38'),(9235,23,226,'2017-06-12','approved','00:00:00',0.00,NULL,NULL,'2017-06-21 10:00:21',NULL,0,'2017-06-13 10:47:38','2017-06-13 10:47:38'),(9446,23,225,'2017-06-19','approved','00:00:00',0.00,NULL,NULL,'2017-06-28 10:00:22',NULL,0,'2017-06-23 10:00:22','2017-06-23 10:00:22'),(9447,23,226,'2017-06-19','approved','00:00:00',0.00,NULL,NULL,'2017-06-28 10:00:22',NULL,0,'2017-06-23 10:00:22','2017-06-23 10:00:22'),(9652,23,225,'2017-06-26','approved','00:00:00',0.00,NULL,NULL,'2017-07-05 10:00:22',NULL,0,'2017-06-30 10:00:22','2017-06-30 10:00:22'),(9653,23,226,'2017-06-26','approved','00:00:00',0.00,NULL,NULL,'2017-07-05 10:00:22',NULL,0,'2017-06-30 10:00:22','2017-06-30 10:00:22'),(9855,23,225,'2017-07-03','approved','00:00:00',0.00,NULL,NULL,'2017-07-12 10:00:22',NULL,0,'2017-07-07 08:55:22','2017-07-07 08:55:22'),(9856,23,226,'2017-07-03','approved','00:00:00',0.00,NULL,NULL,'2017-07-12 10:00:22',NULL,0,'2017-07-07 08:55:22','2017-07-07 08:55:22'),(10064,23,225,'2017-07-10','approved','00:00:00',0.00,NULL,NULL,'2017-07-19 10:00:22',NULL,0,'2017-07-14 10:00:21','2017-07-14 10:00:21'),(10065,23,226,'2017-07-10','approved','00:00:00',0.00,NULL,NULL,'2017-07-19 10:00:22',NULL,0,'2017-07-14 10:00:21','2017-07-14 10:00:21'),(10276,23,225,'2017-07-17','approved','00:00:00',0.00,NULL,NULL,'2017-07-26 10:00:22',NULL,0,'2017-07-21 10:00:21','2017-07-21 10:00:21'),(10277,23,226,'2017-07-17','approved','00:00:00',0.00,NULL,NULL,'2017-07-26 10:00:22',NULL,0,'2017-07-21 10:00:21','2017-07-21 10:00:21'),(10472,23,225,'2017-07-24','approved','00:00:00',0.00,NULL,NULL,'2017-08-02 10:00:21',NULL,0,'2017-07-26 15:32:41','2017-07-26 15:32:41'),(10473,23,226,'2017-07-24','approved','00:00:00',0.00,NULL,NULL,'2017-08-02 10:00:21',NULL,0,'2017-07-26 15:32:41','2017-07-26 15:32:41'),(10627,23,225,'2017-07-31','approved','00:00:00',0.00,NULL,NULL,'2017-08-09 10:00:21',NULL,0,'2017-08-02 11:45:42','2017-08-02 11:45:42'),(10628,23,226,'2017-07-31','approved','00:00:00',0.00,NULL,NULL,'2017-08-09 10:00:21',NULL,0,'2017-08-02 11:45:42','2017-08-02 11:45:42'),(10787,23,225,'2017-08-07','approved','00:00:00',0.00,NULL,NULL,'2017-08-16 10:00:21',NULL,0,'2017-08-11 10:00:21','2017-08-11 10:00:21'),(10788,23,226,'2017-08-07','approved','00:00:00',0.00,NULL,NULL,'2017-08-16 10:00:21',NULL,0,'2017-08-11 10:00:21','2017-08-11 10:00:21'),(10944,23,225,'2017-08-14','approved','00:00:00',0.00,NULL,NULL,'2017-08-23 10:00:22',NULL,0,'2017-08-16 11:08:23','2017-08-16 11:08:23'),(10945,23,226,'2017-08-14','approved','00:00:00',0.00,NULL,NULL,'2017-08-23 10:00:22',NULL,0,'2017-08-16 11:08:23','2017-08-16 11:08:23'),(11131,23,225,'2017-08-21','approved','00:00:00',0.00,NULL,NULL,'2017-08-30 10:00:22',NULL,0,'2017-08-25 10:00:22','2017-08-25 10:00:22'),(11132,23,226,'2017-08-21','approved','00:00:00',0.00,NULL,NULL,'2017-08-30 10:00:22',NULL,0,'2017-08-25 10:00:22','2017-08-25 10:00:22'),(11330,23,225,'2017-08-28','approved','00:00:00',0.00,NULL,NULL,'2017-09-06 10:00:22',NULL,0,'2017-08-31 13:19:34','2017-08-31 13:19:34'),(11331,23,226,'2017-08-28','approved','00:00:00',0.00,NULL,NULL,'2017-09-06 10:00:22',NULL,0,'2017-08-31 13:19:34','2017-08-31 13:19:34'),(11520,23,225,'2017-09-04','approved','00:00:00',0.00,NULL,NULL,'2017-09-13 10:00:21',NULL,0,'2017-09-06 11:38:00','2017-09-06 11:38:00'),(11521,23,226,'2017-09-04','approved','00:00:00',0.00,NULL,NULL,'2017-09-13 10:00:21',NULL,0,'2017-09-06 11:38:00','2017-09-06 11:38:00'),(11816,23,225,'2017-09-11','approved','00:00:00',0.00,NULL,NULL,'2017-09-20 10:00:22',NULL,0,'2017-09-15 10:00:21','2017-09-15 10:00:21'),(11817,23,226,'2017-09-11','approved','00:00:00',0.00,NULL,NULL,'2017-09-20 10:00:22',NULL,0,'2017-09-15 10:00:21','2017-09-15 10:00:21'),(12161,23,225,'2017-09-18','approved','00:00:00',0.00,NULL,NULL,'2017-09-27 10:00:22',NULL,0,'2017-09-22 10:00:21','2017-09-22 10:00:21'),(12162,23,226,'2017-09-18','approved','00:00:00',0.00,NULL,NULL,'2017-09-27 10:00:22',NULL,0,'2017-09-22 10:00:21','2017-09-22 10:00:21'),(12545,23,225,'2017-09-25','approved','00:00:00',0.00,NULL,NULL,'2017-10-04 10:00:21',NULL,0,'2017-09-26 12:12:26','2017-09-26 12:12:26'),(12546,23,226,'2017-09-25','approved','00:00:00',0.00,NULL,NULL,'2017-10-04 10:00:22',NULL,0,'2017-09-26 12:12:27','2017-09-26 12:12:27'),(12943,23,225,'2017-10-02','approved','11:10:00',0.00,NULL,NULL,'2017-10-11 10:00:22',NULL,0,'2017-10-06 10:00:22','2017-10-06 10:00:22'),(12944,23,226,'2017-10-02','approved','00:00:00',0.00,NULL,NULL,'2017-10-11 10:00:22',NULL,0,'2017-10-06 10:00:22','2017-10-06 10:00:22'),(13336,23,225,'2017-10-09','approved','11:10:00',0.00,NULL,NULL,'2017-10-18 10:00:22',NULL,0,'2017-10-13 10:00:22','2017-10-13 10:00:22'),(13337,23,226,'2017-10-09','approved','00:00:00',0.00,NULL,NULL,'2017-10-18 10:00:22',NULL,0,'2017-10-13 10:00:22','2017-10-13 10:00:22'),(13733,23,225,'2017-10-16','approved','11:10:00',0.00,NULL,NULL,'2017-10-25 10:00:22',NULL,0,'2017-10-20 10:00:21','2017-10-20 10:00:21'),(13734,23,226,'2017-10-16','approved','00:00:00',0.00,NULL,NULL,'2017-10-25 10:00:22',NULL,0,'2017-10-20 10:00:21','2017-10-20 10:00:21'),(14135,23,225,'2017-10-23','approved','11:10:00',0.00,NULL,NULL,'2017-11-01 09:00:21',NULL,0,'2017-10-23 16:20:21','2017-10-23 16:20:21'),(14136,23,226,'2017-10-23','approved','00:00:00',0.00,NULL,NULL,'2017-11-01 09:00:21',NULL,0,'2017-10-23 16:20:21','2017-10-23 16:20:21'),(14469,23,225,'2017-10-30','approved','11:10:00',0.00,NULL,NULL,'2017-11-08 09:00:22',NULL,0,'2017-11-03 09:00:21','2017-11-03 09:00:21'),(14470,23,226,'2017-10-30','approved','00:00:00',0.00,NULL,NULL,'2017-11-08 09:00:22',NULL,0,'2017-11-03 09:00:21','2017-11-03 09:00:21'),(14866,23,225,'2017-11-06','approved','11:10:00',0.00,NULL,NULL,'2017-11-15 09:00:21',NULL,0,'2017-11-08 14:07:07','2017-11-08 14:07:07'),(14867,23,226,'2017-11-06','approved','00:00:00',0.00,NULL,NULL,'2017-11-15 09:00:21',NULL,0,'2017-11-08 14:07:07','2017-11-08 14:07:07'),(15278,23,225,'2017-11-13','approved','11:10:00',0.00,NULL,NULL,'2017-11-22 09:00:21',NULL,0,'2017-11-14 14:34:53','2017-11-14 14:34:53'),(15279,23,226,'2017-11-13','approved','00:00:00',0.00,NULL,NULL,'2017-11-22 09:00:21',NULL,0,'2017-11-14 14:34:53','2017-11-14 14:34:53'),(15707,23,225,'2017-11-20','approved','11:10:00',0.00,NULL,NULL,'2017-11-29 09:00:21',NULL,0,'2017-11-24 09:00:21','2017-11-24 09:00:21'),(15708,23,226,'2017-11-20','approved','00:00:00',0.00,NULL,NULL,'2017-11-29 09:00:21',NULL,0,'2017-11-24 09:00:21','2017-11-24 09:00:21'),(16137,23,225,'2017-11-27','approved','11:10:00',0.00,NULL,NULL,'2017-12-06 09:00:21',NULL,0,'2017-11-30 10:52:32','2017-11-30 10:52:32'),(16138,23,226,'2017-11-27','approved','00:00:00',0.00,NULL,NULL,'2017-12-06 09:00:21',NULL,0,'2017-11-30 10:52:32','2017-11-30 10:52:32'),(16564,23,225,'2017-12-04','approved','11:10:00',0.00,NULL,NULL,'2017-12-13 09:00:22',NULL,0,'2017-12-08 09:00:21','2017-12-08 09:00:21'),(16565,23,226,'2017-12-04','approved','00:00:00',0.00,NULL,NULL,'2017-12-13 09:00:22',NULL,0,'2017-12-08 09:00:21','2017-12-08 09:00:21'),(16978,23,225,'2017-12-11','approved','11:10:00',0.00,NULL,NULL,'2017-12-20 09:00:23',NULL,0,'2017-12-14 15:42:29','2017-12-14 15:42:29'),(16979,23,226,'2017-12-11','approved','00:00:00',0.00,NULL,NULL,'2017-12-20 09:00:23',NULL,0,'2017-12-14 15:42:29','2017-12-14 15:42:29'),(17379,23,225,'2017-12-18','approved','11:10:00',0.00,NULL,NULL,'2017-12-27 09:00:22',NULL,0,'2017-12-22 09:00:21','2017-12-22 09:00:21'),(17380,23,226,'2017-12-18','approved','00:00:00',0.00,NULL,NULL,'2017-12-27 09:00:22',NULL,0,'2017-12-22 09:00:21','2017-12-22 09:00:21'),(17981,23,225,'2018-01-01','approved','00:00:00',0.00,NULL,NULL,'2018-01-10 09:00:21',NULL,0,'2018-01-05 09:00:22','2018-01-05 09:00:22'),(17982,23,226,'2018-01-01','approved','00:00:00',0.00,NULL,NULL,'2018-01-10 09:00:21',NULL,0,'2018-01-05 09:00:22','2018-01-05 09:00:22'),(18349,23,225,'2018-01-08','approved','00:00:00',0.00,NULL,NULL,'2018-01-17 09:00:22',NULL,0,'2018-01-12 09:00:21','2018-01-12 09:00:21'),(18350,23,226,'2018-01-08','approved','00:00:00',0.00,NULL,NULL,'2018-01-17 09:00:22',NULL,0,'2018-01-12 09:00:21','2018-01-12 09:00:21'),(18748,23,225,'2018-01-15','approved','00:00:00',0.00,NULL,NULL,'2018-01-24 09:00:21',NULL,0,'2018-01-19 09:00:22','2018-01-19 09:00:22'),(18749,23,226,'2018-01-15','approved','00:00:00',0.00,NULL,NULL,'2018-01-24 09:00:21',NULL,0,'2018-01-19 09:00:22','2018-01-19 09:00:22'),(19167,23,225,'2018-01-22','approved','00:00:00',0.00,NULL,NULL,'2018-01-31 09:00:21',NULL,0,'2018-01-23 11:19:53','2018-01-23 11:19:53'),(19168,23,226,'2018-01-22','approved','00:00:00',0.00,NULL,NULL,'2018-01-31 09:00:21',NULL,0,'2018-01-23 11:19:53','2018-01-23 11:19:53'),(19590,23,225,'2018-01-29','approved','00:00:00',0.00,NULL,NULL,'2018-02-07 09:00:22',NULL,0,'2018-02-02 09:00:21','2018-02-02 09:00:21'),(19591,23,226,'2018-01-29','approved','00:00:00',0.00,NULL,NULL,'2018-02-07 09:00:22',NULL,0,'2018-02-02 09:00:21','2018-02-02 09:00:21'),(20014,23,225,'2018-02-05','approved','00:00:00',0.00,NULL,NULL,'2018-02-14 09:00:22',NULL,0,'2018-02-08 09:56:02','2018-02-08 09:56:02'),(20015,23,226,'2018-02-05','approved','00:00:00',0.00,NULL,NULL,'2018-02-14 09:00:22',NULL,0,'2018-02-08 09:56:02','2018-02-08 09:56:02'),(20443,23,225,'2018-02-12','approved','00:00:00',0.00,NULL,NULL,'2018-02-21 09:00:22',NULL,0,'2018-02-16 09:00:21','2018-02-16 09:00:21'),(20444,23,226,'2018-02-12','approved','00:00:00',0.00,NULL,NULL,'2018-02-21 09:00:22',NULL,0,'2018-02-16 09:00:21','2018-02-16 09:00:21'),(20835,23,225,'2018-02-19','approved','00:00:00',0.00,NULL,NULL,'2018-02-28 09:00:22',NULL,0,'2018-02-23 09:00:23','2018-02-23 09:00:23'),(20836,23,226,'2018-02-19','approved','00:00:00',0.00,NULL,NULL,'2018-02-28 09:00:22',NULL,0,'2018-02-23 09:00:23','2018-02-23 09:00:23'),(21273,23,225,'2018-02-26','approved','00:00:00',0.00,NULL,NULL,'2018-03-07 09:00:21',NULL,0,'2018-03-02 09:00:21','2018-03-02 09:00:21'),(21274,23,226,'2018-02-26','approved','00:00:00',0.00,NULL,NULL,'2018-03-07 09:00:21',NULL,0,'2018-03-02 09:00:21','2018-03-02 09:00:21'),(21713,23,225,'2018-03-05','approved','00:00:00',0.00,NULL,NULL,'2018-03-14 09:00:42',NULL,0,'2018-03-09 09:00:21','2018-03-09 09:00:21'),(21714,23,226,'2018-03-05','approved','00:00:00',0.00,NULL,NULL,'2018-03-14 09:00:42',NULL,0,'2018-03-09 09:00:21','2018-03-09 09:00:21'),(22165,23,225,'2018-03-12','approved','00:00:00',0.00,NULL,NULL,'2018-03-21 09:00:42',NULL,0,'2018-03-16 09:00:42','2018-03-16 09:00:42'),(22166,23,226,'2018-03-12','approved','00:00:00',0.00,NULL,NULL,'2018-03-21 09:00:42',NULL,0,'2018-03-16 09:00:42','2018-03-16 09:00:42'),(22618,23,225,'2018-03-19','approved','00:00:00',0.00,NULL,NULL,'2018-03-28 10:00:43',NULL,0,'2018-03-23 09:00:43','2018-03-23 09:00:43'),(22619,23,226,'2018-03-19','approved','00:00:00',0.00,NULL,NULL,'2018-03-28 10:00:43',NULL,0,'2018-03-23 09:00:43','2018-03-23 09:00:43'),(23083,23,225,'2018-03-26','approved','00:00:00',0.00,NULL,NULL,'2018-04-04 10:00:42',NULL,0,'2018-03-30 10:00:42','2018-03-30 10:00:42'),(23084,23,226,'2018-03-26','approved','00:00:00',0.00,NULL,NULL,'2018-04-04 10:00:42',NULL,0,'2018-03-30 10:00:42','2018-03-30 10:00:42');
/*!40000 ALTER TABLE `app_timesheets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_timesheets_expenses`
--

DROP TABLE IF EXISTS `app_timesheets_expenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_timesheets_expenses` (
  `expenseID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL,
  `timesheetID` int(11) NOT NULL,
  `orgID` int(11) NOT NULL,
  `brandID` int(11) NOT NULL,
  `lessonID` int(11) DEFAULT NULL,
  `edited` tinyint(1) NOT NULL DEFAULT '0',
  `date` date NOT NULL,
  `item` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `amount` decimal(8,2) NOT NULL,
  `status` enum('unsubmitted','submitted','approved','declined') COLLATE utf8_unicode_ci DEFAULT 'unsubmitted',
  `reason` enum('travel','training','marketing','admin','other') COLLATE utf8_unicode_ci DEFAULT NULL,
  `reason_desc` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `receipt_name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `receipt_path` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `receipt_type` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `receipt_ext` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `receipt_size` bigint(100) DEFAULT NULL,
  `approved` datetime DEFAULT NULL,
  `declined` datetime DEFAULT NULL,
  `approverID` int(11) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`expenseID`),
  KEY `accountID` (`accountID`),
  KEY `timesheetID` (`timesheetID`),
  KEY `lessonID` (`lessonID`),
  KEY `approverID` (`approverID`),
  KEY `app_timesheets_expenses_ibfk_11` (`orgID`),
  KEY `app_timesheets_expenses_ibfk_12` (`brandID`),
  CONSTRAINT `app_timesheets_expenses_ibfk_1` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_timesheets_expenses_ibfk_10` FOREIGN KEY (`approverID`) REFERENCES `app_staff` (`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_timesheets_expenses_ibfk_11` FOREIGN KEY (`orgID`) REFERENCES `app_orgs` (`orgID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_timesheets_expenses_ibfk_12` FOREIGN KEY (`brandID`) REFERENCES `app_brands` (`brandID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_timesheets_expenses_ibfk_2` FOREIGN KEY (`timesheetID`) REFERENCES `app_timesheets` (`timesheetID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_timesheets_expenses_ibfk_3` FOREIGN KEY (`lessonID`) REFERENCES `app_bookings_lessons` (`lessonID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_timesheets_expenses_ibfk_4` FOREIGN KEY (`approverID`) REFERENCES `app_staff` (`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_timesheets_expenses_ibfk_5` FOREIGN KEY (`orgID`) REFERENCES `app_orgs` (`orgID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_timesheets_expenses_ibfk_6` FOREIGN KEY (`brandID`) REFERENCES `app_brands` (`brandID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_timesheets_expenses_ibfk_7` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_timesheets_expenses_ibfk_8` FOREIGN KEY (`timesheetID`) REFERENCES `app_timesheets` (`timesheetID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_timesheets_expenses_ibfk_9` FOREIGN KEY (`lessonID`) REFERENCES `app_bookings_lessons` (`lessonID`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_timesheets_expenses`
--

LOCK TABLES `app_timesheets_expenses` WRITE;
/*!40000 ALTER TABLE `app_timesheets_expenses` DISABLE KEYS */;
/*!40000 ALTER TABLE `app_timesheets_expenses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_timesheets_items`
--

DROP TABLE IF EXISTS `app_timesheets_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_timesheets_items` (
  `itemID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL,
  `timesheetID` int(11) NOT NULL,
  `orgID` int(11) NOT NULL,
  `brandID` int(11) NOT NULL,
  `lessonID` int(11) DEFAULT NULL,
  `edited` tinyint(1) NOT NULL DEFAULT '0',
  `date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `extra_time` time DEFAULT '00:00:00',
  `original_start_time` time DEFAULT NULL,
  `original_end_time` time DEFAULT NULL,
  `total_time` time NOT NULL,
  `role` enum('head','assistant','participant','observer','lead') COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` enum('unsubmitted','submitted','approved','declined') COLLATE utf8_unicode_ci DEFAULT 'unsubmitted',
  `reason` enum('travel','training','marketing','admin','other') COLLATE utf8_unicode_ci DEFAULT NULL,
  `reason_desc` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `approved` datetime DEFAULT NULL,
  `declined` datetime DEFAULT NULL,
  `approverID` int(11) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`itemID`),
  KEY `accountID` (`accountID`),
  KEY `timesheetID` (`timesheetID`),
  KEY `lessonID` (`lessonID`),
  KEY `approverID` (`approverID`),
  KEY `app_timesheets_items_ibfk_11` (`orgID`),
  KEY `app_timesheets_items_ibfk_12` (`brandID`),
  CONSTRAINT `app_timesheets_items_ibfk_1` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_timesheets_items_ibfk_10` FOREIGN KEY (`approverID`) REFERENCES `app_staff` (`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_timesheets_items_ibfk_11` FOREIGN KEY (`orgID`) REFERENCES `app_orgs` (`orgID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_timesheets_items_ibfk_12` FOREIGN KEY (`brandID`) REFERENCES `app_brands` (`brandID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_timesheets_items_ibfk_2` FOREIGN KEY (`timesheetID`) REFERENCES `app_timesheets` (`timesheetID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_timesheets_items_ibfk_3` FOREIGN KEY (`lessonID`) REFERENCES `app_bookings_lessons` (`lessonID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_timesheets_items_ibfk_4` FOREIGN KEY (`approverID`) REFERENCES `app_staff` (`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_timesheets_items_ibfk_5` FOREIGN KEY (`orgID`) REFERENCES `app_orgs` (`orgID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_timesheets_items_ibfk_6` FOREIGN KEY (`brandID`) REFERENCES `app_brands` (`brandID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `app_timesheets_items_ibfk_7` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_timesheets_items_ibfk_8` FOREIGN KEY (`timesheetID`) REFERENCES `app_timesheets` (`timesheetID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_timesheets_items_ibfk_9` FOREIGN KEY (`lessonID`) REFERENCES `app_bookings_lessons` (`lessonID`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=91586 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_timesheets_items`
--

LOCK TABLES `app_timesheets_items` WRITE;
/*!40000 ALTER TABLE `app_timesheets_items` DISABLE KEYS */;
INSERT INTO `app_timesheets_items` VALUES (8,23,19,1863,24,15739,0,'2016-04-20','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,NULL,NULL,NULL,'2016-04-23 09:00:21','2016-04-26 11:40:49'),(9,23,19,1863,24,15740,0,'2016-04-20','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,NULL,NULL,NULL,'2016-04-23 09:00:21','2016-04-26 11:40:49'),(10,23,19,1863,24,15742,0,'2016-04-21','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,NULL,NULL,NULL,'2016-04-23 09:00:21','2016-04-26 11:40:49'),(11,23,19,1863,24,15743,0,'2016-04-21','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,NULL,NULL,NULL,'2016-04-23 09:00:21','2016-04-26 11:40:49'),(12,23,19,1863,24,15745,0,'2016-04-22','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,NULL,NULL,NULL,'2016-04-23 09:00:21','2016-04-26 11:40:49'),(13,23,19,1863,24,15746,0,'2016-04-22','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,NULL,NULL,NULL,'2016-04-23 09:00:21','2016-04-26 11:40:49'),(14,23,20,1862,24,15777,0,'2016-04-21','08:00:00','08:45:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-04-27 09:00:20',NULL,NULL,'2016-04-23 09:00:21','2016-04-23 09:00:21'),(15,23,20,1862,24,15778,0,'2016-04-21','15:00:00','16:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-04-27 09:00:20',NULL,NULL,'2016-04-23 09:00:21','2016-04-23 09:00:21'),(16,23,20,1862,24,15779,0,'2016-04-22','08:00:00','08:45:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-04-27 09:00:20',NULL,NULL,'2016-04-23 09:00:21','2016-04-23 09:00:21'),(17,23,21,1864,24,15769,0,'2016-04-18','15:15:00','16:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-04-27 09:00:20',NULL,NULL,'2016-04-23 09:00:21','2016-04-23 09:00:21'),(18,23,21,1864,24,15770,0,'2016-04-20','15:15:00','16:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-04-27 09:00:20',NULL,NULL,'2016-04-23 09:00:21','2016-04-23 09:00:21'),(19,23,22,1862,24,15771,0,'2016-04-21','08:00:00','08:45:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-04-27 09:00:20',NULL,NULL,'2016-04-23 09:00:21','2016-04-23 09:00:21'),(20,23,22,1862,24,15772,0,'2016-04-21','15:00:00','16:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-04-27 09:00:20',NULL,NULL,'2016-04-23 09:00:21','2016-04-23 09:00:21'),(21,23,22,1862,24,15773,0,'2016-04-22','08:00:00','08:45:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-04-27 09:00:20',NULL,NULL,'2016-04-23 09:00:21','2016-04-23 09:00:21'),(22,23,23,1863,24,15730,0,'2016-04-20','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-04-27 09:00:20',NULL,NULL,'2016-04-23 09:00:21','2016-04-23 09:00:21'),(23,23,23,1863,24,15731,0,'2016-04-20','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-04-27 09:00:20',NULL,NULL,'2016-04-23 09:00:21','2016-04-23 09:00:21'),(24,23,23,1863,24,15733,0,'2016-04-21','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-04-27 09:00:20',NULL,NULL,'2016-04-23 09:00:21','2016-04-23 09:00:21'),(25,23,23,1863,24,15734,0,'2016-04-21','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-04-27 09:00:20',NULL,NULL,'2016-04-23 09:00:21','2016-04-23 09:00:21'),(26,23,23,1863,24,15736,0,'2016-04-22','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-04-27 09:00:20',NULL,NULL,'2016-04-23 09:00:21','2016-04-23 09:00:21'),(27,23,23,1863,24,15737,0,'2016-04-22','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-04-27 09:00:20',NULL,NULL,'2016-04-23 09:00:21','2016-04-23 09:00:21'),(28,23,19,1864,26,NULL,0,'2016-04-19','07:00:00','08:00:00','00:00:00',NULL,NULL,'01:00:00',NULL,'declined','training','test',NULL,'2016-04-26 11:41:42',234,'2016-04-26 11:40:49','2016-04-26 11:41:42'),(29,23,25,1864,24,15708,0,'2016-04-26','13:00:00','14:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-04 09:00:21',NULL,NULL,'2016-04-30 09:00:21','2016-04-30 09:00:21'),(30,23,25,1864,24,15709,0,'2016-04-26','14:15:00','15:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-04 09:00:21',NULL,NULL,'2016-04-30 09:00:21','2016-04-30 09:00:21'),(31,23,25,1864,24,15710,0,'2016-04-27','13:00:00','14:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-04 09:00:21',NULL,NULL,'2016-04-30 09:00:21','2016-04-30 09:00:21'),(32,23,25,1864,24,15711,0,'2016-04-27','14:15:00','15:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-04 09:00:21',NULL,NULL,'2016-04-30 09:00:21','2016-04-30 09:00:21'),(33,23,25,1864,24,15712,0,'2016-04-28','13:00:00','14:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-04 09:00:21',NULL,NULL,'2016-04-30 09:00:21','2016-04-30 09:00:21'),(34,23,25,1864,24,15713,0,'2016-04-28','14:15:00','15:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-04 09:00:21',NULL,NULL,'2016-04-30 09:00:21','2016-04-30 09:00:21'),(35,23,27,1863,24,15739,0,'2016-04-27','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-04 09:00:21',NULL,NULL,'2016-04-30 09:00:21','2016-04-30 09:00:21'),(36,23,27,1863,24,15740,0,'2016-04-27','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-05-04 09:00:21',NULL,NULL,'2016-04-30 09:00:21','2016-04-30 09:00:21'),(37,23,27,1863,24,15742,0,'2016-04-28','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-04 09:00:21',NULL,NULL,'2016-04-30 09:00:21','2016-04-30 09:00:21'),(38,23,27,1863,24,15743,0,'2016-04-28','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-05-04 09:00:21',NULL,NULL,'2016-04-30 09:00:21','2016-04-30 09:00:21'),(39,23,27,1863,24,15745,0,'2016-04-29','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-04 09:00:21',NULL,NULL,'2016-04-30 09:00:21','2016-04-30 09:00:21'),(40,23,27,1863,24,15746,0,'2016-04-29','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-05-04 09:00:21',NULL,NULL,'2016-04-30 09:00:21','2016-04-30 09:00:21'),(41,23,28,1862,24,15777,0,'2016-04-28','08:00:00','08:45:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-05-04 09:00:21',NULL,NULL,'2016-04-30 09:00:21','2016-04-30 09:00:21'),(42,23,28,1862,24,15778,0,'2016-04-28','15:00:00','16:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-04 09:00:21',NULL,NULL,'2016-04-30 09:00:21','2016-04-30 09:00:21'),(43,23,28,1862,24,15779,0,'2016-04-29','08:00:00','08:45:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-05-04 09:00:21',NULL,NULL,'2016-04-30 09:00:21','2016-04-30 09:00:21'),(44,23,29,1864,24,15769,0,'2016-04-25','15:15:00','16:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-04 09:00:21',NULL,NULL,'2016-04-30 09:00:21','2016-04-30 09:00:21'),(45,23,29,1864,24,15770,0,'2016-04-27','15:15:00','16:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-04 09:00:21',NULL,NULL,'2016-04-30 09:00:21','2016-04-30 09:00:21'),(46,23,30,1862,24,15771,0,'2016-04-28','08:00:00','08:45:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-05-04 09:00:21',NULL,NULL,'2016-04-30 09:00:21','2016-04-30 09:00:21'),(47,23,30,1862,24,15772,0,'2016-04-28','15:00:00','16:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-04 09:00:21',NULL,NULL,'2016-04-30 09:00:21','2016-04-30 09:00:21'),(48,23,30,1862,24,15773,0,'2016-04-29','08:00:00','08:45:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-05-04 09:00:21',NULL,NULL,'2016-04-30 09:00:21','2016-04-30 09:00:21'),(49,23,30,1869,27,15837,0,'2016-04-25','09:00:00','12:00:00','00:00:00',NULL,NULL,'03:00:00','head','approved',NULL,NULL,'2016-05-04 09:00:21',NULL,NULL,'2016-04-30 09:00:21','2016-04-30 09:00:21'),(50,23,30,1869,27,15838,0,'2016-04-25','13:00:00','15:00:00','00:00:00',NULL,NULL,'02:00:00','head','approved',NULL,NULL,'2016-05-04 09:00:21',NULL,NULL,'2016-04-30 09:00:21','2016-04-30 09:00:21'),(51,23,30,1869,27,15839,0,'2016-04-26','09:00:00','12:00:00','00:00:00',NULL,NULL,'03:00:00','head','approved',NULL,NULL,'2016-05-04 09:00:21',NULL,NULL,'2016-04-30 09:00:21','2016-04-30 09:00:21'),(52,23,30,1869,27,15840,0,'2016-04-26','13:00:00','15:00:00','00:00:00',NULL,NULL,'02:00:00','head','approved',NULL,NULL,'2016-05-04 09:00:21',NULL,NULL,'2016-04-30 09:00:21','2016-04-30 09:00:21'),(53,23,31,1863,24,15730,0,'2016-04-27','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-04 09:00:22',NULL,NULL,'2016-04-30 09:00:21','2016-04-30 09:00:21'),(54,23,31,1863,24,15731,0,'2016-04-27','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-05-04 09:00:22',NULL,NULL,'2016-04-30 09:00:21','2016-04-30 09:00:21'),(55,23,31,1863,24,15733,0,'2016-04-28','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-04 09:00:22',NULL,NULL,'2016-04-30 09:00:21','2016-04-30 09:00:21'),(56,23,31,1863,24,15734,0,'2016-04-28','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-05-04 09:00:22',NULL,NULL,'2016-04-30 09:00:21','2016-04-30 09:00:21'),(57,23,31,1863,24,15736,0,'2016-04-29','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-04 09:00:22',NULL,NULL,'2016-04-30 09:00:21','2016-04-30 09:00:21'),(58,23,31,1863,24,15737,0,'2016-04-29','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-05-04 09:00:22',NULL,NULL,'2016-04-30 09:00:21','2016-04-30 09:00:21'),(59,23,33,1864,24,15708,0,'2016-05-03','13:00:00','14:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-11 09:00:20',NULL,NULL,'2016-05-06 09:00:21','2016-05-06 09:00:21'),(60,23,33,1864,24,15709,0,'2016-05-03','14:15:00','15:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-11 09:00:20',NULL,NULL,'2016-05-06 09:00:21','2016-05-06 09:00:21'),(61,23,33,1864,24,15710,0,'2016-05-04','13:00:00','14:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-11 09:00:20',NULL,NULL,'2016-05-06 09:00:21','2016-05-06 09:00:21'),(62,23,33,1864,24,15711,0,'2016-05-04','14:15:00','15:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-11 09:00:20',NULL,NULL,'2016-05-06 09:00:21','2016-05-06 09:00:21'),(63,23,33,1864,24,15712,0,'2016-05-05','13:00:00','14:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-11 09:00:20',NULL,NULL,'2016-05-06 09:00:21','2016-05-06 09:00:21'),(64,23,33,1864,24,15713,0,'2016-05-05','14:15:00','15:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-11 09:00:20',NULL,NULL,'2016-05-06 09:00:21','2016-05-06 09:00:21'),(65,23,35,1863,24,15739,0,'2016-05-04','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-11 09:00:20',NULL,NULL,'2016-05-06 09:00:21','2016-05-06 09:00:21'),(66,23,35,1863,24,15740,0,'2016-05-04','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-05-11 09:00:20',NULL,NULL,'2016-05-06 09:00:21','2016-05-06 09:00:21'),(67,23,35,1863,24,15742,0,'2016-05-05','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-11 09:00:20',NULL,NULL,'2016-05-06 09:00:21','2016-05-06 09:00:21'),(68,23,35,1863,24,15743,0,'2016-05-05','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-05-11 09:00:20',NULL,NULL,'2016-05-06 09:00:21','2016-05-06 09:00:21'),(69,23,35,1863,24,15745,0,'2016-05-06','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-11 09:00:20',NULL,NULL,'2016-05-06 09:00:21','2016-05-06 09:00:21'),(70,23,35,1863,24,15746,0,'2016-05-06','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-05-11 09:00:20',NULL,NULL,'2016-05-06 09:00:21','2016-05-06 09:00:21'),(71,23,36,1862,24,15777,0,'2016-05-05','08:00:00','08:45:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-05-11 09:00:20',NULL,NULL,'2016-05-06 09:00:21','2016-05-06 09:00:21'),(72,23,36,1862,24,15778,0,'2016-05-05','15:00:00','16:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-11 09:00:20',NULL,NULL,'2016-05-06 09:00:21','2016-05-06 09:00:21'),(73,23,36,1862,24,15779,0,'2016-05-06','08:00:00','08:45:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-05-11 09:00:20',NULL,NULL,'2016-05-06 09:00:21','2016-05-06 09:00:21'),(74,23,37,1864,24,15769,0,'2016-05-02','15:15:00','16:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-11 09:00:20',NULL,NULL,'2016-05-06 09:00:22','2016-05-06 09:00:22'),(75,23,37,1864,24,15770,0,'2016-05-04','15:15:00','16:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-11 09:00:20',NULL,NULL,'2016-05-06 09:00:22','2016-05-06 09:00:22'),(76,23,38,1862,24,15771,0,'2016-05-05','08:00:00','08:45:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-05-11 09:00:20',NULL,NULL,'2016-05-06 09:00:22','2016-05-06 09:00:22'),(77,23,38,1862,24,15772,0,'2016-05-05','15:00:00','16:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-11 09:00:20',NULL,NULL,'2016-05-06 09:00:22','2016-05-06 09:00:22'),(78,23,38,1862,24,15773,0,'2016-05-06','08:00:00','08:45:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-05-11 09:00:20',NULL,NULL,'2016-05-06 09:00:22','2016-05-06 09:00:22'),(79,23,39,1863,24,15730,0,'2016-05-04','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-11 09:00:20',NULL,NULL,'2016-05-06 09:00:22','2016-05-06 09:00:22'),(80,23,39,1863,24,15731,0,'2016-05-04','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-05-11 09:00:20',NULL,NULL,'2016-05-06 09:00:22','2016-05-06 09:00:22'),(81,23,39,1863,24,15733,0,'2016-05-05','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-11 09:00:20',NULL,NULL,'2016-05-06 09:00:22','2016-05-06 09:00:22'),(82,23,39,1863,24,15734,0,'2016-05-05','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-05-11 09:00:20',NULL,NULL,'2016-05-06 09:00:22','2016-05-06 09:00:22'),(83,23,39,1863,24,15736,0,'2016-05-06','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-11 09:00:20',NULL,NULL,'2016-05-06 09:00:22','2016-05-06 09:00:22'),(84,23,39,1863,24,15737,0,'2016-05-06','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-05-11 09:00:20',NULL,NULL,'2016-05-06 09:00:22','2016-05-06 09:00:22'),(85,23,41,1864,24,15708,0,'2016-05-10','13:00:00','14:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-18 10:00:20',NULL,NULL,'2016-05-13 10:00:20','2016-05-13 10:00:20'),(86,23,41,1864,24,15709,0,'2016-05-10','14:15:00','15:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-18 10:00:20',NULL,NULL,'2016-05-13 10:00:20','2016-05-13 10:00:20'),(87,23,41,1864,24,15710,0,'2016-05-11','13:00:00','14:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-18 10:00:20',NULL,NULL,'2016-05-13 10:00:20','2016-05-13 10:00:20'),(88,23,41,1864,24,15711,0,'2016-05-11','14:15:00','15:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-18 10:00:20',NULL,NULL,'2016-05-13 10:00:20','2016-05-13 10:00:20'),(89,23,41,1864,24,15712,0,'2016-05-12','13:00:00','14:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-18 10:00:20',NULL,NULL,'2016-05-13 10:00:20','2016-05-13 10:00:20'),(90,23,41,1864,24,15713,0,'2016-05-12','14:15:00','15:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-18 10:00:20',NULL,NULL,'2016-05-13 10:00:20','2016-05-13 10:00:20'),(91,23,43,1863,24,15739,0,'2016-05-11','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-18 10:00:20',NULL,NULL,'2016-05-13 10:00:20','2016-05-13 10:00:20'),(92,23,43,1863,24,15740,0,'2016-05-11','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-05-18 10:00:20',NULL,NULL,'2016-05-13 10:00:20','2016-05-13 10:00:20'),(93,23,43,1863,24,15742,0,'2016-05-12','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-18 10:00:20',NULL,NULL,'2016-05-13 10:00:20','2016-05-13 10:00:20'),(94,23,43,1863,24,15743,0,'2016-05-12','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-05-18 10:00:20',NULL,NULL,'2016-05-13 10:00:20','2016-05-13 10:00:20'),(95,23,43,1863,24,15745,0,'2016-05-13','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-18 10:00:20',NULL,NULL,'2016-05-13 10:00:20','2016-05-13 10:00:20'),(96,23,43,1863,24,15746,0,'2016-05-13','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-05-18 10:00:20',NULL,NULL,'2016-05-13 10:00:20','2016-05-13 10:00:20'),(97,23,44,1862,24,15777,0,'2016-05-12','08:00:00','08:45:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-05-18 10:00:20',NULL,NULL,'2016-05-13 10:00:21','2016-05-13 10:00:21'),(98,23,44,1862,24,15778,0,'2016-05-12','15:00:00','16:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-18 10:00:20',NULL,NULL,'2016-05-13 10:00:21','2016-05-13 10:00:21'),(99,23,44,1862,24,15779,0,'2016-05-13','08:00:00','08:45:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-05-18 10:00:20',NULL,NULL,'2016-05-13 10:00:21','2016-05-13 10:00:21'),(100,23,45,1864,24,15769,0,'2016-05-09','15:15:00','16:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-18 10:00:20',NULL,NULL,'2016-05-13 10:00:21','2016-05-13 10:00:21'),(101,23,45,1864,24,15770,0,'2016-05-11','15:15:00','16:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-18 10:00:20',NULL,NULL,'2016-05-13 10:00:21','2016-05-13 10:00:21'),(102,23,46,1862,24,15771,0,'2016-05-12','08:00:00','08:45:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-05-18 10:00:20',NULL,NULL,'2016-05-13 10:00:21','2016-05-13 10:00:21'),(103,23,46,1862,24,15772,0,'2016-05-12','15:00:00','16:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-18 10:00:20',NULL,NULL,'2016-05-13 10:00:21','2016-05-13 10:00:21'),(104,23,46,1862,24,15773,0,'2016-05-13','08:00:00','08:45:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-05-18 10:00:20',NULL,NULL,'2016-05-13 10:00:21','2016-05-13 10:00:21'),(105,23,47,1863,24,15730,0,'2016-05-11','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-18 10:00:20',NULL,NULL,'2016-05-13 10:00:21','2016-05-13 10:00:21'),(106,23,47,1863,24,15731,0,'2016-05-11','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-05-18 10:00:20',NULL,NULL,'2016-05-13 10:00:21','2016-05-13 10:00:21'),(107,23,47,1863,24,15733,0,'2016-05-12','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-18 10:00:20',NULL,NULL,'2016-05-13 10:00:21','2016-05-13 10:00:21'),(108,23,47,1863,24,15734,0,'2016-05-12','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-05-18 10:00:20',NULL,NULL,'2016-05-13 10:00:21','2016-05-13 10:00:21'),(109,23,47,1863,24,15736,0,'2016-05-13','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-18 10:00:20',NULL,NULL,'2016-05-13 10:00:21','2016-05-13 10:00:21'),(110,23,47,1863,24,15737,0,'2016-05-13','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-05-18 10:00:20',NULL,NULL,'2016-05-13 10:00:21','2016-05-13 10:00:21'),(111,23,49,1864,24,15708,0,'2016-05-17','13:00:00','14:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-25 10:00:21',NULL,NULL,'2016-05-20 10:00:20','2016-05-20 10:00:20'),(112,23,49,1864,24,15709,0,'2016-05-17','14:15:00','15:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-25 10:00:21',NULL,NULL,'2016-05-20 10:00:20','2016-05-20 10:00:20'),(113,23,49,1864,24,15710,0,'2016-05-18','13:00:00','14:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-25 10:00:21',NULL,NULL,'2016-05-20 10:00:20','2016-05-20 10:00:20'),(114,23,49,1864,24,15711,0,'2016-05-18','14:15:00','15:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-25 10:00:21',NULL,NULL,'2016-05-20 10:00:20','2016-05-20 10:00:20'),(115,23,49,1864,24,15712,0,'2016-05-19','13:00:00','14:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-25 10:00:21',NULL,NULL,'2016-05-20 10:00:20','2016-05-20 10:00:20'),(116,23,49,1864,24,15713,0,'2016-05-19','14:15:00','15:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-25 10:00:21',NULL,NULL,'2016-05-20 10:00:20','2016-05-20 10:00:20'),(117,23,50,1862,27,15855,0,'2016-05-19','13:00:00','15:00:00','00:00:00',NULL,NULL,'02:00:00','lead','approved',NULL,NULL,'2016-05-25 10:00:21',NULL,NULL,'2016-05-20 10:00:21','2016-05-20 10:00:21'),(118,23,51,1863,24,15739,0,'2016-05-18','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-25 10:00:21',NULL,NULL,'2016-05-20 10:00:21','2016-05-20 10:00:21'),(119,23,51,1863,24,15740,0,'2016-05-18','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-05-25 10:00:21',NULL,NULL,'2016-05-20 10:00:21','2016-05-20 10:00:21'),(120,23,51,1863,24,15742,0,'2016-05-19','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-25 10:00:21',NULL,NULL,'2016-05-20 10:00:21','2016-05-20 10:00:21'),(121,23,51,1863,24,15743,0,'2016-05-19','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-05-25 10:00:21',NULL,NULL,'2016-05-20 10:00:21','2016-05-20 10:00:21'),(122,23,51,1863,24,15745,0,'2016-05-20','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-25 10:00:21',NULL,NULL,'2016-05-20 10:00:21','2016-05-20 10:00:21'),(123,23,51,1863,24,15746,0,'2016-05-20','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-05-25 10:00:21',NULL,NULL,'2016-05-20 10:00:21','2016-05-20 10:00:21'),(124,23,51,1862,24,15773,0,'2016-05-20','08:00:00','08:45:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-05-25 10:00:21',NULL,NULL,'2016-05-20 10:00:21','2016-05-20 10:00:21'),(125,23,51,1869,27,15843,0,'2016-05-16','09:00:00','12:00:00','00:00:00',NULL,NULL,'03:00:00','assistant','approved',NULL,NULL,'2016-05-25 10:00:21',NULL,NULL,'2016-05-20 10:00:21','2016-05-20 10:00:21'),(126,23,52,1866,24,15719,0,'2016-05-16','14:25:00','15:25:00','00:00:00',NULL,NULL,'01:00:00','lead','approved',NULL,NULL,'2016-05-25 10:00:21',NULL,NULL,'2016-05-20 10:00:21','2016-05-20 10:00:21'),(127,23,52,1862,24,15772,0,'2016-05-19','15:00:00','16:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-25 10:00:21',NULL,NULL,'2016-05-20 10:00:21','2016-05-20 10:00:21'),(128,23,53,1862,24,15777,0,'2016-05-19','08:00:00','08:45:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-05-25 10:00:21',NULL,NULL,'2016-05-20 10:00:21','2016-05-20 10:00:21'),(129,23,53,1862,24,15778,0,'2016-05-19','15:00:00','16:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-25 10:00:21',NULL,NULL,'2016-05-20 10:00:21','2016-05-20 10:00:21'),(130,23,53,1862,24,15779,0,'2016-05-20','08:00:00','08:45:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-05-25 10:00:21',NULL,NULL,'2016-05-20 10:00:21','2016-05-20 10:00:21'),(131,23,54,1864,24,15769,0,'2016-05-16','15:15:00','16:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-25 10:00:21',NULL,NULL,'2016-05-20 10:00:21','2016-05-20 10:00:21'),(132,23,54,1864,24,15770,0,'2016-05-18','15:15:00','16:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-25 10:00:21',NULL,NULL,'2016-05-20 10:00:21','2016-05-20 10:00:21'),(133,23,55,1862,24,15771,0,'2016-05-19','08:00:00','08:45:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-05-25 10:00:21',NULL,NULL,'2016-05-20 10:00:21','2016-05-20 10:00:21'),(134,23,55,1869,27,15846,0,'2016-05-17','13:00:00','15:00:00','00:00:00',NULL,NULL,'02:00:00','assistant','approved',NULL,NULL,'2016-05-25 10:00:21',NULL,NULL,'2016-05-20 10:00:21','2016-05-20 10:00:21'),(135,23,56,1863,24,15730,0,'2016-05-18','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-25 10:00:21',NULL,NULL,'2016-05-20 10:00:21','2016-05-20 10:00:21'),(136,23,56,1863,24,15731,0,'2016-05-18','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-05-25 10:00:21',NULL,NULL,'2016-05-20 10:00:21','2016-05-20 10:00:21'),(137,23,56,1863,24,15733,0,'2016-05-19','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-25 10:00:21',NULL,NULL,'2016-05-20 10:00:21','2016-05-20 10:00:21'),(138,23,56,1863,24,15734,0,'2016-05-19','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-05-25 10:00:21',NULL,NULL,'2016-05-20 10:00:21','2016-05-20 10:00:21'),(139,23,56,1863,24,15736,0,'2016-05-20','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-05-25 10:00:21',NULL,NULL,'2016-05-20 10:00:21','2016-05-20 10:00:21'),(140,23,56,1863,24,15737,0,'2016-05-20','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-05-25 10:00:21',NULL,NULL,'2016-05-20 10:00:21','2016-05-20 10:00:21'),(141,23,56,1869,27,15844,0,'2016-05-16','13:00:00','15:00:00','00:00:00',NULL,NULL,'02:00:00','assistant','approved',NULL,NULL,'2016-05-25 10:00:21',NULL,NULL,'2016-05-20 10:00:21','2016-05-20 10:00:21'),(142,23,57,1866,24,15718,0,'2016-05-16','12:30:00','13:15:00','00:00:00',NULL,NULL,'00:45:00','lead','approved',NULL,NULL,'2016-05-25 10:00:21',NULL,NULL,'2016-05-20 10:00:21','2016-05-20 10:00:21'),(143,23,57,1870,24,15847,0,'2016-05-16','13:30:00','14:30:00','00:00:00',NULL,NULL,'01:00:00','lead','approved',NULL,NULL,'2016-05-25 10:00:21',NULL,NULL,'2016-05-20 10:00:21','2016-05-20 10:00:21'),(144,23,57,1862,27,15854,0,'2016-05-20','09:00:00','12:00:00','00:00:00',NULL,NULL,'03:00:00','lead','approved',NULL,NULL,'2016-05-25 10:00:21',NULL,NULL,'2016-05-20 10:00:21','2016-05-20 10:00:21'),(874,23,242,1864,24,15708,0,'2016-05-24','13:00:00','14:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-01 10:00:27',NULL,NULL,'2016-05-27 10:00:26','2016-05-27 10:00:26'),(875,23,242,1864,24,15709,0,'2016-05-24','14:15:00','15:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-01 10:00:27',NULL,NULL,'2016-05-27 10:00:26','2016-05-27 10:00:26'),(876,23,242,1864,24,15710,0,'2016-05-25','13:00:00','14:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-01 10:00:27',NULL,NULL,'2016-05-27 10:00:26','2016-05-27 10:00:26'),(877,23,242,1864,24,15711,0,'2016-05-25','14:15:00','15:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-01 10:00:27',NULL,NULL,'2016-05-27 10:00:26','2016-05-27 10:00:26'),(878,23,242,1864,24,15712,0,'2016-05-26','13:00:00','14:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-01 10:00:27',NULL,NULL,'2016-05-27 10:00:26','2016-05-27 10:00:26'),(879,23,242,1864,24,15713,0,'2016-05-26','14:15:00','15:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-01 10:00:27',NULL,NULL,'2016-05-27 10:00:26','2016-05-27 10:00:26'),(880,23,242,1869,27,15845,0,'2016-05-24','09:00:00','12:00:00','00:00:00',NULL,NULL,'03:00:00','head','approved',NULL,NULL,'2016-06-01 10:00:27',NULL,NULL,'2016-05-27 10:00:26','2016-05-27 10:00:26'),(881,23,243,1862,27,15855,0,'2016-05-26','13:00:00','15:00:00','00:00:00',NULL,NULL,'02:00:00','lead','approved',NULL,NULL,'2016-06-01 10:00:27',NULL,NULL,'2016-05-27 10:00:26','2016-05-27 10:00:26'),(882,23,244,1863,24,15739,0,'2016-05-25','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-01 10:00:27',NULL,NULL,'2016-05-27 10:00:26','2016-05-27 10:00:26'),(883,23,244,1863,24,15740,0,'2016-05-25','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-06-01 10:00:27',NULL,NULL,'2016-05-27 10:00:26','2016-05-27 10:00:26'),(884,23,244,1863,24,15742,0,'2016-05-26','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-01 10:00:27',NULL,NULL,'2016-05-27 10:00:26','2016-05-27 10:00:26'),(885,23,244,1863,24,15743,0,'2016-05-26','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-06-01 10:00:27',NULL,NULL,'2016-05-27 10:00:26','2016-05-27 10:00:26'),(886,23,244,1863,24,15745,0,'2016-05-27','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-01 10:00:27',NULL,NULL,'2016-05-27 10:00:26','2016-05-27 10:00:26'),(887,23,244,1863,24,15746,0,'2016-05-27','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-06-01 10:00:27',NULL,NULL,'2016-05-27 10:00:26','2016-05-27 10:00:26'),(888,23,244,1862,24,15773,0,'2016-05-27','08:00:00','08:45:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-06-01 10:00:27',NULL,NULL,'2016-05-27 10:00:26','2016-05-27 10:00:26'),(889,23,244,1869,27,15843,0,'2016-05-23','09:00:00','12:00:00','00:00:00',NULL,NULL,'03:00:00','assistant','approved',NULL,NULL,'2016-06-01 10:00:27',NULL,NULL,'2016-05-27 10:00:26','2016-05-27 10:00:26'),(890,23,244,1870,24,15848,0,'2016-05-24','13:30:00','14:30:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-01 10:00:27',NULL,NULL,'2016-05-27 10:00:26','2016-05-27 10:00:26'),(891,23,245,1866,24,15719,0,'2016-05-23','14:25:00','15:25:00','00:00:00',NULL,NULL,'01:00:00','lead','approved',NULL,NULL,'2016-06-01 10:00:27',NULL,NULL,'2016-05-27 10:00:26','2016-05-27 10:00:26'),(892,23,245,1865,24,15759,0,'2016-05-24','13:15:00','14:30:00','00:00:00',NULL,NULL,'01:15:00','head','approved',NULL,NULL,'2016-06-01 10:00:27',NULL,NULL,'2016-05-27 10:00:26','2016-05-27 10:00:26'),(893,23,245,1865,24,15762,0,'2016-05-25','14:45:00','15:30:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-06-01 10:00:27',NULL,NULL,'2016-05-27 10:00:26','2016-05-27 10:00:26'),(894,23,245,1862,24,15772,0,'2016-05-26','15:00:00','16:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-01 10:00:27',NULL,NULL,'2016-05-27 10:00:26','2016-05-27 10:00:26'),(895,23,245,1870,24,15851,0,'2016-05-27','13:30:00','14:30:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-01 10:00:27',NULL,NULL,'2016-05-27 10:00:26','2016-05-27 10:00:26'),(896,23,246,1862,24,15777,0,'2016-05-26','08:00:00','08:45:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-06-01 10:00:27',NULL,NULL,'2016-05-27 10:00:26','2016-05-27 10:00:26'),(897,23,246,1862,24,15778,0,'2016-05-26','15:00:00','16:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-01 10:00:27',NULL,NULL,'2016-05-27 10:00:26','2016-05-27 10:00:26'),(898,23,246,1862,24,15779,0,'2016-05-27','08:00:00','08:45:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-06-01 10:00:27',NULL,NULL,'2016-05-27 10:00:26','2016-05-27 10:00:26'),(899,23,247,1864,24,15769,0,'2016-05-23','15:15:00','16:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-01 10:00:27',NULL,NULL,'2016-05-27 10:00:26','2016-05-27 10:00:26'),(900,23,247,1864,24,15770,0,'2016-05-25','15:15:00','16:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-01 10:00:27',NULL,NULL,'2016-05-27 10:00:26','2016-05-27 10:00:26'),(901,23,248,1862,24,15771,0,'2016-05-26','08:00:00','08:45:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-06-01 10:00:27',NULL,NULL,'2016-05-27 10:00:26','2016-05-27 10:00:26'),(902,23,248,1869,27,15846,0,'2016-05-24','13:00:00','15:00:00','00:00:00',NULL,NULL,'02:00:00','assistant','approved',NULL,NULL,'2016-06-01 10:00:27',NULL,NULL,'2016-05-27 10:00:26','2016-05-27 10:00:26'),(903,23,249,1863,24,15730,0,'2016-05-25','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-01 10:00:27',NULL,NULL,'2016-05-27 10:00:26','2016-05-27 10:00:26'),(904,23,249,1863,24,15731,0,'2016-05-25','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-06-01 10:00:27',NULL,NULL,'2016-05-27 10:00:26','2016-05-27 10:00:26'),(905,23,249,1863,24,15733,0,'2016-05-26','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-01 10:00:27',NULL,NULL,'2016-05-27 10:00:26','2016-05-27 10:00:26'),(906,23,249,1863,24,15734,0,'2016-05-26','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-06-01 10:00:27',NULL,NULL,'2016-05-27 10:00:26','2016-05-27 10:00:26'),(907,23,249,1863,24,15736,0,'2016-05-27','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-01 10:00:27',NULL,NULL,'2016-05-27 10:00:26','2016-05-27 10:00:26'),(908,23,249,1863,24,15737,0,'2016-05-27','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-06-01 10:00:27',NULL,NULL,'2016-05-27 10:00:26','2016-05-27 10:00:26'),(909,23,249,1869,27,15844,0,'2016-05-23','13:00:00','15:00:00','00:00:00',NULL,NULL,'02:00:00','assistant','approved',NULL,NULL,'2016-06-01 10:00:27',NULL,NULL,'2016-05-27 10:00:26','2016-05-27 10:00:26'),(910,23,250,1866,24,15718,0,'2016-05-23','12:30:00','13:15:00','00:00:00',NULL,NULL,'00:45:00','lead','approved',NULL,NULL,'2016-06-01 10:00:27',NULL,NULL,'2016-05-27 10:00:26','2016-05-27 10:00:26'),(911,23,250,1865,24,15763,0,'2016-05-26','13:15:00','14:30:00','00:00:00',NULL,NULL,'01:15:00','head','approved',NULL,NULL,'2016-06-01 10:00:27',NULL,NULL,'2016-05-27 10:00:26','2016-05-27 10:00:26'),(912,23,250,1870,24,15847,0,'2016-05-23','13:30:00','14:30:00','00:00:00',NULL,NULL,'01:00:00','lead','approved',NULL,NULL,'2016-06-01 10:00:27',NULL,NULL,'2016-05-27 10:00:26','2016-05-27 10:00:26'),(913,23,250,1862,27,15854,0,'2016-05-27','09:00:00','12:00:00','00:00:00',NULL,NULL,'03:00:00','lead','approved',NULL,NULL,'2016-06-01 10:00:27',NULL,NULL,'2016-05-27 10:00:26','2016-05-27 10:00:26'),(1737,23,453,1864,24,15708,0,'2016-05-31','13:00:00','14:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-08 10:00:32',NULL,NULL,'2016-06-03 10:00:26','2016-06-03 10:00:26'),(1738,23,453,1864,24,15709,0,'2016-05-31','14:15:00','15:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-08 10:00:32',NULL,NULL,'2016-06-03 10:00:26','2016-06-03 10:00:26'),(1739,23,453,1864,24,15710,0,'2016-06-01','13:00:00','14:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-08 10:00:32',NULL,NULL,'2016-06-03 10:00:26','2016-06-03 10:00:26'),(1740,23,453,1864,24,15711,0,'2016-06-01','14:15:00','15:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-08 10:00:32',NULL,NULL,'2016-06-03 10:00:26','2016-06-03 10:00:26'),(1741,23,453,1864,24,15712,0,'2016-06-02','13:00:00','14:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-08 10:00:32',NULL,NULL,'2016-06-03 10:00:26','2016-06-03 10:00:26'),(1742,23,453,1864,24,15713,0,'2016-06-02','14:15:00','15:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-08 10:00:32',NULL,NULL,'2016-06-03 10:00:26','2016-06-03 10:00:26'),(1743,23,453,1869,27,15845,0,'2016-05-31','09:00:00','12:00:00','00:00:00',NULL,NULL,'03:00:00','head','approved',NULL,NULL,'2016-06-08 10:00:32',NULL,NULL,'2016-06-03 10:00:26','2016-06-03 10:00:26'),(1744,23,454,1862,27,15855,0,'2016-06-02','13:00:00','15:00:00','00:00:00',NULL,NULL,'02:00:00','lead','approved',NULL,NULL,'2016-06-08 10:00:32',NULL,NULL,'2016-06-03 10:00:26','2016-06-03 10:00:26'),(1745,23,455,1863,24,15739,0,'2016-06-01','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-08 10:00:32',NULL,NULL,'2016-06-03 10:00:26','2016-06-03 10:00:26'),(1746,23,455,1863,24,15740,0,'2016-06-01','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-06-08 10:00:32',NULL,NULL,'2016-06-03 10:00:26','2016-06-03 10:00:26'),(1747,23,455,1863,24,15742,0,'2016-06-02','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-08 10:00:32',NULL,NULL,'2016-06-03 10:00:26','2016-06-03 10:00:26'),(1748,23,455,1863,24,15743,0,'2016-06-02','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-06-08 10:00:32',NULL,NULL,'2016-06-03 10:00:26','2016-06-03 10:00:26'),(1749,23,455,1863,24,15745,0,'2016-06-03','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-08 10:00:32',NULL,NULL,'2016-06-03 10:00:26','2016-06-03 10:00:26'),(1750,23,455,1863,24,15746,0,'2016-06-03','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-06-08 10:00:32',NULL,NULL,'2016-06-03 10:00:26','2016-06-03 10:00:26'),(1751,23,455,1862,24,15773,0,'2016-06-03','08:00:00','08:45:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-06-08 10:00:32',NULL,NULL,'2016-06-03 10:00:26','2016-06-03 10:00:26'),(1752,23,455,1869,27,15843,0,'2016-05-30','09:00:00','12:00:00','00:00:00',NULL,NULL,'03:00:00','assistant','approved',NULL,NULL,'2016-06-08 10:00:32',NULL,NULL,'2016-06-03 10:00:26','2016-06-03 10:00:26'),(1753,23,455,1870,24,15848,0,'2016-05-31','13:30:00','14:30:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-08 10:00:32',NULL,NULL,'2016-06-03 10:00:26','2016-06-03 10:00:26'),(1754,23,456,1866,24,15719,0,'2016-05-30','14:25:00','15:25:00','00:00:00',NULL,NULL,'01:00:00','lead','approved',NULL,NULL,'2016-06-08 10:00:32',NULL,NULL,'2016-06-03 10:00:26','2016-06-03 10:00:26'),(1755,23,456,1865,24,15759,0,'2016-05-31','13:15:00','14:30:00','00:00:00',NULL,NULL,'01:15:00','head','approved',NULL,NULL,'2016-06-08 10:00:32',NULL,NULL,'2016-06-03 10:00:26','2016-06-03 10:00:26'),(1756,23,456,1865,24,15762,0,'2016-06-01','14:45:00','15:30:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-06-08 10:00:32',NULL,NULL,'2016-06-03 10:00:26','2016-06-03 10:00:26'),(1757,23,456,1862,24,15772,0,'2016-06-02','15:00:00','16:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-08 10:00:32',NULL,NULL,'2016-06-03 10:00:26','2016-06-03 10:00:26'),(1758,23,456,1870,24,15851,0,'2016-06-03','13:30:00','14:30:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-08 10:00:32',NULL,NULL,'2016-06-03 10:00:26','2016-06-03 10:00:26'),(1759,23,457,1862,24,15777,0,'2016-06-02','08:00:00','08:45:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-06-08 10:00:32',NULL,NULL,'2016-06-03 10:00:26','2016-06-03 10:00:26'),(1760,23,457,1862,24,15778,0,'2016-06-02','15:00:00','16:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-08 10:00:32',NULL,NULL,'2016-06-03 10:00:26','2016-06-03 10:00:26'),(1761,23,457,1862,24,15779,0,'2016-06-03','08:00:00','08:45:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-06-08 10:00:32',NULL,NULL,'2016-06-03 10:00:26','2016-06-03 10:00:26'),(1762,23,458,1864,24,15769,0,'2016-05-30','15:15:00','16:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-08 10:00:32',NULL,NULL,'2016-06-03 10:00:27','2016-06-03 10:00:27'),(1763,23,458,1864,24,15770,0,'2016-06-01','15:15:00','16:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-08 10:00:32',NULL,NULL,'2016-06-03 10:00:27','2016-06-03 10:00:27'),(1764,23,459,1862,24,15771,0,'2016-06-02','08:00:00','08:45:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-06-08 10:00:32',NULL,NULL,'2016-06-03 10:00:27','2016-06-03 10:00:27'),(1765,23,459,1869,27,15846,0,'2016-05-31','13:00:00','15:00:00','00:00:00',NULL,NULL,'02:00:00','assistant','approved',NULL,NULL,'2016-06-08 10:00:32',NULL,NULL,'2016-06-03 10:00:27','2016-06-03 10:00:27'),(1766,23,460,1863,24,15730,0,'2016-06-01','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-08 10:00:32',NULL,NULL,'2016-06-03 10:00:27','2016-06-03 10:00:27'),(1767,23,460,1863,24,15731,0,'2016-06-01','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-06-08 10:00:32',NULL,NULL,'2016-06-03 10:00:27','2016-06-03 10:00:27'),(1768,23,460,1863,24,15733,0,'2016-06-02','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-08 10:00:32',NULL,NULL,'2016-06-03 10:00:27','2016-06-03 10:00:27'),(1769,23,460,1863,24,15734,0,'2016-06-02','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-06-08 10:00:32',NULL,NULL,'2016-06-03 10:00:27','2016-06-03 10:00:27'),(1770,23,460,1863,24,15736,0,'2016-06-03','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-08 10:00:32',NULL,NULL,'2016-06-03 10:00:27','2016-06-03 10:00:27'),(1771,23,460,1863,24,15737,0,'2016-06-03','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-06-08 10:00:32',NULL,NULL,'2016-06-03 10:00:27','2016-06-03 10:00:27'),(1772,23,460,1869,27,15844,0,'2016-05-30','13:00:00','15:00:00','00:00:00',NULL,NULL,'02:00:00','assistant','approved',NULL,NULL,'2016-06-08 10:00:32',NULL,NULL,'2016-06-03 10:00:27','2016-06-03 10:00:27'),(1773,23,461,1866,24,15718,0,'2016-05-30','12:30:00','13:15:00','00:00:00',NULL,NULL,'00:45:00','lead','approved',NULL,NULL,'2016-06-08 10:00:32',NULL,NULL,'2016-06-03 10:00:27','2016-06-03 10:00:27'),(1774,23,461,1865,24,15763,0,'2016-06-02','13:15:00','14:30:00','00:00:00',NULL,NULL,'01:15:00','head','approved',NULL,NULL,'2016-06-08 10:00:32',NULL,NULL,'2016-06-03 10:00:27','2016-06-03 10:00:27'),(1775,23,461,1870,24,15847,0,'2016-05-30','13:30:00','14:30:00','00:00:00',NULL,NULL,'01:00:00','lead','approved',NULL,NULL,'2016-06-08 10:00:32',NULL,NULL,'2016-06-03 10:00:27','2016-06-03 10:00:27'),(1776,23,461,1862,27,15854,0,'2016-06-03','09:00:00','12:00:00','00:00:00',NULL,NULL,'03:00:00','lead','approved',NULL,NULL,'2016-06-08 10:00:32',NULL,NULL,'2016-06-03 10:00:27','2016-06-03 10:00:27'),(2511,23,640,1864,24,15708,0,'2016-06-07','13:00:00','14:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:26','2016-06-10 10:00:26'),(2512,23,640,1864,24,15709,0,'2016-06-07','14:15:00','15:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:26','2016-06-10 10:00:26'),(2513,23,640,1864,24,15710,0,'2016-06-08','13:00:00','14:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:26','2016-06-10 10:00:26'),(2514,23,640,1864,24,15711,0,'2016-06-08','14:15:00','15:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:26','2016-06-10 10:00:26'),(2515,23,640,1864,24,15712,0,'2016-06-09','13:00:00','14:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:26','2016-06-10 10:00:26'),(2516,23,640,1864,24,15713,0,'2016-06-09','14:15:00','15:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:26','2016-06-10 10:00:26'),(2517,23,640,1867,25,15834,0,'2016-06-06','08:00:00','15:30:00','00:00:00',NULL,NULL,'07:30:00','head','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:26','2016-06-10 10:00:26'),(2518,23,640,1869,27,15845,0,'2016-06-07','09:00:00','12:00:00','00:00:00',NULL,NULL,'03:00:00','head','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:26','2016-06-10 10:00:26'),(2519,23,640,1869,28,15852,0,'2016-06-09','10:00:00','11:00:00','00:00:00',NULL,NULL,'01:00:00','participant','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:26','2016-06-10 10:00:26'),(2520,23,640,1869,28,15853,0,'2016-06-10','10:00:00','11:00:00','00:00:00',NULL,NULL,'01:00:00','participant','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:26','2016-06-10 10:00:26'),(2521,23,641,1869,28,15852,0,'2016-06-09','10:00:00','11:00:00','00:00:00',NULL,NULL,'01:00:00','participant','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:26','2016-06-10 10:00:26'),(2522,23,641,1869,28,15853,0,'2016-06-10','10:00:00','11:00:00','00:00:00',NULL,NULL,'01:00:00','participant','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:26','2016-06-10 10:00:26'),(2523,23,641,1862,27,15855,0,'2016-06-09','13:00:00','15:00:00','00:00:00',NULL,NULL,'02:00:00','lead','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:26','2016-06-10 10:00:26'),(2524,23,642,1863,24,15739,0,'2016-06-08','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:26','2016-06-10 10:00:26'),(2525,23,642,1863,24,15740,0,'2016-06-08','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:26','2016-06-10 10:00:26'),(2526,23,642,1863,24,15742,0,'2016-06-09','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:26','2016-06-10 10:00:26'),(2527,23,642,1863,24,15743,0,'2016-06-09','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:26','2016-06-10 10:00:26'),(2528,23,642,1863,24,15745,0,'2016-06-10','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:26','2016-06-10 10:00:26'),(2529,23,642,1863,24,15746,0,'2016-06-10','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:26','2016-06-10 10:00:26'),(2530,23,642,1862,24,15773,0,'2016-06-10','08:00:00','08:45:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:26','2016-06-10 10:00:26'),(2531,23,642,1869,27,15843,0,'2016-06-06','09:00:00','12:00:00','00:00:00',NULL,NULL,'03:00:00','assistant','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:26','2016-06-10 10:00:26'),(2532,23,642,1870,24,15848,0,'2016-06-07','13:30:00','14:30:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:26','2016-06-10 10:00:26'),(2533,23,642,1869,28,15852,0,'2016-06-09','10:00:00','11:00:00','00:00:00',NULL,NULL,'01:00:00','participant','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:26','2016-06-10 10:00:26'),(2534,23,642,1869,28,15853,0,'2016-06-10','10:00:00','11:00:00','00:00:00',NULL,NULL,'01:00:00','participant','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:26','2016-06-10 10:00:26'),(2535,23,643,1866,24,15719,0,'2016-06-06','14:25:00','15:25:00','00:00:00',NULL,NULL,'01:00:00','lead','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:27','2016-06-10 10:00:27'),(2536,23,643,1865,24,15759,0,'2016-06-07','13:15:00','14:30:00','00:00:00',NULL,NULL,'01:15:00','head','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:27','2016-06-10 10:00:27'),(2537,23,643,1865,24,15762,0,'2016-06-08','14:45:00','15:30:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:27','2016-06-10 10:00:27'),(2538,23,643,1862,24,15772,0,'2016-06-09','15:00:00','16:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:27','2016-06-10 10:00:27'),(2539,23,643,1870,24,15851,0,'2016-06-10','13:30:00','14:30:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:27','2016-06-10 10:00:27'),(2540,23,643,1869,28,15852,0,'2016-06-09','10:00:00','11:00:00','00:00:00',NULL,NULL,'01:00:00','participant','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:27','2016-06-10 10:00:27'),(2541,23,643,1869,28,15853,0,'2016-06-10','10:00:00','11:00:00','00:00:00',NULL,NULL,'01:00:00','participant','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:27','2016-06-10 10:00:27'),(2542,23,644,1862,24,15777,0,'2016-06-09','08:00:00','08:45:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:27','2016-06-10 10:00:27'),(2543,23,644,1862,24,15778,0,'2016-06-09','15:00:00','16:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:27','2016-06-10 10:00:27'),(2544,23,644,1862,24,15779,0,'2016-06-10','08:00:00','08:45:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:27','2016-06-10 10:00:27'),(2545,23,644,1869,27,15843,0,'2016-06-06','09:00:00','12:00:00','00:00:00',NULL,NULL,'03:00:00','head','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:27','2016-06-10 10:00:27'),(2546,23,644,1869,27,15844,0,'2016-06-06','13:00:00','15:00:00','00:00:00',NULL,NULL,'02:00:00','head','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:27','2016-06-10 10:00:27'),(2547,23,644,1869,27,15846,0,'2016-06-07','13:00:00','15:00:00','00:00:00',NULL,NULL,'02:00:00','head','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:27','2016-06-10 10:00:27'),(2548,23,645,1864,24,15769,0,'2016-06-06','15:15:00','16:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:27','2016-06-10 10:00:27'),(2549,23,645,1864,24,15770,0,'2016-06-08','15:15:00','16:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:27','2016-06-10 10:00:27'),(2550,23,645,1869,28,15852,0,'2016-06-09','10:00:00','11:00:00','00:00:00',NULL,NULL,'01:00:00','participant','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:27','2016-06-10 10:00:27'),(2551,23,645,1869,28,15853,0,'2016-06-10','10:00:00','11:00:00','00:00:00',NULL,NULL,'01:00:00','participant','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:27','2016-06-10 10:00:27'),(2552,23,646,1862,24,15771,0,'2016-06-09','08:00:00','08:45:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:27','2016-06-10 10:00:27'),(2553,23,646,1867,25,15834,0,'2016-06-06','08:00:00','15:30:00','00:00:00',NULL,NULL,'07:30:00','assistant','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:27','2016-06-10 10:00:27'),(2554,23,646,1869,27,15846,0,'2016-06-07','13:00:00','15:00:00','00:00:00',NULL,NULL,'02:00:00','assistant','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:27','2016-06-10 10:00:27'),(2555,23,646,1869,28,15852,0,'2016-06-09','10:00:00','11:00:00','00:00:00',NULL,NULL,'01:00:00','participant','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:27','2016-06-10 10:00:27'),(2556,23,646,1869,28,15853,0,'2016-06-10','10:00:00','11:00:00','00:00:00',NULL,NULL,'01:00:00','participant','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:27','2016-06-10 10:00:27'),(2557,23,647,1863,24,15730,0,'2016-06-08','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:27','2016-06-10 10:00:27'),(2558,23,647,1863,24,15731,0,'2016-06-08','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:27','2016-06-10 10:00:27'),(2559,23,647,1863,24,15733,0,'2016-06-09','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:27','2016-06-10 10:00:27'),(2560,23,647,1863,24,15734,0,'2016-06-09','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:27','2016-06-10 10:00:27'),(2561,23,647,1863,24,15736,0,'2016-06-10','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:27','2016-06-10 10:00:27'),(2562,23,647,1863,24,15737,0,'2016-06-10','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:27','2016-06-10 10:00:27'),(2563,23,647,1869,27,15844,0,'2016-06-06','13:00:00','15:00:00','00:00:00',NULL,NULL,'02:00:00','assistant','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:27','2016-06-10 10:00:27'),(2564,23,647,1865,28,15841,0,'2016-06-08','09:30:00','13:00:00','00:00:00',NULL,NULL,'03:30:00','lead','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:27','2016-06-10 10:00:27'),(2565,23,647,1865,28,15842,0,'2016-06-08','13:30:00','16:30:00','00:00:00',NULL,NULL,'03:00:00','lead','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:27','2016-06-10 10:00:27'),(2566,23,647,1869,28,15852,0,'2016-06-09','10:00:00','11:00:00','00:00:00',NULL,NULL,'01:00:00','participant','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:27','2016-06-10 10:00:27'),(2567,23,647,1869,28,15853,0,'2016-06-10','10:00:00','11:00:00','00:00:00',NULL,NULL,'01:00:00','participant','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:27','2016-06-10 10:00:27'),(2568,23,648,1866,24,15718,0,'2016-06-06','12:30:00','13:15:00','00:00:00',NULL,NULL,'00:45:00','lead','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:27','2016-06-10 10:00:27'),(2569,23,648,1865,24,15763,0,'2016-06-09','13:15:00','14:30:00','00:00:00',NULL,NULL,'01:15:00','head','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:27','2016-06-10 10:00:27'),(2570,23,648,1870,24,15847,0,'2016-06-06','13:30:00','14:30:00','00:00:00',NULL,NULL,'01:00:00','lead','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:27','2016-06-10 10:00:27'),(2571,23,648,1862,27,15854,0,'2016-06-10','09:00:00','12:00:00','00:00:00',NULL,NULL,'03:00:00','lead','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:27','2016-06-10 10:00:27'),(2572,23,649,1864,24,15708,0,'2016-06-07','13:00:00','14:00:00','00:00:00',NULL,NULL,'01:00:00','observer','approved',NULL,NULL,'2016-06-15 10:00:29',NULL,NULL,'2016-06-10 10:00:27','2016-06-10 10:00:27'),(3789,23,867,1864,24,15708,0,'2016-06-14','13:00:00','14:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-22 10:00:32',NULL,NULL,'2016-06-17 09:07:45','2016-06-17 09:07:45'),(3790,23,867,1864,24,15709,0,'2016-06-14','14:15:00','15:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-22 10:00:32',NULL,NULL,'2016-06-17 09:07:45','2016-06-17 09:07:45'),(3791,23,867,1864,24,15710,0,'2016-06-15','13:00:00','14:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-22 10:00:32',NULL,NULL,'2016-06-17 09:07:45','2016-06-17 09:07:45'),(3792,23,867,1864,24,15711,0,'2016-06-15','14:15:00','15:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-22 10:00:32',NULL,NULL,'2016-06-17 09:07:45','2016-06-17 09:07:45'),(3793,23,867,1864,24,15712,0,'2016-06-16','13:00:00','14:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-22 10:00:32',NULL,NULL,'2016-06-17 09:07:45','2016-06-17 09:07:45'),(3794,23,867,1864,24,15713,0,'2016-06-16','14:15:00','15:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-22 10:00:32',NULL,NULL,'2016-06-17 09:07:45','2016-06-17 09:07:45'),(3795,23,867,1867,25,15834,0,'2016-06-13','08:00:00','15:30:00','00:00:00',NULL,NULL,'07:30:00','head','approved',NULL,NULL,'2016-06-22 10:00:32',NULL,NULL,'2016-06-17 09:07:45','2016-06-17 09:07:45'),(3796,23,868,1862,27,15855,0,'2016-06-16','13:00:00','15:00:00','00:00:00',NULL,NULL,'02:00:00','lead','approved',NULL,NULL,'2016-06-22 10:00:32',NULL,NULL,'2016-06-17 09:07:45','2016-06-17 09:07:45'),(3797,23,869,1863,24,15739,0,'2016-06-15','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-22 10:00:32',NULL,NULL,'2016-06-17 09:07:45','2016-06-17 09:07:45'),(3798,23,869,1863,24,15740,0,'2016-06-15','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-06-22 10:00:32',NULL,NULL,'2016-06-17 09:07:45','2016-06-17 09:07:45'),(3799,23,869,1863,24,15742,0,'2016-06-16','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-22 10:00:32',NULL,NULL,'2016-06-17 09:07:45','2016-06-17 09:07:45'),(3800,23,869,1863,24,15743,0,'2016-06-16','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-06-22 10:00:32',NULL,NULL,'2016-06-17 09:07:45','2016-06-17 09:07:45'),(3801,23,869,1863,24,15745,0,'2016-06-17','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-22 10:00:32',NULL,NULL,'2016-06-17 09:07:45','2016-06-17 09:07:45'),(3802,23,869,1863,24,15746,0,'2016-06-17','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-06-22 10:00:32',NULL,NULL,'2016-06-17 09:07:45','2016-06-17 09:07:45'),(3803,23,869,1862,24,15773,0,'2016-06-17','08:00:00','08:45:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-06-22 10:00:32',NULL,NULL,'2016-06-17 09:07:45','2016-06-17 09:07:45'),(3804,23,869,1870,24,15848,0,'2016-06-14','13:30:00','14:30:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-22 10:00:32',NULL,NULL,'2016-06-17 09:07:45','2016-06-17 09:07:45'),(3805,23,870,1866,24,15719,0,'2016-06-13','14:25:00','15:25:00','00:00:00',NULL,NULL,'01:00:00','lead','approved',NULL,NULL,'2016-06-22 10:00:32',NULL,NULL,'2016-06-17 09:07:45','2016-06-17 09:07:45'),(3806,23,870,1865,24,15759,0,'2016-06-14','13:15:00','14:30:00','00:00:00',NULL,NULL,'01:15:00','head','approved',NULL,NULL,'2016-06-22 10:00:32',NULL,NULL,'2016-06-17 09:07:45','2016-06-17 09:07:45'),(3807,23,870,1865,24,15762,0,'2016-06-15','14:45:00','15:30:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-06-22 10:00:32',NULL,NULL,'2016-06-17 09:07:45','2016-06-17 09:07:45'),(3808,23,870,1862,24,15772,0,'2016-06-16','15:00:00','16:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-22 10:00:32',NULL,NULL,'2016-06-17 09:07:45','2016-06-17 09:07:45'),(3809,23,870,1870,24,15851,0,'2016-06-17','13:30:00','14:30:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-22 10:00:32',NULL,NULL,'2016-06-17 09:07:45','2016-06-17 09:07:45'),(3810,23,871,1862,24,15777,0,'2016-06-16','08:00:00','08:45:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-06-22 10:00:32',NULL,NULL,'2016-06-17 09:07:46','2016-06-17 09:07:46'),(3811,23,871,1862,24,15778,0,'2016-06-16','15:00:00','16:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-22 10:00:32',NULL,NULL,'2016-06-17 09:07:46','2016-06-17 09:07:46'),(3812,23,871,1862,24,15779,0,'2016-06-17','08:00:00','08:45:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-06-22 10:00:32',NULL,NULL,'2016-06-17 09:07:46','2016-06-17 09:07:46'),(3813,23,872,1864,24,15769,0,'2016-06-13','15:15:00','16:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-22 10:00:32',NULL,NULL,'2016-06-17 09:07:46','2016-06-17 09:07:46'),(3814,23,872,1864,24,15770,0,'2016-06-15','15:15:00','16:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-22 10:00:32',NULL,NULL,'2016-06-17 09:07:46','2016-06-17 09:07:46'),(3815,23,873,1862,24,15771,0,'2016-06-16','08:00:00','08:45:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-06-22 10:00:32',NULL,NULL,'2016-06-17 09:07:46','2016-06-17 09:07:46'),(3816,23,873,1867,25,15834,0,'2016-06-13','08:00:00','15:30:00','00:00:00',NULL,NULL,'07:30:00','assistant','approved',NULL,NULL,'2016-06-22 10:00:32',NULL,NULL,'2016-06-17 09:07:46','2016-06-17 09:07:46'),(3817,23,874,1863,24,15730,0,'2016-06-15','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-22 10:00:32',NULL,NULL,'2016-06-17 09:07:46','2016-06-17 09:07:46'),(3818,23,874,1863,24,15731,0,'2016-06-15','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-06-22 10:00:32',NULL,NULL,'2016-06-17 09:07:46','2016-06-17 09:07:46'),(3819,23,874,1863,24,15733,0,'2016-06-16','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-22 10:00:32',NULL,NULL,'2016-06-17 09:07:46','2016-06-17 09:07:46'),(3820,23,874,1863,24,15734,0,'2016-06-16','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-06-22 10:00:32',NULL,NULL,'2016-06-17 09:07:46','2016-06-17 09:07:46'),(3821,23,874,1863,24,15736,0,'2016-06-17','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-22 10:00:32',NULL,NULL,'2016-06-17 09:07:46','2016-06-17 09:07:46'),(3822,23,874,1863,24,15737,0,'2016-06-17','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-06-22 10:00:32',NULL,NULL,'2016-06-17 09:07:46','2016-06-17 09:07:46'),(3823,23,875,1866,24,15718,0,'2016-06-13','12:30:00','13:15:00','00:00:00',NULL,NULL,'00:45:00','lead','approved',NULL,NULL,'2016-06-22 10:00:32',NULL,NULL,'2016-06-17 09:07:46','2016-06-17 09:07:46'),(3824,23,875,1863,24,15736,0,'2016-06-17','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','observer','approved',NULL,NULL,'2016-06-22 10:00:32',NULL,NULL,'2016-06-17 09:07:46','2016-06-17 09:07:46'),(3825,23,875,1865,24,15763,0,'2016-06-16','13:15:00','14:30:00','00:00:00',NULL,NULL,'01:15:00','head','approved',NULL,NULL,'2016-06-22 10:00:32',NULL,NULL,'2016-06-17 09:07:46','2016-06-17 09:07:46'),(3826,23,875,1870,24,15847,0,'2016-06-13','13:30:00','14:30:00','00:00:00',NULL,NULL,'01:00:00','lead','approved',NULL,NULL,'2016-06-22 10:00:32',NULL,NULL,'2016-06-17 09:07:46','2016-06-17 09:07:46'),(3827,23,875,1862,27,15854,0,'2016-06-17','09:00:00','12:00:00','00:00:00',NULL,NULL,'03:00:00','lead','approved',NULL,NULL,'2016-06-22 10:00:32',NULL,NULL,'2016-06-17 09:07:46','2016-06-17 09:07:46'),(3828,23,876,1864,24,15708,0,'2016-06-14','13:00:00','14:00:00','00:00:00',NULL,NULL,'01:00:00','observer','approved',NULL,NULL,'2016-06-22 10:00:33',NULL,NULL,'2016-06-17 09:07:46','2016-06-17 09:07:46'),(4487,23,1040,1864,24,15708,0,'2016-06-21','13:00:00','14:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-29 10:00:26',NULL,NULL,'2016-06-24 10:00:27','2016-06-24 10:00:27'),(4488,23,1040,1864,24,15709,0,'2016-06-21','14:15:00','15:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-29 10:00:26',NULL,NULL,'2016-06-24 10:00:27','2016-06-24 10:00:27'),(4489,23,1040,1864,24,15710,0,'2016-06-22','13:00:00','14:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-29 10:00:26',NULL,NULL,'2016-06-24 10:00:27','2016-06-24 10:00:27'),(4490,23,1040,1864,24,15711,0,'2016-06-22','14:15:00','15:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-29 10:00:26',NULL,NULL,'2016-06-24 10:00:27','2016-06-24 10:00:27'),(4491,23,1040,1864,24,15712,0,'2016-06-23','13:00:00','14:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-29 10:00:26',NULL,NULL,'2016-06-24 10:00:27','2016-06-24 10:00:27'),(4492,23,1040,1864,24,15713,0,'2016-06-23','14:15:00','15:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-29 10:00:26',NULL,NULL,'2016-06-24 10:00:27','2016-06-24 10:00:27'),(4493,23,1040,1867,25,15834,0,'2016-06-20','08:00:00','15:30:00','00:00:00',NULL,NULL,'07:30:00','head','approved',NULL,NULL,'2016-06-29 10:00:26',NULL,NULL,'2016-06-24 10:00:27','2016-06-24 10:00:27'),(4494,23,1041,1862,27,15855,0,'2016-06-23','13:00:00','15:00:00','00:00:00',NULL,NULL,'02:00:00','lead','approved',NULL,NULL,'2016-06-29 10:00:26',NULL,NULL,'2016-06-24 10:00:27','2016-06-24 10:00:27'),(4495,23,1042,1863,24,15739,0,'2016-06-22','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-29 10:00:26',NULL,NULL,'2016-06-24 10:00:27','2016-06-24 10:00:27'),(4496,23,1042,1863,24,15740,0,'2016-06-22','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-06-29 10:00:26',NULL,NULL,'2016-06-24 10:00:27','2016-06-24 10:00:27'),(4497,23,1042,1863,24,15742,0,'2016-06-23','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-29 10:00:26',NULL,NULL,'2016-06-24 10:00:27','2016-06-24 10:00:27'),(4498,23,1042,1863,24,15743,0,'2016-06-23','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-06-29 10:00:26',NULL,NULL,'2016-06-24 10:00:27','2016-06-24 10:00:27'),(4499,23,1042,1863,24,15745,0,'2016-06-24','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-29 10:00:26',NULL,NULL,'2016-06-24 10:00:27','2016-06-24 10:00:27'),(4500,23,1042,1863,24,15746,0,'2016-06-24','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-06-29 10:00:26',NULL,NULL,'2016-06-24 10:00:27','2016-06-24 10:00:27'),(4501,23,1042,1862,24,15773,0,'2016-06-24','08:00:00','08:45:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-06-29 10:00:26',NULL,NULL,'2016-06-24 10:00:27','2016-06-24 10:00:27'),(4502,23,1042,1870,24,15848,0,'2016-06-21','13:30:00','14:30:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-29 10:00:26',NULL,NULL,'2016-06-24 10:00:27','2016-06-24 10:00:27'),(4503,23,1043,1866,24,15719,0,'2016-06-20','14:25:00','15:25:00','00:00:00',NULL,NULL,'01:00:00','lead','approved',NULL,NULL,'2016-06-29 10:00:26',NULL,NULL,'2016-06-24 10:00:27','2016-06-24 10:00:27'),(4504,23,1043,1865,24,15759,0,'2016-06-21','13:15:00','14:30:00','00:00:00',NULL,NULL,'01:15:00','head','approved',NULL,NULL,'2016-06-29 10:00:26',NULL,NULL,'2016-06-24 10:00:27','2016-06-24 10:00:27'),(4505,23,1043,1865,24,15762,0,'2016-06-22','14:45:00','15:30:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-06-29 10:00:26',NULL,NULL,'2016-06-24 10:00:27','2016-06-24 10:00:27'),(4506,23,1043,1862,24,15772,0,'2016-06-23','15:00:00','16:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-29 10:00:26',NULL,NULL,'2016-06-24 10:00:27','2016-06-24 10:00:27'),(4507,23,1043,1870,24,15851,0,'2016-06-24','13:30:00','14:30:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-29 10:00:26',NULL,NULL,'2016-06-24 10:00:27','2016-06-24 10:00:27'),(4508,23,1044,1862,24,15777,0,'2016-06-23','08:00:00','08:45:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-06-29 10:00:26',NULL,NULL,'2016-06-24 10:00:27','2016-06-24 10:00:27'),(4509,23,1044,1862,24,15778,0,'2016-06-23','15:00:00','16:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-29 10:00:26',NULL,NULL,'2016-06-24 10:00:27','2016-06-24 10:00:27'),(4510,23,1044,1862,24,15779,0,'2016-06-24','08:00:00','08:45:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-06-29 10:00:26',NULL,NULL,'2016-06-24 10:00:27','2016-06-24 10:00:27'),(4511,23,1045,1864,24,15769,0,'2016-06-20','15:15:00','16:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-29 10:00:26',NULL,NULL,'2016-06-24 10:00:27','2016-06-24 10:00:27'),(4512,23,1045,1864,24,15770,0,'2016-06-22','15:15:00','16:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-29 10:00:26',NULL,NULL,'2016-06-24 10:00:27','2016-06-24 10:00:27'),(4513,23,1046,1862,24,15771,0,'2016-06-23','08:00:00','08:45:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-06-29 10:00:26',NULL,NULL,'2016-06-24 10:00:27','2016-06-24 10:00:27'),(4514,23,1046,1867,25,15834,0,'2016-06-20','08:00:00','15:30:00','00:00:00',NULL,NULL,'07:30:00','assistant','approved',NULL,NULL,'2016-06-29 10:00:26',NULL,NULL,'2016-06-24 10:00:27','2016-06-24 10:00:27'),(4515,23,1047,1863,24,15730,0,'2016-06-22','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-29 10:00:26',NULL,NULL,'2016-06-24 10:00:27','2016-06-24 10:00:27'),(4516,23,1047,1863,24,15731,0,'2016-06-22','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-06-29 10:00:26',NULL,NULL,'2016-06-24 10:00:27','2016-06-24 10:00:27'),(4517,23,1047,1863,24,15733,0,'2016-06-23','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-29 10:00:26',NULL,NULL,'2016-06-24 10:00:27','2016-06-24 10:00:27'),(4518,23,1047,1863,24,15734,0,'2016-06-23','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-06-29 10:00:26',NULL,NULL,'2016-06-24 10:00:27','2016-06-24 10:00:27'),(4519,23,1047,1863,24,15736,0,'2016-06-24','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-06-29 10:00:26',NULL,NULL,'2016-06-24 10:00:27','2016-06-24 10:00:27'),(4520,23,1047,1863,24,15737,0,'2016-06-24','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-06-29 10:00:26',NULL,NULL,'2016-06-24 10:00:27','2016-06-24 10:00:27'),(4521,23,1048,1866,24,15718,0,'2016-06-20','12:30:00','13:15:00','00:00:00',NULL,NULL,'00:45:00','lead','approved',NULL,NULL,'2016-06-29 10:00:26',NULL,NULL,'2016-06-24 10:00:27','2016-06-24 10:00:27'),(4522,23,1048,1863,24,15736,0,'2016-06-24','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','observer','approved',NULL,NULL,'2016-06-29 10:00:26',NULL,NULL,'2016-06-24 10:00:27','2016-06-24 10:00:27'),(4523,23,1048,1865,24,15763,0,'2016-06-23','13:15:00','14:30:00','00:00:00',NULL,NULL,'01:15:00','head','approved',NULL,NULL,'2016-06-29 10:00:26',NULL,NULL,'2016-06-24 10:00:27','2016-06-24 10:00:27'),(4524,23,1048,1870,24,15847,0,'2016-06-20','13:30:00','14:30:00','00:00:00',NULL,NULL,'01:00:00','lead','approved',NULL,NULL,'2016-06-29 10:00:26',NULL,NULL,'2016-06-24 10:00:27','2016-06-24 10:00:27'),(4525,23,1048,1862,27,15854,0,'2016-06-24','09:00:00','12:00:00','00:00:00',NULL,NULL,'03:00:00','lead','approved',NULL,NULL,'2016-06-29 10:00:26',NULL,NULL,'2016-06-24 10:00:27','2016-06-24 10:00:27'),(4526,23,1049,1864,24,15708,0,'2016-06-21','13:00:00','14:00:00','00:00:00',NULL,NULL,'01:00:00','observer','approved',NULL,NULL,'2016-06-29 10:00:26',NULL,NULL,'2016-06-24 10:00:28','2016-06-24 10:00:28'),(5368,23,1243,1864,24,15708,0,'2016-06-28','13:00:00','14:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-06 10:00:26',NULL,NULL,'2016-07-01 10:00:24','2016-07-01 10:00:24'),(5369,23,1243,1864,24,15709,0,'2016-06-28','14:15:00','15:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-06 10:00:26',NULL,NULL,'2016-07-01 10:00:24','2016-07-01 10:00:24'),(5370,23,1243,1864,24,15710,0,'2016-06-29','13:00:00','14:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-06 10:00:26',NULL,NULL,'2016-07-01 10:00:24','2016-07-01 10:00:24'),(5371,23,1243,1864,24,15711,0,'2016-06-29','14:15:00','15:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-06 10:00:26',NULL,NULL,'2016-07-01 10:00:24','2016-07-01 10:00:24'),(5372,23,1243,1864,24,15712,0,'2016-06-30','13:00:00','14:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-06 10:00:26',NULL,NULL,'2016-07-01 10:00:24','2016-07-01 10:00:24'),(5373,23,1243,1864,24,15713,0,'2016-06-30','14:15:00','15:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-06 10:00:26',NULL,NULL,'2016-07-01 10:00:24','2016-07-01 10:00:24'),(5374,23,1243,1867,25,15834,0,'2016-06-27','08:00:00','15:30:00','00:00:00',NULL,NULL,'07:30:00','head','approved',NULL,NULL,'2016-07-06 10:00:26',NULL,NULL,'2016-07-01 10:00:24','2016-07-01 10:00:24'),(5375,23,1244,1862,27,15855,0,'2016-06-30','13:00:00','15:00:00','00:00:00',NULL,NULL,'02:00:00','lead','approved',NULL,NULL,'2016-07-06 10:00:26',NULL,NULL,'2016-07-01 10:00:24','2016-07-01 10:00:24'),(5376,23,1245,1863,24,15739,0,'2016-06-29','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-06 10:00:26',NULL,NULL,'2016-07-01 10:00:24','2016-07-01 10:00:24'),(5377,23,1245,1863,24,15740,0,'2016-06-29','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-07-06 10:00:26',NULL,NULL,'2016-07-01 10:00:24','2016-07-01 10:00:24'),(5378,23,1245,1863,24,15742,0,'2016-06-30','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-06 10:00:26',NULL,NULL,'2016-07-01 10:00:24','2016-07-01 10:00:24'),(5379,23,1245,1863,24,15743,0,'2016-06-30','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-07-06 10:00:26',NULL,NULL,'2016-07-01 10:00:24','2016-07-01 10:00:24'),(5380,23,1245,1863,24,15745,0,'2016-07-01','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-06 10:00:26',NULL,NULL,'2016-07-01 10:00:24','2016-07-01 10:00:24'),(5381,23,1245,1863,24,15746,0,'2016-07-01','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-07-06 10:00:26',NULL,NULL,'2016-07-01 10:00:24','2016-07-01 10:00:24'),(5382,23,1245,1862,24,15773,0,'2016-07-01','08:00:00','08:45:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-07-06 10:00:26',NULL,NULL,'2016-07-01 10:00:24','2016-07-01 10:00:24'),(5383,23,1245,1870,24,15848,0,'2016-06-28','13:30:00','14:30:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-06 10:00:26',NULL,NULL,'2016-07-01 10:00:24','2016-07-01 10:00:24'),(5384,23,1246,1866,24,15719,0,'2016-06-27','14:25:00','15:25:00','00:00:00',NULL,NULL,'01:00:00','lead','approved',NULL,NULL,'2016-07-06 10:00:26',NULL,NULL,'2016-07-01 10:00:24','2016-07-01 10:00:24'),(5385,23,1246,1865,24,15759,0,'2016-06-28','13:15:00','14:30:00','00:00:00',NULL,NULL,'01:15:00','head','approved',NULL,NULL,'2016-07-06 10:00:26',NULL,NULL,'2016-07-01 10:00:24','2016-07-01 10:00:24'),(5386,23,1246,1865,24,15762,0,'2016-06-29','14:45:00','15:30:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-07-06 10:00:26',NULL,NULL,'2016-07-01 10:00:24','2016-07-01 10:00:24'),(5387,23,1246,1862,24,15772,0,'2016-06-30','15:00:00','16:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-06 10:00:26',NULL,NULL,'2016-07-01 10:00:24','2016-07-01 10:00:24'),(5388,23,1246,1870,24,15851,0,'2016-07-01','13:30:00','14:30:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-06 10:00:26',NULL,NULL,'2016-07-01 10:00:24','2016-07-01 10:00:24'),(5389,23,1247,1862,24,15777,0,'2016-06-30','08:00:00','08:45:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-07-06 10:00:26',NULL,NULL,'2016-07-01 10:00:24','2016-07-01 10:00:24'),(5390,23,1247,1862,24,15778,0,'2016-06-30','15:00:00','16:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-06 10:00:26',NULL,NULL,'2016-07-01 10:00:24','2016-07-01 10:00:24'),(5391,23,1247,1862,24,15779,0,'2016-07-01','08:00:00','08:45:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-07-06 10:00:26',NULL,NULL,'2016-07-01 10:00:24','2016-07-01 10:00:24'),(5392,23,1248,1864,24,15769,0,'2016-06-27','15:15:00','16:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-06 10:00:26',NULL,NULL,'2016-07-01 10:00:24','2016-07-01 10:00:24'),(5393,23,1248,1864,24,15770,0,'2016-06-29','15:15:00','16:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-06 10:00:26',NULL,NULL,'2016-07-01 10:00:24','2016-07-01 10:00:24'),(5394,23,1249,1862,24,15771,0,'2016-06-30','08:00:00','08:45:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-07-06 10:00:26',NULL,NULL,'2016-07-01 10:00:24','2016-07-01 10:00:24'),(5395,23,1249,1867,25,15834,0,'2016-06-27','08:00:00','15:30:00','00:00:00',NULL,NULL,'07:30:00','assistant','approved',NULL,NULL,'2016-07-06 10:00:26',NULL,NULL,'2016-07-01 10:00:24','2016-07-01 10:00:24'),(5396,23,1250,1863,24,15730,0,'2016-06-29','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-06 10:00:26',NULL,NULL,'2016-07-01 10:00:24','2016-07-01 10:00:24'),(5397,23,1250,1863,24,15731,0,'2016-06-29','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-07-06 10:00:26',NULL,NULL,'2016-07-01 10:00:24','2016-07-01 10:00:24'),(5398,23,1250,1863,24,15733,0,'2016-06-30','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-06 10:00:26',NULL,NULL,'2016-07-01 10:00:24','2016-07-01 10:00:24'),(5399,23,1250,1863,24,15734,0,'2016-06-30','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-07-06 10:00:26',NULL,NULL,'2016-07-01 10:00:24','2016-07-01 10:00:24'),(5400,23,1250,1863,24,15736,0,'2016-07-01','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-06 10:00:26',NULL,NULL,'2016-07-01 10:00:24','2016-07-01 10:00:24'),(5401,23,1250,1863,24,15737,0,'2016-07-01','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-07-06 10:00:26',NULL,NULL,'2016-07-01 10:00:24','2016-07-01 10:00:24'),(5402,23,1251,1866,24,15718,0,'2016-06-27','12:30:00','13:15:00','00:00:00',NULL,NULL,'00:45:00','lead','approved',NULL,NULL,'2016-07-06 10:00:26',NULL,NULL,'2016-07-01 10:00:24','2016-07-01 10:00:24'),(5403,23,1251,1863,24,15736,0,'2016-07-01','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','observer','approved',NULL,NULL,'2016-07-06 10:00:26',NULL,NULL,'2016-07-01 10:00:24','2016-07-01 10:00:24'),(5404,23,1251,1865,24,15763,0,'2016-06-30','13:15:00','14:30:00','00:00:00',NULL,NULL,'01:15:00','head','approved',NULL,NULL,'2016-07-06 10:00:26',NULL,NULL,'2016-07-01 10:00:24','2016-07-01 10:00:24'),(5405,23,1251,1870,24,15847,0,'2016-06-27','13:30:00','14:30:00','00:00:00',NULL,NULL,'01:00:00','lead','approved',NULL,NULL,'2016-07-06 10:00:26',NULL,NULL,'2016-07-01 10:00:24','2016-07-01 10:00:24'),(5406,23,1251,1862,27,15854,0,'2016-07-01','09:00:00','12:00:00','00:00:00',NULL,NULL,'03:00:00','lead','approved',NULL,NULL,'2016-07-06 10:00:26',NULL,NULL,'2016-07-01 10:00:24','2016-07-01 10:00:24'),(5407,23,1252,1864,24,15708,0,'2016-06-28','13:00:00','14:00:00','00:00:00',NULL,NULL,'01:00:00','observer','approved',NULL,NULL,'2016-07-06 10:00:26',NULL,NULL,'2016-07-01 10:00:24','2016-07-01 10:00:24'),(5727,23,1401,1862,24,15773,0,'2016-04-08','08:00:00','08:45:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-07-06 10:00:29',NULL,NULL,'2016-07-01 11:19:43','2016-07-01 11:19:43'),(5728,23,1402,1862,24,15772,0,'2016-04-07','15:00:00','16:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-06 10:00:29',NULL,NULL,'2016-07-01 11:19:43','2016-07-01 11:19:43'),(5729,23,1403,1862,24,15771,0,'2016-04-07','08:00:00','08:45:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-07-06 10:00:29',NULL,NULL,'2016-07-01 11:19:43','2016-07-01 11:19:43'),(5730,23,1404,1863,24,15730,0,'2016-04-06','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-06 10:00:29',NULL,NULL,'2016-07-01 11:19:43','2016-07-01 11:19:43'),(5731,23,1404,1863,24,15731,0,'2016-04-06','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-07-06 10:00:29',NULL,NULL,'2016-07-01 11:19:43','2016-07-01 11:19:43'),(5732,23,1404,1863,24,15733,0,'2016-04-07','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-06 10:00:29',NULL,NULL,'2016-07-01 11:19:43','2016-07-01 11:19:43'),(5733,23,1404,1863,24,15734,0,'2016-04-07','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-07-06 10:00:29',NULL,NULL,'2016-07-01 11:19:43','2016-07-01 11:19:43'),(5734,23,1404,1863,24,15736,0,'2016-04-08','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-06 10:00:29',NULL,NULL,'2016-07-01 11:19:43','2016-07-01 11:19:43'),(5735,23,1404,1863,24,15737,0,'2016-04-08','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-07-06 10:00:29',NULL,NULL,'2016-07-01 11:19:43','2016-07-01 11:19:43'),(5736,23,1405,1863,24,15736,0,'2016-04-08','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','observer','approved',NULL,NULL,'2016-07-06 10:00:29',NULL,NULL,'2016-07-01 11:19:43','2016-07-01 11:19:43'),(6382,23,1588,1864,24,15708,0,'2016-07-05','13:00:00','14:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-13 10:00:23',NULL,NULL,'2016-07-06 15:40:13','2016-07-06 15:40:13'),(6383,23,1588,1864,24,15709,0,'2016-07-05','14:15:00','15:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-13 10:00:23',NULL,NULL,'2016-07-06 15:40:13','2016-07-06 15:40:13'),(6384,23,1588,1864,24,15710,0,'2016-07-06','13:00:00','14:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-13 10:00:23',NULL,NULL,'2016-07-06 15:40:13','2016-07-06 15:40:13'),(6385,23,1588,1864,24,15711,0,'2016-07-06','14:15:00','15:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-13 10:00:23',NULL,NULL,'2016-07-06 15:40:13','2016-07-06 15:40:13'),(6386,23,1588,1864,24,15712,0,'2016-07-07','13:00:00','14:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-13 10:00:23',NULL,NULL,'2016-07-06 15:40:13','2016-07-06 15:40:13'),(6387,23,1588,1864,24,15713,0,'2016-07-07','14:15:00','15:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-13 10:00:23',NULL,NULL,'2016-07-06 15:40:13','2016-07-06 15:40:13'),(6388,23,1589,1867,25,15835,0,'2016-07-05','09:00:00','15:30:00','00:00:00',NULL,NULL,'06:30:00','assistant','approved',NULL,NULL,'2016-07-13 10:00:23',NULL,NULL,'2016-07-06 15:40:13','2016-07-06 15:40:13'),(6389,23,1589,1862,27,15855,0,'2016-07-07','13:00:00','15:00:00','00:00:00',NULL,NULL,'02:00:00','lead','approved',NULL,NULL,'2016-07-13 10:00:23',NULL,NULL,'2016-07-06 15:40:13','2016-07-06 15:40:13'),(6390,23,1590,1863,24,15739,0,'2016-07-06','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-13 10:00:23',NULL,NULL,'2016-07-06 15:40:13','2016-07-06 15:40:13'),(6391,23,1590,1863,24,15740,0,'2016-07-06','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-07-13 10:00:23',NULL,NULL,'2016-07-06 15:40:13','2016-07-06 15:40:13'),(6392,23,1590,1863,24,15742,0,'2016-07-07','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-13 10:00:23',NULL,NULL,'2016-07-06 15:40:13','2016-07-06 15:40:13'),(6393,23,1590,1863,24,15743,0,'2016-07-07','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-07-13 10:00:23',NULL,NULL,'2016-07-06 15:40:13','2016-07-06 15:40:13'),(6394,23,1590,1863,24,15745,0,'2016-07-08','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-13 10:00:23',NULL,NULL,'2016-07-06 15:40:13','2016-07-06 15:40:13'),(6395,23,1590,1863,24,15746,0,'2016-07-08','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-07-13 10:00:23',NULL,NULL,'2016-07-06 15:40:13','2016-07-06 15:40:13'),(6396,23,1590,1862,24,15773,0,'2016-07-08','08:00:00','08:45:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-07-13 10:00:23',NULL,NULL,'2016-07-06 15:40:13','2016-07-06 15:40:13'),(6397,23,1590,1870,24,15848,0,'2016-07-05','13:30:00','14:30:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-13 10:00:23',NULL,NULL,'2016-07-06 15:40:13','2016-07-06 15:40:13'),(6398,23,1591,1866,24,15719,0,'2016-07-04','14:25:00','15:25:00','00:00:00',NULL,NULL,'01:00:00','lead','approved',NULL,NULL,'2016-07-13 10:00:23',NULL,NULL,'2016-07-06 15:40:13','2016-07-06 15:40:13'),(6399,23,1591,1865,24,15759,0,'2016-07-05','13:15:00','14:30:00','00:00:00',NULL,NULL,'01:15:00','head','approved',NULL,NULL,'2016-07-13 10:00:23',NULL,NULL,'2016-07-06 15:40:13','2016-07-06 15:40:13'),(6400,23,1591,1865,24,15762,0,'2016-07-06','14:45:00','15:30:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-07-13 10:00:23',NULL,NULL,'2016-07-06 15:40:13','2016-07-06 15:40:13'),(6401,23,1591,1862,24,15772,0,'2016-07-07','15:00:00','16:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-13 10:00:23',NULL,NULL,'2016-07-06 15:40:13','2016-07-06 15:40:13'),(6402,23,1591,1870,24,15851,0,'2016-07-08','13:30:00','14:30:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-13 10:00:23',NULL,NULL,'2016-07-06 15:40:13','2016-07-06 15:40:13'),(6403,23,1592,1862,24,15777,0,'2016-07-07','08:00:00','08:45:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-07-13 10:00:23',NULL,NULL,'2016-07-06 15:40:13','2016-07-06 15:40:13'),(6404,23,1592,1862,24,15778,0,'2016-07-07','15:00:00','16:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-13 10:00:23',NULL,NULL,'2016-07-06 15:40:13','2016-07-06 15:40:13'),(6405,23,1592,1862,24,15779,0,'2016-07-08','08:00:00','08:45:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-07-13 10:00:23',NULL,NULL,'2016-07-06 15:40:13','2016-07-06 15:40:13'),(6406,23,1593,1864,24,15769,0,'2016-07-04','15:15:00','16:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-13 10:00:23',NULL,NULL,'2016-07-06 15:40:13','2016-07-06 15:40:13'),(6407,23,1593,1864,24,15770,0,'2016-07-06','15:15:00','16:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-13 10:00:23',NULL,NULL,'2016-07-06 15:40:13','2016-07-06 15:40:13'),(6408,23,1593,1867,25,15835,0,'2016-07-05','09:00:00','15:30:00','00:00:00',NULL,NULL,'06:30:00','head','approved',NULL,NULL,'2016-07-13 10:00:23',NULL,NULL,'2016-07-06 15:40:13','2016-07-06 15:40:13'),(6409,23,1594,1862,24,15771,0,'2016-07-07','08:00:00','08:45:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-07-13 10:00:23',NULL,NULL,'2016-07-06 15:40:13','2016-07-06 15:40:13'),(6410,23,1595,1863,24,15730,0,'2016-07-06','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-13 10:00:23',NULL,NULL,'2016-07-06 15:40:13','2016-07-06 15:40:13'),(6411,23,1595,1863,24,15731,0,'2016-07-06','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-07-13 10:00:23',NULL,NULL,'2016-07-06 15:40:13','2016-07-06 15:40:13'),(6412,23,1595,1863,24,15733,0,'2016-07-07','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-13 10:00:23',NULL,NULL,'2016-07-06 15:40:13','2016-07-06 15:40:13'),(6413,23,1595,1863,24,15734,0,'2016-07-07','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-07-13 10:00:23',NULL,NULL,'2016-07-06 15:40:13','2016-07-06 15:40:13'),(6414,23,1595,1863,24,15736,0,'2016-07-08','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-13 10:00:23',NULL,NULL,'2016-07-06 15:40:13','2016-07-06 15:40:13'),(6415,23,1595,1863,24,15737,0,'2016-07-08','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-07-13 10:00:23',NULL,NULL,'2016-07-06 15:40:13','2016-07-06 15:40:13'),(6416,23,1596,1866,24,15718,0,'2016-07-04','12:30:00','13:15:00','00:00:00',NULL,NULL,'00:45:00','lead','approved',NULL,NULL,'2016-07-13 10:00:23',NULL,NULL,'2016-07-06 15:40:14','2016-07-06 15:40:14'),(6417,23,1596,1863,24,15736,0,'2016-07-08','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','observer','approved',NULL,NULL,'2016-07-13 10:00:23',NULL,NULL,'2016-07-06 15:40:14','2016-07-06 15:40:14'),(6418,23,1596,1865,24,15763,0,'2016-07-07','13:15:00','14:30:00','00:00:00',NULL,NULL,'01:15:00','head','approved',NULL,NULL,'2016-07-13 10:00:23',NULL,NULL,'2016-07-06 15:40:14','2016-07-06 15:40:14'),(6419,23,1596,1867,25,15835,0,'2016-07-05','09:00:00','15:30:00','00:00:00',NULL,NULL,'06:30:00','head','approved',NULL,NULL,'2016-07-13 10:00:23',NULL,NULL,'2016-07-06 15:40:14','2016-07-06 15:40:14'),(6420,23,1596,1870,24,15847,0,'2016-07-04','13:30:00','14:30:00','00:00:00',NULL,NULL,'01:00:00','lead','approved',NULL,NULL,'2016-07-13 10:00:23',NULL,NULL,'2016-07-06 15:40:14','2016-07-06 15:40:14'),(6421,23,1596,1862,27,15854,0,'2016-07-08','09:00:00','12:00:00','00:00:00',NULL,NULL,'03:00:00','lead','approved',NULL,NULL,'2016-07-13 10:00:23',NULL,NULL,'2016-07-06 15:40:14','2016-07-06 15:40:14'),(6422,23,1597,1864,24,15708,0,'2016-07-05','13:00:00','14:00:00','00:00:00',NULL,NULL,'01:00:00','observer','approved',NULL,NULL,'2016-07-13 10:00:23',NULL,NULL,'2016-07-06 15:40:14','2016-07-06 15:40:14'),(7423,23,1837,1864,24,15708,0,'2016-07-12','13:00:00','14:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-20 10:00:25',NULL,NULL,'2016-07-15 10:00:26','2016-07-15 10:00:26'),(7424,23,1837,1864,24,15709,0,'2016-07-12','14:15:00','15:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-20 10:00:25',NULL,NULL,'2016-07-15 10:00:26','2016-07-15 10:00:26'),(7425,23,1837,1864,24,15710,0,'2016-07-13','13:00:00','14:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-20 10:00:25',NULL,NULL,'2016-07-15 10:00:26','2016-07-15 10:00:26'),(7426,23,1837,1864,24,15711,0,'2016-07-13','14:15:00','15:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-20 10:00:25',NULL,NULL,'2016-07-15 10:00:26','2016-07-15 10:00:26'),(7427,23,1837,1864,24,15712,0,'2016-07-14','13:00:00','14:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-20 10:00:25',NULL,NULL,'2016-07-15 10:00:26','2016-07-15 10:00:26'),(7428,23,1837,1864,24,15713,0,'2016-07-14','14:15:00','15:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-20 10:00:25',NULL,NULL,'2016-07-15 10:00:26','2016-07-15 10:00:26'),(7429,23,1837,1867,25,15834,0,'2016-07-11','08:00:00','15:30:00','00:00:00',NULL,NULL,'07:30:00','head','approved',NULL,NULL,'2016-07-20 10:00:25',NULL,NULL,'2016-07-15 10:00:26','2016-07-15 10:00:26'),(7430,23,1838,1862,27,15855,0,'2016-07-14','13:00:00','15:00:00','00:00:00',NULL,NULL,'02:00:00','lead','approved',NULL,NULL,'2016-07-20 10:00:25',NULL,NULL,'2016-07-15 10:00:26','2016-07-15 10:00:26'),(7431,23,1839,1863,24,15739,0,'2016-07-13','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-20 10:00:25',NULL,NULL,'2016-07-15 10:00:26','2016-07-15 10:00:26'),(7432,23,1839,1863,24,15740,0,'2016-07-13','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-07-20 10:00:25',NULL,NULL,'2016-07-15 10:00:26','2016-07-15 10:00:26'),(7433,23,1839,1863,24,15742,0,'2016-07-14','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-20 10:00:25',NULL,NULL,'2016-07-15 10:00:26','2016-07-15 10:00:26'),(7434,23,1839,1863,24,15743,0,'2016-07-14','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-07-20 10:00:25',NULL,NULL,'2016-07-15 10:00:26','2016-07-15 10:00:26'),(7435,23,1839,1863,24,15745,0,'2016-07-15','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-20 10:00:25',NULL,NULL,'2016-07-15 10:00:26','2016-07-15 10:00:26'),(7436,23,1839,1863,24,15746,0,'2016-07-15','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-07-20 10:00:25',NULL,NULL,'2016-07-15 10:00:26','2016-07-15 10:00:26'),(7437,23,1839,1862,24,15773,0,'2016-07-15','08:00:00','08:45:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-07-20 10:00:25',NULL,NULL,'2016-07-15 10:00:26','2016-07-15 10:00:26'),(7438,23,1839,1870,24,15848,0,'2016-07-12','13:30:00','14:30:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-20 10:00:25',NULL,NULL,'2016-07-15 10:00:26','2016-07-15 10:00:26'),(7439,23,1840,1866,24,15719,0,'2016-07-11','14:25:00','15:25:00','00:00:00',NULL,NULL,'01:00:00','lead','approved',NULL,NULL,'2016-07-20 10:00:25',NULL,NULL,'2016-07-15 10:00:26','2016-07-15 10:00:26'),(7440,23,1840,1865,24,15759,0,'2016-07-12','13:15:00','14:30:00','00:00:00',NULL,NULL,'01:15:00','head','approved',NULL,NULL,'2016-07-20 10:00:25',NULL,NULL,'2016-07-15 10:00:26','2016-07-15 10:00:26'),(7441,23,1840,1865,24,15762,0,'2016-07-13','14:45:00','15:30:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-07-20 10:00:25',NULL,NULL,'2016-07-15 10:00:26','2016-07-15 10:00:26'),(7442,23,1840,1862,24,15772,0,'2016-07-14','15:00:00','16:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-20 10:00:25',NULL,NULL,'2016-07-15 10:00:26','2016-07-15 10:00:26'),(7443,23,1840,1870,24,15851,0,'2016-07-15','13:30:00','14:30:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-20 10:00:25',NULL,NULL,'2016-07-15 10:00:26','2016-07-15 10:00:26'),(7444,23,1841,1862,24,15777,0,'2016-07-14','08:00:00','08:45:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-07-20 10:00:25',NULL,NULL,'2016-07-15 10:00:26','2016-07-15 10:00:26'),(7445,23,1841,1862,24,15778,0,'2016-07-14','15:00:00','16:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-20 10:00:25',NULL,NULL,'2016-07-15 10:00:26','2016-07-15 10:00:26'),(7446,23,1841,1862,24,15779,0,'2016-07-15','08:00:00','08:45:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-07-20 10:00:25',NULL,NULL,'2016-07-15 10:00:26','2016-07-15 10:00:26'),(7447,23,1842,1864,24,15769,0,'2016-07-11','15:15:00','16:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-20 10:00:25',NULL,NULL,'2016-07-15 10:00:26','2016-07-15 10:00:26'),(7448,23,1842,1864,24,15770,0,'2016-07-13','15:15:00','16:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-20 10:00:25',NULL,NULL,'2016-07-15 10:00:26','2016-07-15 10:00:26'),(7449,23,1843,1862,24,15771,0,'2016-07-14','08:00:00','08:45:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-07-20 10:00:25',NULL,NULL,'2016-07-15 10:00:26','2016-07-15 10:00:26'),(7450,23,1843,1867,25,15834,0,'2016-07-11','08:00:00','15:30:00','00:00:00',NULL,NULL,'07:30:00','assistant','approved',NULL,NULL,'2016-07-20 10:00:25',NULL,NULL,'2016-07-15 10:00:26','2016-07-15 10:00:26'),(7451,23,1844,1863,24,15730,0,'2016-07-13','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-20 10:00:25',NULL,NULL,'2016-07-15 10:00:26','2016-07-15 10:00:26'),(7452,23,1844,1863,24,15731,0,'2016-07-13','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-07-20 10:00:25',NULL,NULL,'2016-07-15 10:00:26','2016-07-15 10:00:26'),(7453,23,1844,1863,24,15733,0,'2016-07-14','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-20 10:00:25',NULL,NULL,'2016-07-15 10:00:26','2016-07-15 10:00:26'),(7454,23,1844,1863,24,15734,0,'2016-07-14','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-07-20 10:00:25',NULL,NULL,'2016-07-15 10:00:26','2016-07-15 10:00:26'),(7455,23,1844,1863,24,15736,0,'2016-07-15','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-20 10:00:25',NULL,NULL,'2016-07-15 10:00:26','2016-07-15 10:00:26'),(7456,23,1844,1863,24,15737,0,'2016-07-15','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-07-20 10:00:25',NULL,NULL,'2016-07-15 10:00:26','2016-07-15 10:00:26'),(7457,23,1845,1866,24,15718,0,'2016-07-11','12:30:00','13:15:00','00:00:00',NULL,NULL,'00:45:00','lead','approved',NULL,NULL,'2016-07-20 10:00:25',NULL,NULL,'2016-07-15 10:00:26','2016-07-15 10:00:26'),(7458,23,1845,1863,24,15736,0,'2016-07-15','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','observer','approved',NULL,NULL,'2016-07-20 10:00:25',NULL,NULL,'2016-07-15 10:00:26','2016-07-15 10:00:26'),(7459,23,1845,1865,24,15763,0,'2016-07-14','13:15:00','14:30:00','00:00:00',NULL,NULL,'01:15:00','head','approved',NULL,NULL,'2016-07-20 10:00:25',NULL,NULL,'2016-07-15 10:00:26','2016-07-15 10:00:26'),(7460,23,1845,1870,24,15847,0,'2016-07-11','13:30:00','14:30:00','00:00:00',NULL,NULL,'01:00:00','lead','approved',NULL,NULL,'2016-07-20 10:00:25',NULL,NULL,'2016-07-15 10:00:26','2016-07-15 10:00:26'),(7461,23,1845,1862,27,15854,0,'2016-07-15','09:00:00','12:00:00','00:00:00',NULL,NULL,'03:00:00','lead','approved',NULL,NULL,'2016-07-20 10:00:25',NULL,NULL,'2016-07-15 10:00:26','2016-07-15 10:00:26'),(7462,23,1846,1864,24,15708,0,'2016-07-12','13:00:00','14:00:00','00:00:00',NULL,NULL,'01:00:00','observer','approved',NULL,NULL,'2016-07-20 10:00:25',NULL,NULL,'2016-07-15 10:00:26','2016-07-15 10:00:26'),(8406,23,2080,1864,24,15708,0,'2016-07-19','13:00:00','14:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-27 10:00:26',NULL,NULL,'2016-07-22 10:00:25','2016-07-22 10:00:25'),(8407,23,2080,1864,24,15709,0,'2016-07-19','14:15:00','15:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-27 10:00:26',NULL,NULL,'2016-07-22 10:00:25','2016-07-22 10:00:25'),(8408,23,2080,1864,24,15710,0,'2016-07-20','13:00:00','14:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-27 10:00:26',NULL,NULL,'2016-07-22 10:00:25','2016-07-22 10:00:25'),(8409,23,2080,1864,24,15711,0,'2016-07-20','14:15:00','15:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-27 10:00:26',NULL,NULL,'2016-07-22 10:00:25','2016-07-22 10:00:25'),(8410,23,2080,1864,24,15712,0,'2016-07-21','13:00:00','14:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-27 10:00:26',NULL,NULL,'2016-07-22 10:00:25','2016-07-22 10:00:25'),(8411,23,2080,1864,24,15713,0,'2016-07-21','14:15:00','15:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-27 10:00:26',NULL,NULL,'2016-07-22 10:00:25','2016-07-22 10:00:25'),(8412,23,2080,1867,25,15834,0,'2016-07-18','08:00:00','15:30:00','00:00:00',NULL,NULL,'07:30:00','head','approved',NULL,NULL,'2016-07-27 10:00:26',NULL,NULL,'2016-07-22 10:00:25','2016-07-22 10:00:25'),(8413,23,2081,1862,27,15855,0,'2016-07-21','13:00:00','15:00:00','00:00:00',NULL,NULL,'02:00:00','lead','approved',NULL,NULL,'2016-07-27 10:00:26',NULL,NULL,'2016-07-22 10:00:25','2016-07-22 10:00:25'),(8414,23,2082,1863,24,15739,0,'2016-07-20','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-27 10:00:26',NULL,NULL,'2016-07-22 10:00:25','2016-07-22 10:00:25'),(8415,23,2082,1863,24,15740,0,'2016-07-20','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-07-27 10:00:26',NULL,NULL,'2016-07-22 10:00:25','2016-07-22 10:00:25'),(8416,23,2082,1863,24,15742,0,'2016-07-21','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-27 10:00:26',NULL,NULL,'2016-07-22 10:00:25','2016-07-22 10:00:25'),(8417,23,2082,1863,24,15743,0,'2016-07-21','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-07-27 10:00:26',NULL,NULL,'2016-07-22 10:00:25','2016-07-22 10:00:25'),(8418,23,2082,1863,24,15745,0,'2016-07-22','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-27 10:00:26',NULL,NULL,'2016-07-22 10:00:25','2016-07-22 10:00:25'),(8419,23,2082,1863,24,15746,0,'2016-07-22','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-07-27 10:00:26',NULL,NULL,'2016-07-22 10:00:25','2016-07-22 10:00:25'),(8420,23,2082,1862,24,15773,0,'2016-07-22','08:00:00','08:45:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-07-27 10:00:26',NULL,NULL,'2016-07-22 10:00:25','2016-07-22 10:00:25'),(8421,23,2082,1870,24,15848,0,'2016-07-19','13:30:00','14:30:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-27 10:00:26',NULL,NULL,'2016-07-22 10:00:25','2016-07-22 10:00:25'),(8422,23,2083,1866,24,15719,0,'2016-07-18','14:25:00','15:25:00','00:00:00',NULL,NULL,'01:00:00','lead','approved',NULL,NULL,'2016-07-27 10:00:26',NULL,NULL,'2016-07-22 10:00:25','2016-07-22 10:00:25'),(8423,23,2083,1865,24,15759,0,'2016-07-19','13:15:00','14:30:00','00:00:00',NULL,NULL,'01:15:00','head','approved',NULL,NULL,'2016-07-27 10:00:26',NULL,NULL,'2016-07-22 10:00:25','2016-07-22 10:00:25'),(8424,23,2083,1865,24,15762,0,'2016-07-20','14:45:00','15:30:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-07-27 10:00:26',NULL,NULL,'2016-07-22 10:00:25','2016-07-22 10:00:25'),(8425,23,2083,1862,24,15772,0,'2016-07-21','15:00:00','16:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-27 10:00:26',NULL,NULL,'2016-07-22 10:00:25','2016-07-22 10:00:25'),(8426,23,2083,1870,24,15851,0,'2016-07-22','13:30:00','14:30:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-27 10:00:26',NULL,NULL,'2016-07-22 10:00:25','2016-07-22 10:00:25'),(8427,23,2084,1862,24,15777,0,'2016-07-21','08:00:00','08:45:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-07-27 10:00:26',NULL,NULL,'2016-07-22 10:00:25','2016-07-22 10:00:25'),(8428,23,2084,1862,24,15778,0,'2016-07-21','15:00:00','16:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-27 10:00:26',NULL,NULL,'2016-07-22 10:00:25','2016-07-22 10:00:25'),(8429,23,2084,1862,24,15779,0,'2016-07-22','08:00:00','08:45:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-07-27 10:00:26',NULL,NULL,'2016-07-22 10:00:25','2016-07-22 10:00:25'),(8430,23,2085,1864,24,15769,0,'2016-07-18','15:15:00','16:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-27 10:00:26',NULL,NULL,'2016-07-22 10:00:25','2016-07-22 10:00:25'),(8431,23,2085,1864,24,15770,0,'2016-07-20','15:15:00','16:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-27 10:00:26',NULL,NULL,'2016-07-22 10:00:25','2016-07-22 10:00:25'),(8432,23,2086,1862,24,15771,0,'2016-07-21','08:00:00','08:45:00','00:00:00',NULL,NULL,'00:45:00','head','approved',NULL,NULL,'2016-07-27 10:00:26',NULL,NULL,'2016-07-22 10:00:25','2016-07-22 10:00:25'),(8433,23,2086,1867,25,15834,0,'2016-07-18','08:00:00','15:30:00','00:00:00',NULL,NULL,'07:30:00','assistant','approved',NULL,NULL,'2016-07-27 10:00:26',NULL,NULL,'2016-07-22 10:00:25','2016-07-22 10:00:25'),(8434,23,2087,1863,24,15730,0,'2016-07-20','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-27 10:00:26',NULL,NULL,'2016-07-22 10:00:25','2016-07-22 10:00:25'),(8435,23,2087,1863,24,15731,0,'2016-07-20','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-07-27 10:00:26',NULL,NULL,'2016-07-22 10:00:25','2016-07-22 10:00:25'),(8436,23,2087,1863,24,15733,0,'2016-07-21','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-27 10:00:26',NULL,NULL,'2016-07-22 10:00:25','2016-07-22 10:00:25'),(8437,23,2087,1863,24,15734,0,'2016-07-21','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-07-27 10:00:26',NULL,NULL,'2016-07-22 10:00:25','2016-07-22 10:00:25'),(8438,23,2087,1863,24,15736,0,'2016-07-22','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-07-27 10:00:26',NULL,NULL,'2016-07-22 10:00:25','2016-07-22 10:00:25'),(8439,23,2087,1863,24,15737,0,'2016-07-22','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-07-27 10:00:26',NULL,NULL,'2016-07-22 10:00:25','2016-07-22 10:00:25'),(8440,23,2088,1866,24,15718,0,'2016-07-18','12:30:00','13:15:00','00:00:00',NULL,NULL,'00:45:00','lead','approved',NULL,NULL,'2016-07-27 10:00:26',NULL,NULL,'2016-07-22 10:00:25','2016-07-22 10:00:25'),(8441,23,2088,1863,24,15736,0,'2016-07-22','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','observer','approved',NULL,NULL,'2016-07-27 10:00:26',NULL,NULL,'2016-07-22 10:00:25','2016-07-22 10:00:25'),(8442,23,2088,1865,24,15763,0,'2016-07-21','13:15:00','14:30:00','00:00:00',NULL,NULL,'01:15:00','head','approved',NULL,NULL,'2016-07-27 10:00:26',NULL,NULL,'2016-07-22 10:00:25','2016-07-22 10:00:25'),(8443,23,2088,1870,24,15847,0,'2016-07-18','13:30:00','14:30:00','00:00:00',NULL,NULL,'01:00:00','lead','approved',NULL,NULL,'2016-07-27 10:00:26',NULL,NULL,'2016-07-22 10:00:25','2016-07-22 10:00:25'),(8444,23,2088,1862,27,15854,0,'2016-07-22','09:00:00','12:00:00','00:00:00',NULL,NULL,'03:00:00','lead','approved',NULL,NULL,'2016-07-27 10:00:26',NULL,NULL,'2016-07-22 10:00:25','2016-07-22 10:00:25'),(8445,23,2089,1864,24,15708,0,'2016-07-19','13:00:00','14:00:00','00:00:00',NULL,NULL,'01:00:00','observer','approved',NULL,NULL,'2016-07-27 10:00:26',NULL,NULL,'2016-07-22 10:00:25','2016-07-22 10:00:25'),(9401,23,2323,1864,24,15708,0,'2016-07-26','13:00:00','14:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-08-03 10:00:28',NULL,NULL,'2016-07-29 10:00:25','2016-07-29 10:00:25'),(9402,23,2323,1864,24,15709,0,'2016-07-26','14:15:00','15:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-08-03 10:00:28',NULL,NULL,'2016-07-29 10:00:25','2016-07-29 10:00:25'),(9403,23,2323,1864,24,15710,0,'2016-07-27','13:00:00','14:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-08-03 10:00:28',NULL,NULL,'2016-07-29 10:00:25','2016-07-29 10:00:25'),(9404,23,2323,1864,24,15711,0,'2016-07-27','14:15:00','15:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-08-03 10:00:28',NULL,NULL,'2016-07-29 10:00:25','2016-07-29 10:00:25'),(9405,23,2323,1864,24,15712,0,'2016-07-28','13:00:00','14:00:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-08-03 10:00:28',NULL,NULL,'2016-07-29 10:00:25','2016-07-29 10:00:25'),(9406,23,2323,1864,24,15713,0,'2016-07-28','14:15:00','15:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-08-03 10:00:28',NULL,NULL,'2016-07-29 10:00:25','2016-07-29 10:00:25'),(9407,23,2323,1867,25,15834,0,'2016-07-25','08:00:00','15:30:00','00:00:00',NULL,NULL,'07:30:00','head','approved',NULL,NULL,'2016-08-03 10:00:28',NULL,NULL,'2016-07-29 10:00:25','2016-07-29 10:00:25'),(9408,23,2324,1862,27,15855,0,'2016-07-28','13:00:00','15:00:00','00:00:00',NULL,NULL,'02:00:00','lead','approved',NULL,NULL,'2016-08-03 10:00:28',NULL,NULL,'2016-07-29 10:00:25','2016-07-29 10:00:25'),(9409,23,2325,1870,24,15848,0,'2016-07-26','13:30:00','14:30:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-08-03 10:00:28',NULL,NULL,'2016-07-29 10:00:25','2016-07-29 10:00:25'),(9410,23,2326,1866,24,15719,0,'2016-07-25','14:25:00','15:25:00','00:00:00',NULL,NULL,'01:00:00','lead','approved',NULL,NULL,'2016-08-03 10:00:28',NULL,NULL,'2016-07-29 10:00:25','2016-07-29 10:00:25'),(9411,23,2326,1870,24,15851,0,'2016-07-29','13:30:00','14:30:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-08-03 10:00:28',NULL,NULL,'2016-07-29 10:00:25','2016-07-29 10:00:25'),(9412,23,2327,1864,24,15769,0,'2016-07-25','15:15:00','16:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-08-03 10:00:28',NULL,NULL,'2016-07-29 10:00:25','2016-07-29 10:00:25'),(9413,23,2327,1864,24,15770,0,'2016-07-27','15:15:00','16:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-08-03 10:00:28',NULL,NULL,'2016-07-29 10:00:25','2016-07-29 10:00:25'),(9414,23,2328,1867,25,15834,0,'2016-07-25','08:00:00','15:30:00','00:00:00',NULL,NULL,'07:30:00','assistant','approved',NULL,NULL,'2016-08-03 10:00:28',NULL,NULL,'2016-07-29 10:00:25','2016-07-29 10:00:25'),(9415,23,2329,1863,24,15730,0,'2016-07-27','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-08-03 10:00:28',NULL,NULL,'2016-07-29 10:00:25','2016-07-29 10:00:25'),(9416,23,2329,1863,24,15731,0,'2016-07-27','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-08-03 10:00:28',NULL,NULL,'2016-07-29 10:00:25','2016-07-29 10:00:25'),(9417,23,2329,1863,24,15733,0,'2016-07-28','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-08-03 10:00:28',NULL,NULL,'2016-07-29 10:00:25','2016-07-29 10:00:25'),(9418,23,2329,1863,24,15734,0,'2016-07-28','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-08-03 10:00:28',NULL,NULL,'2016-07-29 10:00:25','2016-07-29 10:00:25'),(9419,23,2329,1863,24,15736,0,'2016-07-29','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-08-03 10:00:28',NULL,NULL,'2016-07-29 10:00:25','2016-07-29 10:00:25'),(9420,23,2329,1863,24,15737,0,'2016-07-29','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-08-03 10:00:28',NULL,NULL,'2016-07-29 10:00:25','2016-07-29 10:00:25'),(9421,23,2330,1866,24,15718,0,'2016-07-25','12:30:00','13:15:00','00:00:00',NULL,NULL,'00:45:00','lead','approved',NULL,NULL,'2016-08-03 10:00:28',NULL,NULL,'2016-07-29 10:00:25','2016-07-29 10:00:25'),(9422,23,2330,1863,24,15736,0,'2016-07-29','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','observer','approved',NULL,NULL,'2016-08-03 10:00:28',NULL,NULL,'2016-07-29 10:00:25','2016-07-29 10:00:25'),(9423,23,2330,1870,24,15847,0,'2016-07-25','13:30:00','14:30:00','00:00:00',NULL,NULL,'01:00:00','lead','approved',NULL,NULL,'2016-08-03 10:00:28',NULL,NULL,'2016-07-29 10:00:25','2016-07-29 10:00:25'),(9424,23,2330,1862,27,15854,0,'2016-07-29','09:00:00','12:00:00','00:00:00',NULL,NULL,'03:00:00','lead','approved',NULL,NULL,'2016-08-03 10:00:28',NULL,NULL,'2016-07-29 10:00:25','2016-07-29 10:00:25'),(9425,23,2331,1864,24,15708,0,'2016-07-26','13:00:00','14:00:00','00:00:00',NULL,NULL,'01:00:00','observer','approved',NULL,NULL,'2016-08-03 10:00:28',NULL,NULL,'2016-07-29 10:00:25','2016-07-29 10:00:25'),(10139,23,2541,1862,27,15855,0,'2016-08-04','13:00:00','15:00:00','00:00:00',NULL,NULL,'02:00:00','lead','approved',NULL,NULL,'2016-08-10 10:00:25',NULL,NULL,'2016-08-05 10:00:28','2016-08-05 10:00:28'),(10140,23,2542,1863,24,15730,0,'2016-08-03','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-08-10 10:00:25',NULL,NULL,'2016-08-05 10:00:28','2016-08-05 10:00:28'),(10141,23,2542,1863,24,15731,0,'2016-08-03','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-08-10 10:00:25',NULL,NULL,'2016-08-05 10:00:28','2016-08-05 10:00:28'),(10142,23,2542,1863,24,15733,0,'2016-08-04','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-08-10 10:00:25',NULL,NULL,'2016-08-05 10:00:28','2016-08-05 10:00:28'),(10143,23,2542,1863,24,15734,0,'2016-08-04','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-08-10 10:00:25',NULL,NULL,'2016-08-05 10:00:28','2016-08-05 10:00:28'),(10144,23,2542,1863,24,15736,0,'2016-08-05','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-08-10 10:00:25',NULL,NULL,'2016-08-05 10:00:28','2016-08-05 10:00:28'),(10145,23,2542,1863,24,15737,0,'2016-08-05','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-08-10 10:00:25',NULL,NULL,'2016-08-05 10:00:28','2016-08-05 10:00:28'),(10146,23,2543,1863,24,15736,0,'2016-08-05','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','observer','approved',NULL,NULL,'2016-08-10 10:00:25',NULL,NULL,'2016-08-05 10:00:28','2016-08-05 10:00:28'),(10147,23,2543,1862,27,15854,0,'2016-08-05','09:00:00','12:00:00','00:00:00',NULL,NULL,'03:00:00','lead','approved',NULL,NULL,'2016-08-10 10:00:25',NULL,NULL,'2016-08-05 10:00:28','2016-08-05 10:00:28'),(10577,23,2692,1862,27,15855,0,'2016-08-11','13:00:00','15:00:00','00:00:00',NULL,NULL,'02:00:00','lead','approved',NULL,NULL,'2016-08-17 10:00:25',NULL,NULL,'2016-08-12 10:00:22','2016-08-12 10:00:22'),(10578,23,2693,1863,24,15730,0,'2016-08-10','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-08-17 10:00:25',NULL,NULL,'2016-08-12 10:00:22','2016-08-12 10:00:22'),(10579,23,2693,1863,24,15731,0,'2016-08-10','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-08-17 10:00:25',NULL,NULL,'2016-08-12 10:00:22','2016-08-12 10:00:22'),(10580,23,2693,1863,24,15733,0,'2016-08-11','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-08-17 10:00:25',NULL,NULL,'2016-08-12 10:00:22','2016-08-12 10:00:22'),(10581,23,2693,1863,24,15734,0,'2016-08-11','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-08-17 10:00:25',NULL,NULL,'2016-08-12 10:00:22','2016-08-12 10:00:22'),(10582,23,2693,1863,24,15736,0,'2016-08-12','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-08-17 10:00:25',NULL,NULL,'2016-08-12 10:00:22','2016-08-12 10:00:22'),(10583,23,2693,1863,24,15737,0,'2016-08-12','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-08-17 10:00:25',NULL,NULL,'2016-08-12 10:00:22','2016-08-12 10:00:22'),(10584,23,2694,1863,24,15736,0,'2016-08-12','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','observer','approved',NULL,NULL,'2016-08-17 10:00:25',NULL,NULL,'2016-08-12 10:00:23','2016-08-12 10:00:23'),(10585,23,2694,1862,27,15854,0,'2016-08-12','09:00:00','12:00:00','00:00:00',NULL,NULL,'03:00:00','lead','approved',NULL,NULL,'2016-08-17 10:00:25',NULL,NULL,'2016-08-12 10:00:23','2016-08-12 10:00:23'),(10950,23,2828,1862,27,15855,0,'2016-08-18','13:00:00','15:00:00','00:00:00',NULL,NULL,'02:00:00','lead','approved',NULL,NULL,'2016-08-24 10:00:22',NULL,NULL,'2016-08-19 10:00:23','2016-08-19 10:00:23'),(10951,23,2829,1863,24,15730,0,'2016-08-17','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-08-24 10:00:22',NULL,NULL,'2016-08-19 10:00:23','2016-08-19 10:00:23'),(10952,23,2829,1863,24,15731,0,'2016-08-17','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-08-24 10:00:22',NULL,NULL,'2016-08-19 10:00:23','2016-08-19 10:00:23'),(10953,23,2829,1863,24,15733,0,'2016-08-18','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-08-24 10:00:22',NULL,NULL,'2016-08-19 10:00:23','2016-08-19 10:00:23'),(10954,23,2829,1863,24,15734,0,'2016-08-18','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-08-24 10:00:22',NULL,NULL,'2016-08-19 10:00:23','2016-08-19 10:00:23'),(10955,23,2829,1863,24,15736,0,'2016-08-19','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-08-24 10:00:22',NULL,NULL,'2016-08-19 10:00:23','2016-08-19 10:00:23'),(10956,23,2829,1863,24,15737,0,'2016-08-19','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-08-24 10:00:22',NULL,NULL,'2016-08-19 10:00:23','2016-08-19 10:00:23'),(10957,23,2830,1863,24,15736,0,'2016-08-19','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','observer','approved',NULL,NULL,'2016-08-24 10:00:22',NULL,NULL,'2016-08-19 10:00:23','2016-08-19 10:00:23'),(10958,23,2830,1862,27,15854,0,'2016-08-19','09:00:00','12:00:00','00:00:00',NULL,NULL,'03:00:00','lead','approved',NULL,NULL,'2016-08-24 10:00:22',NULL,NULL,'2016-08-19 10:00:23','2016-08-19 10:00:23'),(11263,23,2951,1862,27,15855,0,'2016-08-25','13:00:00','15:00:00','00:00:00',NULL,NULL,'02:00:00','lead','approved',NULL,NULL,'2016-08-31 10:00:22',NULL,NULL,'2016-08-26 10:00:23','2016-08-26 10:00:23'),(11264,23,2952,1863,24,15730,0,'2016-08-24','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-08-31 10:00:22',NULL,NULL,'2016-08-26 10:00:23','2016-08-26 10:00:23'),(11265,23,2952,1863,24,15731,0,'2016-08-24','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-08-31 10:00:22',NULL,NULL,'2016-08-26 10:00:23','2016-08-26 10:00:23'),(11266,23,2952,1863,24,15733,0,'2016-08-25','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-08-31 10:00:22',NULL,NULL,'2016-08-26 10:00:23','2016-08-26 10:00:23'),(11267,23,2952,1863,24,15734,0,'2016-08-25','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-08-31 10:00:22',NULL,NULL,'2016-08-26 10:00:23','2016-08-26 10:00:23'),(11268,23,2952,1863,24,15736,0,'2016-08-26','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-08-31 10:00:22',NULL,NULL,'2016-08-26 10:00:23','2016-08-26 10:00:23'),(11269,23,2952,1863,24,15737,0,'2016-08-26','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-08-31 10:00:22',NULL,NULL,'2016-08-26 10:00:23','2016-08-26 10:00:23'),(11270,23,2953,1863,24,15736,0,'2016-08-26','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','observer','approved',NULL,NULL,'2016-08-31 10:00:22',NULL,NULL,'2016-08-26 10:00:23','2016-08-26 10:00:23'),(11271,23,2953,1862,27,15854,0,'2016-08-26','09:00:00','12:00:00','00:00:00',NULL,NULL,'03:00:00','lead','approved',NULL,NULL,'2016-08-31 10:00:22',NULL,NULL,'2016-08-26 10:00:23','2016-08-26 10:00:23'),(11569,23,3070,1863,24,15730,0,'2016-08-31','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-09-07 10:00:23',NULL,NULL,'2016-09-02 10:00:24','2016-09-02 10:00:24'),(11570,23,3070,1863,24,15731,0,'2016-08-31','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-09-07 10:00:23',NULL,NULL,'2016-09-02 10:00:24','2016-09-02 10:00:24'),(11571,23,3070,1863,24,15733,0,'2016-09-01','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-09-07 10:00:23',NULL,NULL,'2016-09-02 10:00:24','2016-09-02 10:00:24'),(11572,23,3070,1863,24,15734,0,'2016-09-01','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-09-07 10:00:23',NULL,NULL,'2016-09-02 10:00:24','2016-09-02 10:00:24'),(11573,23,3070,1863,24,15736,0,'2016-09-02','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-09-07 10:00:23',NULL,NULL,'2016-09-02 10:00:24','2016-09-02 10:00:24'),(11574,23,3070,1863,24,15737,0,'2016-09-02','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-09-07 10:00:23',NULL,NULL,'2016-09-02 10:00:24','2016-09-02 10:00:24'),(11575,23,3071,1863,24,15736,0,'2016-09-02','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','observer','approved',NULL,NULL,'2016-09-07 10:00:23',NULL,NULL,'2016-09-02 10:00:24','2016-09-02 10:00:24'),(11833,23,3175,1863,24,15730,0,'2016-09-07','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-09-14 10:00:22',NULL,NULL,'2016-09-09 10:00:22','2016-09-09 10:00:22'),(11834,23,3175,1863,24,15731,0,'2016-09-07','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-09-14 10:00:22',NULL,NULL,'2016-09-09 10:00:22','2016-09-09 10:00:22'),(11835,23,3175,1863,24,15733,0,'2016-09-08','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-09-14 10:00:22',NULL,NULL,'2016-09-09 10:00:22','2016-09-09 10:00:22'),(11836,23,3175,1863,24,15734,0,'2016-09-08','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-09-14 10:00:22',NULL,NULL,'2016-09-09 10:00:22','2016-09-09 10:00:22'),(11837,23,3175,1863,24,15736,0,'2016-09-09','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-09-14 10:00:22',NULL,NULL,'2016-09-09 10:00:22','2016-09-09 10:00:22'),(11838,23,3175,1863,24,15737,0,'2016-09-09','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-09-14 10:00:22',NULL,NULL,'2016-09-09 10:00:22','2016-09-09 10:00:22'),(11839,23,3176,1863,24,15736,0,'2016-09-09','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','observer','approved',NULL,NULL,'2016-09-14 10:00:23',NULL,NULL,'2016-09-09 10:00:22','2016-09-09 10:00:22'),(12118,23,3280,1863,24,15730,0,'2016-09-14','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-09-21 10:00:23',NULL,NULL,'2016-09-16 10:00:22','2016-09-16 10:00:22'),(12119,23,3280,1863,24,15731,0,'2016-09-14','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-09-21 10:00:23',NULL,NULL,'2016-09-16 10:00:22','2016-09-16 10:00:22'),(12120,23,3280,1863,24,15733,0,'2016-09-15','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-09-21 10:00:23',NULL,NULL,'2016-09-16 10:00:22','2016-09-16 10:00:22'),(12121,23,3280,1863,24,15734,0,'2016-09-15','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-09-21 10:00:23',NULL,NULL,'2016-09-16 10:00:22','2016-09-16 10:00:22'),(12122,23,3280,1863,24,15736,0,'2016-09-16','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-09-21 10:00:23',NULL,NULL,'2016-09-16 10:00:22','2016-09-16 10:00:22'),(12123,23,3280,1863,24,15737,0,'2016-09-16','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-09-21 10:00:23',NULL,NULL,'2016-09-16 10:00:22','2016-09-16 10:00:22'),(12124,23,3281,1863,24,15736,0,'2016-09-16','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','observer','approved',NULL,NULL,'2016-09-21 10:00:23',NULL,NULL,'2016-09-16 10:00:22','2016-09-16 10:00:22'),(12361,23,3381,1863,24,15730,0,'2016-09-21','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-09-28 10:00:22',NULL,NULL,'2016-09-23 10:00:21','2016-09-23 10:00:21'),(12362,23,3381,1863,24,15731,0,'2016-09-21','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-09-28 10:00:22',NULL,NULL,'2016-09-23 10:00:21','2016-09-23 10:00:21'),(12363,23,3381,1863,24,15733,0,'2016-09-22','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-09-28 10:00:22',NULL,NULL,'2016-09-23 10:00:21','2016-09-23 10:00:21'),(12364,23,3381,1863,24,15734,0,'2016-09-22','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-09-28 10:00:22',NULL,NULL,'2016-09-23 10:00:21','2016-09-23 10:00:21'),(12365,23,3381,1863,24,15736,0,'2016-09-23','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-09-28 10:00:22',NULL,NULL,'2016-09-23 10:00:21','2016-09-23 10:00:21'),(12366,23,3381,1863,24,15737,0,'2016-09-23','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-09-28 10:00:22',NULL,NULL,'2016-09-23 10:00:21','2016-09-23 10:00:21'),(12367,23,3382,1863,24,15736,0,'2016-09-23','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','observer','approved',NULL,NULL,'2016-09-28 10:00:22',NULL,NULL,'2016-09-23 10:00:21','2016-09-23 10:00:21'),(12636,23,3492,1863,24,15730,0,'2016-09-28','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-10-05 10:00:21',NULL,NULL,'2016-09-29 10:50:02','2016-09-29 10:50:02'),(12637,23,3492,1863,24,15731,0,'2016-09-28','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-10-05 10:00:21',NULL,NULL,'2016-09-29 10:50:02','2016-09-29 10:50:02'),(12638,23,3492,1863,24,15733,0,'2016-09-29','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-10-05 10:00:21',NULL,NULL,'2016-09-29 10:50:02','2016-09-29 10:50:02'),(12639,23,3492,1863,24,15734,0,'2016-09-29','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-10-05 10:00:21',NULL,NULL,'2016-09-29 10:50:02','2016-09-29 10:50:02'),(12640,23,3492,1863,24,15736,0,'2016-09-30','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-10-05 10:00:21',NULL,NULL,'2016-09-29 10:50:02','2016-09-29 10:50:02'),(12641,23,3492,1863,24,15737,0,'2016-09-30','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-10-05 10:00:21',NULL,NULL,'2016-09-29 10:50:02','2016-09-29 10:50:02'),(12642,23,3493,1863,24,15736,0,'2016-09-30','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','observer','approved',NULL,NULL,'2016-10-05 10:00:21',NULL,NULL,'2016-09-29 10:50:02','2016-09-29 10:50:02'),(12909,23,3603,1863,24,15730,0,'2016-10-05','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-10-12 10:00:22',NULL,NULL,'2016-10-07 10:00:22','2016-10-07 10:00:22'),(12910,23,3603,1863,24,15731,0,'2016-10-05','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-10-12 10:00:22',NULL,NULL,'2016-10-07 10:00:22','2016-10-07 10:00:22'),(12911,23,3603,1863,24,15733,0,'2016-10-06','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-10-12 10:00:22',NULL,NULL,'2016-10-07 10:00:22','2016-10-07 10:00:22'),(12912,23,3603,1863,24,15734,0,'2016-10-06','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-10-12 10:00:22',NULL,NULL,'2016-10-07 10:00:22','2016-10-07 10:00:22'),(12913,23,3603,1863,24,15736,0,'2016-10-07','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-10-12 10:00:22',NULL,NULL,'2016-10-07 10:00:22','2016-10-07 10:00:22'),(12914,23,3603,1863,24,15737,0,'2016-10-07','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-10-12 10:00:22',NULL,NULL,'2016-10-07 10:00:22','2016-10-07 10:00:22'),(12915,23,3604,1863,24,15736,0,'2016-10-07','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','observer','approved',NULL,NULL,'2016-10-12 10:00:22',NULL,NULL,'2016-10-07 10:00:22','2016-10-07 10:00:22'),(13208,23,3714,1863,24,15730,0,'2016-10-12','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-10-19 10:00:22',NULL,NULL,'2016-10-14 10:00:22','2016-10-14 10:00:22'),(13209,23,3714,1863,24,15731,0,'2016-10-12','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-10-19 10:00:22',NULL,NULL,'2016-10-14 10:00:22','2016-10-14 10:00:22'),(13210,23,3714,1863,24,15733,0,'2016-10-13','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-10-19 10:00:22',NULL,NULL,'2016-10-14 10:00:22','2016-10-14 10:00:22'),(13211,23,3714,1863,24,15734,0,'2016-10-13','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-10-19 10:00:22',NULL,NULL,'2016-10-14 10:00:22','2016-10-14 10:00:22'),(13212,23,3714,1863,24,15736,0,'2016-10-14','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-10-19 10:00:22',NULL,NULL,'2016-10-14 10:00:22','2016-10-14 10:00:22'),(13213,23,3714,1863,24,15737,0,'2016-10-14','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-10-19 10:00:22',NULL,NULL,'2016-10-14 10:00:22','2016-10-14 10:00:22'),(13214,23,3715,1863,24,15736,0,'2016-10-14','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','observer','approved',NULL,NULL,'2016-10-19 10:00:22',NULL,NULL,'2016-10-14 10:00:22','2016-10-14 10:00:22'),(13509,23,3829,1863,24,15730,0,'2016-10-19','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-10-26 10:00:44',NULL,NULL,'2016-10-21 09:45:23','2016-10-21 09:45:23'),(13510,23,3829,1863,24,15731,0,'2016-10-19','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-10-26 10:00:44',NULL,NULL,'2016-10-21 09:45:23','2016-10-21 09:45:23'),(13511,23,3829,1863,24,15733,0,'2016-10-20','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-10-26 10:00:44',NULL,NULL,'2016-10-21 09:45:23','2016-10-21 09:45:23'),(13512,23,3829,1863,24,15734,0,'2016-10-20','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-10-26 10:00:44',NULL,NULL,'2016-10-21 09:45:23','2016-10-21 09:45:23'),(13513,23,3829,1863,24,15736,0,'2016-10-21','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-10-26 10:00:44',NULL,NULL,'2016-10-21 09:45:23','2016-10-21 09:45:23'),(13514,23,3829,1863,24,15737,0,'2016-10-21','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-10-26 10:00:44',NULL,NULL,'2016-10-21 09:45:23','2016-10-21 09:45:23'),(13515,23,3830,1863,24,15736,0,'2016-10-21','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','observer','approved',NULL,NULL,'2016-10-26 10:00:44',NULL,NULL,'2016-10-21 09:45:23','2016-10-21 09:45:23'),(13803,23,3947,1863,24,15730,0,'2016-10-26','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-11-02 09:00:21',NULL,NULL,'2016-10-28 10:00:21','2016-10-28 10:00:21'),(13804,23,3947,1863,24,15731,0,'2016-10-26','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-11-02 09:00:21',NULL,NULL,'2016-10-28 10:00:21','2016-10-28 10:00:21'),(13805,23,3947,1863,24,15733,0,'2016-10-27','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-11-02 09:00:21',NULL,NULL,'2016-10-28 10:00:21','2016-10-28 10:00:21'),(13806,23,3947,1863,24,15734,0,'2016-10-27','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-11-02 09:00:21',NULL,NULL,'2016-10-28 10:00:21','2016-10-28 10:00:21'),(13807,23,3947,1863,24,15736,0,'2016-10-28','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-11-02 09:00:21',NULL,NULL,'2016-10-28 10:00:21','2016-10-28 10:00:21'),(13808,23,3947,1863,24,15737,0,'2016-10-28','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-11-02 09:00:21',NULL,NULL,'2016-10-28 10:00:21','2016-10-28 10:00:21'),(13809,23,3948,1863,24,15736,0,'2016-10-28','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','observer','approved',NULL,NULL,'2016-11-02 09:00:22',NULL,NULL,'2016-10-28 10:00:21','2016-10-28 10:00:21'),(14083,23,4064,1863,24,15730,0,'2016-11-02','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-11-09 09:00:22',NULL,NULL,'2016-11-02 10:30:30','2016-11-02 10:30:30'),(14084,23,4064,1863,24,15731,0,'2016-11-02','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-11-09 09:00:22',NULL,NULL,'2016-11-02 10:30:30','2016-11-02 10:30:30'),(14085,23,4064,1863,24,15733,0,'2016-11-03','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-11-09 09:00:22',NULL,NULL,'2016-11-02 10:30:30','2016-11-02 10:30:30'),(14086,23,4064,1863,24,15734,0,'2016-11-03','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-11-09 09:00:22',NULL,NULL,'2016-11-02 10:30:30','2016-11-02 10:30:30'),(14087,23,4064,1863,24,15736,0,'2016-11-04','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-11-09 09:00:22',NULL,NULL,'2016-11-02 10:30:30','2016-11-02 10:30:30'),(14088,23,4064,1863,24,15737,0,'2016-11-04','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-11-09 09:00:22',NULL,NULL,'2016-11-02 10:30:30','2016-11-02 10:30:30'),(14089,23,4065,1863,24,15736,0,'2016-11-04','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','observer','approved',NULL,NULL,'2016-11-09 09:00:22',NULL,NULL,'2016-11-02 10:30:30','2016-11-02 10:30:30'),(14414,23,4194,1863,24,15730,0,'2016-11-09','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-11-16 09:00:22',NULL,NULL,'2016-11-07 09:05:47','2016-11-07 09:05:47'),(14415,23,4194,1863,24,15731,0,'2016-11-09','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-11-16 09:00:22',NULL,NULL,'2016-11-07 09:05:47','2016-11-07 09:05:47'),(14416,23,4194,1863,24,15733,0,'2016-11-10','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-11-16 09:00:22',NULL,NULL,'2016-11-07 09:05:47','2016-11-07 09:05:47'),(14417,23,4194,1863,24,15734,0,'2016-11-10','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-11-16 09:00:22',NULL,NULL,'2016-11-07 09:05:47','2016-11-07 09:05:47'),(14418,23,4194,1863,24,15736,0,'2016-11-11','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-11-16 09:00:22',NULL,NULL,'2016-11-07 09:05:47','2016-11-07 09:05:47'),(14419,23,4194,1863,24,15737,0,'2016-11-11','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-11-16 09:00:22',NULL,NULL,'2016-11-07 09:05:47','2016-11-07 09:05:47'),(14420,23,4195,1863,24,15736,0,'2016-11-11','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','observer','approved',NULL,NULL,'2016-11-16 09:00:22',NULL,NULL,'2016-11-07 09:05:47','2016-11-07 09:05:47'),(14779,23,4335,1862,24,15668,0,'2016-11-15','13:15:00','14:20:00','00:00:00',NULL,NULL,'01:05:00','lead','approved',NULL,NULL,'2016-11-23 09:00:22',NULL,NULL,'2016-11-18 09:00:25','2016-11-18 09:00:25'),(14780,23,4337,1863,24,15730,0,'2016-11-16','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-11-23 09:00:22',NULL,NULL,'2016-11-18 09:00:25','2016-11-18 09:00:25'),(14781,23,4337,1863,24,15731,0,'2016-11-16','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-11-23 09:00:22',NULL,NULL,'2016-11-18 09:00:25','2016-11-18 09:00:25'),(14782,23,4337,1863,24,15733,0,'2016-11-17','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-11-23 09:00:22',NULL,NULL,'2016-11-18 09:00:25','2016-11-18 09:00:25'),(14783,23,4337,1863,24,15734,0,'2016-11-17','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-11-23 09:00:22',NULL,NULL,'2016-11-18 09:00:25','2016-11-18 09:00:25'),(14784,23,4337,1863,24,15736,0,'2016-11-18','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-11-23 09:00:22',NULL,NULL,'2016-11-18 09:00:25','2016-11-18 09:00:25'),(14785,23,4337,1863,24,15737,0,'2016-11-18','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-11-23 09:00:22',NULL,NULL,'2016-11-18 09:00:25','2016-11-18 09:00:25'),(14786,23,4338,1863,24,15736,0,'2016-11-18','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','observer','approved',NULL,NULL,'2016-11-23 09:00:22',NULL,NULL,'2016-11-18 09:00:25','2016-11-18 09:00:25'),(15155,23,4482,1862,24,15668,0,'2016-11-22','13:15:00','14:20:00','00:00:00',NULL,NULL,'01:05:00','lead','approved',NULL,NULL,'2016-11-30 09:00:22',NULL,NULL,'2016-11-24 14:11:49','2016-11-24 14:11:49'),(15156,23,4484,1863,24,15730,0,'2016-11-23','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-11-30 09:00:22',NULL,NULL,'2016-11-24 14:11:49','2016-11-24 14:11:49'),(15157,23,4484,1863,24,15731,0,'2016-11-23','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-11-30 09:00:22',NULL,NULL,'2016-11-24 14:11:49','2016-11-24 14:11:49'),(15158,23,4484,1863,24,15733,0,'2016-11-24','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-11-30 09:00:22',NULL,NULL,'2016-11-24 14:11:49','2016-11-24 14:11:49'),(15159,23,4484,1863,24,15734,0,'2016-11-24','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-11-30 09:00:22',NULL,NULL,'2016-11-24 14:11:49','2016-11-24 14:11:49'),(15160,23,4484,1863,24,15736,0,'2016-11-25','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-11-30 09:00:22',NULL,NULL,'2016-11-24 14:11:49','2016-11-24 14:11:49'),(15161,23,4484,1863,24,15737,0,'2016-11-25','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-11-30 09:00:22',NULL,NULL,'2016-11-24 14:11:49','2016-11-24 14:11:49'),(15162,23,4485,1863,24,15736,0,'2016-11-25','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','observer','approved',NULL,NULL,'2016-11-30 09:00:22',NULL,NULL,'2016-11-24 14:11:49','2016-11-24 14:11:49'),(15534,23,4633,1862,24,15668,0,'2016-11-29','13:15:00','14:20:00','00:00:00',NULL,NULL,'01:05:00','lead','approved',NULL,NULL,'2016-12-07 09:00:24',NULL,NULL,'2016-11-28 12:41:57','2016-11-28 12:41:57'),(15535,23,4635,1863,24,15730,0,'2016-11-30','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-12-07 09:00:24',NULL,NULL,'2016-11-28 12:41:57','2016-11-28 12:41:57'),(15536,23,4635,1863,24,15731,0,'2016-11-30','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-12-07 09:00:24',NULL,NULL,'2016-11-28 12:41:57','2016-11-28 12:41:57'),(15537,23,4635,1863,24,15733,0,'2016-12-01','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-12-07 09:00:24',NULL,NULL,'2016-11-28 12:41:57','2016-11-28 12:41:57'),(15538,23,4635,1863,24,15734,0,'2016-12-01','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-12-07 09:00:24',NULL,NULL,'2016-11-28 12:41:57','2016-11-28 12:41:57'),(15539,23,4635,1863,24,15736,0,'2016-12-02','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-12-07 09:00:24',NULL,NULL,'2016-11-28 12:41:57','2016-11-28 12:41:57'),(15540,23,4635,1863,24,15737,0,'2016-12-02','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-12-07 09:00:24',NULL,NULL,'2016-11-28 12:41:57','2016-11-28 12:41:57'),(15541,23,4636,1863,24,15736,0,'2016-12-02','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','observer','approved',NULL,NULL,'2016-12-07 09:00:24',NULL,NULL,'2016-11-28 12:41:57','2016-11-28 12:41:57'),(15899,23,4782,1862,24,15668,0,'2016-12-06','13:15:00','14:20:00','00:00:00',NULL,NULL,'01:05:00','lead','approved',NULL,NULL,'2016-12-14 09:00:23',NULL,NULL,'2016-12-06 11:32:27','2016-12-06 11:32:27'),(15900,23,4784,1863,24,15730,0,'2016-12-07','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-12-14 09:00:23',NULL,NULL,'2016-12-06 11:32:27','2016-12-06 11:32:27'),(15901,23,4784,1863,24,15731,0,'2016-12-07','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-12-14 09:00:23',NULL,NULL,'2016-12-06 11:32:27','2016-12-06 11:32:27'),(15902,23,4784,1863,24,15733,0,'2016-12-08','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-12-14 09:00:23',NULL,NULL,'2016-12-06 11:32:27','2016-12-06 11:32:27'),(15903,23,4784,1863,24,15734,0,'2016-12-08','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-12-14 09:00:23',NULL,NULL,'2016-12-06 11:32:27','2016-12-06 11:32:27'),(15904,23,4784,1863,24,15736,0,'2016-12-09','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-12-14 09:00:23',NULL,NULL,'2016-12-06 11:32:27','2016-12-06 11:32:27'),(15905,23,4784,1863,24,15737,0,'2016-12-09','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-12-14 09:00:23',NULL,NULL,'2016-12-06 11:32:27','2016-12-06 11:32:27'),(15906,23,4785,1863,24,15736,0,'2016-12-09','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','observer','approved',NULL,NULL,'2016-12-14 09:00:23',NULL,NULL,'2016-12-06 11:32:27','2016-12-06 11:32:27'),(16233,23,4918,1862,24,15668,0,'2016-12-13','13:15:00','14:20:00','00:00:00',NULL,NULL,'01:05:00','lead','approved',NULL,NULL,'2016-12-21 09:00:22',NULL,NULL,'2016-12-14 13:00:59','2016-12-14 13:00:59'),(16234,23,4920,1863,24,15730,0,'2016-12-14','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-12-21 09:00:22',NULL,NULL,'2016-12-14 13:00:59','2016-12-14 13:00:59'),(16235,23,4920,1863,24,15731,0,'2016-12-14','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-12-21 09:00:22',NULL,NULL,'2016-12-14 13:00:59','2016-12-14 13:00:59'),(16236,23,4920,1863,24,15733,0,'2016-12-15','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-12-21 09:00:22',NULL,NULL,'2016-12-14 13:00:59','2016-12-14 13:00:59'),(16237,23,4920,1863,24,15734,0,'2016-12-15','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-12-21 09:00:22',NULL,NULL,'2016-12-14 13:00:59','2016-12-14 13:00:59'),(16238,23,4920,1863,24,15736,0,'2016-12-16','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-12-21 09:00:22',NULL,NULL,'2016-12-14 13:00:59','2016-12-14 13:00:59'),(16239,23,4920,1863,24,15737,0,'2016-12-16','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-12-21 09:00:22',NULL,NULL,'2016-12-14 13:00:59','2016-12-14 13:00:59'),(16240,23,4921,1863,24,15736,0,'2016-12-16','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','observer','approved',NULL,NULL,'2016-12-21 09:00:22',NULL,NULL,'2016-12-14 13:00:59','2016-12-14 13:00:59'),(16602,23,5070,1863,24,15730,0,'2016-12-21','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-12-28 09:00:23',NULL,NULL,'2016-12-23 09:00:23','2016-12-23 09:00:23'),(16603,23,5070,1863,24,15731,0,'2016-12-21','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-12-28 09:00:23',NULL,NULL,'2016-12-23 09:00:23','2016-12-23 09:00:23'),(16604,23,5070,1863,24,15733,0,'2016-12-22','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-12-28 09:00:23',NULL,NULL,'2016-12-23 09:00:23','2016-12-23 09:00:23'),(16605,23,5070,1863,24,15734,0,'2016-12-22','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-12-28 09:00:23',NULL,NULL,'2016-12-23 09:00:23','2016-12-23 09:00:23'),(16606,23,5070,1863,24,15736,0,'2016-12-23','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','head','approved',NULL,NULL,'2016-12-28 09:00:23',NULL,NULL,'2016-12-23 09:00:23','2016-12-23 09:00:23'),(16607,23,5070,1863,24,15737,0,'2016-12-23','14:15:00','15:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2016-12-28 09:00:23',NULL,NULL,'2016-12-23 09:00:23','2016-12-23 09:00:23'),(16608,23,5071,1863,24,15736,0,'2016-12-23','13:15:00','14:15:00','00:00:00',NULL,NULL,'01:00:00','observer','approved',NULL,NULL,'2016-12-28 09:00:23',NULL,NULL,'2016-12-23 09:00:23','2016-12-23 09:00:23'),(18583,23,6046,1864,26,15780,0,'2017-02-13','08:00:00','10:30:00','00:00:00',NULL,NULL,'02:30:00','head','approved',NULL,NULL,'2017-02-22 09:00:22',NULL,NULL,'2017-02-17 09:00:23','2017-02-17 09:00:23'),(18584,23,6046,1864,26,15781,0,'2017-02-13','10:30:00','15:30:00','00:00:00',NULL,NULL,'05:00:00','head','approved',NULL,NULL,'2017-02-22 09:00:22',NULL,NULL,'2017-02-17 09:00:23','2017-02-17 09:00:23'),(18585,23,6046,1864,26,15782,0,'2017-02-14','08:00:00','10:30:00','00:00:00',NULL,NULL,'02:30:00','head','approved',NULL,NULL,'2017-02-22 09:00:22',NULL,NULL,'2017-02-17 09:00:23','2017-02-17 09:00:23'),(18586,23,6046,1864,26,15783,0,'2017-02-14','10:30:00','15:30:00','00:00:00',NULL,NULL,'05:00:00','head','approved',NULL,NULL,'2017-02-22 09:00:22',NULL,NULL,'2017-02-17 09:00:23','2017-02-17 09:00:23'),(18587,23,6046,1864,26,15784,0,'2017-02-15','08:00:00','10:30:00','00:00:00',NULL,NULL,'02:30:00','head','approved',NULL,NULL,'2017-02-22 09:00:22',NULL,NULL,'2017-02-17 09:00:23','2017-02-17 09:00:23'),(18588,23,6046,1864,26,15785,0,'2017-02-15','10:30:00','15:30:00','00:00:00',NULL,NULL,'05:00:00','head','approved',NULL,NULL,'2017-02-22 09:00:22',NULL,NULL,'2017-02-17 09:00:23','2017-02-17 09:00:23'),(18589,23,6046,1864,26,15786,0,'2017-02-16','08:00:00','10:30:00','00:00:00',NULL,NULL,'02:30:00','head','approved',NULL,NULL,'2017-02-22 09:00:22',NULL,NULL,'2017-02-17 09:00:23','2017-02-17 09:00:23'),(18590,23,6046,1864,26,15787,0,'2017-02-16','10:30:00','15:30:00','00:00:00',NULL,NULL,'05:00:00','head','approved',NULL,NULL,'2017-02-22 09:00:22',NULL,NULL,'2017-02-17 09:00:23','2017-02-17 09:00:23'),(18591,23,6046,1864,26,15788,0,'2017-02-17','08:00:00','10:30:00','00:00:00',NULL,NULL,'02:30:00','head','approved',NULL,NULL,'2017-02-22 09:00:22',NULL,NULL,'2017-02-17 09:00:23','2017-02-17 09:00:23'),(18592,23,6046,1864,26,15789,0,'2017-02-17','10:30:00','15:30:00','00:00:00',NULL,NULL,'05:00:00','head','approved',NULL,NULL,'2017-02-22 09:00:22',NULL,NULL,'2017-02-17 09:00:23','2017-02-17 09:00:23'),(18593,23,6048,1863,26,15810,0,'2017-02-13','08:00:00','10:30:00','00:00:00',NULL,NULL,'02:30:00','head','approved',NULL,NULL,'2017-02-22 09:00:22',NULL,NULL,'2017-02-17 09:00:23','2017-02-17 09:00:23'),(18594,23,6048,1863,26,15811,0,'2017-02-14','10:30:00','15:30:00','00:00:00',NULL,NULL,'05:00:00','head','approved',NULL,NULL,'2017-02-22 09:00:22',NULL,NULL,'2017-02-17 09:00:23','2017-02-17 09:00:23'),(18595,23,6048,1863,26,15812,0,'2017-02-14','08:00:00','10:30:00','00:00:00',NULL,NULL,'02:30:00','head','approved',NULL,NULL,'2017-02-22 09:00:22',NULL,NULL,'2017-02-17 09:00:23','2017-02-17 09:00:23'),(18596,23,6048,1863,26,15813,0,'2017-02-15','10:30:00','15:30:00','00:00:00',NULL,NULL,'05:00:00','head','approved',NULL,NULL,'2017-02-22 09:00:22',NULL,NULL,'2017-02-17 09:00:23','2017-02-17 09:00:23'),(18597,23,6048,1863,26,15814,0,'2017-02-15','08:00:00','10:30:00','00:00:00',NULL,NULL,'02:30:00','head','approved',NULL,NULL,'2017-02-22 09:00:22',NULL,NULL,'2017-02-17 09:00:23','2017-02-17 09:00:23'),(18598,23,6048,1863,26,15815,0,'2017-02-13','10:30:00','15:30:00','00:00:00',NULL,NULL,'05:00:00','head','approved',NULL,NULL,'2017-02-22 09:00:22',NULL,NULL,'2017-02-17 09:00:23','2017-02-17 09:00:23'),(18599,23,6048,1863,26,15816,0,'2017-02-17','08:00:00','10:30:00','00:00:00',NULL,NULL,'02:30:00','head','approved',NULL,NULL,'2017-02-22 09:00:22',NULL,NULL,'2017-02-17 09:00:23','2017-02-17 09:00:23'),(18600,23,6048,1863,26,15817,0,'2017-02-17','10:30:00','15:30:00','00:00:00',NULL,NULL,'05:00:00','head','approved',NULL,NULL,'2017-02-22 09:00:22',NULL,NULL,'2017-02-17 09:00:23','2017-02-17 09:00:23'),(23236,23,7093,1864,26,15790,0,'2017-03-27','08:00:00','10:30:00','00:00:00',NULL,NULL,'02:30:00','head','approved',NULL,NULL,'2017-04-05 10:00:22',NULL,NULL,'2017-03-31 10:00:23','2017-03-31 10:00:23'),(23237,23,7093,1864,26,15791,0,'2017-03-27','10:30:00','15:30:00','00:00:00',NULL,NULL,'05:00:00','head','approved',NULL,NULL,'2017-04-05 10:00:22',NULL,NULL,'2017-03-31 10:00:23','2017-03-31 10:00:23'),(23238,23,7093,1864,26,15792,0,'2017-03-28','08:00:00','10:30:00','00:00:00',NULL,NULL,'02:30:00','head','approved',NULL,NULL,'2017-04-05 10:00:22',NULL,NULL,'2017-03-31 10:00:23','2017-03-31 10:00:23'),(23239,23,7093,1864,26,15793,0,'2017-03-28','10:30:00','15:30:00','00:00:00',NULL,NULL,'05:00:00','head','approved',NULL,NULL,'2017-04-05 10:00:22',NULL,NULL,'2017-03-31 10:00:23','2017-03-31 10:00:23'),(23240,23,7093,1864,26,15794,0,'2017-03-29','08:00:00','10:30:00','00:00:00',NULL,NULL,'02:30:00','head','approved',NULL,NULL,'2017-04-05 10:00:22',NULL,NULL,'2017-03-31 10:00:23','2017-03-31 10:00:23'),(23241,23,7093,1864,26,15795,0,'2017-03-29','10:30:00','15:30:00','00:00:00',NULL,NULL,'05:00:00','head','approved',NULL,NULL,'2017-04-05 10:00:22',NULL,NULL,'2017-03-31 10:00:23','2017-03-31 10:00:23'),(23242,23,7093,1864,26,15796,0,'2017-03-30','08:00:00','10:30:00','00:00:00',NULL,NULL,'02:30:00','head','approved',NULL,NULL,'2017-04-05 10:00:22',NULL,NULL,'2017-03-31 10:00:23','2017-03-31 10:00:23'),(23243,23,7093,1864,26,15797,0,'2017-03-30','10:30:00','15:30:00','00:00:00',NULL,NULL,'05:00:00','head','approved',NULL,NULL,'2017-04-05 10:00:22',NULL,NULL,'2017-03-31 10:00:23','2017-03-31 10:00:23'),(23244,23,7093,1864,26,15798,0,'2017-03-31','08:00:00','10:30:00','00:00:00',NULL,NULL,'02:30:00','head','approved',NULL,NULL,'2017-04-05 10:00:22',NULL,NULL,'2017-03-31 10:00:23','2017-03-31 10:00:23'),(23245,23,7093,1864,26,15799,0,'2017-03-31','10:30:00','15:30:00','00:00:00',NULL,NULL,'05:00:00','head','approved',NULL,NULL,'2017-04-05 10:00:22',NULL,NULL,'2017-03-31 10:00:23','2017-03-31 10:00:23'),(23246,23,7095,1864,26,15790,0,'2017-03-27','08:00:00','10:30:00','00:00:00',NULL,NULL,'02:30:00','assistant','approved',NULL,NULL,'2017-04-05 10:00:22',NULL,NULL,'2017-03-31 10:00:23','2017-03-31 10:00:23'),(23247,23,7095,1864,26,15791,0,'2017-03-27','10:30:00','15:30:00','00:00:00',NULL,NULL,'05:00:00','assistant','approved',NULL,NULL,'2017-04-05 10:00:22',NULL,NULL,'2017-03-31 10:00:23','2017-03-31 10:00:23'),(23248,23,7095,1864,26,15792,0,'2017-03-28','08:00:00','10:30:00','00:00:00',NULL,NULL,'02:30:00','assistant','approved',NULL,NULL,'2017-04-05 10:00:22',NULL,NULL,'2017-03-31 10:00:23','2017-03-31 10:00:23'),(23249,23,7095,1864,26,15793,0,'2017-03-28','10:30:00','15:30:00','00:00:00',NULL,NULL,'05:00:00','assistant','approved',NULL,NULL,'2017-04-05 10:00:22',NULL,NULL,'2017-03-31 10:00:23','2017-03-31 10:00:23'),(23250,23,7095,1864,26,15794,0,'2017-03-29','08:00:00','10:30:00','00:00:00',NULL,NULL,'02:30:00','assistant','approved',NULL,NULL,'2017-04-05 10:00:22',NULL,NULL,'2017-03-31 10:00:23','2017-03-31 10:00:23'),(23251,23,7095,1864,26,15795,0,'2017-03-29','10:30:00','15:30:00','00:00:00',NULL,NULL,'05:00:00','assistant','approved',NULL,NULL,'2017-04-05 10:00:22',NULL,NULL,'2017-03-31 10:00:23','2017-03-31 10:00:23'),(23252,23,7095,1864,26,15796,0,'2017-03-30','08:00:00','10:30:00','00:00:00',NULL,NULL,'02:30:00','assistant','approved',NULL,NULL,'2017-04-05 10:00:22',NULL,NULL,'2017-03-31 10:00:23','2017-03-31 10:00:23'),(23253,23,7095,1864,26,15797,0,'2017-03-30','10:30:00','15:30:00','00:00:00',NULL,NULL,'05:00:00','assistant','approved',NULL,NULL,'2017-04-05 10:00:22',NULL,NULL,'2017-03-31 10:00:23','2017-03-31 10:00:23'),(23254,23,7095,1864,26,15798,0,'2017-03-31','08:00:00','10:30:00','00:00:00',NULL,NULL,'02:30:00','assistant','approved',NULL,NULL,'2017-04-05 10:00:22',NULL,NULL,'2017-03-31 10:00:23','2017-03-31 10:00:23'),(23255,23,7095,1864,26,15799,0,'2017-03-31','10:30:00','15:30:00','00:00:00',NULL,NULL,'05:00:00','assistant','approved',NULL,NULL,'2017-04-05 10:00:22',NULL,NULL,'2017-03-31 10:00:23','2017-03-31 10:00:23'),(25337,23,7481,1863,26,15818,0,'2017-04-10','08:00:00','10:30:00','00:00:00',NULL,NULL,'02:30:00','head','approved',NULL,NULL,'2017-04-19 10:00:21',NULL,NULL,'2017-04-14 10:00:32','2017-04-14 10:00:32'),(25338,23,7481,1863,26,15819,0,'2017-04-11','10:30:00','15:30:00','00:00:00',NULL,NULL,'05:00:00','head','approved',NULL,NULL,'2017-04-19 10:00:21',NULL,NULL,'2017-04-14 10:00:32','2017-04-14 10:00:32'),(25339,23,7481,1863,26,15820,0,'2017-04-11','08:00:00','10:30:00','00:00:00',NULL,NULL,'02:30:00','head','approved',NULL,NULL,'2017-04-19 10:00:21',NULL,NULL,'2017-04-14 10:00:32','2017-04-14 10:00:32'),(25340,23,7481,1863,26,15821,0,'2017-04-12','10:30:00','15:30:00','00:00:00',NULL,NULL,'05:00:00','head','approved',NULL,NULL,'2017-04-19 10:00:21',NULL,NULL,'2017-04-14 10:00:32','2017-04-14 10:00:32'),(25341,23,7481,1863,26,15822,0,'2017-04-12','08:00:00','10:30:00','00:00:00',NULL,NULL,'02:30:00','head','approved',NULL,NULL,'2017-04-19 10:00:21',NULL,NULL,'2017-04-14 10:00:32','2017-04-14 10:00:32'),(25342,23,7481,1863,26,15823,0,'2017-04-10','10:30:00','15:30:00','00:00:00',NULL,NULL,'05:00:00','head','approved',NULL,NULL,'2017-04-19 10:00:21',NULL,NULL,'2017-04-14 10:00:32','2017-04-14 10:00:32'),(25343,23,7481,1863,26,15824,0,'2017-04-14','08:00:00','10:30:00','00:00:00',NULL,NULL,'02:30:00','head','approved',NULL,NULL,'2017-04-19 10:00:21',NULL,NULL,'2017-04-14 10:00:32','2017-04-14 10:00:32'),(25344,23,7481,1863,26,15825,0,'2017-04-14','10:30:00','15:30:00','00:00:00',NULL,NULL,'05:00:00','head','approved',NULL,NULL,'2017-04-19 10:00:21',NULL,NULL,'2017-04-14 10:00:32','2017-04-14 10:00:32'),(25345,23,7482,1864,26,15800,0,'2017-04-10','08:00:00','10:30:00','00:00:00',NULL,NULL,'02:30:00','head','approved',NULL,NULL,'2017-04-19 10:00:21',NULL,NULL,'2017-04-14 10:00:32','2017-04-14 10:00:32'),(25346,23,7482,1864,26,15801,0,'2017-04-10','10:30:00','15:30:00','00:00:00',NULL,NULL,'05:00:00','head','approved',NULL,NULL,'2017-04-19 10:00:21',NULL,NULL,'2017-04-14 10:00:32','2017-04-14 10:00:32'),(25347,23,7482,1864,26,15802,0,'2017-04-11','08:00:00','10:30:00','00:00:00',NULL,NULL,'02:30:00','head','approved',NULL,NULL,'2017-04-19 10:00:21',NULL,NULL,'2017-04-14 10:00:32','2017-04-14 10:00:32'),(25348,23,7482,1864,26,15803,0,'2017-04-11','10:30:00','15:30:00','00:00:00',NULL,NULL,'05:00:00','head','approved',NULL,NULL,'2017-04-19 10:00:21',NULL,NULL,'2017-04-14 10:00:32','2017-04-14 10:00:32'),(25349,23,7482,1864,26,15804,0,'2017-04-12','08:00:00','10:30:00','00:00:00',NULL,NULL,'02:30:00','head','approved',NULL,NULL,'2017-04-19 10:00:21',NULL,NULL,'2017-04-14 10:00:32','2017-04-14 10:00:32'),(25350,23,7482,1864,26,15805,0,'2017-04-12','10:30:00','15:30:00','00:00:00',NULL,NULL,'05:00:00','head','approved',NULL,NULL,'2017-04-19 10:00:21',NULL,NULL,'2017-04-14 10:00:32','2017-04-14 10:00:32'),(25351,23,7482,1864,26,15806,0,'2017-04-13','08:00:00','10:30:00','00:00:00',NULL,NULL,'02:30:00','head','approved',NULL,NULL,'2017-04-19 10:00:21',NULL,NULL,'2017-04-14 10:00:32','2017-04-14 10:00:32'),(25352,23,7482,1864,26,15807,0,'2017-04-13','10:30:00','15:30:00','00:00:00',NULL,NULL,'05:00:00','head','approved',NULL,NULL,'2017-04-19 10:00:21',NULL,NULL,'2017-04-14 10:00:32','2017-04-14 10:00:32'),(25353,23,7482,1864,26,15808,0,'2017-04-14','08:00:00','10:30:00','00:00:00',NULL,NULL,'02:30:00','head','approved',NULL,NULL,'2017-04-19 10:00:21',NULL,NULL,'2017-04-14 10:00:32','2017-04-14 10:00:32'),(25354,23,7482,1864,26,15809,0,'2017-04-14','10:30:00','15:30:00','00:00:00',NULL,NULL,'05:00:00','head','approved',NULL,NULL,'2017-04-19 10:00:21',NULL,NULL,'2017-04-14 10:00:32','2017-04-14 10:00:32'),(25903,23,7665,1863,26,15826,0,'2017-04-17','08:00:00','10:30:00','00:00:00',NULL,NULL,'02:30:00','head','approved',NULL,NULL,'2017-04-26 10:00:22',NULL,NULL,'2017-04-21 10:00:22','2017-04-21 10:00:22'),(25904,23,7665,1863,26,15827,0,'2017-04-18','10:30:00','15:30:00','00:00:00',NULL,NULL,'05:00:00','head','approved',NULL,NULL,'2017-04-26 10:00:22',NULL,NULL,'2017-04-21 10:00:22','2017-04-21 10:00:22'),(25905,23,7665,1863,26,15828,0,'2017-04-18','08:00:00','10:30:00','00:00:00',NULL,NULL,'02:30:00','head','approved',NULL,NULL,'2017-04-26 10:00:22',NULL,NULL,'2017-04-21 10:00:22','2017-04-21 10:00:22'),(25906,23,7665,1863,26,15829,0,'2017-04-19','10:30:00','15:30:00','00:00:00',NULL,NULL,'05:00:00','head','approved',NULL,NULL,'2017-04-26 10:00:22',NULL,NULL,'2017-04-21 10:00:22','2017-04-21 10:00:22'),(25907,23,7665,1863,26,15830,0,'2017-04-19','08:00:00','10:30:00','00:00:00',NULL,NULL,'02:30:00','head','approved',NULL,NULL,'2017-04-26 10:00:22',NULL,NULL,'2017-04-21 10:00:22','2017-04-21 10:00:22'),(25908,23,7665,1863,26,15831,0,'2017-04-17','10:30:00','15:30:00','00:00:00',NULL,NULL,'05:00:00','head','approved',NULL,NULL,'2017-04-26 10:00:22',NULL,NULL,'2017-04-21 10:00:22','2017-04-21 10:00:22'),(25909,23,7665,1863,26,15832,0,'2017-04-21','08:00:00','10:30:00','00:00:00',NULL,NULL,'02:30:00','head','approved',NULL,NULL,'2017-04-26 10:00:22',NULL,NULL,'2017-04-21 10:00:22','2017-04-21 10:00:22'),(25910,23,7665,1863,26,15833,0,'2017-04-21','10:30:00','15:30:00','00:00:00',NULL,NULL,'05:00:00','head','approved',NULL,NULL,'2017-04-26 10:00:22',NULL,NULL,'2017-04-21 10:00:22','2017-04-21 10:00:22'),(39452,23,5338,1862,27,NULL,0,'2017-12-26','07:00:00','08:00:00','00:00:00',NULL,NULL,'01:00:00',NULL,'approved','other','admin','2017-08-23 10:42:55',NULL,208,'2017-08-23 10:42:20','2017-08-23 10:42:55'),(45503,23,12943,1862,24,15666,0,'2017-10-02','13:15:00','14:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-10-11 10:00:22',NULL,NULL,'2017-10-06 10:00:22','2017-10-06 10:00:22'),(45504,23,12943,1862,24,15667,0,'2017-10-02','14:20:00','15:30:00','00:00:00',NULL,NULL,'01:10:00','head','approved',NULL,NULL,'2017-10-11 10:00:22',NULL,NULL,'2017-10-06 10:00:22','2017-10-06 10:00:22'),(45505,23,12943,1862,24,15668,0,'2017-10-03','13:15:00','14:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-10-11 10:00:22',NULL,NULL,'2017-10-06 10:00:22','2017-10-06 10:00:22'),(45506,23,12943,1862,24,15669,0,'2017-10-03','14:20:00','15:30:00','00:00:00',NULL,NULL,'01:10:00','head','approved',NULL,NULL,'2017-10-11 10:00:22',NULL,NULL,'2017-10-06 10:00:22','2017-10-06 10:00:22'),(45507,23,12943,1862,24,15670,0,'2017-10-04','13:15:00','14:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-10-11 10:00:22',NULL,NULL,'2017-10-06 10:00:22','2017-10-06 10:00:22'),(45508,23,12943,1862,24,15671,0,'2017-10-04','14:20:00','15:30:00','00:00:00',NULL,NULL,'01:10:00','head','approved',NULL,NULL,'2017-10-11 10:00:22',NULL,NULL,'2017-10-06 10:00:22','2017-10-06 10:00:22'),(45509,23,12943,1862,24,15672,0,'2017-10-05','13:15:00','14:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-10-11 10:00:22',NULL,NULL,'2017-10-06 10:00:22','2017-10-06 10:00:22'),(45510,23,12943,1862,24,15673,0,'2017-10-05','14:20:00','15:30:00','00:00:00',NULL,NULL,'01:10:00','head','approved',NULL,NULL,'2017-10-11 10:00:22',NULL,NULL,'2017-10-06 10:00:22','2017-10-06 10:00:22'),(45511,23,12943,1862,24,15674,0,'2017-10-06','09:50:00','10:55:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-10-11 10:00:22',NULL,NULL,'2017-10-06 10:00:22','2017-10-06 10:00:22'),(45512,23,12943,1862,24,15675,0,'2017-10-06','11:10:00','12:15:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-10-11 10:00:22',NULL,NULL,'2017-10-06 10:00:22','2017-10-06 10:00:22'),(47291,23,13336,1862,24,15666,0,'2017-10-09','13:15:00','14:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-10-18 10:00:22',NULL,NULL,'2017-10-13 10:00:22','2017-10-13 10:00:22'),(47292,23,13336,1862,24,15667,0,'2017-10-09','14:20:00','15:30:00','00:00:00',NULL,NULL,'01:10:00','head','approved',NULL,NULL,'2017-10-18 10:00:22',NULL,NULL,'2017-10-13 10:00:22','2017-10-13 10:00:22'),(47293,23,13336,1862,24,15668,0,'2017-10-10','13:15:00','14:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-10-18 10:00:22',NULL,NULL,'2017-10-13 10:00:22','2017-10-13 10:00:22'),(47294,23,13336,1862,24,15669,0,'2017-10-10','14:20:00','15:30:00','00:00:00',NULL,NULL,'01:10:00','head','approved',NULL,NULL,'2017-10-18 10:00:22',NULL,NULL,'2017-10-13 10:00:22','2017-10-13 10:00:22'),(47295,23,13336,1862,24,15670,0,'2017-10-11','13:15:00','14:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-10-18 10:00:22',NULL,NULL,'2017-10-13 10:00:22','2017-10-13 10:00:22'),(47296,23,13336,1862,24,15671,0,'2017-10-11','14:20:00','15:30:00','00:00:00',NULL,NULL,'01:10:00','head','approved',NULL,NULL,'2017-10-18 10:00:22',NULL,NULL,'2017-10-13 10:00:22','2017-10-13 10:00:22'),(47297,23,13336,1862,24,15672,0,'2017-10-12','13:15:00','14:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-10-18 10:00:22',NULL,NULL,'2017-10-13 10:00:22','2017-10-13 10:00:22'),(47298,23,13336,1862,24,15673,0,'2017-10-12','14:20:00','15:30:00','00:00:00',NULL,NULL,'01:10:00','head','approved',NULL,NULL,'2017-10-18 10:00:22',NULL,NULL,'2017-10-13 10:00:22','2017-10-13 10:00:22'),(47299,23,13336,1862,24,15674,0,'2017-10-13','09:50:00','10:55:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-10-18 10:00:22',NULL,NULL,'2017-10-13 10:00:22','2017-10-13 10:00:22'),(47300,23,13336,1862,24,15675,0,'2017-10-13','11:10:00','12:15:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-10-18 10:00:22',NULL,NULL,'2017-10-13 10:00:22','2017-10-13 10:00:22'),(49114,23,13733,1862,24,15666,0,'2017-10-16','13:15:00','14:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-10-25 10:00:22',NULL,NULL,'2017-10-20 10:00:21','2017-10-20 10:00:21'),(49115,23,13733,1862,24,15667,0,'2017-10-16','14:20:00','15:30:00','00:00:00',NULL,NULL,'01:10:00','head','approved',NULL,NULL,'2017-10-25 10:00:22',NULL,NULL,'2017-10-20 10:00:21','2017-10-20 10:00:21'),(49116,23,13733,1862,24,15668,0,'2017-10-17','13:15:00','14:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-10-25 10:00:22',NULL,NULL,'2017-10-20 10:00:21','2017-10-20 10:00:21'),(49117,23,13733,1862,24,15669,0,'2017-10-17','14:20:00','15:30:00','00:00:00',NULL,NULL,'01:10:00','head','approved',NULL,NULL,'2017-10-25 10:00:22',NULL,NULL,'2017-10-20 10:00:21','2017-10-20 10:00:21'),(49118,23,13733,1862,24,15670,0,'2017-10-18','13:15:00','14:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-10-25 10:00:22',NULL,NULL,'2017-10-20 10:00:21','2017-10-20 10:00:21'),(49119,23,13733,1862,24,15671,0,'2017-10-18','14:20:00','15:30:00','00:00:00',NULL,NULL,'01:10:00','head','approved',NULL,NULL,'2017-10-25 10:00:22',NULL,NULL,'2017-10-20 10:00:21','2017-10-20 10:00:21'),(49120,23,13733,1862,24,15672,0,'2017-10-19','13:15:00','14:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-10-25 10:00:22',NULL,NULL,'2017-10-20 10:00:21','2017-10-20 10:00:21'),(49121,23,13733,1862,24,15673,0,'2017-10-19','14:20:00','15:30:00','00:00:00',NULL,NULL,'01:10:00','head','approved',NULL,NULL,'2017-10-25 10:00:22',NULL,NULL,'2017-10-20 10:00:21','2017-10-20 10:00:21'),(49122,23,13733,1862,24,15674,0,'2017-10-20','09:50:00','10:55:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-10-25 10:00:22',NULL,NULL,'2017-10-20 10:00:21','2017-10-20 10:00:21'),(49123,23,13733,1862,24,15675,0,'2017-10-20','11:10:00','12:15:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-10-25 10:00:22',NULL,NULL,'2017-10-20 10:00:21','2017-10-20 10:00:21'),(51011,23,14135,1862,24,15666,0,'2017-10-23','13:15:00','14:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-11-01 09:00:21',NULL,NULL,'2017-10-23 16:20:21','2017-10-23 16:20:21'),(51012,23,14135,1862,24,15667,0,'2017-10-23','14:20:00','15:30:00','00:00:00',NULL,NULL,'01:10:00','head','approved',NULL,NULL,'2017-11-01 09:00:21',NULL,NULL,'2017-10-23 16:20:21','2017-10-23 16:20:21'),(51013,23,14135,1862,24,15668,0,'2017-10-24','13:15:00','14:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-11-01 09:00:21',NULL,NULL,'2017-10-23 16:20:21','2017-10-23 16:20:21'),(51014,23,14135,1862,24,15669,0,'2017-10-24','14:20:00','15:30:00','00:00:00',NULL,NULL,'01:10:00','head','approved',NULL,NULL,'2017-11-01 09:00:21',NULL,NULL,'2017-10-23 16:20:21','2017-10-23 16:20:21'),(51015,23,14135,1862,24,15670,0,'2017-10-25','13:15:00','14:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-11-01 09:00:21',NULL,NULL,'2017-10-23 16:20:21','2017-10-23 16:20:21'),(51016,23,14135,1862,24,15671,0,'2017-10-25','14:20:00','15:30:00','00:00:00',NULL,NULL,'01:10:00','head','approved',NULL,NULL,'2017-11-01 09:00:21',NULL,NULL,'2017-10-23 16:20:21','2017-10-23 16:20:21'),(51017,23,14135,1862,24,15672,0,'2017-10-26','13:15:00','14:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-11-01 09:00:21',NULL,NULL,'2017-10-23 16:20:21','2017-10-23 16:20:21'),(51018,23,14135,1862,24,15673,0,'2017-10-26','14:20:00','15:30:00','00:00:00',NULL,NULL,'01:10:00','head','approved',NULL,NULL,'2017-11-01 09:00:21',NULL,NULL,'2017-10-23 16:20:21','2017-10-23 16:20:21'),(51019,23,14135,1862,24,15674,0,'2017-10-27','09:50:00','10:55:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-11-01 09:00:21',NULL,NULL,'2017-10-23 16:20:21','2017-10-23 16:20:21'),(51020,23,14135,1862,24,15675,0,'2017-10-27','11:10:00','12:15:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-11-01 09:00:21',NULL,NULL,'2017-10-23 16:20:21','2017-10-23 16:20:21'),(52070,23,14469,1862,24,15666,0,'2017-10-30','13:15:00','14:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-11-08 09:00:22',NULL,NULL,'2017-11-03 09:00:21','2017-11-03 09:00:21'),(52071,23,14469,1862,24,15667,0,'2017-10-30','14:20:00','15:30:00','00:00:00',NULL,NULL,'01:10:00','head','approved',NULL,NULL,'2017-11-08 09:00:22',NULL,NULL,'2017-11-03 09:00:21','2017-11-03 09:00:21'),(52072,23,14469,1862,24,15668,0,'2017-10-31','13:15:00','14:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-11-08 09:00:22',NULL,NULL,'2017-11-03 09:00:21','2017-11-03 09:00:21'),(52073,23,14469,1862,24,15669,0,'2017-10-31','14:20:00','15:30:00','00:00:00',NULL,NULL,'01:10:00','head','approved',NULL,NULL,'2017-11-08 09:00:22',NULL,NULL,'2017-11-03 09:00:21','2017-11-03 09:00:21'),(52074,23,14469,1862,24,15670,0,'2017-11-01','13:15:00','14:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-11-08 09:00:22',NULL,NULL,'2017-11-03 09:00:21','2017-11-03 09:00:21'),(52075,23,14469,1862,24,15671,0,'2017-11-01','14:20:00','15:30:00','00:00:00',NULL,NULL,'01:10:00','head','approved',NULL,NULL,'2017-11-08 09:00:22',NULL,NULL,'2017-11-03 09:00:21','2017-11-03 09:00:21'),(52076,23,14469,1862,24,15672,0,'2017-11-02','13:15:00','14:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-11-08 09:00:22',NULL,NULL,'2017-11-03 09:00:21','2017-11-03 09:00:21'),(52077,23,14469,1862,24,15673,0,'2017-11-02','14:20:00','15:30:00','00:00:00',NULL,NULL,'01:10:00','head','approved',NULL,NULL,'2017-11-08 09:00:22',NULL,NULL,'2017-11-03 09:00:21','2017-11-03 09:00:21'),(52078,23,14469,1862,24,15674,0,'2017-11-03','09:50:00','10:55:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-11-08 09:00:22',NULL,NULL,'2017-11-03 09:00:21','2017-11-03 09:00:21'),(52079,23,14469,1862,24,15675,0,'2017-11-03','11:10:00','12:15:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-11-08 09:00:22',NULL,NULL,'2017-11-03 09:00:21','2017-11-03 09:00:21'),(53822,23,14866,1862,24,15666,0,'2017-11-06','13:15:00','14:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-11-15 09:00:21',NULL,NULL,'2017-11-08 14:07:07','2017-11-08 14:07:07'),(53823,23,14866,1862,24,15667,0,'2017-11-06','14:20:00','15:30:00','00:00:00',NULL,NULL,'01:10:00','head','approved',NULL,NULL,'2017-11-15 09:00:21',NULL,NULL,'2017-11-08 14:07:07','2017-11-08 14:07:07'),(53824,23,14866,1862,24,15668,0,'2017-11-07','13:15:00','14:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-11-15 09:00:21',NULL,NULL,'2017-11-08 14:07:07','2017-11-08 14:07:07'),(53825,23,14866,1862,24,15669,0,'2017-11-07','14:20:00','15:30:00','00:00:00',NULL,NULL,'01:10:00','head','approved',NULL,NULL,'2017-11-15 09:00:21',NULL,NULL,'2017-11-08 14:07:07','2017-11-08 14:07:07'),(53826,23,14866,1862,24,15670,0,'2017-11-08','13:15:00','14:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-11-15 09:00:21',NULL,NULL,'2017-11-08 14:07:07','2017-11-08 14:07:07'),(53827,23,14866,1862,24,15671,0,'2017-11-08','14:20:00','15:30:00','00:00:00',NULL,NULL,'01:10:00','head','approved',NULL,NULL,'2017-11-15 09:00:21',NULL,NULL,'2017-11-08 14:07:07','2017-11-08 14:07:07'),(53828,23,14866,1862,24,15672,0,'2017-11-09','13:15:00','14:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-11-15 09:00:21',NULL,NULL,'2017-11-08 14:07:07','2017-11-08 14:07:07'),(53829,23,14866,1862,24,15673,0,'2017-11-09','14:20:00','15:30:00','00:00:00',NULL,NULL,'01:10:00','head','approved',NULL,NULL,'2017-11-15 09:00:21',NULL,NULL,'2017-11-08 14:07:07','2017-11-08 14:07:07'),(53830,23,14866,1862,24,15674,0,'2017-11-10','09:50:00','10:55:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-11-15 09:00:21',NULL,NULL,'2017-11-08 14:07:07','2017-11-08 14:07:07'),(53831,23,14866,1862,24,15675,0,'2017-11-10','11:10:00','12:15:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-11-15 09:00:21',NULL,NULL,'2017-11-08 14:07:07','2017-11-08 14:07:07'),(55881,23,15278,1862,24,15666,0,'2017-11-13','13:15:00','14:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-11-22 09:00:21',NULL,NULL,'2017-11-14 14:34:53','2017-11-14 14:34:53'),(55882,23,15278,1862,24,15667,0,'2017-11-13','14:20:00','15:30:00','00:00:00',NULL,NULL,'01:10:00','head','approved',NULL,NULL,'2017-11-22 09:00:21',NULL,NULL,'2017-11-14 14:34:53','2017-11-14 14:34:53'),(55883,23,15278,1862,24,15668,0,'2017-11-14','13:15:00','14:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-11-22 09:00:21',NULL,NULL,'2017-11-14 14:34:53','2017-11-14 14:34:53'),(55884,23,15278,1862,24,15669,0,'2017-11-14','14:20:00','15:30:00','00:00:00',NULL,NULL,'01:10:00','head','approved',NULL,NULL,'2017-11-22 09:00:21',NULL,NULL,'2017-11-14 14:34:53','2017-11-14 14:34:53'),(55885,23,15278,1862,24,15670,0,'2017-11-15','13:15:00','14:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-11-22 09:00:21',NULL,NULL,'2017-11-14 14:34:53','2017-11-14 14:34:53'),(55886,23,15278,1862,24,15671,0,'2017-11-15','14:20:00','15:30:00','00:00:00',NULL,NULL,'01:10:00','head','approved',NULL,NULL,'2017-11-22 09:00:21',NULL,NULL,'2017-11-14 14:34:53','2017-11-14 14:34:53'),(55887,23,15278,1862,24,15672,0,'2017-11-16','13:15:00','14:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-11-22 09:00:21',NULL,NULL,'2017-11-14 14:34:53','2017-11-14 14:34:53'),(55888,23,15278,1862,24,15673,0,'2017-11-16','14:20:00','15:30:00','00:00:00',NULL,NULL,'01:10:00','head','approved',NULL,NULL,'2017-11-22 09:00:21',NULL,NULL,'2017-11-14 14:34:53','2017-11-14 14:34:53'),(55889,23,15278,1862,24,15674,0,'2017-11-17','09:50:00','10:55:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-11-22 09:00:21',NULL,NULL,'2017-11-14 14:34:53','2017-11-14 14:34:53'),(55890,23,15278,1862,24,15675,0,'2017-11-17','11:10:00','12:15:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-11-22 09:00:21',NULL,NULL,'2017-11-14 14:34:53','2017-11-14 14:34:53'),(58192,23,15707,1862,24,15666,0,'2017-11-20','13:15:00','14:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-11-29 09:00:21',NULL,NULL,'2017-11-24 09:00:21','2017-11-24 09:00:21'),(58193,23,15707,1862,24,15667,0,'2017-11-20','14:20:00','15:30:00','00:00:00',NULL,NULL,'01:10:00','head','approved',NULL,NULL,'2017-11-29 09:00:21',NULL,NULL,'2017-11-24 09:00:21','2017-11-24 09:00:21'),(58194,23,15707,1862,24,15668,0,'2017-11-21','13:15:00','14:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-11-29 09:00:21',NULL,NULL,'2017-11-24 09:00:21','2017-11-24 09:00:21'),(58195,23,15707,1862,24,15669,0,'2017-11-21','14:20:00','15:30:00','00:00:00',NULL,NULL,'01:10:00','head','approved',NULL,NULL,'2017-11-29 09:00:21',NULL,NULL,'2017-11-24 09:00:21','2017-11-24 09:00:21'),(58196,23,15707,1862,24,15670,0,'2017-11-22','13:15:00','14:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-11-29 09:00:21',NULL,NULL,'2017-11-24 09:00:21','2017-11-24 09:00:21'),(58197,23,15707,1862,24,15671,0,'2017-11-22','14:20:00','15:30:00','00:00:00',NULL,NULL,'01:10:00','head','approved',NULL,NULL,'2017-11-29 09:00:21',NULL,NULL,'2017-11-24 09:00:21','2017-11-24 09:00:21'),(58198,23,15707,1862,24,15672,0,'2017-11-23','13:15:00','14:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-11-29 09:00:21',NULL,NULL,'2017-11-24 09:00:21','2017-11-24 09:00:21'),(58199,23,15707,1862,24,15673,0,'2017-11-23','14:20:00','15:30:00','00:00:00',NULL,NULL,'01:10:00','head','approved',NULL,NULL,'2017-11-29 09:00:21',NULL,NULL,'2017-11-24 09:00:21','2017-11-24 09:00:21'),(58200,23,15707,1862,24,15674,0,'2017-11-24','09:50:00','10:55:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-11-29 09:00:21',NULL,NULL,'2017-11-24 09:00:21','2017-11-24 09:00:21'),(58201,23,15707,1862,24,15675,0,'2017-11-24','11:10:00','12:15:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-11-29 09:00:21',NULL,NULL,'2017-11-24 09:00:21','2017-11-24 09:00:21'),(60530,23,16137,1862,24,15666,0,'2017-11-27','13:15:00','14:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-12-06 09:00:21',NULL,NULL,'2017-11-30 10:52:32','2017-11-30 10:52:32'),(60531,23,16137,1862,24,15667,0,'2017-11-27','14:20:00','15:30:00','00:00:00',NULL,NULL,'01:10:00','head','approved',NULL,NULL,'2017-12-06 09:00:21',NULL,NULL,'2017-11-30 10:52:32','2017-11-30 10:52:32'),(60532,23,16137,1862,24,15668,0,'2017-11-28','13:15:00','14:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-12-06 09:00:21',NULL,NULL,'2017-11-30 10:52:32','2017-11-30 10:52:32'),(60533,23,16137,1862,24,15669,0,'2017-11-28','14:20:00','15:30:00','00:00:00',NULL,NULL,'01:10:00','head','approved',NULL,NULL,'2017-12-06 09:00:21',NULL,NULL,'2017-11-30 10:52:32','2017-11-30 10:52:32'),(60534,23,16137,1862,24,15670,0,'2017-11-29','13:15:00','14:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-12-06 09:00:21',NULL,NULL,'2017-11-30 10:52:32','2017-11-30 10:52:32'),(60535,23,16137,1862,24,15671,0,'2017-11-29','14:20:00','15:30:00','00:00:00',NULL,NULL,'01:10:00','head','approved',NULL,NULL,'2017-12-06 09:00:21',NULL,NULL,'2017-11-30 10:52:32','2017-11-30 10:52:32'),(60536,23,16137,1862,24,15672,0,'2017-11-30','13:15:00','14:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-12-06 09:00:21',NULL,NULL,'2017-11-30 10:52:32','2017-11-30 10:52:32'),(60537,23,16137,1862,24,15673,0,'2017-11-30','14:20:00','15:30:00','00:00:00',NULL,NULL,'01:10:00','head','approved',NULL,NULL,'2017-12-06 09:00:21',NULL,NULL,'2017-11-30 10:52:32','2017-11-30 10:52:32'),(60538,23,16137,1862,24,15674,0,'2017-12-01','09:50:00','10:55:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-12-06 09:00:21',NULL,NULL,'2017-11-30 10:52:32','2017-11-30 10:52:32'),(60539,23,16137,1862,24,15675,0,'2017-12-01','11:10:00','12:15:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-12-06 09:00:21',NULL,NULL,'2017-11-30 10:52:32','2017-11-30 10:52:32'),(62829,23,16564,1862,24,15666,0,'2017-12-04','13:15:00','14:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-12-13 09:00:22',NULL,NULL,'2017-12-08 09:00:21','2017-12-08 09:00:21'),(62830,23,16564,1862,24,15667,0,'2017-12-04','14:20:00','15:30:00','00:00:00',NULL,NULL,'01:10:00','head','approved',NULL,NULL,'2017-12-13 09:00:22',NULL,NULL,'2017-12-08 09:00:21','2017-12-08 09:00:21'),(62831,23,16564,1862,24,15668,0,'2017-12-05','13:15:00','14:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-12-13 09:00:22',NULL,NULL,'2017-12-08 09:00:21','2017-12-08 09:00:21'),(62832,23,16564,1862,24,15669,0,'2017-12-05','14:20:00','15:30:00','00:00:00',NULL,NULL,'01:10:00','head','approved',NULL,NULL,'2017-12-13 09:00:22',NULL,NULL,'2017-12-08 09:00:21','2017-12-08 09:00:21'),(62833,23,16564,1862,24,15670,0,'2017-12-06','13:15:00','14:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-12-13 09:00:22',NULL,NULL,'2017-12-08 09:00:21','2017-12-08 09:00:21'),(62834,23,16564,1862,24,15671,0,'2017-12-06','14:20:00','15:30:00','00:00:00',NULL,NULL,'01:10:00','head','approved',NULL,NULL,'2017-12-13 09:00:22',NULL,NULL,'2017-12-08 09:00:21','2017-12-08 09:00:21'),(62835,23,16564,1862,24,15672,0,'2017-12-07','13:15:00','14:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-12-13 09:00:22',NULL,NULL,'2017-12-08 09:00:21','2017-12-08 09:00:21'),(62836,23,16564,1862,24,15673,0,'2017-12-07','14:20:00','15:30:00','00:00:00',NULL,NULL,'01:10:00','head','approved',NULL,NULL,'2017-12-13 09:00:22',NULL,NULL,'2017-12-08 09:00:21','2017-12-08 09:00:21'),(62837,23,16564,1862,24,15674,0,'2017-12-08','09:50:00','10:55:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-12-13 09:00:22',NULL,NULL,'2017-12-08 09:00:21','2017-12-08 09:00:21'),(62838,23,16564,1862,24,15675,0,'2017-12-08','11:10:00','12:15:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-12-13 09:00:22',NULL,NULL,'2017-12-08 09:00:21','2017-12-08 09:00:21'),(64886,23,16978,1862,24,15666,0,'2017-12-11','13:15:00','14:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-12-20 09:00:23',NULL,NULL,'2017-12-14 15:42:29','2017-12-14 15:42:29'),(64887,23,16978,1862,24,15667,0,'2017-12-11','14:20:00','15:30:00','00:00:00',NULL,NULL,'01:10:00','head','approved',NULL,NULL,'2017-12-20 09:00:23',NULL,NULL,'2017-12-14 15:42:29','2017-12-14 15:42:29'),(64888,23,16978,1862,24,15668,0,'2017-12-12','13:15:00','14:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-12-20 09:00:23',NULL,NULL,'2017-12-14 15:42:29','2017-12-14 15:42:29'),(64889,23,16978,1862,24,15669,0,'2017-12-12','14:20:00','15:30:00','00:00:00',NULL,NULL,'01:10:00','head','approved',NULL,NULL,'2017-12-20 09:00:23',NULL,NULL,'2017-12-14 15:42:29','2017-12-14 15:42:29'),(64890,23,16978,1862,24,15670,0,'2017-12-13','13:15:00','14:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-12-20 09:00:23',NULL,NULL,'2017-12-14 15:42:29','2017-12-14 15:42:29'),(64891,23,16978,1862,24,15671,0,'2017-12-13','14:20:00','15:30:00','00:00:00',NULL,NULL,'01:10:00','head','approved',NULL,NULL,'2017-12-20 09:00:23',NULL,NULL,'2017-12-14 15:42:29','2017-12-14 15:42:29'),(64892,23,16978,1862,24,15672,0,'2017-12-14','13:15:00','14:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-12-20 09:00:23',NULL,NULL,'2017-12-14 15:42:29','2017-12-14 15:42:29'),(64893,23,16978,1862,24,15673,0,'2017-12-14','14:20:00','15:30:00','00:00:00',NULL,NULL,'01:10:00','head','approved',NULL,NULL,'2017-12-20 09:00:23',NULL,NULL,'2017-12-14 15:42:29','2017-12-14 15:42:29'),(64894,23,16978,1862,24,15674,0,'2017-12-15','09:50:00','10:55:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-12-20 09:00:23',NULL,NULL,'2017-12-14 15:42:29','2017-12-14 15:42:29'),(64895,23,16978,1862,24,15675,0,'2017-12-15','11:10:00','12:15:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-12-20 09:00:23',NULL,NULL,'2017-12-14 15:42:29','2017-12-14 15:42:29'),(66710,23,17379,1862,24,15666,0,'2017-12-18','13:15:00','14:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-12-27 09:00:22',NULL,NULL,'2017-12-22 09:00:21','2017-12-22 09:00:21'),(66711,23,17379,1862,24,15667,0,'2017-12-18','14:20:00','15:30:00','00:00:00',NULL,NULL,'01:10:00','head','approved',NULL,NULL,'2017-12-27 09:00:22',NULL,NULL,'2017-12-22 09:00:21','2017-12-22 09:00:21'),(66712,23,17379,1862,24,15668,0,'2017-12-19','13:15:00','14:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-12-27 09:00:22',NULL,NULL,'2017-12-22 09:00:21','2017-12-22 09:00:21'),(66713,23,17379,1862,24,15669,0,'2017-12-19','14:20:00','15:30:00','00:00:00',NULL,NULL,'01:10:00','head','approved',NULL,NULL,'2017-12-27 09:00:22',NULL,NULL,'2017-12-22 09:00:21','2017-12-22 09:00:21'),(66714,23,17379,1862,24,15670,0,'2017-12-20','13:15:00','14:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-12-27 09:00:22',NULL,NULL,'2017-12-22 09:00:21','2017-12-22 09:00:21'),(66715,23,17379,1862,24,15671,0,'2017-12-20','14:20:00','15:30:00','00:00:00',NULL,NULL,'01:10:00','head','approved',NULL,NULL,'2017-12-27 09:00:22',NULL,NULL,'2017-12-22 09:00:21','2017-12-22 09:00:21'),(66716,23,17379,1862,24,15672,0,'2017-12-21','13:15:00','14:20:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-12-27 09:00:22',NULL,NULL,'2017-12-22 09:00:21','2017-12-22 09:00:21'),(66717,23,17379,1862,24,15673,0,'2017-12-21','14:20:00','15:30:00','00:00:00',NULL,NULL,'01:10:00','head','approved',NULL,NULL,'2017-12-27 09:00:22',NULL,NULL,'2017-12-22 09:00:21','2017-12-22 09:00:21'),(66718,23,17379,1862,24,15674,0,'2017-12-22','09:50:00','10:55:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-12-27 09:00:22',NULL,NULL,'2017-12-22 09:00:21','2017-12-22 09:00:21'),(66719,23,17379,1862,24,15675,0,'2017-12-22','11:10:00','12:15:00','00:00:00',NULL,NULL,'01:05:00','head','approved',NULL,NULL,'2017-12-27 09:00:22',NULL,NULL,'2017-12-22 09:00:21','2017-12-22 09:00:21');
/*!40000 ALTER TABLE `app_timesheets_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_timetable_read`
--

DROP TABLE IF EXISTS `app_timetable_read`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_timetable_read` (
  `recordID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `staffID` int(11) DEFAULT NULL,
  `byID` int(11) DEFAULT NULL,
  `fromdash` tinyint(1) NOT NULL DEFAULT '0',
  `week` int(2) NOT NULL,
  `year` int(4) NOT NULL,
  `added` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`recordID`),
  KEY `fk_timetable_read_byID` (`byID`),
  KEY `fk_timetable_read_staffID` (`staffID`),
  KEY `fk_timetable_read_accountID` (`accountID`),
  CONSTRAINT `app_timetable_read_ibfk_1` FOREIGN KEY (`staffID`) REFERENCES `app_staff` (`staffID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_timetable_read_ibfk_2` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `app_timetable_read_ibfk_3` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11008 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_timetable_read`
--

LOCK TABLES `app_timetable_read` WRITE;
/*!40000 ALTER TABLE `app_timetable_read` DISABLE KEYS */;
INSERT INTO `app_timetable_read` VALUES (7258,23,224,224,1,16,2016,'2016-04-14 12:49:00','2016-04-14 12:49:00'),(7259,23,225,225,0,17,2016,'2016-04-26 12:20:23','2016-04-26 12:20:23'),(7323,23,225,225,0,28,2016,'2016-07-11 11:25:45','2016-07-11 11:25:45'),(7347,23,225,225,0,41,2016,'2016-10-11 09:59:55','2016-10-11 09:59:55'),(9099,23,208,208,0,40,2017,'2017-09-28 13:59:09','2017-09-28 13:59:09'),(9100,23,225,225,0,40,2017,'2017-09-28 14:24:17','2017-09-28 14:24:17');
/*!40000 ALTER TABLE `app_timetable_read` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_vouchers`
--

DROP TABLE IF EXISTS `app_vouchers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_vouchers` (
  `voucherID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL DEFAULT '1',
  `byID` int(11) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `code` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `discount_type` enum('percentage','amount') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'percentage',
  `discount` decimal(6,2) NOT NULL,
  `comment` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `added` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`voucherID`),
  KEY `fk_vouchers_byID` (`byID`),
  KEY `fk_vouchers_accountID` (`accountID`),
  CONSTRAINT `app_vouchers_ibfk_1` FOREIGN KEY (`byID`) REFERENCES `app_staff` (`staffID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `app_vouchers_ibfk_2` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=130 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_vouchers`
--

LOCK TABLES `app_vouchers` WRITE;
/*!40000 ALTER TABLE `app_vouchers` DISABLE KEYS */;
INSERT INTO `app_vouchers` VALUES (13,23,224,1,'Early Bird Discount','10','percentage',10.00,'Early Bird Discount Only','2016-04-22 13:09:45','2016-04-22 13:09:45');
/*!40000 ALTER TABLE `app_vouchers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_vouchers_lesson_types`
--

DROP TABLE IF EXISTS `app_vouchers_lesson_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_vouchers_lesson_types` (
  `linkID` int(11) NOT NULL AUTO_INCREMENT,
  `accountID` int(11) NOT NULL,
  `voucherID` int(11) NOT NULL,
  `typeID` int(11) NOT NULL,
  `added` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`linkID`),
  KEY `accountID` (`accountID`),
  KEY `voucherID` (`voucherID`),
  KEY `typeID` (`typeID`),
  CONSTRAINT `app_vouchers_lesson_types_ibfk_1` FOREIGN KEY (`accountID`) REFERENCES `app_accounts` (`accountID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_vouchers_lesson_types_ibfk_2` FOREIGN KEY (`voucherID`) REFERENCES `app_vouchers` (`voucherID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_vouchers_lesson_types_typeID` FOREIGN KEY (`typeID`) REFERENCES `app_lesson_types` (`typeID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=119 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_vouchers_lesson_types`
--

LOCK TABLES `app_vouchers_lesson_types` WRITE;
/*!40000 ALTER TABLE `app_vouchers_lesson_types` DISABLE KEYS */;
INSERT INTO `app_vouchers_lesson_types` VALUES (1,23,13,103,'2016-04-28 10:01:28','2016-04-28 10:01:28');
/*!40000 ALTER TABLE `app_vouchers_lesson_types` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-04-04 18:38:27
