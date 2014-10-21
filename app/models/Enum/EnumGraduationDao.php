<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * slouží pro práci s enumy pro vzdělání
 *
 * @author Daniel Holubář
 */
class EnumGraduationDao extends AbstractDao {

	const TABLE_NAME = "enum_graduation";

	/* Column name */
	const COLUMN_ID = "id";
	const COLUMN_GRADUATION = "graduation";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

}
