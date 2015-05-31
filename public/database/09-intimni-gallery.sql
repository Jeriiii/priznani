/** intimni galerie **/
ALTER TABLE `user_galleries`
	ADD COLUMN `intim` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `allow_friends`;
