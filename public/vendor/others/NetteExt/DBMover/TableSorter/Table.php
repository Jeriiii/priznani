<?php

/*
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.
 * created on 3.3.2015
 */

namespace NetteExt\DBMover;

/**
 * Zastupuje tabulku a její nutné předchůdce
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class Table {

	/**
	 * @var array Seznam tabulek, které jsou důležité pro tuto tabulku
	 */
	private $ancestors = array();

	/**
	 * @var string Název tabulky
	 */
	private $tableName;

	/**
	 * @var \Nette\ArrayHash Data tabulky.
	 */
	private $data;

	public function __construct($tableName, $data) {
		$this->tableName = $tableName;
		$this->data = $data;
	}

	/**
	 * Přidá předchůdce
	 * @param string $tableName Název tabulky, která je předchůdce této.
	 */
	public function addAncestor($tableName) {
		$this->ancestors[$tableName] = $tableName;
	}

	/**
	 * Má tato tabulka předchůdce tabulku s názvem $tableName
	 * @param string $tableName Název tabulky.
	 * @return boolean TRUE = tato tabulka má předchůdce s názvem $tableName, jinak FALSE
	 */
	public function haveAncestor($tableName) {
		if (array_key_exists($tableName, $this->ancestors)) {
			return true;
		}

		return false;
	}

	/**
	 * Odstraní předchůdce u této tabulky. Používat vyjímečně jen
	 * pokuď existuje kruhová závislost!!!
	 * @param string $tableName
	 */
	public function removeAncestor($tableName) {
		unset($this->ancestors[$tableName]);
	}

	/**
	 * Odstraní více předchůdců u této tabulky - používáno při řazení.
	 * @param array $tableNames Pole předchůdců.
	 */
	public function removeAnchestors(array $tableNames) {
		foreach ($tableNames as $tableName) {
			if ($this->haveAncestor($tableName)) {
				$this->removeAncestor($tableName);
			}
		}
	}

	/**
	 * Vrátí všechny předchůdce v poli
	 */
	public function getAncestors() {
		return $this->ancestors;
	}

	public function getTableName() {
		return $this->tableName;
	}

	public function getData() {
		return $this->data;
	}

}
