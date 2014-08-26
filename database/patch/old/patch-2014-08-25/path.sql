/* 01 */
/* Vytvoří tabulku pro zprávy uživatelů */
CREATE TABLE `contacts` (
	`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`userID` INT(11) UNSIGNED NULL DEFAULT NULL,
	`email` VARCHAR(50) NULL DEFAULT '',
	`phone` VARCHAR(20) NULL DEFAULT '',
	`text` VARCHAR(500) NULL DEFAULT NULL,
	`viewed` TINYINT(4) NULL DEFAULT '0',
	`created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	INDEX `FK_contacts_users` (`userID`),
	CONSTRAINT `FK_contacts_users` FOREIGN KEY (`userID`) REFERENCES `users` (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB

/* 02 */
/* kaskádní mazání všech relací s tab. images, galleries, user_images a streams*/

ALTER TABLE `images`
	DROP FOREIGN KEY `videoID`,
	DROP FOREIGN KEY `galleryID`;
ALTER TABLE `images`
	ADD CONSTRAINT `videoID` FOREIGN KEY (`videoID`) REFERENCES `videos` (`id`) ON DELETE CASCADE,
	ADD CONSTRAINT `galleryID` FOREIGN KEY (`galleryID`) REFERENCES `galleries` (`id`) ON DELETE CASCADE;

ALTER TABLE `stream_items`
	DROP FOREIGN KEY `stream_items_galleries`,
	DROP FOREIGN KEY `stream_items_ibfk_5`;
ALTER TABLE `stream_items`
	ADD CONSTRAINT `stream_items_galleries` FOREIGN KEY (`galleryID`) REFERENCES `galleries` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	ADD CONSTRAINT `stream_items_ibfk_5` FOREIGN KEY (`userGalleryID`) REFERENCES `user_galleries` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE `galleries`
	DROP FOREIGN KEY `image`;
ALTER TABLE `galleries`
	ADD CONSTRAINT `image` FOREIGN KEY (`lastImageID`) REFERENCES `images` (`id`) ON DELETE CASCADE;

ALTER TABLE `user_images`
	DROP FOREIGN KEY `gallery`;
ALTER TABLE `user_images`
	ADD CONSTRAINT `gallery` FOREIGN KEY (`galleryID`) REFERENCES `user_galleries` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;

/* 03 */
/* Vytvoří tabulku pro soutěžní obrázky */
CREATE TABLE `competitions_images` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`imageID` INT(10) UNSIGNED NULL DEFAULT NULL,
	`userID` INT(10) UNSIGNED NULL DEFAULT NULL,
	`competitionID` INT(10) UNSIGNED NULL DEFAULT NULL,
	`phone` VARCHAR(20) NULL DEFAULT NULL,
	`name` VARCHAR(35) NULL DEFAULT NULL,
	`surname` VARCHAR(35) NULL DEFAULT NULL,
	`allowed` TINYINT(4) NULL DEFAULT '0',
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=12;

/* 04 */
/* Vytvoří tabulku pro soutěže */
CREATE TABLE `users_competitions` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(150) NOT NULL,
	`description` VARCHAR(300) NOT NULL,
	`imageUrl` VARCHAR(50) NOT NULL,
	`current` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`lastImageID` INT(10) UNSIGNED NULL DEFAULT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=3;

/* 05 */
/* Spojí lastImage s tabulkou competitions_images */
ALTER TABLE `users_competitions` 
	ADD CONSTRAINT `FK_users_competitions_user_images` FOREIGN KEY (`lastImageID`) REFERENCES `user_images` (`id`)

/* 06 */
/* Spoji s userem, soutěží a userovo obrázkem */
ALTER TABLE `competitions_images`
	ADD CONSTRAINT `FK_competitions_images_competitions_images` FOREIGN KEY (`competitionID`) REFERENCES `users_competitions` (`id`),
	ADD CONSTRAINT `FK_competitions_images_users` FOREIGN KEY (`userID`) REFERENCES `users` (`id`),
	ADD CONSTRAINT `FK_competitions_images_user_images` FOREIGN KEY (`imageID`) REFERENCES `user_images` (`id`)

/* 07 */
/* Autoinkrement v tab. user_properties - musí se odstranit a zase přidat cizí klíč */

ALTER TABLE `users`
	DROP FOREIGN KEY `FK_users_users_properties`;

ALTER TABLE `users_properties`
	CHANGE COLUMN `id` `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT FIRST;

ALTER TABLE `users`
	ADD CONSTRAINT `FK_users_users_properties` FOREIGN KEY (`propertyID`) REFERENCES `users_properties` (`id`) ON UPDATE CASCADE;

/* 08 */
/* defaultní hodnota u want to meet */

ALTER TABLE `users_properties`
	CHANGE COLUMN `want_to_meet_men` `want_to_meet_men` TINYINT(1) NOT NULL DEFAULT '0' AFTER `deepthrought`,
	CHANGE COLUMN `want_to_meet_women` `want_to_meet_women` TINYINT(1) NOT NULL DEFAULT '0' AFTER `want_to_meet_men`,
	CHANGE COLUMN `want_to_meet_couple` `want_to_meet_couple` TINYINT(1) NOT NULL DEFAULT '0' AFTER `want_to_meet_women`,
	CHANGE COLUMN `want_to_meet_couple_men` `want_to_meet_couple_men` TINYINT(1) NOT NULL DEFAULT '0' AFTER `want_to_meet_couple`,
	CHANGE COLUMN `want_to_meet_couple_women` `want_to_meet_couple_women` TINYINT(1) NOT NULL DEFAULT '0' AFTER `want_to_meet_couple_men`,
	CHANGE COLUMN `want_to_meet_group` `want_to_meet_group` TINYINT(1) NOT NULL DEFAULT '0' AFTER `want_to_meet_couple_women`;

/* změna uživatele - věku  */

ALTER TABLE `users_properties`
	ALTER `age` DROP DEFAULT;
ALTER TABLE `users_properties`
	CHANGE COLUMN `age` `age` INT(11) UNSIGNED NOT NULL AFTER `id`;

ALTER TABLE `users_properties`
	ALTER `age` DROP DEFAULT;
ALTER TABLE `users_properties`
	CHANGE COLUMN `age` `age` TINYINT UNSIGNED NOT NULL AFTER `id`;

/* 09 */
/* smazání sloupce interested in - již je nahrazen sloupci want_to .. */
ALTER TABLE `users_properties`
	DROP COLUMN `interested_in`;

/* 10 */
ALTER TABLE `users_competitions`
	DROP FOREIGN KEY `FK_users_competitions_user_images`;
ALTER TABLE `users_competitions`
	ADD CONSTRAINT `FK_users_competitions_user_images` FOREIGN KEY (`lastImageID`) REFERENCES `user_images` (`id`) ON UPDATE SET NULL ON DELETE SET NULL;

ALTER TABLE `competitions_images`
	DROP FOREIGN KEY `FK_competitions_images_competitions_images`,
	DROP FOREIGN KEY `FK_competitions_images_users`,
	DROP FOREIGN KEY `FK_competitions_images_user_images`;
ALTER TABLE `competitions_images`
	ADD CONSTRAINT `FK_competitions_images_competitions_images` FOREIGN KEY (`competitionID`) REFERENCES `users_competitions` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	ADD CONSTRAINT `FK_competitions_images_users` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON UPDATE SET NULL ON DELETE SET NULL,
	ADD CONSTRAINT `FK_competitions_images_user_images` FOREIGN KEY (`imageID`) REFERENCES `user_images` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;

/* 12 */
/* změna názvu sloupečku */
ALTER TABLE `user_images`
	CHANGE COLUMN `allow` `approved` TINYINT(1) NULL DEFAULT '0' AFTER `galleryID`;

/* 13 */
/* vytvoří tabulku přátel */

CREATE TABLE `friends` (
	`userID1` INT(11) UNSIGNED NOT NULL,
	`userID2` INT(11) UNSIGNED NOT NULL,
	PRIMARY KEY (`userID1`, `userID2`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

ALTER TABLE `friends`
	ADD CONSTRAINT `userID1` FOREIGN KEY (`userID1`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	ADD CONSTRAINT `userID2` FOREIGN KEY (`userID2`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE `friends`
	COMMENT='Přátelství funguje mezi přáteli A a B, jen když je vazba A,B a B,A.';

ALTER TABLE `friends`
	ADD COLUMN `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT FIRST,
	DROP PRIMARY KEY,
	ADD PRIMARY KEY (`id`),
	ADD INDEX `userID1` (`userID1`);


/* vytvoří tabulku žádostí o přátelství */
CREATE TABLE `friendRequest` (
	`userIDFrom` INT(11) UNSIGNED NOT NULL,
	`userIDTo` INT(11) UNSIGNED NOT NULL,
	`message` VARCHAR(200) NOT NULL,
	`create` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

ALTER TABLE `friendrequest`
	ADD COLUMN `id` INT(11) UNSIGNED NOT NULL FIRST;

ALTER TABLE `friendrequest`
	CHANGE COLUMN `id` `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT FIRST,
	ADD PRIMARY KEY (`id`);

ALTER TABLE `friendrequest`
	ADD CONSTRAINT `FK_friendrequest_users` FOREIGN KEY (`userIDFrom`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	ADD CONSTRAINT `FK_friendrequest_users_2` FOREIGN KEY (`userIDTo`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;

/* vytvoří tabulku sledování */

CREATE TABLE `follows` (
	`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`userIDFrom` INT(11) UNSIGNED NOT NULL,
	`userIDTo` INT(11) UNSIGNED NOT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

ALTER TABLE `follows`
	ADD CONSTRAINT `FK_follows_users` FOREIGN KEY (`userIDFrom`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	ADD CONSTRAINT `FK_follows_users_2` FOREIGN KEY (`userIDTo`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE `follows`
	COMMENT='Sledování = odebírání příspěvků od uživatele bez přátelství';

ALTER TABLE `follows`
	COMMENT='Sexy = odebírání příspěvků od uživatele bez přátelství, možnost jak nenápadně naznačit že se chce spřátelit';
RENAME TABLE `follows` TO `you_are_sexy`;

ALTER TABLE `friends`
	ALTER `userID1` DROP DEFAULT,
	ALTER `userID2` DROP DEFAULT;
ALTER TABLE `friends`
	CHANGE COLUMN `userID1` `user1ID` INT(11) UNSIGNED NOT NULL AFTER `id`,
	CHANGE COLUMN `userID2` `user2ID` INT(11) UNSIGNED NOT NULL AFTER `user1ID`;

ALTER TABLE `you_are_sexy`
	ALTER `userIDFrom` DROP DEFAULT,
	ALTER `userIDTo` DROP DEFAULT;
ALTER TABLE `you_are_sexy`
	CHANGE COLUMN `userIDFrom` `userFromID` INT(11) UNSIGNED NOT NULL AFTER `id`,
	CHANGE COLUMN `userIDTo` `userToID` INT(11) UNSIGNED NOT NULL AFTER `userFromID`;

ALTER TABLE `friendrequest`
	ALTER `userIDFrom` DROP DEFAULT,
	ALTER `userIDTo` DROP DEFAULT;
ALTER TABLE `friendrequest`
	CHANGE COLUMN `userIDFrom` `userFromID` INT(11) UNSIGNED NOT NULL AFTER `id`,
	CHANGE COLUMN `userIDTo` `userToID` INT(11) UNSIGNED NOT NULL AFTER `userFromID`;


/* 14 */
/* oprava cizích klíčů v tab. users */
ALTER TABLE `users`
	DROP FOREIGN KEY `FK_users_user_images`,
	DROP FOREIGN KEY `FK_users_users_properties`;
ALTER TABLE `users`
	ADD CONSTRAINT `FK_users_user_images` FOREIGN KEY (`profilFotoID`) REFERENCES `user_images` (`id`) ON UPDATE CASCADE ON DELETE SET NULL,
	ADD CONSTRAINT `FK_users_users_properties` FOREIGN KEY (`propertyID`) REFERENCES `users_properties` (`id`) ON UPDATE CASCADE ON DELETE SET NULL;

/* 15 */
/* upraví tabulku status, přejmenuje políčko text a zmení jeho typ */
ALTER TABLE `status`
	CHANGE COLUMN `text` `message` TEXT NULL DEFAULT NULL;
ALTER TABLE `status`
	DROP FOREIGN KEY `FK_status_users`;
ALTER TABLE `status`	
ADD CONSTRAINT `FK_status_users` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;

/* 16 */
/* Přidá políčko pro statusID a spojí ho s tabulkou status */
ALTER TABLE `stream_items`
	ADD COLUMN `statusID` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `galleryID`,
	ADD CONSTRAINT `FK_stream_items_status` FOREIGN KEY (`statusID`) REFERENCES `status` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;

/* 17 */
/* Vytvoří tabulku pro tickety k obnově hesla uživatele */
CREATE TABLE `user_change_password` (
	`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`userID` INT(11) UNSIGNED NULL DEFAULT NULL,
	`ticket` VARCHAR(30) NULL DEFAULT NULL,
	`create` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	INDEX `FK_ user_change_password_users` (`userID`),
	CONSTRAINT `FK_ user_change_password_users` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

/* 18 */
/* přidá sloupeček pro lajky k obrázkům */
ALTER TABLE `user_images`
	ADD COLUMN `likes` INT(5) UNSIGNED NOT NULL DEFAULT '0';

/* 19 */
/* Vytvoří vazební tabulku pro lajky obrázků */
CREATE TABLE `image_likes` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`userID` INT(10) UNSIGNED NULL DEFAULT NULL,
	`imageID` INT(10) UNSIGNED NULL DEFAULT NULL,
	PRIMARY KEY (`id`),
	INDEX `FK_image_likes_users` (`userID`),
	INDEX `FK_image_likes_user_images` (`imageID`),
	CONSTRAINT `FK_image_likes_users` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT `FK_image_likes_user_images` FOREIGN KEY (`imageID`) REFERENCES `user_images` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
)
ENGINE=InnoDB
AUTO_INCREMENT=14;

/* 20 */
/* Přidá tabulky pro města, okresy, a kraje */
CREATE TABLE `city` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(35) NOT NULL,
	`districtID` INT(10) UNSIGNED NULL DEFAULT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

CREATE TABLE `district` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(35) NULL DEFAULT NULL,
	`regionID` INT(10) UNSIGNED NULL DEFAULT NULL,
	PRIMARY KEY (`id`)
)
COMMENT='okres'
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

CREATE TABLE `region` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(35) NULL DEFAULT NULL,
	PRIMARY KEY (`id`)
)
COMMENT='kraj'
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

/* 21 */
/* propojí města a okrasy a kraje */
ALTER TABLE city
	ADD CONSTRAINT `FK_city_district` FOREIGN KEY (`districtID`) REFERENCES `district` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE district
	ADD CONSTRAINT `FK_district_region` FOREIGN KEY (`regionID`) REFERENCES `region` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;

/* 22 */
/* Přidá sloupeček pro město, okres a region */
ALTER TABLE `users_properties`
	ADD COLUMN `cityID` INT UNSIGNED NULL DEFAULT NULL AFTER `want_to_meet_group`,
	ADD COLUMN `districtID` INT UNSIGNED NULL DEFAULT NULL AFTER `cityID`,
	ADD COLUMN `regionID` INT UNSIGNED NULL DEFAULT NULL AFTER `districtID`,
	ADD CONSTRAINT `FK_users_properties_city` FOREIGN KEY (`cityID`) REFERENCES `city` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	ADD CONSTRAINT `FK_users_properties_district` FOREIGN KEY (`districtID`) REFERENCES `district` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	ADD CONSTRAINT `FK_users_properties_region` FOREIGN KEY (`regionID`) REFERENCES `region` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;
