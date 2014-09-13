<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

use POS\Exception\DuplicateRowException;

/**
 * City DAO
 * Administrace měst
 *
 * @author Daniel Holubář
 */
class CityDao extends AbstractDao {

	/** @var Nette\Database */
	protected $database;

	const TABLE_NAME = "city";

	/* Column name */
	const COLUMN_ID = "id";
	const COLUMN_NAME = "name";
	const COLUMN_DISTRICT_ID = "districtID";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	/**
	 * Vloží do DB nové město, kontroluje duplikáty
	 * @param string $name Jméno města, které chceme vložit
	 * @param int $districtID ID okresu, vněmž se město nachází
	 * @return Nette\Database\Table\ActiveRow
	 * @throws DuplicateRowException
	 */
	public function addCity($name, $districtID) {
		$rowExist = $this->findByNameAndDistrictID($name, $districtID);
		if (!empty($rowExist)) {
			throw new DuplicateRowException;
		}

		$sel = $this->getTable();
		$city = $sel->insert(array(
			self::COLUMN_NAME => $name,
			self::COLUMN_DISTRICT_ID => $districtID
		));
		return $city;
	}

	/**
	 * Aktualizuje město
	 * @param int $id ID města, které bude aktualizováno
	 * @param array $data Pole se jménem a okresem, do kterého město patří
	 * @return Nette\Database\Table\ActiveRow
	 */
	public function updateCity($id, $data) {
		$sel = $this->getTable();
		$sel->wherePrimary($id);
		$district = $sel->update($data);
		return $district;
	}

	/**
	 * Vyhledá město podle jména a ID okresu
	 * @param string $name Jméno města, které hledáme
	 * @param int $districtID ID okresu
	 * @return Nette\Database\Table\ActiveRow
	 */
	public function findByNameAndDistrictID($name, $districtID) {
		$sel = $this->getTable();
		$sel->where(array(
			self::COLUMN_NAME => $name,
			self::COLUMN_DISTRICT_ID => $districtID
		));
		return $sel->fetch();
	}

	/**
	 * Získá potřebná data pro Grido z db.
	 * @return Nette\Database\Table\Selection
	 */
	public function getCitiesData() {
		$sel = $this->getTable();
		$data = $sel->select('city.id, city.name AS city, districtID.name AS district, districtID.id AS districtID, districtID.regionID.name AS region, districtID.regionID.id AS regionID');
		return $data;
	}

	/**
	 * Získá potřebná data pro našeptávač v registraci z db.
	 * @return Nette\Database\Table\ActiveRow
	 */
	public function getNamesOfProperties() {
		$sel = $this->getTable();
		$data = $sel->select('city.name AS city, districtID.name AS district, districtID.regionID.name AS region');
		return $data;
	}

}
