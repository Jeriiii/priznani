<?php

/*
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.
 * created on 27.3.2015
 */

namespace POS\Model;

/**
 * Slouží pro hodnocení obrázků
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class RateImageDao extends AbstractDao {

	const TABLE_NAME = "rate_images";

	/* Column name */
	const COLUMN_ID = "id";
	const COLUMN_IMAGE_ID = 'imageID';
	const COLUMN_USER_ID = 'userID';

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

}
