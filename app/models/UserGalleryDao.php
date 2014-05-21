<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * NAME DAO NAMEDao
 * slouží k
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class UserGalleryDao extends AbstractDao {

	const TABLE_NAME = "user_galleries";

	/* Column name */
	const COLUMN_ID = "id";
	const COLUMN_USER_ID = "userID";
	const COLUMN_BEST_IMAGE_ID = "bestImageID";
	const COLUMN_LAST_IMAGE_ID = "lastImageID";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	/**
	 * Vrátí galerie určitého uživatele.
	 * @param int $userID ID uživatele.
	 * @return Nette\Database\Table\Selection
	 */
	public function getInUser($userID) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_USER_ID, $userID);
		$sel->order(self::COLUMN_ID . " DESC");
		return $sel;
	}

	/**
	 * Vrátí galerii která má nejlepší NEBO poslední obrázek tento
	 * @param int $bestimageID ID nejlepšího obrázku.
	 * @param int $lastImageID ID posledního obrázku.
	 * @return bool|Database\Table\IRow
	 */
	public function findByBestOrLastImage($bestimageID, $lastImageID) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_BEST_IMAGE_ID . " = ? OR " . self::COLUMN_LAST_IMAGE_ID . " = ?", $bestimageID, $lastImageID);
		return $sel->fetch();
	}

	/*	 * ****************************** UPDATE **************************** */

	/**
	 * Změní nejlepší a poslední vložený obrázek.
	 * @param type $bestImageID ID nejlepšího obrázku.
	 * @param type $lastImageID ID posledního vloženého obrázku.
	 */
	public function updateBestAndLastImage($bestImageID, $lastImageID) {
		$sel = $this->getTable();
		$sel->update(array(
			UserGalleryDao::COLUMN_BEST_IMAGE_ID => $bestImageID,
			UserGalleryDao::COLUMN_LAST_IMAGE_ID => $lastImageID
		));
	}

}
