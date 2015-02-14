/* vytvoření tabulky na konverzace */

CREATE TABLE `conversation` (
	`id` INT UNSIGNED NOT NULL,
	`name` VARCHAR(20) NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

ALTER TABLE `conversation`
	CHANGE COLUMN `id` `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT FIRST;

/* propojení s tabulkami zpráv */
ALTER TABLE `chat_messages`
	ALTER `id_recipient` DROP DEFAULT;
ALTER TABLE `chat_messages`
	CHANGE COLUMN `id_recipient` `id_recipient` INT(11) UNSIGNED NULL COMMENT 'komu to poslal' AFTER `id_sender`,
	ADD COLUMN `id_conversation` INT(11) UNSIGNED NULL COMMENT 'do jaké konverzace zpráva spadá' AFTER `id_recipient`,
	ADD CONSTRAINT `FK_chat_messages_confessions` FOREIGN KEY (`id_conversation`) REFERENCES `confessions` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE `chat_messages`
	DROP FOREIGN KEY `FK_chat_messages_confessions`;
ALTER TABLE `chat_messages`
	ADD CONSTRAINT `FK_chat_messages_confessions` FOREIGN KEY (`id_conversation`) REFERENCES `conversation` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;
