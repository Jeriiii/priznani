/* přidání sloupce pro poradnu do streamu */
ALTER TABLE `stream_items`
	ADD COLUMN `adviceID` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `galleryID`,
	ADD CONSTRAINT `FK_stream_items_advices` FOREIGN KEY (`adviceID`) REFERENCES `advices` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;
