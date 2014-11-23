/* tyto tri sloupce maji vychozi hodnotu null */
ALTER TABLE `couple`
	CHANGE COLUMN `smoke` `smoke` VARCHAR(11) NULL DEFAULT NULL AFTER `bra_size`,
	CHANGE COLUMN `drink` `drink` VARCHAR(11) NULL DEFAULT NULL AFTER `smoke`,
	CHANGE COLUMN `graduation` `graduation` VARCHAR(11) NULL DEFAULT NULL AFTER `drink`;

/* prodloužení sloupce marital_state na 18 znaků */
ALTER TABLE `enum_marital_state`
	CHANGE COLUMN `marital_state` `marital_state` VARCHAR(18) NULL DEFAULT NULL AFTER `id`;

