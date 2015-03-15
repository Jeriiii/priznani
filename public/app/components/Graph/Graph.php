<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POSComponent;

use Nette\DateTime;
use Nette\Application\UI\Form\IntervalPickerForm;
use DateInterval;
use POS\Statistics\IStatistics;
use POSComponent\Lines;
use POSComponent\Line;

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

	/* Typy grafu */
	const GRAPH_TYPE_AREASPLINE = 'areaspline'; //hory
	const GRAPH_TYPE_LINE = 'line'; //prostá čára
	const GRAPH_TYPE_PIE = 'pie'; //koláč
	const GRAPH_TYPE_AREA = 'area'; //hodnoty se sčítají a dělají hory

	/** @var DateTime Data (hodnoty), které se mají do grafu zobrazit. */

	private $fromDate;

	/** @var int Čas, který se má zobrazit na Y ose (dny, týdny, měsíce, roky) */
	private $interval = self::INTERVAL_DAILY;

	/** @var string Název celého grafu */
	public $graphName = 'Graf';

	/** @var string Typ grafu */
	private $graphType = self::GRAPH_TYPE_AREASPLINE;

	/** @var string Název čáry */
	public $linename1 = '';

	/** @var string Velký nadpis celého grafu */
	public $header = '';

	/** @var int Počet jednotek (počet dní, měsíců ...) */
	private $countItems = 6; // 6 dní = jeden týden (6 + 1)

	/** @var Lines Křivky, co se mají zobrazit v grafu. */
	private $lines = NULL;

	/** @var string Součet všech křivek */
	private $totalLineName = NULL;

	public function __construct($parent, $name, Lines $lines = NULL) {
		parent::__construct($parent, $name);

		$this->lines = $lines;

		$this->fromDate = new DateTime();
		$this->fromDate->modify('- ' . ($this->countItems) . ' ' . $this->interval);
	}

	/**
	 * Přidá další čárů do grafu.
	 * @param IStatistics|array $data Model pro zobrazení dat | data.
	 * @param string $name Název čáry v grafu.
	 */
	public function addLine($data, $name) {
		if ($this->lines === NULL) {
			$this->lines = new Lines();
		}

		$this->lines->addLine($data, $name);
	}

	/**
	 * Přidá čáru s celkovým součtem všech čar.
	 * @param string $name Název čáry se součtem všech ostatních.
	 */
	public function addTotalLine($name) {
		$this->totalLineName = $name;
	}

	/**
	 * Vykresli graf.
	 * @param int $elementId Id html elementu, na který se má graf (plugin) navázat
	 */
	public function render($elementId) {
		$this->template->elementId = $elementId;
		$this->template->graphName = $this->graphName;
		$this->template->graphType = $this->graphType;

		$this->template->setFile(dirname(__FILE__) . '/default.latte');
		$this->template->render();
	}

	/**
	 * Vykresli nastavení js pluginu.
	 * @param int $elementId Id html elementu, na který se má graf (plugin) navázat
	 */
	public function renderJs($elementId) {
		$this->template->lines = $this->getLinesInGraph();
		$this->template->fromDate = $this->fromDate;
		$this->template->countItems = $this->countItems;
		$this->template->interval = $this->interval;
		$this->template->graphName = $this->graphName;
		$this->template->graphType = $this->graphType;
		$this->template->header = $this->header;
		$this->template->elementId = $elementId;
		$this->template->linename1 = $this->linename1;

		$this->template->setFile(dirname(__FILE__) . '/js.latte');
		$this->template->render();
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
	 * Nastaví typ grafu na koláč
	 */
	public function setTypePie() {
		$this->graphType = self::GRAPH_TYPE_PIE;
	}

	/**
	 * Nastaví typ grafu na prostou křivku
	 */
	public function setTypeLine() {
		$this->graphType = self::GRAPH_TYPE_LINE;
	}

	/**
	 * Nastaví typ grafu na sečítající se hory
	 */
	public function setTypeArea() {
		$this->graphType = self::GRAPH_TYPE_AREA;
	}

	/**
	 * Vrátí data do grafu.
	 * @return Lines
	 */
	public function getLinesInGraph() {
		if ($this->interval == self::INTERVAL_MONTHLY) {
			$this->lines->setMonthly();
		}

		$this->lines->setInterval($this->fromDate, $this->countItems);

		if (!empty($this->totalLineName)) {
			$this->lines->addTotalLine($this->totalLineName);
		}

		return $this->lines->getLines();
	}

	protected function createComponentIntervalPickerForm($name) {
		$fromDate = $this->fromDate;
		$toDate = new DateTime($fromDate);
		$toDate->modify('+ ' . ($this->countItems) . ' ' . $this->interval);
		$interval = $this->interval;

		return new IntervalPickerForm($fromDate, $toDate, $interval, $this, $name);
	}

}
