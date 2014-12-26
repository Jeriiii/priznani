<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace Notify;

use Nette\Mail\IMailer;

/**
 * Abstraktní třída poskytující fce pro odeslání seznamu emailů.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class Emails {

	/**
	 * @var IMailer Odesílání emailů
	 */
	private $mailer;

	/**
	 * @var array Pole oznámení (EmailNotify), co se mají poslat
	 */
	protected $emailNotifies = array();

	public function __construct(IMailer $mailer) {
		$this->mailer = $mailer;
	}

	/**
	 * Odešle všechny upozornění a odstraní je z fronty
	 */
	public function sendEmails() {
		$notifies = $this->emailNotifies;
		foreach ($notifies as $notify) {
			$notify->sendEmail($this->mailer);
		}
		$this->emailNotifies = array();
	}

}
