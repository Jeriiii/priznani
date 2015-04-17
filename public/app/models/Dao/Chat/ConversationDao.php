<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * Slouží k práci s konverzacemi.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class ConversationDao extends AbstractDao {

	const TABLE_NAME = "conversations";

	/* Column name */
	const COLUMN_ID = "id";
	const COLUMN_NAME = "name";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

}
