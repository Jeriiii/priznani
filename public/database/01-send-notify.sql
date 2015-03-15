/* přidání sloupečku na odeslání oznámení emailem */
ALTER TABLE `activities`
	ADD COLUMN `sendNotify` TINYINT(1) NULL DEFAULT '0' COMMENT 'Odeslání emailu o aktivitě' AFTER `viewed`;
ALTER TABLE `chat_messages`
	ADD COLUMN `sendNotify` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '1 = bylo odesláno oznámení emailem o nové zprávě' AFTER `readed`;