/* Spoji s userem, soutěží a userovo obrázkem */
ALTER TABLE `competitions_images`
	ADD CONSTRAINT `FK_competitions_images_competitions_images` FOREIGN KEY (`competitionID`) REFERENCES `users_competitions` (`id`),
	ADD CONSTRAINT `FK_competitions_images_users` FOREIGN KEY (`userID`) REFERENCES `users` (`id`),
	ADD CONSTRAINT `FK_competitions_images_user_images` FOREIGN KEY (`imageID`) REFERENCES `user_images` (`id`)