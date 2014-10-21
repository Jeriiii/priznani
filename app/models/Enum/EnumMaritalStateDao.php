<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * slouží pro práci s enumy pro stav
 * @author Daniel Holubář
 */
class EnumMaritalStateDao extends AbstractDao {

	const TABLE_NAME = "enum_marital_state";

	/* Column name */
	const COLUMN_ID = "id";
	const COLUMN_MARITAL_STATE = "marital_state";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

}
