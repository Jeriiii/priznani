<?php

/**
 * Návrhový vzor přepravka. Sám se nepoužívá, třídy od něj dějí.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */

namespace NetteExt;

abstract class Box {

	/** @var array Proměnné templaty */
	protected $vars = array();

	/**
	 * Obecný setter pro nastavení proměnných.
	 * @param string $name Název proměnné.
	 * @param mixed $value Hodnota proměnné.
	 */
	public function __set($name, $value) {
		$this->vars[$name] = $value;
	}

	/**
	 * Obecný getter pro vrácení proměnné
	 * @param string $name Název proměnné.
	 */
	public function __get($name) {
		if (array_key_exists($name, $this->vars)) {
			return $this->vars[$name];
		}

		return NULL;
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
	 * @param mixes $value Dao.
	 */
	public function offsetSet($name, $value) {
		$this->__set($name, $value);
	}

	/**
	 * Obecný getter pro vrácení proměnné
	 * @param string $name Název Dao.
	 * @return mixes
	 */
	public function offsetGet($name) {
		return $this->__get($name);
	}

	/**
	 * Vrátí všechny proměnné jako pole.
	 * @return array Pole s hodnotami
	 */
	public function getVariables() {
		return $this->vars;
	}

}
