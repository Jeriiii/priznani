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
	 * @var array Pole oznámení (Email), co se mají poslat
	 */
	protected $emailNotifies = array();

	/**
	 * Odešle všechny upozornění a odstraní je z fronty
	 */
	public function sendEmails(IMailer $mailer) {
		$notifies = $this->emailNotifies;
		foreach ($notifies as $notify) {
			$notify->sendEmail($mailer);
		}
		$this->emailNotifies = array();
	}

	/**
	 * Vrací pole s emaily, co se mají poslat.
	 * @return array Pole s emaily, co se mají poslat.
	 */
	public function getEmails() {
		$notifies = $this->emailNotifies;
		$emails = array();
		foreach ($notifies as $notify) {
			$emails[] = $notify->getEmail();
		}

		return $emails;
	}

}
