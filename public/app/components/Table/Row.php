<?php

namespace JKB\Component\Statistics;

use Nette\Utils\Html;

/**
 * Obecný řádek v tabulce
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class Row implements IRow {

	/**
	 * @var Id jednoznačně identifikující řádek v tabulce. Může to být
	 * např. i id z databáze, k jehož datům se tento řádek váže.
	 */
	public $var;

	/**
	 * @var string Název řádku
	 */
	public $name;

	/**
	 * @var string Může obsahovat odkaz na detail tohoto řádku.
	 */
	public $link = null;

	/**
	 * @var Iterator Data v buňkách, která se dají proiterovat.
	 */
	public $cells;

	/**
	 * @var int Součet všech hodnot v řádku.
	 */
	public $sum = null;

	/**
	 * @var \ArrowStatistics\StatisticBox Obsahuje další statistická data vázající se k tomuto řádku.
	 */
	public $box = null;

	public function __construct($id, $name, array $cells = array()) {
		$this->id = $id;
		$this->name = $name;
		$this->cells = $cells;
	}

	/**
	 * Vrátí data která se mají vypsat do jednoho řádku
	 * @return Iterator Data do všech sloupců, která se dají proiterovat, např. array.
	 */
	public function getCells() {
		return $this->cells;
	}

	/**
	 * Zvýrazní řádek tak, že každou buňku uzavře do <strong> elementu.
	 */
	public function highlightRow() {
		$this->name = Html::el('strong')->setText($this->name);

		foreach ($this->cells as $cell) {
			$cell->text = Html::el('strong')->setText($cell->text);
		}
	}

}
