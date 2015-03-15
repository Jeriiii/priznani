/* přidá políčko pro počet lajků statusu */
ALTER TABLE `status`
	ADD COLUMN `comments` INT(5) UNSIGNED NOT NULL DEFAULT '0' AFTER `likes`;