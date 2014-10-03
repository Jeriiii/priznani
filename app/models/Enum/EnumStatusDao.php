<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * Pro zpřístupnění statusu uživatele. Zpřístupňuje Enumy.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class EnumStatusDao extends AbstractDao {

	const TABLE_NAME = "enum_status";

	/* Column name */
	const COLUMN_ID = "id";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

}
