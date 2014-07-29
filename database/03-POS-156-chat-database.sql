
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


/* tabulka na kontakty (relacni) */
CREATE TABLE `chat_contacts` (
	`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`id_user` INT(11) UNSIGNED NOT NULL COMMENT 'uživatel, kterému patří tento záznam',
	`id_contact` INT(11) UNSIGNED NOT NULL COMMENT 'uživatel, který je v kontaktech uživatele id_user',
	PRIMARY KEY (`id`),
	CONSTRAINT `chat_user_contact` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`),
	CONSTRAINT `chat_user_in_contact_list` FOREIGN KEY (`id_contact`) REFERENCES `users` (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

