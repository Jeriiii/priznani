<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\ComponentModel\IContainer;
use POS\Model\RegionDao;
use POS\Model\DistrictDao;

/**
 * Upraví okres
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class DistrictEditForm extends DistrictNewForm {

	/**
	 * @var \POS\Model\CityDao
	 */
	private $districtDao;
	private $district;
	private $regionID;

	public function __construct(DistrictDao $districtDao, RegionDao $regionDao, $district, $regionID, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($districtDao, $regionDao, $parent, $name);

		$this->districtDao = $districtDao;
		$this->district = $district;
		$this->regionID = $regionID;

		$this->setDefaults(array(
			"name" => $this->district,
			"regionID" => $this->regionID
		));
	}

	public function submitted($form) {
		$values = $form->values;

		$district = $this->districtDao->findByNameAndRegionID($this->district, $this->regionID);

		if (!$district) {
			$this->getPresenter()->flashMessage('Okres nebyl nalezen', 'danger');
			$this->getPresenter()->redirect('Cities:');
		}
		$data = array(
			"name" => $values->name,
			"regionID" => $values->regionID
		);

		/* ohlídání duplicit */
		$this->districtDao->updateDistrict($district->id, $data);

		$this->getPresenter()->flashMessage('Okres byl upraven', 'success');
		$this->getPresenter()->redirect('Cities:');
	}

}
