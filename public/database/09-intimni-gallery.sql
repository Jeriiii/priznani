/** intimni galerie **/
ALTER TABLE `user_galleries`
	ADD COLUMN `intim` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `allow_friends`;

/** chce videt intimni fotky/clanky **/
ALTER TABLE `users_properties`
	ADD COLUMN `showIntim` TINYINT(1) UNSIGNED NULL DEFAULT NULL AFTER `type`;

/** intimní příspěvek **/
ALTER TABLE `stream_items`
	ADD COLUMN `intim` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `type`;
