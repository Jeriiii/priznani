/* změna věku na datetime */
ALTER TABLE `users_properties`
	ALTER `age` DROP DEFAULT;
ALTER TABLE `users_properties`
	CHANGE COLUMN `age` `age` DATE NOT NULL AFTER `id`;

ALTER TABLE `couple`
	CHANGE COLUMN `age` `age` DATE NULL DEFAULT NULL AFTER `id`;