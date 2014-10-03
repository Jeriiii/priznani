<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * Tabulka se zablokovanými uživateli.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class UserBlokedDao extends AbstractDao {

	const TABLE_NAME = "users_bloked";

	/* Column name */
	const COLUMN_ID = "id";
	const COLUMN_OWNER_ID = "ownerID";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

}
