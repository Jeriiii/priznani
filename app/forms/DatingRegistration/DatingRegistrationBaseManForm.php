<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer;
use POS\Model\UserDao;

/*
 * rozšiřuje DatingRegistrationBaseForm o konkrétní věci pro muže
 */

class DatingRegistrationBaseManForm extends DatingRegistrationBaseForm {

	/**
	 * @var \POS\Model\UserDao
	 */
	public $userDao;

	public function __construct(UserDao $userDao, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($userDao, $parent, $name);
		$this->userDao = $userDao;
		$users = $this->userDao;

		$this->addSelect('penis_length', 'Délka penisu:', $users->getUserPenisLengthOption());
		$this->addSelect('penis_width', 'Šířka penisu:', $users->getUserPenisWidthOption());
	}

	public function submitted($form) {
		parent::submitted($form);
	}

}