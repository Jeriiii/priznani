<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * NAME DAO NAMEDao
 * slouží k
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class GalleryDao extends BaseGalleryDao {

	const TABLE_NAME = "galleries";

	/* Column name */
	const COLUMN_ID = "id";
	const COLUMN_NAME = "name";
	const COLUMN_DESCRIPTION = "description";
	const COLUMN_SEX_MODE = "sexmode";
	const COLUMN_PARTY_MODE = "partymode";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	public function updateNameDecripMode($id, $name, $description, $mode) {
		if ($mode == "sex") {
			$column_set_mode = self::COLUMN_SEX_MODE;
			$column_unset_mode = self::COLUMN_PARTY_MODE;
		} else {
			$column_set_mode = self::COLUMN_PARTY_MODE;
			$column_unset_mode = self::COLUMN_SEX_MODE;
		}

		$sel = $this->getTable();
		$sel->wherePrimary($id);
		$sel->update(array(
			self::COLUMN_NAME => $name,
			self::COLUMN_DESCRIPTION => $description,
			$column_set_mode => 1,
			$column_unset_mode => 0
		));
	}

}
