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
	COMMENT='Přátelství funguje i když je vazba A,B i B,A ale ne najednou.';

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

