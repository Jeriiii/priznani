-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               5.6.10-log - MySQL Community Server (GPL)
-- Server OS:                    Win64
-- HeidiSQL version:             7.0.0.4053
-- Date/time:                    2014-04-29 17:46:03
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET FOREIGN_KEY_CHECKS=0 */;

/*
	Tento skript udela z produkcni databaze databazi se stejnou strukturou, jakou ma 2014-04-29-stable
*/

-- Dumping structure for table priznaniosexu.stream_items
CREATE TABLE IF NOT EXISTS `stream_items` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `videoID` int(10) unsigned DEFAULT NULL,
  `imageID` int(10) unsigned DEFAULT NULL,
  `userGalleryID` int(11) unsigned DEFAULT NULL,
  `confessionID` int(10) unsigned DEFAULT NULL,
  `userID` int(11) unsigned DEFAULT NULL,
  `type` tinyint(3) unsigned DEFAULT '0',
  `create` time NOT NULL,
  PRIMARY KEY (`id`),
  KEY `videoID` (`videoID`),
  KEY `imageID` (`imageID`),
  KEY `confessionID` (`confessionID`),
  KEY `userID` (`userID`),
  KEY `userGalleryID` (`userGalleryID`),
  CONSTRAINT `stream_items_ibfk_1` FOREIGN KEY (`videoID`) REFERENCES `videos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `stream_items_ibfk_2` FOREIGN KEY (`imageID`) REFERENCES `images` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `stream_items_ibfk_3` FOREIGN KEY (`confessionID`) REFERENCES `confessions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `stream_items_ibfk_4` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `stream_items_ibfk_5` FOREIGN KEY (`userGalleryID`) REFERENCES `user_galleries` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.

DROP TABLE `users_fotos`;
-- Dumping structure for table priznaniosexu.users_fotos
CREATE TABLE IF NOT EXISTS `users_fotos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `suffix` varchar(5) DEFAULT NULL,
  `description` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table priznaniosexu.user_galleries
CREATE TABLE IF NOT EXISTS `user_galleries` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `description` varchar(400) DEFAULT NULL,
  `userID` int(11) unsigned NOT NULL,
  `bestImageID` int(11) unsigned DEFAULT NULL,
  `lastImageID` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lastImage` (`lastImageID`),
  KEY `bestImageID` (`bestImageID`),
  KEY `userID` (`userID`),
  CONSTRAINT `bestImage` FOREIGN KEY (`bestImageID`) REFERENCES `user_images` (`id`),
  CONSTRAINT `lastImage` FOREIGN KEY (`lastImageID`) REFERENCES `user_images` (`id`),
  CONSTRAINT `user` FOREIGN KEY (`userID`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table priznaniosexu.user_images
CREATE TABLE IF NOT EXISTS `user_images` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `suffix` varchar(5) NOT NULL,
  `name` varchar(40) DEFAULT NULL,
  `description` varchar(100) NOT NULL,
  `galleryID` int(11) unsigned DEFAULT NULL,
  `widthGalScrn` smallint(3) unsigned NOT NULL DEFAULT '700',
  `heightGalScrn` smallint(3) unsigned NOT NULL DEFAULT '500',
  PRIMARY KEY (`id`),
  KEY `galleryID` (`galleryID`),
  CONSTRAINT `gallery` FOREIGN KEY (`galleryID`) REFERENCES `user_galleries` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table priznaniosexu.wall_items
CREATE TABLE IF NOT EXISTS `wall_items` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `confessionID` int(10) NOT NULL DEFAULT '0',
  `imageID` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `confessionID` (`confessionID`),
  KEY `imageID` (`imageID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.
/*!40014 SET FOREIGN_KEY_CHECKS=1 */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;






ALTER TABLE `confessions`
	ADD COLUMN `inStream` TINYINT(1) NOT NULL DEFAULT '0' AFTER `mark`;

ALTER TABLE `images`
	DROP FOREIGN KEY `images_ibfk_1`;
ALTER TABLE `images`
	ADD CONSTRAINT `videoID` FOREIGN KEY (`videoID`) REFERENCES `videos` (`id`);

ALTER TABLE `galleries`
	CHANGE COLUMN `sexmode` `sexmode` TINYINT(1) NOT NULL DEFAULT '0' AFTER `name`;
	
	ALTER TABLE `galleries`
	CHANGE COLUMN `partymode` `partymode` TINYINT(1) NOT NULL DEFAULT '0' AFTER `sexmode`;

