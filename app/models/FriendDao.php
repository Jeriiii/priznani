<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * Tabulka k navázání přátelství.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class FriendDao extends AbstractDao {

	const TABLE_NAME = "friends";

	/* Column name */
	const COLUMN_ID = "id";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

}
