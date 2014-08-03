/* Vytvoří tabulku pro soutěžní obrázky */
CREATE TABLE `competitions_images` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`imageID` INT(10) UNSIGNED NULL DEFAULT NULL,
	`userID` INT(10) UNSIGNED NULL DEFAULT NULL,
	`competitionID` INT(10) UNSIGNED NULL DEFAULT NULL,
	`phone` VARCHAR(20) NULL DEFAULT NULL,
	`name` VARCHAR(35) NULL DEFAULT NULL,
	`surname` VARCHAR(35) NULL DEFAULT NULL,
	`allowed` TINYINT(4) NULL DEFAULT '0',
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=12;