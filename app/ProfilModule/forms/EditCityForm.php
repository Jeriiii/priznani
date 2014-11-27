<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace Nette\Application\UI\Form;

use Nette\Forms\Form;
use Nette\Database\Table\ActiveRow;
use POS\Model\UserPropertyDao;
use POS\Model\DistrictDao;
use POS\Model\RegionDao;
use POS\Model\CityDao;

/**
 * Změna/okresu/kraje města uživatele
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class EditCityForm extends BaseForm {

	/**
	 * @var \POS\Model\CityDao
	 */
	public $cityDao;

	/**
	 * @var \POS\Model\DistrictDao
	 */
	public $districtDao;

	/**
	 * @var \POS\Model\RegionDao
	 */
	public $regionDao;

	/**
	 * @var \POS\Model\UserPropertyDao
	 */
	public $userPropertyDao;

	/**
	 * @var ActiveRow
	 */
	private $property;

	public function __construct(RegionDao $regionDao, DistrictDao $districtDao, CityDao $cityDao, UserPropertyDao $userPropertyDao, ActiveRow $property, $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->cityDao = $cityDao;
		$this->districtDao = $districtDao;
		$this->regionDao = $regionDao;
		$this->userPropertyDao = $userPropertyDao;
		$this->property = $property;

		$this->addGroup("Vyskytuji se");

		$this->addText('city', 'Město')
			->addRule(Form::FILLED, 'Město není vyplněno.');

		if (isset($property->city->name)) {
			$this->setDefaults(array(
				'city' => $property->city->name . ", " . $property->district->name . ", " . $property->region->name,
			));
		}

		$this->setBootstrapRender();
		$this->addSubmit('send', 'Uložit')
			->setAttribute("class", "btn-main medium button");
		$this->onValidate[] = callback($this, "existingCity");
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}

	public function submitted(EditCityForm $form) {
		$values = $form->getValues();
		$presenter = $this->getPresenter();

		$dataAboutCities = $this->getDataAboutCity($values);
		$cityName = $dataAboutCities[0];
		$districtName = $dataAboutCities[1];
		$regionName = $dataAboutCities[2];

		$city = $this->cityDao->findByName($cityName);
		$district = $this->districtDao->findByName($districtName);
		$region = $this->regionDao->findByName($regionName);

		$this->userPropertyDao->update($this->property->id, array(
			UserPropertyDao::COLUMN_CITY_ID => $city->id,
			UserPropertyDao::COLUMN_DISTRICT_ID => $district->id,
			UserPropertyDao::COLUMN_REGION_ID => $region->id
		));

		$presenter->flashMessage('Město bylo uloženo.');
		$presenter->redirect('this');
	}

	/**
	 * Zkontorluje, zda dané město je v databázi
	 * @param Nette\Application\UI\Form $form
	 * @return
	 */
	public function existingCity($form) {
		$values = $form->getValues();

		$data = $this->getDataAboutCity($values);


		if (sizeof($data) < 3) {
			$form->addError('Neúplná data o městu(město, okres, kraj)');
			return;
		}

		$region = $this->regionDao->findByName($data[2]);
		if (!$region) {
			$form->addError('Omlouváme se, toto město není v naší databázi. Prosím, vyberte ze seznamu větší město ve vašem okolí.');
			return;
		}

		$district = $this->districtDao->findByNameAndRegionID($data[1], $region->id);
		if (!$district) {
			$form->addError('Omlouváme se, toto město není v naší databázi. Prosím, vyberte ze seznamu větší město ve vašem okolí.');
			return;
		}

		$city = $this->cityDao->findByNameAndDistrictID($data[0], $district->id);
		if (!$city) {
			$form->addError('Omlouváme se, toto město není v naší databázi. Prosím, vyberte ze seznamu větší město ve vašem okolí.');
			return;
		}
	}

	/**
	 * Uloží data z řetězce do pole
	 * @param array $values
	 * @return array Pole s daty o městu: 0 => ID města, 1 => ID okresu, 2 => ID kraje
	 */
	private function getDataAboutCity($values) {
		return explode(", ", $values->city);
	}

}
