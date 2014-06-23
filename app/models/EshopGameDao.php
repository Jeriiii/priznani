<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * Prodávané erotické hry.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class EshopGameDao extends AbstractDao {

	const TABLE_NAME = "eshop_games";

	/* Column name */
	const COLUMN_ID = "id";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

}
