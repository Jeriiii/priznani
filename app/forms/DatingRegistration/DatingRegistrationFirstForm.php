<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer;
use POS\Model\UserDao;

class DatingRegistrationFirstForm extends BaseForm {

	/**
	 * @var \POS\Model\UserDao
	 */
	public $userDao;

	public function __construct(UserDao $userDao, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->userDao = $userDao;
		$users = $this->userDao;

		$renderer = $this->getRenderer();
		$renderer->wrappers['controls']['container'] = 'table';
		$renderer->wrappers['pair']['container'] = 'tr';
		$renderer->wrappers['label']['container'] = 'td';
		$renderer->wrappers['control']['container'] = 'td';

		$this->addText('age', 'Věk')
			->addRule(Form::FILLED, 'Věk není vyplněn.')
			->addRule(Form::INTEGER, 'Věk není číslo.')
			->addRule(Form::RANGE, 'Věk musí být od %d do %d let.', array(18, 120));

		$this->addSelect('user_property', 'Jsem:', $users->getUserPropertyOption());

		$this->addSelect('interested_in', 'Zajímám se o:', $users->getUserInterestInOption());

		$this->onSuccess[] = callback($this, 'submitted');
		$this->addSubmit('send', 'Do druhé části registrace')
			->setAttribute("class", "btn btn-success");

		return $this;
	}

	public function submitted($form) {
		$values = $form->values;
		$presenter = $this->getPresenter();

		$presenter->redirect('Datingregistration:SecondRegForm', $values->age, $values->user_property, $values->interested_in);
	}

}
