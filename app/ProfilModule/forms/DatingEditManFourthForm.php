<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer;

class DatingEditManFourthForm extends DatingRegistrationBaseManForm {

	private $userModel;
	private $id_user;
	private $record;
	private $record_couple_partner;

	public function __construct(IContainer $parent = NULL, $name = NULL) {
		$this->addGroup('Osobní údaje(partner 2) - On');
		parent::__construct($parent, $name);

		$presenter = $this->getPresenter();
		$this->userModel = $this->getPresenter()->context->userModel;
		$this->id_user = $presenter->getUser()->getId();
		$this->record = $presenter->context->userModel->findUser(array('id' => $this->id_user));

		$userPartnerInfo = $presenter->context->userModel->findUserPartner(array('id' => $this->record->id_couple));

		$this->addText('age', 'Věk')
			->setDefaultValue($userPartnerInfo->age)
			->addRule(Form::FILLED, 'Věk není vyplněn.')
			->addRule(Form::INTEGER, 'Věk není číslo.')
			->addRule(Form::RANGE, 'Věk musí být od %d do %d let.', array(18, 120));

		$this->setDefaults(array(
			'marital_state' => $userPartnerInfo->marital_state,
			'orientation' => $userPartnerInfo->orientation,
			'tallness' => $userPartnerInfo->tallness,
			'shape' => $userPartnerInfo->shape,
			'smoke' => $userPartnerInfo->smoke,
			'drink' => $userPartnerInfo->drink,
			'graduation' => $userPartnerInfo->graduation,
			'penis_length' => $userPartnerInfo->penis_length,
			'penis_width' => $userPartnerInfo->penis_width,
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
		$this->record_couple_partner = $this->userModel->findUserPartner(array('id' => $this->record->id_couple));

		$presenter->context->userModel->updateUser($this->record_couple_partner, array('age' => $values->age, 'marital_state' => $values->marital_state, 'orientation' => $values->orientation, 'tallness' => $values->tallness, 'shape' => $values->shape, 'smoke' => $values->smoke, 'drink' => $values->drink, 'graduation' => $values->graduation, 'penis_length' => $values->penis_length, 'penis_width' => $values->penis_width));
		$presenter->flashMessage('Změna osobních údajů vašeho partnera byla úspěšná');
		$presenter->redirect("EditProfil:default");
	}

}
