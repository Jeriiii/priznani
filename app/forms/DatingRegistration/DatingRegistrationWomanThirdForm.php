<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer;
use POS\Model\UserDao;
use Nette\Http\SessionSection;

class DatingRegistrationWomanThirdForm extends DatingRegistrationBaseWomanForm {

	/**
	 * @var \POS\Model\UserDao
	 */
	public $userDao;

	/** @var \Nette\Http\SessionSection */
	private $regSession;

	public function __construct(UserDao $userDao, IContainer $parent = NULL, $name = NULL, SessionSection $regSession = NULL) {
		parent::__construct($userDao, $parent, $name);

		$this->regSession = $regSession;
		$this->onSuccess[] = callback($this, 'submitted');
		$this->addSubmit('send', 'Do čtvrté části registrace')
			->setAttribute("class", "btn btn-main");

		return $this;
	}

	public function submitted($form) {
		$values = $form->values;

		$this->regSession->marital_state = $values->marital_state;
		$this->regSession->orientation = $values->orientation;
		$this->regSession->tallness = $values->tallness;
		$this->regSession->shape = $values->shape;
		$this->regSession->smoke = $values->smoke;
		$this->regSession->drink = $values->drink;
		$this->regSession->graduation = $values->graduation;
		$this->regSession->bra_size = $values->bra_size;
		$this->regSession->hair_colour = $values->hair_colour;
		$this->regSession->penis_length = "";
		$this->regSession->penis_width = "";

		$this->getPresenter()->redirect('Datingregistration:register');
	}

}
