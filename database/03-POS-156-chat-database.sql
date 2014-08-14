
/* tabulka na zpravy */
CREATE TABLE `chat_messages` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`text` TEXT NULL,
	`type` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0 - klasicka zprava',
	`readed` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0 - neprecteno, 1/jine - precteno',
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;
/* tabulka na zpravy - reference */
ALTER TABLE `chat_messages`
	ADD COLUMN `id_sender` INT(11) UNSIGNED NOT NULL COMMENT 'kdo to poslal' AFTER `id`,
	ADD COLUMN `id_recipient` INT(11) UNSIGNED NOT NULL COMMENT 'komu to poslal' AFTER `id_sender`,
	ADD CONSTRAINT `chat_sender` FOREIGN KEY (`id_sender`) REFERENCES `users` (`id`),
	ADD CONSTRAINT `chat_recipient` FOREIGN KEY (`id_recipient`) REFERENCES `users` (`id`);

