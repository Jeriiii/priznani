<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * Práce s uživateli UserBaseDao
 * slouží k statické práci s vlastnosmi uživatelů, převádění do polí a pod.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
abstract class UserBaseDao extends AbstractDao {
	/* Column name */

	const COLUMN_ID = "id";
	const COLUMN_AGE = "age";
	const COLUMN_TYPE = "type";
	const COLUMN_MARITAL_STATE = "marital_state";
	const COLUMN_ORIENTATION = "orientation";
	const COLUMN_TALLNESS = "tallness";
	const COLUMN_SHAPE = "shape";
	const COLUMN_PENIS_LENGTH = "penis_length";
	const COLUMN_PENIS_WIDTH = "penis_width";
	const COLUMN_SMOKE = "smoke";
	const COLUMN_DRINK = "drink";
	const COLUMN_GRADUATION = "graduation";
	const COLUMN_BRA_SIZE = "bra_size";
	const COLUMN_HAIR_COLOUR = "hair_colour";
	const COLUMN_VIGOR = "vigor";

	/* Druh uživatele */
	const PROPERTY_MAN = 1;
	const PROPERTY_WOMAN = 2;
	const PROPERTY_COUPLE = 3;
	const PROPERTY_COUPLE_MAN = 4;
	const PROPERTY_COUPLE_WOMAN = 5;
	const PROPERTY_GROUP = 6;

	/* Znamení uživatele */
	const VIGOR_VODNAR = 1;
	const VIGOR_RYBY = 2;
	const VIGOR_BERNA = 3;
	const VIGOR_BYK = 4;
	const VIGOR_BLIZENEC = 5;
	const VIGOR_RAK = 6;
	const VIGOR_LEV = 7;
	const VIGOR_PANNA = 8;
	const VIGOR_VAHY = 9;
	const VIGOR_STIR = 10;
	const VIGOR_STRELEC = 11;
	const VIGOR_KOZOROH = 12;

	/**
	 * vrátí specifické věci pro pohlaví
	 */
	protected function getSex($userProperty) {
		$property = $userProperty->type;
		/* žena */
		if ($property == 2 || $property == 3 || $property == 5) {
			return $this->getWomanData($userProperty);
		}
		/* muž */
		if ($property == 1 || $property == 4) {
			return $this->getManData($userProperty);
		}
		/* skupina */
		return array();
	}

	/**
	 * vrací data specifická pro ženu
	 */
	protected function getWomanData($userProperty) {
		return array(
			'Velikost košíčku' => UserBaseDao::getTranslateUserBraSize($userProperty->bra_size),
			'Barva vlasů' => self::getTranslateUserHairColor($userProperty->hair_colour)
		);
	}

	/**
	 * vrací data specifická pro muže
	 */
	protected function getManData($userProperty) {
		return array(
			'Délka penisu (cm)' => $userProperty->penis_length,
			'Obvod penisu' => UserBaseDao::getTranslateUserPenisWidth($userProperty->penis_width),
		);
	}

	/**
	 * vrací další data jako je třeba jestli polyká
	 */
	protected function getOtherData($user) {
		$other = array(
			'Trojka' => $user->threesome,
			'Anální sex' => $user->anal,
			'Skupinový sex' => $user->group,
			'BDSM' => $user->bdsm,
			'Polykání' => $user->swallow,
			'Orální sex' => $user->oral,
			'Piss' => $user->piss,
			'Sex masáž' => $user->sex_massage,
			'Petting' => $user->petting,
			'Fisting' => $user->fisting,
			'Hluboké kouření' => $user->deepthrought,
		);

		/* vyhození nevyplňěných položek */
		foreach ($other as $key => $item) {
			if (isset($item))
				$other[$key] = $item == 1 ? "ano" : "ne";
			else
				unset($other[$key]);
		}
		return $other;
	}

	/**
	 * vrácí data která jsou stejná jak pro uživatele tak pro partnera
	 */
	protected function getBaseData($user) {
		return array(
			'Věk' => self::getAge($user->age),
			'Výška' => UserBaseDao::getTranslateUserTallness($user->tallness),
			'Typ těla' => UserBaseDao::getTranslateUserShape($user->shape),
			'Kouřeni cigaret' => UserBaseDao::getTranslateUserHabit($user->smoke),
			'Pití alkoholu' => UserBaseDao::getTranslateUserHabit($user->drink),
			'Vzdělání' => UserBaseDao::getTranslateUserGraduation($user->graduation),
			'Status' => UserBaseDao::getTranslateUserState($user->marital_state),
			'Sexuální orientace' => UserBaseDao::getTranslateUserOrientacion($user->orientation),
		);
	}

