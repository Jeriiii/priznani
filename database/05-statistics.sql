/* změna create z TIME na DATETIME */
ALTER TABLE `stream_items`
	ALTER `create` DROP DEFAULT;
ALTER TABLE `stream_items`
	CHANGE COLUMN `create` `create` DATETIME NOT NULL AFTER `type`;

ALTER TABLE `stream_items`
	CHANGE COLUMN `create` `create` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `type`;

/* čas vytvoření uživatelských fotek */
ALTER TABLE `user_images`
	ADD COLUMN `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `checkApproved`;

