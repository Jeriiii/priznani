<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer,
	POS\Model\UserDao,
	POS\Model\UserPropertyDao;

class DatingEditFirstForm extends DatingRegistrationFirstForm {

	/**
	 * @var \POS\Model\UserDao
	 */
	public $userDao;

	/**
	 * @var \POS\Model\UserPropertyDao
	 */
	public $userPropertyDao;
	private $id_user;

	public function __construct(UserPropertyDao $userPropertyDao, UserDao $userDao, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($userDao, $parent, $name);

		$this->userPropertyDao = $userPropertyDao;
		$presenter = $this->getPresenter();
		$this->id_user = $presenter->getUser()->getId();
		$userInfo = $this->userDao->find($this->id_user);

		/* $this["age"]->setDefaultValue($userInfo->property->age); */

		$this["user_property"]->setDefaultValue($userInfo->property->user_property);
		$this["interested_in"]->setDefaultValue($userInfo->property->interested_in);

		$this["send"]->caption = "Uložit";
		$this["send"]->setAttribute("class", "btn btn-info");

		return $this;
	}

	public function submitted($form) {
		$values = $form->values;
		$presenter = $this->getPresenter();
		$this->id_user = $presenter->getUser()->getId();
		$record = $this->userDao->find($this->id_user);
		if (!$record) {
			throw new BadRequestException;
		}
		$this->userPropertyDao->update($record->propertyID, array(/* 'age' => $values->age, */'user_property' => $values->user_property, 'interested_in' => $values->interested_in));
		$presenter->redirect('this');
	}

}
