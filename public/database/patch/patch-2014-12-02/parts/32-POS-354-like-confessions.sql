/* tabulka na lajkování řiznání */
CREATE TABLE `like_confessions` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`userID` INT(10) UNSIGNED NULL DEFAULT NULL,
	`confessionID` INT(10) UNSIGNED NULL DEFAULT NULL,
	PRIMARY KEY (`id`),
	INDEX `FK_like_confessions_users` (`userID`),
	INDEX `FK_like_confessions_confessions` (`confessionID`),
	CONSTRAINT `FK_like_confessions_confessions` FOREIGN KEY (`confessionID`) REFERENCES `confessions` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT `FK_like_confessions_users` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;