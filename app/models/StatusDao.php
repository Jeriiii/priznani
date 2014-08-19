<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * StatusDAO
 * slouží k práci se statusama
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class StatusDao extends AbstractDao {

	/** @var Nette\Database */
	protected $database;

	const TABLE_NAME = "status";

	/* Column name */
	const COLUMN_ID = "id";
	const COLUMN_USER_ID = "userID";
	const COLUMN_TEXT = "message";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

}
