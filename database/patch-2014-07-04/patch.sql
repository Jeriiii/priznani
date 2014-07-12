
/* Pridani sloupecku default pro urceni default galerie */
ALTER TABLE `user_galleries`
	ADD COLUMN `default` TINYINT NULL DEFAULT '0' AFTER `more`;

ALTER TABLE `stream_items` ADD COLUMN `adviceID` INT(10) UNSIGNED NULL AFTER `confessionID`;

/*zmena pri smazani obrazku, ktery je last nebo best v galerii se nastavi null*/

ALTER TABLE `user_galleries`
	DROP FOREIGN KEY `bestImage`,
	DROP FOREIGN KEY `lastImage`;
ALTER TABLE `user_galleries`
	ADD CONSTRAINT `bestImage` FOREIGN KEY (`bestImageID`) REFERENCES `user_images` (`id`) ON DELETE SET NULL,
	ADD CONSTRAINT `lastImage` FOREIGN KEY (`lastImageID`) REFERENCES `user_images` (`id`) ON DELETE SET NULL;

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


/* propojí tabulky users a users_properties */
ALTER TABLE `users`
	ADD COLUMN `propertyID` INT UNSIGNED NULL DEFAULT NULL AFTER `id`,
	ADD CONSTRAINT `FK_users_users_properties` FOREIGN KEY (`propertyID`) REFERENCES `users_properties` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE `users`
	CHANGE COLUMN `propertyID` `propertyID` INT(11) UNSIGNED NULL DEFAULT NULL AFTER `id`;

/* propojení s párem přímo z tabulky users */
ALTER TABLE `users`
	ADD COLUMN `coupleID` INT(11) UNSIGNED NULL DEFAULT NULL AFTER `propertyID`,
	ADD CONSTRAINT `FK_users_couple` FOREIGN KEY (`coupleID`) REFERENCES `couple` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;

/* zkrácení sloupce pro věk */
ALTER TABLE `users`
	CHANGE COLUMN `age` `age` TINYINT(3) UNSIGNED NULL DEFAULT NULL AFTER `last_active`;

/* přesunutí sloupce s věkem */
ALTER TABLE `users`
	DROP COLUMN `age`;

/* přidání sloupce pro poradnu do streamu */
ALTER TABLE `stream_items`
	ADD COLUMN `adviceID` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `galleryID`,
	ADD CONSTRAINT `FK_stream_items_advices` FOREIGN KEY (`adviceID`) REFERENCES `advices` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;
