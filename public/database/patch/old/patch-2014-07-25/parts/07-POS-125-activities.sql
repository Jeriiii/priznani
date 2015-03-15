/* Přidá sloupeček do tabulky activities */
ALTER TABLE `activities`
	ADD COLUMN `viewed` TINYINT NULL DEFAULT '0' AFTER `event_creatorID`;