<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace NetteExt;

/**
 * Box na dao, aby se dali přenášet jedním objektem
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
use POS\Model\AbstractDao;

class DaoBox {

	/** @var array Proměnné templaty */
	protected $vars = array();

	/**
	 * Obecný setter pro nastavení proměnných.
	 * @param string $name Název Dao.
	 * @param AbstractDao $value Dao.
	 */
	public function __set($name, AbstractDao $value) {
		$this->offsetSet($name, $value);
	}

	/**
	 * Obecný getter pro vrácení proměnné
	 * @param string $name Název Dao.
	 * @return AbstractDao
	 */
	public function __get($name) {
		return $this->offsetGet($name);
	}

	/**
	 * Je již toto dao nastavené?
	 * @param string $name Název Dao
	 * @return TRUE = pokud je nastavené.
	 */
	public function offsetExist($name) {
		if (array_key_exists($name, $this->vars)) {
			return TRUE;
		} elsE {
			return FALSE;
		}
	}

	/**
	 * Obecný setter pro nastavení proměnných.
	 * @param string $name Název Dao.
	 * @param AbstractDao $value Dao.
	 */
	public function offsetSet($name, AbstractDao $value) {
		$this->vars[$name] = $value;
	}

	/**
	 * Obecný getter pro vrácení proměnné
	 * @param string $name Název Dao.
	 * @return AbstractDao
	 */
	public function offsetGet($name) {
		if (array_key_exists($name, $this->vars)) {
			return $this->vars[$name];
		}

		return NULL;
	}

	/**
	 * Vrátí všechny proměnné jako pole.
	 * @return array Pole s Dao
	 */
	public function getVariables() {
		return $this->vars;
	}

}
