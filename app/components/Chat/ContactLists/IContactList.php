<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POSComponent\Chat;

/**
 * Obecná komponenta pro vykreslení seznamu kontaktů
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
interface IContactList {

	/**
	 * vykreslí seznam kontaktů
	 */
	public function render();
}
