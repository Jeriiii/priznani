ALTER TABLE `users_properties`
	ADD COLUMN `sound_effect` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' AFTER `type`;