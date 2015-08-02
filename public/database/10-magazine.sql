/*vytvoření tabulky pro blog*/

CREATE TABLE IF NOT EXISTS `magazine` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `text` text NOT NULL,
  `url` varchar(200) NOT NULL,
  `homepage` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `order` int(10) unsigned NOT NULL DEFAULT '0',
  `access_rights` varchar(10) DEFAULT 'all',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

ALTER TABLE `magazine`
	ADD COLUMN `excerpt` VARCHAR(200) NOT NULL COMMENT 'Výňatek z článku / popis článku' AFTER `name`;

ALTER TABLE `magazine`
	CHANGE COLUMN `excerpt` `excerpt` VARCHAR(200) NULL DEFAULT NULL COMMENT 'Výňatek z článku / popis článku' AFTER `name`;
