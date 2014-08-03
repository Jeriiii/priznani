/* Vloží testovací soutěž */
INSERT INTO `users_competitions` (`name`, `description`, `imageUrl`) VALUES ('Test competition', 'Test competition', 'images/galleries/5/79.JPG');
/* Získá id poslední vložené soutěže, potřebné pro FK v dolní části */
SELECT @last_id := MAX(id) FROM `users_competitions`;
/* Vloží testovací obrázky pro testovací soutěž */
INSERT INTO `competitions_images` (`imageID`, `userID`, `competitionID`, `allowed`) VALUES (3, 3, @last_id, 1);
INSERT INTO `competitions_images` (`imageID`, `userID`, `competitionID`, `allowed`) VALUES (2, 3, @last_id, 1);