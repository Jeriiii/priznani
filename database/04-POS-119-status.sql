/* Vytvoření tabulky status */
CREATE TABLE `status` (
	`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`userID` INT(11) UNSIGNED NOT NULL DEFAULT '0',
	`text` VARCHAR(600) NULL DEFAULT NULL,
	PRIMARY KEY (`id`),
	INDEX `FK_status_users` (`userID`),
	CONSTRAINT `FK_status_users` FOREIGN KEY (`userID`) REFERENCES `users` (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;
