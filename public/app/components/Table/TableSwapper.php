<?php

/**
 * Komponenta zobrazující jednu tabulku ze seznamu tabulek podle toho, kterou chce
 * uživatel aktuálně vidět.
 * Všechny tabulky mají pořadové číslo (první tabulka má p.č. 1). Uživatel
 * si klikne na název tabulky kterou chce vidět (např. s pořadovým číslem 3)
 * a napozadí se pošle AJAX požadavek, že se má zorazit tabulka s pořadovým
 * číslem 3.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */

namespace JKB\Component\Statistics;

use JKB\Component\BaseProjectControl;
use JKB\Component\Statistics\Daily\TableOnlineStatisic as DailyTableOnline;
use JKB\Component\Statistics\Monthly\TableOnlineStatistics as MonthlyTableOnline;

class TableSwapper extends BaseProjectControl {

	/**
	 * @var array Pole komponent ITable. Jen jedna z tabulek je
	 * vidět v jeden čas podle toho, kterou chce uživatel vidět.
	 */
	private $tables;

	/**
	 * @var int Pořadové čístlo tabulky v poli (první prvek má číslo 1)
	 * která se má zobrazit uživateli. Ostatní tabulky se nezobrazí.
	 */
	private $visibleTableNumber = 1;

	public function __construct(array $tables, $parent, $name) {
		parent::__construct($parent, $name);

		$this->tables = $tables;
	}

	public function render() {
		$this->template->tables = $this->tables;
		$this->template->visibleTableNumber = $this->visibleTableNumber; /* zobrazí první tabulku v seznamu */

		$this->template->setFile(dirname(__FILE__) . '/swapper.latte');
		$this->template->render();
	}

	protected function createComponentTable($name) {
		return new TableCom(null, $this, $name);
	}

	public function handleSwapVisibleTable($visibleTableNumber) {
		$this->visibleTableNumber = $visibleTableNumber;
		$this->redrawControl('swapper');
	}

}
