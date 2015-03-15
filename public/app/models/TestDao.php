<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * Přístup k databázi výhradně pro testovací účely. Neporovádí se nad tabulkou,
 * ale nad testovací databází
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
class TestDao extends AbstractDao {

	/**
	 * Vrátí data z dané tabulky, která mají v daném sloupci daná data
	 * @param type $data daná data
	 * @param type $columnName název sloupce
	 * @param type $tableName název tabulky
	 * @return \Nette\Database\Table\Selection dotčené sloupce nebo NULL
	 */
	public function getFromTableWithColumn($data, $columnName, $tableName) {
		$sel = $this->createSelection($tableName);
		return $sel->where($columnName, $data);
	}

}
