<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * UserPosition DAO
 * vkládání oblíbených poloh
 *
 * @author Christine Baierová
 */
class UserPositionDao extends AbstractDao {

	/** @var Nette\Database */
	protected $database;

	const TABLE_NAME = "user_position";

	/* Column name */
	const COLUMN_ID = "id";
	const COLUMN_USER_PROPERTIES_ID = "user_propertiesID";
	const COLUMN_USER_ENUM_POSITION_ID = "enum_positionID";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	/**
	 * Vloží do DB oblíbenou polohu, kontroluje duplicitu
	 * @param string $userPropertyID id user_properties
	 * @param int $positionID id oblíbené polohy
	 */
	public function insertNewPosition($userPropertyID, $positionID) {
		$rowExist = $this->findPosItem($userPropertyID, $positionID);
		if (!empty($rowExist)) {
			return;
		} else {
			$sel = $this->getTable();
			$sel->insert(array(
				self::COLUMN_USER_PROPERTIES_ID => $userPropertyID,
				self::COLUMN_USER_ENUM_POSITION_ID => $positionID,
			));
		}
	}

	/**
	 * Najde záznam dle usera a id pozice
	 * @param string $userPropertyID id user_properties
	 * @param int $positionID id oblíbené polohy
	 */
	public function findPosItem($userPropertyID, $positionID) {
		$sel = $this->getTable();
		$sel->where(array(
			self::COLUMN_USER_PROPERTIES_ID => $userPropertyID,
			self::COLUMN_USER_ENUM_POSITION_ID => $positionID
		));
		return $sel->fetch();
	}

	/**
	 * Vrací hodnotu, zda uživatel již vyplnil své oblíbené místo k milování
	 * @param string $userPropertyID id user_properties
	 * @return 0 pokud uživatel nemá vyplněnou oblíbenou polohu k milování
	 * @return 1 pokud uživatel má vyplněnou oblíbenou polohu k milování
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
		$sel->where(self::COLUMN_USER_PROPERTIES_ID, $userPropertyID);
		return $sel;
	}

	/**
	 * Smaže všechny záznamy podle property uživatele
	 * @param string $userPropertyID id user_properties
	 */
	public function deleteByProperty($userPropertyID) {
		$sel = $this->getTable();
		$sel->where(array(
			self::COLUMN_USER_PROPERTIES_ID => $userPropertyID,
		));
		$sel->delete();
	}

}
