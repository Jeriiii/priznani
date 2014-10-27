/* tabulka pro lajkování kometářů u přiznání */
CREATE TABLE `like_confession_comments` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`userID` INT(10) UNSIGNED NULL DEFAULT NULL,
	`commentID` INT(10) UNSIGNED NULL DEFAULT NULL,
	PRIMARY KEY (`id`),
	INDEX `FK_like_confession_comments_users` (`userID`),
	INDEX `FK_like_confession_comments_comment_confessions` (`commentID`),
	CONSTRAINT `FK_like_confession_comments_users` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT `FK_like_confession_comments_comment_confessions` FOREIGN KEY (`commentID`) REFERENCES `comment_confessions` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;