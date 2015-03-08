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
 * Vloží nové město do DB, kontroluje duplikáty
 *
 * @author Daniel Holubář
 */
class CityNewForm extends BaseForm {

	/**
	 * @var \POS\Model\DistrictDao
	 */
	private $districtDao;

	/**
	 * @var \POS\Model\CityDao
	 */
	private $cityDao;

	public function __construct(CityDao $cityDao, DistrictDao $districtDao, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->cityDao = $cityDao;
		$this->districtDao = $districtDao;
		$districtsRaw = $this->districtDao->getAll();
		$districts = $this->extractDistricts($districtsRaw);

		$this->addText("name", "Jméno města:")
			->addRule(Form::FILLED, "Musíte zadat jméno města.");
		$this->addSelect("districtID", "Okres:")
			->setItems($districts)
			->addRule(Form::FILLED, "Musíte vybrat okres");

		$this->addSubmit("submit", "Uložit");
		$this->setBootstrapRender();
		$this->onSuccess[] = callback($this, 'submitted');
	}

	public function submitted($form) {
		$values = $form->values;

		try {
			$this->cityDao->addCity($values->name, $values->districtID);
		} catch (\POS\Exception\DuplicateRowException $ex) {
			$this->getPresenter()->flashMessage("Toto město již existuje!", 'danger');
			$this->getPresenter()->redirect('Cities:addCity');
		}

		$this->getPresenter()->flashMessage('Město bylo vloženo', 'success');
		$this->getPresenter()->redirect('Cities:');
	}

	/**
	 * Uloží data z databáze do pole kde klíčem je primární klíč a hodnotou jméno
	 * @param Nette\Database\Table\Selection $districtsRaw Data okresů z databáze
	 * @return array
	 */
	public function extractDistricts($districtsRaw) {
		$districts = array();
		foreach ($districtsRaw as $district) {
			$districts[$district->id] = $district->name;
		}

		return $districts;
	}

}
