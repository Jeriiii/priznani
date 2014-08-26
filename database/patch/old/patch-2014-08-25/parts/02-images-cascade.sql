/* kaskádní mazání všech relací s tab. images, galleries, user_images a streams*/

ALTER TABLE `images`
	DROP FOREIGN KEY `videoID`,
	DROP FOREIGN KEY `galleryID`;
ALTER TABLE `images`
	ADD CONSTRAINT `videoID` FOREIGN KEY (`videoID`) REFERENCES `videos` (`id`) ON DELETE CASCADE,
	ADD CONSTRAINT `galleryID` FOREIGN KEY (`galleryID`) REFERENCES `galleries` (`id`) ON DELETE CASCADE;

ALTER TABLE `stream_items`
	DROP FOREIGN KEY `stream_items_galleries`,
	DROP FOREIGN KEY `stream_items_ibfk_5`;
ALTER TABLE `stream_items`
	ADD CONSTRAINT `stream_items_galleries` FOREIGN KEY (`galleryID`) REFERENCES `galleries` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	ADD CONSTRAINT `stream_items_ibfk_5` FOREIGN KEY (`userGalleryID`) REFERENCES `user_galleries` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE `galleries`
	DROP FOREIGN KEY `image`;
ALTER TABLE `galleries`
	ADD CONSTRAINT `image` FOREIGN KEY (`lastImageID`) REFERENCES `images` (`id`) ON DELETE CASCADE;

ALTER TABLE `user_images`
	DROP FOREIGN KEY `gallery`;
ALTER TABLE `user_images`
	ADD CONSTRAINT `gallery` FOREIGN KEY (`galleryID`) REFERENCES `user_galleries` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;

