<?php

/*
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.
 * created on 3.3.2015
 */

namespace NetteExt\DBMover;

use POS\Model\DatabaseDao;
use Nette\ArrayHash;
use NetteExt\Install\DB\Sql;
use NetteExt\Serialize\Serializer;
use Nette\Caching\Cache;
use Nette\Caching\Storages\FileStorage;
use POS\Model\ConfessionDao;
use POS\Model\AdviceDao;
use POS\Model\ChatMessagesDao;
use POS\Model\StreamDao;
use POS\Model\OldUserDao;
use NetteExt\DBMover\DBDataInstaler;

/**
 * Přesune DB tak, že načte data z DB a uloží do cache. Na cílovém stroji
 * ji z cache vyjme a znovu nahraje.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class DBMover {

	const CACHE_DATA_NAME = 'db-data';

	/** @var array Tabulky, se kterými se nemá pracovat - např. jsou moc velké než aby se
	 * ukládali do cache. */
	private $notUseTables = array(
		ConfessionDao::TABLE_NAME, AdviceDao::TABLE_NAME,
		ChatMessagesDao::TABLE_NAME, StreamDao::TABLE_NAME,
		OldUserDao::TABLE_NAME
	);

	/** @var DatabaseDao */
	private $databaseDao;

	/** @var array Názvy tabulek, z kterých se mají načíst data. */
	private $tableNames = array();

	/** @var string Název databáze. */
	private $dbName = 'datenodecz'; //'datenodecz';

	public function __construct(DatabaseDao $databaseDao) {
		$this->databaseDao = $databaseDao;

		$sqlAllTables = Sql::getAllTablesNameInDB($this->dbName);
		$allTables = $this->databaseDao->getDatabase()->query($sqlAllTables);
		$tableNames = array();

		foreach ($allTables as $table) {
			$tableNames[] = $table->TABLE_NAME;
		}

		$this->tableNames = array_diff($tableNames, $this->notUseTables);
	}

	/**
	 * Uloží data z DB do cache (souboru)
	 */
	public function saveToCache() {
		$data = $this->loadDB();

		$sorter = new TablesSorter($this->databaseDao, $this->notUseTables, $this->dbName);
		$tables = $sorter->sort($data);

		$cache = self::createCache();
		$cache->save(self::CACHE_DATA_NAME, $tables);
	}

	/**
	 * Načte data z cache (souboru)
	 */
	public function loadFromCache() {
		$cache = self::createCache();
		$data = $cache->load(self::CACHE_DATA_NAME);

		$instaler = new DBDataInstaler($this->databaseDao);
		$instaler->install($data);

		$this->data = $data;
	}

	/**
	 * Vytvoří a vrátí cache uložiště.
	 * @return Cache
	 */
	private static function createCache() {
		$storage = new FileStorage(WWW_DIR . '/../dbtemp');
		return new Cache($storage);
	}

	/**
	 * Načte data z databáze.
	 * @return array Data z databáze.
	 */
	private function loadDB() {
		$db = $this->databaseDao->getDatabase();
		$data = array();

		foreach ($this->tableNames as $tableName) {
			$sel = $db->table($tableName);
			$serializer = new Serializer($sel);
			$data[$tableName] = $serializer->toArrayHash();
		}

		return $data;
	}

}
