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
	const COLUMN_USER_FROM_ID = "userIDFrom";
	const COLUMN_USER_TO_ID = "userIDTo";
	const COLUMN_MESSAGE = "message";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	/**
	 * Vrátí žádosti o přátelství odesnané uživatelem.
	 * @param int $userFromID
	 * @return \Nette\Database\Table\Selection
	 */
	public function getAllFromUser($userFromID) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_USER_FROM_ID, $userFromID);

		return $sel;
	}

	/**
	 * Vrátí všechny žádosti, které žádají tohoto uživatele o přátelství.
	 * @param int $userToID
	 * @return \Nette\Database\Table\Selection
	 */
	public function getAllToUser($userToID) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_USER_TO_ID, $userToID);

		return $sel;
	}

	/**
	 * Odešle žádost o přítelství
	 * @param int $userIDFrom ID žadatele
	 * @param int $userIDTo ID příjemce
	 * @param string $message Zpráva pro příjemce
	 */
	public function sendRequest($userIDFrom, $userIDTo, $message) {
		$sel = $this->getTable();
		$request = $sel->insert(array(
			self::COLUMN_USER_FROM_ID => $userIDFrom,
			self::COLUMN_USER_TO_ID => $userIDTo,
			self::COLUMN_MESSAGE => $message
		));
		return $request;
	}

}
