<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

/**
 * DataGrid pro procházení měst, okresů a krajů. Vše najednou.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */

namespace POS\Grids;

use Grido\Grid;
use POS\Model\CityDao;
use POS\Model\DistrictDao;
use POS\Model\RegionDao;

class CityGrid extends Grid {

	/** @var \POS\Model\DistrictDao */
	public $districtDao;

	/** @var \POS\Model\RegionDao */
	public $regionDao;

	public function __construct(CityDao $cityDao, DistrictDao $districtDao, RegionDao $regionDao, $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->districtDao = $districtDao;
		$this->regionDao = $regionDao;

		$this->setModel($cityDao->getAll());

		$this->addColumns();
		$this->addActionHrefs();
	}

	/**
	 * Přidá sloupce do grida.
	 */
	private function addColumns() {
		$districts = $this->districtDao->getDistrictsInArray();
		$regions = $this->regionDao->getRegionsInArray();

		$this->addColumnText("name", "Město")
			->setFilterText()
			->setSuggestion();
		$this->addColumnText("district", "Okres")
			->setColumn('districtID.name')
			->setCustomRender(function($item) {
				return $item->district->name;
			})
			->setFilterText()
			->setColumn('districtID.name')
			->setSuggestion(function($item) {
				return $item->district->name;
			});
		$this->addColumnText("region", "Kraj")
			->setColumn('districtID.regionID.name')
			->setCustomRender(function($item) {
				return $item->district->region->name;
			})
			->setFilterText()
			->setColumn('districtID.regionID.name')
			->setSuggestion(function($item) {
				return $item->district->region->name;
			});
	}

	/**
	 * Přidá tlačítka s akcemi do grida.
	 */
	private function addActionHrefs() {
		$this->addActionHref('edit_city', 'Upravit město', 'Cities:editCity')
			->setCustomHref(function($item) {
				return '..\admin.cities\edit-city?city=' . $item->name . '&districtID=' . $item->district->id;
			});
		$this->addActionHref('edit_district', 'Upravit okres', 'Cities:editDistrict')
			->setCustomHref(function($item) {
				return '..\admin.cities\edit-district?district=' . $item->district->name . '&regionID=' . $item->district->region->id;
			});
		$this->addActionHref('edit_region', 'Upravit kraj', 'Cities:editRegion')
			->setCustomHref(function($item) {
				return '..\admin.cities\edit-region?region=' . $item->district->region->name;
			});
	}

}
