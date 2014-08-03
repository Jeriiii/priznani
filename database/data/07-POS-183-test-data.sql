/* Vloží testovací soutěž */
INSERT INTO `postest`.`users_competitions` (`name`, `description`) VALUES ('Test competition', 'Test competition');
/* Získá id poslední vložené soutěže, potřebné pro FK v dolní části */
SELECT @last_id := MAX(id) FROM `postest`.`users_competitions`;
/* Vloží testovací obrázky pro testovací soutěž */
INSERT INTO `postest`.`competitions_images` (`imageID`, `userID`, `competitionID`, `allowed`) VALUES (3, 3, @last_id, 1);
INSERT INTO `postest`.`competitions_images` (`imageID`, `userID`, `competitionID`, `allowed`) VALUES (2, 3, @last_id, 1);