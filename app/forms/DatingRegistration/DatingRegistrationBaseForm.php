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
	 * @param date|null $age věk uživatele
	 * @param date|null $seccondAge věk partnera
	 * @param int|null $type Typ uživatele
	 * @param boolean $isRegistration TRUE = nacházím se na registraci
	 */
	public function addAge($age, $seccondAge, $type = NULL, $isRegistration = FALSE) {
		if (isset($age)) {
			$date = new DateTime($age);
			$year = $date->format("Y");
			$month = intval($date->format("m")); // intval - ochrana proti 01,02 a pod.
			$day = intval($date->format("d")); // intval - ochrana proti 01,02 a pod.
		}

		$months = array(1 => 'leden', 'únor', 'březen', 'duben', 'květen', 'červen', 'červenec', 'srpen', 'září', 'říjen', 'listopad', 'prosinec');
		$days = array_combine(range(1, 31), range(1, 31));
		$years = array_combine(range(date("Y"), 1910), range(date("Y"), 1910));

		$this->addGroupFirstAge($type);
		$this->addSelectAge($days, $months, $years);

		if (isset($day)) {
			$this->setDefaults(array(
				"day" => $day,
				"month" => $month,
				"year" => $year
			));
		}

		if (isset($seccondAge)) {
			$date = new DateTime($seccondAge);
			$year = $date->format("Y");
			$month = intval($date->format("m")); // intval - ochrana proti 01,02 a pod.
			$day = intval($date->format("d")); // intval - ochrana proti 01,02 a pod.
		}

		if (isset($seccondAge) || $isRegistration) {
			$this->addGroupSecondAge($type);

			$this->addSelectAge($days, $months, $years, FALSE, "Second");

			if (isset($day)) {
				$this->setDefaults(array(
					"daySecond" => $day,
					"monthSecond" => $month,
					"yearSecond" => $year
				));
			}
		}
	}

	private function addGroupFirstAge($type) {
		if ($type == UserDao::PROPERTY_COUPLE || $type == UserDao::PROPERTY_COUPLE_MAN || $type == UserDao::PROPERTY_COUPLE_WOMAN) {
			if ($type == UserDao::PROPERTY_COUPLE || $type == UserDao::PROPERTY_COUPLE_WOMAN) {
				$this->addGroup("Datum narození Partnerky");
			}
			if ($type == UserDao::PROPERTY_COUPLE_MAN) {
				$this->addGroup("Datum narození Partnera");
			}
		}
	}

	private function addGroupSecondAge($type) {
		if ($type == UserDao::PROPERTY_COUPLE || $type == UserDao::PROPERTY_COUPLE_MAN || $type == UserDao::PROPERTY_COUPLE_WOMAN) {
			if ($type == UserDao::PROPERTY_COUPLE || $type == UserDao::PROPERTY_COUPLE_MAN) {
				$this->addGroup("Datum narození Partnera");
			}
			if ($type == UserDao::PROPERTY_COUPLE_WOMAN) {
				$this->addGroup("Datum narození Partnerky");
			}
		}
	}

	private function addSelectAge($days, $months, $years, $filled = TRUE, $suffixName = "") {
		$day = $this->addSelect('day' . $suffixName, 'Den narození: ', $days);
		$day->setPrompt('Den');
		if ($filled) {
			$day->addRule(Form::FILLED, "Prosím vyplňte den Vašeho narození.");
		}

		$month = $this->addSelect('month' . $suffixName, 'Měsíc narození:  ', $months);
		$month->setPrompt('Měsíc');
		if ($filled) {
			$month->addRule(Form::FILLED, "Prosím vyplňte měsíc Vašeho narození.");
		}

		$year = $this->addSelect('year' . $suffixName, 'Rok narození: ', $years);
		$year->setPrompt('Rok');
		if ($filled) {
			$year->addRule(Form::FILLED, "Prosím vyplňte rok Vašeho narození.");
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
		$this->adult($age);

		/* nejdeli o muže, ženu či skupinu, musí být vyplněn i druhý rok narození */
		if (isset($values->type) && $values->type != UserDao::PROPERTY_MAN && $values->type != UserDao::PROPERTY_WOMAN && $values->type != UserDao::PROPERTY_GROUP) {
			/* vyplňěné všechny */
			if ($values->yearSecond && $values->monthSecond && $values->daySecond) {
				$age = new DateTime();
				$age->setDate($values->yearSecond, $values->monthSecond, $values->daySecond);
				$this->adult($age);
			} else {
				$this->addError("Vyplňtě obě data narození. Vaše i partnera.");
			}
		}
	}

	private function adult($age) {
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
	 * Vytvoří datum narození ze zadaných hodnot do formuláře.
	 * @param Nette\ArrayHash $values Hodnoty formuláře.
	 * @return Nette\DateTime Datum narození uživatele.
	 */
	public function getSecondAge($values) {
		$age = new DateTime();
		$age->setDate($values->yearSecond, $values->monthSecond, $values->daySecond);

		unset($values["yearSecond"]);
		unset($values["monthSecond"]);
		unset($values["daySecond"]);
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
