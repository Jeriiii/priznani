<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * zpřístupní všechny přiznání z pařby
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class PartyDao extends BaseConfessionDao {

	const TABLE_NAME = "party_confessions";

	/* Column name */
	const COLUMN_ID = "id";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

}
