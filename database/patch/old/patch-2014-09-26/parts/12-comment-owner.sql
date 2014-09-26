/* přidá komentářům majitele */
ALTER TABLE `comment_images`
	ADD COLUMN `userID` INT(10) UNSIGNED NOT NULL AFTER `id`,
	ADD INDEX `userID` (`userID`),
	ADD CONSTRAINT `FK_comment_images_comment_images` FOREIGN KEY (`userID`) REFERENCES `comment_images` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE `comment_images`
	DROP FOREIGN KEY `FK_comment_images_comment_images`;
ALTER TABLE `comment_images`
	ADD CONSTRAINT `FK_comment_images_comment_images` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;
