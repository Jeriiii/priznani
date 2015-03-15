/* Přidání vlastností platby */
ALTER TABLE `payments`
	ALTER `create` DROP DEFAULT;
ALTER TABLE `payments`
	ADD COLUMN `type` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT '1 = premium. 2 = gold' AFTER `userID`,
	CHANGE COLUMN `create` `from` DATETIME NOT NULL AFTER `type`,
	ADD COLUMN `to` DATETIME NOT NULL AFTER `from`;

