ALTER TABLE `users_old`
	ADD COLUMN `noEmails` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'pokud je 1, u�ivatel si odhl�sil odeb�r�n� email�' AFTER `sendNotify`;