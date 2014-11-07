<?php

namespace Nette\Application\UI\Form;

use Nette\ComponentModel\IContainer,
	POS\Model\UserDao,
	POS\Model\UserPropertyDao;

class DatingEditWomanThirdForm extends DatingRegistrationBaseWomanForm {

	/**
	 * @var \POS\Model\UserDao
	 */
	public $userDao;

	/**
	 * @var \POS\Model\UserPropertyDao
	 */
	public $userPropertyDao;
	private $id_user;

	/** @var ActiveRow */
	private $user;

	public function __construct(UserPropertyDao $userPropertyDao, UserDao $userDao, IContainer $parent = NULL, $name = NULL) {
		$this->addGroup('Osobní údaje');
		parent::__construct($userDao, $parent, $name);

		$presenter = $this->getPresenter();
		$this->userDao = $userDao;
		$this->userPropertyDao = $userPropertyDao;
		$this->id_user = $presenter->getUser()->getId();
		$this->user = $this->userDao->find($this->id_user);
		$property = $this->user->property;

		$this->setDefaults(array(
			'marital_state' => $property->marital_state,
			'orientation' => $property->orientation,
			'tallness' => $property->tallness,
			'shape' => $property->shape,
			'smoke' => $property->smoke,
			'drink' => $property->drink,
			'graduation' => $property->graduation,
			'bra_size' => $property->bra_size,
			'hair_colour' => $property->hair_colour,
		));


		$this->onSuccess[] = callback($this, 'submitted');
		$this->addSubmit('send', 'Uložit')
			->setAttribute("class", "btn btn-info");

		return $this;
	}

	public function submitted($form) {
		$values = $form->values;
		$presenter = $this->getPresenter();

		$this->userPropertyDao->update($this->user->propertyID, $values);
		$presenter->flashMessage('Změna osobních údajů byla úspěšná');
		$presenter->redirect("this");
	}

}
