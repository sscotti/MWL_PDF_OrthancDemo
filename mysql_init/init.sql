-- create root user and grant rights
-- this only runs if the data volume has not yet been created

CREATE USER IF NOT EXISTS 'root'@'localhost' IDENTIFIED BY 'root';
GRANT ALL PRIVILEGES ON *.* TO 'root'@'%';
GRANT ALL PRIVILEGES ON *.* TO 'orthanc'@'%';

-- create databases

CREATE DATABASE IF NOT EXISTS `orthanc_ris`;