/* přidá sloupeček který rozhoduje, jak často se mají posílat emaily o novinkách */

ALTER TABLE `users`
	ADD COLUMN `email_period` CHAR(1) NULL DEFAULT '' COMMENT 'jak často se mají odesílat emaily o novinkách' AFTER `email`;

ALTER TABLE `users`
	CHANGE COLUMN `email_period` `email_news_period` CHAR(1) NOT NULL DEFAULT 'd' COMMENT 'jak často se mají odesílat emaily o novinkách' AFTER `email`,
	ADD COLUMN `email_news_last_sended` DATE NULL DEFAULT NULL AFTER `email_news_period`;

UPDATE `users` SET `email_news_last_sended` = CURDATE();
