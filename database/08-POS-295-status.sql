/* přidání políčka pro počet lajků ke statusu */
ALTER TABLE `status`
	ADD COLUMN `likes` INT(5) UNSIGNED NULL DEFAULT '0' AFTER `message`;