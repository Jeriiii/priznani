<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\Database\SqlLiteral;

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
	const COLUMN_CITY_ID = "cityID";
	const COLUMN_DISTRICT_ID = "districtID";
	const COLUMN_REGION_ID = "regionID";
	const COLUMN_PREFERENCES_ID = "preferencesID";
	const COLUMN_COINS = "coins";
	const COLUMN_SCORE = "score";
	const COLUMN_SOUND_EFFECT = "sound_effect";
	const COLUMN_INTIM = 'intim';
	const COLUMN_SHOW_INTIM = 'showIntim';

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	public function getUsers() {
		$sel = $this->getTable();
		$sel->select("users.*, :user_properties.*");
	}

	/**
	 * Zaregistruje vlastnosti uřivatele
	 * @param Nette\Http\Session|\Nette\ArrayHash $data Data obsahující nejen informace o páru, musí se
	 * proto probrat.
	 */
	public function registerProperty($data, UserCategoryDao $userCategoryDao) {
		$sel = $this->getTable();
		$data = $this->nullEmptyData($data);

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

		$propertyRow = $sel->insert($property);

		$this->updatePreferencesID($propertyRow, $userCategoryDao);

		return $propertyRow;
	}

	/**
	 * Vrátí uživatele podle jeho typu
	 * @param int $type od 1 - 6 (1 - muž, 2 - žena ...)
	 * @return Selection
	 */
	public function getByType($type) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_TYPE, $type);

		return $sel;
	}

	/**
	 * Správně nastaví nové preference uživatele.
	 * @param ActiveRow|ArrayHash $property
	 */
	public function updatePreferencesID($property, UserCategoryDao $userCategoryDao) {
		if (!($property instanceof ActiveRow) && !($property instanceof \Nette\ArrayHash)) {
			throw new Exception('variable $property must be instance of ActiveRow or ArrayHash');
		}

		$myNewPreference = $userCategoryDao->getMyCategory($property);

		$this->update($property->id, array(
			UserPropertyDao::COLUMN_PREFERENCES_ID => $myNewPreference->id
		));
	}

	/**
	 * @deprecated
	 * Přijme názvy města/okresu/kraje a vrátí jejich id.
	 * @param Nette\Http\Session|\Nette\ArrayHash $data Pole původních dat.
	 * @return Nette\Http\Session|\Nette\ArrayHash Pole dat s ID
	 */
	private function getCityIDs($data) {
		$selCity = $this->createSelection(CityDao::TABLE_NAME);
		$selCity->where(CityDao::COLUMN_NAME, $data->city);
		$city = $selCity->fetch();
		$data->cityID = $city->id;

		$selDistrict = $this->createSelection(DistrictDao::TABLE_NAME);
		$selDistrict->where(DistrictDao::COLUMN_NAME, $data->district);
		$district = $selDistrict->fetch();
		$data->districtID = $district->id;

		$selRegion = $this->createSelection(RegionDao::TABLE_NAME);
		$selRegion->where(RegionDao::COLUMN_NAME, $data->region);
		$region = $selRegion->fetch();
		$data->regionID = $region->id;

		return $data;
	}

	/**
	 * Přidá uživateli určité množství zlaťáků
	 * @param int $userID id uživatele, kterého se to týká
	 * @param int|float $amount množství zlatek
	 */
	public function incraseCoinsBy($userID, $amount) {
		$this->incrasePropertyBy($userID, $amount, self::COLUMN_COINS);
	}

	/**
	 * Odebere uživateli určité množství zlaťáků. Nemůže mít méně než 0
	 * @param int $userID id uživatele, kterého se to týká
	 * @param int|float $amount množství zlatek
	 */
	public function decraseCoinsBy($userID, $amount) {
		$this->decrasePropertyBy($userID, $amount, self::COLUMN_COINS);
	}

	/**
	 * Přidá uživateli určité množství bodů
	 * @param int $userID id uživatele, kterého se to týká
	 * @param int $amount množství bodů
	 */
	public function incraseScoreBy($userID, $amount) {
		$this->incrasePropertyBy($userID, $amount, self::COLUMN_SCORE);
	}

	/**
	 * Odebere uživateli určité množství bodů. Nemůže mít méně než 0
	 * @param int $userID id uživatele, kterého se to týká
	 * @param int $amount množství bodů
	 */
	public function decraseScoreBy($userID, $amount) {
		$this->decrasePropertyBy($userID, $amount, self::COLUMN_SCORE);
	}

	/**
	 * Přidá uživateli určité množství dané vlastnosti
	 * @param int $userID id uživatele, kterého se to týká
	 * @param int|float $amount množství
	 * @param $column_name jméno sloupce v user_property
	 */
	public function incrasePropertyBy($userID, $amount, $column_name) {
		$sel = $this->createSelection(UserDao::TABLE_NAME);
		$propId = $sel->wherePrimary($userID)
			->fetch()
			->offsetGet(UserDao::COLUMN_PROPERTY_ID); //property
		$sel2 = $this->getTable();
		$sel2->where(self::COLUMN_ID, $propId)
			->update(array($column_name => new SqlLiteral($column_name . ' + ' . $amount)));
	}

	/**
	 * Odebere uživateli určité číslo z nějaké vlastnosti. Nemůže mít méně než 0
	 * @param int $userID id uživatele, kterého se to týká
	 * @param int|float $amount množství
	 * @param $column_name jméno sloupce v user_property
	 */
	public function decrasePropertyBy($userID, $amount, $column_name) {
		$selu = $this->createSelection(UserDao::TABLE_NAME); //ziskani id uzivatele
		$propId = $selu->wherePrimary($userID)
			->fetch()
			->offsetGet(UserDao::COLUMN_ID);
		$sel = $this->getTable();

		$sel2 = $this->getTable();
		$current = $sel->wherePrimary($propId)
			->fetch()
			->offsetGet($column_name);
		$updated = max(array(0, $current - $amount));
		return $sel2->where(self::COLUMN_ID, $userID)
				->update(array($column_name => $updated));
	}

	/**
	 * Přepočítá kategorii uživatele do které spadá.
	 * @param \Nette\Database\Table\ActiveRow $userProperty Uživatel.
	 */
	public function recalculateCategory(ActiveRow $userProperty) {
		/* vyhledávání podle subkategorie - property want to meet */
		$catPWTM = $this->getCatPWTM($userProperty);

		// TO DO další subkategorie

		$selCat = $this->createSelection(UserCategoryDao::TABLE_NAME);
		$selCat->where(UserCategoryDao::COLUMN_PROPERTY_WANT_TO_MEET, $catPWTM->id);

		// TO DO další výběry podle subkategorií

		$cat = $selCat->fetch();
		$userProperty->update(array(
			UserPropertyDao::COLUMN_PREFERENCES_ID => $cat->id
		));
	}

	/**
	 * Vrátí subkategorii property - want to meet které daný uživatel odpovídá.
	 * @param \Nette\Database\Table\ActiveRow $userProperty Vlastnosti uživatele.
	 * @return \Nette\Database\Table\ActiveRow|FALSE|null
	 */
	private function getCatPWTM(ActiveRow $userProperty) {
		$selCatPWTM = $this->createSelection(CatPropertyWantToMeetDao::TABLE_NAME);
		$selCatPWTM->where(CatPropertyWantToMeetDao::COLUMN_TYPE, $userProperty->type);
		$selCatPWTM->where(CatPropertyWantToMeetDao::COLUMN_WANT_TO_MEET_COUPLE, $userProperty->want_to_meet_couple);
		$selCatPWTM->where(CatPropertyWantToMeetDao::COLUMN_WANT_TO_MEET_COUPLE_MEN, $userProperty->want_to_meet_couple_men);
		$selCatPWTM->where(CatPropertyWantToMeetDao::COLUMN_WANT_TO_MEET_COUPLE_WOMEN, $userProperty->want_to_meet_couple_women);
		$selCatPWTM->where(CatPropertyWantToMeetDao::COLUMN_WANT_TO_MEET_MEN, $userProperty->want_to_meet_men);
		$selCatPWTM->where(CatPropertyWantToMeetDao::COLUMN_WANT_TO_MEET_WOMEN, $userProperty->want_to_meet_women);
		$selCatPWTM->where(CatPropertyWantToMeetDao::COLUMN_WANT_TO_MEET_GROUP, $userProperty->want_to_meet_group);

		return $selCatPWTM->fetch();
	}

}
