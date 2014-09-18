<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form;
use Nette\ComponentModel\IContainer;
use POS\Model\UserDao;
use Nette\Http\SessionSection;
use Nette\DateTime;

/**
 * První formulář registrace
 */
class DatingRegistrationFirstForm extends DatingRegistrationBaseForm {

	/**
	 * @var \POS\Model\UserDao
	 */
	public $userDao;

	/** @var \Nette\Http\SessionSection */
	private $regSession;

	public function __construct(UserDao $userDao, IContainer $parent = NULL, $name = NULL, $regSession = NULL) {
		parent::__construct($parent, $name);

		$this->userDao = $userDao;
		$this->regSession = $regSession;

		$this->addGroup('Základní údaje:');

		$this->addAge();

		$this->addSelect('user_property', 'Jsem:', $this->userDao->getUserPropertyOption());

		$this->addGroup('Zajímám se o:');

		$this->addWantToMeet();

		if (isset($regSession)) {
			$this->setDefaults(array(
				"age" => $regSession->age,
				"user_property" => $regSession->user_property
			));
		}

		$this->onSuccess[] = callback($this, 'submitted');
		$this->onValidate[] = callback($this, 'validateWantToMeet');
		$this->addSubmit('send', 'Do druhé části registrace')
			->setAttribute("class", "btn btn-success");

		return $this;
	}

	public function submitted($form) {
		$values = $form->getValues();
		$presenter = $this->getPresenter();

		//uložení checkboxů
		foreach ($this->userDao->getArrWantToMeet() as $key => $interest) {
			$this->regSession[$key] = $values[$key] == TRUE ? 1 : 0;
		}

		$this->regSession->role = 'unconfirmed_user';
		$this->regSession->age = $this->getAge($values);
		$this->regSession->user_property = $values->user_property;

		$presenter->redirect('Datingregistration:SecondRegForm');
	}

	/**
	 * Vytváření checkboxů.
	 */
	public function addWantToMeet() {
		foreach ($this->userDao->getArrWantToMeet() as $key => $want) {
			$checkBox = $this->addCheckbox($key, $want);
			$checkBox->setDefaultValue($this->regSession[$key]);
		}
	}

	/**
	 * Zkontroluje, zda je zaškrtlý alespoň jeden checkbox
	 * @param Nette\Application\UI\Form $form
	 */
	public function validateWantToMeet($form) {
		$values = $form->values;

		foreach ($this->userDao->getArrWantToMeet() as $key => $interest) {
			if ($values[$key]) {
				return;
			}
		}

		$this->addError("Zaškrtněte prosím o koho se zajímáte");
	}

}
