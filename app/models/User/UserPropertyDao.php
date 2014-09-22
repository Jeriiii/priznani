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
	const COLUMN_CITY_ID = "cityID";
	const COLUMN_DISTRICT_ID = "districtID";
	const COLUMN_REGION_ID = "regionID";
	const COLUMN_PREFERENCES_ID = "preferencesID";

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
	public function registerProperty($data) {
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
		$data = $this->getCityIDs($data);
		$property[UserPropertyDao::COLUMN_CITY_ID] = $data->cityID;
		$property[UserPropertyDao::COLUMN_DISTRICT_ID] = $data->districtID;
		$property[UserPropertyDao::COLUMN_REGION_ID] = $data->regionID;
		return $sel->insert($property);
	}

	/**
	 * Všechny prázdné řetězce změní na null (kvůli databázi)
	 * @param Nette\Http\Session|\Nette\ArrayHash $data Data co se mají uložit do DB
	 * @return Nette\Http\Session|\Nette\ArrayHash
	 */
	public function nullEmptyData($data) {
		foreach ($data as $key => $record) {
			$record = empty($record) && !is_numeric($record) ? null : $record;
			$data->offsetSet($key, $record);
		}
		return $data;
	}

	/**
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
