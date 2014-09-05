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

UPDATE `enum_hair_colour` SET `id`=0 WHERE  `id`=5;