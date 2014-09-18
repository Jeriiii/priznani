/* zvětšení sloupce na status */
ALTER TABLE `enum_status`
	ALTER `name` DROP DEFAULT;
ALTER TABLE `enum_status`
	CHANGE COLUMN `name` `name` VARCHAR(25) NOT NULL AFTER `id`;
