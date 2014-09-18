<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer;
use POS\Model\UserDao;

/*
 * slouží jako základ pro ženy, muže a páry
 * konkrétně se jedná o třetí a čtvrté formuláře
 */

class DatingRegistrationBaseSomebodyForm extends DatingRegistrationBaseForm {

	protected $presenter;

	/**
	 * @var \POS\Model\UserDao
	 */
	public $userDao;

	public function __construct(UserDao $userDao, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);
		$this->userDao = $userDao;
		$users = $this->userDao;

		$this->addSelect('marital_state', 'Stav:', $users->getUserStateOption())
			->setPrompt("- vyberte -")
			->addRule(Form::FILLED, "Vyberte Váš stav");
		$this->addSelect('orientation', 'Orientace:', $users->getUserOrientationOption())
			->setPrompt("- vyberte -")
			->addRule(Form::FILLED, "Vyberte váší orientaci");
		$this->addSelect('tallness', 'Výška:', $users->getUserTallnessOption())
			->setPrompt("- vyberte -")
			->addRule(Form::FILLED, "Vyberte váši výšku");
		$this->addSelect('shape', 'Postava:', $users->getUserShapeOption())
			->setPrompt("- vyberte -")
			->addRule(Form::FILLED, "Vyberte váší postavu");
		$this->addSelect('smoke', 'Kouřím:', $users->getUserHabitOption())
			->setPrompt("- vyberte -")
			->addRule(Form::FILLED, "Vyberte zda kouříte");
		$this->addSelect('drink', 'Alkohol:', $users->getUserHabitOption())
			->setPrompt("- vyberte -")
			->addRule(Form::FILLED, "Vyberte zda pijete");
		$this->addSelect('graduation', 'Vzdělání:', $users->getUserGraduationOption())
			->setPrompt("- vyberte -")
			->addRule(Form::FILLED, "Vyberte Vaše vzdělání");
	}

	public function submitted($form) {
		$this->presenter = $this->getPresenter();
	}

}
