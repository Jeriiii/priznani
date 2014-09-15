/* vytvoří tabulku na komentáře k obrázkům */
CREATE TABLE `comment_images` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	PRIMARY KEY (`id`),
	`imageID` INT UNSIGNED NULL,
	`likes` SMALLINT(4) UNSIGNED NULL DEFAULT '0',
	`comment` TEXT NULL,
	CONSTRAINT `FK__user_images` FOREIGN KEY (`imageID`) REFERENCES `user_images` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;