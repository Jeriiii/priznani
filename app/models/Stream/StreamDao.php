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
	const COLUMN_CATEGORY_ID = "categoryID";
	const COLUMN_AGE = "age";
	const COLUMN_TALLNESS = "tallness";

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

	/**
	 * Přidá nový status do streamu
	 * @param int $stausID ID statusu
	 * @param int $userID ID uživatele
	 */
	public function addNewStatus($stausID, $userID) {
		$sel = $this->getTable();
		$sel->insert(array(
			"statusID" => $stausID,
			"userID" => $userID,
			"create" => new DateTime(),
		));
	}

	/**
	 * Vrátí všechny položky streamu, které mají některé z daných id kategorií
	 * (tj. které splňují podmínky některé z kategorií)
	 * Položky jsou vraceny od konce.
	 * @param array $categoryIDs pole ID kategorií z tabulky stream_categories
	 * @param int $limit maximální počet vrácených položek (vrací všechny když je limit 0)
	 * @param int $offset offset limitu položek
	 * @return \Nette\Database\Table\Selection všechny vyhovující položky
	 */
	public function getAllItemsWhatFits(array $categoryIDs, $limit = 0, $offset = 0) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_CATEGORY_ID, $categoryIDs);
		if ($limit != 0) {
			$sel->order('id DESC');
			$sel->limit($limit, $offset);
		}
		return $sel;
	}

	/**
	 * Vrátí všechny položky streamu, které mají některé z daných id kategorií
	 * (tj. které splňují podmínky některé z kategorií) a jsou novější než zadané ID
	 * (jejich ID je vyšší). Jsou seřazené sestupně.
	 * @param array $categoryIDs pole ID kategorií z tabulky stream_categories
	 * @param int $lastId id poslední předchozí položky
	 * @return \Nette\Database\Table\Selection všechny vyhovující položky
	 */
	public function getAllItemsWhatFitsSince(array $categoryIDs, $lastId) {
		$sel = $this->getTable();
		$sel->where('id > ?', $lastId);
		$sel->where(self::COLUMN_CATEGORY_ID, $categoryIDs);
		$sel->order('id DESC');
		return $sel;
	}

	/**
	 * Vrátí všechny položky streamu, které spadají do některé z daných id kategorií
	 * (splňují podmínky některé z nich) a mají hodnoty daných sloupců v daném rozsahu. (např. sloupec 'tallness' mezi 180 a 200)
	 * Položky jsou vraceny od konce.
	 * @param array $categoryIDs pole ID kategorií z tabulky stream_categories
	 * @param array $rangedValues pole polí, kde je klíč prvního pole název sloupce a hodnota je druhé pole
	 * první hodnota druhého pole je pak dolní omezení, druhá hodnota horní omezení
	 *
	 * Tedy například: $this->streamDao->getAllItemsWhatFitsAndRange(array(1, 2, 3),
	 *  array(
	  'age' => array('2014-06-26 00:27:02', '2014-09-26 22:27:02'),
	  'tallness' => array(180, 200)
	  ));
	 *
	 * vybere jen položky, co mají kategorii 1, 2 nebo 3 a sloupec age mezi dvěma časy
	 * a sloupec tallness mezi 180 a 200 (obojí včetně)
	 * @param int $limit maximální počet vrácených položek (vrací všechny když je limit 0)
	 * @param int $offset offset limitu položek
	 * @return \Nette\Database\Table\Selection vyhovující položky
	 */
	public function getAllItemsWhatFitsAndRange(array $categoryIDs, array $rangedValues, $limit = 0, $offset = 0) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_CATEGORY_ID, $categoryIDs);
		foreach ($rangedValues as $column => $ranges) {
			$sel->where($column . ' BETWEEN ? AND ?', $ranges[0], $ranges[1]);
		}
		if ($limit != 0) {
			$sel->order('id DESC');
			$sel->limit($limit, $offset);
		}
		return $sel;
	}

}
