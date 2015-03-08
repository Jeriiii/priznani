/* přidá políčko na počet komentářů */
ALTER TABLE `user_images`
	ADD COLUMN `comments` INT(5) UNSIGNED NOT NULL DEFAULT '0' AFTER `likes`;