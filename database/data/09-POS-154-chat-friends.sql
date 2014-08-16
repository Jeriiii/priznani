-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               5.6.10-log - MySQL Community Server (GPL)
-- Server OS:                    Win64
-- HeidiSQL version:             7.0.0.4053
-- Date/time:                    2014-08-14 15:59:46
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET FOREIGN_KEY_CHECKS=0 */;
-- Dumping data for table pos.friends: ~0 rows (approximately)
/*!40000 ALTER TABLE `friends` DISABLE KEYS */;
INSERT INTO `friends` (`id`, `userID1`, `userID2`) VALUES
	(1, 3, 4),
	(2, 4, 3),
	(3, 1, 3),
	(4, 3, 1),
	(5, 1, 4),
	(6, 4, 1);
/*!40000 ALTER TABLE `friends` ENABLE KEYS */;
/*!40014 SET FOREIGN_KEY_CHECKS=1 */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
