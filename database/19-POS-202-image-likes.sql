/* Vytvoří vazební tabulku pro lajky obrázků */
CREATE TABLE `image_likes` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`userID` INT(10) UNSIGNED NULL DEFAULT NULL,
	`imageID` INT(10) UNSIGNED NULL DEFAULT NULL,
	PRIMARY KEY (`id`),
	INDEX `FK_image_likes_users` (`userID`),
	INDEX `FK_image_likes_user_images` (`imageID`),
	CONSTRAINT `FK_image_likes_users` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT `FK_image_likes_user_images` FOREIGN KEY (`imageID`) REFERENCES `user_images` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
)
ENGINE=InnoDB
AUTO_INCREMENT=14;
