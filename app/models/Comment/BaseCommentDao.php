<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * Slouží jako základní dao pro všechny dao s komentářema.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
abstract class BaseCommentDao extends AbstractDao implements ICommentDao {

	public function getActivityTable() {
		return $this->createSelection(ActivitiesDao::TABLE_NAME);
	}

}
