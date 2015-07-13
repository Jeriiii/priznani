<?php

namespace Nette\Application\UI\Form;

use Nette\ComponentModel\IContainer,
	POS\Model\UserDao,
	POS\Model\UserPropertyDao;
use Nette\Database\Table\ActiveRow;
use POS\Model\UserCategoryDao;

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

	/**
	 * @var \POS\Model\UserCategoryDao
	 */
	public $userCategoryDao;

	public function __construct(UserCategoryDao $userCategoryDao, UserPropertyDao $userPropertyDao, UserDao $userDao, ActiveRow $user, $couple, IContainer $parent = NULL, $name = NULL) {
		$this->userPropertyDao = $userPropertyDao;
		$userProperty = $this->userPropertyDao->find($user->propertyID);
		$this->couple = $couple;
		$this->userCategoryDao = $userCategoryDao;

		parent::__construct($userDao, $parent, $name, $userProperty, $couple, FALSE);

		if (isset($this["type"])) {
			unset($this["type"]);
		}

		$this["send"]->caption = "Uložit";
		$this["send"]->setAttribute("class", "btn-main medium button");

		return $this;
	}

	public function submitted($form) {
		$values = $form->values;
		$presenter = $this->getPresenter();
		$this->id_user = $presenter->getUser()->getId();

		$user = $this->userDao->find($this->id_user);

		$values->age = $this->getAge($values);
		if (!empty($this->couple)) {
			$secondAge = $this->getSecondAge($values);
		}
		$values->vigor = $this->getVigor($values->age);

		$this->userPropertyDao->update($user->propertyID, $values);

		if (!empty($this->couple)) {
			$this->couple->update(array(
				"age" => $secondAge,
				"vigor" => $this->getVigor($secondAge)
			));
		}

		$property = $this->userPropertyDao->find($user->propertyID);
		$this->userPropertyDao->updatePreferencesID($property, $this->userCategoryDao);

		$presenter->calculateLoggedUser();
		$presenter->flashMessage("Informace byly změněny.");
		$presenter->redirect('this');
	}

}
