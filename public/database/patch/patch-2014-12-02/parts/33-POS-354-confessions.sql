/* přidání políček pro počet lajků a komentářů u přiznání */
ALTER TABLE `confessions`
	ADD COLUMN `likes` INT(5) UNSIGNED NOT NULL DEFAULT '0' AFTER `adminID`,
	ADD COLUMN `comments` INT(5) UNSIGNED NOT NULL DEFAULT '0' AFTER `likes`;
