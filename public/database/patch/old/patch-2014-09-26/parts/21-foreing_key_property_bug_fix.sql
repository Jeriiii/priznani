/* změna vlastností cizího klíče */
ALTER TABLE `users_properties`
	DROP FOREIGN KEY `FK_users_properties_enum_property`;
ALTER TABLE `users_properties`
	CHANGE COLUMN `type` `type` TINYINT(3) UNSIGNED NULL DEFAULT '1' COMMENT 'user type' AFTER `statusID`,
	ADD CONSTRAINT `FK_users_properties_enum_property` FOREIGN KEY (`type`) REFERENCES `enum_property` (`id`) ON UPDATE SET NULL ON DELETE SET NULL;
