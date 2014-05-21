<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer;

class DatingRegistrationFirstForm extends BaseForm {

	public function __construct(IContainer $parent = NULL, $name = NULL) {
		$renderer = $this->getRenderer();
		$renderer->wrappers['controls']['container'] = 'table';
		$renderer->wrappers['pair']['container'] = 'tr';
		$renderer->wrappers['label']['container'] = 'td';
		$renderer->wrappers['control']['container'] = 'td';
		parent::__construct($parent, $name);
		$this->addText('age', 'Věk')
				->addRule(Form::FILLED, 'Věk není vyplněn.')
				->addRule(Form::INTEGER, 'Věk není číslo.')
				->addRule(Form::RANGE, 'Věk musí být od %d do %d let.', array(18, 120));

		$UserPropertyOption = array(
			'woman' => 'Žena',
			'man' => 'Muž',
			'couple' => 'Pár',
			'coupleWoman' => 'Pár dvě ženy',
			'coupleMan' => 'Pár dva muži',
			'group' => 'Skupina',
		);
		$this->addSelect('user_property', 'Jsem:', $UserPropertyOption);

		$InterestOption = array(
			'woman' => 'Žena',
			'man' => 'Muž',
			'couple' => 'Pár',
			'group' => 'Skupina',
		);
		$this->addSelect('interested_in', 'Zajímám se o:', $InterestOption);

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
