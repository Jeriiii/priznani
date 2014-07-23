/* vložení sloupce pro věk */
ALTER TABLE `users_properties`
	ADD COLUMN `age` INT(11) UNSIGNED NULL DEFAULT NULL AFTER `id_couple`;

ALTER TABLE `users`
	ADD COLUMN `profilFotoID` INT(11) UNSIGNED NULL DEFAULT NULL AFTER `coupleID`;
ALTER TABLE `users`
	ADD CONSTRAINT `FK_users_user_images` FOREIGN KEY (`profilFotoID`) REFERENCES `user_images` (`id`) ON UPDATE CASCADE;
