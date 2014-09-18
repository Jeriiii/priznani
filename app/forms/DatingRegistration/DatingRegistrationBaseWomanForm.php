<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer;
use POS\Model\UserDao;

/*
 * rozšiřuje DatingRegistrationBaseSomebodyForm o konkrétní věci pro ženu
 */

class DatingRegistrationBaseWomanForm extends DatingRegistrationBaseSomebodyForm {

	/**
	 * @var \POS\Model\UserDao
	 */
	public $userDao;

	public function __construct(UserDao $userDao, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($userDao, $parent, $name);
		$this->userDao = $userDao;
		$users = $this->userDao;

		$this->addSelect('bra_size', 'Velikost košíčků:', $users->getUserBraSizeOption());

		$this->addSelect('hair_colour', 'Barva vlasů:', $users->getUserHairs())
			->setPrompt("- vyberte -")
			->addRule(Form::FILLED, "Vyberte prosím barvu vlasů");
	}

	public function submitted($form) {
		parent::submitted($form);
	}

}
