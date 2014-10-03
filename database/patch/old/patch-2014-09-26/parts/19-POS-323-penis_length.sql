/* smaže tabulku enum_penis_length a relace na ní*/
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
DROP TABLE `enum_penis_length`;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;

ALTER TABLE `users_properties`
	DROP INDEX `FK_users_properties_enum_penis_length`,
	DROP FOREIGN KEY `FK_users_properties_enum_penis_length`;