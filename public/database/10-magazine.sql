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

/* obrázky k blogům */
CREATE TABLE `magazine_images` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`suffix` VARCHAR(10) NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;

/* propojení relace mezi články a obrázky */
ALTER TABLE `magazine_images`
	CHANGE COLUMN `id` `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT FIRST,
	ADD COLUMN `articleID` INT UNSIGNED NULL AFTER `suffix`;

ALTER TABLE `magazine_images`
	ADD CONSTRAINT `FK_magazine_images_magazine` FOREIGN KEY (`articleID`) REFERENCES `magazine` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;

/* vyšlé články */
ALTER TABLE `magazine`
	ADD COLUMN `release` TINYINT(1) UNSIGNED NULL DEFAULT '0' AFTER `access_rights`;
