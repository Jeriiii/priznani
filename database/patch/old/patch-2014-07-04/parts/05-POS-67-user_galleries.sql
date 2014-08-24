
/* Pridani sloupecku default pro urceni default galerie */
ALTER TABLE `user_galleries`
	ADD COLUMN `default` TINYINT NULL DEFAULT '0' AFTER `more`;
