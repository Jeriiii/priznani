/* vytvoří vazební tabulku na lajky statusů */
CREATE TABLE `like_statuses` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`userID` INT UNSIGNED NULL,
	`statusID` INT UNSIGNED NULL,
	PRIMARY KEY (`id`),
	CONSTRAINT `FK__users` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT `FK__status` FOREIGN KEY (`statusID`) REFERENCES `status` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;