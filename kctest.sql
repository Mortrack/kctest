-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.1.37-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win32
-- HeidiSQL Version:             10.3.0.5771
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


-- Dumping database structure for kctest
CREATE DATABASE IF NOT EXISTS `kctest` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci */;
USE `kctest`;

-- Dumping structure for table kctest.api_users
CREATE TABLE IF NOT EXISTS `api_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL,
  `password` varchar(510) NOT NULL,
  `status` varchar(255) NOT NULL,
  `created_by` varchar(255) NOT NULL,
  `modified_by` varchar(255) DEFAULT NULL,
  `last_activity_at` timestamp NULL DEFAULT NULL,
  `connected_at` timestamp NULL DEFAULT NULL,
  `disconnected_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `modified_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=latin1;

-- Dumping data for table kctest.api_users: ~1 rows (approximately)
/*!40000 ALTER TABLE `api_users` DISABLE KEYS */;
INSERT INTO `api_users` (`id`, `username`, `first_name`, `last_name`, `role`, `password`, `status`, `created_by`, `modified_by`, `last_activity_at`, `connected_at`, `disconnected_at`, `created_at`, `modified_at`, `deleted_at`) VALUES
	(46, 'test', 'name', 'lastName', 'user', 'Yz++YyO+QxZ7PtgkzfCAQ0nhabsfdT2u1KwjNvPJTVtca1ncb01zpSuOBwhUw34l4/xRVa8glQ2IQYqAMfQCZw==', 'ACTIVE', 'server api', NULL, '2020-01-24 02:12:59', '2020-01-24 00:49:48', '2020-01-24 02:12:59', '2020-01-15 04:16:08', NULL, NULL);
/*!40000 ALTER TABLE `api_users` ENABLE KEYS */;

-- Dumping structure for table kctest.session_keys
CREATE TABLE IF NOT EXISTS `session_keys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `session_key` varchar(510) NOT NULL,
  `browser` varchar(50) NOT NULL,
  `ip` varchar(50) NOT NULL,
  `expiration_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_key` (`session_key`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=latin1;

-- Dumping data for table kctest.session_keys: ~0 rows (approximately)
/*!40000 ALTER TABLE `session_keys` DISABLE KEYS */;
INSERT INTO `session_keys` (`id`, `user_id`, `session_key`, `browser`, `ip`, `expiration_at`) VALUES
	(19, 46, 'aPyKXMAlfo//Dg+z+87RvydiRP+mKmdLaggv1YMHL25W+XpNDf9QbCKwGmFjHNfY9bZt2vrX2C1DTjRGPyg4gg==', 'Chrome', '::1', '2020-01-24 03:12:59');
/*!40000 ALTER TABLE `session_keys` ENABLE KEYS */;

-- Dumping structure for table kctest.students
CREATE TABLE IF NOT EXISTS `students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `created_by` varchar(255) DEFAULT NULL,
  `modified_by` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `modified_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=latin1;

-- Dumping data for table kctest.students: ~7 rows (approximately)
/*!40000 ALTER TABLE `students` DISABLE KEYS */;
INSERT INTO `students` (`id`, `username`, `first_name`, `last_name`, `created_by`, `modified_by`, `created_at`, `modified_at`) VALUES
	(49, 'Happy_man1', 'Alison', 'Burbery', '46', NULL, '2020-01-24 00:26:39', NULL),
	(50, 'Lalaland1', 'Rick', 'Sanchez', '46', NULL, '2020-01-24 00:26:40', NULL),
	(51, 'sea_man1', 'Summer', 'Ashton', '46', NULL, '2020-01-24 00:26:41', NULL),
	(52, 'danger_human1', 'Mike', 'Tyson', '46', NULL, '2020-01-24 00:26:42', NULL),
	(53, 'TheFollower1', 'Morty', 'Diaz', '46', NULL, '2020-01-24 00:26:42', NULL),
	(54, 'Godzilla1', 'Leonardo', 'Harrison', '46', NULL, '2020-01-24 00:26:43', NULL),
	(55, 'UltraMan1', 'Lucio', 'Norton', '46', NULL, '2020-01-24 00:26:44', NULL);
/*!40000 ALTER TABLE `students` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
