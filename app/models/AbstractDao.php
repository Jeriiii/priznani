<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

use Nette\Database;
use Nette\Object;

/**
 * Abstraktní DAO AbstractDao
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
abstract class AbstractDao extends Object {

	/** @var Database\Context */
	protected $database;

	public function __construct(Database\Context $database) {
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
	 * Finds the entity by its primary key.
	 *
	 * @param mixed|array $primaryKey Primary key of the entity. Array when the primary key is composite.
	 * @return bool|Database\Table\IRow Single table row or `FALSE` if the row was not found.
	 */
	public function find($primaryKey) {
		$table = $this->createTableSelection();
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
		$table = $this->createTableSelection();
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
		$table = $this->createTableSelection();
		$table->wherePrimary($primaryKey);
		return $table->delete();
	}

}
