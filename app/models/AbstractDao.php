<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

use Nette\Database;
use Nette\Object;
use Nette\Http\Session;

/**
 * Abstraktní DAO AbstractDao
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
abstract class AbstractDao extends Object {

	/** @var Database\Context */
	protected $database;

	/** @var Session */
	protected $session;

	/** @var boolean */
	protected $inTransaction;

	const SECTION_DB_PREFFIX = "db";

	public function __construct(Database\Context $database, Session $session) {
		$this->session = $session;
		$this->database = $database;
	}

	/**
	 * Vytvoří a vrátí nové Selection
	 * @param string $tableName
	 * @return Nette\Database\Table\Selection
	 */
	public function createSelection($tableName) {
		return $this->database->table($tableName);
	}

	/**
	 * Vrátí všechny řádky z databáze
	 * @param type $order Seřazení podle id
	 * @return Nette\Database\Table\Selection
	 */
	public function getAll($order = "ASC") {
		$sel = $this->getTable();
		$sel->order("id " . $order);
		return $sel;
	}

	/**
	 * Vrátí pravdivostní hodnotu, jestli danný řádek existuje
	 * @param Nette\Database\Table\ActiveRow|NULL|FALSE $row Řádek z databáze, null nebo pravdivostní hodnota.
	 * @return boolean Informace, jestli řádek existuje.
	 */
	protected function exist($row) {
		if ($row) {
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Finds the entity by its primary key.
	 *
	 * @param mixed|array $primaryKey Primary key of the entity. Array when the primary key is composite.
	 * @return Database\Table\IRow|bool Single table row or `FALSE` if the row was not found.
	 */
	public function find($primaryKey) {
		$table = $this->getTable();
		$table->wherePrimary($primaryKey);
		return $table->fetch();
	}

	/**
	 * Insert a single row with values from array, or a set of rows selected by the given Selection object.
	 *
	 * @param array|\Traversable|Database\Table\Selection $data Array/Traversable of (column => value) to insert, or
	 *                                                          Selection to perform INSERT INTO ... SELECT ...
	 * @return bool|int|Database\Table\IRow Number of affected rows for insert by selection, or inserted row with
	 *                                      primary key value filled.
	 */
	public function insert($data) {
		$table = $this->getTable();
		return $table->insert($data);
	}

	/**
	 * Deletes a row by primary keys.
	 *
	 * @param mixed|array $primaryKey Primary key of the entity. Array of (column => value) when the
	 *                                primary key is composite.
	 * @return integer Number of deleted rows.
	 */
	public function delete($primaryKey) {
		$table = $this->getTable();
		$table->wherePrimary($primaryKey);
		return $table->delete();
	}

	/**
	 * Smaže celou tabulku !!!
	 */
	public function deleteAllTable() {
		$table = $this->getTable();
		return $table->delete();
	}

	/**
	 * Najde prvek podle primárního klíče a aktualizuje mu data
	 * @param int $id ID prvku.
	 * @param array $data Data co se mají změnit.
	 */
	public function update($id, $data) {
		$sel = $this->getTable();
		$sel->wherePrimary($id);
		$sel->update($data);
	}

	public function begginTransaction() {
		if ($this->inTransaction == FALSE) {
			$this->database->beginTransaction();
			$this->inTransaction = TRUE;
		}
	}

	public function endTransaction() {
		$this->database->commit();
		$this->inTransaction = FALSE;
	}

	/**
	 * Vrátí sekci pro KONKRÉTNÍ dao. Mezi dvěma dao jsou vždy RŮZNÉ sekce.
	 * @param string $tableName Název tabulky
	 * @param string $expiration Doba expirace.
	 * @return SessionSection
	 */
	public function getUnicateSection($tableName, $expiration = "10 minutes") {
		$name = self::SECTION_DB_PREFFIX . "-" . $tableName;
		$section = $this->session->getSection($name);
		$section->setExpiration($expiration);
		return $section;
	}

	/**
	 * Všechny prázdné řetězce změní na null (kvůli databázi)
	 * @param Nette\Http\Session|\Nette\ArrayHash $data Data co se mají uložit do DB
	 * @return Nette\Http\Session|\Nette\ArrayHash
	 */
	public function nullEmptyData($data) {
		foreach ($data as $key => $record) {
			$record = empty($record) && !is_numeric($record) ? null : $record;
			$data->offsetSet($key, $record);
		}
		return $data;
	}

}
