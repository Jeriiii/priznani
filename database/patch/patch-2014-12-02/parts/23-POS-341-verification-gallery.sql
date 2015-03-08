/* Přidá políčko pro označení verifikační galerie */
ALTER TABLE `user_galleries`
	ADD COLUMN `verification_gallery` TINYINT(1) NULL DEFAULT '0' AFTER `profil_gallery`;