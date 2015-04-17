/* přidání času do chatu */
ALTER TABLE `chat_messages`
	ADD COLUMN `sendedDate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `sendNotify`;
