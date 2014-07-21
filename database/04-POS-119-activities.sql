/* vytvoření tabulky pro seznam aktivit */
CREATE TABLE `activities` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`event_type` VARCHAR(50) NULL DEFAULT NULL,
	`imageID` INT(11) UNSIGNED NULL DEFAULT NULL,
	`statusID` INT(11) UNSIGNED NULL DEFAULT NULL,
	`event_ownerID` INT(11) UNSIGNED NULL DEFAULT NULL,
	`event_creatorID` INT(11) UNSIGNED NULL DEFAULT NULL,
	PRIMARY KEY (`id`),
	INDEX `FK_activities_user_images` (`imageID`),
	INDEX `FK_activities_status` (`statusID`),
	INDEX `FK_activities_users` (`event_ownerID`),
	INDEX `FK_activities_users_2` (`event_creatorID`),
	CONSTRAINT `FK_activities_status` FOREIGN KEY (`statusID`) REFERENCES `status` (`id`),
	CONSTRAINT `FK_activities_users` FOREIGN KEY (`event_ownerID`) REFERENCES `users` (`id`),
	CONSTRAINT `FK_activities_users_2` FOREIGN KEY (`event_creatorID`) REFERENCES `users` (`id`),
	CONSTRAINT `FK_activities_user_images` FOREIGN KEY (`imageID`) REFERENCES `user_images` (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;