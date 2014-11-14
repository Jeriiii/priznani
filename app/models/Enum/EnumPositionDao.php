<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * EnumPosition DAO
 * tabulka s polohami
 *
 * @author Christine Baierová
 */
class EnumPositionDao extends AbstractDao {

	/** @var Nette\Database */
	protected $database;

	const TABLE_NAME = "enum_position";

	/* Column name */
	const COLUMN_ID = "id";
	const COLUMN_POSITION = "position";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	/**
	 * Vybere id oblíbené polohy z tabulky
	 * @param string $position zvolená poloha
	 * @return Nette\Database\Table\ActiveRow
	 */
	public function selPosition($position) {
		$sel = $this->getTable();
		$sel->select('*')->where(array(
			self::COLUMN_POSITION => $position
		));
		return $sel->fetch();
	}

	/**
	 * Vybere vyplněné pozice
	 * @param $positionId id polohy
	 * @return Nette\Database\Table\ActiveRow
	 */
	public function getFilledPositions($positionId) {
		$sel = $this->getTable();
		$sel->select('*')->where(array(
			self::COLUMN_ID => $positionId
		));
		return $sel->fetch();
	}

}
