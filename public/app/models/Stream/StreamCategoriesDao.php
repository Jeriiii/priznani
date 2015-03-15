<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

use Nette\DateTime;

/**
 * StreamCategoriesDao
 * pracuje s kategoriemi ve streamu
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
class StreamCategoriesDao extends AbstractDao {

	const TABLE_NAME = "stream_categories";

	/* sloupečky */
	const COLUMN_ID = "id";
	const COLUMN_MEET_GROUP = "want_to_meet_group";
	const COLUMN_MEET_COUPLE_WOMEN = "want_to_meet_couple_women";
	const COLUMN_MEET_COUPLE_MEN = "want_to_meet_couple_men";
	const COLUMN_MEET_COUPLE = "want_to_meet_couple";
	const COLUMN_MEET_WOMEN = "want_to_meet_women";
	const COLUMN_MEET_MEN = "want_to_meet_men";
	const COLUMN_FISTING = "fisting";
	const COLUMN_PETTING = "petting";
	const COLUMN_SEX_MASSAGE = "sex_massage";
	const COLUMN_PISS = "piss";
	const COLUMN_ORAL = "oral";
	const COLUMN_CUM = "cum";
	const COLUMN_SWALLOW = "swallow";
	const COLUMN_BDSM = "bdsm";
	const COLUMN_GROUP = "group";
	const COLUMN_ANAL = "anal";
	const COLUMN_THREESOME = "threesome";

	/**
	 * Vrací tuto tabulku
	 * @return Nette\Database\Table\Selection
	 */
	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	/**
	 * Vrátí všechny řádky z tabulky
	 * @return Nette\Database\Table\Selection
	 */
	public function getAllRows() {
		return $this->getTable();
	}

	/**
	 * Vrátí všechny kategorie splňující dané podmínky
	 * @param array $terms asociativní pole podmínek ve tvaru 'sloupec' => 1|0
	 * @return \Nette\Database\Table\Selection všechny kategorie, které splňují podmínky
	 */
	public function getCategoriesWhatFit(array $terms) {
		$sel = $this->getTable();
		foreach ($terms as $column => $option) {
			$sel->where($column, $option);
		}
		return $sel;
	}

}
