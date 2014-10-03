<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer;
use POS\Model\UserDao;

/*
 * rozšiřuje DatingRegistrationBaseSomebodyForm o konkrétní věci pro muže
 */

class DatingRegistrationBaseManForm extends DatingRegistrationBaseSomebodyForm {

	/**
	 * @var \POS\Model\UserDao
	 */
	public $userDao;

	public function __construct(UserDao $userDao, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($userDao, $parent, $name);
		$this->userDao = $userDao;
		$users = $this->userDao;

		$this->addText('penis_length', 'Délka penisu:')
			->setType('number')
			->addRule(Form::INTEGER, 'Délka musí být číslo.')
			->addRule(Form::RANGE, 'Délka je mezi 2 - 40 cm', array(2, 40));

		$this->addSelect('penis_width', 'Šířka penisu:', $users->getUserPenisWidthOption());
	}

	public function submitted($form) {
		parent::submitted($form);
	}

}
