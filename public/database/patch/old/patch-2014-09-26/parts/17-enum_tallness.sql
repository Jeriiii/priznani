/* vytvoření enumu na výšku */
CREATE TABLE `enum_talness` (
	`id` TINYINT(2) UNSIGNED NOT NULL AUTO_INCREMENT,
	`tallness` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

RENAME TABLE `enum_talness` TO `enum_tallness`;

/* navázání na tabulku user_properties */

ALTER TABLE `users_properties`
	ADD COLUMN `tallness` TINYINT(2) UNSIGNED NULL DEFAULT NULL AFTER `about_me`,
	DROP COLUMN `tallness`,
	ADD CONSTRAINT `FK_users_properties_enum_tallness` FOREIGN KEY (`tallness`) REFERENCES `enum_tallness` (`id`) ON UPDATE SET NULL ON DELETE SET NULL;

/* navázání na tabulku stream_categories */
ALTER TABLE `stream_categories`
	ADD COLUMN `tallness` TINYINT(2) UNSIGNED NOT NULL DEFAULT '0' AFTER `want_to_meet_men`;