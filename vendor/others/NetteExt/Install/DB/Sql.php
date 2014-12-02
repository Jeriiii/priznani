<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace NetteExt\Install\DB;

/**
 * Přepravka na SQL + vytváří SQL dotazy
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
use NetteExt\Dir\Dir;

class Sql {
	/* složky */

	const DIR_DATABASE = "database";
	const DIR_STABLE = "stable";
	const DIR_PATCH = "patch";
	const DIR_DATA = "data";
	const DIR_CHAT = "chat";
	const DIR_MORE_DATA = "data/more";
	const DIR_CHAT_DATA = "chat/data";

	/** @var string Název databáze */
	private $dbName;

	/** @var array Výsledné sql */
	private $sql = array();

	/** @var string Cesta do kořenové složky s SQL */
	private $sqlRootDir;

	public function __construct($dbName, $testingMode) {
		$this->dbName = $dbName;
		$this->setRootDir();
	}

	/**
	 * Nastaví kořenovou složku SQL scriptů.
	 * @param boolean $testingMode Je instalace SQL zapnutá testovacím nástrojem?
	 */
	private function setRootDir() {
		$this->sqlRootDir = WWW_DIR . "/../" . self::DIR_DATABASE;
	}

	/**
	 * Přidá SQL do celkového SQL
	 * @param string|array $sql SQL co se má přidat.
	 */
	private function addSql($sql) {
		if (is_array($sql)) {
			$this->sql = $this->sql + $sql;
		} else {
			$this->sql[] = $sql;
		}
	}

	/**
	 * Vrátí výsledné SQL.
	 * @return array
	 */
	public function getSql() {
		return $this->sql;
	}

	/**
	 * Nastaví SQL pro smazání a celé vytvoření jedné databáze. Neobsahuje
	 * data pro chat databázi
	 * Tu pak i naplní daty.
	 */
	public function setSqlAllDB() {

		$this->addStartSql(TRUE);
		$this->addStable();
		$this->addPatches();
		$this->addDevelopSql();
		$this->addData();
	}

	/**
	 * Nastaví SQL pro obnovu dat
	 * @param \POS\Model\DatabaseDao $dbDao
	 */
	public function setData($dbDao) {
		$this->addStartSql();
		$this->addRemoveData($dbDao);
		$this->addData();
	}

	/**
	 * Uloží obsah SQL scriptů ze složky
	 * @param string $dirName Název složky.
	 */
	private function addSqlFiles($dirName) {
		$stablePath = $this->sqlRootDir . "/" . $dirName;
		$stableDir = new Dir($stablePath);
		$stableDir->sortOutFilles(0, ".sql");

		$sql = $stableDir->getFilles();
		$this->addSql($sql);
	}

	/**
	 * Vytvoří sql pro nastevení databáze.
	 * @param boolean $createDB Smaže a znovu vytvoří databázi.
	 */
	private function addStartSql($createDB = FALSE) {
		$sql = "";
		if ($createDB) {
			$sql .= "DROP DATABASE `" . $this->dbName . "`;";
			$sql .= "CREATE DATABASE `" . $this->dbName . "`;";
		}
		$sql .= "USE `" . $this->dbName . "`;";
		$this->addSql($sql);
	}

	/**
	 * Přidá stabilní verzi databáze, která vytvoří DB bez dat
	 */
	private function addStable() {
		$this->addSqlFiles(self::DIR_STABLE);
	}

	/**
	 * Přidá sql z patchů
	 */
	private function addPatches() {
		$this->addSqlFiles(self::DIR_PATCH);
	}

	/**
	 * Přidá SQL z aktuálního vývoje
	 */
	public function addDevelopSql() {
		$this->addSqlFiles("../" . self::DIR_DATABASE);
	}

	/**
	 * Přidá data do DB
	 */
	public function addData() {
		$this->addSqlFiles(self::DIR_DATA);
	}

	/**
	 * Přidá další data do DB
	 */
	public function setMoreData() {
		$this->addSqlFiles(self::DIR_MORE_DATA);
	}

	/**
	 * Přidá chat data do DB
	 */
	public function addChat() {
		$this->addSqlFiles(self::DIR_CHAT);
		$this->addSqlFiles(self::DIR_CHAT_DATA);
	}

	/**
	 * Přidá SQL pro mazání dat
	 * @param \POS\Model\DatabaseDao $dbDao
	 */
	public function addRemoveData($dbDao) {
		$sqlAllTables = "SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '" . $this->dbName . "' AND TABLE_TYPE = 'BASE TABLE'";
		$allTables = $dbDao->getDatabase()->query($sqlAllTables);

		$sql = "";
		foreach ($allTables as $table) {
			$sql = $sql . "DELETE FROM " . $table->TABLE_NAME . "; ";
		}

		$this->addSql($sql);
	}

	/**
	 * Vrátí celou cestu ke kořenové složce s SQL scripty.
	 * @return string
	 */
	public function getSQLRootDir() {
		return $this->sqlRootDir;
	}

}
