<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

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
		$property[UserPropertyDao::COLUMN_CITY_ID] = $data->cityID;
		$property[UserPropertyDao::COLUMN_DISTRICT_ID] = $data->districtID;
		$property[UserPropertyDao::COLUMN_REGION_ID] = $data->regionID;
		return $sel->insert($property);
	}

}
