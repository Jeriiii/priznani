<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * Sledování.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class FollowDao extends AbstractDao {

	const TABLE_NAME = "follows";

	/* Column name */
	const COLUMN_ID = "id";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

}
