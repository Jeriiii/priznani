<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * pro přístup k tabulce forms. Tabulka forms ukládá informace o
 * vytvořených formulářích  a jejich typech.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class FormDao extends AbstractDao {

	const TABLE_NAME = "forms";

	/* Column name */
	const COLUMN_TMP = "";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

}
