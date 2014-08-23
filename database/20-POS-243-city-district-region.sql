/* Přidá tabulky pro města, okresy, a kraje */
CREATE TABLE `city` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(35) NOT NULL,
	`districtID` INT(10) UNSIGNED NULL DEFAULT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

CREATE TABLE `district` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(35) NULL DEFAULT NULL,
	`regionID` INT(10) UNSIGNED NULL DEFAULT NULL,
	PRIMARY KEY (`id`)
)
COMMENT='okres'
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

CREATE TABLE `region` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(35) NULL DEFAULT NULL,
	PRIMARY KEY (`id`)
)
COMMENT='kraj'
COLLATE='utf8_general_ci'
ENGINE=InnoDB;