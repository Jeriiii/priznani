<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace Notify;

/**
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
interface IEmail {

	/**
	 * Vrátí předmět emailu
	 */
	public function getEmailSubject();

	/**
	 * Vrtátí zprávu co se má odeslat uživateli.
	 */
	public function getEmailBody();
}
