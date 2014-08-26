/* přidá sloupeček pro lajky k obrázkům */
ALTER TABLE `user_images`
	ADD COLUMN `likes` INT(5) UNSIGNED NOT NULL DEFAULT '0';