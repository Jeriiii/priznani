/* Vytvoří tabulku pro tickety k obnově hesla uživatele */
CREATE TABLE `user_change_password` (
	`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`userID` INT(11) UNSIGNED NULL DEFAULT NULL,
	`ticket` VARCHAR(30) NULL DEFAULT NULL,
	`create` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	INDEX `FK_ user_change_password_users` (`userID`),
	CONSTRAINT `FK_ user_change_password_users` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;
