<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * Contact ContactDao
 * slouží k práci se zprávami od uživatelů
 *
 * @author Daniel Holubář
 */
class ContactDao extends AbstractDao {

	/** @var Nette\Database */
	protected $database;

	const TABLE_NAME = "contacts";

	/* Column name */
	const COLUMN_ID = "id";
	const COLUMN_USER_ID = "userID";
	const COLUMN_PHONE = "phone";
	const COLUMN_EMAIL = "email";
	const COLUMN_TEXT = "text";
	const COLUMN_VIEWED = "viewed";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	/**
	 * Vloží nový záznam do tabulky
	 * @param int $userID ID uživatele
	 * @param string $email Email uživatele
	 * @param string $phone Telefon uživatele
	 * @param string $text Obsah zprávy
	 */
	public function addNewContact($userID, $email, $phone, $text) {
		$sel = $this->getTable();
		$sel->insert(array(
			"userID" => $userID,
			"email" => $email,
			"phone" => $phone,
			"text" => $text
		));
	}

	/**
	 * Označí zprávu jako přečtenou podle jejího ID
	 * @param type $messageID ID zprávy, která bude označena jako přečtená
	 */
	public function markViewed($messageID) {
		$sel = $this->getTable()->get($messageID);
		$sel->update(array(
			"viewed" => 1
		));
	}

	/**
	 * Vrátí počet zpráv podle počtu id
	 * @return int
	 */
	public function getCount() {
		$sel = $this->getTable();
		return $sel->count(self::COLUMN_ID);
	}

	/**
	 * Vrátí část zpráv podle požadavků paginatoru
	 * @param type int $limit Počet zpráv
	 * @param type int $offset Velikost množiny pro výběr
	 * @return Nette\Database\Table\Selection
	 */
	public function getLimit($limit, $offset = 0) {
		$sel = $this->getTable();
		$sel->limit($limit, $offset);
		return $sel;
	}

	/**
	 * Vrátí počet nepřečtených zpráv od uživatelů
	 * @return int
	 */
	public function getUnviewedCount() {
		$sel = $this->getTable();
		$sel->select('id');
		$sel->where(self::COLUMN_VIEWED, 0);
		return $sel->count();
	}

}
