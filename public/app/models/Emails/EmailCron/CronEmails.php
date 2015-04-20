<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace Notify;

use Nette\Mail\IMailer;

/**
 * Pro práci s emaily v cronu.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
abstract class CronEmails implements ICronEmails {

	/**
	 * Odešle emaily uživatelům.
	 */
	public function sendEmails(IMailer $mailer) {
		$emails = $this->createEmails();
		$emails->sendEmails($mailer);
	}

	/**
	 * Vrátí pole s emaily uživatelů
	 */
	public function getEmails() {
		$emails = $this->createEmails();
		return $emails->getEmails();
	}

}
