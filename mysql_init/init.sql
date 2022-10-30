-- create root user and grant rights
-- this only runs if the data volume has not yet been created

CREATE USER IF NOT EXISTS 'root'@'localhost' IDENTIFIED BY 'root';
GRANT ALL PRIVILEGES ON *.* TO 'root'@'%';
GRANT ALL PRIVILEGES ON *.* TO 'orthanc'@'%';

-- create databases

CREATE DATABASE IF NOT EXISTS `orthanc_ris`;

-- add tables

USE orthanc_ris;

-- Create syntax for TABLE 'mwl'
CREATE TABLE `mwl` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `AccessionNumber` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `StudyInstanceUID` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ScheduledProcedureStepStartDate` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `AET` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `MWLJSON` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `Dataset` blob,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `completed` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1245 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

-- Create syntax for TABLE 'n_set'
CREATE TABLE `n_set` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `AffectedSOPInstanceUID` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `MessageID` int DEFAULT NULL,
  `managed_instance` json DEFAULT NULL,
  `mod_list` json DEFAULT NULL,
  `response` json DEFAULT NULL,
  `response_status` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create syntax for TABLE 'study_complete'
CREATE TABLE `study_complete` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `JSON` json DEFAULT NULL,
  `uuid` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `StudyInstanceUID` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `AccessionNumber` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Tech` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `PatientID` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create syntax for TABLE 'study_first_instances'
CREATE TABLE `study_first_instances` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `JSON` json DEFAULT NULL,
  `uuid` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `StudyInstanceUID` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `AccessionNumber` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `PatientID` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;