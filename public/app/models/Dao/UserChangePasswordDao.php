<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * Slouží ke správě ticketů zapomenutého hesla
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class UserChangePasswordDao extends AbstractDao {

	/** @var Nette\Database */
	protected $database;

	const TABLE_NAME = "user_change_password";

	/* Column name */
	const COLUMN_ID = "id";
	const COLUMN_USER_ID = "userID";
	const COLUMN_TICKET = "ticket";
	const COLUMN_CREATE = "create";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	/**
	 * Přidá nový ticket
	 * @param int $userID ID uživatele
	 * @param String $ticket string k uložení
	 */
	public function addNewTicket($userID, $ticket) {
		$sel = $this->getTable();
		$sel->insert(array(
			"userID" => $userID,
			"ticket" => $ticket,
		));
	}

	/**
	 * Najde ticket v tabulce
	 * @param String $ticket hledaný string
	 * @return Nette\Database\Table\ActiveRow
	 */
	public function findTicket($ticket) {
		$sel = $this->getTable();
		return $sel->where('ticket', $ticket)->fetch();
	}

	/**
	 * Smaže ticekty uživatele
	 * @param type $userID ID uživatele
	 */
	public function deleteUserTickets($userID) {
		$sel = $this->getTable();
		$sel->where('userID', $userID);
		$sel->delete();
	}

}
