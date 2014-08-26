/* defaultní hodnota u want to meet */

ALTER TABLE `users_properties`
	CHANGE COLUMN `want_to_meet_men` `want_to_meet_men` TINYINT(1) NOT NULL DEFAULT '0' AFTER `deepthrought`,
	CHANGE COLUMN `want_to_meet_women` `want_to_meet_women` TINYINT(1) NOT NULL DEFAULT '0' AFTER `want_to_meet_men`,
	CHANGE COLUMN `want_to_meet_couple` `want_to_meet_couple` TINYINT(1) NOT NULL DEFAULT '0' AFTER `want_to_meet_women`,
	CHANGE COLUMN `want_to_meet_couple_men` `want_to_meet_couple_men` TINYINT(1) NOT NULL DEFAULT '0' AFTER `want_to_meet_couple`,
	CHANGE COLUMN `want_to_meet_couple_women` `want_to_meet_couple_women` TINYINT(1) NOT NULL DEFAULT '0' AFTER `want_to_meet_couple_men`,
	CHANGE COLUMN `want_to_meet_group` `want_to_meet_group` TINYINT(1) NOT NULL DEFAULT '0' AFTER `want_to_meet_couple_women`;

/* změna uživatele - věku  */

ALTER TABLE `users_properties`
	ALTER `age` DROP DEFAULT;
ALTER TABLE `users_properties`
	CHANGE COLUMN `age` `age` INT(11) UNSIGNED NOT NULL AFTER `id`;

ALTER TABLE `users_properties`
	ALTER `age` DROP DEFAULT;
ALTER TABLE `users_properties`
	CHANGE COLUMN `age` `age` TINYINT UNSIGNED NOT NULL AFTER `id`;