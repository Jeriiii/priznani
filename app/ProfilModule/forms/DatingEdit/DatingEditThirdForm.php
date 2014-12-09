<?php

namespace Nette\Application\UI\Form;

use Nette\ComponentModel\IContainer,
	POS\Model\CoupleDao,
	POS\Model\UserDao;
use Nette\Database\Table\ActiveRow;
use Nette\ArrayHash;
use POS\Model\UserCategoryDao;
use POS\Model\UserPropertyDao;
use Nette\ObjectMixin;

/**
 * Editace dalších nastavení pro muže, ženu a pár.
 */
class DatingEditThirdForm extends DatingRegistrationThirdForm {

	/** @var \POS\Model\UserDao */
	public $userDao;

	/** @var \POS\Model\CoupleDao */
	public $coupleDao;

	/** @var ActiveRow|\Nette\ArrayHash\ */
	private $userProperty;

	/** @var ActiveRow|\Nette\ArrayHash" */
	private $couple;

	/** @var \POS\Model\UserPropertyDao */
	public $userPropertyDao;

	/**
	 * @var \POS\Model\UserCategoryDao
	 */
	public $userCategoryDao;

	public function __construct(UserCategoryDao $userCategoryDao, UserPropertyDao $userPropertyDao, CoupleDao $coupleDao, UserDao $userDao, $userProperty, $couple, IContainer $parent = NULL, $name = NULL) {
		$this->userDao = $userDao;
		$this->coupleDao = $coupleDao;
		$this->userProperty = $userProperty;
		$this->couple = $couple;
		$this->userCategoryDao = $userCategoryDao;
		$this->userPropertyDao = $userPropertyDao;

		parent::__construct($userDao, $userProperty, $couple, $parent, $name);

		$this->setFirstManDefaults($this->type, $userProperty);

		if ($this->isCouple($this->type)) {
			$this->setCoupleDefaults($this->type, $couple);
		}

		$this["send"]->setAttribute("class", "btn-main medium button");
		$this['send']->caption = "Uložit";
		return $this;
	}

	public function submitted($form) {
		$values = $form->values;
		$presenter = $this->getPresenter();

		$userData = new ArrayHash();
		$this->setFirstPersonData($this->type, $userData, $values);
		$this->userPropertyDao->update($this->userProperty->id, $userData);

		if ($this->isCouple($this->type)) {
			$coupleData = new ArrayHash();
			$this->setSecondPersonData($this->type, $coupleData, $values);
			$this->coupleDao->update($this->user->coupleID, $coupleData);
		}


		$this->userPropertyDao->updatePreferencesID($this->userProperty, $this->userCategoryDao);

		$presenter->calculateLoggedUser();
		$presenter->flashMessage('Změna osobních údajů byla úspěšná');
		$presenter->redirect("this");
	}

	/**
	 * Vyplní hodnoty z DB do formuláře pro muže, ženu nebo prvního z páru.
	 * @param int $type Typ uživatele.
	 * @param \Nette\Database\Table\ActiveRow|\Nette\ArrayHash $user Uživatel nebo pár
	 */
	private function setFirstManDefaults($type, $user) {
		if (self::isFirstWoman($type)) {
			$this->setWomanDefaults($user);
		}
		if (self::isFirstMan($type)) {
			$this->setManDefaults($user);
		}
		$this->setBaseDefaults($user);
	}

	/**
	 * Vyplní hodnoty z DB do formuláře pro druhého z páru.
	 * @param int $type Typ uživatele.
	 * @param \Nette\Database\Table\ActiveRow|\Nette\ArrayHash $user Uživatel nebo pár
	 */
	private function setCoupleDefaults($type, $user) {
		if (self::isSecondWoman($type)) {
			$this->setWomanDefaults($user, self::SECOND_MAN_SUFFIX);
		}
		if (self::isSecondMan($type)) {
			$this->setManDefaults($user, self::SECOND_MAN_SUFFIX);
		}
		$this->setBaseDefaults($user, self::SECOND_MAN_SUFFIX);
	}

	private function setBaseDefaults($user, $suffixName = "") {
		$this->setDefaults(array(
			'marital_state' . $suffixName => $user->marital_state,
			'orientation' . $suffixName => $user->orientation,
			'tallness' . $suffixName => $user->tallness,
			'shape' . $suffixName => $user->shape,
		));
	}

	private function setWomanDefaults($user, $suffixName = "") {
		$this->setDefaults(array(
			'bra_size' . $suffixName => $user->bra_size,
			'hair_colour' . $suffixName => $user->hair_colour,
		));
	}

	private function setManDefaults($user, $suffixName = "") {
		$this->setDefaults(array(
			'penis_length' . $suffixName => $user->penis_length,
			'penis_width' . $suffixName => $user->penis_width,
		));
	}

}
