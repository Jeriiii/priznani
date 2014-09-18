<?php

namespace Nette\Application\UI\Form;

use Nette\DateTime;
use Nette\Application\UI\Form;

/**
 * Základní formulář pro celou registraci.
 */
class DatingRegistrationBaseForm extends BaseForm {

	/**
	 * Přidá výběr věku uživatele.
	 * @param int $year Rok narození uživatele.
	 * @param int $month Měsíc narození uživatele.
	 * @param int $day Den narození uživatele.
	 */
	public function addAge($year = null, $month = null, $day = null) {
		$months = array(1 => 'leden', 'únor', 'březen', 'duben', 'květen', 'červen', 'červenec', 'srpen', 'září', 'říjen', 'listopad', 'prosinec');
		$days = array_combine(range(1, 31), range(1, 31));
		$years = array_combine(range(date("Y"), 1910), range(date("Y"), 1910));

		$this->addSelect('day', 'Den narození: ', $days)
			->setPrompt('Den')
			->addRule(Form::FILLED, "Prosím vyplňte den Vašeho narození.");

		$this->addSelect('month', 'Měsíc narození:  ', $months)
			->setPrompt('Měsíc')
			->addRule(Form::FILLED, "Prosím vyplňte měsíc Vašeho narození.");

		$this->addSelect('year', 'Rok narození: ', $years)
			->setPrompt('Rok')
			->addRule(Form::FILLED, "Prosím vyplňte rok Vašeho narození.");

		if (isset($day)) {
			$this->setDefaults(array(
				"day" => $day,
				"month" => $month,
				"year" => $year
			));
		}
	}

	/**
	 * Vytvoří datum narození ze zadaných hodnot do formuláře.
	 * @param Nette\ArrayHash $values Hodnoty formuláře.
	 * @return Nette\DateTime Datum narození uživatele.
	 */
	public function getAge($values) {
		$age = new DateTime();
		$age->setDate($values->year, $values->month, $values->day);
		return $age;
	}

}
