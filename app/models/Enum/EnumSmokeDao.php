<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * slouží pro práci s enumy pro kouření
 *
 * @author Daniel Holubář
 */
class EnumSmokeDao extends BaseEnumDao {

	const TABLE_NAME = "enum_smoke";

	/* Column name */
	const COLUMN_ID = "id";
	const COLUMN_SMOKE = "smoke";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	public function getColumnName() {
		return self::COLUMN_SMOKE;
	}

}
