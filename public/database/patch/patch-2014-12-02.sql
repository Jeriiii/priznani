/************** 01-bloked-users.sql **************/
/* blokovaní uživatelé */
CREATE TABLE `users_bloked` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`ownerID` INT UNSIGNED NOT NULL,
	`blokedID` INT UNSIGNED NOT NULL,
	PRIMARY KEY (`id`),
	INDEX `ownerID` (`ownerID`),
	INDEX `blokedID` (`blokedID`),
	CONSTRAINT `FK_owner_users` FOREIGN KEY (`ownerID`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT `FK_bloked_users_2` FOREIGN KEY (`blokedID`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;


/************** 22-payment.sql **************/
/* Přidání vlastností platby */
ALTER TABLE `payments`
	ALTER `create` DROP DEFAULT;
ALTER TABLE `payments`
	ADD COLUMN `type` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT '1 = premium. 2 = gold' AFTER `userID`,
	CHANGE COLUMN `create` `from` DATETIME NOT NULL AFTER `type`,
	ADD COLUMN `to` DATETIME NOT NULL AFTER `from`;


/************** 23-POS-341-verification-gallery.sql **************/
/* Přidá políčko pro označení verifikační galerie */
ALTER TABLE `user_galleries`
	ADD COLUMN `verification_gallery` TINYINT(1) NULL DEFAULT '0' AFTER `profil_gallery`;
/************** 24-POS-339-user-galleries.sql **************/
/* přidá sloupčeky o informaci, zda se jedná o privátní galerii a zda ni mohou kamarádi */
ALTER TABLE `user_galleries`
	ADD COLUMN `private` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `verification_gallery`,
	ADD COLUMN `allow_friends` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' AFTER `private`;
/************** 24-POS-366-coins.sql **************/
ALTER TABLE `users_properties`
	ADD COLUMN `coins` FLOAT(11) UNSIGNED NOT NULL DEFAULT '0' AFTER `id`;
	
ALTER TABLE `chat_messages`
	ADD COLUMN `checked_by_cron` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0/1 zda už tento sloupec prošel cron přidávající zlatky' AFTER `type`;
/************** 25-POS-339-users-allowed-galleries.sql **************/
/* tabulka pro zaznamenávání povolených uživatelů pro dané galerie */
CREATE TABLE `users_allowed_galleries` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`userID` INT(10) UNSIGNED NULL DEFAULT NULL,
	`galleryID` INT(10) UNSIGNED NULL DEFAULT NULL,
	PRIMARY KEY (`id`),
	INDEX `FK_user_allowed_galleries_users` (`userID`),
	INDEX `FK_user_allowed_galleries_user_galleries` (`galleryID`),
	CONSTRAINT `FK_user_allowed_galleries_users` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT `FK_user_allowed_galleries_user_galleries` FOREIGN KEY (`galleryID`) REFERENCES `user_galleries` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
)
ENGINE=InnoDB;
/************** 26-POS-354-user-images.sql **************/
/* Přidá políčko pro označení jako intimní */
ALTER TABLE `user_images`
	ADD COLUMN `intim` TINYINT(1) NULL DEFAULT '0' AFTER `comments`;
/************** 26-POS-369-reward-for-sign-in.sql **************/
ALTER TABLE `users`
	ADD COLUMN `last_signed_in` DATE NULL DEFAULT NULL AFTER `last_active`;
	
ALTER TABLE `users`
	CHANGE COLUMN `last_signed_in` `first_signed_day_streak` DATE NULL DEFAULT NULL COMMENT 'den kdy se přihlásil a od té doby se každý den stavil' AFTER `last_active`;

ALTER TABLE `users`
	ADD COLUMN `last_signed_in` DATE NULL DEFAULT NULL AFTER `last_active`;
/************** 27-POS-354-comment-statuses.sql **************/
/* vytoří tabulku pro komentáře statusu */
CREATE TABLE `comment_statuses` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`userID` INT(10) UNSIGNED NOT NULL,
	`statusID` INT(10) UNSIGNED NULL DEFAULT NULL,
	`likes` SMALLINT(4) UNSIGNED NULL DEFAULT '0',
	`comment` TEXT NULL,
	PRIMARY KEY (`id`),
	INDEX `FK_comment_statuses_users` (`userID`),
	INDEX `FK_comment_statuses_status` (`statusID`),
	CONSTRAINT `FK_comment_statuses_users` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT `FK_comment_statuses_status` FOREIGN KEY (`statusID`) REFERENCES `status` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;
/************** 28-POS-354-status.sql **************/
/* přidá políčko pro počet lajků statusu */
ALTER TABLE `status`
	ADD COLUMN `comments` INT(5) UNSIGNED NOT NULL DEFAULT '0' AFTER `likes`;
/************** 29-POS-354-like-status-comments.sql **************/
/* tabulka pro vazbu mezi uživatelem a statusem, udržuje info kdo co lajkl za satus */
CREATE TABLE `like_status_comments` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`userID` INT(10) UNSIGNED NULL DEFAULT NULL,
	`commentID` INT(10) UNSIGNED NULL DEFAULT NULL,
	PRIMARY KEY (`id`),
	INDEX `FK_like_status_comments_users` (`userID`),
	INDEX `FK_like_status_comments_comment_statuses` (`commentID`),
	CONSTRAINT `FK_like_status_comments_users` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT `FK_like_status_comments_comment_statuses` FOREIGN KEY (`commentID`) REFERENCES `comment_statuses` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;
/************** 30-POS-354-comment-confessions.sql **************/
/* tabulka pro komentáře přiznání */
CREATE TABLE `comment_confessions` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`userID` INT(10) UNSIGNED NOT NULL,
	`confessionID` INT(10) UNSIGNED NULL DEFAULT NULL,
	`likes` SMALLINT(4) UNSIGNED NULL DEFAULT '0',
	`comment` TEXT NULL,
	PRIMARY KEY (`id`),
	INDEX `FK_comment_confessions_users` (`userID`),
	INDEX `FK_comment_confessions_confessions` (`confessionID`),
	CONSTRAINT `FK_comment_confessions_users` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT `FK_comment_confessions_confessions` FOREIGN KEY (`confessionID`) REFERENCES `confessions` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;
/************** 31-POS-354-like-confession-comments.sql **************/
/* tabulka pro lajkování kometářů u přiznání */
CREATE TABLE `like_confession_comments` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`userID` INT(10) UNSIGNED NULL DEFAULT NULL,
	`commentID` INT(10) UNSIGNED NULL DEFAULT NULL,
	PRIMARY KEY (`id`),
	INDEX `FK_like_confession_comments_users` (`userID`),
	INDEX `FK_like_confession_comments_comment_confessions` (`commentID`),
	CONSTRAINT `FK_like_confession_comments_users` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT `FK_like_confession_comments_comment_confessions` FOREIGN KEY (`commentID`) REFERENCES `comment_confessions` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;
/************** 32-POS-354-like-confessions.sql **************/
/* tabulka na lajkování řiznání */
CREATE TABLE `like_confessions` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`userID` INT(10) UNSIGNED NULL DEFAULT NULL,
	`confessionID` INT(10) UNSIGNED NULL DEFAULT NULL,
	PRIMARY KEY (`id`),
	INDEX `FK_like_confessions_users` (`userID`),
	INDEX `FK_like_confessions_confessions` (`confessionID`),
	CONSTRAINT `FK_like_confessions_confessions` FOREIGN KEY (`confessionID`) REFERENCES `confessions` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT `FK_like_confessions_users` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;
/************** 33-POS-354-confessions.sql **************/
/* přidání políček pro počet lajků a komentářů u přiznání */
ALTER TABLE `confessions`
	ADD COLUMN `likes` INT(5) UNSIGNED NOT NULL DEFAULT '0' AFTER `adminID`,
	ADD COLUMN `comments` INT(5) UNSIGNED NOT NULL DEFAULT '0' AFTER `likes`;

/************** 34-POS-355-verification-photo-request.sql **************/
/* tabulka na data o pořadavacích na ověřovací foto */
CREATE TABLE `verification_photo_requests` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`userID` INT(10) UNSIGNED NULL DEFAULT NULL,
	`user2ID` INT(10) UNSIGNED NULL DEFAULT NULL,
	`accepted` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	INDEX `FK_users_users` (`userID`),
	INDEX `FK_users_users_2` (`user2ID`),
	CONSTRAINT `FK_users_users` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT `FK_users_users_2` FOREIGN KEY (`user2ID`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

/************** 35-POS-355-users.sql **************/
/* políčko na verifikaci usera */
ALTER TABLE `users`
	ADD COLUMN `verified` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `wasCategoryChanged`;
/************** 36-POS-user-images.sql **************/
/* políčko na označení fotky jako odmítnuté */
ALTER TABLE `user_images`
	ADD COLUMN `rejected` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `comments`;
/************** 37-vigors.sql **************/
/* vytvoří znamení */
CREATE TABLE IF NOT EXISTS `enum_vigors` (
  `id` tinyint(2) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;

/* propojení s tabulkou properties */
ALTER TABLE `users_properties`
	ADD COLUMN `vigor` TINYINT(2) UNSIGNED NULL DEFAULT NULL AFTER `hair_colour`;

ALTER TABLE `users_properties`
	ADD CONSTRAINT `FK_users_properties_enum_vigors` FOREIGN KEY (`vigor`) REFERENCES `enum_vigors` (`id`) ON UPDATE SET NULL ON DELETE SET NULL;

/* propojen9 s tabulkou couples */
ALTER TABLE `couple`
	ADD COLUMN `vigor` TINYINT(2) UNSIGNED NULL DEFAULT NULL AFTER `age`,
	ADD CONSTRAINT `FK_couple_enum_vigors` FOREIGN KEY (`vigor`) REFERENCES `enum_vigors` (`id`) ON UPDATE SET NULL ON DELETE SET NULL;


/************** 38-POS-407-user-photos.sql **************/
/* přidá uživ. fotkám možnost zpětně zkontrolovat schválenou fotku */

ALTER TABLE `user_images`
	ADD COLUMN `checkApproved` TINYINT(1) NULL DEFAULT '0' AFTER `intim`;

/************** 39-POS-414-errors.sql **************/
/* přidání sloupce do enumu s českýma naázvama */
ALTER TABLE `enum_property`
	ADD COLUMN `czname` VARCHAR(15) NOT NULL AFTER `name`;

/************** 40-POS-349-scoreListener.sql **************/
ALTER TABLE `users_properties`
	ADD COLUMN `score` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `coins`;
/************** 40-POS-415-events.sql **************/
/* změna názvu sloupce u aktivit */
ALTER TABLE `activities`
	CHANGE COLUMN `event_type` `type` VARCHAR(50) NULL DEFAULT NULL AFTER `id`;

/* propejení aktivit s komentářema */
ALTER TABLE `activities`
	ADD COLUMN `commentID` INT(11) UNSIGNED NULL DEFAULT NULL AFTER `statusID`,
	ADD CONSTRAINT `FK_activities_like_comments` FOREIGN KEY (`commentID`) REFERENCES `like_comments` (`id`) ON UPDATE CASCADE ON DELETE NO ACTION;

ALTER TABLE `activities`
	DROP FOREIGN KEY `FK_activities_like_comments`;
ALTER TABLE `activities`
	ADD CONSTRAINT `FK_activities_like_comments` FOREIGN KEY (`commentID`) REFERENCES `comment_images` (`id`) ON UPDATE CASCADE ON DELETE NO ACTION;
RENAME TABLE `like_comments` TO `like_image_comments`;

ALTER TABLE `activities`
	CHANGE COLUMN `commentID` `commentImageID` INT(11) UNSIGNED NULL DEFAULT NULL AFTER `statusID`;
/************** 41-POS-450-reg-from.sql **************/
/* tyto tri sloupce maji vychozi hodnotu null */
ALTER TABLE `couple`
	CHANGE COLUMN `smoke` `smoke` VARCHAR(11) NULL DEFAULT NULL AFTER `bra_size`,
	CHANGE COLUMN `drink` `drink` VARCHAR(11) NULL DEFAULT NULL AFTER `smoke`,
	CHANGE COLUMN `graduation` `graduation` VARCHAR(11) NULL DEFAULT NULL AFTER `drink`;

/* prodloužení sloupce marital_state na 18 znaků */
ALTER TABLE `enum_marital_state`
	CHANGE COLUMN `marital_state` `marital_state` VARCHAR(18) NULL DEFAULT NULL AFTER `id`;


/************** 42-POS-485-friend-request.sql **************/
/* propojení aktivit a žádostí o přátelství */
ALTER TABLE `activities`
	ADD COLUMN `friendRequest` INT(11) UNSIGNED NULL DEFAULT NULL AFTER `commentImageID`,
	ADD CONSTRAINT `FK_activities_friendrequest` FOREIGN KEY (`friendRequest`) REFERENCES `friendrequest` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE `activities`
	CHANGE COLUMN `friendRequest` `friendRequestID` INT(11) UNSIGNED NULL DEFAULT NULL AFTER `commentImageID`;
/************** 43-old-users.sql **************/
/* starší registrovaní uživatelé */

CREATE TABLE IF NOT EXISTS `old_users` (
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=13022 ;

