<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form;
use Nette\ComponentModel\IContainer;
use POS\Model\UserDao;
use Nette\Http\SessionSection;

/**
 * První formulář registrace
 */
class DatingRegistrationFirstForm extends BaseForm {

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

		$months = array(1 => 'leden', 'únor', 'březen', 'duben', 'květen', 'červen', 'červenec', 'srpen', 'září', 'říjen', 'listopad', 'prosinec');
		$days = range(1, 31);
		$years = array_combine(range(date("Y"), 1910), range(date("Y"), 1910));

		$this->addSelect('day', 'Den: ', $days)
			->setPrompt('Den')
			->addRule(Form::FILLED, "Prosím vyplňte den Vašeho narození.");

		$this->addSelect('month', 'Měsíc:  ', $months)
			->setPrompt('Měsíc')
			->addRule(Form::FILLED, "Prosím vyplňte měsíc Vašeho narození.");

		$this->addSelect('year', 'Rok: ', $years)
			->setPrompt('Rok')
			->addRule(Form::FILLED, "Prosím vyplňte měsíc Vašeho narození.");

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
		$values = $form->values;
		$presenter = $this->getPresenter();

		//přičte jedna ke dni - zruší posun při číslování pole 'days' od nuly
		$date = date_create()->setDate($values->year, $values->month, $values->day + 1);

		//uložení checkboxů
		foreach ($this->userDao->getArrWantToMeet() as $key => $interest) {
			$this->regSession[$key] = $values[$key] == TRUE ? 1 : 0;
		}

		$this->regSession->role = 'unconfirmed_user';
		$this->regSession->age = $date;
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
