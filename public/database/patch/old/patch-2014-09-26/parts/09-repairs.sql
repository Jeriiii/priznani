/* nastavení akcí při smazání/změně cizího klíče */
ALTER TABLE `contacts`
	DROP FOREIGN KEY `FK_contacts_users`;
ALTER TABLE `contacts`
	ADD CONSTRAINT `FK_contacts_users` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE `galleries`
	DROP FOREIGN KEY `image`;
ALTER TABLE `galleries`
	ADD CONSTRAINT `image` FOREIGN KEY (`lastImageID`) REFERENCES `images` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE `payments`
	DROP FOREIGN KEY `FK_payments_users`;
ALTER TABLE `payments`
	ADD CONSTRAINT `FK_payments_users` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE `users_properties`
	DROP FOREIGN KEY `FK_users_properties_enum_hair_colour`,
	DROP FOREIGN KEY `FK_users_properties_enum_bra_size`,
	DROP FOREIGN KEY `FK_users_properties_enum_drink`,
	DROP FOREIGN KEY `FK_users_properties_enum_graduation`,
	DROP FOREIGN KEY `FK_users_properties_enum_marital_state`,
	DROP FOREIGN KEY `FK_users_properties_enum_orientation`,
	DROP FOREIGN KEY `FK_users_properties_enum_penis_length`,
	DROP FOREIGN KEY `FK_users_properties_enum_penis_width`,
	DROP FOREIGN KEY `FK_users_properties_enum_shape`,
	DROP FOREIGN KEY `FK_users_properties_enum_smoke`;
ALTER TABLE `users_properties`
	ADD CONSTRAINT `FK_users_properties_enum_hair_colour` FOREIGN KEY (`hair_colour`) REFERENCES `enum_hair_colour` (`id`) ON UPDATE SET NULL ON DELETE SET NULL,
	ADD CONSTRAINT `FK_users_properties_enum_bra_size` FOREIGN KEY (`bra_size`) REFERENCES `enum_bra_size` (`id`) ON UPDATE SET NULL ON DELETE SET NULL,
	ADD CONSTRAINT `FK_users_properties_enum_drink` FOREIGN KEY (`drink`) REFERENCES `enum_drink` (`id`) ON UPDATE SET NULL ON DELETE SET NULL,
	ADD CONSTRAINT `FK_users_properties_enum_graduation` FOREIGN KEY (`graduation`) REFERENCES `enum_graduation` (`id`) ON UPDATE SET NULL ON DELETE SET NULL,
	ADD CONSTRAINT `FK_users_properties_enum_marital_state` FOREIGN KEY (`marital_state`) REFERENCES `enum_marital_state` (`id`) ON UPDATE SET NULL ON DELETE SET NULL,
	ADD CONSTRAINT `FK_users_properties_enum_orientation` FOREIGN KEY (`orientation`) REFERENCES `enum_orientation` (`id`) ON UPDATE SET NULL ON DELETE SET NULL,
	ADD CONSTRAINT `FK_users_properties_enum_penis_length` FOREIGN KEY (`penis_length`) REFERENCES `enum_penis_length` (`id`) ON UPDATE SET NULL ON DELETE SET NULL,
	ADD CONSTRAINT `FK_users_properties_enum_penis_width` FOREIGN KEY (`penis_width`) REFERENCES `enum_penis_width` (`id`) ON UPDATE SET NULL ON DELETE SET NULL,
	ADD CONSTRAINT `FK_users_properties_enum_shape` FOREIGN KEY (`shape`) REFERENCES `enum_shape` (`id`) ON UPDATE SET NULL ON DELETE SET NULL,
	ADD CONSTRAINT `FK_users_properties_enum_smoke` FOREIGN KEY (`smoke`) REFERENCES `enum_smoke` (`id`) ON UPDATE SET NULL ON DELETE SET NULL;

