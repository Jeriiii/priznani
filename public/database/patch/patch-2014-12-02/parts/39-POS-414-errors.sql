/* přidání sloupce do enumu s českýma naázvama */
ALTER TABLE `enum_property`
	ADD COLUMN `czname` VARCHAR(15) NOT NULL AFTER `name`;
