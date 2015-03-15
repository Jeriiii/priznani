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

	/** @var DateTime Data (hodnoty), které se mají do grafu zobrazit. */
	private $fromDate = NULL;

	/** @var int Počet jednotek (počet dní, měsíců ...) */
	private $countItems; // 6 dní = jeden týden (6 + 1)

	/**
	 * Přidá další čárů do grafu.
	 * @param IStatistics|array $data Model pro zobrazení dat | data.
	 * @param string $name Název čáry v grafu.
	 */

	public function addLine($data, $name) {
		$this->lines[] = new Line($name, $data);
	}

	/**
	 * Přidá čáru s celkovým součtem všech čar. Musí se přidat jako poslední křívka!
	 * @param string $name Název čáry se součtem všech ostatních.
	 */
	public function addTotalLine($name) {
		$data = array();

		foreach ($this->lines as $line) {
			$dataLine = $line->getData();
			foreach ($dataLine as $key => $val) {
				if (array_key_exists($key, $data)) {
					$data[$key] = $data[$key] + $val;
				} else {
					$data[$key] = $val;
				}
			}
		}

		$line = new Line($name, $data);
		$line->setInterval($this->fromDate, $this->countItems);
		$this->lines[] = $line;
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
		$this->fromDate = new DateTime($fromDate); //ochrana proti posunutí datumu z vnějšku
		$this->countItems = $countItems;

		foreach ($this->lines as $line) {
			$line->setInterval($this->fromDate, $this->countItems);
		}
	}

}
