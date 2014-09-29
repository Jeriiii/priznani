/* blokovaní uživatelé */
CREATE TABLE `users_bloked` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`ownerID` INT UNSIGNED NOT NULL,
	`blokedID` INT UNSIGNED NOT NULL,
	PRIMARY KEY (`id`),
	INDEX `owner` (`owner`),
	INDEX `bloked` (`bloked`),
	CONSTRAINT `FK_owner_users` FOREIGN KEY (`owner`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT `FK_bloked_users_2` FOREIGN KEY (`bloked`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

