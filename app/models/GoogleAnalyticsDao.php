<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * GoogleAnalyticsDao
 * slouží k zpžístupnění údajů o Google Analytics z databáze
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class GoogleAnalyticsDao extends AbstractDao {

	/** @var Nette\Database */
	protected $database;

	const TABLE_NAME = "google_analytics";

	/* Column name */

	// TO DO

	/**
	 * Vrací tuto tabulku
	 * @return Nette\Database\Table\Selection
	 */
	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

}
