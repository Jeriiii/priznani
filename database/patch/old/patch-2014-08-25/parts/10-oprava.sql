ALTER TABLE `users_competitions`
	DROP FOREIGN KEY `FK_users_competitions_user_images`;
ALTER TABLE `users_competitions`
	ADD CONSTRAINT `FK_users_competitions_user_images` FOREIGN KEY (`lastImageID`) REFERENCES `user_images` (`id`) ON UPDATE SET NULL ON DELETE SET NULL;

ALTER TABLE `competitions_images`
	DROP FOREIGN KEY `FK_competitions_images_competitions_images`,
	DROP FOREIGN KEY `FK_competitions_images_users`,
	DROP FOREIGN KEY `FK_competitions_images_user_images`;
ALTER TABLE `competitions_images`
	ADD CONSTRAINT `FK_competitions_images_competitions_images` FOREIGN KEY (`competitionID`) REFERENCES `users_competitions` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	ADD CONSTRAINT `FK_competitions_images_users` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON UPDATE SET NULL ON DELETE SET NULL,
	ADD CONSTRAINT `FK_competitions_images_user_images` FOREIGN KEY (`imageID`) REFERENCES `user_images` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;
