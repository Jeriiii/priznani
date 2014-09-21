<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * Dao pracující s kategoií, která rozeznává kdo je uživatel (muž, pár ...) a
 * koho hledá (ženu, skupinu ...)
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class CatPropertyWantToMeetDao extends AbstractDao {

	const TABLE_NAME = "category_property_want_to_meet";

	/* Column name */
	const COLUMN_ID = "id";
	const COLUMN_USER_PROPERTY = "user_property";
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
