<?php

/*
 * @copyright Copyright (c) 2013-2013 Kukral COMPANY s.r.o.
 */

namespace POS\Ajax;

use \Nette\Application\UI\Presenter;

/**
 * Vyřizuje sjednocené požadavky od klienta. Sem lze přidat jakoukoli
 * funkci, kterou má ajax observer obstarávat
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
class AjaxObserver extends \Nette\Object {

	/**
	 * Handle vyřizující požadavek od klienta (javascriptového Observeru)
	 * @param Presenter $presenter presenter, který bude vracet odpověď
	 * @param AjaxCrate $handles přepravka objektů získávajících data
	 */
	public function sendRefreshRequests(Presenter $presenter, AjaxCrate $handles) {
		$response = $this->executeAllRequests($handles);
		$presenter->sendJson($response);
	}

	/**
	 * Získá data od všech registrovaných objektů
	 * @param AjaxCrate $handles přepravka objektů získávajících data
	 * @return array vyřídí všechny implementované funkce a vrátí jejich výsledky v poli
	 * ve tvaru 'klíč' => jakákoliData
	 */
	public function executeAllRequests(AjaxCrate $handles) {
		$response = array();
		foreach ($handles->getHandles() as $componentKey => $handle) {
			$response[$componentKey] = $handle->getData();
		}
		return $response;
	}

}
