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

