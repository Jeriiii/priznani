<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * Práce s uživateli UsersBaseDao
 * slouží k statické práci s vlastnosmi uživatelů, převádění do polí a pod.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
abstract class UsersBaseDao extends AbstractDao {

	/**
	 * vrátí specifické věci pro pohlaví
	 */
	protected function getSex($user) {
		/* žena */
		if ($user->user_property == "woman" || $user->user_property == "couple" || $user->user_property == "coupleWoman") {
			return $this->getWomanData($user);
		}
		/* muž */
		if ($user->user_property == "man" || $user->user_property == "coupleMan") {
			return $this->getManData($user);
		}
		/* skupina */
		return array();
	}

	/**
	 * vrací data specifická pro ženu
	 */
	protected function getWomanData($user) {
		return array(
			'Velikost košíčku' => Users::getTranslateUserBraSize($user->bra_size),
			'Barva vlasů' => $user->hair_colour
		);
	}

	/**
	 * vrací data specifická pro muže
	 */
	protected function getManData($user) {
		return array(
			'Délka penisu' => Users::getTranslateUserPenisLength($user->penis_length),
			'Šířka penisu' => Users::getTranslateUserPenisWidth($user->penis_width),
		);
	}

	/**
	 * vrací další data jako je třeba jestli poliká
	 */
	protected function getOtherData($user) {
		$other = array(
			'Trojka' => $user->threesome,
			'Anální sex' => $user->anal,
			'Skupinový sex' => $user->group,
			'BDSM' => $user->bdsm,
			'Polykání' => $user->swallow,
			'Sperma' => $user->cum,
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
			'Výška' => Users::getTranslateUserTallness($user->tallness),
			'Typ těla' => Users::getTranslateUserShape($user->shape),
			'Kouřeni cigaret' => Users::getTranslateUserHabit($user->smoke),
			'Pití alkoholu' => Users::getTranslateUserHabit($user->drink),
			'Vzdělání' => Users::getTranslateUserGraduation($user->graduation),
			'Status' => Users::getTranslateUserState($user->marital_state),
			'Sexuální orientace' => Users::getTranslateUserOrientacion($user->orientation),
		);
	}

	/*	 * ***************** PŘEKLADAČE PRO VLASTNOSTI UŽIVATELE ************ */

	/**
	 * vrací překlad user property - typ uživatele např. pár
	 */
	public static function getTranslateUserProperty($property) {
		$translate_properties = Users::getUserPropertyOption();
		return $translate_properties[$property];
	}

	/**
	 * vrací překlad user state - stav uživatele např. zadaný
	 */
	public static function getTranslateUserState($state) {
		$translate_states = Users::getUserStateOption();
		return $translate_states[$state];
	}

	/**
	 * vrací překlad user orientacion - sexuální orientaci uživatele
	 */
	public static function getTranslateUserOrientacion($orientacion) {
		$translate_orientacions = Users::getUserOrientationOption();
		return $translate_orientacions[$orientacion];
	}

	/**
	 * vrací překlad user tallness - výšku uživatele
	 */
	public static function getTranslateUserTallness($tallness) {
		$translate_tallness = Users::getUserTallnessOption();
		return $translate_tallness[$tallness];
	}

	/**
	 * vrací překlad user shape - postavu uživatele
	 */
	public static function getTranslateUserShape($shape) {
		$translate_shapes = Users::getUserShapeOption();
		return $translate_shapes[$shape];
	}

	/**
	 * vrací překlad user habits - zvyky uživatele
	 */
	public static function getTranslateUserHabit($habit) {
		$translate_habits = Users::getUserHabitOption();
		return $translate_habits[$habit];
	}

	/**
	 * vrací překlad user graduation - nejvyšší dosažené vzdělání uživatele
	 */
	public static function getTranslateUserGraduation($graduation) {
		$translate_graduations = Users::getUserGraduationOption();
		return $translate_graduations[$graduation];
	}

	/**
	 * vrací překlad user penis lenght - délka penisu uživatele
	 */
	public static function getTranslateUserPenisLength($penisLength) {
		$translate_penis_length = Users::getUserPenisLengthOption();
		return $translate_penis_length[$penisLength];
	}

	/**
	 * vrací překlad user penis width - šířka penisu uživatele
	 */
	public static function getTranslateUserPenisWidth($penisWidth) {
		$translate_penis_width = Users::getUserPenisWidthOption();
		return $translate_penis_width[$penisWidth];
	}

	/**
	 * vrací překlad user bra size - velikost prsou uživatele
	 */
	public static function getTranslateUserBraSize($braSize) {
		$translate_bra_size = Users::getUserBraSizeOption();
		return $translate_bra_size[$braSize];
	}

	/*	 * ************* VRACÍ STATICKÁ POLE S PŘEKLADAMA ****************** */

	/**
	 * vrací pole s překlady pro user property - typ uživatele např. pár
	 */
	public static function getUserPropertyOption() {
		return array(
			"man" => "muž",
			"woman" => "žena",
			"couple" => "pár",
			"coupleMan" => "dva muži",
			"coupleWoman" => "dvě ženy",
		);
	}

	/**
	 * vrací pole s překlady pro user state - stav uživatele např. zadaný
	 */
	public static function getUserStateOption() {
		return array(
			'free' => 'volný',
			'maried' => 'ženatý / vdaná',
			'divorced' => 'rozvedený/á',
			'separated' => 'oddělený/á',
			'widow' => 'vdovec / vdova',
			'engaged' => 'zadaný',
		);
	}

	/**
	 * vrací pole s překlady pro user orientacion - sexuální orientaci uživatele např. bi
	 */
	public static function getUserOrientationOption() {
		return array(
			'hetero' => 'hetero',
			'homo' => 'homo',
			'bi' => 'bi',
			'biTry' => 'bi - chtěl bych zkusit',
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
			'0' => 'hubená',
			'1' => 'štíhlá',
			'2' => 'normální',
			'3' => 'atletická',
			'4' => 'plnoštíhlá',
			'5' => 'při těle',
		);
	}

	/**
	 * vrací pole s překlady pro user habit - zvyky uživatele
	 */
	public static function getUserHabitOption() {
		return array(
			'often' => 'často',
			'no' => 'ne',
			'occasionlly' => 'příležitostně',
		);
	}

	/**
	 * vrací pole s překlady pro user graduation - nejvyšší vzdělání uživatele
	 */
	public static function getUserGraduationOption() {
		return array(
			'zs' => 'základní',
			'sou' => 'vyučen/a',
			'sos' => 'střední',
			'vos' => 'vyšší odborné',
			'vs' => 'vysoké',
		);
	}

	/**
	 * vrací pole s překlady pro user Penis Length - délka penisu
	 */
	public static function getUserPenisLengthOption() {
		return array(
			'tiny' => 'malá',
			'normal' => 'střední',
			'big' => 'velká',
			'huge' => 'obrovská',
		);
	}

	/**
	 * vrací pole s překlady pro user penis width - šířka penisu
	 */
	public static function getUserPenisWidthOption() {
		return array(
			'tiny' => 'hubený',
			'normal' => 'střední',
			'big' => 'tlustý',
		);
	}

	/**
	 * vrací pole s překlady pro user bra size - velikost prsou
	 */
	public static function getUserBraSizeOption() {
		return array(
			'a' => 'A',
			'b' => 'B',
			'c' => 'C',
			'd' => 'D',
			'e' => 'E',
			'f' => 'F',
		);
	}

}
