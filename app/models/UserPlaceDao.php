<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * UserPlace DAO
 * vkládání oblíbených míst k milování
 *
 * @author Christine Baierová
 */
class UserPlaceDao extends AbstractDao {

	/** @var Nette\Database */
	protected $database;

	const TABLE_NAME = "user_place";

	/* Column name */
	const COLUMN_ID = "id";
	const COLUMN_USER_PROPERTIES_ID = "user_propertiesID";
	const COLUMN_ENUM_PLACE_ID = "enum_placeID";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	/**
	 * Vloží do DB oblíbené místo k milování
	 * @param string $userPropertyID id user_properties
	 * @param int $placeID id oblíbeného místa
	 */
	public function insertNewPlace($userPropertyID, $placeID) {
		$rowExist = $this->findPlaceItem($userPropertyID, $placeID);
		if (!empty($rowExist)) {
			return;
		} else {
			$sel = $this->getTable();
			$sel->insert(array(
				self::COLUMN_USER_PROPERTIES_ID => $userPropertyID,
				self::COLUMN_ENUM_PLACE_ID => $placeID,
			));
		}
	}

	/**
	 * Najde záznam dle usera a id místa
	 * @param string $userPropertyID id user_properties
	 * @param int $placeID id oblíbené polohy
	 */
	public function findPlaceItem($userPropertyID, $placeID) {
		$sel = $this->getTable();
		$sel->where(array(
			self::COLUMN_USER_PROPERTIES_ID => $userPropertyID,
			self::COLUMN_ENUM_PLACE_ID => $placeID
		));
		return $sel->fetch();
	}

	/**
	 * Vrací hodnotu, zda uživatel již vyplnil své oblíbené místo k milování
	 * @param string $userPropertyID id user_properties
	 * @return 0 pokud uživatel nemá vyplněné místo k milování
	 * @return 1 pokud uživatel má vyplněné místo k milování
	 */
	public function isFilled($userPropertyID) {
		$sel = $this->getTable();

		$temp = $sel->select('*')->where(self::COLUMN_USER_PROPERTIES_ID, $userPropertyID)->fetch();

		if ($temp == FALSE) {
			return 0;
		} else {
			return 1;
		}
	}

	/**
	 * Vytahne vyplněné info dle uživatele
	 * @param string $userPropertyID id user_properties
	 * @return Nette\Database\Table\ActiveRow
	 */
	public function getFilled($userPropertyID) {
		$sel = $this->getTable();
		return $sel->select('*')->where(self::COLUMN_USER_PROPERTIES_ID, $userPropertyID)->fetchAll();
	}

	/**
	 * Smaže záznamy od daného uživatele.
	 * @param string $userPropertyID id user_properties
	 */
	public function deleteByProperty($userPropertyID) {
		$sel = $this->getTable();
		$sel->where(array(
			self::COLUMN_USER_PROPERTIES_ID => $userPropertyID
		));
		$sel->delete();
	}

}
