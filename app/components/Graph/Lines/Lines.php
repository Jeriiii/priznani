<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POSComponent;

use POS\Statistics\IStatistics;
use RecursiveArrayIterator;
use Nette\ArrayHash;
use Nette\DateTime;

/**
 * Všechny křivky v grafu
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class Lines {

	/** @var Line Křivky které mají jít do grafu. */
	private $lines = array();

	/**
	 * Přidá další čárů do grafu.
	 * @param IStatistics $dataModel Model pro zobrazení dat.
	 * @param string $name Název čáry v grafu.
	 */
	public function addLine(IStatistics $dataModel, $name) {
		$this->lines[] = new Line($dataModel, $name);
	}

	/**
	 * Vrátí všechny čáry v grafu.
	 * @return array
	 */
	public function getLines() {
		return $this->lines;
	}

	/**
	 * Data se budou v grafu zobrazovat po měsících.
	 */
	public function setMonthly() {
		foreach ($this->lines as $line) {
			$line->setMonthly();
		}
	}

	public function setInterval($fromDate, $countItems) {
		$fromDate = new DateTime($fromDate); //ochrana proti posunutí datumu z vnějšku

		foreach ($this->lines as $line) {
			$line->setInterval($fromDate, $countItems);
		}
	}

}
