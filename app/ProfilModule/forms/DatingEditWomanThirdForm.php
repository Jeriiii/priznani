<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer;

class DatingEditWomanThirdForm extends DatingRegistrationBaseWomanForm {

	private $userModel;
	private $id_user;
	private $record;

	public function __construct(IContainer $parent = NULL, $name = NULL) {
		$this->addGroup('Osobní údaje');
		parent::__construct($parent, $name);

		$presenter = $this->getPresenter();
		$this->userModel = $this->getPresenter()->context->userModel;
		$this->id_user = $presenter->getUser()->getId();
		$userInfo = $presenter->context->userModel->findUser(array('id' => $this->id_user));

		$this->setDefaults(array(
			'marital_state' => $userInfo->marital_state,
			'orientation' => $userInfo->orientation,
			'tallness' => $userInfo->tallness,
			'shape' => $userInfo->shape,
			'smoke' => $userInfo->smoke,
			'drink' => $userInfo->drink,
			'graduation' => $userInfo->graduation,
			'bra_size' => $userInfo->bra_size,
			'hair_colour' => $userInfo->hair_colour,
		));


		$this->onSuccess[] = callback($this, 'submitted');
		$this->addSubmit('send', 'Uložit')
			->setAttribute("class", "btn btn-info");

		return $this;
	}

	public function submitted($form) {
		parent::submitted($form);
		$values = $form->values;

		$presenter = $this->getPresenter();
		$this->id_user = $presenter->getUser()->getId();

		$this->record = $presenter->context->userModel->findUser(array('id' => $this->id_user));
		if (!$this->record) {
			throw new BadRequestException;
		}

		$presenter->context->userModel->updateUser($this->record, array('marital_state' => $values->marital_state, 'orientation' => $values->orientation, 'tallness' => $values->tallness, 'shape' => $values->shape, 'smoke' => $values->smoke, 'drink' => $values->drink, 'graduation' => $values->graduation, 'bra_size' => $values->bra_size, 'hair_colour' => $values->hair_colour));
		$presenter->flashMessage('Změna osobních údajů byla úspěšná');
		$presenter->redirect("this");
	}

}
