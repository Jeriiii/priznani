/* obrázek u galerie který ji zastupuje je defaultně null */
ALTER TABLE `galleries`
	CHANGE COLUMN `imageUrl` `imageUrl` VARCHAR(50) NULL DEFAULT NULL AFTER `description`;
