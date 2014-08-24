<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer,
        POS\Model\CoupleDao,
        POS\Model\UserDao;

class DatingEditWomanFourthForm extends DatingRegistrationBaseWomanForm {

        /**
	 * @var \POS\Model\UserDao
	 */
	public $userDao;
        /**
	 * @var \POS\Model\CoupleDao
	 */
        public $coupleDao;
	private $id_user;
	private $record;
	private $record_couple_partner;

	public function __construct(CoupleDao $coupleDao, UserDao $userDao, IContainer $parent = NULL, $name = NULL) {
		$this->addGroup('Osobní údaje(partner 2) - Ona');
		parent::__construct($userDao, $parent, $name);

                $this->coupleDao = $coupleDao;
		$presenter = $this->getPresenter();
		$this->id_user = $presenter->getUser()->getId();
		$this->record = $this->userDao->find($this->id_user);

		$userPartnerInfo = $this->coupleDao->find($this->record->coupleID);

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
			'bra_size' => $userPartnerInfo->bra_size,
			'hair_colour' => $userPartnerInfo->hair_colour,
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

		$this->record = $this->userDao->find($this->id_user);
		if (!$this->record) {
			throw new BadRequestException;
		}
		$this->record_couple_partner = $this->coupleDao->find($this->record->coupleID);

		$this->coupleDao->update($this->record->coupleID, array('age' => $values->age, 'marital_state' => $values->marital_state, 'orientation' => $values->orientation, 'tallness' => $values->tallness, 'shape' => $values->shape, 'smoke' => $values->smoke, 'drink' => $values->drink, 'graduation' => $values->graduation, 'bra_size' => $values->bra_size, 'hair_colour' => $values->hair_colour));
		$presenter->flashMessage('Změna osobních údajů vašeho partnera byla úspěšná');
		$presenter->redirect("this");
	}

}
