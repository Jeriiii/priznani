<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * Propojí uživatele a novinky
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class UsersNewsDao extends AbstractDao {

	const TABLE_NAME = "users_news";

	/* Column name */
	const COLUMN_ID = "id";
	const COLUMN_USER_ID = "userID";
	const COLUMN_NEW_ID = "newID";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	/**
	 * Vrátí aktuální novinku uživateli.
	 * @param int $userID ID uživatele.
	 * @return \Nette\Database\Table\Selection|boolean
	 */
	public function getActual($userID) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_USER_ID, $userID);
		$sel->order(self::COLUMN_ID . " DESC");
		$activeNew = $sel->fetch();

		if ($activeNew) {
			return $activeNew->new;
		} else {
			return FALSE;
		}
	}

	/**
	 * Smaže novinku uživateli
	 * @param int $userID ID uživatele
	 * @param int $newID ID novinky z tabulky news
	 */
	public function deleteByUser($userID, $newID) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_NEW_ID, $newID);
		$sel->where(self::COLUMN_USER_ID, $userID);
		$sel->delete();
	}

}
