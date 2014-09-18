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
	const COLUMN_USER_PROPERTY = "user_property";
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

	/* Druh uživatele */
	const PROPERTY_MAN = "m";
	const PROPERTY_WOMAN = "w";
	const PROPERTY_COUPLE = "c";
	const PROPERTY_COUPLE_MAN = "cm";
	const PROPERTY_COUPLE_WOMAN = "cw";
	const PROPERTY_GROUP = "g";

	/**
	 * vrátí specifické věci pro pohlaví
	 */
	protected function getSex($userProperty) {
		$property = $userProperty->user_property;
		/* žena */
		if ($property == "w" || $property == "c" || $property == "cw") {
			return $this->getWomanData($userProperty);
		}
		/* muž */
		if ($property == "m" || $property == "cm") {
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
			'Barva vlasů' => $userProperty->hair_colour
		);
	}

	/**
	 * vrací data specifická pro muže
	 */
	protected function getManData($userProperty) {
		return array(
			'Délka penisu' => UserBaseDao::getTranslateUserPenisLength($userProperty->penis_length),
			'Šířka penisu' => UserBaseDao::getTranslateUserPenisWidth($userProperty->penis_width),
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
			'Věk' => $user->age,
			'Výška' => UserBaseDao::getTranslateUserTallness($user->tallness),
			'Typ těla' => UserBaseDao::getTranslateUserShape($user->shape),
			'Kouřeni cigaret' => UserBaseDao::getTranslateUserHabit($user->smoke),
			'Pití alkoholu' => UserBaseDao::getTranslateUserHabit($user->drink),
			'Vzdělání' => UserBaseDao::getTranslateUserGraduation($user->graduation),
			'Status' => UserBaseDao::getTranslateUserState($user->marital_state),
			'Sexuální orientace' => UserBaseDao::getTranslateUserOrientacion($user->orientation),
		);
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
		$property[UserPropertyDao::COLUMN_MARITAL_STATE] = $data->marital_state;
		$property[UserPropertyDao::COLUMN_USER_PROPERTY] = $data->user_property;
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
	 * vrací překlad user penis lenght - délka penisu uživatele
	 */
	public static function getTranslateUserPenisLength($penisLength) {
		$translate_penis_length = UserBaseDao::getUserPenisLengthOption();
		return $translate_penis_length[$penisLength];
	}

	/**
	 * vrací překlad user penis width - šířka penisu uživatele
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

	/*	 * ************* VRACÍ STATICKÁ POLE S PŘEKLADAMA ****************** */

	/**
	 * vrací pole s překlady pro user property - typ uživatele např. pár
	 */
	public static function getUserPropertyOption() {
		return array(
			'w' => 'Žena',
			'm' => 'Muž',
			'c' => 'Pár',
			'cw' => 'Pár dvě ženy',
			'cm' => 'Pár dva muži',
			'g' => 'Skupina',
		);
	}

	/**
	 * vrací pole s překlady pro user interest in - např. žena nebo pár
	 */
	public static function getUserInterestInOption() {
		return array(
			'w' => 'Žena',
			'm' => 'Muž',
			'c' => 'Pár',
			'g' => 'Skupina',
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
			'160' => '< 160 cm',
			'170' => '160 - 170 cm',
			'180' => '170 - 180 cm',
			'190' => '180 - 190 cm',
			'200' => '> 190 cm',
		);
	}

	/**
	 * vrací pole s překlady pro user shape - postava uživatele
	 */
	public static function getUserShapeOption() {
		return array(
			1 => 'hubená',
			2 => 'štíhlá',
			3 => 'normální',
			4 => 'atletická',
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
	 * vrací pole s překlady pro user Penis Length - délka penisu
	 */
	public static function getUserPenisLengthOption() {
		return array(
			1 => 'malá',
			2 => 'střední',
			3 => 'velká',
			4 => 'obrovská',
		);
	}

	/**
	 * vrací pole s překlady pro user penis width - šířka penisu
	 */
	public static function getUserPenisWidthOption() {
		return array(
			1 => 'hubený',
			2 => 'střední',
			3 => 'tlustý',
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

}
