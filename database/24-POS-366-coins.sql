ALTER TABLE `users_properties`
	ADD COLUMN `coins` FLOAT(11) UNSIGNED NOT NULL DEFAULT '0' AFTER `id`;
	
ALTER TABLE `chat_messages`
	ADD COLUMN `checked_by_cron` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0/1 zda už tento sloupec prošel cron přidávající zlatky' AFTER `type`;