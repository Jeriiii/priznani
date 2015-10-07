<?php

/**
 * Komponenta pro tabulkové statistiky.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */

namespace JKB\Component\Statistics;

use JKB\Component\BaseProjectControl;

class TableCom extends \POSComponent\BaseProjectControl {

	/**
	 * @var TableStatisics Tabulkové statistiky.
	 */
	private $table;

	public function __construct(ITable $table = null, $parent, $name) {
		parent::__construct($parent, $name);

		$this->table = $table;
	}

	public function render() {
		$rows = $this->table->getRows();
		$this->template->rows = $rows;
		$this->template->columnNames = $this->table->getColumnNames();
		$this->template->header = $this->table->getHeader();
		$this->template->anchor = $this->table->getAnchor();
		$this->template->visibleInviromentColumn = $this->table->visibleInviromentColumn();

		if (count($rows) != 0) {
			$this->template->setFile(dirname(__FILE__) . '/default.latte');
			$this->template->render();
		}
	}

	/**
	 * Vloží tabulku co se má vykreslit až při rendrování.
	 */
	public function renderInjectTable(ITable $table) {
		$this->table = $table;

		$this->render();
	}

}
