<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\ComponentModel\IContainer;
use POS\Model\RegionDao;

/**
 * Vloží nový kraj do DB, kontroluje duplikáty
 *
 * @author Daniel Holubář
 */
class RegionNewForm extends BaseForm {

	/**
	 * @var \POS\Model\RegionDao
	 */
	private $regionDao;

	public function __construct(RegionDao $regionDao, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->regionDao = $regionDao;

		$this->addText("name", "Jméno kraje:")
			->addRule(Form::FILLED, "Musíte zadat jméno kraje.");

		$this->addSubmit("submit", "Uložit");
		$this->setBootstrapRender();
		$this->onSuccess[] = callback($this, 'submitted');
	}

	public function submitted($form) {
		$values = $form->values;

		try {
			$this->regionDao->addRegion($values->name);
		} catch (\POS\Exception\DuplicateRowException $ex) {
			$this->getPresenter()->flashMessage("Tento kraj již existuje!", 'danger');
			$this->getPresenter()->redirect('Cities:addRegion');
		}

		$this->getPresenter()->flashMessage('Kraj byl vložen', 'success');
		$this->getPresenter()->redirect('Cities:');
	}

}
