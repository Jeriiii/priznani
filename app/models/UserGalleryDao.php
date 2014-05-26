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
	const COLUMN_MAN = "man";
	const COLUMN_WOMEN = "woman";
	const COLUMN_COUPLE = "couple";
	const COLUMN_MORE = "more";
	const COLUMN_DEFAULT = "default";
	const COLUMN_NAME = "name";
	const COLUMN_DESCRIPTION = "description";

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

	/**
	 * Vrátí defaultní galerii uživatel, když existuje
	 * @param int $userID ID uživatele.
	 * @return bool|Database\Table\IRow
	 */
	public function findDefaultGallery($userID) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_USER_ID, $userID);
		$sel->where(self::COLUMN_DEFAULT, 1);
		return $sel->fetch();
	}

	/**
	 * Vytvoří defaultní galerii uživateli
	 * @param int $userID ID uživatele.
	 * @return Database\Table\IRow
	 */
	public function insertDefaultGallery($userID) {
		$sel = $this->getTable();
		$defaultGallery = $sel->insert(array(
			self::COLUMN_NAME => "Moje fotky",
			self::COLUMN_USER_ID => $userID,
			self::COLUMN_DEFAULT => 1,
		));
		return $defaultGallery;
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

	/**
	 * Změní hodnoty muž|žena|pár|3 a více u galerie.
	 * @param int $galleryID ID galerie.
	 * @param int $man Muž.
	 * @param int $women Žena.
	 * @param int $couple Par.
	 * @param int $more 3 a více.
	 */
	public function updateGender($galleryID, $man, $women, $couple, $more) {
		$sel = $this->getTable();
		$sel->wherePrimary($galleryID);
		$sel->update(array(
			self::COLUMN_MAN => $man,
			self::COLUMN_WOMEN => $women,
			self::COLUMN_COUPLE => $couple,
			self::COLUMN_MORE => $more
		));
	}

	/**
	 * Změní název, popisek a další hodnoty galerie.
	 * @param int $galleryID ID galerie.
	 * @param string $name Jméno galerie.
	 * @param string $description Popisek galerie.
	 * @param int $man Muž.
	 * @param int $women Žena.
	 * @param int $couple Par.
	 * @param int $more 3 a více.
	 */
	public function updateNameDescGender($galleryID, $name, $description, $man, $women, $couple, $more) {
		$sel = $this->getTable();
		$sel->wherePrimary($galleryID);
		$sel->update(array(
			self::COLUMN_NAME => $name,
			self::COLUMN_DESCRIPTION => $description,
			self::COLUMN_MAN => $man,
			self::COLUMN_WOMEN => $women,
			self::COLUMN_COUPLE => $couple,
			self::COLUMN_MORE => $more
		));
	}

}
