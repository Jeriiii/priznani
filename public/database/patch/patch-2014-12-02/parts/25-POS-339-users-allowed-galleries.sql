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