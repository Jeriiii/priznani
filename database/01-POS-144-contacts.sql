/* Vytvoří tabulku pro zprávy uživatelů */
CREATE TABLE `contacts` (
	`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`userID` INT(11) UNSIGNED NULL DEFAULT NULL,
	`email` VARCHAR(50) NULL DEFAULT '',
	`phone` VARCHAR(20) NULL DEFAULT '',
	`text` VARCHAR(500) NULL DEFAULT NULL,
	`viewed` TINYINT(4) NULL DEFAULT '0',
	`created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	INDEX `FK_contacts_users` (`userID`),
	CONSTRAINT `FK_contacts_users` FOREIGN KEY (`userID`) REFERENCES `users` (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB