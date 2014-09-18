/* vytvoří tabulku na zaznemání kdo už daný komentář lajkoval */
CREATE TABLE `like_comments` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`userID` INT UNSIGNED NULL DEFAULT NULL,
	`commentID` INT UNSIGNED NULL DEFAULT NULL,
	PRIMARY KEY (`id`),
	CONSTRAINT `FK__userID_users` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT `FK__commentID_comment_images` FOREIGN KEY (`commentID`) REFERENCES `comment_images` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;