	/**
	 * Vrátí věk z datumu narození.
	 * @param date $birth
	 */
	public static function getAge($birth) {
		$now = new \Nette\DateTime;
		$birth = new \Nette\DateTime($birth);
		$diff = $now->diff($birth);
		return $diff->y;
	}

	/*
	 * vrací data o cilove skupine uzivatele
	 */

	protected function getWantToMeet($user) {
		$seek = "";
		if ($user->want_to_meet_men == 1) {
			$seek .= "muže, ";
		}
		if ($user->want_to_meet_women == 1) {
			$seek .= "ženu, ";
		}
		if ($user->want_to_meet_couple == 1) {
			$seek .= "pár, ";
		}
		if ($user->want_to_meet_couple_men == 1) {
			$seek .= "mužský pár, ";
		}
		if ($user->want_to_meet_couple_women == 1) {
			$seek .= "ženský pár, ";
		}
		if ($user->want_to_meet_group == 1) {
			$seek .= "skupinu, ";
		}
		$seek = substr($seek, 0, -2);
		return array(
			'Hledám' => $seek,
		);
	}

	/**
	 * Vrátí správná data k zapsání do DB
	 * @param array $data Data obsahující nejen informace o páru, musí se
	 * proto probrat.
	 */
	protected function getBaseUserProperty($data) {
		$property[UserPropertyDao::COLUMN_AGE] = $data->age;
		$property[UserPropertyDao::COLUMN_VIGOR] = $data->vigor;
		$property[UserPropertyDao::COLUMN_MARITAL_STATE] = $data->marital_state;
		$property[UserPropertyDao::COLUMN_TYPE] = $data->type;
		$property[UserPropertyDao::COLUMN_ORIENTATION] = $data->orientation;
		$property[UserPropertyDao::COLUMN_TALLNESS] = $data->tallness;
		$property[UserPropertyDao::COLUMN_SHAPE] = $data->shape;
		$property[UserPropertyDao::COLUMN_PENIS_LENGTH] = $data->penis_length;
		$property[UserPropertyDao::COLUMN_PENIS_WIDTH] = $data->penis_width;
		$property[UserPropertyDao::COLUMN_SMOKE] = $data->smoke;
		$property[UserPropertyDao::COLUMN_DRINK] = $data->drink;
		$property[UserPropertyDao::COLUMN_GRADUATION] = $data->graduation;
		$property[UserPropertyDao::COLUMN_BRA_SIZE] = $data->bra_size;
		$property[UserPropertyDao::COLUMN_HAIR_COLOUR] = $data->hair_colour;

		return $property;
	}

	/*	 * ***************** PŘEKLADAČE PRO VLASTNOSTI UŽIVATELE ************ */

	/**
	 * vrací překlad user property - typ uživatele např. pár
	 */
	public static function getTranslateUserProperty($property) {
		$translate_properties = UserBaseDao::getUserPropertyOption();
		return $translate_properties[$property];
	}

	/**
	 * vrací překlad user state - stav uživatele např. zadaný
	 */
	public static function getTranslateUserState($state) {
		$translate_states = UserBaseDao::getUserStateOption();
		return $translate_states[$state];
	}

	/**
	 * vrací překlad user orientacion - sexuální orientaci uživatele
	 */
	public static function getTranslateUserOrientacion($orientacion) {
		$translate_orientacions = UserBaseDao::getUserOrientationOption();
		return $translate_orientacions[$orientacion];
	}

	/**
	 * vrací překlad user tallness - výšku uživatele
	 */
	public static function getTranslateUserTallness($tallness) {
		$translate_tallness = UserBaseDao::getUserTallnessOption();
		return $translate_tallness[$tallness];
	}

	/**
	 * vrací překlad user shape - postavu uživatele
	 */
	public static function getTranslateUserShape($shape) {
		$translate_shapes = UserBaseDao::getUserShapeOption();
		return $translate_shapes[$shape];
	}

	/**
	 * vrací překlad user habits - zvyky uživatele
	 */
	public static function getTranslateUserHabit($habit) {
		$translate_habits = UserBaseDao::getUserHabitOption();
		return $translate_habits[$habit];
	}

	/**
	 * vrací překlad user graduation - nejvyšší dosažené vzdělání uživatele
	 */
	public static function getTranslateUserGraduation($graduation) {
		$translate_graduations = UserBaseDao::getUserGraduationOption();
		return $translate_graduations[$graduation];
	}

