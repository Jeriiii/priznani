

/* properties */
INSERT INTO `users_properties` (`id`, `coins`, `score`, `age`, `statusID`, `type`, `first_sentence`, `about_me`, `tallness`, `preferencesID`, `threesome`, `anal`, `group`, `bdsm`, `swallow`, `cum`, `oral`, `piss`, `sex_massage`, `petting`, `fisting`, `deepthrought`, `want_to_meet_men`, `want_to_meet_women`, `want_to_meet_couple`, `want_to_meet_couple_men`, `want_to_meet_couple_women`, `want_to_meet_group`, `cityID`, `districtID`, `regionID`, `marital_state`, `orientation`, `shape`, `penis_length`, `penis_width`, `drink`, `graduation`, `bra_size`, `smoke`, `hair_colour`, `vigor`) VALUES
	(5, 5, 15, '1986-09-10', NULL, 1, 'Jsem hustej týpek.', 'jsem moc kchůl na tenhle nápis', 3, 9355, 1, 0, 1, 0, 1, 1, 1, 1, 1, 0, 1, 1, 0, 2, 1, 1, 0, 0, 9530, 77, 14, 4, 1, 4, 3, 3, 2, 1, 3, 1, 1, 3),
	(6, 40, 0, '1986-09-10', NULL, 2, 'Ráda jím.', 'Moc ráda bych nějakýho svalouše co to umí v posteli.', 2, 10084, 1, 1, 1, 1, 1, 0, 1, 1, 1, 1, 1, 1, 0, 2, 1, 1, 0, 0, 9530, 77, 14, 4, 4, 5, NULL, NULL, 2, 3, 3, 1, 4, 4),
	(7, 20, 100, '1969-06-01', NULL, 3, 'Děláme to každou hodinu.', 'Nikdy nás nebolí hlava.', 3, 10813, 1, 0, 0, 0, 1, 0, 1, 1, 1, 0, 1, 0, 0, 2, 1, 1, 0, 0, 9530, 77, 14, 1, 3, 5, NULL, NULL, 2, 4, 1, 2, 1, NULL),
	(8, 150, 5, '1987-10-09', NULL, 4, 'Žuzláme růžovoučké mufíky.', 'Moc rádi bychom nějakýho svalouše co to umí v posteli.', 5, 11542, 1, 1, 1, 1, 0, 1, 0, 1, 1, 1, 0, 1, 0, 2, 1, 1, 0, 0, 9530, 77, 14, 4, 2, 1, 2, 1, 2, 3, 3, 3, 4, 5),
	(9, 0, 0, '1985-02-10', NULL, 5, 'Jsme jako dvě panenky z tý písničky.', 'Umíme slízat tapety ze zdi.', 4, 13000, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 2, 1, 1, 0, 0, 9530, 77, 14, 1, 2, 2, NULL, NULL, 2, 3, 3, 1, 4, 6),
	(10, 10, 30, '1994-09-11', NULL, 6, 'Deset zadků, dvacet nohou, šestnáct koulí. To jsme my.', 'To není hromada mrtvých tuleňů. To jsme my.', 4, 13000, 1, 1, 0, 1, 1, 1, 1, 1, 0, 1, 1, 1, 0, 2, 1, 1, 0, 0, 9530, 77, 14, 6, 1, 6, NULL, NULL, 2, 3, 6, 3, 5, 7);


/* couple */
INSERT INTO `couple` (`id`, `age`, `vigor`, `marital_state`, `orientation`, `tallness`, `shape`, `type`, `penis_length`, `penis_width`, `bra_size`, `smoke`, `drink`, `graduation`, `hair_colour`) VALUES (2, '1972-01-05', NULL, 'free', 'hetero', 174, '0', 3, '32', '5', 'a', 'often', 'often', 'zs', '1');
INSERT INTO `couple` (`id`, `age`, `vigor`, `marital_state`, `orientation`, `tallness`, `shape`, `type`, `penis_length`, `penis_width`, `bra_size`, `smoke`, `drink`, `graduation`, `hair_colour`) VALUES (3, '1990-04-18', NULL, 'free', 'homo', 190, '0', 4, '15', '3', 'a', 'often', 'often', 'zs', '1');
INSERT INTO `couple` (`id`, `age`, `vigor`, `marital_state`, `orientation`, `tallness`, `shape`, `type`, `penis_length`, `penis_width`, `bra_size`, `smoke`, `drink`, `graduation`, `hair_colour`) VALUES (4, '1969-08-17', NULL, 'free', 'homo', 150, '0', 5, NULL, NULL, 'a', 'often', 'often', 'zs', '1');

