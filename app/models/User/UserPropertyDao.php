<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

/**
 * NAME DAO NAMEDao
 * slouží k
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class UserPropertyDao extends UserBaseDao {

	const TABLE_NAME = "users_properties";

	/* Column name */
	const COLUMN_FIRST_SENTENCE = "first_sentence";
	const COLUMN_ABOUT_ME = "about_me";
	const COLUMN_THREESOME = "threesome";
	const COLUMN_ANAL = "anal";
	const COLUMN_GROUP = "group";
	const COLUMN_BDSM = "bdsm";
	const COLUMN_SWALLOW = "swallow";
	const COLUMN_CUM = "cum";
	const COLUMN_ORAL = "oral";
	const COLUMN_PISS = "piss";
	const COLUMN_SEX_MASSAGE = "sex_massage";
	const COLUMN_PETTING = "petting";
	const COLUMN_FISTING = "fisting";
	const COLUMN_DEEPTHROATING = "deepthrought";
	const COLUMN_WANT_TO_MEET_MEN = "want_to_meet_men";
	const COLUMN_WANT_TO_MEET_WOMEN = "want_to_meet_women";
	const COLUMN_WANT_TO_MEET_COUPLE = "want_to_meet_couple";
	const COLUMN_WANT_TO_MEET_COUPLE_MEN = "want_to_meet_couple_men";
	const COLUMN_WANT_TO_MEET_COUPLE_WOMEN = "want_to_meet_couple_women";
	const COLUMN_WANT_TO_MEET_GROUP = "want_to_meet_group";

	/* Druh uživatele */
	const PROPERTY_MAN = "m";
	const PROPERTY_WOMAN = "w";
	const PROPERTY_COUPLE = "c";
	const PROPERTY_COUPLE_MAN = "cm";
	const PROPERTY_COUPLE_WOMAN = "cw";
	const PROPERTY_GROUP = "g";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	/**
	 * Zaregistruje vlastnosti uřivatele
	 * @param array $data Data obsahující nejen informace o páru, musí se
	 * proto probrat.
	 */
	public function registerProperty($data) {
		$sel = $this->getTable();

		//vybere základní data
		$property = $this->getBaseUserProperty($data);

		$property[UserPropertyDao::COLUMN_ABOUT_ME] = $data->about_me;
		$property[UserPropertyDao::COLUMN_FIRST_SENTENCE] = $data->first_sentence;

		$property[UserPropertyDao::COLUMN_WANT_TO_MEET_MEN] = $data->want_to_meet_men;
		$property[UserPropertyDao::COLUMN_WANT_TO_MEET_WOMEN] = $data->want_to_meet_women;
		$property[UserPropertyDao::COLUMN_WANT_TO_MEET_COUPLE] = $data->want_to_meet_couple;
		$property[UserPropertyDao::COLUMN_WANT_TO_MEET_COUPLE_MEN] = $data->want_to_meet_couple_men;
		$property[UserPropertyDao::COLUMN_WANT_TO_MEET_COUPLE_WOMEN] = $data->want_to_meet_couple_women;
		$property[UserPropertyDao::COLUMN_WANT_TO_MEET_GROUP] = $data->want_to_meet_group;

		return $sel->insert($property);
	}

	/**
	 * Spojí uživatele podle toho kdo jsou (muž, žena, pár ...) a koho hledají.
	 * @param \Nette\Database\Table\ActiveRow $userProperty
	 * @param \Nette\Database\Table\Selection $users
	 */
	public function whoWantsWhom(ActiveRow $userProperty, Selection $users) {
		$this->iWantToMeet($userProperty, $users);
		$this->theyWantToMeet($userProperty, $users);
	}

	/**
	 * Hledání podle toho koho uživatel hledá (muže, ženy, pár atd...)
	 * @param \Nette\Database\Table\ActiveRow $userProperty Vlastnosti (v tomto smyslu preference) uživatele
	 * @param \Nette\Database\Table\Selection $users Neprotřídení uživatelé.
	 * @return \Nette\Database\Table\Selection Hledaní uživatelé.
	 */
	public function iWantToMeet(ActiveRow $userProperty, Selection $users) {

		$iWantToMeetPeople = array();

		$iWantToMeetMen = $userProperty->want_to_meet_men;
		if ($iWantToMeetMen) {
			$iWantToMeetPeople [] = self::COLUMN_USER_PROPERTY . "==" . self::PROPERTY_MAN;
		}

		$iWantToMeetWomen = $userProperty->want_to_meet_women;
		if ($iWantToMeetWomen) {
			$users->where(self::COLUMN_USER_PROPERTY == . self::PROPERTY_WOMAN);
		}

		$iWantToMeetCouple = $userProperty->want_to_meet_couple;
		if ($iWantToMeetCouple) {
			$users->where(self::COLUMN_USER_PROPERTY, self::PROPERTY_COUPLE);
		}

		$iWantToMeetCoupleMen = $userProperty->want_to_meet_couple_men;
		if ($iWantToMeetCoupleMen) {
			$users->where(self::COLUMN_USER_PROPERTY, self::PROPERTY_COUPLE_MAN);
		}

		$iWantToMeetCoupleWomen = $userProperty->want_to_meet_couple_women;
		if ($iWantToMeetCoupleWomen) {
			$users->where(self::COLUMN_USER_PROPERTY, self::PROPERTY_COUPLE_WOMAN);
		}

		$iWantToMeetGroup = $userProperty->want_to_meet_group;
		if ($iWantToMeetGroup) {
			$users->where(self::COLUMN_USER_PROPERTY, self::PROPERTY_GROUP);
		}

		$condition = "";
		foreach ($iWantToMeetPeople as $iWant) {

		}

		return $users->where($condition);
	}

	/**
	 * Hledání podle toho, jestli uživatele hledají mě (uživatele hledají muže a já jsem muž,
	 * tak je to vybere, ale nevybere to ty co hledají ženy).
	 * @param \Nette\Database\Table\ActiveRow $userProperty Vlastnosti (v tomto smyslu preference) uživatele
	 * @param \Nette\Database\Table\Selection $users Neprotřídení uživatelé.
	 * @return \Nette\Database\Table\Selection Hledaní uživatelé.
	 */
	public function theyWantToMeet(ActiveRow $userProperty, Selection $users) {
		$property = $userProperty->user_property;
		$man = $property == self::PROPERTY_MAN ? TRUE : FALSE;
		if ($man) {
			$users->where(self::COLUMN_WANT_TO_MEET_MEN, 1);
		}

		$woman = $property == self::PROPERTY_WOMAN ? TRUE : FALSE;
		if ($woman) {
			$users->where(self::COLUMN_WANT_TO_MEET_WOMEN, 1);
		}

		$couple = $property == self::PROPERTY_COUPLE ? TRUE : FALSE;
		if ($couple) {
			$users->where(self::COLUMN_WANT_TO_MEET_COUPLE, 1);
		}

		$coupleMen = $property == self::PROPERTY_COUPLE_MAN ? TRUE : FALSE;
		if ($coupleMen) {
			$users->where(self::COLUMN_WANT_TO_MEET_COUPLE_MEN, 1);
		}

		$coupleWomen = $property == self::PROPERTY_COUPLE_WOMAN ? TRUE : FALSE;
		if ($coupleWomen) {
			$users->where(self::COLUMN_WANT_TO_MEET_COUPLE_WOMEN, 1);
		}

		$group = $property == self::PROPERTY_GROUP ? TRUE : FALSE;
		if ($group) {
			$users->where(self::COLUMN_WANT_TO_MEET_GROUP, 1);
		}

		return $users;
	}

}
