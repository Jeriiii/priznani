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

}
