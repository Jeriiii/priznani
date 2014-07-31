/* Spojí lastImage s tabulkou competitions_images */
ALTER TABLE `users_competitions` 
	ADD CONSTRAINT `FK_users_competitions_competitions_images` FOREIGN KEY (`lastImageID`) REFERENCES `competitions_images` (`id`)