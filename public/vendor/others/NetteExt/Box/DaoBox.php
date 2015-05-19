<?php

/*
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.
 * created on 27.3.2015
 */

namespace NetteExt;

use POS\Model\AbstractDao;
use Exception;

/**
 * Přepravka pro DAO
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class DaoBox extends Box {

	public function __construct($autoControl = TRUE) {
		parent::__construct($autoControl);
	}

	/**
	 * Obecný setter pro nastavení proměnných.
	 * @param string $name Název proměnné.
	 * @param AbstractDao $value Hodnota proměnné.
	 */
	public function __set($name, $value) {
		if (!($value instanceof AbstractDao)) {
			throw new Exception("Value must be instance of AbstractDao");
		}

		parent::__set($name, $value);
	}

	/**
	 * Obecný getter pro vrácení proměnné
	 * @param string $name Název proměnné.
	 * @return AbstractDao
	 */
	public function __get($name) {
		return parent::__get($name);
	}

	/**
	 * Obecný setter pro nastavení proměnných.
	 * @param string $name Název Dao.
	 * @param AbstractDao $value Dao.
	 */
	public function offsetSet($name, $value) {
		if (!($value instanceof AbstractDao)) {
			throw new Exception("Value must be instance of AbstractDao");
		}

		parent::offsetSet($name, $value);
	}

	/**
	 * Obecný getter pro vrácení proměnné
	 * @param string $name Název Dao.
	 * @return AbstractDao
	 */
	public function offsetGet($name) {
		return parent::offsetGet($name);
	}

}
