/* vytvoření výčtového typu property */
CREATE TABLE `enum_property` (
	`id` TINYINT(1) UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(15) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

ALTER TABLE `enum_property`
	CHANGE COLUMN `name` `name` VARCHAR(15) NOT NULL AFTER `id`;

/* přejmenování tabulky pro kategorii, přiložení pohlaví čkověka */
RENAME TABLE `category_want_to_meet` TO `category_property_want_to_meet`;
ALTER TABLE `user_categories`
	CHANGE COLUMN `want_to_meet` `property_want_to_meet` TINYINT(2) UNSIGNED NULL DEFAULT NULL AFTER `likes`;

ALTER TABLE `category_property_want_to_meet`
	ADD COLUMN `user_property` TINYINT(1) UNSIGNED NOT NULL AFTER `want_to_meet_men`;

/* zvětšení rozsahu sloupce id */
ALTER TABLE `user_categories`
	DROP FOREIGN KEY `FK_user_categories_category_want_to_meet`;

ALTER TABLE `category_property_want_to_meet`
	CHANGE COLUMN `id` `id` INT(5) UNSIGNED NOT NULL AUTO_INCREMENT FIRST;

ALTER TABLE `user_categories`
	CHANGE COLUMN `property_want_to_meet` `property_want_to_meet` INT UNSIGNED NULL DEFAULT NULL AFTER `likes`,
	ADD CONSTRAINT `FK_user_categories_category_property_want_to_meet` FOREIGN KEY (`property_want_to_meet`) REFERENCES `category_property_want_to_meet` (`id`) ON UPDATE SET NULL ON DELETE SET NULL;

ALTER TABLE `category_property_want_to_meet`
	ADD UNIQUE INDEX `all_colums` (`want_to_meet_group`, `want_to_meet_couple_women`, `want_to_meet_couple_men`, `want_to_meet_couple`, `want_to_meet_women`, `want_to_meet_men`, `user_property`);



