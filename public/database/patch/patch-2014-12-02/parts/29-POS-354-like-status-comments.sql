/* tabulka pro vazbu mezi uživatelem a statusem, udržuje info kdo co lajkl za satus */
CREATE TABLE `like_status_comments` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`userID` INT(10) UNSIGNED NULL DEFAULT NULL,
	`commentID` INT(10) UNSIGNED NULL DEFAULT NULL,
	PRIMARY KEY (`id`),
	INDEX `FK_like_status_comments_users` (`userID`),
	INDEX `FK_like_status_comments_comment_statuses` (`commentID`),
	CONSTRAINT `FK_like_status_comments_users` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT `FK_like_status_comments_comment_statuses` FOREIGN KEY (`commentID`) REFERENCES `comment_statuses` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;