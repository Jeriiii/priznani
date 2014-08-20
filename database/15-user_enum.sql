/* změna struktury tabuly user_propertie aby se co nejvíce blížila enumu */
ALTER TABLE `users_properties`
	CHANGE COLUMN `user_property` `user_property` VARCHAR(11) NULL DEFAULT '' COMMENT 'w - women, m - man, c - couple, cw - coupleWomen, cm - coupleMen, g - group' AFTER `age`;

ALTER TABLE `users_properties`
	CHANGE COLUMN `user_property` `user_property` VARCHAR(2) NULL DEFAULT '' COMMENT 'w - women, m - man, c - couple, cw - coupleWomen, cm - coupleMen, g - group' AFTER `age`;

ALTER TABLE `users_properties`
	CHANGE COLUMN `first_sentence` `first_sentence` TEXT NULL DEFAULT '' AFTER `user_property`,
	CHANGE COLUMN `about_me` `about_me` TEXT NULL DEFAULT '' AFTER `first_sentence`;
/* SQL chyba (1101): BLOB/TEXT column 'first_sentence' can't have a default value */
ALTER TABLE `users_properties`
	CHANGE COLUMN `first_sentence` `first_sentence` TEXT NULL DEFAULT NULL AFTER `user_property`,
	CHANGE COLUMN `about_me` `about_me` TEXT NULL DEFAULT NULL AFTER `first_sentence`;
