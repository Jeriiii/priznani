<?php

/*
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.
 * created on 3.3.2015
 */

namespace NetteExt\DBMover;

use POS\Model\UserDao;
use POS\Model\DatabaseDao;
use POS\Model\UserGalleryDao;
use POS\Model\UserImageDao;
use POS\Model\GalleryDao;
use POS\Model\ImageDao;

/**
 * Seřadí tabulky podle priorit.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class TablesSorter {

	/** @var string Název databáze, se kterou se pracuje. */
	private $dbName;

	/** @var DatabaseDao Dao pro práci s celou DB */
	private $databaseDao;

	/** @var array Tabulky, se kterými se nemá pracovat - např. jsou moc velké než aby se
	 * ukládali do cache. */
	private $notUseTables;

	public function __construct(DatabaseDao $databaseDao, array $notUseTables, $dbName) {
		$this->databaseDao = $databaseDao;
		$this->notUseTables = $notUseTables;
		$this->dbName = $dbName;
	}

	/**
	 * Seředí tabulky podle vazeb.
	 * @param array $tablesData Data tabulek.
	 * @return array Pole seřazených tabulek podle vazeb.
	 */
	public function sort(array $tablesData) {
		$unsortTables = $this->createTables($tablesData);
		$sortTables = $this->sortByRelations($unsortTables);
		$tablesData = $this->getTablesData($sortTables);

		return $tablesData;
	}

	/**
	 * Dá data o tabulkách do řaditelných objektů
	 * @param array $tables Data o tabulkách.
	 * @return array Pole řaditelných objektů
	 */
	private function createTables(array $tables) {
		foreach ($tables as $tableName => $table) {
			$tab = new Table($tableName, $table);
			$refs = $this->databaseDao->getAnchestors($this->dbName, $tableName);

			/* přidání předchůdců tabulky */
			foreach ($refs as $ref) {
				/* když se nemají používat, nezaznamenají se do předchůdců */
				if (in_array($ref->to_table, $this->notUseTables)) {
					continue;
				}

				$tab->addAncestor($ref->to_table);
			}

			$tables[$tableName] = $tab;
		}

		return $tables;
	}

	private function getTablesData(array $tables) {
		$tableData = array();

		foreach ($tables as $table) {
			$tableData[$table->getTableName()] = $table->getData();
		}

		return $tableData;
	}

	/**
	 * Seřadí tabulky podle vazeb.
	 * @param array $tables Tabulky, co se mají seřadit.
	 */
	private function sortByRelations(array $tables) {
		$this->removeCircleRelations($tables);

		$sortTables = array();
		$tmpTables = $tables;
		while (!empty($tables)) {
			foreach ($tables as $tableName => $table) {
				if (empty($table->getAncestors())) {
					$sortTables[$tableName] = $table;

					unset($tmpTables[$tableName]);

					$this->removeFromAnchestors($tables, $tableName);
				}
			}

			$tables = $tmpTables;
		}

		return $sortTables;
	}

	/**
	 * Odstraní tabulku s tímto jménem ze seznamu předchůdců u každé tabulky.
	 * @param array $tables Pole tabulek.
	 * @param string $tableName Název tabulky, co se má odstranit z předchůdců.
	 */
	private function removeFromAnchestors(array $tables, $tableName) {
		foreach ($tables as $tab) {
			if ($tab->haveAncestor($tableName)) {
				$tab->removeAncestor($tableName);
			}
		}
	}

	/**
	 * Odstraní kruhové vazby mezi tabulkami.
	 * @param array $tables Pole tabulek, ze kterých se mají odstranit kruhové vazby.
	 */
	private function removeCircleRelations(array $tables) {
		/* odstranění kruhové závislosti mezi tabulkami users, users_images a users_galleries - vzniká profilovkou */
		$tables[UserDao::TABLE_NAME]->removeAncestor(UserImageDao::TABLE_NAME);
		/* odstranění kruhové závislosti mezi tabulkami users_images a users_galleries a images a gelleries - vzniká náhledem galerie */
		$tables[UserGalleryDao::TABLE_NAME]->removeAncestor(UserImageDao::TABLE_NAME);
		$tables[GalleryDao::TABLE_NAME]->removeAncestor(ImageDao::TABLE_NAME);
		/* dočasné smazání tabulky - časem lze celý řádek odstranit */
		$tables['files']->removeAncestor('texts');
	}

}
