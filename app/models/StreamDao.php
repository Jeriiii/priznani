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

	public function addNewConfession($confessionID, $userID) {
		$this->getTable()->insert(array(
			"confessionID" => $confessionID,
			"userID" => $userID,
			"type" => 1,
			"create" => new DateTime(),
		));
	}

	public function addNewAdvice($adviceID, $userID) {
		$this->getTable()->insert(array(
			"adviceID" => $adviceID,
			"userID" => $userID,
			"type" => 1,
			"create" => new DateTime(),
		));
	}

	public function addNewGallery($userGalleryID, $userID) {
		$this->getTable()->insert(array(
			"userGalleryID" => $userGalleryID,
			"userID" => $userID,
			"type" => 1,
			"create" => new DateTime(),
		));
	}

	public function aliveCompGallery($galleryID) {
		$this->getTable()->where("galleryID", $galleryID)->delete();
		$this->getTable()->insert(array(
			"galleryID" => $galleryID,
//			"userID" => $userID,
			"type" => 1,
			"create" => new DateTime(),
		));
	}

	public function aliveGallery($userGalleryID, $userID) {
		$this->getTable()->where("userGalleryID", $userGalleryID)->delete();
		$this->getTable()->insert(array(
			"userGalleryID" => $userGalleryID,
			"userID" => $userID,
			"type" => 1,
			"create" => new DateTime(),
		));
	}

}
