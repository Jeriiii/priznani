/* změna user_property na type */
ALTER TABLE `couple`
	CHANGE COLUMN `user_property` `type` TINYINT(1) UNSIGNED NULL DEFAULT NULL AFTER `shape`,
	ADD CONSTRAINT `FK_couple_enum_property` FOREIGN KEY (`type`) REFERENCES `enum_property` (`id`) ON UPDATE SET NULL ON DELETE SET NULL;

