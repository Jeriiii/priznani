/* vytvoření tabulky */
CREATE TABLE `enum_status` (
	`id` TINYINT(3) UNSIGNED NOT NULL,
	`name` VARCHAR(20) NOT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

ALTER TABLE `enum_status`
	CHANGE COLUMN `id` `id` TINYINT(3) UNSIGNED NOT NULL AUTO_INCREMENT FIRST;

/* navázání na tabulku users_preferences */
ALTER TABLE `users_properties`
	ADD COLUMN `status` TINYINT(2) UNSIGNED NULL DEFAULT NULL AFTER `age`;

ALTER TABLE `users_properties`
	ADD CONSTRAINT `FK_users_properties_enum_status` FOREIGN KEY (`status`) REFERENCES `enum_status` (`id`) ON UPDATE SET NULL ON DELETE SET NULL;

ALTER TABLE `users_properties`
	CHANGE COLUMN `status` `statusID` TINYINT(2) UNSIGNED NULL DEFAULT NULL AFTER `age`;
