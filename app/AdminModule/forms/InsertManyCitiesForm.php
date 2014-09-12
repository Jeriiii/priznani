<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\ComponentModel\IContainer;
use POS\Model\CityDao;
use POS\Model\DistrictDao;
use POS\Model\RegionDao;

/**
 * Vloží nová města
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class InsertManyCitiesForm extends BaseForm {

	/** Maximální délka názvu města */
	const MAX_CITY_LENGHT = 40;

	/**
	 * @var \POS\Model\DistrictDao
	 */
	private $districtDao;

	/**
	 * @var \POS\Model\CityDao
	 */
	private $cityDao;

	/**
	 * @var \POS\Model\RegionDao
	 */
	private $regionDao;

	public function __construct(CityDao $cityDao, DistrictDao $districtDao, RegionDao $regionDao, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->cityDao = $cityDao;
		$this->districtDao = $districtDao;
		$this->regionDao = $regionDao;

		$this->addTextArea("data")
			->addRule(Form::FILLED, "Musíte vložit data");


		$this->addSubmit("submit", "Vložit");
		$this->setBootstrapRender();
		$this->onSuccess[] = callback($this, 'submitted');
	}

	public function submitted($form) {
		$values = $form->values;
		$data = $this->getCitiesFromString($values->data);

		$this->cityDao->begginTransaction();

		foreach ($data as $item) {
			$region = $this->manageRegion($item[2]);

			$district = $this->manageDistrict($item[1], $region->id);

			$this->manageCity($item[0], $district->id);
		}

		$this->cityDao->endTransaction();

		$this->getPresenter()->flashMessage("Záznamy vloženy", "success");
		$this->getPresenter()->redirect('Cities:');
	}

	/**
	 * Převede celý vložený vstup na pole polý s městem, okresem a krajem.
	 * @param string $string Vložený vstup.
	 * @return array Pole polý s městem, okresem a krajem.
	 */
	private function getCitiesFromString($string) {
		$citiesLines = explode("\n", \Nette\Utils\Strings::trim($string));
		$cities = array();
		foreach ($citiesLines as $key => $city) {
			$cities[$key] = explode("\t", $city);
		}
		return $cities;
	}

	/**
	 * Prohledá db, jestli už region neexistuje,
	 * pokud ne, uloží ho a vypíše flashMessage,
	 * pokud ano vypíše flashMessage
	 * @param string $name jméno regionu
	 * @return Nette\Database\Table\ActiveRow
	 */
	public function manageRegion($name) {
		$region = $this->regionDao->findByName($name);

		if (!$region) {
			$region = $this->regionDao->addRegion($name);
			$this->getPresenter()->flashMessage($name . " byl vložen do databáze.", "success");
		} else {
			$this->getPresenter()->flashMessage($name . " již v databázi je.", "warning");
		}

		return $region;
	}

	/**
	 * Prohledá db, jestli už okres neexistuje,
	 * pokud ne, uloží ho a vypíše flashMessage,
	 * pokud ano vypíše flashMessage
	 * @param string $name jméno okresu
	 * @return Nette\Database\Table\ActiveRow
	 */
	public function manageDistrict($name, $regionID) {
		$district = $this->districtDao->findByNameAndRegionID($name, $regionID);

		if (!$district) {
			$district = $this->districtDao->addDistrict($name, $regionID);
			$this->getPresenter()->flashMessage("Okres " . $name . " byl vložen do databáze.", "success");
		} else {
			$this->getPresenter()->flashMessage("Okres " . $name . " již v databázi je.", "warning");
		}

		return $district;
	}

	/**
	 * Prohledá db, jestli už město neexistuje, pokud ne, uloží ho,
	 * pokud ano vypíše flashMessage
	 * @param string $name jméno města
	 * @return Nette\Database\Table\ActiveRow|bool FALSE když mšsto přesáhne max. délku názvu
	 */
	public function manageCity($name, $districtID) {
		//kontrola delky nazvu
		if (strlen($name) > self::MAX_CITY_LENGHT) {
			$this->getPresenter()->flashMessage("Město " . $name . " je delší než " . self::MAX_CITY_LENGHT . " znaků.", "warning");
			return FALSE;
		} else {
			$city = $this->cityDao->findByNameAndDistrictID($name, $districtID);

			if (!$city) {
				$city = $this->cityDao->addCity($name, $districtID);
			} else {
				$this->getPresenter()->flashMessage("Město " . $name . " již v databázi je.", "warning");
			}
		}

		return $city;
	}

}
