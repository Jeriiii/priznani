<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace NetteExt;

use Nette\Http\Session;

/**
 * Třída detekující typ a další vlastnosti zařízení, kterým se klient dotazuje na server. Používá sešnu k uchovávání informací o zařízení
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
class DeviceDetector extends \Nette\Object {

	/** @var \Nette\Http\SessionSection $session sekce sešny použitá k uložení informací o zařízení */
	public $session;

	/** @var Mobile_Detect detektor zařízení, uchovává se v proměnné, aby se při neznalosti zařízení nemusel vytvářet vícekrát
	 * http://mobiledetect.net/ verze 2.8.11 */
	private $detector = NULL;

	public function __construct(Session $session) {
		$this->session = $session->getSection('deviceInformation');
		$this->session->setExpiration('30 days');
	}

	/**
	 * Rozhodne, zda je zařízení desktop nebo laptop.
	 * @return boolean
	 */
	public function isComputer() {
		if ($this->session->isComputer == NULL) {/* nelze kontrolovat přes empty, protože může být FALSE */
			$this->createDetector();
			$this->session->isComputer = !$this->detector->isMobile() && !$this->detector->isTablet(); /* není ani mobil ani tablet, takže musí být počítač */
		}
		return $this->session->isComputer;
	}

	/**
	 * Rozhodne, zda je zařízení tablet.
	 * @return boolean
	 */
	public function isTablet() {
		if ($this->session->isTablet == NULL) {/* nelze kontrolovat přes empty, protože může být FALSE */
			$this->createDetector();
			$this->session->isTablet = $this->detector->isTablet();
		}
		return $this->session->isTablet;
	}

	/**
	 * Rozhodne, zda je zařízení mobilní telefon.
	 * @return boolean
	 */
	public function isMobile() {
		if ($this->session->isMobile == NULL) {/* nelze kontrolovat přes empty, protože může být FALSE */
			$this->createDetector();
			$this->session->isMobile = $this->detector->isMobile(); /* není ani mobil ani tablet, takže musí být počítač */
		}
		return $this->session->isMobile;
	}

	/**
	 * Vytvoří detektor zařízení, pokud ještě neexistuje
	 */
	public function createDetector() {
		if (empty($this->detector)) {
			$this->detector = new Mobile_Detect;
		}
	}

}
