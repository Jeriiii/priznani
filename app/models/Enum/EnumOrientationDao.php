<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * slouží pro práci s enumy pro sexualní orientaci
 *
 * @author Daniel Holubář
 */
class EnumOrientationDao extends BaseEnumDao {

	const TABLE_NAME = "enum_orientation";

	/* Column name */
	const COLUMN_ID = "id";
	const COLUMN_ORIENTATION = "orientation";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	public function getColumnName() {
		return self::COLUMN_ORIENTATION;
	}

}
