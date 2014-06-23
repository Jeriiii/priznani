<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * zpřístupní poradnu
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class AdviceDao extends BaseConfessionDao {

	const TABLE_NAME = "advices";

	/* Column name */
	const COLUMN_ID = "id";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

}
