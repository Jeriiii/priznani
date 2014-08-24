<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\ComponentModel\IContainer;
use POS\Model\CityDao;
use POS\Model\DistrictDao;

/**
 * Upraví město
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class CityEditForm extends CityNewForm {

	/**
	 * @var \POS\Model\CityDao
	 */
	private $cityDao;
	private $city;
	private $districtID;

	public function __construct(CityDao $cityDao, DistrictDao $districtDao, $city, $districtID, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($cityDao, $districtDao, $parent, $name);

		$this->cityDao = $cityDao;
		$this->city = $city;
		$this->districtID = $districtID;

		$this->setDefaults(array(
			"name" => $this->city,
			"districtID" => $this->districtID
		));
	}

	public function submitted($form) {
		$values = $form->values;

		$city = $this->cityDao->findByNameAndDistrictID($this->city, $this->districtID);

		if (!$city) {
			$this->getPresenter()->flashMessage('Město nebylo nalezeno', 'danger');
			$this->getPresenter()->redirect('Cities:');
		}
		$data = array(
			"name" => $values->name,
			"districtID" => $values->districtID
		);

		/* ohlídání duplicit */
		try {
			$this->cityDao->update($city->id, $data);
		} catch (\POS\Exception\DuplicateRowException $ex) {
			$this->getPresenter()->flashMessage("Toto město již existuje!", 'danger');
			$this->getPresenter()->redirect('Cities:addCity');
		}

		$this->getPresenter()->flashMessage('Město bylo upraveno', 'success');
		$this->getPresenter()->redirect('Cities:');
	}

}
