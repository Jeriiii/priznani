/* přidá uživ. fotkám možnost zpětně zkontrolovat schválenou fotku */

ALTER TABLE `user_images`
	ADD COLUMN `checkApproved` TINYINT(1) NULL DEFAULT '0' AFTER `intim`;
