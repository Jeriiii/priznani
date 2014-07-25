/* prehodi imageID na galleryID */
ALTER TABLE `stream_items`
	DROP FOREIGN KEY `stream_items_ibfk_2`;
ALTER TABLE `stream_items`
	CHANGE COLUMN `imageID` `galleryID` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `videoID`,
	ADD CONSTRAINT `stream_items_ibfk_2` FOREIGN KEY (`galleryID`) REFERENCES `galleries` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;

/* prida lastImageID do galerie */
ALTER TABLE `galleries`
	ADD CONSTRAINT `image` FOREIGN KEY (`lastImageID`) REFERENCES `images` (`id`);
