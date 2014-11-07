/* vytvoří znamení */
CREATE TABLE IF NOT EXISTS `enum_vigors` (
  `id` tinyint(2) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;
INSERT INTO `enum_vigors` (`id`, `name`) VALUES
	(1, 'Vodnar'),
	(2, 'Ryby'),
	(3, 'Beran'),
	(4, 'Byk'),
	(5, 'Blizenec'),
	(6, 'Rak'),
	(7, 'Lev'),
	(8, 'Panna'),
	(9, 'Váhy'),
	(10, 'Štír'),
	(11, 'Střelec'),
	(12, 'Kozoroh');

/* propojení s tabulkou properties */
ALTER TABLE `users_properties`
	ADD COLUMN `vigor` TINYINT(2) UNSIGNED NULL DEFAULT NULL AFTER `hair_colour`;

ALTER TABLE `users_properties`
	ADD CONSTRAINT `FK_users_properties_enum_vigors` FOREIGN KEY (`vigor`) REFERENCES `enum_vigors` (`id`) ON UPDATE SET NULL ON DELETE SET NULL;

/* propojen9 s tabulkou couples */
ALTER TABLE `couple`
	ADD COLUMN `vigor` TINYINT(2) UNSIGNED NULL DEFAULT NULL AFTER `age`,
	ADD CONSTRAINT `FK_couple_enum_vigors` FOREIGN KEY (`vigor`) REFERENCES `enum_vigors` (`id`) ON UPDATE SET NULL ON DELETE SET NULL;
