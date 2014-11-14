<?php

namespace Nette\Application\UI\Form;

use Nette\DateTime;
use Nette\Application\UI\Form;
use POS\Model\UserDao;

/**
 * Základní formulář pro celou registraci.
 */
class DatingRegistrationBaseForm extends BaseForm {

	/**
	 * Přidá výběr věku uživatele.
	 * @param date|null $age Věk uživatele
	 */
	public function addAge($age) {
		if (isset($age)) {
			$date = new DateTime($age);
			$year = $date->format("Y");
			$month = intval($date->format("m")); // intval - ochrana proti 01,02 a pod.
			$day = intval($date->format("d")); // intval - ochrana proti 01,02 a pod.
		}
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
	 * Zkontroluje, zda je uživateli 18 let
	 * @param Nette\Application\UI\Form $form
	 */
	public function validateAge($form) {
		$values = $form->getValues();

		$age = new DateTime();
		$age->setDate($values->year, $values->month, $values->day);

		$now = new DateTime();
		$diff = $now->diff($age);
		if ($diff->y < 18) {
			$this->addError("Musí vám být alespoň 18 let.");
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

		unset($values["year"]);
		unset($values["month"]);
		unset($values["day"]);
		return $age;
	}

	/**
	 * Vrátí ve kterém se uživatel narodil znamení.
	 * @param DateTime $age
	 */
	public static function getVigor($age) {
		$age = new DateTime($age);
		$bornMonth = intval($age->format("m"));
		$bornDay = intval($age->format("d"));

		$vigors[] = array("dayFrom" => 21, "dayTo" => 20, "monthFrom" => 1, "monthTo" => 2, "name" => UserDao::VIGOR_VODNAR);
		$vigors[] = array("dayFrom" => 21, "dayTo" => 20, "monthFrom" => 2, "monthTo" => 3, "name" => UserDao::VIGOR_RYBY);
		$vigors[] = array("dayFrom" => 21, "dayTo" => 20, "monthFrom" => 3, "monthTo" => 4, "name" => UserDao::VIGOR_BERNA);
		$vigors[] = array("dayFrom" => 21, "dayTo" => 21, "monthFrom" => 4, "monthTo" => 5, "name" => UserDao::VIGOR_BYK);
		$vigors[] = array("dayFrom" => 22, "dayTo" => 21, "monthFrom" => 5, "monthTo" => 6, "name" => UserDao::VIGOR_BLIZENEC);
		$vigors[] = array("dayFrom" => 22, "dayTo" => 22, "monthFrom" => 6, "monthTo" => 7, "name" => UserDao::VIGOR_RAK);
		$vigors[] = array("dayFrom" => 23, "dayTo" => 22, "monthFrom" => 7, "monthTo" => 8, "name" => UserDao::VIGOR_LEV);
		$vigors[] = array("dayFrom" => 23, "dayTo" => 22, "monthFrom" => 8, "monthTo" => 9, "name" => UserDao::VIGOR_PANNA);
		$vigors[] = array("dayFrom" => 23, "dayTo" => 23, "monthFrom" => 9, "monthTo" => 10, "name" => UserDao::VIGOR_VAHY);
		$vigors[] = array("dayFrom" => 24, "dayTo" => 22, "monthFrom" => 10, "monthTo" => 11, "name" => UserDao::VIGOR_STIR);
		$vigors[] = array("dayFrom" => 23, "dayTo" => 21, "monthFrom" => 11, "monthTo" => 12, "name" => UserDao::VIGOR_STRELEC);
		$vigors [] = array("dayFrom" => 22, "dayTo" => 20, "monthFrom" => 12, "monthTo" => 1, "name" => UserDao::VIGOR_KOZOROH);

		foreach ($vigors as $vigor) {
			if (($bornDay >= $vigor["dayFrom"] && $bornMonth >= $vigor["monthFrom"] ) ||
				($bornDay <= $vigor["dayTo"] && $bornMonth <= $vigor["monthTo"])) {
				return $vigor["name"];
			}
		}
	}

}
