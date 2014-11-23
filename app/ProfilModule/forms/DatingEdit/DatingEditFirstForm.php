<?php

namespace Nette\Application\UI\Form;

use Nette\ComponentModel\IContainer,
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

	/**
	 * @var ActiveRow
	 */
	private $couple;

	public function __construct(UserPropertyDao $userPropertyDao, UserDao $userDao, ActiveRow $user, $couple, IContainer $parent = NULL, $name = NULL) {
		$this->userPropertyDao = $userPropertyDao;
		$userProperty = $this->userPropertyDao->find($user->propertyID);
		$this->couple = $couple;

		parent::__construct($userDao, $parent, $name, $userProperty, $couple);

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

		$values->age = $this->getAge($values);
		$secondAge = $this->getSecondAge($values);
		$values->vigor = $this->getVigor($values->age);

		$this->userPropertyDao->update($user->propertyID, $values);

		if (!empty($this->couple)) {
			$this->couple->update(array(
				"age" => $secondAge
			));
		}

		$presenter->flashMessage("Informace byla změněny");
		$presenter->redirect('this');
	}

}
