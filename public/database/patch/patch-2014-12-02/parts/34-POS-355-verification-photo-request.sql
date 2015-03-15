/* tabulka na data o pořadavacích na ověřovací foto */
CREATE TABLE `verification_photo_requests` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`userID` INT(10) UNSIGNED NULL DEFAULT NULL,
	`user2ID` INT(10) UNSIGNED NULL DEFAULT NULL,
	`accepted` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	INDEX `FK_users_users` (`userID`),
	INDEX `FK_users_users_2` (`user2ID`),
	CONSTRAINT `FK_users_users` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT `FK_users_users_2` FOREIGN KEY (`user2ID`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;
