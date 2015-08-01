<?php

/*
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.
 * created on 1.8.2015
 */

namespace POS\Model;

/**
 * Dao pro stránky na blogu.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class BlogDao extends AbstractDao {

	const TABLE_NAME = "magazine";

	/* Column name */
	const COLUMN_ID = "id";
	const COLUMN_HOMEPAGE = 'homepage';
	const COLUMN_ORDER = 'order';

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	/**
	 * Najde a vrátí homepage
	 * @return \Nette\Database\Table\ActiveRow
	 */
	public function findHomepage() {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_HOMEPAGE, 1);

		return $sel->fetch();
	}

	/**
	 * Vrátí seznam stránek
	 * @return \Nette\Database\Table\Selection
	 */
	public function getListMages() {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_HOMEPAGE, 0);
		$sel->order(self::COLUMN_ORDER . ' ASC');

		return $sel;
	}

}
