-- --------------------------------------------------------
-- Hostitel:                     127.0.0.1
-- Verze serveru:                5.6.15-log - MySQL Community Server (GPL)
-- OS serveru:                   Win64
-- HeidiSQL Verze:               8.3.0.4694
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Exportování struktury pro tabulka pos.activities
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
  CONSTRAINT `FK_activities_status` FOREIGN KEY (`statusID`) REFERENCES `status` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_activities_users` FOREIGN KEY (`event_ownerID`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_activities_users_2` FOREIGN KEY (`event_creatorID`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_activities_user_images` FOREIGN KEY (`imageID`) REFERENCES `user_images` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka pos.advices
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


-- Exportování struktury pro tabulka pos.category_likes
CREATE TABLE IF NOT EXISTS `category_likes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fisting` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `petting` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `sex_massage` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `piss` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `oral` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `swallow` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `bdsm` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `group` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `anal` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `threesome` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `fisting_petting_sex_massage_piss_oral` (`fisting`,`petting`,`sex_massage`,`piss`,`oral`),
  UNIQUE KEY `swallow_bdsm_group_anal_threesome` (`swallow`,`bdsm`,`group`,`anal`,`threesome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka pos.category_property_want_to_meet
CREATE TABLE IF NOT EXISTS `category_property_want_to_meet` (
  `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `want_to_meet_group` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `want_to_meet_couple_women` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `want_to_meet_couple_men` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `want_to_meet_couple` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `want_to_meet_women` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `want_to_meet_men` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `type` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `all_colums` (`want_to_meet_group`,`want_to_meet_couple_women`,`want_to_meet_couple_men`,`want_to_meet_couple`,`want_to_meet_women`,`want_to_meet_men`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka pos.chat_messages
CREATE TABLE IF NOT EXISTS `chat_messages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_sender` int(11) unsigned NOT NULL COMMENT 'kdo to poslal',
  `id_recipient` int(11) unsigned NOT NULL COMMENT 'komu to poslal',
  `text` text,
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 - klasicka zprava',
  `readed` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 - neprecteno, 1/jine - precteno',
  PRIMARY KEY (`id`),
  KEY `id_sender_id_recipient` (`id_recipient`,`id_sender`),
  KEY `FK_chat_messages_users` (`id_sender`),
  CONSTRAINT `FK_chat_messages_users` FOREIGN KEY (`id_sender`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_chat_messages_users_2` FOREIGN KEY (`id_recipient`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka pos.city
CREATE TABLE IF NOT EXISTS `city` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(35) NOT NULL,
  `districtID` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_city_district` (`districtID`),
  CONSTRAINT `FK_city_district` FOREIGN KEY (`districtID`) REFERENCES `district` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka pos.comment_images
CREATE TABLE IF NOT EXISTS `comment_images` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userID` int(10) unsigned NOT NULL,
  `imageID` int(10) unsigned DEFAULT NULL,
  `likes` smallint(4) unsigned DEFAULT '0',
  `comment` text,
  PRIMARY KEY (`id`),
  KEY `FK__user_images` (`imageID`),
  KEY `userID` (`userID`),
  CONSTRAINT `FK_comment_images_comment_images` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK__user_images` FOREIGN KEY (`imageID`) REFERENCES `user_images` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka pos.competitions_images
CREATE TABLE IF NOT EXISTS `competitions_images` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `imageID` int(10) unsigned DEFAULT NULL,
  `userID` int(10) unsigned DEFAULT NULL,
  `competitionID` int(10) unsigned DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `name` varchar(35) DEFAULT NULL,
  `surname` varchar(35) DEFAULT NULL,
  `allowed` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `FK_competitions_images_competitions_images` (`competitionID`),
  KEY `FK_competitions_images_users` (`userID`),
  KEY `FK_competitions_images_user_images` (`imageID`),
  CONSTRAINT `FK_competitions_images_competitions_images` FOREIGN KEY (`competitionID`) REFERENCES `users_competitions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_competitions_images_users` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `FK_competitions_images_user_images` FOREIGN KEY (`imageID`) REFERENCES `user_images` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka pos.confessions
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


-- Exportování struktury pro tabulka pos.contacts
CREATE TABLE IF NOT EXISTS `contacts` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `userID` int(11) unsigned DEFAULT NULL,
  `email` varchar(50) DEFAULT '',
  `phone` varchar(20) DEFAULT '',
  `text` varchar(500) DEFAULT NULL,
  `viewed` tinyint(4) DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `FK_contacts_users` (`userID`),
  CONSTRAINT `FK_contacts_users` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka pos.couple
CREATE TABLE IF NOT EXISTS `couple` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `age` date DEFAULT NULL,
  `marital_state` varchar(11) NOT NULL DEFAULT '',
  `orientation` varchar(11) NOT NULL DEFAULT '',
  `tallness` int(11) NOT NULL,
  `shape` varchar(11) NOT NULL DEFAULT '',
  `type` tinyint(1) unsigned DEFAULT NULL,
  `penis_length` varchar(11) DEFAULT '',
  `penis_width` varchar(11) DEFAULT '',
  `bra_size` varchar(11) DEFAULT '',
  `smoke` varchar(11) NOT NULL DEFAULT '',
  `drink` varchar(11) NOT NULL DEFAULT '',
  `graduation` varchar(11) NOT NULL DEFAULT '',
  `hair_colour` varchar(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_couple_enum_property` (`type`),
  CONSTRAINT `FK_couple_enum_property` FOREIGN KEY (`type`) REFERENCES `enum_property` (`id`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka pos.district
CREATE TABLE IF NOT EXISTS `district` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(35) DEFAULT NULL,
  `regionID` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_district_region` (`regionID`),
  CONSTRAINT `FK_district_region` FOREIGN KEY (`regionID`) REFERENCES `region` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='okres';

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka pos.embed_videos
CREATE TABLE IF NOT EXISTS `embed_videos` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `id_serie` int(10) NOT NULL DEFAULT '0' COMMENT 'alex = 1',
  `name` varchar(100) NOT NULL DEFAULT '',
  `script` varchar(1200) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka pos.enum_bra_size
CREATE TABLE IF NOT EXISTS `enum_bra_size` (
  `id` tinyint(2) unsigned NOT NULL AUTO_INCREMENT,
  `bra_size` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka pos.enum_drink
CREATE TABLE IF NOT EXISTS `enum_drink` (
  `id` tinyint(2) unsigned NOT NULL AUTO_INCREMENT,
  `drink` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka pos.enum_graduation
CREATE TABLE IF NOT EXISTS `enum_graduation` (
  `id` tinyint(2) unsigned NOT NULL AUTO_INCREMENT,
  `graduation` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka pos.enum_hair_colour
CREATE TABLE IF NOT EXISTS `enum_hair_colour` (
  `id` tinyint(2) unsigned NOT NULL AUTO_INCREMENT,
  `hair_colour` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka pos.enum_marital_state
CREATE TABLE IF NOT EXISTS `enum_marital_state` (
  `id` tinyint(2) unsigned NOT NULL AUTO_INCREMENT,
  `marital_state` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka pos.enum_orientation
CREATE TABLE IF NOT EXISTS `enum_orientation` (
  `id` tinyint(2) unsigned NOT NULL AUTO_INCREMENT,
  `orientation` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka pos.enum_penis_length
CREATE TABLE IF NOT EXISTS `enum_penis_length` (
  `id` tinyint(2) unsigned NOT NULL AUTO_INCREMENT,
  `penis_width` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka pos.enum_penis_width
CREATE TABLE IF NOT EXISTS `enum_penis_width` (
  `id` tinyint(2) unsigned NOT NULL AUTO_INCREMENT,
  `penis_width` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka pos.enum_place
CREATE TABLE IF NOT EXISTS `enum_place` (
  `id` tinyint(2) unsigned NOT NULL AUTO_INCREMENT,
  `place` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka pos.enum_position
CREATE TABLE IF NOT EXISTS `enum_position` (
  `id` tinyint(2) unsigned NOT NULL AUTO_INCREMENT,
  `position` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka pos.enum_property
CREATE TABLE IF NOT EXISTS `enum_property` (
  `id` tinyint(1) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(15) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka pos.enum_shape
CREATE TABLE IF NOT EXISTS `enum_shape` (
  `id` tinyint(2) unsigned NOT NULL AUTO_INCREMENT,
  `shape` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka pos.enum_smoke
CREATE TABLE IF NOT EXISTS `enum_smoke` (
  `id` tinyint(2) unsigned NOT NULL AUTO_INCREMENT,
  `smoke` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka pos.enum_status
CREATE TABLE IF NOT EXISTS `enum_status` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(25) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka pos.enum_tallness
CREATE TABLE IF NOT EXISTS `enum_tallness` (
  `id` tinyint(2) unsigned NOT NULL AUTO_INCREMENT,
  `tallness` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka pos.eshop_games
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


-- Exportování struktury pro tabulka pos.eshop_games_orders
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


-- Exportování struktury pro tabulka pos.files
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


-- Exportování struktury pro tabulka pos.forms
CREATE TABLE IF NOT EXISTS `forms` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `type` tinyint(2) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka pos.friendrequest
CREATE TABLE IF NOT EXISTS `friendrequest` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `userFromID` int(11) unsigned NOT NULL,
  `userToID` int(11) unsigned NOT NULL,
  `message` varchar(200) NOT NULL,
  `create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `FK_friendrequest_users` (`userFromID`),
  KEY `FK_friendrequest_users_2` (`userToID`),
  CONSTRAINT `FK_friendrequest_users` FOREIGN KEY (`userFromID`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_friendrequest_users_2` FOREIGN KEY (`userToID`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka pos.friends
CREATE TABLE IF NOT EXISTS `friends` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user1ID` int(11) unsigned NOT NULL,
  `user2ID` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userID2` (`user2ID`),
  KEY `userID1` (`user1ID`),
  CONSTRAINT `userID1` FOREIGN KEY (`user1ID`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `userID2` FOREIGN KEY (`user2ID`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Přátelství funguje mezi přáteli A a B, jen když je vazba A,B a B,A.';

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka pos.galleries
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
  CONSTRAINT `image` FOREIGN KEY (`lastImageID`) REFERENCES `images` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka pos.images
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
  CONSTRAINT `videoID` FOREIGN KEY (`videoID`) REFERENCES `videos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `galleryID` FOREIGN KEY (`galleryID`) REFERENCES `galleries` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka pos.like_comments
CREATE TABLE IF NOT EXISTS `like_comments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userID` int(10) unsigned DEFAULT NULL,
  `commentID` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK__userID_users` (`userID`),
  KEY `FK__commentID_comment_images` (`commentID`),
  CONSTRAINT `FK__userID_users` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK__commentID_comment_images` FOREIGN KEY (`commentID`) REFERENCES `comment_images` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka pos.like_images
CREATE TABLE IF NOT EXISTS `like_images` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userID` int(10) unsigned DEFAULT NULL,
  `imageID` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_image_likes_users` (`userID`),
  KEY `FK_image_likes_user_images` (`imageID`),
  CONSTRAINT `FK_image_likes_users` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_image_likes_user_images` FOREIGN KEY (`imageID`) REFERENCES `user_images` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka pos.like_statuses
CREATE TABLE IF NOT EXISTS `like_statuses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(10) unsigned DEFAULT NULL,
  `statusID` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK__users` (`userID`),
  KEY `FK__status` (`statusID`),
  CONSTRAINT `FK__users` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK__status` FOREIGN KEY (`statusID`) REFERENCES `status` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka pos.party_confessions
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


-- Exportování struktury pro tabulka pos.payments
CREATE TABLE IF NOT EXISTS `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) unsigned NOT NULL DEFAULT '0',
  `create` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_payments_users` (`userID`),
  CONSTRAINT `FK_payments_users` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka pos.region
CREATE TABLE IF NOT EXISTS `region` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(35) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='kraj';

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka pos.status
CREATE TABLE IF NOT EXISTS `status` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `userID` int(11) unsigned NOT NULL DEFAULT '0',
  `message` text,
  `likes` int(5) unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `FK_status_users` (`userID`),
  CONSTRAINT `FK_status_users` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka pos.stream_items
CREATE TABLE IF NOT EXISTS `stream_items` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `videoID` int(10) unsigned DEFAULT NULL,
  `galleryID` int(10) unsigned DEFAULT NULL,
  `statusID` int(10) unsigned DEFAULT NULL,
  `userGalleryID` int(11) unsigned DEFAULT NULL,
  `confessionID` int(10) unsigned DEFAULT NULL,
  `adviceID` int(10) unsigned DEFAULT NULL,
  `userID` int(11) unsigned DEFAULT NULL,
  `categoryID` int(11) unsigned DEFAULT NULL,
  `type` tinyint(3) unsigned DEFAULT '0',
  `create` time NOT NULL,
  `age` date DEFAULT NULL,
  `tallness` int(6) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `videoID` (`videoID`),
  KEY `imageID` (`galleryID`),
  KEY `confessionID` (`confessionID`),
  KEY `userID` (`userID`),
  KEY `userGalleryID` (`userGalleryID`),
  KEY `FK_stream_items_advices` (`adviceID`),
  KEY `FK_stream_items_status` (`statusID`),
  KEY `age` (`age`),
  KEY `categoryID` (`categoryID`),
  CONSTRAINT `categoryIDtoBitmap` FOREIGN KEY (`categoryID`) REFERENCES `user_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_stream_items_advices` FOREIGN KEY (`adviceID`) REFERENCES `advices` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_stream_items_status` FOREIGN KEY (`statusID`) REFERENCES `status` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `stream_items_galleries` FOREIGN KEY (`galleryID`) REFERENCES `galleries` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `stream_items_ibfk_1` FOREIGN KEY (`videoID`) REFERENCES `videos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `stream_items_ibfk_3` FOREIGN KEY (`confessionID`) REFERENCES `confessions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `stream_items_ibfk_4` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `stream_items_ibfk_5` FOREIGN KEY (`userGalleryID`) REFERENCES `user_galleries` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka pos.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `propertyID` int(11) unsigned DEFAULT NULL,
  `coupleID` int(11) unsigned DEFAULT NULL,
  `profilFotoID` int(11) unsigned DEFAULT NULL,
  `wasCategoryChanged` tinyint(1) unsigned NOT NULL DEFAULT '0',
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
  CONSTRAINT `FK_users_couple` FOREIGN KEY (`coupleID`) REFERENCES `couple` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_users_users_properties` FOREIGN KEY (`propertyID`) REFERENCES `users_properties` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_users_user_images` FOREIGN KEY (`profilFotoID`) REFERENCES `user_images` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka pos.users_competitions
CREATE TABLE IF NOT EXISTS `users_competitions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `description` varchar(300) NOT NULL,
  `imageUrl` varchar(50) NOT NULL,
  `current` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `lastImageID` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_users_competitions_user_images` (`lastImageID`),
  CONSTRAINT `FK_users_competitions_user_images` FOREIGN KEY (`lastImageID`) REFERENCES `user_images` (`id`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka pos.users_fotos
CREATE TABLE IF NOT EXISTS `users_fotos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `suffix` varchar(5) DEFAULT NULL,
  `description` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka pos.users_properties
CREATE TABLE IF NOT EXISTS `users_properties` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `age` date NOT NULL,
  `statusID` tinyint(2) unsigned DEFAULT NULL,
  `type` tinyint(3) unsigned DEFAULT '1' COMMENT 'user type',
  `first_sentence` varchar(200) DEFAULT '',
  `about_me` varchar(300) DEFAULT '',
  `tallness` tinyint(2) unsigned DEFAULT NULL,
  `preferencesID` int(11) unsigned DEFAULT NULL,
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
  `want_to_meet_men` tinyint(1) NOT NULL DEFAULT '0',
  `want_to_meet_women` tinyint(1) NOT NULL DEFAULT '0',
  `want_to_meet_couple` tinyint(1) NOT NULL DEFAULT '0',
  `want_to_meet_couple_men` tinyint(1) NOT NULL DEFAULT '0',
  `want_to_meet_couple_women` tinyint(1) NOT NULL DEFAULT '0',
  `want_to_meet_group` tinyint(1) NOT NULL DEFAULT '0',
  `cityID` int(10) unsigned DEFAULT NULL,
  `districtID` int(10) unsigned DEFAULT NULL,
  `regionID` int(10) unsigned DEFAULT NULL,
  `marital_state` tinyint(2) unsigned DEFAULT NULL,
  `orientation` tinyint(2) unsigned DEFAULT NULL,
  `shape` tinyint(2) unsigned DEFAULT NULL,
  `penis_length` tinyint(2) unsigned DEFAULT NULL,
  `penis_width` tinyint(2) unsigned DEFAULT NULL,
  `drink` tinyint(2) unsigned DEFAULT NULL,
  `graduation` tinyint(2) unsigned DEFAULT NULL,
  `bra_size` tinyint(2) unsigned DEFAULT NULL,
  `smoke` tinyint(2) unsigned DEFAULT NULL,
  `hair_colour` tinyint(2) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_users_properties_city` (`cityID`),
  KEY `FK_users_properties_district` (`districtID`),
  KEY `FK_users_properties_region` (`regionID`),
  KEY `preferencesID` (`preferencesID`),
  KEY `FK_users_properties_enum_hair_colour` (`hair_colour`),
  KEY `FK_users_properties_enum_bra_size` (`bra_size`),
  KEY `FK_users_properties_enum_drink` (`drink`),
  KEY `FK_users_properties_enum_graduation` (`graduation`),
  KEY `FK_users_properties_enum_marital_state` (`marital_state`),
  KEY `FK_users_properties_enum_orientation` (`orientation`),
  KEY `FK_users_properties_enum_penis_width` (`penis_width`),
  KEY `FK_users_properties_enum_shape` (`shape`),
  KEY `FK_users_properties_enum_smoke` (`smoke`),
  KEY `FK_users_properties_enum_status` (`statusID`),
  KEY `FK_users_properties_enum_tallness` (`tallness`),
  KEY `FK_users_properties_enum_property` (`type`),
  CONSTRAINT `FK_users_properties_enum_property` FOREIGN KEY (`type`) REFERENCES `enum_property` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `FK_users_properties_city` FOREIGN KEY (`cityID`) REFERENCES `city` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `FK_users_properties_district` FOREIGN KEY (`districtID`) REFERENCES `district` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `FK_users_properties_enum_bra_size` FOREIGN KEY (`bra_size`) REFERENCES `enum_bra_size` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `FK_users_properties_enum_drink` FOREIGN KEY (`drink`) REFERENCES `enum_drink` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `FK_users_properties_enum_graduation` FOREIGN KEY (`graduation`) REFERENCES `enum_graduation` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `FK_users_properties_enum_hair_colour` FOREIGN KEY (`hair_colour`) REFERENCES `enum_hair_colour` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `FK_users_properties_enum_marital_state` FOREIGN KEY (`marital_state`) REFERENCES `enum_marital_state` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `FK_users_properties_enum_orientation` FOREIGN KEY (`orientation`) REFERENCES `enum_orientation` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `FK_users_properties_enum_penis_width` FOREIGN KEY (`penis_width`) REFERENCES `enum_penis_width` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `FK_users_properties_enum_shape` FOREIGN KEY (`shape`) REFERENCES `enum_shape` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `FK_users_properties_enum_smoke` FOREIGN KEY (`smoke`) REFERENCES `enum_smoke` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `FK_users_properties_enum_status` FOREIGN KEY (`statusID`) REFERENCES `enum_status` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `FK_users_properties_enum_tallness` FOREIGN KEY (`tallness`) REFERENCES `enum_tallness` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `FK_users_properties_region` FOREIGN KEY (`regionID`) REFERENCES `region` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `FK_users_properties_stream_items_preferences` FOREIGN KEY (`preferencesID`) REFERENCES `user_categories` (`id`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka pos.user_categories
CREATE TABLE IF NOT EXISTS `user_categories` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `tallness` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `likes` int(10) unsigned DEFAULT NULL,
  `property_want_to_meet` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_user_categories_category_likes` (`likes`),
  KEY `FK_user_categories_category_property_want_to_meet` (`property_want_to_meet`),
  CONSTRAINT `FK_user_categories_category_property_want_to_meet` FOREIGN KEY (`property_want_to_meet`) REFERENCES `category_property_want_to_meet` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `FK_user_categories_category_likes` FOREIGN KEY (`likes`) REFERENCES `category_likes` (`id`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka pos.user_change_password
CREATE TABLE IF NOT EXISTS `user_change_password` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `userID` int(11) unsigned DEFAULT NULL,
  `ticket` varchar(30) DEFAULT NULL,
  `create` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `FK_ user_change_password_users` (`userID`),
  CONSTRAINT `FK_ user_change_password_users` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka pos.user_galleries
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


-- Exportování struktury pro tabulka pos.user_images
CREATE TABLE IF NOT EXISTS `user_images` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `suffix` varchar(5) NOT NULL,
  `name` varchar(40) DEFAULT NULL,
  `description` varchar(100) NOT NULL,
  `galleryID` int(11) unsigned DEFAULT NULL,
  `approved` tinyint(1) DEFAULT '0',
  `widthGalScrn` smallint(3) unsigned NOT NULL DEFAULT '700',
  `heightGalScrn` smallint(3) unsigned NOT NULL DEFAULT '500',
  `likes` int(5) unsigned NOT NULL DEFAULT '0',
  `comments` int(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `galleryID` (`galleryID`),
  CONSTRAINT `gallery` FOREIGN KEY (`galleryID`) REFERENCES `user_galleries` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka pos.user_place
CREATE TABLE IF NOT EXISTS `user_place` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_propertiesID` int(11) unsigned DEFAULT NULL,
  `enum_placeID` tinyint(2) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_user_place_enum_place` (`enum_placeID`),
  KEY `FK_user_place_user_place` (`user_propertiesID`),
  CONSTRAINT `FK_user_place_user_place` FOREIGN KEY (`user_propertiesID`) REFERENCES `users_properties` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_user_place_enum_place` FOREIGN KEY (`enum_placeID`) REFERENCES `enum_place` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka pos.user_position
CREATE TABLE IF NOT EXISTS `user_position` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_propertiesID` int(11) unsigned DEFAULT NULL,
  `enum_positionID` tinyint(2) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_user_position_enum_position` (`enum_positionID`),
  KEY `FK_user_position_user_position` (`user_propertiesID`),
  CONSTRAINT `FK_user_position_user_position` FOREIGN KEY (`user_propertiesID`) REFERENCES `users_properties` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_user_position_enum_position` FOREIGN KEY (`enum_positionID`) REFERENCES `enum_position` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka pos.videos
CREATE TABLE IF NOT EXISTS `videos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Export dat nebyl vybrán.


-- Exportování struktury pro tabulka pos.you_are_sexy
CREATE TABLE IF NOT EXISTS `you_are_sexy` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `userFromID` int(11) unsigned NOT NULL,
  `userToID` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_follows_users` (`userFromID`),
  KEY `FK_follows_users_2` (`userToID`),
  CONSTRAINT `FK_follows_users` FOREIGN KEY (`userFromID`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_follows_users_2` FOREIGN KEY (`userToID`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Sexy = odebírání příspěvků od uživatele bez přátelství, možnost jak nenápadně naznačit že se chce spřátelit';

-- Export dat nebyl vybrán.
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;

