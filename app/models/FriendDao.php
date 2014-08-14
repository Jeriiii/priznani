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
	const COLUMN_USER_ID_1 = "userID1";
	const COLUMN_USER_ID_2 = "userID2";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	/**
	 * Přidá uživatele do přátel.
	 * @param int $userID1
	 * @param int $userID2
	 */
	public function addToFriend($userID1, $userID2) {
		$sel = $this->getTable();
		$sel->insert(array($userID1, $userID2));
		$sel->insert(array($userID2, $userID1));
	}

	/**
	 * Odstraní přátelství mezi dvěma uživateli
	 * @param int $userID1 První uživatel.
	 * @param int $userID2 Druhý uživatel.
	 */
	public function removeRelationship($userID1, $userID2) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_USER_ID_1, $userID1);
		$sel->where(self::COLUMN_USER_ID_2, $userID2);
		$sel->delete();

		$sel = $this->getTable();
		$sel->where(self::COLUMN_USER_ID_1, $userID2);
		$sel->where(self::COLUMN_USER_ID_2, $userID1);
		$sel->delete();
	}

	/**
	 * Vrátí seznam přátel daného uživatele
	 * @param int $userID
	 */
	public function getList($userID) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_USER_ID_1, $userID);
		return $sel;
	}

	/**
	 * Vrátí seznam kontaktů (přátel) daného uživatele
	 * @param int $idUser id uživatele
	 * @return Nette\Database\Table\Selection seznam kontaktů (spojený s tabulkou uživatelů)
	 */
	public function getUsersContactList($idUser) {
		$sel = $this->getTable();
		$sel->select(self::TABLE_NAME . ".*, " . self::COLUMN_USER_ID_2 . ".*"); //spojeni tabulek
		$sel->where(self::COLUMN_USER_ID_1, $idUser);
		return $sel;
	}

}
