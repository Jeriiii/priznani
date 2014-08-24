/* Autoinkrement v tab. user_properties - musí se odstranit a zase přidat cizí klíč */

ALTER TABLE `users`
	DROP FOREIGN KEY `FK_users_users_properties`;

ALTER TABLE `users_properties`
	CHANGE COLUMN `id` `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT FIRST;

ALTER TABLE `users`
	ADD CONSTRAINT `FK_users_users_properties` FOREIGN KEY (`propertyID`) REFERENCES `users_properties` (`id`) ON UPDATE CASCADE;