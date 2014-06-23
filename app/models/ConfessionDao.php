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
class ConfessionDao extends BaseConfessionDao {

	const TABLE_NAME = "confessions";

	/* Column name */
	const COLUMN_ID = "id";
	const COLUMN_IN_STREAM = "inStream";
	const COLUMN_MARK = "mark";
	const COLUMN_RELEASE_DATE = "release_date";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	/**
	 * Spočítá přiznání připravená k vydání (schválená, nevydaná na streamu)
	 * @return int
	 */
	public function countReleaseNotStream() {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_IN_STREAM, 0);
		$sel->where(self::COLUMN_MARK, 1);
		return $sel->count(self::COLUMN_ID);
	}

	/**
	 * Vrátí přiznání připravená k vydání.
	 * @param int $limit Maximální limit přiznání k vydání.
	 * @return Nette\Database\Table\Selection
	 */
	public function getToRelease($limit = 15) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_IN_STREAM, 0);
		$sel->where(self::COLUMN_MARK, 1);
		$sel->order(self::COLUMN_RELEASE_DATE . " ASC");
		$sel->limit($limit);
		return $sel;
	}

	/**
	 * Označí přiznání jako vydaná.
	 * @param array $confessionsID IDčka jednotlivých přiznání.
	 */
	public function setAsRelease($confessionsID) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_ID, $confessionsID);
		$sel->update(array(
			self::COLUMN_IN_STREAM => 1
		));
	}

}
