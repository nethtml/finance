-- Adminer 4.17.1 MySQL 5.7.26 dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

CREATE DATABASE `app_lizhenwei_cn` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `app_lizhenwei_cn`;

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `records`;
CREATE TABLE `records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `category` varchar(20) NOT NULL,
  `income` decimal(10,2) DEFAULT '0.00',
  `expense` decimal(10,2) DEFAULT '0.00',
  `image` text,
  `note` text,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4;

INSERT INTO `records` (`id`, `date`, `category`, `income`, `expense`, `image`, `note`, `created_at`) VALUES
(1,	'2024-10-04',	'工资',	1000.00,	0.00,	'',	'预支款',	'2025-02-24 00:18:18'),
(2,	'2024-10-05',	'房租',	0.00,	500.00,	'',	'宾馆住宿',	'2025-02-24 00:19:02'),
(3,	'2025-02-15',	'餐饮',	0.00,	100.00,	'http://localhost/uploads/img_67bb55d2c3bf7.png',	'饭餐请嘉伟吃饭',	'2025-02-24 01:08:18'),
(4,	'2025-02-19',	'餐饮',	0.00,	200.00,	'http://localhost/uploads/img_67bb563ae612f.jpg',	'吃饭',	'2025-02-24 01:09:20'),
(5,	'2025-02-24',	'餐饮',	0.00,	20.00,	'http://localhost/uploads/img_67bb6585be5ef.jpg',	'吃饭',	'2025-02-24 02:14:42'),
(6,	'2025-02-24',	'餐饮',	0.00,	10.00,	'http://localhost/uploads/img_67bb66b5ca07b.jpg',	'早餐',	'2025-02-24 02:19:41'),
(7,	'2025-02-13',	'工资',	654.00,	0.00,	'http://localhost/uploads/img_67c173092b18c.jpg',	'',	'2025-02-28 16:25:57'),
(8,	'2025-02-28',	'投资',	123456.00,	0.00,	'http://localhost/uploads/img_67c173ad1b1fe.jpg',	'1234',	'2025-02-28 16:28:31');

-- 2025-03-01 10:24:31