CREATE TABLE `emails_type` (
	`id` INT NULL,
	`name` VARCHAR(20) NULL
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;

ALTER TABLE `emails_type`
	CHANGE COLUMN `id` `id` INT(11) NOT NULL AUTO_INCREMENT FIRST,
	ADD PRIMARY KEY (`id`);

INSERT INTO `pos_cron_emails`.`emails_type` (`name`) VALUES ('Novinky');