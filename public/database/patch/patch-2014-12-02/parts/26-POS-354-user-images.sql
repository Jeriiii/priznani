/* Přidá políčko pro označení jako intimní */
ALTER TABLE `user_images`
	ADD COLUMN `intim` TINYINT(1) NULL DEFAULT '0' AFTER `comments`;