

/* příprava tabulky kategorií */
INSERT INTO `stream_categories` (`id`, `want_to_meet_group`, `want_to_meet_couple_women`, `want_to_meet_couple_men`, `want_to_meet_couple`, `want_to_meet_women`, `want_to_meet_men`, `fisting`, `petting`, `sex_massage`, `piss`, `oral`, `cum`, `swallow`, `bdsm`, `group`, `anal`, `threesome`) VALUES
	(1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
	(2, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 1, 0, 0, 1, 0),
	(6, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0);
	
/* aktualizace nastavení testovacích uživatelů */	
UPDATE `users_properties` SET `preferencesID`=1 WHERE  `id`=3 LIMIT 1;
UPDATE `users_properties` SET `preferencesID`=2 WHERE  `id`=4 LIMIT 1;