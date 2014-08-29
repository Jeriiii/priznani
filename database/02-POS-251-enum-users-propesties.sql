/* enum MARITAL_STATE*/

CREATE TABLE `enum_marital_state` (
	`id` TINYINT(2) UNSIGNED NOT NULL AUTO_INCREMENT,
	`marital_state` VARCHAR(15) NULL DEFAULT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

INSERT INTO `enum_marital_state` (`marital_state`) VALUES ('volný');
INSERT INTO `enum_marital_state` (`marital_state`) VALUES ('ženatý / vdaná');
INSERT INTO `enum_marital_state` (`marital_state`) VALUES ('rozvedený/á');
INSERT INTO `enum_marital_state` (`marital_state`) VALUES ('oddělený/á');
INSERT INTO `enum_marital_state` (`marital_state`) VALUES ('vdovec / vdova');
INSERT INTO `enum_marital_state` (`marital_state`) VALUES ('zadaný');

ALTER TABLE `users_properties` DROP COLUMN `marital_state`;

/* zde musí být nastavena default hodnota 1 -> kvůli NOT NULL cizímu klíči*/
ALTER TABLE `users_properties` ADD COLUMN `marital_state` TINYINT(2) UNSIGNED NOT NULL DEFAULT '1';
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

INSERT INTO `enum_orientation` (`orientation`) VALUES ('hetero');
INSERT INTO `enum_orientation` (`orientation`) VALUES ('homo');
INSERT INTO `enum_orientation` (`orientation`) VALUES ('bi');
INSERT INTO `enum_orientation` (`orientation`) VALUES ('bi - chtěl bych zkusit');

ALTER TABLE `users_properties` DROP COLUMN `orientation`;
ALTER TABLE `users_properties` ADD COLUMN `orientation` TINYINT(2) UNSIGNED NOT NULL DEFAULT '1';
ALTER TABLE `users_properties` ADD CONSTRAINT `FK_users_properties_enum_orientation` FOREIGN KEY (`orientation`) REFERENCES `enum_orientation` (`id`);

/* enum SHAPE */
 
CREATE TABLE `enum_shape` (
	`id` TINYINT(2) UNSIGNED NOT NULL AUTO_INCREMENT,
	`shape` VARCHAR(15) NULL DEFAULT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

INSERT INTO `enum_shape` (`shape`) VALUES ('hubená');
INSERT INTO `enum_shape` (`shape`) VALUES ('štíhlá');
INSERT INTO `enum_shape` (`shape`) VALUES ('normální');
INSERT INTO `enum_shape` (`shape`) VALUES ('atletická');
INSERT INTO `enum_shape` (`shape`) VALUES ('plnoštíhlá');
INSERT INTO `enum_shape` (`shape`) VALUES ('při těle');

ALTER TABLE `users_properties` DROP COLUMN `shape`;
ALTER TABLE `users_properties` ADD COLUMN `shape` TINYINT(2) UNSIGNED NOT NULL DEFAULT '1';
ALTER TABLE `users_properties` ADD CONSTRAINT `FK_users_properties_enum_shape` FOREIGN KEY (`shape`) REFERENCES `enum_shape` (`id`);

/* enum PENIS_LENGTH*/

CREATE TABLE `enum_penis_length` (
	`id` TINYINT(2) UNSIGNED NOT NULL AUTO_INCREMENT,
	`penis_length` VARCHAR(25) NULL DEFAULT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

INSERT INTO `enum_penis_length` (`penis_length`) VALUES ('malá');
INSERT INTO `enum_penis_length` (`penis_length`) VALUES ('střední');
INSERT INTO `enum_penis_length` (`penis_length`) VALUES ('velká');
INSERT INTO `enum_penis_length` (`penis_length`) VALUES ('obrovská');


ALTER TABLE `users_properties` DROP COLUMN `penis_length`;
ALTER TABLE `users_properties` ADD COLUMN `penis_length` TINYINT(2) UNSIGNED NOT NULL DEFAULT '1';
ALTER TABLE `users_properties` ADD CONSTRAINT `FK_users_properties_enum_penis_length` FOREIGN KEY (`penis_length`) REFERENCES `enum_penis_length` (`id`);

/* enum PENIS_WIDTH*/

CREATE TABLE `enum_penis_width` (
	`id` TINYINT(2) UNSIGNED NOT NULL AUTO_INCREMENT,
	`penis_width` VARCHAR(15) NULL DEFAULT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

INSERT INTO `enum_penis_width` (`penis_width`) VALUES ('hubený');
INSERT INTO `enum_penis_width` (`penis_width`) VALUES ('střední');
INSERT INTO `enum_penis_width` (`penis_width`) VALUES ('tlustý');

ALTER TABLE `users_properties` DROP COLUMN `penis_width`;
ALTER TABLE `users_properties` ADD COLUMN `penis_width` TINYINT(2) UNSIGNED NOT NULL DEFAULT '1';
ALTER TABLE `users_properties` ADD CONSTRAINT `FK_users_properties_enum_penis_width` FOREIGN KEY (`penis_width`) REFERENCES `enum_penis_width` (`id`);

/* enum DRINK*/
 
CREATE TABLE `enum_drink` (
	`id` TINYINT(2) UNSIGNED NOT NULL AUTO_INCREMENT,
	`drink` VARCHAR(15) NULL DEFAULT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

INSERT INTO `enum_drink` (`drink`) VALUES ('často');
INSERT INTO `enum_drink` (`drink`) VALUES ('ne');
INSERT INTO `enum_drink` (`drink`) VALUES ('příležitostně');

ALTER TABLE `users_properties` DROP COLUMN `drink`;
ALTER TABLE `users_properties` ADD COLUMN `drink` TINYINT(2) UNSIGNED NOT NULL DEFAULT '1';
ALTER TABLE `users_properties` ADD CONSTRAINT `FK_users_properties_enum_drink` FOREIGN KEY (`drink`) REFERENCES `enum_drink` (`id`);


/* enum GRADUATION*/
 
CREATE TABLE `enum_graduation` (
	`id` TINYINT(2) UNSIGNED NOT NULL AUTO_INCREMENT,
	`graduation` VARCHAR(15) NULL DEFAULT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

INSERT INTO `enum_graduation` (`graduation`) VALUES ('základní');
INSERT INTO `enum_graduation` (`graduation`) VALUES ('vyučen/a');
INSERT INTO `enum_graduation` (`graduation`) VALUES ('střední');
INSERT INTO `enum_graduation` (`graduation`) VALUES ('vyšší odborné');
INSERT INTO `enum_graduation` (`graduation`) VALUES ('vysoké');

ALTER TABLE `users_properties` DROP COLUMN `graduation`;
ALTER TABLE `users_properties` ADD COLUMN `graduation` TINYINT(2) UNSIGNED NOT NULL DEFAULT '1';
ALTER TABLE `users_properties` ADD CONSTRAINT `FK_users_properties_enum_graduation` FOREIGN KEY (`graduation`) REFERENCES `enum_graduation` (`id`);


/* enum BRA_SIZE*/
 
CREATE TABLE `enum_bra_size` (
	`id` TINYINT(2) UNSIGNED NOT NULL AUTO_INCREMENT,
	`bra_size` VARCHAR(15) NULL DEFAULT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

INSERT INTO `enum_bra_size` (`bra_size`) VALUES ('A');
INSERT INTO `enum_bra_size` (`bra_size`) VALUES ('B');
INSERT INTO `enum_bra_size` (`bra_size`) VALUES ('C');
INSERT INTO `enum_bra_size` (`bra_size`) VALUES ('D');
INSERT INTO `enum_bra_size` (`bra_size`) VALUES ('E');
INSERT INTO `enum_bra_size` (`bra_size`) VALUES ('F');

ALTER TABLE `users_properties` DROP COLUMN `bra_size`;
ALTER TABLE `users_properties` ADD COLUMN `bra_size` TINYINT(2) UNSIGNED NOT NULL DEFAULT '1';
ALTER TABLE `users_properties` ADD CONSTRAINT `FK_users_properties_enum_bra_size` FOREIGN KEY (`bra_size`) REFERENCES `enum_bra_size` (`id`);

/* enum SMOKE */
 
CREATE TABLE `enum_smoke` (
	`id` TINYINT(2) UNSIGNED NOT NULL AUTO_INCREMENT,
	`smoke` VARCHAR(15) NULL DEFAULT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

INSERT INTO `enum_smoke` (`smoke`) VALUES ('často');
INSERT INTO `enum_smoke` (`smoke`) VALUES ('ne');
INSERT INTO `enum_smoke` (`smoke`) VALUES ('příležitostně');

ALTER TABLE `users_properties` DROP COLUMN `smoke`;
ALTER TABLE `users_properties` ADD COLUMN `smoke` TINYINT(2) UNSIGNED NOT NULL DEFAULT '1';
ALTER TABLE `users_properties` ADD CONSTRAINT `FK_users_properties_enum_smoke` FOREIGN KEY (`smoke`) REFERENCES `enum_smoke` (`id`);