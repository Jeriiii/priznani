<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * Pro přímou práci s DB. NEPOUŽÍVAT!
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class DatabaseDao extends AbstractDao {

	/**
	 * Vrací databázi
	 * @return Database\Context
	 */
	public function getDatabase() {
		return $this->database;
	}

	/**
	 * Vrací vazby mezi tabulkami tak, že vrátí vazbu z tabulky $tableName
	 * na ostatní, ale nevrací vazbu z ostatních tabulek na $tableName.
	 * $tableName -> ostatní tabulky
	 * from_table - tabulka, ze které jsou relace
	 * to_table - tabulka, kam relace směřují
	 * @param string $dbName Název databáze, nad kterou se mají vazby zjistit
	 * @param string $tableName Název tabulky, ze které chci vazby na jiné.
	 * @return \Nette\Database\Table\Selection Vazby mezi tabulkami.
	 */
	public function getAnchestors($dbName, $tableName) {
		$sql = 'SELECT
			`column_name`,
			`table_name` AS from_table,
			`referenced_table_name` AS to_table
			FROM
				`information_schema`.`KEY_COLUMN_USAGE`
			WHERE
				`referenced_table_schema` = "' . $dbName . '"
			AND
				`table_name` = "' . $tableName . '"
			AND
				`referenced_column_name` IS NOT NULL';

		$resultSet = $this->database->query($sql);
		return $resultSet;
	}

	/**
	 * Vrací vazby mezi tabulkami tak, že vrátí vazbu na tabulku $tableName
	 * z ostatních tabulek, ale nevrací vazbu z $tableName tabulek na
	 * ostatní tabulky.
	 * ostatní tabulky -> $tableName
	 * from_table - tabulka, ze které jsou relace
	 * to_table - tabulka, kam relace směřují
	 * @param string $dbName Název databáze, nad kterou se mají vazby zjistit
	 * @param string $tableName Název tabulky, ze které chci vazby na jiné.
	 * @return \Nette\Database\Table\Selection Vazby mezi tabulkami.
	 */
	public function getRelations($dbName, $tableName) {
		$sql = 'SELECT
			`column_name`,
			`table_name` AS from_table,
			`referenced_table_name` AS to_table
			FROM
				`information_schema`.`KEY_COLUMN_USAGE`
			WHERE
				`referenced_table_schema` = "' . $dbName . '"
			AND
				`referenced_table_name` = "' . $tableName . '"
			AND
				`referenced_column_name` IS NOT NULL';

		$resultSet = $this->database->query($sql);
		return $resultSet;
	}

}
