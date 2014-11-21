<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * Tabulka k navázání přátelství.
 * Přátelství funguje mezi přáteli A a B, jen když je vazba A,B a zároveň B,A.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class FriendDao extends AbstractDao {

	const TABLE_NAME = "friends";

	/* Column name */
	const COLUMN_USER_ID_1 = "user1ID";
	const COLUMN_USER_ID_2 = "user2ID";

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
	 * @param int $limit
	 * @param int $offset
	 * @return \Nette\Database\Table\Selection
	 */
	public function getList($userID, $limit = 0, $offset = 0) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_USER_ID_1, $userID);
		if ($limit != 0) {
			$sel->limit($limit, $offset);
		}
		return $sel;
	}

	/**
	 * Vrátí seznam kontaktů (přátel) daného uživatele
	 * @param int $idUser id uživatele
	 * @return Nette\Database\Table\Selection seznam kontaktů (spojený s tabulkou uživatelů)
	 */
	public function getUsersContactList($idUser) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_USER_ID_1, $idUser);
		return $sel;
	}

	/**
	 * Testuje, zda jsou dva uživatelé kamarádi
	 * @param int $userID ID prvního uživatele
	 * @param int $friendID ID druhého uživatele
	 * @return boolean
	 */
	public function isFriend($userID, $friendID) {
		if ($userID == $friendID) {
			return true;
		}
		$sel = $this->getTable();
		$sel->where(array(self::COLUMN_USER_ID_1 => $userID, self::COLUMN_USER_ID_2 => $friendID));
		if ($sel->fetch()) {
			return true;
		}
		return false;
	}

}
