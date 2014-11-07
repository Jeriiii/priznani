<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer,
	POS\Model\CoupleDao,
	POS\Model\UserDao;

class DatingEditWomanFourthForm extends DatingRegistrationWomanFourthForm {

	/**
	 * @var \POS\Model\UserDao
	 */
	public $userDao;

	/**
	 * @var \POS\Model\CoupleDao
	 */
	public $coupleDao;
	private $id_user;
	private $user;
	private $record_couple_partner;

	public function __construct(CoupleDao $coupleDao, UserDao $userDao, IContainer $parent = NULL, $name = NULL) {
		$this->coupleDao = $coupleDao;
		$presenter = $this->getPresenter();
		$this->id_user = $presenter->getUser()->getId();
		$this->user = $this->userDao->find($this->id_user);

		$userPartnerInfo = $this->coupleDao->find($this->user->coupleID);

		parent::__construct($userDao, $parent, $name, $userPartnerInfo);

		$this->addGroup('Ona');

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


		$this['send']->caption = "Uložit";

		return $this;
	}

	public function submitted($form) {
		$values = $form->values;
		$presenter = $this->getPresenter();
		$this->id_user = $presenter->getUser()->getId();

		if (!$this->user) {
			throw new BadRequestException;
		}
		$this->record_couple_partner = $this->coupleDao->find($this->user->coupleID);
		$values->age = $this->getAge($values);
		$values->vigor = $this->getVigor($values->age);

		$this->coupleDao->update($this->user->coupleID, array($values));
		$presenter->flashMessage('Změna osobních údajů vašeho partnera byla úspěšná');
		$presenter->redirect("this");
	}

}
