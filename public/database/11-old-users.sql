ALTER TABLE `users_old`
	ADD COLUMN `noEmails` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'pokud je 1, uživatel si odhlásil odebírání emailù' AFTER `sendNotify`;