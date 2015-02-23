<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POSComponent;

use POS\Statistics\IStatistics;
use Nette\ArrayHash;

/**
 * Jedna čára (jedny zobrazené data) v grafu
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class Line implements \IteratorAggregate {

	/** @var string Název křívky */
	public $name;

	/** @var array Body grafu - pouze hodnoty Y, hodnoty X jsou dány pořadím */
	private $data = array();

	/** @var IStatistics Model ye kterého se tahají data pro danou čáru. */
	private $dataModel;

	/** @var DateTime Data (hodnoty), které se mají do grafu zobrazit. */
	private $fromDate = NULL;

	/** @var int Počet jednotek (počet dní, měsíců ...) */
	private $countItems; // 6 dní = jeden týden (6 + 1)

	const DAILY_MODE = 0;
	const MONTHLY_MODE = 1;

	/** @var int Mód, po jakém časovém intervalu se mají zobrazovat data.	 */
	private $interval;

	public function __construct(IStatistics $dataModel, $name) {
		$this->dataModel = $dataModel;
		$this->name = $name;
		$this->interval = self::DAILY_MODE;
	}

	/**
	 * Nastaví data aby se načítali po měsících.
	 */
	public function setMonthly() {
		$this->interval = self::MONTHLY_MODE;
	}

	public function setInterval($fromDate, $countItems) {
		$this->fromDate = $fromDate;
		$this->countItems = $countItems + 1; //aby to započítalo i aktuální den
	}

	/**
	 * Vrátí data o čáře
	 */
	public function getData() {
		$data = array();

		if ($this->fromDate === NULL) {
			throw new Exception('You must call setInterval method first.');
		}

		/* spočítá data, co se mají zobrazit v grafu */
		if ($this->interval == self::DAILY_MODE) {
			$data = $this->dataModel->getDaily($this->fromDate, $this->countItems);
		} else {
			$data = $this->dataModel->getMonthly($this->fromDate, $this->countItems);
		}

		return $data;
	}

	public function getName() {
		return $this->name;
	}

	public function getIterator() {
		$arrayHash = new ArrayHash($this->getData());
		return $arrayHash->getIterator();
	}

}
