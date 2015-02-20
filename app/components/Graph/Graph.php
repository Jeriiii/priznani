<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POSComponent;

use Nette\DateTime;
use Nette\Application\UI\Form\IntervalPickerForm;
use DateInterval;
use POS\Statistics\IStatistics;

/**
 * Komponenta zobrazující data do grafu. Jde o graf
 * http://www.highcharts.com/demo/areaspline
 * NUTNÉ před použitím přilinkovat jQuery 1.7 a vyšší
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class Graph extends BaseProjectControl {

	const INTERVAL_DAILY = 'days';
	const INTERVAL_MONTHLY = 'months';

	/** @var array Data (hodnoty), které se mají do grafu zobrazit. */
	private $dataToGraph = NULL;

	/** @var DateTime Data (hodnoty), které se mají do grafu zobrazit. */
	private $fromDate;

	/** @var int Čas, který se má zobrazit na Y ose (dny, týdny, měsíce, roky) */
	private $interval = self::INTERVAL_DAILY;

	/** @var string Název celého grafu */
	public $graphName = "Graf";

	/** @var string Velký nadpis celého grafu */
	public $header = "";

	/** @var int Počet jednotek (počet dní, měsíců ...) */
	private $countItems = 6; // 6 dní = jeden týden (6 + 1)

	/** @var IStatistics Objekt který vrací data, co se mají zobrazit v grafu. */
	private $statistics;

	public function __construct(IStatistics $statistics, $parent, $name) {
		parent::__construct($parent, $name);

		$this->statistics = $statistics;

		$this->fromDate = new DateTime();
		$this->fromDate->modify('- ' . ($this->countItems) . ' ' . $this->interval);
	}

	public function setInterval($interval) {
		$this->interval = $interval;
	}

	/**
	 * Vlastní zadání rozmezí OD - DO
	 * @param date $from Datum od.
	 * @param date $to Datum do.
	 * @param int $interval Časový interval, po kdy se mají dávat body na X osu
	 */
	public function handleSetInterval($from, $to, $interval) {
		$this->setInterval($interval);

		if (!empty($from) && !empty($to)) {
			$this->fromDate = new DateTime($from);
			$to = new DateTime($to);

			/* vypočítání počtu jednotek */
			$diffDate = $to->diff($this->fromDate);
			switch ($this->interval) {
				case self::INTERVAL_DAILY:
					$this->countItems = $diffDate->d;
					break;
				case self::INTERVAL_MONTHLY:
					$this->countItems = $diffDate->m;
					break;
			}
		}
	}

	/**
	 * Vykresli graf.
	 * @param int $elementId Id html elementu, na který se má graf (plugin) navázat
	 */
	public function render($elementId) {
		$this->template->elementId = $elementId;

		$this->template->setFile(dirname(__FILE__) . '/default.latte');
		$this->template->render();
	}

	/**
	 * Vykresli nastavení js pluginu.
	 * @param int $elementId Id html elementu, na který se má graf (plugin) navázat
	 */
	public function renderJs($elementId) {
		$this->template->items = $this->getDataGraph();
		$this->template->fromDate = $this->fromDate;
		$this->template->countItems = $this->countItems;
		$this->template->interval = $this->interval;
		$this->template->graphName = $this->graphName;
		$this->template->header = $this->header;
		$this->template->elementId = $elementId;

		$this->template->setFile(dirname(__FILE__) . '/js.latte');
		$this->template->render();
	}

	public function getDataGraph() {
		$countItems = $this->countItems + 1; //aby to započítalo i aktuální den
		if ($this->dataToGraph === NULL) {
			/* spočítá data, co se mají zobrazit v grafu */
			switch ($this->interval) {
				case self::INTERVAL_DAILY:
					$this->dataToGraph = $this->statistics->getDaily($this->fromDate, $countItems);
					break;
				case self::INTERVAL_MONTHLY:
					$this->dataToGraph = $this->statistics->getMonthly($this->fromDate, $countItems);
					break;
			}
		}

		return $this->dataToGraph;
	}

	protected function createComponentIntervalPickerForm($name) {
		$fromDate = $this->fromDate;
		$toDate = new DateTime($fromDate);
		$toDate->modify('+ ' . ($this->countItems) . ' ' . $this->interval);
		$interval = $this->interval;

		return new IntervalPickerForm($fromDate, $toDate, $interval, $this, $name);
	}

}
