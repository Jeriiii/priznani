/*P?idá sloupe?ek allow do tabulky user_images*/
ALTER TABLE `user_images`
	ADD COLUMN `allow` TINYINT(1) NULL DEFAULT '0' AFTER `galleryID`;
