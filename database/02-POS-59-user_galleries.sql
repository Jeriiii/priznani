/* Pridani novych sloupecku man, woman, couple, more do uzivatelskych galerii */
ALTER TABLE `user_galleries`
	ADD COLUMN `man` TINYINT(1) NULL DEFAULT '0' AFTER `lastImageID`,
	ADD COLUMN `woman` TINYINT(1) NULL DEFAULT '0' AFTER `man`,
	ADD COLUMN `couple` TINYINT(1) NULL DEFAULT '0' AFTER `woman`,
	ADD COLUMN `more` TINYINT(1) NULL DEFAULT '0' AFTER `couple`;
