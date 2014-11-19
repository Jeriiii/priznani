<?php

/*
 * @copyright Copyright (c) 2013 - 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * Dao pro práci s tabulkou udržující povolené lidi do galerií
 *
 * @author Daniel Holubář
 */
class UserAllowedDao extends AbstractDao {

	const TABLE_NAME = "users_allowed_galleries";

	/* Column name */
	const COLUMN_ID = "id";
	const COLUMN_USER_ID = "userID";
	const COLUMN_GALLERY_ID = "galleryID";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	/**
	 * Vrátí uživatelé, kteří již jsou povoleni pro danou galerii
	 * @param int $galleryID ID galerie
	 * @return Nette\Database\Table\Selection
	 */
	public function getAllowedByGallery($galleryID) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_GALLERY_ID, $galleryID);
		return $sel;
	}

	/**
	 * Zjišťuje, zda uživatel má povoleno procházet galerii
	 * @param type $userID ID uživatele
	 * @param type $galleryID ID galerie
	 * @return Nette\Database\Table\ActiveRow
	 */
	public function getByUserID($userID, $galleryID) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_USER_ID, $userID);
		$sel->where(self::COLUMN_GALLERY_ID, $galleryID);
		return $sel->fetch();
	}

	/**
	 * vloží povoleného uživatele
	 * @param type $userID id uživatele
	 * @param type $galleryID id galerie
	 */
	public function insertData($userID, $galleryID) {
		$sel = $this->getTable();
		$sel->insert(array(
			self::COLUMN_USER_ID => $userID,
			self::COLUMN_GALLERY_ID => $galleryID
		));
	}

}
