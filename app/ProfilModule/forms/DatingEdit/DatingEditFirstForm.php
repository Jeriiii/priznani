<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer,
	POS\Model\UserDao,
	POS\Model\UserPropertyDao;
use Nette\Database\Table\ActiveRow;

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

	public function __construct(UserPropertyDao $userPropertyDao, UserDao $userDao, ActiveRow $user, IContainer $parent = NULL, $name = NULL) {

		$this->userPropertyDao = $userPropertyDao;
		$userProperty = $this->userPropertyDao->find($user->propertyID);

		parent::__construct($userDao, $parent, $name, $userProperty);

		if (isset($this["type"])) {
			unset($this["type"]);
		}

		$this["send"]->caption = "Uložit";
		$this["send"]->setAttribute("class", "btn btn-info");

		return $this;
	}

	public function submitted($form) {
		$values = $form->values;
		$presenter = $this->getPresenter();
		$this->id_user = $presenter->getUser()->getId();

		$user = $this->userDao->find($this->id_user);
		if (!$user) {
			throw new BadRequestException;
		}
		$values->age = $this->getAge($values);
		$values->vigor = $this->getVigor($values->age);

		$this->userPropertyDao->update($user->propertyID, $values);
		$presenter->redirect('this');
	}

}
