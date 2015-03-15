<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

use Nette\Database\Table\Selection;

/**
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class OldUserDao extends AbstractDao {

	const TABLE_NAME = "users_old";

	/* Column name */
	const COLUMN_ID = "id";
	const COLUMN_SEND_NOTIFY = "sendNotify";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	/**
	 * Vrátí všechny uživatele, kterým ještě nebyl odeslán email o neové seznamce
	 */
	public function getNoNotify($limit = 100) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_SEND_NOTIFY, 0);
		$sel->limit($limit);

		return $sel;
	}

	/**
	 * Spočítá uživatele, kterým nebyl odeslán email o nové seznamce.
	 * @return int
	 */
	public function countNoNotify() {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_SEND_NOTIFY, 0);
		return $sel->count();
	}

	/**
	 * Upraví odeslané emaily
	 * @param \POS\Model\Selection $usersSendEmail
	 */
	public function updateLimitNotify(Selection $usersSendEmail) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_ID, $usersSendEmail);
		$sel->update(array(
			self::COLUMN_SEND_NOTIFY => 1
		));
	}

}
