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
class UserPropertyDao extends AbstractDao {

	const TABLE_NAME = "users_properties";

	/* Column name */
	const COLUMN_ID = "id";
	const COLUMN_ID_COUPLE = "id_couple";
	const COLUMN_USER_PROPERTY = "user_property";
	const COLUMN_INTERESTED_IN = "interested_in";
	const COLUMN_FIRST_SENTENCE = "first_sentence";
	const COLUMN_ABOUT_ME = "about_me";
	const COLUMN_MARITAL_STATE = "marital_state";
        const COLUMN_ORIENTTION = "orientation";
        const COLUMN_TALLNESS = "tallness";
        const COLUMN_SHAPE = "shape";
        const COLUMN_PENIS_LENGTH = "penis_length";
        const COLUMN_PENIS_WIDTH = "penis_width";
        const COLUMN_SMOKE = "smoke";
        const COLUMN_DRINK = "drink";
        const COLUMN_GRADUATION = "graduation";
        const COLUMN_BRA_SIZE = "bra_size";
        const COLUMN_HAIR_COLOR = "hair_colour";
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
        

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

}
