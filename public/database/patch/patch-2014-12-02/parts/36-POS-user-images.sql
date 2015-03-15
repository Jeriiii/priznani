/* políčko na označení fotky jako odmítnuté */
ALTER TABLE `user_images`
	ADD COLUMN `rejected` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `comments`;