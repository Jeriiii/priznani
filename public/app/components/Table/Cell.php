<?php

namespace JKB\Component\Statistics;

use Nette\ArrayHash;
use Nette\Utils\Html;

/**
 * Buňka v tabulce
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class Cell {

	/** @var int Číslo v buňce - počítají se z toho různé statistiky */
	public $val;

	/** @var string Text zobrazený v tabulce. */
	public $text;

	/** @var ArrayHash Další data stahující se k buňce a používající se např. při výpočtu sumy. */
	public $data;

	public function __construct($val, $text, $data = array()) {
		$this->val = $val;
		$this->text = $text;
		$this->data = ArrayHash::from($data);
	}

	/**
	 * Zvýrazní řádek tak, že každou buňku uzavře do <strong> elementu.
	 */
	public function highlight() {
		$this->text = Html::el('strong')->setText($this->text);
	}

}
