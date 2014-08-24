/* Přidá sloupeček pro město, okres a region */
ALTER TABLE `users_properties`
	ADD COLUMN `cityID` INT UNSIGNED NULL DEFAULT NULL AFTER `want_to_meet_group`,
	ADD COLUMN `districtID` INT UNSIGNED NULL DEFAULT NULL AFTER `cityID`,
	ADD COLUMN `regionID` INT UNSIGNED NULL DEFAULT NULL AFTER `districtID`,
	ADD CONSTRAINT `FK_users_properties_city` FOREIGN KEY (`cityID`) REFERENCES `city` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	ADD CONSTRAINT `FK_users_properties_district` FOREIGN KEY (`districtID`) REFERENCES `district` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	ADD CONSTRAINT `FK_users_properties_region` FOREIGN KEY (`regionID`) REFERENCES `region` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;