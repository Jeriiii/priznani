/* upraví tabulku status, přejmenuje políčko text a zmení jeho typ */
ALTER TABLE `status`
	CHANGE COLUMN `text` `message` TEXT NULL DEFAULT NULL;
ALTER TABLE `status`
	DROP FOREIGN KEY `FK_status_users`;
ALTER TABLE `status`	
ADD CONSTRAINT `FK_status_users` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;