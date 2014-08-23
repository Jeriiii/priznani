<?php

/*
 * @copyright Copyright (c) 2013 - 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

use POS\Exception\DuplicateRowException;

/**
 * District DAO
 * Administrace okresů
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class DistrictDao extends AbstractDao {

	/** @var Nette\Database */
	protected $database;

	const TABLE_NAME = "district";

	/* Column name */
	const COLUMN_ID = "id";
	const COLUMN_NAME = "name";
	const COLUMN_REGION_ID = "regionID";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	/**
	 * Vloží do DB nový okres, kontroluje duplikáty
	 * @param string $name Jméno okresu, které chceme vložit
	 * @param int $regionID ID kraje, vněmž se okres nachází
	 * @return Nette\Database\Table\ActiveRow
	 * @throws DuplicateRowException
	 */
	public function addDistrict($name, $regionID) {
		$rowExist = $this->findByNameAndRegionID($name, $regionID);
		if (!empty($rowExist)) {
			throw new DuplicateRowException;
		}

		$sel = $this->getTable();
		$district = $sel->insert(array(
			self::COLUMN_NAME => $name,
			self::COLUMN_REGION_ID => $regionID
		));
		return $district;
	}

	/**
	 * Aktualizuje okres
	 * @param int $id ID okresu
	 * @param array $data Pole se jménem okresu a krajem, do kterého patří
	 * @return Nette\Database\Table\ActiveRow
	 * @throws DuplicateRowException
	 */
	public function updateDistrict($id, $data) {
		$rowExist = $this->findByNameAndRegionID($data["name"], $data["regionID"]);
		if (!empty($rowExist)) {
			throw new DuplicateRowException;
		}

		$sel = $this->getTable();
		$sel->wherePrimary($id);
		$district = $sel->update($data);
		return $district;
	}

	/**
	 * Vyhledá okres podle jména
	 * @param string $name Jméno okresu, který hledáme
	 * @param ind $regionID ID kraje, do kterého okres patří
	 * @return Nette\Database\Table\ActiveRow
	 */
	public function findByNameAndRegionID($name, $regionID) {
		$sel = $this->getTable();
		$sel->where(array(
			self::COLUMN_NAME => $name,
			self::COLUMN_REGION_ID => $regionID
		));
		return $sel->fetch();
	}

	/**
	 * Uloží záznamy z tabulky do pole s primárním klíčem jako klíčem
	 * a jménem jako hodnotou
	 * @return array
	 */
	public function getDistrictsInArray() {
		$sel = $this->getTable();
		$districtsRaw = $sel->select('district.id, district.name')->order('district.name ASC');
		$districts = array();

		foreach ($districtsRaw as $district) {
			$districts[$district->id] = $district->name;
		}
		return $districts;
	}

}
