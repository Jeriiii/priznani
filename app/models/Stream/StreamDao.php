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
	const COLUMN_CONFESSION_ID = "confessionID";

	/**
	 * Vrací tuto tabulku
	 * @return Nette\Database\Table\Selection
	 */
	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	/**
	 * Vrátí všechny položky streamu, co se můžou zobrazit nepřihlášenému uživateli.
	 */
	public function getForUnloggedUser() {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_CONFESSION_ID . " IS NOT NULL");
		$sel->order("id DESC");
		return $sel;
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
	 * @param int $categoryID ID kategorie
	 */
	public function addNewGallery($userGalleryID, $userID, $categoryID) {
		$sel = $this->getTable();
		$sel->insert(array(
			"userGalleryID" => $userGalleryID,
			"userID" => $userID,
			"type" => 1,
			"create" => new DateTime(),
			self::COLUMN_CATEGORY_ID => $categoryID,
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
	 * @param int $categoryID ID kategorie
	 */
	public function aliveGallery($userGalleryID, $userID, $categoryID) {
//smazání starého řádku
		$sel = $this->getTable();
		$sel->where("userGalleryID", $userGalleryID);
		$sel->delete();

		$this->addNewGallery($userGalleryID, $userID, $categoryID);
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
	 * @param int $categoryID ID kategorie
	 */
	public function addNewStatus($stausID, $userID, $categoryID) {
		$sel = $this->getTable();
		$sel->insert(array(
			"statusID" => $stausID,
			"userID" => $userID,
			self::COLUMN_CATEGORY_ID => $categoryID,
			"create" => new DateTime(),
		));
	}

	/**
	 * Vrátí všechny položky streamu, které mají některé z daných id kategorií
	 * (tj. které splňují podmínky některé z kategorií)
	 * Položky jsou vraceny od konce.
	 * @param array $categoryIDs pole ID kategorií z tabulky stream_categories
	 * @param int $meUserID Moje ID uživatele.
	 * @param int $limit maximální počet vrácených položek (vrací všechny když je limit 0)
	 * @param int $offset offset limitu položek
	 * @return \Nette\Database\Table\Selection všechny vyhovující položky
	 */
	public function getAllItemsWhatFits(array $categoryIDs, $meUserID, $limit = 0, $offset = 0) {
		$sel = $this->getTable();
		$this->sortOutItems($categoryIDs, $meUserID, $sel);

		$sel->order('id DESC');
		if ($limit != 0) {
			$sel->limit($limit, $offset);
		}
		return $sel;
	}

	/**
	 * Vrátí všechny položky streamu, které mají některé z daných id kategorií
	 * (tj. které splňují podmínky některé z kategorií) a jsou novější než zadané ID
	 * (jejich ID je vyšší). Jsou seřazené sestupně.
	 * @param array $categoryIDs pole ID kategorií z tabulky stream_categories
	 * @param int $meUserID Moje ID uživatele.
	 * @param int $lastId id poslední předchozí položky
	 * @return \Nette\Database\Table\Selection všechny vyhovující položky
	 */
	public function getAllItemsWhatFitsSince(array $categoryIDs, $meUserID, $lastId) {
		$sel = $this->getTable();
		$sel->where('id > ?', $lastId);
		$this->sortOutItems($categoryIDs, $meUserID, $sel);
		$sel->order('id DESC');

		return $sel;
	}

	/**
	 * Vytřídí/přidá příspěvky
	 * @param array $categoryIDs pole ID kategorií z tabulky stream_categories
	 * @param int $meUserID Moje ID uživatele.
	 * @param \Nette\Database\Table\Selection $sel Nevytříděné příspěvky.
	 * @return \Nette\Database\Table\Selection Vytříděné příspěvky.
	 */
	private function sortOutItems(array $categoryIDs, $meUserID, $sel) {
		/* musí to jít v tomto pořadí */
		$sel = $this->sortOut($sel, $categoryIDs, $meUserID);
		$sel = $this->sortOutUsers($sel, $meUserID);

		return $sel;
	}

	/**
	 * Vytřídění příspvěků podle preferencí
	 * @param \Nette\Database\Table\Selection $sel Nevytříděné příspěvky.
	 * @param array $categoryIDs pole ID kategorií z tabulky stream_categories
	 * @param int $meUserID Moje ID uživatele.
	 * @return \Nette\Database\Table\Selection Vytříděné příspěvky.
	 */
	private function sortOut($sel, array $categoryIDs, $meUserID) {
		$where = $params = array();

		/* přidání přátel */
		$where[] = self::COLUMN_USER_ID . " IN (?)";
		$params[] = $this->getFriendIDs($meUserID);
		/* přidání lidí co jsem označil jako sexy */
		$where[] = self::COLUMN_USER_ID . " IN (?)";
		$params[] = $this->getUsersIMarkSexy($meUserID);
		/* přidání příspěvků v kategoriích, které hledám */
		$where[] = self::COLUMN_CATEGORY_ID . " IN (?)";
		$params[] = $categoryIDs;
		/* přidání přiznání */
		$where[] = self::COLUMN_CONFESSION_ID . " IS NOT NULL";

		/* vytřídění příspěvků */
		$sel->where(implode(" OR ", $where), $params);

		return $sel;
	}

	/**
	 * Vytřídí sebe a blokované uživatele. Vždy v tomto pořadí!
	 * @param \Nette\Database\Table\Selection $sel Nevytříděné příspěvky.
	 * @param int $meUserID Moje ID uživatele.
	 * @return \Nette\Database\Table\Selection Vytříděné příspěvky.
	 */
	private function sortOutUsers($sel, $meUserID) {
		/* vytřídění uživatelů */
		$sel = $this->sortOutMe($sel, $meUserID);
		$sel = $this->sortOutBlokedUsers($sel, $meUserID);

		return $sel;
	}

	/**
	 * Vrátí id přátel
	 * @param int $meUserID Moje ID uživatele.
	 * @return array ID přátel.
	 */
	private function getFriendIDs($meUserID) {
		$friends = $this->createSelection(FriendDao::TABLE_NAME);
		$friends->where(FriendDao::COLUMN_USER_ID_1, $meUserID);
		$friendIDs = array();
		foreach ($friends as $friend) {
			$friendIDs[] = $friend->offsetGet(FriendDao::COLUMN_USER_ID_2);
		}

		return $friendIDs;
	}

	/**
	 * Vrátí id uživatelů, co mě označili jako sexy
	 * @param int $meUserID Moje ID uživatele.
	 * @return array ID uživatelů, co mě označili jako sexy.
	 */
	private function getUsersIMarkSexy($meUserID) {
		$markMeSexy = $this->createSelection(YouAreSexyDao::TABLE_NAME);
		$markMeSexy->where(YouAreSexyDao::COLUMN_USER_FROM_ID, $meUserID);
		$markMeSexyIDs = array();
		foreach ($markMeSexy as $m) {
			$markMeSexyIDs[] = $m->offsetGet(YouAreSexyDao::COLUMN_USER_TO_ID);
		}

		return $markMeSexyIDs;
	}

	/**
	 * Vytřídění sebe.
	 * @param \Nette\Database\Table\Selection $sel Nevytříděné příspěvky.
	 * @param int $meUserID Moje ID uživatele.
	 * @return \Nette\Database\Table\Selection Vytříděné příspěvky.
	 */
	private function sortOutMe($sel, $meUserID) {
		/* nevezme sam sebe */
		$sel->where(self::COLUMN_USER_ID . " != ? OR " . self::COLUMN_USER_ID . " IS NULL", $meUserID);
		return $sel;
	}

	/**
	 * Vytřídění blokovaných uživatelů.
	 * @param \Nette\Database\Table\Selection $sel Nevytříděné příspěvky.
	 * @param int $meUserID Moje ID uživatele.
	 * @return \Nette\Database\Table\Selection Vytříděné příspěvky.
	 */
	private function sortOutBlokedUsers($sel, $meUserID) {
		/* blokovaní uživatelé tohoto uživatele */
		$blokedUsers = $this->createSelection(UserBlokedDao::TABLE_NAME);
		$blokedUsers->where(UserBlokedDao::COLUMN_OWNER_ID, $meUserID);
		if ($blokedUsers->count(UserBlokedDao::COLUMN_ID)) {
			$sel->where(self::COLUMN_USER_ID . " NOT IN ? OR " . self::COLUMN_USER_ID . " IS NULL", $blokedUsers);
		}
		return $sel;
	}

	/**
	 * ZASTARALÉ - SMAZAT
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
