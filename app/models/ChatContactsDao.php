<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

use POS\Model\UserDao;

/**
 * NAME DAO NAMEDao
 * slouží k
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
class ChatContactsDao extends AbstractDao {

	const TABLE_NAME = "chat_contacts";

	/* Column name */
	const COLUMN_ID = "id";
	const COLUMN_ID_USER = "id_user";
	const COLUMN_ID_CONTACT = "id_contact";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	/**
	 * Vrátí seznam kontaktů daného uživatele
	 * @param int $idUser id uživatele
	 * @return Nette\Database\Table\Selection seznam kontaktů (spojený s tabulkou uživatelů)
	 */
	public function getUsersContactList($idUser) {
		$sel = $this->getTable();
		$sel->select(self::TABLE_NAME . ".*, " . self::COLUMN_ID_CONTACT . ".*"); //spojeni tabulek
		$sel->where(self::COLUMN_ID_USER, $idUser);
		return $sel;
	}

	/**
	 * Přidá do seznamu kontaktů uživatele nový kontakt
	 * @param int $idUser uživatel, kterému vytváříme kontakt
	 * @param int $idContactUser kontakt uživatele (je to id jiného uživatele)
	 * @return Nette\Database\Table\Selection vytvořený kontakt
	 */
	public function addToContacts($idUser, $idContactUser) {
		$sel = $this->getTable();
		$sel->insert(array(
			self::COLUMN_ID_USER => $idUser,
			self::COLUMN_ID_CONTACT => $idContactUser
		));
		return $sel;
	}

	/**
	 * Odstraní kontakt podle primárního klíče kontaktu
	 * @param int $id id klíče kontaktu
	 */
	public function removeContact($id) {
		$sel = $this->getTable();
		$sel->wherePrimary($id);
		$sel->delete();
	}

	/**
	 * Odstraní daný kontakt daného uživatele
	 * @param int $idUser komu kontakt odstranit (id uživatele)
	 * @param int $idContactUser který kontakt odstranit (id uživatele)
	 */
	public function removeFromContacts($idUser, $idContactUser) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_ID_USER, $idUser);
		$sel->where(self::COLUMN_ID_CONTACT, $idContactUser);
		$sel->delete();
	}

}
