/* změna názvu sloupce u aktivit */
ALTER TABLE `activities`
	CHANGE COLUMN `event_type` `type` VARCHAR(50) NULL DEFAULT NULL AFTER `id`;

/* propejení aktivit s komentářema */
ALTER TABLE `activities`
	ADD COLUMN `commentID` INT(11) UNSIGNED NULL DEFAULT NULL AFTER `statusID`,
	ADD CONSTRAINT `FK_activities_like_comments` FOREIGN KEY (`commentID`) REFERENCES `like_comments` (`id`) ON UPDATE CASCADE ON DELETE NO ACTION;

ALTER TABLE `activities`
	DROP FOREIGN KEY `FK_activities_like_comments`;
ALTER TABLE `activities`
	ADD CONSTRAINT `FK_activities_like_comments` FOREIGN KEY (`commentID`) REFERENCES `comment_images` (`id`) ON UPDATE CASCADE ON DELETE NO ACTION;
RENAME TABLE `like_comments` TO `like_image_comments`;

ALTER TABLE `activities`
	CHANGE COLUMN `commentID` `commentImageID` INT(11) UNSIGNED NULL DEFAULT NULL AFTER `statusID`;