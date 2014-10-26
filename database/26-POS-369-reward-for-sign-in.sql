ALTER TABLE `users`
	ADD COLUMN `last_signed_in` DATE NULL DEFAULT NULL AFTER `last_active`;
	
ALTER TABLE `users`
	CHANGE COLUMN `last_signed_in` `first_signed_day_streak` DATE NULL DEFAULT NULL COMMENT 'den kdy se přihlásil a od té doby se každý den stavil' AFTER `last_active`;
