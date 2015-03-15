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

	/** @var \Nette\Http\SessionSection */
	private $regCoupleSession;

	/** @var array Možnosti pro zaškrtnutí s kým se chci potkat. */
	private $wantToMeetOption = array(1 => "ano", 2 => "nezáleží", 0 => "ne");

	public function __construct(UserDao $userDao, IContainer $parent = NULL, $name = NULL, $regSession = NULL, $regCoupleSession = NULL, $isRegistration = TRUE) {
		parent::__construct($parent, $name);

		$this->userDao = $userDao;
		$this->regSession = $regSession;
		$this->regCoupleSession = $regCoupleSession;

		$this->addGroup('Základní údaje:');

		$secondAge = !empty($regCoupleSession) ? $regCoupleSession->age : NULL;
		$this->addAge($regSession->age, $secondAge, $regSession->type, $isRegistration);

		$this->addSelect('type', 'Jsem:', $this->userDao->getUserPropertyOption());

		$this->addGroup('Zajímám se o:');

		$this->addWantToMeet();

		if (isset($regSession)) {
			$this->setDefaults(array(
				"type" => $regSession->type
			));
		}

		$this->onSuccess[] = callback($this, 'submitted');
		$this->onValidate[] = callback($this, 'validateWantToMeet');
		$this->onValidate[] = callback($this, 'validateAge');
		$this->addSubmit('send', 'Do druhé části registrace')
			->setAttribute("class", "btn btn-main");

		return $this;
	}

	public function submitted($form) {
		$values = $form->getValues();
		$presenter = $this->getPresenter();

		$this->regSession->role = 'unconfirmed_user';
		$this->regSession->age = $this->getAge($values);
		$this->regCoupleSession->age = $this->getSecondAge($values);
		$this->regSession->vigor = $this->getVigor($this->regSession->age);
		$this->regSession->type = $values->type;

		foreach ($this->userDao->getArrWantToMeet() as $key => $want) {
			$this->regSession[$key] = $values[$key];
		}

		$presenter->redirect('Datingregistration:SecondRegForm');
	}

	/**
	 * Vytváření checkboxů.
	 */
	public function addWantToMeet() {
		foreach ($this->userDao->getArrWantToMeet() as $key => $want) {
			$radioList = $this->addRadioList($key, $want, $this->wantToMeetOption);
			$radioList->getSeparatorPrototype()->setName(NULL);

			if (!empty($this->regSession[$key]) || is_numeric($this->regSession[$key])) { //0 = ne, ale empty by neprošla
				$radioList->setDefaultValue($this->regSession[$key]);
			} else {
				$radioList->setDefaultValue(2);
			}
		}
	}

	/**
	 * Zkontroluje, zda je zaškrtlý alespoň jeden checkbox
	 * @param Nette\Application\UI\Form $form
	 */
	public function validateWantToMeet($form) {
		$values = $form->values;

		foreach ($this->userDao->getArrWantToMeet() as $key => $interest) {
			if ($values[$key] == 1) { //alespoň jedno ANO
				return;
			}
		}

		$this->addError("Zaškrtněte prosím o koho se zajímáte - alespoň jedno ano");
	}

}