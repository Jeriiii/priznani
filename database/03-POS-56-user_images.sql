ALTER TABLE `user_images`
    ALTER `galleryID` DROP DEFAULT;
ALTER TABLE `user_images`
    CHANGE COLUMN `galleryID` `galleryID` INT(11) UNSIGNED NOT NULL AFTER `description`,
    ADD CONSTRAINT `FK_user_images_user_galleries` FOREIGN KEY (`galleryID`) REFERENCES `user_galleries` (`id`);
