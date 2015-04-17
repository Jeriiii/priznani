<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * slouží pro práci s enumy pro tvar těla
 *
 * @author Daniel Holubář
 */
class EnumShapeDao extends BaseEnumDao {

	const TABLE_NAME = "enum_shape";

	/* Column name */
	const COLUMN_ID = "id";
	const COLUMN_SHAPE = "shape";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	public function getColumnName() {
		return self::COLUMN_SHAPE;
	}

}
