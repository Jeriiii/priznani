/* upraví cizí klíče na region okres a město na SET NULL, první část smaže staré, druhá vyrobí nové */
ALTER TABLE `users_properties`
	DROP FOREIGN KEY `FK_users_properties_city`,
	DROP FOREIGN KEY `FK_users_properties_district`,
	DROP FOREIGN KEY `FK_users_properties_region`;
ALTER TABLE `users_properties`
	ADD CONSTRAINT `FK_users_properties_city` FOREIGN KEY (`cityID`) REFERENCES `city` (`id`) ON UPDATE SET NULL ON DELETE SET NULL,
	ADD CONSTRAINT `FK_users_properties_district` FOREIGN KEY (`districtID`) REFERENCES `district` (`id`) ON UPDATE SET NULL ON DELETE SET NULL,
	ADD CONSTRAINT `FK_users_properties_region` FOREIGN KEY (`regionID`) REFERENCES `region` (`id`) ON UPDATE SET NULL ON DELETE SET NULL;
