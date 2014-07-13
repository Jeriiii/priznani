<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * Objednávky erotických her
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class EshopGameOrderDao extends AbstractDao {

	const TABLE_NAME = "eshop_games_orders";

	/* Column name */
	const COLUMN_ID = "id";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

}