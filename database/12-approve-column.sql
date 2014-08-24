/* změna názvu sloupečku */
ALTER TABLE `user_images`
	CHANGE COLUMN `allow` `approved` TINYINT(1) NULL DEFAULT '0' AFTER `galleryID`;
