<?php

/*
 * @copyright Copyright (c) 2013 - 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

use POS\Exception\DuplicateRowException;

/**
 * Region DAO
 * Administrace krajů
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class RegionDao extends AbstractDao {

	/** @var Nette\Database */
	protected $database;

	const TABLE_NAME = "region";

	/* Column name */
	const COLUMN_ID = "id";
	const COLUMN_NAME = "name";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	/**
	 * Vloží do DB nový kraj, kontroluje duplikáty
	 * @param string $name Jméno kraje, které chceme vložit
	 * @return Nette\Database\Table\ActiveRow
	 * @throws DuplicateRowException
	 */
	public function addRegion($name) {
		$rowExist = $this->findByName($name);
		if (!empty($rowExist)) {
			throw new DuplicateRowException;
		}

		$sel = $this->getTable();
		$region = $sel->insert(array(
			self::COLUMN_NAME => $name
		));
		return $region;
	}

	/**
	 * Aktualizuje Kraj
	 * @param int $id ID kraje
	 * @param array $data pole obsahující jméno
	 * @return Nette\Database\Table\ActiveRow
	 */
	public function updateRegion($id, $data) {

		$sel = $this->getTable();
		$sel->wherePrimary($id);
		$region = $sel->update($data);
		return $region;
	}

	/**
	 * Vyhledá kraj podle jména
	 * @param string $name Jméno kraje, který hledáme
	 * @return Nette\Database\Table\ActiveRow
	 */
	public function findByName($name) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_NAME, $name);
		return $sel->fetch();
	}

	/**
	 * Uloží záznamy z tabulky do pole s primárním klíčem jako klíčem
	 * a jménem jako hodnotou
	 * @return array
	 */
	public function getRegionsInArray() {
		$sel = $this->getTable();
		$regionsRaw = $sel->select('region.id, region.name')->order('region.name ASC');
		$regions = array();

		foreach ($regionsRaw as $region) {
			$regions[$region->id] = $region->name;
		}
		return $regions;
	}

}
