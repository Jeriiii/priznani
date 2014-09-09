<?php

namespace AdminModule;

use Nette;
use Nette\Application\UI\Form as Frm;

/**
 * TempPresenter Description
 *
 * @author Daniel Holubář
 */
class CitiesPresenter extends AdminSpacePresenter {

	/**
	 * @var \POS\Model\CityDao
	 * @inject
	 */
	public $cityDao;

	/**
	 * @var \POS\Model\DistrictDao
	 * @inject
	 */
	public $districtDao;

	/**
	 * @var \POS\Model\RegionDao
	 * @inject
	 */
	public $regionDao;
	public $city;
	public $districtID;
	public $district;
	public $regionID;
	public $region;

	public function renderDefault() {

	}

	public function actionEditCity($city, $districtID) {
		$this->city = $city;
		$this->districtID = $districtID;
	}

	public function actionEditDistrict($district, $regionID) {
		$this->district = $district;
		$this->regionID = $regionID;
	}

	public function actionEditRegion($region) {
		$this->region = $region;
	}

	/**
	 * Komponenta grido vykresluje přehledně tabulky s daty o městech
	 * @param type $name
	 */
	protected function createComponentGrid($name) {
		$grid = new \Grido\Grid($this, $name);
		$grid->setModel($this->cityDao->getCitiesData());

		$districts = $this->districtDao->getDistrictsInArray();
		$regions = $this->regionDao->getRegionsInArray();

		/* sloupce komponenty */
		$grid->addColumnText("id", "ID")
			->setDefaultSort('ASC');
		$grid->addColumnText("city", "Město")
			->setFilterText()
			->setColumn('city.name');
		$grid->addColumnText("district", "Okres")
			->setFilterSelect($districts)
			->setColumn('districtID.id');
		$grid->addColumnText("region", "Kraj")
			->setFilterSelect($regions)
			->setColumn('districtID.regionID.id');
		$grid->addActionHref('edit_city', 'Upravit město', 'Cities:editCity')
			->setCustomHref(function($item) {
				return '..\admin.cities\edit-city?city=' . $item->city . '&districtID=' . $item->districtID;
			});
		$grid->addActionHref('edit_district', 'Upravit okres', 'Cities:editDistrict')
			->setCustomHref(function($item) {
				return '..\admin.cities\edit-district?district=' . $item->district . '&regionID=' . $item->regionID;
			});
		$grid->addActionHref('edit_region', 'Upravit kraj', 'Cities:editRegion')
			->setCustomHref(function($item) {
				return '..\admin.cities\edit-region?region=' . $item->region;
			});
		/* konec sloupců */
	}

	public function createComponentDatForm($name) {
		return new Frm\InsertManyCitiesForm($this->cityDao, $this->districtDao, $this->regionDao, $this, $name);
	}

	public function createComponentNewCityForm($name) {
		return new Frm\CityNewForm($this->cityDao, $this->districtDao, $this, $name);
	}

	public function createComponentNewDistrictForm($name) {
		return new Frm\DistrictNewForm($this->districtDao, $this->regionDao, $this, $name);
	}

	public function createComponentNewRegionForm($name) {
		return new Frm\RegionNewForm($this->regionDao, $this, $name);
	}

	public function createComponentEditCityForm($name) {
		return new Frm\CityEditForm($this->cityDao, $this->districtDao, $this->city, $this->districtID, $this, $name);
	}

	public function createComponentEditDistrictForm($name) {
		return new Frm\DistrictEditForm($this->districtDao, $this->regionDao, $this->district, $this->regionID, $this, $name);
	}

	public function createComponentEditRegionForm($name) {
		return new Frm\RegionEditForm($this->regionDao, $this->region, $this, $name);
	}

}
