/* přidá sloupčeky o informaci, zda se jedná o privátní galerii a zda ni mohou kamarádi */
ALTER TABLE `user_galleries`
	ADD COLUMN `private` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `verification_gallery`,
	ADD COLUMN `allow_friends` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' AFTER `private`;