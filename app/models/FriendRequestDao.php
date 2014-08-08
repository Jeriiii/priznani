<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * Tabulka pro žádosti o přátelství.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class FriendRequestDao extends AbstractDao {

	const TABLE_NAME = "friendrequest";

	/* Column name */
	const COLUMN_ID = "id";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

}
