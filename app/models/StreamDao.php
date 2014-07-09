<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

use Nette\DateTime;

/**
 * ActivityStreamDao
 * pracuje s prvkami ve streamu
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class StreamDao extends AbstractDao {

	const TABLE_NAME = "stream_items";

	/* sloupečky */
	const COLUMN_USER_GALLERY_ID = "userGalleryID";
	const COLUMN_USER_ID = "userID";

	/**
	 * Vrací tuto tabulku
	 * @return Nette\Database\Table\Selection
	 */
	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	/**
	 * Vrátí všechny řádky z tabulky
	 * @return Nette\Database\Table\Selection
	 */
	public function getAllRows() {
		return $this->getTable();
	}

	public function getUserStreamPosts($userId) {
		$sel = $this->getTable();
		$userPosts = $sel->where(self::COLUMN_USER_ID, $userId);
		return $userPosts->order("id DESC");
	}

	/**
	 * Přidá odkaz na přiznání do streamu
	 * @param int $confessionID ID přiznání
	 */
	public function addNewConfession($confessionID, $create = NULL, $userID = NULL) {
		if (empty($create)) {
			$create = new DateTime();
		}

		$sel = $this->getTable();
		$sel->insert(array(
			"confessionID" => $confessionID,
			"userID" => $userID,
			"type" => 1,
			"create" => $create,
		));
	}

	/**
	 * Přidá odkaz na otázku do streamu
	 * @param type $adviceID ID otázky
	 * @param type $userID ID uživatele
	 */
	public function addNewAdvice($adviceID, $create = NULL, $userID = NULL) {
		if (empty($create)) {
			$create = new DateTime();
		}

		$sel = $this->getTable();
		$sel->insert(array(
			"adviceID" => $adviceID,
			"userID" => $userID,
			"type" => 1,
			"create" => $create,
		));
	}

	/**
	 * Přidá odkaz na gallerii do streamu
	 * @param int $userGalleryID ID galerie
	 * @param int $userID ID uživatele
	 */
	public function addNewGallery($userGalleryID, $userID) {
		$sel = $this->getTable();
		$sel->insert(array(
			"userGalleryID" => $userGalleryID,
			"userID" => $userID,
			"type" => 1,
			"create" => new DateTime(),
		));
	}

	/**
	 * Snaže starý záznam z galerie a vloží ho znovu. Tím se příspěvek
	 * dostane opět nahoru ve streamu.
	 * @param int $galleryID ID galerie
	 */
	public function aliveCompGallery($galleryID) {
		// smazání starého řádku
		$sel = $this->getTable();
		$sel->where("galleryID", $galleryID);
		$sel->delete();

		$this->addNewComGallery($galleryID);
	}

	/**
	 * Přidá nový odkaz na galerii do streamu
	 * @param int $galleryID ID galerie
	 */
	public function addNewComGallery($galleryID) {
		$sel = $this->getTable();
		$sel->insert(array(
			"galleryID" => $galleryID,
//			"userID" => $userID,
			"type" => 1,
			"create" => new DateTime(),
		));
	}

	/**
	 * Snaže starý záznam z galerie a vloží ho znovu. Tím se příspěvek
	 * dostane opět nahoru ve streamu.
	 * @param int $userGalleryID ID galerie
	 * @param int $userID ID uživatele
	 */
	public function aliveGallery($userGalleryID, $userID) {
		//smazání starého řádku
		$sel = $this->getTable();
		$sel->where("userGalleryID", $userGalleryID);
		$sel->delete();

		$this->addNewGallery($userGalleryID, $userID);
	}

	/**
	 * Odstraní záznam o uživatelské galerii ze streamu
	 * @param int $userGalleryID ID uživatelské galerie.
	 */
	public function deleteUserGallery($userGalleryID) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_USER_GALLERY_ID, $userGalleryID);
		$sel->delete();
	}

}
