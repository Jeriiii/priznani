/* políčko na verifikaci usera */
ALTER TABLE `users`
	ADD COLUMN `verified` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `wasCategoryChanged`;