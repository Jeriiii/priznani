/* přidá sloupeček na ujištění, zda byly zaslány oznámení o nové seznamce emailem */
ALTER TABLE `users_old`
	ADD COLUMN `sendNotify` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `password`;
