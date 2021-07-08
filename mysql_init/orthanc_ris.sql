-- Create root and demo users

CREATE USER IF NOT EXISTS 'root'@'localhost' IDENTIFIED BY 'root';
GRANT ALL PRIVILEGES ON *.* TO 'root'@'%';
CREATE USER IF NOT EXISTS 'demo'@'localhost' IDENTIFIED BY 'demo';
GRANT ALL PRIVILEGES ON *.* TO 'demo'@'%';

-- Create Database

CREATE DATABASE IF NOT EXISTS orthanc_ris;

USE orthanc_ris;

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `mwl`;

-- Create syntax for TABLE 'mwl'
CREATE TABLE `mwl` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `AccessionNumber` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `StudyInstanceUID` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ScheduledProcedureStepStartDate` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `AET` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `MWLJSON` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `Dataset` blob,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `n_create`;

-- Create syntax for TABLE 'n_create'
CREATE TABLE `n_create` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `AccessionNumber` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `StudyInstanceUID` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `MessageID` int unsigned DEFAULT NULL,
  `dataset_in` json DEFAULT NULL,
  `mwl` json DEFAULT NULL,
  `dataset_out` json DEFAULT NULL,
  `named_tags` json DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `n_set`;

-- Create syntax for TABLE 'n_set'
CREATE TABLE `n_set` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `AffectedSOPInstanceUID` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `MessageID` int DEFAULT NULL,
  `managed_instance` json DEFAULT NULL,
  `mod_list` json DEFAULT NULL,
  `response` json DEFAULT NULL,
  `response_status` varchar(8) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;