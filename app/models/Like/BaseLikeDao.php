<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * Základní dao ke všem lajkovacím Dao
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
abstract class BaseLikeDao extends AbstractDao implements ILikeDao {

	public function getActivityTable() {
		return $this->createSelection(ActivitiesDao::TABLE_NAME);
	}

}
