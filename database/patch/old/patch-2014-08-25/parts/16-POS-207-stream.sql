/* Přidá políčko pro statusID a spojí ho s tabulkou status */
ALTER TABLE `stream_items`
	ADD COLUMN `statusID` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `galleryID`,
	ADD CONSTRAINT `FK_stream_items_status` FOREIGN KEY (`statusID`) REFERENCES `status` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;