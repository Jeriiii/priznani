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
	const COLUMN_URL = 'url';
	const COLUMN_NAME = 'name';
	const COLUMN_TEXT = 'text';
	const COLUMN_ACCESS_RIGHTS = 'access_rights';
	const COLUMN_EXCERPT = 'excerpt'; //úryvek článku

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

	/**
	 * Najde stránku podle url.
	 * @param string $url Url stránky.
	 * @return \Nette\Database\Table\ActiveRow Nalezený článek.
	 */
	public function findByUrl($url) {
		$sel = $this->getTable();

		$sel->where(self::COLUMN_URL, $url);
		return $sel->fetch();
	}

	/**
	 * Najde a vrátí poslední článek.
	 * @return \Nette\Database\Table\ActiveRow Poslední článek.
	 */
	public function findLast() {
		$sel = $this->getTable();
		$sel->order(self::COLUMN_ORDER . ' DESC');

		return $sel->fetch();
	}

}