/* USERS */
INSERT INTO `users` (`id`, `propertyID`, `coupleID`, `profilFotoID`, `wasCategoryChanged`, `verified`, `confirmed`, `admin_score`, `role`, `last_active`, `last_signed_in`, `first_signed_day_streak`, `created`, `email`, `user_name`, `password`) VALUES 
(5, 5, NULL, NULL, 0, 0, '1', 154, 'user', NULL, NULL, NULL, '2014-07-25 12:01:18', 'man@test.cz', 'Igor', '125d6d03b32c84d492747f79cf0bf6e179d287f341384eb5d6d3197525ad6be8e6df0116032935698f99a09e265073d1d6c32c274591bf1d0a20ad67cba921bc');
INSERT INTO `users` (`id`, `propertyID`, `coupleID`, `profilFotoID`, `wasCategoryChanged`, `verified`, `confirmed`, `admin_score`, `role`, `last_active`, `last_signed_in`, `first_signed_day_streak`, `created`, `email`, `user_name`, `password`) VALUES 
(6, 6, NULL, NULL, 0, 0, '1', 7766, 'user', NULL, NULL, NULL, '2014-07-25 12:01:18', 'woman@test.cz', 'Majka', '125d6d03b32c84d492747f79cf0bf6e179d287f341384eb5d6d3197525ad6be8e6df0116032935698f99a09e265073d1d6c32c274591bf1d0a20ad67cba921bc');
INSERT INTO `users` (`id`, `propertyID`, `coupleID`, `profilFotoID`, `wasCategoryChanged`, `verified`, `confirmed`, `admin_score`, `role`, `last_active`, `last_signed_in`, `first_signed_day_streak`, `created`, `email`, `user_name`, `password`) VALUES 
(7, 7, 2, NULL, 0, 0, '1', 5478, 'user', '2014-04-10 08:01:18', NULL, NULL, '2014-07-25 12:01:18', 'couple@test.cz', 'Párek s hořčicí', '125d6d03b32c84d492747f79cf0bf6e179d287f341384eb5d6d3197525ad6be8e6df0116032935698f99a09e265073d1d6c32c274591bf1d0a20ad67cba921bc');
INSERT INTO `users` (`id`, `propertyID`, `coupleID`, `profilFotoID`, `wasCategoryChanged`, `verified`, `confirmed`, `admin_score`, `role`, `last_active`, `last_signed_in`, `first_signed_day_streak`, `created`, `email`, `user_name`, `password`) VALUES 
(8, 8, 3, NULL, 0, 0, '1', 4688, 'user', NULL, NULL, NULL, '2015-01-25 12:01:18', 'man.couple@test.cz', 'Žaludová dvojka', '125d6d03b32c84d492747f79cf0bf6e179d287f341384eb5d6d3197525ad6be8e6df0116032935698f99a09e265073d1d6c32c274591bf1d0a20ad67cba921bc');
INSERT INTO `users` (`id`, `propertyID`, `coupleID`, `profilFotoID`, `wasCategoryChanged`, `verified`, `confirmed`, `admin_score`, `role`, `last_active`, `last_signed_in`, `first_signed_day_streak`, `created`, `email`, `user_name`, `password`) VALUES 
(9, 9, 4, NULL, 0, 0, '1', 4578, 'user', '2014-05-10 12:01:11', NULL, NULL, '2014-07-25 12:01:18', 'woman.couple@test.cz', 'Lízalky', '125d6d03b32c84d492747f79cf0bf6e179d287f341384eb5d6d3197525ad6be8e6df0116032935698f99a09e265073d1d6c32c274591bf1d0a20ad67cba921bc');
INSERT INTO `users` (`id`, `propertyID`, `coupleID`, `profilFotoID`, `wasCategoryChanged`, `verified`, `confirmed`, `admin_score`, `role`, `last_active`, `last_signed_in`, `first_signed_day_streak`, `created`, `email`, `user_name`, `password`) VALUES 
(100, 10, NULL, NULL, 0, 0, '1', 3215, 'user', '2013-11-10 12:01:18', NULL, NULL, '2014-07-25 12:01:18', 'group@test.cz', 'Termiti', '125d6d03b32c84d492747f79cf0bf6e179d287f341384eb5d6d3197525ad6be8e6df0116032935698f99a09e265073d1d6c32c274591bf1d0a20ad67cba921bc');
