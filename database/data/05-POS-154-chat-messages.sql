

/* vloží do tabulky zpráv vycpávkové zprávy */
-- Dumping data for table pos.chat_messages: ~40 rows (approximately)
/*!40000 ALTER TABLE `chat_messages` DISABLE KEYS */;
INSERT INTO `chat_messages` (`id`, `id_sender`, `id_recipient`, `text`, `type`, `readed`) VALUES
	(1, 87, 87, 'fsdfdf', 0, 1),
	(2, 87, 87, 'dwa', 0, 1),
	(3, 87, 87, 'd', 0, 1),
	(4, 1, 87, 'werwer', 0, 1),
	(5, 1, 87, 'fsdf', 0, 1),
	(6, 1, 87, 'werwe', 0, 1),
	(7, 1, 87, 'wer', 0, 1),
	(22, 87, 86, 'wrew', 0, 1),
	(23, 87, 1, 'wrerw', 0, 1),
	(24, 87, 1, 'eete', 0, 1),
	(25, 1, 87, 'egt', 0, 1),
	(26, 1, 87, 'dvě okna?', 0, 1),
	(27, 1, 87, 'wrw', 0, 1),
	(28, 87, 86, 'sfsfeet', 0, 1),
	(29, 87, 87, 'ert', 0, 1),
	(30, 1, 86, 'tee', 0, 0),
	(31, 1, 87, 'ert', 0, 1),
	(32, 87, 87, 'esrfsef', 0, 1),
	(33, 94, 87, 'fsdf', 0, 1),
	(34, 18, 1, 'sdfs', 0, 1),
	(35, 1, 91, 'svfs', 0, 1),
	(36, 1, 87, 'odjerryho', 0, 1),
	(37, 87, 86, 'sfertwt', 0, 1),
	(38, 87, 1, 'odtestu', 0, 1),
	(39, 87, 87, 'ahoj', 0, 1),
	(40, 1, 87, 'jsi tu?', 0, 1),
	(41, 1, 87, 'aha, tak nic', 0, 1),
	(42, 87, 94, 'afrfe', 0, 0),
	(43, 1, 87, 'dfggd', 0, 1),
	(44, 1, 87, 'jsi tu?', 0, 1),
	(45, 1, 87, 'nesji tu?', 0, 1),
	(46, 1, 87, 'jsi tu?', 0, 1),
	(47, 87, 1, 'teeees', 0, 1),
	(48, 1, 4, 'Ahoj já jsem Jerry', 0, 0),
	(49, 1, 4, 'Jak se máš?', 0, 0),
	(50, 3, 4, 'Ahoj, jsem první test uživatel.', 0, 1),
	(51, 3, 4, 'Jak je?', 0, 1),
	(52, 3, 4, 'Jde to?', 0, 1),
	(53, 4, 3, 'Čau.', 0, 1),
	(54, 4, 3, 'Není to špatný.', 0, 1);
/*!40000 ALTER TABLE `chat_messages` ENABLE KEYS */;
/*!40014 SET FOREIGN_KEY_CHECKS=1 */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;

INSERT INTO `friends` (`user1ID`, `user2ID`) VALUES (4, 3);
INSERT INTO `friends` (`user1ID`, `user2ID`) VALUES (3, 4);
INSERT INTO `friends` (`user1ID`, `user2ID`) VALUES (3, 1);
