CREATE TABLE `payments` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`userID` INT(11) UNSIGNED NOT NULL DEFAULT '0',
	`create` DATETIME NULL DEFAULT NULL,
	PRIMARY KEY (`id`),
	INDEX `FK_payments_users` (`userID`),
	CONSTRAINT `FK_payments_users` FOREIGN KEY (`userID`) REFERENCES `users` (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB