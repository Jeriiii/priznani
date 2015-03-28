/* přidá sloupeček do obrázků, které mohou jít na hlavní stranu */
ALTER TABLE `user_images`
	ADD COLUMN `isOnFrontPage` TINYINT(1) NULL DEFAULT NULL AFTER `checkApproved`;

/* vytvoření databáze již hodnocených obrázků */
CREATE TABLE `rate_images` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`imageID` INT UNSIGNED NOT NULL,
	`userID` INT UNSIGNED NOT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;

ALTER TABLE `rate_images`
	ADD CONSTRAINT `FK_rate_images_user_images` FOREIGN KEY (`imageID`) REFERENCES `user_images` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE `rate_images`
	ADD CONSTRAINT `FK_rate_images_users` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;

