<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

use Nette\DateTime;
use Nette\Database\SqlLiteral;

/**
 * Základní dao pro všechny druhy přiznání.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class BaseConfessionDao extends AbstractDao {
	/* Column name */

	const COLUMN_ID = "id";
	const COLUMN_MARK = "mark";
	const COLUMN_RELEASE_DATE = "release_date";
	const COLUMN_SORT_DATE = "sort_date";
	const COLUMN_NOTE = "note";
	const COLUMN_FBLIKE = "fblike";
	const COLUMN_COMMENT = "comment";
	const COLUMN_WAS_ON_FB = "was_on_fb";
	const COLUMN_ADMIN_ID = "adminID";
	const COLUMN_CREATE = "create";

	/* marks */
	const MARK_UNPROCESSED = 0;
	const MARK_PROCESSED = 1;
	const MARK_INRUBBISH = 2;
	const MARK_TOFB = 3;
	const MARK_DUPLICATE = 4;

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	/**
	 * Vrátí počet řádků přiznání ve stavu na v koši.
	 * @return int
	 */
	public function countInRubbish() {
		return $this->countMark(self::MARK_INRUBBISH);
	}

	/**
	 * Vrátí počet řádků přiznání ve stavu na fb.
	 * @return int
	 */
	public function countToFb() {
		return $this->countMark(self::MARK_TOFB);
	}

	/**
	 * Vrátí počet řádků přiznání ve stavu nevyřízeno.
	 * @return int
	 */
	public function countUnprocessed() {
		return $this->countMark(self::MARK_UNPROCESSED);
	}

	/**
	 * Vrátí počet řádků přiznání ve stavu vyřízeno.
	 * @return int
	 */
	public function countProcessed() {
		return $this->countMark(self::MARK_PROCESSED);
	}

	/**
	 *
	 * @param string $mark Nový stav přiznání
	 * @param int $selectAdminID
	 * @return Nette\Database\Table\Selection
	 */
	public function getInMarkAndAdmin($mark, $selectAdminID) {
		$sel = $this->getByMark($mark);

		if (isset($selectAdminID)) {
			$sel->where(self::COLUMN_ADMIN_ID, $selectAdminID);
		}

		$sel->order("mark ASC, id ASC");

		return $sel;
	}

	/**
	 * Vrátí počet řádků přiznání v určitém stavu.
	 * @param int $mark Stav přiznání.
	 * @return int
	 */
	private function countMark($mark) {
		$sel = $this->getByMark($mark);
		return $sel->count();
	}

	/**
	 * Vrátí přiznání v určitém stavu
	 * @param int $mark Stav přiznání.
	 * @return int
	 */
	private function getByMark($mark) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_MARK, $mark);
		return $sel;
	}

	/**
	 * Vrátí vydaná přiznání.
	 * @param string $order Řazení.
	 * @param int $duplicate Vrátit i duplicitní přiznání.
	 * @return Nette\Database\Table\Selection
	 */
	public function getPublishedConfession($order = NULL, $duplicate = FALSE) {
		$sel = $this->getTable();
		$order = empty($order) ? self::COLUMN_SORT_DATE : $order;

		if (!$duplicate) {
			$sel->where(self::COLUMN_MARK . ' != ?', 4);
		}

		$sel->where(self::COLUMN_RELEASE_DATE . ' <= ?', new DateTime());
		$sel->order($order . " DESC");

		return $sel;
	}

	/**
	 * Vrátí všechna přiznání obsahující tento text.
	 * @param string $text Text samotného přiznání.
	 * @return Nette\Database\Table\Selection
	 */
	public function getConnectionsLikeText($text) {
		$text = trim($text);

		$sel = $this->getTable();
		$sel->where(self::COLUMN_NOTE . " LIKE ?", "%" . $text . "%");
		return $sel;
	}

	/**
	 * Vrátí jedno přiznání obsahující tento text.
	 * @param string $text Text samotného přiznání.
	 * @return bool|Database\Table\IRow
	 */
	public function getConnectionLikeText($text) {
		return $this->getConnectionsLikeText($text)->fetch();
	}

	/**
	 * Zjistí, zda dané přiznání již existuje.
	 * @param string $text Text samotného přiznání.
	 * @return bool
	 */
	public function existConnectionLikeText($text) {
		$exist = $this->getConnectionLikeText($text);

		if ($exist) {
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Vrátí poslední naplánované přiznání.
	 * @return bool|Database\Table\IRow
	 */
	public function getLastScheduledConfession() {
		$sel = $this->getTable();
		$sel->where("NOT " . self::COLUMN_RELEASE_DATE, NULL);
		$sel->order(self::COLUMN_RELEASE_DATE . " DESC");
		return $sel->fetch();
	}

	/**
	 * Vrati přiznání, ktere ma brzi vyjít.
	 * @return bool|Database\Table\IRow
	 */
	public function getConfessionRelease() {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_RELEASE_DATE . ' > ?', new DateTime());
		$sel->order(self::COLUMN_RELEASE_DATE . " ASC");
		return $sel->fetch();
	}

	/**
	 * Zvysi like u priznani o jedna.
	 * @param int $id_confession ID přiznání.
	 */
	public function incLike($id_confession) {
		$sel = $this->getTable();
		$sel->wherePrimary($id_confession);
		$sel->update(array(
			self::COLUMN_SORT_DATE => new Nette\DateTime,
			self::COLUMN_FBLIKE => new SqlLiteral(self::COLUMN_FBLIKE . ' + 1')
		));
	}

	/**
	 * Snizi like u priznani o jedna.
	 * @param int $id_confession ID přiznání.
	 */
	public function decLike($id_confession) {
		$sel = $this->getTable();
		$sel->wherePrimary($id_confession);
		$sel->update(array(
			self::COLUMN_SORT_DATE => new Nette\DateTime,
			self::COLUMN_FBLIKE => new SqlLiteral(self::COLUMN_FBLIKE . ' - 1')
		));
	}

	/**
	 * Zvysi like u priznani o jedna.
	 * @param int $id_confession ID přiznání.
	 */
	public function incComment($id_confession) {
		$sel = $this->getTable();
		$sel->wherePrimary($id_confession);
		$sel->update(array(
			self::COLUMN_SORT_DATE => new Nette\DateTime,
			self::COLUMN_COMMENT => new SqlLiteral(self::COLUMN_COMMENT . ' + 1')
		));
	}

	/**
	 * Sníží like u priznani o jedna.
	 * @param int $id_confession ID přiznání.
	 */
	public function decComment($id_confession) {
		$sel = $this->getTable();
		$sel->wherePrimary($id_confession);
		$sel->update(array(
			self::COLUMN_SORT_DATE => new Nette\DateTime,
			self::COLUMN_COMMENT => new SqlLiteral(self::COLUMN_COMMENT . ' - 1')
		));
	}

	/**
	 * Přiřadí přiznání adminovi, který ho schválil
	 * @param int $id Přiznání ID.
	 * @param int $adminID ID administrátora, které přiznání schválil.
	 */
	public function assignAdmin($id, $adminID) {
		$sel = $this->getTable();
		$sel->wherePrimary($id);
		$sel->update(array(
			self::COLUMN_ADMIN_ID => $adminID
		));
	}

	/*	 * ************************* INSERT ************************** */

	/**
	 * Vložení přiznání
	 * @param type $note
	 * @param type $create
	 * @return int New confession ID
	 */
	public function insertNoteCreate($note, $create) {
		$sel = $this->getTable();
		$item = $sel->insert(array(
			self::COLUMN_NOTE => $note,
			self::COLUMN_CREATE => $create
		));
		return $item->id;
	}

	/*	 * ************************* UPDATE ************************** */

	/**
	 * Změna stavu a aktualizace časů
	 * @param int $id ID přiznání
	 * @param int $mark Stav přiznání
	 */
	public function updateMarkDate($id, $mark, $date = NULL) {
		if (empty($date)) {
			$date = new DateTime();
		}

		$sel = $this->getTable();
		$sel->wherePrimary($id);
		$sel->update(array(
			self::COLUMN_MARK => $mark,
			self::COLUMN_RELEASE_DATE => $date,
			self::COLUMN_SORT_DATE => $date
		));
	}

	/**
	 * Změna stavu
	 * @param int $id ID přiznání
	 * @param int $mark Stav přiznání
	 */
	public function updateMark($id, $mark) {
		$sel = $this->getTable();
		$sel->wherePrimary($id);
		$sel->update(array(
			self::COLUMN_MARK => $mark
		));
	}

	/**
	 * Změna stavu a fb
	 * @param int $id ID přiznání
	 * @param int $mark Stav přiznání
	 */
	public function updateMarkWasOnFB($id, $mark) {
		$sel = $this->getTable();
		$sel->wherePrimary($id);
		$sel->update(array(
			self::COLUMN_MARK => $mark,
			self::COLUMN_WAS_ON_FB => 1
		));
	}

}
