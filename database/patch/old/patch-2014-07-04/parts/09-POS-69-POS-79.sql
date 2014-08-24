/*zmena pri smazani obrazku, ktery je last nebo best v galerii se nastavi null*/

ALTER TABLE `user_galleries`
	DROP FOREIGN KEY `bestImage`,
	DROP FOREIGN KEY `lastImage`;
ALTER TABLE `user_galleries`
	ADD CONSTRAINT `bestImage` FOREIGN KEY (`bestImageID`) REFERENCES `user_images` (`id`) ON DELETE SET NULL,
	ADD CONSTRAINT `lastImage` FOREIGN KEY (`lastImageID`) REFERENCES `user_images` (`id`) ON DELETE SET NULL;
