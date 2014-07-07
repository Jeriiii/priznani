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

class DatingRegistrationBaseForm extends BaseForm {

	protected $presenter;

	/**
	 * @var \POS\Model\UserDao
	 */
	public $userDao;

	public function __construct(UserDao $userDao, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);
		$this->userDao = $userDao;
		$users = $this->userDao;

		$this->addSelect('marital_state', 'Stav:', $users->getUserStateOption());
		$this->addSelect('orientation', 'Orientace:', $users->getUserOrientationOption());
		$this->addSelect('tallness', 'Výška:', $users->getUserTallnessOption());
		$this->addSelect('shape', 'Postava:', $users->getUserShapeOption());
		$this->addSelect('smoke', 'Kouřím:', $users->getUserHabitOption());
		$this->addSelect('drink', 'Piju:', $users->getUserHabitOption());
		$this->addSelect('graduation', 'Vzdělání:', $users->getUserGraduationOption());
	}

	public function submitted($form) {
		$this->presenter = $this->getPresenter();
	}

}
