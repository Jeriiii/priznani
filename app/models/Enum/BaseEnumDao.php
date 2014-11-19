<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * Základ pro všechny enumy
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class BaseEnumDao extends AbstractDao {
	/* Column name */

	const COLUMN_ID = "id";
	const COLUMN_NAME = "name";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	/**
	 * Vrátí seznam položek id => name
	 * @return array Seznam položek
	 */
	public function getList() {
		$name = $this->getColumnName();
		$sel = $this->getAll()->fetchPairs(self::COLUMN_ID, $name);
		return $sel;
	}

}
