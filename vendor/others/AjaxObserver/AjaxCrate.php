<?php

namespace POS\Ajax;

/*
 * @copyright Copyright (c) 2013-2013 Kukral COMPANY s.r.o.
 */

/**
 * Slouží jako přepravka pro objekty, které musí implementovat rozhraní pro
 * Observer
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
class AjaxCrate extends \Nette\Object {

	private $observerHandles = array();

	/**
	 * Přidá objekt implementující IObserverHandle do přepravky
	 * @param klíč registrovaný u klientské části Observeru
	 * @param IObserverHandle $handle objekt získávající data
	 */
	public function addHandle($key, IObserverHandle $handle) {
		$this->observerHandles[$key] = $handle;
	}

	/**
	 * Vrátí pole dotyčných objektů
	 * @return array
	 */
	public function getHandles() {
		return $this->observerHandles;
	}

}
