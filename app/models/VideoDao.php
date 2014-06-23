<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * zpřístupní videa
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class VideoDao extends AbstractDao {

	const TABLE_NAME = "videos";

	/* Column name */
	const COLUMN_ID = "id";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

}