	/**
	 * vrací překlad user penis width - obvod penisu uživatele
	 */
	public static function getTranslateUserPenisWidth($penisWidth) {
		$translate_penis_width = UserBaseDao::getUserPenisWidthOption();
		return $translate_penis_width[$penisWidth];
	}

	/**
	 * vrací překlad user bra size - velikost prsou uživatele
	 */
	public static function getTranslateUserBraSize($braSize) {
		$translate_bra_size = UserBaseDao::getUserBraSizeOption();
		return $translate_bra_size[$braSize];
	}

	/**
	 * vrací překlad user hair color
	 */
	public static function getTranslateUserHairColor($hairColor) {
		$translate_hairs = UserBaseDao::getUserHairs();
		return $translate_hairs[$hairColor];
	}

	/*	 * ************* VRACÍ STATICKÁ POLE S PŘEKLADAMA ****************** */

	/**
	 * vrací pole s překlady pro user property - typ uživatele např. pár
	 */
	public static function getUserPropertyOption() {
		return array(
			2 => 'Žena',
			1 => 'Muž',
			3 => 'Pár',
			5 => 'Pár dvě ženy',
			4 => 'Pár dva muži',
			6 => 'Skupina',
		);
	}

	/**
	 * vrací pole s překlady pro user interest in - např. žena nebo pár
	 */
	public static function getUserInterestInOption() {
		return array(
			2 => 'Žena',
			1 => 'Muž',
			3 => 'Pár',
			6 => 'Skupina',
		);
	}

	public static function getArrWantToMeet() {
		return array(
			"want_to_meet_men" => "muže",
			"want_to_meet_women" => "ženu",
			"want_to_meet_couple" => "pár",
			"want_to_meet_couple_men" => "pár mužů",
			"want_to_meet_couple_women" => "pár žen",
			"want_to_meet_group" => "skupinu"
		);
	}

	/**
	 * vrací pole s překlady pro user state - stav uživatele např. zadaný
	 */
	public static function getUserStateOption() {
		return array(
			1 => 'volný',
			2 => 'ženatý / vdaná',
			3 => 'rozvedený/á',
			4 => 'oddělený/á',
			5 => 'vdovec / vdova',
			6 => 'zadaný',
		);
	}

	/**
	 * vrací pole s překlady pro user orientacion - sexuální orientaci uživatele např. bi
	 */
	public static function getUserOrientationOption() {
		return array(
			1 => 'hetero',
			2 => 'homo',
			3 => 'bi',
			4 => 'bi - chtěl bych zkusit',
		);
	}

	/**
	 * vrací pole s překlady pro user tallness - výška uživatele
	 */
	public static function getUserTallnessOption() {
		return array(
			'1' => '< 160 cm',
			'2' => '160 - 170 cm',
			'3' => '170 - 180 cm',
			'4' => '180 - 190 cm',
			'5' => '> 190 cm',
		);
	}

	/**
	 * vrací pole s překlady pro user shape - postava uživatele
	 */
	public static function getUserShapeOption() {
		return array(
			1 => 'atletická',
			2 => 'hubená',
			3 => 'štíhlá',
			4 => 'normální',
			5 => 'plnoštíhlá',
			6 => 'při těle',
		);
	}

	/**
	 * vrací pole s překlady pro user habit - zvyky uživatele
	 */
	public static function getUserHabitOption() {
		return array(
			1 => 'často',
			2 => 'ne',
			3 => 'příležitostně',
		);
	}

	/**
	 * vrací pole s překlady pro user graduation - nejvyšší vzdělání uživatele
	 */
	public static function getUserGraduationOption() {
		return array(
			1 => 'základní',
			2 => 'vyučen/a',
			3 => 'střední',
			4 => 'vyšší odborné',
			5 => 'vysoké',
		);
	}

	/**
	 * vrací pole s překlady pro user penis width - obvod penisu
	 */
	public static function getUserPenisWidthOption() {
		return array(
			1 => '3cm-8cm',
			2 => '8cm-11cm',
			3 => '11cm-15cm',
			4 => '15cm-20cm'
		);
	}

	/**
	 * vrací pole s překlady pro user bra size - velikost prsou
	 */
	public static function getUserBraSizeOption() {
		return array(
			1 => 'A',
			2 => 'B',
			3 => 'C',
			4 => 'D',
			5 => 'E',
			6 => 'F',
		);
	}

	/**
	 * vrací pole s překlady pro barvu vlasů
	 */
	public static function getUserHairs() {
		return array(
			1 => 'blond',
			2 => 'hnědá',
			3 => 'zrzavá',
			4 => 'černá',
			5 => 'jiná',
		);
	}

}
