
/* vytvoří tabulku na zprávy i s komentáři*/
-- Dumping structure for table pos.chat_messages
CREATE TABLE IF NOT EXISTS `chat_messages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_sender` int(11) unsigned NOT NULL COMMENT 'kdo to poslal',
  `id_recipient` int(11) unsigned NOT NULL COMMENT 'komu to poslal',
  `text` text,
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 - klasicka zprava',
  `readed` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 - neprecteno, 1/jine - precteno',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8;
/* přidání cizích klíčů a vazeb */
ALTER TABLE `chat_messages`
	ADD INDEX `id_sender_id_recipient` (`id_recipient`, `id_sender`),
	ADD CONSTRAINT `FK_chat_messages_users` FOREIGN KEY (`id_sender`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	ADD CONSTRAINT `FK_chat_messages_users_2` FOREIGN KEY (`id_recipient`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;

