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

	/**
	 * Vrátí blokované uživatele daného uživatele.
	 * @param int $userID Id uživatele, od kterého chceme zjistit blokované uživatele.
	 * @param int $limit
	 * @param int $offset
	 * @return Nette\Database\Table\Selection
	 */
	public function getBlokedUsers($userID, $limit = 0, $offset = 0) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_OWNER_ID, $userID);
		if ($limit != 0) {
			$sel->limit($limit, $offset);
		}
		return $sel;
	}

}
