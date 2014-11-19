/* tabulka pro komentáře přiznání */
CREATE TABLE `comment_confessions` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`userID` INT(10) UNSIGNED NOT NULL,
	`confessionID` INT(10) UNSIGNED NULL DEFAULT NULL,
	`likes` SMALLINT(4) UNSIGNED NULL DEFAULT '0',
	`comment` TEXT NULL,
	PRIMARY KEY (`id`),
	INDEX `FK_comment_confessions_users` (`userID`),
	INDEX `FK_comment_confessions_confessions` (`confessionID`),
	CONSTRAINT `FK_comment_confessions_users` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT `FK_comment_confessions_confessions` FOREIGN KEY (`confessionID`) REFERENCES `confessions` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;