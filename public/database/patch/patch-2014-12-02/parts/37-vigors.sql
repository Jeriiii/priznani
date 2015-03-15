/* vytvoří znamení */
CREATE TABLE IF NOT EXISTS `enum_vigors` (
  `id` tinyint(2) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;

/* propojení s tabulkou properties */
ALTER TABLE `users_properties`
	ADD COLUMN `vigor` TINYINT(2) UNSIGNED NULL DEFAULT NULL AFTER `hair_colour`;

ALTER TABLE `users_properties`
	ADD CONSTRAINT `FK_users_properties_enum_vigors` FOREIGN KEY (`vigor`) REFERENCES `enum_vigors` (`id`) ON UPDATE SET NULL ON DELETE SET NULL;

/* propojen9 s tabulkou couples */
ALTER TABLE `couple`
	ADD COLUMN `vigor` TINYINT(2) UNSIGNED NULL DEFAULT NULL AFTER `age`,
	ADD CONSTRAINT `FK_couple_enum_vigors` FOREIGN KEY (`vigor`) REFERENCES `enum_vigors` (`id`) ON UPDATE SET NULL ON DELETE SET NULL;

