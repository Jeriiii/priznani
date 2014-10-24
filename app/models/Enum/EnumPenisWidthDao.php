<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * slouží pro práci s enumy pro šířku penisu
 *
 * @author Daniel Holubář
 */
class EnumPenisWidthDao extends AbstractDao {

	const TABLE_NAME = "enum_penis_width";

	/* Column name */
	const COLUMN_ID = "id";
	const COLUMN_PENIS_WIDTH = "penis_width";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

}
