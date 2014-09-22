/* propojení kategorií s tab. users */
ALTER TABLE `users`
	ADD COLUMN `categoryID` INT(11) UNSIGNED NULL DEFAULT NULL AFTER `profilFotoID`,
	ADD CONSTRAINT `FK_users_user_categories` FOREIGN KEY (`categoryID`) REFERENCES `user_categories` (`id`) ON UPDATE SET NULL ON DELETE SET NULL;

ALTER TABLE `users`
	ADD COLUMN `wasCategoryChanged` TINYINT UNSIGNED NOT NULL DEFAULT '0' AFTER `categoryID`;
ALTER TABLE `users`
	CHANGE COLUMN `wasCategoryChanged` `wasCategoryChanged` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `categoryID`;

/* změna názvu sloupce user properties na user types */
ALTER TABLE `users_properties`
	CHANGE COLUMN `user_property` `type` TINYINT UNSIGNED NOT NULL DEFAULT '1' COMMENT 'user type' AFTER `statusID`,
	ADD CONSTRAINT `FK_users_properties_enum_property` FOREIGN KEY (`type`) REFERENCES `enum_property` (`id`) ON UPDATE NO ACTION ON DELETE NO ACTION;

ALTER TABLE `category_property_want_to_meet`
	ALTER `user_property` DROP DEFAULT;
ALTER TABLE `category_property_want_to_meet`
	CHANGE COLUMN `user_property` `type` TINYINT(1) UNSIGNED NOT NULL AFTER `want_to_meet_men`;

ALTER TABLE `users`
	DROP COLUMN `categoryID`,
	DROP FOREIGN KEY `FK_users_user_categories`;