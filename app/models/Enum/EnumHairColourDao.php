<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * slouží pro práci s enumy pro barvu vlasů
 *
 * @author Daniel Holubář
 */
class EnumHairColourDao extends AbstractDao {

	const TABLE_NAME = "enum_hair_colour";

	/* Column name */
	const COLUMN_ID = "id";
	const COLUMN_HAIR_COLOUR = "hair_colour";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

}
