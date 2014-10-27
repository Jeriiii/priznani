/* vytoří tabulku pro komentáře statusu */
CREATE TABLE `comment_statuses` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`userID` INT(10) UNSIGNED NOT NULL,
	`statusID` INT(10) UNSIGNED NULL DEFAULT NULL,
	`likes` SMALLINT(4) UNSIGNED NULL DEFAULT '0',
	`comment` TEXT NULL,
	PRIMARY KEY (`id`),
	INDEX `FK_comment_statuses_users` (`userID`),
	INDEX `FK_comment_statuses_status` (`statusID`),
	CONSTRAINT `FK_comment_statuses_users` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT `FK_comment_statuses_status` FOREIGN KEY (`statusID`) REFERENCES `status` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;