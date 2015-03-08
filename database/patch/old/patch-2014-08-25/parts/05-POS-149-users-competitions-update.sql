/* Spojí lastImage s tabulkou competitions_images */
ALTER TABLE `users_competitions` 
	ADD CONSTRAINT `FK_users_competitions_user_images` FOREIGN KEY (`lastImageID`) REFERENCES `user_images` (`id`)