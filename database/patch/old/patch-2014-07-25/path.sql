/* 01 */

/* Vytvoření tabulky payments pro přehled plateb uživatelů. */
CREATE TABLE `payments` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`userID` INT(11) UNSIGNED NOT NULL DEFAULT '0',
	`create` DATETIME NULL DEFAULT NULL,
	PRIMARY KEY (`id`),
	INDEX `FK_payments_users` (`userID`),
	CONSTRAINT `FK_payments_users` FOREIGN KEY (`userID`) REFERENCES `users` (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

/* 02 */

/* smazání sloupce */
ALTER TABLE `users_properties`
	DROP COLUMN `id_couple`;

/* 03 */

/* Vložení testovacích uživatelů admina a usera s příslušnými daty */
insert into users
values	(3, NULL, NULL, 1, 2650, 'user', DATE_ADD(CURRENT_TIMESTAMP(),INTERVAL 12 DAY), CURRENT_TIMESTAMP(), 'user@test.cz', 'Test User', '125d6d03b32c84d492747f79cf0bf6e179d287f341384eb5d6d3197525ad6be8e6df0116032935698f99a09e265073d1d6c32c274591bf1d0a20ad67cba921bc'),
			(4, NULL, NULL, 1, 8796, 'admin', DATE_ADD(CURRENT_TIMESTAMP(),INTERVAL 16 DAY), CURRENT_TIMESTAMP(), 'admin@test.cz', 'Test Admin', '125d6d03b32c84d492747f79cf0bf6e179d287f341384eb5d6d3197525ad6be8e6df0116032935698f99a09e265073d1d6c32c274591bf1d0a20ad67cba921bc');
			
/* Vložení testovacích dat pro uživatele z tabulyk výše */			
insert into users_properties
values	(3, 'man', 'couple', 'Oh bože, už budu.', 'Hledám zábavu a vzrušení.', 'free', 'hetero', '180', '5', 'abnormal', 'normal', 'sometimes', 'often', 'vš', 'b', 'black', 1, 0, 1, 1, 0, 0, 1, 0, 1, 1, 0, 1, 0, 1, 0, 0, 1, 0),
		(4, 'woman', 'man', 'To je ale macek.', 'Moc ráda bych nějakýho svalouše co to umí v posteli.', 'free', 'hetero', '160', NULL, NULL, NULL, 'often', 'no', 'sos', 'c', 'blond', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1);

/* Provázání uživatelů s jejich user_properties */			
update users SET propertyID = 3 WHERE id = 3;

update users SET propertyID = 4 WHERE id = 4;

/* 04 */

/* Vytvoření tabulky status */
CREATE TABLE `status` (
	`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`userID` INT(11) UNSIGNED NOT NULL DEFAULT '0',
	`text` VARCHAR(600) NULL DEFAULT NULL,
	PRIMARY KEY (`id`),
	INDEX `FK_status_users` (`userID`),
	CONSTRAINT `FK_status_users` FOREIGN KEY (`userID`) REFERENCES `users` (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

/* 05 */

/* vložení sloupce pro věk */
ALTER TABLE `users_properties`
	ADD COLUMN `age` INT(11) UNSIGNED NULL DEFAULT NULL AFTER `id`;

/* vložení sloupce pro profilové foto */
ALTER TABLE `users`
	ADD COLUMN `profilFotoID` INT(11) UNSIGNED NULL DEFAULT NULL AFTER `coupleID`;
ALTER TABLE `users`
	ADD CONSTRAINT `FK_users_user_images` FOREIGN KEY (`profilFotoID`) REFERENCES `user_images` (`id`) ON UPDATE CASCADE;

/* změna šířky sloupce na 1 */
ALTER TABLE `user_galleries`
	CHANGE COLUMN `default` `default` TINYINT(1) NULL DEFAULT '0' AFTER `more`;

/* přidání sloupce pro profilovou galerii */
ALTER TABLE `user_galleries`
	ADD COLUMN `profil_gallery` TINYINT UNSIGNED NOT NULL DEFAULT '0' AFTER `default`;

/* 06 */

/* vytvoření tabulky pro seznam aktivit */
CREATE TABLE `activities` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`event_type` VARCHAR(50) NULL DEFAULT NULL,
	`imageID` INT(11) UNSIGNED NULL DEFAULT NULL,
	`statusID` INT(11) UNSIGNED NULL DEFAULT NULL,
	`event_ownerID` INT(11) UNSIGNED NULL DEFAULT NULL,
	`event_creatorID` INT(11) UNSIGNED NULL DEFAULT NULL,
	PRIMARY KEY (`id`),
	INDEX `FK_activities_user_images` (`imageID`),
	INDEX `FK_activities_status` (`statusID`),
	INDEX `FK_activities_users` (`event_ownerID`),
	INDEX `FK_activities_users_2` (`event_creatorID`),
	CONSTRAINT `FK_activities_status` FOREIGN KEY (`statusID`) REFERENCES `status` (`id`),
	CONSTRAINT `FK_activities_users` FOREIGN KEY (`event_ownerID`) REFERENCES `users` (`id`),
	CONSTRAINT `FK_activities_users_2` FOREIGN KEY (`event_creatorID`) REFERENCES `users` (`id`),
	CONSTRAINT `FK_activities_user_images` FOREIGN KEY (`imageID`) REFERENCES `user_images` (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

/* 07 */

/* Přidá sloupeček do tabulky activities */
ALTER TABLE `activities`
	ADD COLUMN `viewed` TINYINT NULL DEFAULT '0' AFTER `event_creatorID`;

/* 08 */

/* smazání nepotřebných tabulek */
DROP TABLE `facebook`;
DROP TABLE `wall_items`;
DROP TABLE `pages_galleries`;
DROP TABLE `pages_forms`;
DROP TABLE `pages`;
DROP TABLE `map`;
DROP TABLE `forms_query`;
DROP TABLE `forms3`;
DROP TABLE `forms2`;
DROP TABLE `form_new_send`;
DROP TABLE `news`;
DROP TABLE `news_galleries`;
DROP TABLE `google_analytics`;
DROP TABLE `authorizator_table`;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
DROP TABLE `texts`;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;