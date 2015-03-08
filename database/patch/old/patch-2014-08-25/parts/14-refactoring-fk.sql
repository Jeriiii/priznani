/* oprava cizích klíčů v tab. users */
ALTER TABLE `users`
	DROP FOREIGN KEY `FK_users_user_images`,
	DROP FOREIGN KEY `FK_users_users_properties`;
ALTER TABLE `users`
	ADD CONSTRAINT `FK_users_user_images` FOREIGN KEY (`profilFotoID`) REFERENCES `user_images` (`id`) ON UPDATE CASCADE ON DELETE SET NULL,
	ADD CONSTRAINT `FK_users_users_properties` FOREIGN KEY (`propertyID`) REFERENCES `users_properties` (`id`) ON UPDATE CASCADE ON DELETE SET NULL;

