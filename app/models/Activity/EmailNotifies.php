<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

/**
 * Správce emailových oznámení
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */

namespace Notify;

use Nette\Mail\IMailer;
use Nette\Database\Table\ActiveRow;
use Nette\Object;

class EmailNotifies extends Object {

	/**
	 * @var IMailer Odesílání emailů
	 */
	private $mailer;

	/**
	 * @var array Pole oznámení (EmailNotify), co se mají poslat
	 */
	private $emailNotifies = array();

	public function __construct(IMailer $mailer) {
		$this->mailer = $mailer;
	}

	/**
	 * Přidá aktivitu mezi upozornění k odeslání
	 * @param \Nette\Database\Table\ActiveRow $user
	 * @param \Nette\Database\Table\ActiveRow $activity
	 */
	public function addActivity(ActiveRow $user, ActiveRow $activity) {
		$userNotify = $this->getUserNotify($user);
		$userNotify->addActivity($activity);
	}

	/**
	 * Přidá zprávu mezi upozornění k odeslání
	 * @param \Nette\Database\Table\ActiveRow $user
	 */
	public function addMessage(ActiveRow $user) {
		$userNotify = $this->getUserNotify($user);
		$userNotify->addMessage();
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

	/**
	 * existuje již oznámení pro uživatele
	 * @return EmailNotify Upozornění pro uživatele.
	 */
	private function getUserNotify($user) {
		if (!array_key_exists($user->id, $this->emailNotifies)) {
			$this->emailNotifies[$user->id] = new EmailNotify($user);
		}

		return $this->emailNotifies[$user->id];
	}

}
