<?php

/*
 * @copyright Copyright (c) 2013-2013 Kukral COMPANY s.r.o.
 */

namespace POS\Ajax;

/**
 * 	Interface pro objekty implementované programátory ajaxových komponent s obnovováním
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
interface IObserverHandle {

	/**
	 * Vrátí data, která vyžaduje komponenta na klientovi
	 * @return mixed Data, která potřebuje registrovaná javascriptová komponenta na klientovi
	 */
	public function getData();
}
