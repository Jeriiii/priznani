/************** 01-POS-148-stream-categories.sql **************/
/* vytvoření tabulky kategorií a jejího primárního klíče*/
CREATE TABLE `stream_categories` (
	`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

/* přidání kategorií do tabulky */
ALTER TABLE `stream_categories`
	ADD COLUMN `want_to_meet_group` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `want_to_meet_couple_women` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `want_to_meet_couple_men` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `want_to_meet_couple` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `want_to_meet_women` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `want_to_meet_men` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `fisting` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `petting` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `sex_massage` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `piss` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `oral` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `cum` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `swallow` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `bdsm` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `group` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `anal` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `threesome` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0';
	
	/* doplnění rozsahových hodnot	*/
ALTER TABLE `stream_items`
	ADD COLUMN `age` DATETIME,
	ADD COLUMN `tallness` INT(6) UNSIGNED NOT NULL DEFAULT '0';

/* vytvoření indexů (není tam vše, protože maximální počet částí klíče je 16)
	Vyloučeny byly sloupce, kde se určuje, koho chce poznat. Tam se totiž předpokládá, že to bude jen jeden max dva z nich.
*/
ALTER TABLE `stream_categories`
	ADD INDEX `stream_categories_heavy_key` (`oral`, `sex_massage`, `anal`, `threesome`, `group`, `fisting`, `petting`, `piss`, `cum`, `swallow`, `bdsm`);
/* pro tallness index přidán není - příliš nízká selektivita */
ALTER TABLE `stream_items`
	ADD INDEX `age` (`age`);
	
	
/* přidání relace příspěvku na kategorie */
ALTER TABLE `stream_items`
	ADD COLUMN `categoryID` INT(11) UNSIGNED NULL DEFAULT NULL AFTER `userID`,
	ADD INDEX `categoryID` (`categoryID`),
	ADD CONSTRAINT `categoryIDtoBitmap` FOREIGN KEY (`categoryID`) REFERENCES `stream_categories` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;


/* přidání klíče jako preference uživatele */
ALTER TABLE `users_properties`
	ADD COLUMN `preferencesID` INT(11) UNSIGNED NULL AFTER `hair_colour`,
	ADD INDEX `preferencesID` (`preferencesID`),
	ADD CONSTRAINT `FK_users_properties_stream_items_preferences` FOREIGN KEY (`preferencesID`) REFERENCES `stream_categories` (`id`) ON UPDATE SET NULL ON DELETE SET NULL;

/************** 02-POS-251-enum-users-propesties.sql **************/
/* enum MARITAL_STATE*/

CREATE TABLE IF NOT EXISTS `enum_marital_state` (
	`id` TINYINT(2) UNSIGNED NOT NULL AUTO_INCREMENT,
	`marital_state` VARCHAR(15) NULL DEFAULT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

ALTER TABLE `users_properties` DROP COLUMN `marital_state`;

/* zde musí být nastavena default hodnota 1 -> kvůli NOT NULL cizímu klíči*/
ALTER TABLE `users_properties` ADD COLUMN `marital_state` TINYINT(2) UNSIGNED NULL;
/* cizí klíč nemůže být nastaven při update/delete SET NULL, je to v rozporu s "not null" vlastností klíče, bude tedy defaultně RESTRICT */
ALTER TABLE `users_properties` ADD CONSTRAINT `FK_users_properties_enum_marital_state` FOREIGN KEY (`marital_state`) REFERENCES `enum_marital_state` (`id`);

/* enum ORIENTATION*/

CREATE TABLE `enum_orientation` (
	`id` TINYINT(2) UNSIGNED NOT NULL AUTO_INCREMENT,
	`orientation` VARCHAR(25) NULL DEFAULT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

ALTER TABLE `users_properties` DROP COLUMN `orientation`;
ALTER TABLE `users_properties` ADD COLUMN `orientation` TINYINT(2) UNSIGNED NULL;
ALTER TABLE `users_properties` ADD CONSTRAINT `FK_users_properties_enum_orientation` FOREIGN KEY (`orientation`) REFERENCES `enum_orientation` (`id`);

/* enum SHAPE */
 
CREATE TABLE `enum_shape` (
	`id` TINYINT(2) UNSIGNED NOT NULL AUTO_INCREMENT,
	`shape` VARCHAR(15) NULL DEFAULT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

ALTER TABLE `users_properties` DROP COLUMN `shape`;
ALTER TABLE `users_properties` ADD COLUMN `shape` TINYINT(2) UNSIGNED NULL;
ALTER TABLE `users_properties` ADD CONSTRAINT `FK_users_properties_enum_shape` FOREIGN KEY (`shape`) REFERENCES `enum_shape` (`id`);

/* enum PENIS_LENGTH*/

CREATE TABLE `enum_penis_length` (
	`id` TINYINT(2) UNSIGNED NOT NULL AUTO_INCREMENT,
	`penis_length` VARCHAR(25) NULL DEFAULT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

ALTER TABLE `users_properties` DROP COLUMN `penis_length`;
ALTER TABLE `users_properties` ADD COLUMN `penis_length` TINYINT(2) UNSIGNED NULL;
ALTER TABLE `users_properties` ADD CONSTRAINT `FK_users_properties_enum_penis_length` FOREIGN KEY (`penis_length`) REFERENCES `enum_penis_length` (`id`);

/* enum PENIS_WIDTH*/

CREATE TABLE `enum_penis_width` (
	`id` TINYINT(2) UNSIGNED NOT NULL AUTO_INCREMENT,
	`penis_width` VARCHAR(15) NULL DEFAULT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

ALTER TABLE `users_properties` DROP COLUMN `penis_width`;
ALTER TABLE `users_properties` ADD COLUMN `penis_width` TINYINT(2) UNSIGNED NULL;
ALTER TABLE `users_properties` ADD CONSTRAINT `FK_users_properties_enum_penis_width` FOREIGN KEY (`penis_width`) REFERENCES `enum_penis_width` (`id`);

/* enum DRINK*/
 
CREATE TABLE `enum_drink` (
	`id` TINYINT(2) UNSIGNED NOT NULL AUTO_INCREMENT,
	`drink` VARCHAR(15) NULL DEFAULT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

ALTER TABLE `users_properties` DROP COLUMN `drink`;
ALTER TABLE `users_properties` ADD COLUMN `drink` TINYINT(2) UNSIGNED NULL;
ALTER TABLE `users_properties` ADD CONSTRAINT `FK_users_properties_enum_drink` FOREIGN KEY (`drink`) REFERENCES `enum_drink` (`id`);


/* enum GRADUATION*/
 
CREATE TABLE `enum_graduation` (
	`id` TINYINT(2) UNSIGNED NOT NULL AUTO_INCREMENT,
	`graduation` VARCHAR(15) NULL DEFAULT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

ALTER TABLE `users_properties` DROP COLUMN `graduation`;
ALTER TABLE `users_properties` ADD COLUMN `graduation` TINYINT(2) UNSIGNED NULL;
ALTER TABLE `users_properties` ADD CONSTRAINT `FK_users_properties_enum_graduation` FOREIGN KEY (`graduation`) REFERENCES `enum_graduation` (`id`);


/* enum BRA_SIZE*/
 
CREATE TABLE `enum_bra_size` (
	`id` TINYINT(2) UNSIGNED NOT NULL AUTO_INCREMENT,
	`bra_size` VARCHAR(15) NULL DEFAULT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

ALTER TABLE `users_properties` DROP COLUMN `bra_size`;
ALTER TABLE `users_properties` ADD COLUMN `bra_size` TINYINT(2) UNSIGNED NULL;
ALTER TABLE `users_properties` ADD CONSTRAINT `FK_users_properties_enum_bra_size` FOREIGN KEY (`bra_size`) REFERENCES `enum_bra_size` (`id`);

/* enum SMOKE */
 
CREATE TABLE `enum_smoke` (
	`id` TINYINT(2) UNSIGNED NOT NULL AUTO_INCREMENT,
	`smoke` VARCHAR(15) NULL DEFAULT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

ALTER TABLE `users_properties` DROP COLUMN `smoke`;
ALTER TABLE `users_properties` ADD COLUMN `smoke` TINYINT(2) UNSIGNED NULL;
ALTER TABLE `users_properties` ADD CONSTRAINT `FK_users_properties_enum_smoke` FOREIGN KEY (`smoke`) REFERENCES `enum_smoke` (`id`);

/************** 03-POS-265-age-datetime-to-date.sql **************/
ALTER TABLE `stream_items` CHANGE COLUMN `age` `age` DATE NULL DEFAULT NULL AFTER `create`;
/************** 04-POS-268-enum-hair-colour.sql **************/
/* enum HAIR_COLOUR*/

CREATE TABLE IF NOT EXISTS `enum_hair_colour` (
	`id` TINYINT(2) UNSIGNED NOT NULL AUTO_INCREMENT,
	`hair_colour` VARCHAR(15) NULL DEFAULT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

ALTER TABLE `users_properties` DROP COLUMN `hair_colour`;
ALTER TABLE `users_properties` ADD COLUMN `hair_colour` TINYINT(2) UNSIGNED NULL;

ALTER TABLE `users_properties` ADD CONSTRAINT `FK_users_properties_enum_hair_colour` FOREIGN KEY (`hair_colour`) REFERENCES `enum_hair_colour` (`id`);
/************** 05-POS-273-activites.sql **************/
/* upraví cizí klíče v tabulce acitivities na ON CASCADE */
ALTER TABLE `activities`
	DROP FOREIGN KEY `FK_activities_status`,
	DROP FOREIGN KEY `FK_activities_users`,
	DROP FOREIGN KEY `FK_activities_users_2`,
	DROP FOREIGN KEY `FK_activities_user_images`;
ALTER TABLE `activities`
	ADD CONSTRAINT `FK_activities_status` FOREIGN KEY (`statusID`) REFERENCES `status` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	ADD CONSTRAINT `FK_activities_users` FOREIGN KEY (`event_ownerID`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	ADD CONSTRAINT `FK_activities_users_2` FOREIGN KEY (`event_creatorID`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	ADD CONSTRAINT `FK_activities_user_images` FOREIGN KEY (`imageID`) REFERENCES `user_images` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;

/************** 06-POS-293-like-images.sql **************/
/* Přejmenuje tabulku image_likes na like_images */
RENAME TABLE `image_likes` TO `like_images`;
/************** 07-POS-295-like-statuses.sql **************/
/* vytvoří vazební tabulku na lajky statusů */
CREATE TABLE `like_statuses` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`userID` INT UNSIGNED NULL,
	`statusID` INT UNSIGNED NULL,
	PRIMARY KEY (`id`),
	CONSTRAINT `FK__users` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT `FK__status` FOREIGN KEY (`statusID`) REFERENCES `status` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;
/************** 08-POS-295-status.sql **************/
/* přidání políčka pro počet lajků ke statusu */
ALTER TABLE `status`
	ADD COLUMN `likes` INT(5) UNSIGNED NULL DEFAULT '0' AFTER `message`;
/************** 09-POS-305-comment-images.sql **************/
/* vytvoří tabulku na komentáře k obrázkům */
CREATE TABLE `comment_images` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	PRIMARY KEY (`id`),
	`imageID` INT UNSIGNED NULL,
	`likes` SMALLINT(4) UNSIGNED NULL DEFAULT '0',
	`comment` TEXT NULL,
	CONSTRAINT `FK__user_images` FOREIGN KEY (`imageID`) REFERENCES `user_images` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;
/************** 09-repairs.sql **************/
/* nastavení akcí při smazání/změně cizího klíče */
ALTER TABLE `contacts`
	DROP FOREIGN KEY `FK_contacts_users`;
ALTER TABLE `contacts`
	ADD CONSTRAINT `FK_contacts_users` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE `galleries`
	DROP FOREIGN KEY `image`;
ALTER TABLE `galleries`
	ADD CONSTRAINT `image` FOREIGN KEY (`lastImageID`) REFERENCES `images` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE `payments`
	DROP FOREIGN KEY `FK_payments_users`;
ALTER TABLE `payments`
	ADD CONSTRAINT `FK_payments_users` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE `users_properties`
	DROP FOREIGN KEY `FK_users_properties_enum_hair_colour`,
	DROP FOREIGN KEY `FK_users_properties_enum_bra_size`,
	DROP FOREIGN KEY `FK_users_properties_enum_drink`,
	DROP FOREIGN KEY `FK_users_properties_enum_graduation`,
	DROP FOREIGN KEY `FK_users_properties_enum_marital_state`,
	DROP FOREIGN KEY `FK_users_properties_enum_orientation`,
	DROP FOREIGN KEY `FK_users_properties_enum_penis_length`,
	DROP FOREIGN KEY `FK_users_properties_enum_penis_width`,
	DROP FOREIGN KEY `FK_users_properties_enum_shape`,
	DROP FOREIGN KEY `FK_users_properties_enum_smoke`;
ALTER TABLE `users_properties`
	ADD CONSTRAINT `FK_users_properties_enum_hair_colour` FOREIGN KEY (`hair_colour`) REFERENCES `enum_hair_colour` (`id`) ON UPDATE SET NULL ON DELETE SET NULL,
	ADD CONSTRAINT `FK_users_properties_enum_bra_size` FOREIGN KEY (`bra_size`) REFERENCES `enum_bra_size` (`id`) ON UPDATE SET NULL ON DELETE SET NULL,
	ADD CONSTRAINT `FK_users_properties_enum_drink` FOREIGN KEY (`drink`) REFERENCES `enum_drink` (`id`) ON UPDATE SET NULL ON DELETE SET NULL,
	ADD CONSTRAINT `FK_users_properties_enum_graduation` FOREIGN KEY (`graduation`) REFERENCES `enum_graduation` (`id`) ON UPDATE SET NULL ON DELETE SET NULL,
	ADD CONSTRAINT `FK_users_properties_enum_marital_state` FOREIGN KEY (`marital_state`) REFERENCES `enum_marital_state` (`id`) ON UPDATE SET NULL ON DELETE SET NULL,
	ADD CONSTRAINT `FK_users_properties_enum_orientation` FOREIGN KEY (`orientation`) REFERENCES `enum_orientation` (`id`) ON UPDATE SET NULL ON DELETE SET NULL,
	ADD CONSTRAINT `FK_users_properties_enum_penis_length` FOREIGN KEY (`penis_length`) REFERENCES `enum_penis_length` (`id`) ON UPDATE SET NULL ON DELETE SET NULL,
	ADD CONSTRAINT `FK_users_properties_enum_penis_width` FOREIGN KEY (`penis_width`) REFERENCES `enum_penis_width` (`id`) ON UPDATE SET NULL ON DELETE SET NULL,
	ADD CONSTRAINT `FK_users_properties_enum_shape` FOREIGN KEY (`shape`) REFERENCES `enum_shape` (`id`) ON UPDATE SET NULL ON DELETE SET NULL,
	ADD CONSTRAINT `FK_users_properties_enum_smoke` FOREIGN KEY (`smoke`) REFERENCES `enum_smoke` (`id`) ON UPDATE SET NULL ON DELETE SET NULL;


/************** 10-POS-305-user-images.sql **************/
/* přidá políčko na počet komentářů */
ALTER TABLE `user_images`
	ADD COLUMN `comments` INT(5) UNSIGNED NOT NULL DEFAULT '0' AFTER `likes`;
/************** 11-POS-156-chat-database.sql **************/

/* vytvoří tabulku na zprávy i s komentáři*/
-- Dumping structure for table pos.chat_messages
CREATE TABLE IF NOT EXISTS `chat_messages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_sender` int(11) unsigned NOT NULL COMMENT 'kdo to poslal',
  `id_recipient` int(11) unsigned NOT NULL COMMENT 'komu to poslal',
  `text` text,
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 - klasicka zprava',
  `readed` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 - neprecteno, 1/jine - precteno',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8;
/* přidání cizích klíčů a vazeb */
ALTER TABLE `chat_messages`
	ADD INDEX `id_sender_id_recipient` (`id_recipient`, `id_sender`),
	ADD CONSTRAINT `FK_chat_messages_users` FOREIGN KEY (`id_sender`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	ADD CONSTRAINT `FK_chat_messages_users_2` FOREIGN KEY (`id_recipient`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;


/************** 11-POS-312-like-comments.sql **************/
/* vytvoří tabulku na zaznemání kdo už daný komentář lajkoval */
CREATE TABLE `like_comments` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`userID` INT UNSIGNED NULL DEFAULT NULL,
	`commentID` INT UNSIGNED NULL DEFAULT NULL,
	PRIMARY KEY (`id`),
	CONSTRAINT `FK__userID_users` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT `FK__commentID_comment_images` FOREIGN KEY (`commentID`) REFERENCES `comment_images` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

/************** 11-enum-status.sql **************/
/* vytvoření tabulky */
CREATE TABLE `enum_status` (
	`id` TINYINT(3) UNSIGNED NOT NULL,
	`name` VARCHAR(20) NOT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

ALTER TABLE `enum_status`
	CHANGE COLUMN `id` `id` TINYINT(3) UNSIGNED NOT NULL AUTO_INCREMENT FIRST;

/* navázání na tabulku users_preferences */
ALTER TABLE `users_properties`
	ADD COLUMN `status` TINYINT(2) UNSIGNED NULL DEFAULT NULL AFTER `age`;

ALTER TABLE `users_properties`
	ADD CONSTRAINT `FK_users_properties_enum_status` FOREIGN KEY (`status`) REFERENCES `enum_status` (`id`) ON UPDATE SET NULL ON DELETE SET NULL;

ALTER TABLE `users_properties`
	CHANGE COLUMN `status` `statusID` TINYINT(2) UNSIGNED NULL DEFAULT NULL AFTER `age`;

/************** 12-comment-owner.sql **************/
/* přidá komentářům majitele */
ALTER TABLE `comment_images`
	ADD COLUMN `userID` INT(10) UNSIGNED NOT NULL AFTER `id`,
	ADD INDEX `userID` (`userID`),
	ADD CONSTRAINT `FK_comment_images_comment_images` FOREIGN KEY (`userID`) REFERENCES `comment_images` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE `comment_images`
	DROP FOREIGN KEY `FK_comment_images_comment_images`;
ALTER TABLE `comment_images`
	ADD CONSTRAINT `FK_comment_images_comment_images` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;

/************** 13-POS-243-region-district-city.sql **************/
/* upraví cizí klíče na region okres a město na SET NULL, první část smaže staré, druhá vyrobí nové */
ALTER TABLE `users_properties`
	DROP FOREIGN KEY `FK_users_properties_city`,
	DROP FOREIGN KEY `FK_users_properties_district`,
	DROP FOREIGN KEY `FK_users_properties_region`;
ALTER TABLE `users_properties`
	ADD CONSTRAINT `FK_users_properties_city` FOREIGN KEY (`cityID`) REFERENCES `city` (`id`) ON UPDATE SET NULL ON DELETE SET NULL,
	ADD CONSTRAINT `FK_users_properties_district` FOREIGN KEY (`districtID`) REFERENCES `district` (`id`) ON UPDATE SET NULL ON DELETE SET NULL,
	ADD CONSTRAINT `FK_users_properties_region` FOREIGN KEY (`regionID`) REFERENCES `region` (`id`) ON UPDATE SET NULL ON DELETE SET NULL;

/************** 13-POS-318-enum-positions.sql **************/
/* vytvoří tabulku pro sexuální pozice a rovněž jednu spojovací tabulku */
CREATE TABLE IF NOT EXISTS `enum_position` (
	`id` TINYINT(2) UNSIGNED NOT NULL AUTO_INCREMENT,
	`position` VARCHAR(15) NULL DEFAULT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

CREATE TABLE `user_position` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`user_propertiesID` INT(11) UNSIGNED NULL DEFAULT NULL,
	`enum_positionID` TINYINT(2) UNSIGNED NULL DEFAULT NULL,
	PRIMARY KEY (`id`),
	INDEX `FK_user_position_enum_position` (`enum_positionID`),
	INDEX `FK_user_position_user_position` (`user_propertiesID`),
	CONSTRAINT `FK_user_position_user_position` FOREIGN KEY (`user_propertiesID`) REFERENCES `users_properties` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT `FK_user_position_enum_position` FOREIGN KEY (`enum_positionID`) REFERENCES `enum_position` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;
/************** 14-POS-320-enum-places.sql **************/
/* vytvoří tabulku pro oblíbená místa na sex a rovněž jednu spojovací tabulku */
CREATE TABLE IF NOT EXISTS `enum_place` (
	`id` TINYINT(2) UNSIGNED NOT NULL AUTO_INCREMENT,
	`place` VARCHAR(20) NULL DEFAULT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

CREATE TABLE `user_place` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`user_propertiesID` INT(11) UNSIGNED NULL DEFAULT NULL,
	`enum_placeID` TINYINT(2) UNSIGNED NULL DEFAULT NULL,
	PRIMARY KEY (`id`),
	INDEX `FK_user_place_enum_place` (`enum_placeID`),
	INDEX `FK_user_place_user_place` (`user_propertiesID`),
	CONSTRAINT `FK_user_place_user_place` FOREIGN KEY (`user_propertiesID`) REFERENCES `users_properties` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT `FK_user_place_enum_place` FOREIGN KEY (`enum_placeID`) REFERENCES `enum_place` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;
/************** 15-bug-fix-age.sql **************/
/* změna věku na datetime */
ALTER TABLE `users_properties`
	ALTER `age` DROP DEFAULT;
ALTER TABLE `users_properties`
	CHANGE COLUMN `age` `age` DATE NOT NULL AFTER `id`;

ALTER TABLE `couple`
	CHANGE COLUMN `age` `age` DATE NULL DEFAULT NULL AFTER `id`;
/************** 16-bug-fix-status.sql **************/
/* zvětšení sloupce na status */
ALTER TABLE `enum_status`
	ALTER `name` DROP DEFAULT;
ALTER TABLE `enum_status`
	CHANGE COLUMN `name` `name` VARCHAR(25) NOT NULL AFTER `id`;

/************** 17-enum_tallness.sql **************/
/* vytvoření enumu na výšku */
CREATE TABLE `enum_talness` (
	`id` TINYINT(2) UNSIGNED NOT NULL AUTO_INCREMENT,
	`tallness` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

RENAME TABLE `enum_talness` TO `enum_tallness`;

/* navázání na tabulku user_properties */

ALTER TABLE `users_properties`
	ADD COLUMN `tallness` TINYINT(2) UNSIGNED NULL DEFAULT NULL AFTER `about_me`,
	DROP COLUMN `tallness`,
	ADD CONSTRAINT `FK_users_properties_enum_tallness` FOREIGN KEY (`tallness`) REFERENCES `enum_tallness` (`id`) ON UPDATE SET NULL ON DELETE SET NULL;

/* navázání na tabulku stream_categories */
ALTER TABLE `stream_categories`
	ADD COLUMN `tallness` TINYINT(2) UNSIGNED NOT NULL DEFAULT '0' AFTER `want_to_meet_men`;
/************** 18-categories.sql **************/
/* kategorie zálib */

CREATE TABLE `category_likes` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

ALTER TABLE `category_likes`
	ADD COLUMN `fisting` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `petting` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `sex_massage` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `piss` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `oral` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `swallow` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `bdsm` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `group` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `anal` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `threesome` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0';

/* unikátní indexy */

ALTER TABLE `category_likes`
	ADD UNIQUE INDEX `fisting_petting_sex_massage_piss_oral` (`fisting`, `petting`, `sex_massage`, `piss`, `oral`),
	ADD UNIQUE INDEX `swallow_bdsm_group_anal_threesome` (`swallow`, `bdsm`, `group`, `anal`, `threesome`);

/* změna názvu tabulky na kategorie */
RENAME TABLE `stream_categories` TO `user_categories`;

/* smazání přesunutých záznamů z kategorií - přesunují se do jiných tabulek */
ALTER TABLE `user_categories`
	DROP COLUMN `want_to_meet_group`,
	DROP COLUMN `want_to_meet_couple_women`,
	DROP COLUMN `want_to_meet_couple_men`,
	DROP COLUMN `want_to_meet_couple`,
	DROP COLUMN `want_to_meet_women`,
	DROP COLUMN `want_to_meet_men`,
	DROP COLUMN `fisting`,
	DROP COLUMN `petting`,
	DROP COLUMN `sex_massage`,
	DROP COLUMN `piss`,
	DROP COLUMN `oral`,
	DROP COLUMN `cum`,
	DROP COLUMN `swallow`,
	DROP COLUMN `bdsm`,
	DROP COLUMN `group`,
	DROP COLUMN `anal`,
	DROP COLUMN `threesome`;

/* připojení do obecných kategorií */
ALTER TABLE `user_categories`
	ADD COLUMN `likes` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `tallness`,
	ADD CONSTRAINT `FK_user_categories_category_likes` FOREIGN KEY (`likes`) REFERENCES `category_likes` (`id`) ON UPDATE SET NULL ON DELETE SET NULL;

/* kategorie koho chce poznat */
CREATE TABLE `category_want_to_meet` (
	`id` TINYINT(2) UNSIGNED NOT NULL AUTO_INCREMENT,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

ALTER TABLE `category_want_to_meet`
	ADD COLUMN `want_to_meet_group` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `want_to_meet_couple_women` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `want_to_meet_couple_men` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `want_to_meet_couple` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `want_to_meet_women` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `want_to_meet_men` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0';

/* připojení do obecných kategorií */
ALTER TABLE `user_categories`
	ADD COLUMN `want_to_meet` TINYINT(2) UNSIGNED NULL DEFAULT NULL AFTER `likes`,
	ADD CONSTRAINT `FK_user_categories_category_want_to_meet` FOREIGN KEY (`want_to_meet`) REFERENCES `category_want_to_meet` (`id`) ON UPDATE SET NULL ON DELETE SET NULL;



/************** 19-POS-323-penis_length.sql **************/
/* smaže tabulku enum_penis_length a relace na ní*/
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
DROP TABLE `enum_penis_length`;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;

ALTER TABLE `users_properties`
	DROP INDEX `FK_users_properties_enum_penis_length`,
	DROP FOREIGN KEY `FK_users_properties_enum_penis_length`;
/************** 19-enum-property.sql **************/
/* vytvoření výčtového typu property */
CREATE TABLE `enum_property` (
	`id` TINYINT(1) UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(15) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

ALTER TABLE `enum_property`
	CHANGE COLUMN `name` `name` VARCHAR(15) NOT NULL AFTER `id`;

/* přejmenování tabulky pro kategorii, přiložení pohlaví čkověka */
RENAME TABLE `category_want_to_meet` TO `category_property_want_to_meet`;
ALTER TABLE `user_categories`
	CHANGE COLUMN `want_to_meet` `property_want_to_meet` TINYINT(2) UNSIGNED NULL DEFAULT NULL AFTER `likes`;

ALTER TABLE `category_property_want_to_meet`
	ADD COLUMN `user_property` TINYINT(1) UNSIGNED NOT NULL AFTER `want_to_meet_men`;

/* zvětšení rozsahu sloupce id */
ALTER TABLE `user_categories`
	DROP FOREIGN KEY `FK_user_categories_category_want_to_meet`;

ALTER TABLE `category_property_want_to_meet`
	CHANGE COLUMN `id` `id` INT(5) UNSIGNED NOT NULL AUTO_INCREMENT FIRST;

ALTER TABLE `user_categories`
	CHANGE COLUMN `property_want_to_meet` `property_want_to_meet` INT UNSIGNED NULL DEFAULT NULL AFTER `likes`,
	ADD CONSTRAINT `FK_user_categories_category_property_want_to_meet` FOREIGN KEY (`property_want_to_meet`) REFERENCES `category_property_want_to_meet` (`id`) ON UPDATE SET NULL ON DELETE SET NULL;

ALTER TABLE `category_property_want_to_meet`
	ADD UNIQUE INDEX `all_colums` (`want_to_meet_group`, `want_to_meet_couple_women`, `want_to_meet_couple_men`, `want_to_meet_couple`, `want_to_meet_women`, `want_to_meet_men`, `user_property`);




/************** 20-category-vazba.sql **************/
/* propojení kategorií s tab. users */
ALTER TABLE `users`
	ADD COLUMN `categoryID` INT(11) UNSIGNED NULL DEFAULT NULL AFTER `profilFotoID`,
	ADD CONSTRAINT `FK_users_user_categories` FOREIGN KEY (`categoryID`) REFERENCES `user_categories` (`id`) ON UPDATE SET NULL ON DELETE SET NULL;

ALTER TABLE `users`
	ADD COLUMN `wasCategoryChanged` TINYINT UNSIGNED NOT NULL DEFAULT '0' AFTER `categoryID`;
ALTER TABLE `users`
	CHANGE COLUMN `wasCategoryChanged` `wasCategoryChanged` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `categoryID`;

/* změna názvu sloupce user properties na user types */
ALTER TABLE `users_properties`
	CHANGE COLUMN `user_property` `type` TINYINT UNSIGNED NOT NULL DEFAULT '1' COMMENT 'user type' AFTER `statusID`,
	ADD CONSTRAINT `FK_users_properties_enum_property` FOREIGN KEY (`type`) REFERENCES `enum_property` (`id`) ON UPDATE NO ACTION ON DELETE NO ACTION;

ALTER TABLE `category_property_want_to_meet`
	ALTER `user_property` DROP DEFAULT;
ALTER TABLE `category_property_want_to_meet`
	CHANGE COLUMN `user_property` `type` TINYINT(1) UNSIGNED NOT NULL AFTER `want_to_meet_men`;

ALTER TABLE `users`
	DROP COLUMN `categoryID`,
	DROP FOREIGN KEY `FK_users_user_categories`;
/************** 21-couple-type-bug-fix.sql **************/
/* změna user_property na type */
ALTER TABLE `couple`
	CHANGE COLUMN `user_property` `type` TINYINT(1) UNSIGNED NULL DEFAULT NULL AFTER `shape`,
	ADD CONSTRAINT `FK_couple_enum_property` FOREIGN KEY (`type`) REFERENCES `enum_property` (`id`) ON UPDATE SET NULL ON DELETE SET NULL;


/************** 21-foreing_key_property_bug_fix.sql **************/
/* změna vlastností cizího klíče */
ALTER TABLE `users_properties`
	DROP FOREIGN KEY `FK_users_properties_enum_property`;
ALTER TABLE `users_properties`
	CHANGE COLUMN `type` `type` TINYINT(3) UNSIGNED NULL DEFAULT '1' COMMENT 'user type' AFTER `statusID`,
	ADD CONSTRAINT `FK_users_properties_enum_property` FOREIGN KEY (`type`) REFERENCES `enum_property` (`id`) ON UPDATE SET NULL ON DELETE SET NULL;

ALTER TABLE `category_property_want_to_meet`
	ALTER `user_property` DROP DEFAULT;
ALTER TABLE `category_property_want_to_meet`
	CHANGE COLUMN `user_property` `type` TINYINT(1) UNSIGNED NOT NULL AFTER `want_to_meet_men`;
