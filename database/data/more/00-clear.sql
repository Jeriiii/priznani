/* vyčistí databázi */
DELETE FROM users;
DELETE FROM users_properties;
DELETE FROM couple;

DELETE FROM user_galleries;

DELETE FROM users_competitions;
INSERT INTO `users_competitions` (`name`, `description`, `imageUrl`) VALUES ('Od fanoušků', 'Všechny super fotky od fanoušků', ' ');