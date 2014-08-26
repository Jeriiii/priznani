-- --------------------------------------------------------
-- Hostitel:                     127.0.0.1
-- Verze serveru:                5.6.15-log - MySQL Community Server (GPL)
-- OS serveru:                   Win64
-- HeidiSQL Verze:               8.2.0.4675
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Exportování struktury pro tabulka postest.activities
CREATE TABLE IF NOT EXISTS `activities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_type` varchar(50) DEFAULT NULL,
  `imageID` int(11) unsigned DEFAULT NULL,
  `statusID` int(11) unsigned DEFAULT NULL,
  `event_ownerID` int(11) unsigned DEFAULT NULL,
  `event_creatorID` int(11) unsigned DEFAULT NULL,
  `viewed` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `FK_activities_user_images` (`imageID`),
  KEY `FK_activities_status` (`statusID`),
  KEY `FK_activities_users` (`event_ownerID`),
  KEY `FK_activities_users_2` (`event_creatorID`),
  CONSTRAINT `FK_activities_status` FOREIGN KEY (`statusID`) REFERENCES `status` (`id`),
  CONSTRAINT `FK_activities_users` FOREIGN KEY (`event_ownerID`) REFERENCES `users` (`id`),
  CONSTRAINT `FK_activities_users_2` FOREIGN KEY (`event_creatorID`) REFERENCES `users` (`id`),
  CONSTRAINT `FK_activities_user_images` FOREIGN KEY (`imageID`) REFERENCES `user_images` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka postest.advices
CREATE TABLE IF NOT EXISTS `advices` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `note` text NOT NULL,
  `mark` tinyint(1) NOT NULL DEFAULT '0',
  `was_on_fb` tinyint(1) NOT NULL DEFAULT '0',
  `create` datetime DEFAULT NULL,
  `release_date` datetime DEFAULT NULL,
  `sort_date` datetime DEFAULT NULL,
  `real` int(7) NOT NULL DEFAULT '0',
  `fake` int(7) NOT NULL DEFAULT '0',
  `fblike` int(7) NOT NULL DEFAULT '0',
  `comment` int(7) NOT NULL DEFAULT '0',
  `add_to_fb_page` int(7) NOT NULL DEFAULT '0',
  `adminID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka postest.confessions
CREATE TABLE IF NOT EXISTS `confessions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `note` text NOT NULL,
  `mark` tinyint(1) NOT NULL DEFAULT '0',
  `inStream` tinyint(1) NOT NULL DEFAULT '0',
  `was_on_fb` tinyint(1) NOT NULL DEFAULT '0',
  `create` datetime DEFAULT NULL,
  `release_date` datetime DEFAULT NULL,
  `sort_date` datetime DEFAULT NULL,
  `real` int(7) NOT NULL DEFAULT '0',
  `fake` int(7) NOT NULL DEFAULT '0',
  `fblike` int(7) NOT NULL DEFAULT '0',
  `comment` int(7) NOT NULL DEFAULT '0',
  `add_to_fb_page` int(7) NOT NULL DEFAULT '0',
  `adminID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka postest.couple
CREATE TABLE IF NOT EXISTS `couple` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `age` int(11) DEFAULT NULL,
  `marital_state` varchar(11) NOT NULL DEFAULT '',
  `orientation` varchar(11) NOT NULL DEFAULT '',
  `tallness` int(11) NOT NULL,
  `shape` varchar(11) NOT NULL DEFAULT '',
  `user_property` varchar(11) NOT NULL DEFAULT '',
  `penis_length` varchar(11) DEFAULT '',
  `penis_width` varchar(11) DEFAULT '',
  `bra_size` varchar(11) DEFAULT '',
  `smoke` varchar(11) NOT NULL DEFAULT '',
  `drink` varchar(11) NOT NULL DEFAULT '',
  `graduation` varchar(11) NOT NULL DEFAULT '',
  `hair_colour` varchar(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka postest.embed_videos
CREATE TABLE IF NOT EXISTS `embed_videos` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `id_serie` int(10) NOT NULL DEFAULT '0' COMMENT 'alex = 1',
  `name` varchar(100) NOT NULL DEFAULT '',
  `script` varchar(1200) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka postest.eshop_games
CREATE TABLE IF NOT EXISTS `eshop_games` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `suffix` varchar(5) DEFAULT NULL,
  `price` smallint(5) DEFAULT NULL,
  `tags` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka postest.eshop_games_orders
CREATE TABLE IF NOT EXISTS `eshop_games_orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `surname` varchar(30) NOT NULL,
  `email` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `discount_coupon` varchar(50) DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `create` datetime NOT NULL,
  `vasnivefantazie` tinyint(1) NOT NULL DEFAULT '0',
  `nespoutanevzruseni` tinyint(1) NOT NULL DEFAULT '0',
  `zhaveukolypropary` tinyint(1) NOT NULL DEFAULT '0',
  `ceskahralasky` tinyint(1) NOT NULL DEFAULT '0',
  `nekonecnaparty` tinyint(1) NOT NULL DEFAULT '0',
  `sexyaktivity` tinyint(1) NOT NULL DEFAULT '0',
  `ceskachlastacka` tinyint(1) NOT NULL DEFAULT '0',
  `milackuuklidto` tinyint(1) NOT NULL DEFAULT '0',
  `sexyhratky` tinyint(1) NOT NULL DEFAULT '0',
  `manazeruvsen` tinyint(1) NOT NULL DEFAULT '0',
  `print` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka postest.files
