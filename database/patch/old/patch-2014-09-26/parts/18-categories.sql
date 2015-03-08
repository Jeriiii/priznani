/* kategorie zálib */

CREATE TABLE `category_likes` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

ALTER TABLE `category_likes`
	ADD COLUMN `fisting` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `petting` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `sex_massage` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `piss` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `oral` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `swallow` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `bdsm` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `group` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `anal` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `threesome` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0';

/* unikátní indexy */

ALTER TABLE `category_likes`
	ADD UNIQUE INDEX `fisting_petting_sex_massage_piss_oral` (`fisting`, `petting`, `sex_massage`, `piss`, `oral`),
	ADD UNIQUE INDEX `swallow_bdsm_group_anal_threesome` (`swallow`, `bdsm`, `group`, `anal`, `threesome`);

/* změna názvu tabulky na kategorie */
RENAME TABLE `stream_categories` TO `user_categories`;

/* smazání přesunutých záznamů z kategorií - přesunují se do jiných tabulek */
ALTER TABLE `user_categories`
	DROP COLUMN `want_to_meet_group`,
	DROP COLUMN `want_to_meet_couple_women`,
	DROP COLUMN `want_to_meet_couple_men`,
	DROP COLUMN `want_to_meet_couple`,
	DROP COLUMN `want_to_meet_women`,
	DROP COLUMN `want_to_meet_men`,
	DROP COLUMN `fisting`,
	DROP COLUMN `petting`,
	DROP COLUMN `sex_massage`,
	DROP COLUMN `piss`,
	DROP COLUMN `oral`,
	DROP COLUMN `cum`,
	DROP COLUMN `swallow`,
	DROP COLUMN `bdsm`,
	DROP COLUMN `group`,
	DROP COLUMN `anal`,
	DROP COLUMN `threesome`;

/* připojení do obecných kategorií */
ALTER TABLE `user_categories`
	ADD COLUMN `likes` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `tallness`,
	ADD CONSTRAINT `FK_user_categories_category_likes` FOREIGN KEY (`likes`) REFERENCES `category_likes` (`id`) ON UPDATE SET NULL ON DELETE SET NULL;

/* kategorie koho chce poznat */
CREATE TABLE `category_want_to_meet` (
	`id` TINYINT(2) UNSIGNED NOT NULL AUTO_INCREMENT,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

ALTER TABLE `category_want_to_meet`
	ADD COLUMN `want_to_meet_group` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `want_to_meet_couple_women` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `want_to_meet_couple_men` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `want_to_meet_couple` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `want_to_meet_women` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	ADD COLUMN `want_to_meet_men` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0';

/* připojení do obecných kategorií */
ALTER TABLE `user_categories`
	ADD COLUMN `want_to_meet` TINYINT(2) UNSIGNED NULL DEFAULT NULL AFTER `likes`,
	ADD CONSTRAINT `FK_user_categories_category_want_to_meet` FOREIGN KEY (`want_to_meet`) REFERENCES `category_want_to_meet` (`id`) ON UPDATE SET NULL ON DELETE SET NULL;


