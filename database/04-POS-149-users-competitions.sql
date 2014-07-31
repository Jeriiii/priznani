/* Vytvoří tabulku pro soutěže */
CREATE TABLE `users_competitions` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(150) NOT NULL,
	`description` VARCHAR(300) NOT NULL,
	`imageUrl` VARCHAR(50) NOT NULL,
	`current` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`lastImageID` INT(10) UNSIGNED NULL DEFAULT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=3;