CREATE TABLE IF NOT EXISTS `files` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_page` int(10) unsigned DEFAULT NULL,
  `name` varchar(200) NOT NULL,
  `suffix` varchar(10) NOT NULL,
  `special_condition` tinyint(2) unsigned DEFAULT '0' COMMENT 'obchodní podmínky',
  PRIMARY KEY (`id`),
  KEY `pages_files` (`id_page`),
  CONSTRAINT `pages_files` FOREIGN KEY (`id_page`) REFERENCES `texts` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka postest.forms
CREATE TABLE IF NOT EXISTS `forms` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `type` tinyint(2) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka postest.galleries
CREATE TABLE IF NOT EXISTS `galleries` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `sexmode` tinyint(1) NOT NULL DEFAULT '0',
  `partymode` tinyint(1) NOT NULL DEFAULT '0',
  `competition` tinyint(1) NOT NULL DEFAULT '0',
  `description` varchar(300) NOT NULL,
  `imageUrl` varchar(50) DEFAULT NULL,
  `current` tinyint(1) NOT NULL DEFAULT '0',
  `lastImageID` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `image` (`lastImageID`),
  CONSTRAINT `image` FOREIGN KEY (`lastImageID`) REFERENCES `images` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka postest.images
CREATE TABLE IF NOT EXISTS `images` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `galleryID` int(10) unsigned NOT NULL,
  `videoID` int(10) unsigned DEFAULT NULL,
  `name` varchar(35) NOT NULL,
  `comment` varchar(500) NOT NULL,
  `user_name` varchar(50) NOT NULL,
  `user_phone` varchar(50) NOT NULL,
  `user_email` varchar(200) NOT NULL,
  `suffix` varchar(10) NOT NULL,
  `order` smallint(3) NOT NULL DEFAULT '0',
  `userID` int(10) unsigned DEFAULT NULL,
  `idInGallery` int(4) unsigned DEFAULT NULL COMMENT 'id obrázku vůči své galerii (pořadové číslo)',
  `approved` tinyint(4) NOT NULL DEFAULT '0',
  `widthGalScrn` smallint(3) DEFAULT NULL,
  `heightGalScrn` smallint(3) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`galleryID`),
  KEY `userID` (`userID`),
  KEY `videoID` (`videoID`),
  CONSTRAINT `videoID` FOREIGN KEY (`videoID`) REFERENCES `videos` (`id`),
  CONSTRAINT `galleryID` FOREIGN KEY (`galleryID`) REFERENCES `galleries` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka postest.party_confessions
CREATE TABLE IF NOT EXISTS `party_confessions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `note` text NOT NULL,
  `mark` tinyint(1) NOT NULL DEFAULT '0',
  `was_on_fb` tinyint(1) NOT NULL DEFAULT '0',
  `create` datetime DEFAULT NULL,
  `release_date` datetime DEFAULT NULL,
  `sort_date` datetime DEFAULT NULL,
  `real` int(7) NOT NULL DEFAULT '0',
  `fake` int(7) NOT NULL DEFAULT '0',
  `fblike` int(7) NOT NULL DEFAULT '0',
  `comment` int(7) NOT NULL DEFAULT '0',
  `add_to_fb_page` int(7) NOT NULL DEFAULT '0',
  `adminID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka postest.payments
CREATE TABLE IF NOT EXISTS `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) unsigned NOT NULL DEFAULT '0',
  `create` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_payments_users` (`userID`),
  CONSTRAINT `FK_payments_users` FOREIGN KEY (`userID`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka postest.status
CREATE TABLE IF NOT EXISTS `status` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `userID` int(11) unsigned NOT NULL DEFAULT '0',
  `text` varchar(600) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_status_users` (`userID`),
  CONSTRAINT `FK_status_users` FOREIGN KEY (`userID`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka postest.stream_items
CREATE TABLE IF NOT EXISTS `stream_items` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `videoID` int(10) unsigned DEFAULT NULL,
  `galleryID` int(10) unsigned DEFAULT NULL,
  `userGalleryID` int(11) unsigned DEFAULT NULL,
  `confessionID` int(10) unsigned DEFAULT NULL,
  `adviceID` int(10) unsigned DEFAULT NULL,
  `userID` int(11) unsigned DEFAULT NULL,
  `type` tinyint(3) unsigned DEFAULT '0',
  `create` time NOT NULL,
  PRIMARY KEY (`id`),
  KEY `videoID` (`videoID`),
  KEY `imageID` (`galleryID`),
  KEY `confessionID` (`confessionID`),
  KEY `userID` (`userID`),
  KEY `userGalleryID` (`userGalleryID`),
  KEY `FK_stream_items_advices` (`adviceID`),
  CONSTRAINT `FK_stream_items_advices` FOREIGN KEY (`adviceID`) REFERENCES `advices` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `stream_items_galleries` FOREIGN KEY (`galleryID`) REFERENCES `galleries` (`id`),
  CONSTRAINT `stream_items_ibfk_1` FOREIGN KEY (`videoID`) REFERENCES `videos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `stream_items_ibfk_3` FOREIGN KEY (`confessionID`) REFERENCES `confessions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `stream_items_ibfk_4` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `stream_items_ibfk_5` FOREIGN KEY (`userGalleryID`) REFERENCES `user_galleries` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka postest.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `propertyID` int(11) unsigned DEFAULT NULL,
  `coupleID` int(11) unsigned DEFAULT NULL,
  `profilFotoID` int(11) unsigned DEFAULT NULL,
  `confirmed` varchar(100) DEFAULT NULL,
  `admin_score` int(100) DEFAULT NULL,
  `role` varchar(50) NOT NULL DEFAULT '',
  `last_active` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `email` varchar(50) DEFAULT '',
  `user_name` varchar(20) DEFAULT '',
  `password` varchar(200) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `FK_users_users_properties` (`propertyID`),
  KEY `FK_users_couple` (`coupleID`),
  KEY `FK_users_user_images` (`profilFotoID`),
  CONSTRAINT `FK_users_user_images` FOREIGN KEY (`profilFotoID`) REFERENCES `user_images` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `FK_users_couple` FOREIGN KEY (`coupleID`) REFERENCES `couple` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_users_users_properties` FOREIGN KEY (`propertyID`) REFERENCES `users_properties` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka postest.users_fotos
CREATE TABLE IF NOT EXISTS `users_fotos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `suffix` varchar(5) DEFAULT NULL,
  `description` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka postest.users_properties
CREATE TABLE IF NOT EXISTS `users_properties` (
  `id` int(11) unsigned NOT NULL,
  `age` int(11) unsigned DEFAULT NULL,
  `user_property` varchar(11) DEFAULT '',
  `interested_in` varchar(11) DEFAULT '',
  `first_sentence` varchar(200) DEFAULT '',
  `about_me` varchar(300) DEFAULT '',
  `marital_state` varchar(50) DEFAULT '',
  `orientation` varchar(50) DEFAULT '',
  `tallness` int(11) DEFAULT NULL,
  `shape` varchar(50) DEFAULT '',
  `penis_length` varchar(50) DEFAULT NULL,
  `penis_width` varchar(50) DEFAULT NULL,
  `smoke` varchar(50) DEFAULT '',
  `drink` varchar(50) DEFAULT '',
  `graduation` varchar(50) DEFAULT NULL,
  `bra_size` varchar(11) DEFAULT NULL,
  `hair_colour` varchar(50) DEFAULT '',
  `threesome` tinyint(1) DEFAULT NULL,
  `anal` tinyint(1) DEFAULT NULL,
  `group` tinyint(1) DEFAULT NULL,
  `bdsm` tinyint(1) DEFAULT NULL,
  `swallow` tinyint(1) DEFAULT NULL,
  `cum` tinyint(1) DEFAULT NULL,
  `oral` tinyint(1) DEFAULT NULL,
  `piss` tinyint(1) DEFAULT NULL,
  `sex_massage` tinyint(1) DEFAULT NULL,
  `petting` tinyint(1) DEFAULT NULL,
  `fisting` tinyint(1) DEFAULT NULL,
  `deepthrought` tinyint(1) DEFAULT NULL,
  `want_to_meet_men` tinyint(1) DEFAULT NULL,
  `want_to_meet_women` tinyint(1) DEFAULT NULL,
  `want_to_meet_couple` tinyint(1) DEFAULT NULL,
  `want_to_meet_couple_men` tinyint(1) DEFAULT NULL,
  `want_to_meet_couple_women` tinyint(1) DEFAULT NULL,
  `want_to_meet_group` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka postest.user_galleries
CREATE TABLE IF NOT EXISTS `user_galleries` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `description` varchar(400) DEFAULT NULL,
  `userID` int(11) unsigned DEFAULT NULL,
  `bestImageID` int(11) unsigned DEFAULT NULL,
  `lastImageID` int(11) unsigned DEFAULT NULL,
  `man` tinyint(1) DEFAULT '0',
  `women` tinyint(1) DEFAULT '0',
  `couple` tinyint(1) DEFAULT '0',
  `more` tinyint(1) DEFAULT '0',
  `default` tinyint(1) DEFAULT '0',
  `profil_gallery` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `lastImage` (`lastImageID`),
  KEY `bestImageID` (`bestImageID`),
  KEY `userID` (`userID`),
  CONSTRAINT `FK1_bestImageID` FOREIGN KEY (`bestImageID`) REFERENCES `user_images` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `FK2_lastImageID` FOREIGN KEY (`lastImageID`) REFERENCES `user_images` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `FK3_userID` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka postest.user_images
CREATE TABLE IF NOT EXISTS `user_images` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `suffix` varchar(5) NOT NULL,
  `name` varchar(40) DEFAULT NULL,
  `description` varchar(100) NOT NULL,
  `galleryID` int(11) unsigned DEFAULT NULL,
  `allow` tinyint(1) DEFAULT '0',
  `widthGalScrn` smallint(3) unsigned NOT NULL DEFAULT '700',
  `heightGalScrn` smallint(3) unsigned NOT NULL DEFAULT '500',
  PRIMARY KEY (`id`),
  KEY `galleryID` (`galleryID`),
  CONSTRAINT `gallery` FOREIGN KEY (`galleryID`) REFERENCES `user_galleries` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka postest.videos
CREATE TABLE IF NOT EXISTS `videos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;

