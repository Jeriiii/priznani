<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * Pro práci s enumy.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class EnumVigorDao extends BaseEnumDao {

	const TABLE_NAME = "enum_vigors";
	const COLUMN_ID = "id";
	const COLUMN_NAME = "name";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	public function getColumnName() {
		return self::COLUMN_NAME;
	}

}
