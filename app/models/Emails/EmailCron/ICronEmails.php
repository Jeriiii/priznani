<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace Notify;

/**
 * Emaily odeslatelné cronem.
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
interface ICronEmails {

	/**
	 * Vrací objekt pro práci s emaily již plně nastavený
	 * @return Emails Nastavený objekt pro práci s emaily
	 */
	public function createEmails();

	/**
	 * Oznámí, že všechny emaily co mohli být do teď odeslány, opravdu odeslány jsou
	 */
	public function markEmailsLikeSended();
}
