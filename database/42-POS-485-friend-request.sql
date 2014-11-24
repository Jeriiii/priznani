/* propojení aktivit a žádostí o přátelství */
ALTER TABLE `activities`
	ADD COLUMN `friendRequest` INT(11) UNSIGNED NULL DEFAULT NULL AFTER `commentImageID`,
	ADD CONSTRAINT `FK_activities_friendrequest` FOREIGN KEY (`friendRequest`) REFERENCES `friendrequest` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE `activities`
	CHANGE COLUMN `friendRequest` `friendRequestID` INT(11) UNSIGNED NULL DEFAULT NULL AFTER `commentImageID`;