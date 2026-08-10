/*
SQLyog Ultimate - MySQL GUI v8.22 
MySQL - 5.5.27-log : Database - toda_system
*********************************************************************
*/


/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`toda_system` /*!40100 DEFAULT CHARACTER SET latin1 */;

USE `toda_system`;

/*Table structure for table `availability` */

DROP TABLE IF EXISTS `availability`;

CREATE TABLE `availability` (
  `availabilityid` int(11) NOT NULL AUTO_INCREMENT,
  `driverid` int(11) NOT NULL,
  `status` enum('available','ontrip','offline') NOT NULL,
  `last_updated` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`availabilityid`),
  KEY `fk_availability_driver` (`driverid`),
  CONSTRAINT `fk_availability_driver` FOREIGN KEY (`driverid`) REFERENCES `driver` (`driverid`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

/*Data for the table `availability` */

insert  into `availability`(`availabilityid`,`driverid`,`status`,`last_updated`) values (1,2,'available','2025-12-19 18:24:59'),(2,1,'available','2025-12-19 22:29:09'),(3,1,'offline','2025-12-19 22:34:51'),(4,1,'ontrip','2025-12-19 22:42:37'),(5,1,'available','2025-12-19 22:48:52');

/*Table structure for table `driver` */

DROP TABLE IF EXISTS `driver`;

CREATE TABLE `driver` (
  `driverid` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) DEFAULT NULL,
  `fullname` varchar(100) NOT NULL,
  `contactno` varchar(20) DEFAULT NULL,
  `address` varchar(150) DEFAULT NULL,
  `tricycleid` int(11) NOT NULL,
  PRIMARY KEY (`driverid`),
  KEY `fk_driver_user` (`userid`),
  KEY `fk_driver_tricycle` (`tricycleid`),
  CONSTRAINT `fk_driver_user` FOREIGN KEY (`userid`) REFERENCES `users` (`userid`) ON DELETE CASCADE,
  CONSTRAINT `fk_driver_tricycle` FOREIGN KEY (`tricycleid`) REFERENCES `tricycle` (`tricycleid`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

/*Data for the table `driver` */

insert  into `driver`(`driverid`,`userid`,`fullname`,`contactno`,`address`,`tricycleid`) values (1,1,'System Administrator','09123456789','Santa Rosa, Santiago City, Isabela',1),(2,2,'Juan Dela Cruz','09384950293','Santa Rosa, Santiago City, Isabela',2),(3,3,'John Doe','09887665544','Santa Rosa, Santiago City, Isabela',5),(4,3,'Luis Jay','09832848747','Santa Rosa, Santiago City, Isabela',5);

/*Table structure for table `driver_status_log` */

DROP TABLE IF EXISTS `driver_status_log`;

CREATE TABLE `driver_status_log` (
  `logid` int(11) NOT NULL AUTO_INCREMENT,
  `driverid` int(11) NOT NULL,
  `status` enum('available','ontrip','offline') NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`logid`),
  KEY `driverid` (`driverid`),
  CONSTRAINT `driver_status_log_ibfk_1` FOREIGN KEY (`driverid`) REFERENCES `driver` (`driverid`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=latin1;

/*Data for the table `driver_status_log` */

insert  into `driver_status_log`(`logid`,`driverid`,`status`,`updated_at`) values (1,1,'available','2025-12-19 05:25:12'),(2,1,'available','2025-12-19 05:25:12'),(3,2,'ontrip','2025-12-19 17:58:25'),(4,2,'ontrip','2025-12-19 17:58:25'),(5,2,'offline','2025-12-19 17:58:39'),(6,2,'offline','2025-12-19 17:58:39'),(7,2,'ontrip','2025-12-19 18:07:47'),(8,2,'offline','2025-12-19 18:07:59'),(9,1,'ontrip','2025-12-19 18:13:14'),(10,1,'offline','2025-12-19 18:13:22'),(11,2,'offline','2025-12-19 18:15:42'),(12,2,'available','2025-12-19 18:24:59'),(13,1,'available','2025-12-19 22:29:09'),(14,1,'offline','2025-12-19 22:34:51'),(15,1,'ontrip','2025-12-19 22:42:37'),(16,1,'available','2025-12-19 22:48:52');

/*Table structure for table `printlogs` */

DROP TABLE IF EXISTS `printlogs`;

CREATE TABLE `printlogs` (
  `printid` int(11) NOT NULL AUTO_INCREMENT,
  `printed_by` int(11) NOT NULL,
  `report_type` varchar(50) DEFAULT NULL,
  `printed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`printid`),
  KEY `fk_print_user` (`printed_by`),
  CONSTRAINT `fk_print_user` FOREIGN KEY (`printed_by`) REFERENCES `users` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Data for the table `printlogs` */

/*Table structure for table `tricycle` */

DROP TABLE IF EXISTS `tricycle`;

CREATE TABLE `tricycle` (
  `tricycleid` int(11) NOT NULL AUTO_INCREMENT,
  `plate_no` varchar(20) NOT NULL,
  `color` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`tricycleid`),
  UNIQUE KEY `plate_no` (`plate_no`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;

/*Data for the table `tricycle` */

insert  into `tricycle`(`tricycleid`,`plate_no`,`color`) values (1,'TEST-001','Red'),(2,'TEST-002','Red'),(3,'TEST-003','Red'),(5,'Test-004','Red'),(7,'19283','Red');

/*Table structure for table `triplogs` */

DROP TABLE IF EXISTS `triplogs`;

CREATE TABLE `triplogs` (
  `triplogid` int(11) NOT NULL AUTO_INCREMENT,
  `driverid` int(11) NOT NULL,
  `tricycleid` int(11) NOT NULL,
  `trip_start` timestamp NULL DEFAULT NULL,
  `trip_end` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`triplogid`),
  KEY `fk_trip_driver` (`driverid`),
  KEY `fk_trip_tricycle` (`tricycleid`),
  CONSTRAINT `fk_trip_driver` FOREIGN KEY (`driverid`) REFERENCES `driver` (`driverid`) ON DELETE CASCADE,
  CONSTRAINT `fk_trip_tricycle` FOREIGN KEY (`tricycleid`) REFERENCES `tricycle` (`tricycleid`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

/*Data for the table `triplogs` */

insert  into `triplogs`(`triplogid`,`driverid`,`tricycleid`,`trip_start`,`trip_end`) values (1,1,1,'2025-12-19 18:13:14','2025-12-19 18:13:22'),(2,1,1,'2025-12-19 22:42:37',NULL);

/*Table structure for table `users` */

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `userid` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `userrole` enum('admin','driver') NOT NULL,
  PRIMARY KEY (`userid`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

/*Data for the table `users` */

insert  into `users`(`userid`,`username`,`password`,`userrole`) values (1,'admin','240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9','admin'),(2,'juan','ef92b778bafe771e89245b89ecbc08a44a4e166c06659911881f383d4473e94f','driver'),(3,'driver4','ef92b778bafe771e89245b89ecbc08a44a4e166c06659911881f383d4473e94f','driver');

/*Table structure for table `weatherlogs` */

DROP TABLE IF EXISTS `weatherlogs`;

CREATE TABLE `weatherlogs` (
  `weatherid` int(11) NOT NULL AUTO_INCREMENT,
  `weather_condition` varchar(50) DEFAULT NULL,
  `temperature` decimal(5,2) DEFAULT NULL,
  `windspeed` varchar(20) DEFAULT NULL,
  `logged_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`weatherid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Data for the table `weatherlogs` */

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
