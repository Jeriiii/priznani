<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * EnumPlace DAO
 * tabulka s místy k milování
 *
 * @author Christine Baierová
 */
class EnumPlaceDao extends AbstractDao {

	/** @var Nette\Database */
	protected $database;

	const TABLE_NAME = "enum_place";

	/* Column name */
	const COLUMN_ID = "id";
	const COLUMN_PLACE = "place";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	/**
	 * Vybere id oblíbeného místa k milování z tabulky
	 * @param string $place zvolené místo
	 * @return Nette\Database\Table\ActiveRow
	 */
	public function selPlace($place) {
		$sel = $this->getTable();
		$sel->select('*')->where(array(
			self::COLUMN_PLACE => $place
		));
		return $sel->fetch();
	}

	/**
	 * Vybere vyplněná místa
	 * @param $placeId id zvoleného místo
	 * @return Nette\Database\Table\ActiveRow
	 */
	public function getFilledPlaces($placeId) {
		$sel = $this->getTable();
		$sel->select('*')->where(array(
			self::COLUMN_ID => $placeId
		));
		return $sel->fetch();
	}

}
