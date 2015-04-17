<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * Novinky
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class NewsDao extends AbstractDao {

	const TABLE_NAME = "news";

	/* Názvy sloupců */
	const COLUMN_ID = "id";
	const COLUMN_TEXT = "text";
	const COLUMN_NAME = "name";
	const COLUMN_RELEASE = "release";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

}
