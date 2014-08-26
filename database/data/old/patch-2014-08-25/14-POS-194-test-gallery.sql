INSERT INTO `user_galleries` (`id`, `name`, `description`, `userID`, `couple`, `profil_gallery`) VALUES (4, 'Super fotky', 'Toto je galerie uživatele Test User', 3, 1, 1);

/* Vloží 2 testovací testovací obrázky do galerie Test User */
INSERT INTO `user_images` (`id`, `suffix`, `name`, `description`, `galleryID`) VALUES (6, 'png', 'Foto 1', 'Foto 1 uživatele Test User', 4);
INSERT INTO `user_images` (`id`, `suffix`, `name`, `description`, `galleryID`) VALUES (7, 'jpg', 'Foto 2', 'Foto 2 uživatele Test User', 4);

/* Zjistí id poslední vložené fotografie */
SELECT @lastImage_id := MAX(id) FROM `user_images` WHERE `galleryID` = 4;

/* Update nejlepší a poslední fotografie*/
UPDATE `user_galleries` SET `bestImageID`=6, `lastImageID`=@lastImage_id WHERE  `id`=4;
