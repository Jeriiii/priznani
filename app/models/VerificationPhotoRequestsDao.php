<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 *
 * @author Daniel Holubář
 */
class VerificationPhotoRequestsDao extends AbstractDao {

	const TABLE_NAME = "verification_photo_requests";

	/* Column name */
	const COLUMN_ID = "id";
	const COLUMN_USER_ID = "userID";
	const COLUMN_USER_ID_2 = "user2ID";
	const COLUMN_ACCEPTED = "accepted";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	/**
	 * Vrátí uživatele,kteří požádali daného uživatele o ověřovací fotku
	 * @param type $userID ID uživatele, kterému kontrolujeme requesty
	 * @return Nette\Database\Table\Selection
	 */
	public function findByUserID($userID) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_USER_ID, $userID);
		$sel->where(self::COLUMN_ACCEPTED, 0);
		return $sel;
	}

	/**
	 * Vrátí uživatele,kteří požádali daného uživatele o ověřovací fotku
	 * @param type $userID ID uživatele, kterému kontrolujeme requesty
	 * @return Nette\Database\Table\Selection
	 */
	public function findByUserID2($userID) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_USER_ID_2, $userID);
		return $sel;
	}

	/**
	 * VYrobí request o fotku
	 * @param type $userID ID uživatele, který je žádán
	 * @param type $userID2 ID uživatele, který žádá fotku
	 */
	public function createRequest($userID, $userID2) {
		$sel = $this->getTable();
		$sel->insert(array(
			self::COLUMN_USER_ID => $userID,
			self::COLUMN_USER_ID_2 => $userID2
		));
	}

	/**
	 * schválí ukázání ověřovací fotky pro uživatele
	 * @param type $userID ID uživatele
	 */
	public function acceptRequest($userID) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_USER_ID_2, $userID);
		$request = $sel->fetch();
		$request->update(array(self::COLUMN_ACCEPTED => 1));
	}

	/**
	 * zamtne as maže ukázání ověřovací fotky pro uživatele
	 * @param type $userID ID uživatele
	 */
	public function rejectRequest($userID) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_USER_ID_2, $userID);
		$request = $sel->fetch();
		$request->delete();
	}

}
