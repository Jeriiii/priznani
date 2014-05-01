<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * AuthorizatorDao
 * slouží k zpřístupnění práv jednotlivých rolí z databáze
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class AuthorizatorDao extends AbstractDao {

	/** @var Nette\Database */
	protected $database;

	const TABLE_NAME = "authorizator_table";

	/* Column name */

	//TO DO

	/**
	 * Vrací tuto tabulku
	 * @return Nette\Database\Table\Selection
	 */
	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

}
