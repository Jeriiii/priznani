/* Vlo�� testovac� sout� */
INSERT INTO `postest`.`users_competitions` (`id`, `name`, `description`) VALUES ('1', 'Test competition', 'Test competition');
/* Vlo�� testovac� obr�zky pro testovac� sout� */
INSERT INTO `postest`.`competitions_images` (`imageID`, `userID`, `competitionID`, `allowed`) VALUES (3, 3, 1, 1);
INSERT INTO `postest`.`competitions_images` (`imageID`, `userID`, `competitionID`, `allowed`) VALUES (2, 3, 1, 1);