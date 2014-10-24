<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * slouží pro práci s enumy pro velikost prsou
 *
 * @author Daniel Holubář
 */
class EnumBraSizeDao extends AbstractDao {

	const TABLE_NAME = "enum_bra_size";

	/* Column name */
	const COLUMN_ID = "id";
	const COLUMN_BRA_SIZE = "bra_size";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

}
