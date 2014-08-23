<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\ComponentModel\IContainer;
use POS\Model\DistrictDao;
use POS\Model\RegionDao;

/**
 * Vloží nový kraj do DB, kontroluje duplikáty
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class DistrictNewForm extends BaseForm {

	/**
	 * @var \POS\Model\RegionDao
	 */
	private $regionDao;

	/**
	 * @var \POS\Model\DistrictDao
	 */
	private $districtDao;

	public function __construct(DistrictDao $districtDao, RegionDao $regionDao, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->regionDao = $regionDao;
		$this->districtDao = $districtDao;
		$regionsRaw = $this->regionDao->getAll();
		$regions = $this->extractRegions($regionsRaw);

		$this->addText("name", "Jméno okresu:")
			->addRule(Form::FILLED, "Musíte zadat jméno okresu.");
		$this->addSelect("regionID", "Kraj:")
			->setItems($regions)
			->addRule(Form::FILLED, "Musíte vybrat kraj");

		$this->addSubmit("submit", "Vložit");
		$this->setBootstrapRender();
		$this->onSuccess[] = callback($this, 'submitted');
	}

	public function submitted($form) {
		$values = $form->values;

		try {
			$this->districtDao->addDistrict($values->name, $values->regionID);
		} catch (\POS\Exception\DuplicateRowException $ex) {
			$this->getPresenter()->flashMessage("Tento okres již existuje!", 'danger');
			$this->getPresenter()->redirect('Cities:addDistrict');
		}

		$this->getPresenter()->flashMessage('Okres byl vložen', 'success');
		$this->getPresenter()->redirect('Cities:');
	}

	/**
	 * Uloží data z databáze do pole kde klíčem je primární klíč a hodnotou jméno
	 * @param Nette\Database\Table\Selection $regionsRaw Data krajů z databáze
	 * @return array
	 */
	public function extractRegions($regionsRaw) {
		$regions = array();
		foreach ($regionsRaw as $region) {
			$regions[$region->id] = $region->name;
		}

		return $regions;
	}

}
