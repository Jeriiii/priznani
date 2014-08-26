/* vytvoření tabulky kategorií a jejího primárního klíče*/
CREATE TABLE `stream_categories` (
	`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

/* přidání kategorií do tabulky */
ALTER TABLE `stream_categories`
	ADD COLUMN `want_to_meet_group` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `want_to_meet_couple_women` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `want_to_meet_couple_men` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `want_to_meet_couple` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `want_to_meet_women` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `want_to_meet_men` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `fisting` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `petting` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `sex_massage` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `piss` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `oral` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `cum` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `swallow` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `bdsm` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `group` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `anal` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `threesome` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0';
	
	/* doplnění rozsahových hodnot	*/
ALTER TABLE `stream_items`
	ADD COLUMN `age` DATETIME,
	ADD COLUMN `tallness` INT(6) UNSIGNED NOT NULL DEFAULT '0';

/* vytvoření indexů (není tam vše, protože maximální počet částí klíče je 16)
	Vyloučeny byly sloupce, kde se určuje, koho chce poznat. Tam se totiž předpokládá, že to bude jen jeden max dva z nich.
*/
ALTER TABLE `stream_categories`
	ADD INDEX `stream_categories_heavy_key` (`oral`, `sex_massage`, `anal`, `threesome`, `group`, `fisting`, `petting`, `piss`, `cum`, `swallow`, `bdsm`);
/* pro tallness index přidán není - příliš nízká selektivita */
ALTER TABLE `stream_items`
	ADD INDEX `age` (`age`);
	
	
/* přidání relace příspěvku na kategorie */
ALTER TABLE `stream_items`
	ADD COLUMN `categoryID` INT(11) UNSIGNED NULL DEFAULT NULL AFTER `userID`,
	ADD INDEX `categoryID` (`categoryID`),
	ADD CONSTRAINT `categoryIDtoBitmap` FOREIGN KEY (`categoryID`) REFERENCES `stream_categories` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;
