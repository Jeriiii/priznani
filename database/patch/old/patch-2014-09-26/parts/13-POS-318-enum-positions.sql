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