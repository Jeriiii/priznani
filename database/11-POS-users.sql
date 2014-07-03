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
