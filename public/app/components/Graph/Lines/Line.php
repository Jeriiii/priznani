<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POSComponent;

use POS\Statistics\IStatistics;
use Nette\ArrayHash;
use Exception;

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
	private $dataModel = NULL;

	/** @var DateTime Data (hodnoty), které se mají do grafu zobrazit. */
	private $fromDate = NULL;

	/** @var int Počet jednotek (počet dní, měsíců ...) */
	private $countItems; // 6 dní = jeden týden (6 + 1)

	const DAILY_MODE = 0;
	const MONTHLY_MODE = 1;

	/** @var int Mód, po jakém časovém intervalu se mají zobrazovat data.	 */
	private $interval;

	public function __construct($name, $data = NULL) {
		$this->name = $name;
		$this->interval = self::DAILY_MODE;

		if ($data instanceof IStatistics) {
			$this->dataModel = $data;
		} else if (is_array($data)) {
			$this->data = $data;
		} else if ($data === NULL) {
			//záměrně je to prázdné
		} else {
			throw new Exception('Variable $data must by instance of IStatistics or array.');
		}
	}

	public function addDataModel(IStatistics $dataModel) {
		$this->dataModel = $dataModel;
	}

	public function addData(array $data) {
		$this->data = $data;
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
		if ($this->fromDate === NULL) {
			throw new Exception('You must call setInterval method first.');
		}

		if (empty($this->data) && empty($this->dataModel)) {
			throw new Exception('You must set dataModel or data.');
		}

		if (empty($this->data)) {
			/* spočítá data, co se mají zobrazit v grafu */
			if ($this->interval == self::DAILY_MODE) {
				$this->data = $this->dataModel->getDaily($this->fromDate, $this->countItems);
			} else {
				$this->data = $this->dataModel->getMonthly($this->fromDate, $this->countItems);
			}
		}

		return $this->data;
	}

	public function getName() {
		return $this->name;
	}

	public function getIterator() {
		$arrayHash = new ArrayHash($this->getData());
		return $arrayHash->getIterator();
	}

}
