/* obrázek u galerie který ji zastupuje je defaultně null */
ALTER TABLE `galleries`
	CHANGE COLUMN `imageUrl` `imageUrl` VARCHAR(50) NULL DEFAULT NULL AFTER `description`;

/* změna cizích klíčů galerie - možná bude dělat problémy */
ALTER TABLE `user_galleries`
	DROP FOREIGN KEY `bestImage`,
	DROP FOREIGN KEY `lastImage`,
	DROP FOREIGN KEY `user`;

ALTER TABLE `user_galleries`
	CHANGE COLUMN `userID` `userID` INT(11) UNSIGNED NULL AFTER `description`,
	ADD CONSTRAINT `FK1_bestImageID` FOREIGN KEY (`bestImageID`) REFERENCES `user_images` (`id`) ON UPDATE SET NULL ON DELETE SET NULL,
	ADD CONSTRAINT `FK2_lastImageID` FOREIGN KEY (`lastImageID`) REFERENCES `user_images` (`id`) ON UPDATE SET NULL ON DELETE SET NULL,
	ADD CONSTRAINT `FK3_userID` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON UPDATE SET NULL ON DELETE SET NULL;
ALTER TABLE `user_galleries`
	ALTER `userID` DROP DEFAULT;