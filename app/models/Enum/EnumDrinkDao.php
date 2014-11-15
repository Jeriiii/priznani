<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * slouží pro práci s enumy pro pití
 *
 * @author Daniel Holubář
 */
class EnumDrinkDao extends BaseEnumDao {

	const TABLE_NAME = "enum_drink";

	/* Column name */
	const COLUMN_ID = "id";
	const COLUMN_DRINK = "drink";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	public function getColumnName() {
		return self::COLUMN_DRINK;
	}

}
