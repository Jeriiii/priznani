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