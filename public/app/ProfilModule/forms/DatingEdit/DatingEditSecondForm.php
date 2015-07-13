<?php

namespace Nette\Application\UI\Form;

use Nette\ComponentModel\IContainer,
	POS\Model\UserDao,
	POS\Model\UserPropertyDao;
use Nette\Database\Table\ActiveRow;
use POS\Model\UserCategoryDao;

class DatingEditSecondForm extends DatingRegistrationSecondForm {

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

	public function __construct(UserCategoryDao $userCategoryDao, UserPropertyDao $userPropertyDao, UserDao $userDao, IContainer $parent = NULL, $name = NULL) {
		$this->userPropertyDao = $userPropertyDao;
		$this->userCategoryDao = $userCategoryDao;
		$this->userDao = $userDao;

		parent::__construct($userDao, $parent, $name);

		if (isset($this["type"])) {
			unset($this["type"]);
		}
		$defaults = $userDao->findProperties($this->getPresenter()->getUser()->getId());
		$this->setDefaults($defaults);

		$this["send"]->caption = "Uložit";
		$this["send"]->setAttribute("class", "btn-main medium button");

		return $this;
	}

	public function submitted($form) {
		$values = $form->values;
		$presenter = $this->getPresenter();
		$this->id_user = $presenter->getUser()->getId();

		$user = $this->userDao->find($this->id_user);
		$this->userPropertyDao->update($user->propertyID, $values);

		$property = $this->userPropertyDao->find($user->propertyID);
		$this->userPropertyDao->updatePreferencesID($property, $this->userCategoryDao);

		$presenter->calculateLoggedUser();
		$presenter->flashMessage("Informace byly změněny.");
		$presenter->redirect('this');
	}

}
