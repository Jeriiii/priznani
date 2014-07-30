/* Vytvoří tabulku pro soutěžní obrázky */
CREATE TABLE `competitions_images` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`imageID` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`userID` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`competitionID` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`phone` VARCHAR(20) NULL DEFAULT NULL,
	`name` VARCHAR(35) NULL DEFAULT NULL,
	`surname` VARCHAR(35) NULL DEFAULT NULL,
	`allowed` TINYINT(4) NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	INDEX `FK_competitions_images_users` (`userID`),
	INDEX `FK_competitions_images_user_images` (`imageID`),
	INDEX `FK_competitions_images_competitions_images` (`competitionID`),
	CONSTRAINT `FK_competitions_images_competitions_images` FOREIGN KEY (`competitionID`) REFERENCES `users_competitions` (`id`),
	CONSTRAINT `FK_competitions_images_users` FOREIGN KEY (`userID`) REFERENCES `users` (`id`),
	CONSTRAINT `FK_competitions_images_user_images` FOREIGN KEY (`imageID`) REFERENCES `user_images` (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=12;
