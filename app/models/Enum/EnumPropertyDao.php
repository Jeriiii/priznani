<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * slouží k práci s enumy pro property
 *
 * @author Daniel Holubář
 */
class EnumPropertyDao extends BaseEnumDao {

	const TABLE_NAME = "enum_property";

	/* Column name */
	const COLUMN_ID = "id";
	const COLUMN_NAME = "name";
	const COLUMN_CZ_NAME = "czname";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	public function getColumnName() {
		return self::COLUMN_CZ_NAME;
	}

}
