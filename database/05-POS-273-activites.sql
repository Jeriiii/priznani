/* upraví cizí klíče v tabulce acitivities na ON CASCADE */
ALTER TABLE `activities`
	DROP FOREIGN KEY `FK_activities_status`,
	DROP FOREIGN KEY `FK_activities_users`,
	DROP FOREIGN KEY `FK_activities_users_2`,
	DROP FOREIGN KEY `FK_activities_user_images`;
ALTER TABLE `activities`
	ADD CONSTRAINT `FK_activities_status` FOREIGN KEY (`statusID`) REFERENCES `status` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	ADD CONSTRAINT `FK_activities_users` FOREIGN KEY (`event_ownerID`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	ADD CONSTRAINT `FK_activities_users_2` FOREIGN KEY (`event_creatorID`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	ADD CONSTRAINT `FK_activities_user_images` FOREIGN KEY (`imageID`) REFERENCES `user_images` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;
