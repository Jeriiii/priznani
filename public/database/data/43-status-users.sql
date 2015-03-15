/* vložení testovacích statusů pro různé uživatele */
INSERT INTO `status` (`id`, `userID`, `message`, `likes`, `comments`) VALUES
	(1, 3, 'Status uživatele Test User', 0, 0),
	(2, 4, 'Status uživatele Test Admin', 0, 0),
	(3, 5, 'Status uživatele Igor', 0, 0),
	(4, 6, 'Status uživatele Majka', 0, 0),
	(5, 7, 'Status uživatele Párek s hořčicí', 0, 0),
	(6, 8, 'Status uživatele Žaludová dvojka', 0, 0),
	(7, 9, 'Status uživatele Lízalky', 0, 0);
INSERT INTO `stream_items` (`id`, `videoID`, `galleryID`, `statusID`, `userGalleryID`, `confessionID`, `adviceID`, `userID`, `categoryID`, `type`, `age`, `tallness`) VALUES
	(16680, NULL, NULL, 1, NULL, NULL, NULL, 3, 9241, 0, NULL, 0),
	(16674, NULL, NULL, 2, NULL, NULL, NULL, 4, 10322, 0, NULL, 0),
	(16675, NULL, NULL, 3, NULL, NULL, NULL, 5, 9355, 0, NULL, 0),
	(16676, NULL, NULL, 4, NULL, NULL, NULL, 6, 10084, 0, NULL, 0),
	(16677, NULL, NULL, 5, NULL, NULL, NULL, 7, 10813, 0, NULL, 0),
	(16678, NULL, NULL, 6, NULL, NULL, NULL, 8, 11542, 0, NULL, 0),
	(16679, NULL, NULL, 7, NULL, NULL, NULL, 9, 13000, 0, NULL, 0);