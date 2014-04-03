<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer;

class DatingEditFirstForm extends EditBaseForm {

	private $userModel;
	private $id_user;

	public function __construct(IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$presenter = $this->getPresenter();
		$this->userModel = $this->getPresenter()->context->userModel;
		$this->id_user = $presenter->getUser()->getId();
		$userInfo = $presenter->context->userModel->findUser(array('id' => $this->id_user));

		$this->addText('age', 'Věk')
			->setDefaultValue($userInfo->age)
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
		$this->addSelect('user_property', 'Jsem:', $UserPropertyOption)
			->setDefaultValue($userInfo->user_property);

		$InterestOption = array(
			'woman' => 'Žena',
			'man' => 'Muž',
			'couple' => 'Pár',
			'group' => 'Skupina',
		);
		$this->addSelect('interested_in', 'Zajímám se o:', $InterestOption)
			->setDefaultValue($userInfo->interested_in);

		$this->onSuccess[] = callback($this, 'editformSubmitted');
		$this->addSubmit('send', 'Uložit')
			->setAttribute("class", "btn btn-info");

		return $this;
	}

	public function editformSubmitted($form) {
		$values = $form->values;
		$presenter = $this->getPresenter();

		$presenter->redirect('Editprofil:EditFirstForm', $values->age, $values->user_property, $values->interested_in);
	}

}
