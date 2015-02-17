<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POSComponent;

use Nette\DateTime;

/**
 * Komponenta zobrazující data do grafu. Jde o graf
 * http://www.highcharts.com/demo/areaspline
 * NUTNÉ před použitím přilinkovat jQuery 1.7 a vyšší
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class GraphComponent extends BaseProjectControl {

	const INTERVAL_DAILY = 0;
	const INTERVAL_WEEKLY = 1;
	const INTERVAL_MONTHLY = 2;

	/** @var array Data (hodnoty), které se mají do grafu zobrazit. */
	private $dataToGraph;

	/** @var DateTime Data (hodnoty), které se mají do grafu zobrazit. */
	private $fromDate;

	/** @var int Čas, který se má zobrazit na Y ose (dny, týdny, měsíce, roky) */
	private $interval = 0;

	/** @var string Název celého grafu */
	public $graphName = "Graf";

	/** @var string Velký nadpis celého grafu */
	public $header = "";

	public function __construct(array $dataToGraph, $fromDate, $parent, $name) {
		parent::__construct($parent, $name);
		$this->fromDate = $fromDate;
		$this->dataToGraph = $dataToGraph;
	}

	public function setTimeInterval($interval) {
		$this->interval = $interval;
	}

	/**
	 * Vykresli šablonu.
	 */
	public function render() {
		$this->template->items = $this->dataToGraph;
		$this->template->fromDate = $this->fromDate;
		$this->template->countItems = count($this->dataToGraph);
		$this->template->interval = $this->interval;
		$this->template->graphName = $this->graphName;
		$this->template->header = $this->header;

		$this->template->setFile(dirname(__FILE__) . '/default.latte');
		$this->template->render();
	}

}
