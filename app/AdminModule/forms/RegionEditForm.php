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
 * Upraví kraj
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class RegionEditForm extends RegionNewForm {

	/**
	 * @var \POS\Model\RegionDao
	 */
	private $regionDao;
	private $region;

	public function __construct(RegionDao $regionDao, $region, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($regionDao, $parent, $name);

		$this->regionDao = $regionDao;
		$this->region = $region;
		$region = $this->getPresenter()->getParameter('region');

		$this->setDefaults(array(
			"name" => $region,
		));
	}

	public function submitted($form) {
		$values = $form->values;

		$region = $this->regionDao->findByName($this->region);

		if (!$region) {
			$this->getPresenter()->flashMessage('Kraj nebyl nalezen', 'danger');
			$this->getPresenter()->redirect('Cities:');
		}
		$data = array(
			"name" => $values->name,
		);

		/* ohlídání duplicit */
		try {
			$this->regionDao->updateRegion($region->id, $data);
		} catch (\POS\Exception\DuplicateRowException $ex) {
			$this->getPresenter()->flashMessage("Tento kraj již existuje!", 'danger');
			$this->getPresenter()->redirect('Cities:addRegion');
		}

		$this->getPresenter()->flashMessage('Kraj byl upraven', 'success');
		$this->getPresenter()->redirect('Cities:');
	}

}
