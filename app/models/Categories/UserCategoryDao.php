<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

/**
 * Umožňuje práci s kategoriemi jednotlivých uživatelů. Kategoriemi se
 * myslí určitě vlastnosti uživatel. Př. jednoduché kategorie jsou
 * muži.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class UserCategoryDao extends AbstractDao {

	const TABLE_NAME = "user_categories";

	/* Column name */
	const COLUMN_ID = "id";
	const COLUMN_PROPERTY_WANT_TO_MEET = "property_want_to_meet";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	/**
	 * Hlavní metoda vracící všechny kategorie.
	 * @param \Nette\Database\Table\ActiveRow $userProperty Vlastnosti uživatele co hledá.
	 */
	public function getMine(ActiveRow $userProperty) {
		$sel = $this->getTable();

		$catsWTMProperty = $this->getPropertyWantToMeetCats($userProperty);
		$sel->where(self::COLUMN_PROPERTY_WANT_TO_MEET, $catsWTMProperty);

		//TO DO - filtrování podle dalších subkategorií

		return $sel;
	}

	private function getPropertyWantToMeetCats(ActiveRow $userProperty) {
		$sel = $this->createSelection(CatPropertyWantToMeetDao::TABLE_NAME);
		$sel = $this->getPeopertyCats($userProperty, $sel);
		$sel = $this->getWantToMeetCats($userProperty, $sel);
		return $sel;
	}

	/**
	 * Vrátí výsledné subkategorie podle property uživatelů, které hledám.
	 * @param \Nette\Database\Table\ActiveRow $userProperty Vlastnosti uživatele co hledá.
	 * @param \Nette\Database\Table\Selection $sel Celkové selection.
	 * @return \Nette\Database\Table\Selection Výsledné selection.
	 */
	private function getPeopertyCats(ActiveRow $userProperty, Selection $sel) {
		$property = array();
		if ($userProperty->want_to_meet_men != 0) {
			$property[] = 1;
		}

		if ($userProperty->want_to_meet_women != 0) {
			$property[] = 2;
		}

		if ($userProperty->want_to_meet_couple != 0) {
			$property[] = 3;
		}

		if ($userProperty->want_to_meet_couple_men != 0) {
			$property[] = 4;
		}

		if ($userProperty->want_to_meet_couple_women != 0) {
			$property[] = 5;
		}

		if ($userProperty->want_to_meet_group != 0) {
			$property[] = 6;
		}

		$conProperty = CatPropertyWantToMeetDao::COLUMN_TYPE . "= ? ";
		for ($i = 0; $i < (count($property) - 1); $i++) {
			$conProperty = $conProperty . " OR " . CatPropertyWantToMeetDao::COLUMN_TYPE . "= ? ";
		}
		$sel->where($conProperty, $property);

		return $sel;
	}

	/**
	 * Vrátí správně vybrané subkategorie podle wantToMeet uživatelů které hledám.
	 * @param \Nette\Database\Table\ActiveRow $userProperty Vlastnosti uživatele co hledá.
	 * @param \Nette\Database\Table\Selection $sel Celkové selection.
	 * @return \Nette\Database\Table\Selection Výsledné selection.
	 */
	private function getWantToMeetCats(ActiveRow $userProperty, Selection $sel) {
		$selKeys = array(1, 2);
		if ($userProperty->type == UserBaseDao::PROPERTY_MAN) {
			$sel->where(
				CatPropertyWantToMeetDao::COLUMN_WANT_TO_MEET_MEN . "= ? OR " .
				CatPropertyWantToMeetDao::COLUMN_WANT_TO_MEET_MEN . "= ?", $selKeys
			);
		} else if ($userProperty->type == UserBaseDao::PROPERTY_WOMAN) {
			$sel->where(
				CatPropertyWantToMeetDao::COLUMN_WANT_TO_MEET_WOMEN . "= ? OR " .
				CatPropertyWantToMeetDao::COLUMN_WANT_TO_MEET_WOMEN . "= ?", $selKeys
			);
		} else if ($userProperty->type == UserBaseDao::PROPERTY_COUPLE) {
			$sel->where(
				CatPropertyWantToMeetDao::COLUMN_WANT_TO_MEET_COUPLE . "= ? OR " .
				CatPropertyWantToMeetDao::COLUMN_WANT_TO_MEET_COUPLE . "= ?", $selKeys
			);
		} else if ($userProperty->type == UserBaseDao::PROPERTY_COUPLE_MAN) {
			$sel->where(
				CatPropertyWantToMeetDao::COLUMN_WANT_TO_MEET_COUPLE_MEN . "= ? OR " .
				CatPropertyWantToMeetDao::COLUMN_WANT_TO_MEET_COUPLE_MEN . "= ?", $selKeys
			);
		} else if ($userProperty->type == UserBaseDao::PROPERTY_COUPLE_WOMAN) {
			$sel->where(
				CatPropertyWantToMeetDao::COLUMN_WANT_TO_MEET_COUPLE_WOMEN . "= ? OR " .
				CatPropertyWantToMeetDao::COLUMN_WANT_TO_MEET_COUPLE_WOMEN . "= ?", $selKeys
			);
		} else if ($userProperty->type == UserBaseDao::PROPERTY_GROUP) {
			$sel->where(
				CatPropertyWantToMeetDao::COLUMN_WANT_TO_MEET_GROUP . "= ? OR " .
				CatPropertyWantToMeetDao::COLUMN_WANT_TO_MEET_GROUP . "= ?", $selKeys
			);
		}

		return $sel;
	}

}
