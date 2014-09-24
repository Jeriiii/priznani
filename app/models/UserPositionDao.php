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
	 * Vloží do DB oblíbenou polohu
	 * @param string $userPropertyID id user_properties
	 * @param int $positionID id oblíbené polohy
	 */
	public function insertNewPosition($userPropertyID, $positionID) {
		$sel = $this->getTable();
		$sel->insert(array(
			self::COLUMN_USER_PROPERTIES_ID => $userPropertyID,
			self::COLUMN_USER_ENUM_POSITION_ID => $positionID,
		));
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

}
