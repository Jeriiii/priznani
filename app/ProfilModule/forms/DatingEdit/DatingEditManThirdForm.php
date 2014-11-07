<?php

namespace Nette\Application\UI\Form;

use Nette\ComponentModel\IContainer,
	POS\Model\UserDao,
	POS\Model\UserPropertyDao;
use Nette\Database\Table\ActiveRow;

class DatingEditManThirdForm extends DatingRegistrationBaseManForm {

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
		$this->user = $userDao->find($this->id_user);
		$property = $property;

		$this->setDefaults(array(
			'marital_state' => $property->marital_state,
			'orientation' => $property->orientation,
			'tallness' => $property->tallness,
			'shape' => $property->shape,
			'smoke' => $property->smoke,
			'drink' => $property->drink,
			'graduation' => $property->graduation,
			'penis_length' => $property->penis_length,
			'penis_width' => $property->penis_width,
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
