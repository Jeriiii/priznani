/* přidá sloupeček do obrázků, které mohou jít na hlavní stranu */
ALTER TABLE `user_images`
	ADD COLUMN `isOnFrontPage` TINYINT(1) NULL DEFAULT NULL AFTER `checkApproved`;

