/* vytvoří tabulku přátel */

CREATE TABLE `friends` (
	`userID1` INT(11) UNSIGNED NOT NULL,
	`userID2` INT(11) UNSIGNED NOT NULL,
	PRIMARY KEY (`userID1`, `userID2`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

ALTER TABLE `friends`
	ADD CONSTRAINT `userID1` FOREIGN KEY (`userID1`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	ADD CONSTRAINT `userID2` FOREIGN KEY (`userID2`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE `friends`
	COMMENT='Přátelství funguje mezi přáteli A a B, jen když je vazba A,B a B,A.';

ALTER TABLE `friends`
	ADD COLUMN `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT FIRST,
	DROP PRIMARY KEY,
	ADD PRIMARY KEY (`id`),
	ADD INDEX `userID1` (`userID1`);


/* vytvoří tabulku žádostí o přátelství */
CREATE TABLE `friendRequest` (
	`userIDFrom` INT(11) UNSIGNED NOT NULL,
	`userIDTo` INT(11) UNSIGNED NOT NULL,
	`message` VARCHAR(200) NOT NULL,
	`create` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

ALTER TABLE `friendrequest`
	ADD COLUMN `id` INT(11) UNSIGNED NOT NULL FIRST;

ALTER TABLE `friendrequest`
	CHANGE COLUMN `id` `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT FIRST,
	ADD PRIMARY KEY (`id`);

ALTER TABLE `friendrequest`
	ADD CONSTRAINT `FK_friendrequest_users` FOREIGN KEY (`userIDFrom`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	ADD CONSTRAINT `FK_friendrequest_users_2` FOREIGN KEY (`userIDTo`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;

/* vytvoří tabulku sledování */

CREATE TABLE `follows` (
	`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`userIDFrom` INT(11) UNSIGNED NOT NULL,
	`userIDTo` INT(11) UNSIGNED NOT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

ALTER TABLE `follows`
	ADD CONSTRAINT `FK_follows_users` FOREIGN KEY (`userIDFrom`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	ADD CONSTRAINT `FK_follows_users_2` FOREIGN KEY (`userIDTo`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE `follows`
	COMMENT='Sledování = odebírání příspěvků od uživatele bez přátelství';

ALTER TABLE `follows`
	COMMENT='Sexy = odebírání příspěvků od uživatele bez přátelství, možnost jak nenápadně naznačit že se chce spřátelit';
RENAME TABLE `follows` TO `you_are_sexy`;

ALTER TABLE `friends`
	ALTER `userID1` DROP DEFAULT,
	ALTER `userID2` DROP DEFAULT;
ALTER TABLE `friends`
	CHANGE COLUMN `userID1` `user1ID` INT(11) UNSIGNED NOT NULL AFTER `id`,
	CHANGE COLUMN `userID2` `user2ID` INT(11) UNSIGNED NOT NULL AFTER `user1ID`;

ALTER TABLE `you_are_sexy`
	ALTER `userIDFrom` DROP DEFAULT,
	ALTER `userIDTo` DROP DEFAULT;
ALTER TABLE `you_are_sexy`
	CHANGE COLUMN `userIDFrom` `userFromID` INT(11) UNSIGNED NOT NULL AFTER `id`,
	CHANGE COLUMN `userIDTo` `userToID` INT(11) UNSIGNED NOT NULL AFTER `userFromID`;

ALTER TABLE `friendrequest`
	ALTER `userIDFrom` DROP DEFAULT,
	ALTER `userIDTo` DROP DEFAULT;
ALTER TABLE `friendrequest`
	CHANGE COLUMN `userIDFrom` `userFromID` INT(11) UNSIGNED NOT NULL AFTER `id`,
	CHANGE COLUMN `userIDTo` `userToID` INT(11) UNSIGNED NOT NULL AFTER `userFromID`;
