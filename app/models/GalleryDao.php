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
class GalleryDao extends AbstractDao {

	const TABLE_NAME = "galleries";

	/* Column name */
	const COLUMN_ID = "id";
	const COLUMN_NAME = "name";
	const COLUMN_DESCRIPTION = "decription";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	public function updateNameDecrip($id, $name, $description) {
		$sel = $this->getTable();
		$sel->wherePrimary($id);
		$sel->update(array(
			self::COLUMN_NAME => $name,
			self::COLUMN_DESCRIPTION => $description
		));
	}

}
