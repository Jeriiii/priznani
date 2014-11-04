<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * slouží pro práci s enumy pro výšku
 *
 * @author Daniel Holubář
 */
class EnumTallnessDao extends AbstractDao {

	const TABLE_NAME = "enum_tallness";

	/* Column name */
	const COLUMN_ID = "id";
	const COLUMN_TALLNESS = "tallness";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

}
