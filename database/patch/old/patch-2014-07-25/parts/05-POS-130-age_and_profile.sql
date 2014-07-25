/* vložení sloupce pro věk */
ALTER TABLE `users_properties`
	ADD COLUMN `age` INT(11) UNSIGNED NULL DEFAULT NULL AFTER `id`;

/* vložení sloupce pro profilové foto */
ALTER TABLE `users`
	ADD COLUMN `profilFotoID` INT(11) UNSIGNED NULL DEFAULT NULL AFTER `coupleID`;
ALTER TABLE `users`
	ADD CONSTRAINT `FK_users_user_images` FOREIGN KEY (`profilFotoID`) REFERENCES `user_images` (`id`) ON UPDATE CASCADE;

/* změna šířky sloupce na 1 */
ALTER TABLE `user_galleries`
	CHANGE COLUMN `default` `default` TINYINT(1) NULL DEFAULT '0' AFTER `more`;

/* přidání sloupce pro profilovou galerii */
ALTER TABLE `user_galleries`
	ADD COLUMN `profil_gallery` TINYINT UNSIGNED NOT NULL DEFAULT '0' AFTER `default`;